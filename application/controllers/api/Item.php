<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Item extends REST_Controller {
    
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
            $sql="SELECT a.no_WTR
                    from wtr_header a
                    left join wtr_detail b on a.id=b.id_WTR_header
                    inner join items c on c.kode_barang_card = b.kode_barang
                    group by 
                    no_WTR";
            $query = $this->db->query($sql);   
            $data= $query->result_array();
            $item=0;
           
            $list_final=array();
            $list=array();
            $no_wtr_before=0;
            foreach ($data as $key) {
                $barang_detil=array();
                $no_wtr=$key['no_WTR'];
                $barang_detil["no_WTR"]=$no_wtr;

                //cek no wtr yg sekarang sama dengan sebelumnya atau engga
                if($no_wtr===$no_wtr_before){   
                    //kalo ya, di skip atau continue aja
                    continue;

                }else{
                    $barang_detil_2=array();
                    //kalo enggak baru push array
                    $sql_2="SELECT a.no_WTR, b.kode_barang, b.nama_barang, b.qty, c.uid
                    from wtr_header a
                    left join wtr_detail b on a.id=b.id_WTR_header
                    inner join items c on c.kode_barang_card = b.kode_barang
                    where a.no_WTR='$no_wtr' ";
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
                                        "uid" => $key2['uid'],
                                        "no_WTR"=>$key2['no_WTR'],

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
        $this->db->insert('transaksi',$input);
     
        $this->response(['Item created successfully.'], REST_Controller::HTTP_OK);
    } 
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_put($id)
    {
        $input = $this->put();
        $this->db->update('wtr_header', $input, array('id'=>$id));
     
        $this->response(['Item updated successfully.'], REST_Controller::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_delete($id)
    {
        $this->db->delete('wtr_header', array('id'=>$id));
       
        $this->response(['Item deleted successfully.'], REST_Controller::HTTP_OK);
    }
    	
}