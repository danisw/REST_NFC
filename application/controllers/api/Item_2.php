<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Item_2 extends REST_Controller {
    
      /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function __construct() {
       parent::__construct();
       $this->load->database();
    }
       
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_get($id = 0)
    {
    
     // if(!empty($id)){
     //        $this->db->select('*');
     //        $this->db->from('wtr_header');
     //        $this->db->join('wtr_detail', 'wtr_detail.id_WTR_header = wtr_header.id');
     //         $this->db->where(array('wtr_detail.id_WTR_header'=> $id));
     //        $query = $this->db->get();
     //        $data=$query->result();

     //    }else{

            // $this->db->select('*');
            // $this->db->from('wtr_header');
            // $this->db->join('wtr_detail', 'wtr_header.id = wtr_detail.id_WTR_header');
            // $this->db->join('items', 'wtr_detail.kode_barang=items.kode_barang_card');

            // $query=$this->db->get();
            // $data = $query->result_array();
            $sql="SELECT a.no_WTR,a.waktu
                    from nfc_wtr_header a
                    left join nfc_wtr_detail b on a.id=b.id_WTR_header
                    inner join nfc_rcv c on c.kode_item_rcv = b.kode_barang
                    group by 
                    a.no_WTR,a.waktu";
            $query = $this->db->query($sql);   
            $data= $query->result_array();
            $item=0;
           
            $list_final=array();
            $list=array();
            $no_wtr_before=0;
            foreach ($data as $key) {
                $barang_detil=array();
                $no_wtr=$key['no_WTR'];
                $waktu=$key['waktu'];
                $barang_detil["no_WTR"]=$no_wtr;
                $barang_detil["waktu"]=$waktu;

                //cek no wtr yg sekarang sama dengan sebelumnya atau engga
                if($no_wtr===$no_wtr_before){   
                    //kalo ya, di skip atau continue aja
                    continue;

                }else{
                    $barang_detil_2=array();
                    //kalo enggak baru push array
                    // $sql_2="SELECT a.no_WTR, b.kode_barang, b.nama_barang, b.qty, c.uid
                    // from nfc_wtr_header a
                    // left join nfc_wtr_detail b on a.id=b.id_WTR_header
                    // inner join nfc_item c on c.kode_barang_card = b.kode_barang
                    // where a.no_WTR='$no_wtr' ":


                     $sql_2=" SELECT b.no_WTR,c.kode_barang,c.nama_barang,c.qty,(c.qty-trans.qty_trans) as sisa_qty, (trans.qty_trans) as picked_qty
                    from nfc_wtr_header b
                    left join nfc_wtr_detail c on c.id_WTR_header=b.id
                    left join (
                    select a.no_WTR_pick,a.kode_barang, SUM(a.qty) qty_trans from nfc_transaksi a
                    GROUP by a.kode_barang,a.no_WTR_pick,a.kode_barang ) as trans on b.no_WTR=trans.no_WTR_pick and trans.kode_barang=c.kode_barang
                    where b.no_WTR='$no_wtr'
                    ";
                    $query2 = $this->db->query($sql_2);   
                    //return $query->result_array();
                    $item=array();
                    $data2=$query2->result_array();
                    //var_dump($data2);
                        foreach ($data2 as $key2) {

                            $barang_detil["detil"][]=
                               array("kode_barang" => $key2['kode_barang'],
                                     "nama_barang"=>$key2['nama_barang'],
                                        "qty"=>$key2['qty'],
                                        //"uid" => $key2['uid'],
                                        "no_WTR"=>$key2['no_WTR'],
                                        "picked_qty"=>$key2['picked_qty'],
                                        "sisa_qty"=>$key2['sisa_qty'],
                                );
                        }

                        array_push($list,$barang_detil);     
                }
                //set no wtr before berisi no wtr yg skrg
                $no_wtr_before=$no_wtr;
            }
     
        $this->response($list, REST_Controller::HTTP_OK);
    }
      
    /**
     * Get All Data from this method.
     * 
     * @return Response
    */
    public function index_post()
    {
        $input = $this->input->post();
        $kode = $this->input->post("kode_barang");
        $uid = $this->input->post("uid_picked");
        $qty = $this->input->post("qty");
        //$qty = $this->input->post("qty");

        //select qty di rcv
        // $this->db->select('qty_rcv');
        // $this->db->where('kode_item_rcv',$kode);
        // $this->db->where('uid_nfc',$uid);
        // $this->db->where('qty_rcv >','0');
        // $query = $this->db->get('nfc_rcv');
        // $q1=$query->result_array();

        //dikurangi qty input
        // foreach ($q1 as $key) {
        //     $qty_rcv = $key['qty_rcv'];
        // }
        //  $new_qty = $qty_rcv-$qty;

        //transaction begin
        $this->db->trans_start();

        $sql="SELECT * from dbo.nfc_id_card where nic_uid_nfc='$uid'";
        $query = $this->db->query($sql);
        $data=$query->result_array();
        foreach ($data as $key) {
            $exp=$key["nic_expired"];
        }
        $input["exp_date"]=$exp;
        //insert data transaksi
        $trans = $this->db->insert('nfc_transaksi',$input);

        //update data qty in rcv table
        // $this->db->set('qty_rcv',$new_qty);
        // $this->db->where('kode_item_rcv',$kode);
        // $this->db->where('uid_nfc',$uid);
        // $update=$this->db->update('nfc_rcv');

        //transaksi end
        $this->db->trans_complete();
        //get status transaksi
        $status=$this->db->trans_status();
        
       if($status){
            $this->response(['Item created successfully. '.$kode.' '.$uid.' '.$qty.' '.$new_qty], REST_Controller::HTTP_OK);
        }else{}
        
    } 
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_put($id)
    {
        $input = $this->put();
        $this->db->update('nfc_wtr_header', $input, array('id'=>$id));
     
        $this->response(['Item updated successfully.'], REST_Controller::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_delete($id)
    {
        $this->db->delete('nfc_wtr_header', array('id'=>$id));
       
        $this->response(['Item deleted successfully.'], REST_Controller::HTTP_OK);
    }
       
    
}