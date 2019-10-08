<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
Class Cek extends CI_Controller {


public function __construct() {
       parent::__construct();
       $this->load->database();
    }

public function cek_qty(){
        //ambil data yang punya item code tsb dan 
        //jika ada dibandingkan yang paling terkini
        // samakan uid_nfc, kalo sama => return true, slah return false & uid nya
        $sql_2="SELECT TOP 1 a.uid_nfc uid, a.kode_item_rcv kode_item, a.exp_date exp_date
                from nfc_rcv a
                where a.kode_item_rcv='01020101000339' AND a.qty_rcv >= '2'
                order by a.exp_date asc ";
        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        var_dump($data2);
        //echo "tes";
        $this->response($data2, REST_Controller::HTTP_OK);

    } 
}
 ?>