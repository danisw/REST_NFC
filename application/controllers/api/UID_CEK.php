<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
Class UID_CEK extends REST_Controller {


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
        $uid=$this->uri->segment('5');

        $sql_22="SELECT * from dbo.nfc_rcv
                where kode_item_rcv=$kode_item 
                and exp_date > getdate() and uid_nfc='$uid' 
                order by exp_date asc";

        $sql_23="SELECT 
                nih_item,
                nih_uid_nfc,
                sum(
                CASE nih_trx_type
                    WHEN '+' THEN nih_qty
                    ELSE -nih_qty
                END
                ) qty_sisa
                 from nfc_item_hist
                where nih_uid_nfc='$uid' and nih_wh=23 and nih_item=$kode_item 
                GROUP BY nih_item,nih_uid_nfc
                HAVING sum(
                CASE nih_trx_type
                    WHEN '+' THEN nih_qty
                    ELSE -nih_qty
                END
                ) > 0";

        $sql_22="SELECT  --TOP 1
                nih_item,
                nih_lot,
                sum(
                CASE nih_trx_type
                    WHEN '+' THEN nih_qty
                    ELSE -nih_qty
                END
                ) qty_sisa
                 from nfc_item_hist
                where nih_uid_nfc='3CEF9F04' and
                nih_wh=23 and nih_item=$kode_item
                GROUP BY nih_item,nih_lot
                HAVING sum(
                CASE nih_trx_type
                    WHEN '+' THEN nih_qty
                    ELSE -nih_qty
                END
                ) > 0";

        $sql_2="SELECT a.nic_uid_nfc, a.nic_lot, a.nic_expired, a.nic_qty, a.nic_wh, a.nic_item
                from nfc_id_card a
                where a.nic_expired > getdate()
                and a.nic_uid_nfc='$uid'
                and a.nic_item =$kode_item
                and a.nic_qty >0";

        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        //var_dump($data2);
       
        $this->response($data2, REST_Controller::HTTP_OK);

    } 
}
 ?>