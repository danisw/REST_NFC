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
        $kode_item=$this->uri->segment('4');
        $no_lot=$this->uri->segment('5');
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

        $sql_2="SELECT TOP 1 a.nic_uid_nfc, a.nic_lot, a.nic_expired, a.nic_qty, a.nic_wh, a.nic_item
                from nfc_id_card a
                where a.nic_expired > getdate()
                and a.nic_item =$kode_item
               -- and a.nic_uid_nfc='BC348E04'
                --and a.nic_wh=23
                and a.nic_qty >0
                order by nic_expired ASC";

        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        $this->response($data2, REST_Controller::HTTP_OK);

    } 
}
 ?>