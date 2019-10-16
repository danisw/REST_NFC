<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
Class Cek extends REST_Controller {


public function __construct() {
       parent::__construct();
       $this->load->database();
    }

//get information about latest expired date. Cek and check
public function index_get(){
        //ambil data yang punya item code tsb dan 
        //jika ada dibandingkan yang paling terkini
        // samakan uid_nfc, kalo sama => return true, slah return false & uid nya
    $data_final=array();
        $kode_item=$this->uri->segment('4');
        $uid=$this->uri->segment('5');
        $no_lot=$this->uri->segment('6');
       // $uid=$this->uri->segment('6');
        $sql_3="SELECT TOP 1 a.no_rcv ,a.uid_nfc uid, a.kode_item_rcv kode_item, a.exp_date exp_date, a.qty_rcv
                from dbo.nfc_rcv a
                where a.kode_item_rcv=$kode_item 
                AND a.qty_rcv >= 0
                --AND a.no_lot='$no_lot'
                order by a.exp_date asc ";

        $sql_22="SELECT  TOP 1
                nih_item as kode_barang, nih_uid_nfc as uid,nih_expired as exp_date,sum(
                CASE nih_trx_type
                    WHEN '+' THEN nih_qty
                    ELSE -nih_qty
                END
                ) As qty
                 from nfc_item_hist
                where --nih_uid_nfc<>'BC348E04' and 
                --- nih WH dari shared preffered login ----
                nih_wh=23 and nih_item=$kode_item 
                GROUP BY nih_item,nih_uid_nfc,nih_expired
                HAVING sum(
                CASE nih_trx_type
                    WHEN '+' THEN nih_qty
                    ELSE -nih_qty
                END
                ) > 0
                ORDER BY nih_expired ASC
                ";

        $sql_2="SELECT TOP 1 a.nic_uid_nfc, a.nic_lot, a.nic_expired, a.nic_qty, a.nic_wh, a.nic_item, a.nic_location
                from nfc_id_card a
                where a.nic_expired > getdate()
                and a.nic_item =$kode_item
               -- and a.nic_uid_nfc='BC348E04'
                --and a.nic_wh=23
                and a.nic_qty >0
                order by nic_expired ASC";

        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();

        $sql_3="SELECT 
        a.nic_lot as lot_card, a.nic_qty as qty_card, a.nic_expired as expired_card, a.nic_location as location_card
         from nfc_id_card a where a.nic_uid_nfc='$uid'";
        $query3 = $this->db->query($sql_3);
        $data3=$query3->result_array();

        $sql_4="SELECT isnull(sum(qty),0) as qty from dbo.nfc_transaksi a
                 where kode_barang=$kode_item and uid_picked='$uid'
                 and not EXISTS (Select * from dbo.nfc_wtr_head h where epoch = h.h_wtr_id)";
        $query4 = $this->db->query($sql_4);
        $data4=$query4->result_array();
        foreach ($data4 as $key) {
            $qty_transaksi=$key["qty"];
        }

        if( count($data2) > 0 && count($data3)>0 ){
           // $data_final=["bisa"];
             foreach ($data3 as $key_card ) {
               $lot_card=$key_card["lot_card"];
               $qty_card=$key_card["qty_card"];
               $expired_card=$key_card["expired_card"];
               $location_card=$key_card["location_card"];
            }

            foreach ($data2 as $key ) {
                //hitung qty gantung
                $val_ready = number_format($qty_card) - number_format($qty_transaksi);
              $data_["nic_uid_nfc"]   = $key["nic_uid_nfc"];
              $data_["nic_lot"]       = $key["nic_lot"];
              $data_["nic_expired"]   = $key["nic_expired"];
              $data_["nic_qty"]       = $key["nic_qty"];
              $data_["nic_wh"]        = $key["nic_wh"];
              $data_["nic_item"]      = $key["nic_item"];
              $data_["lot_card"]      = $lot_card;
              $data_["qty_card"]      = number_format($qty_card);
              $data_["expired_card"]  = $expired_card;
              $data_["location_exp"] = $key["nic_location"];
              $data_["qty_ready"]    = $val_ready;
              array_push($data_final, $data_);
            }
        }else{
             $data_final=[];
        }

       
        $this->response($data_final, REST_Controller::HTTP_OK);

    } 
}
 ?>