<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
Class Item_picked extends REST_Controller {


public function __construct() {
       parent::__construct();
       $this->load->database();
    }

//get information about latest expired date. Cek and check
public function index_get(){
        $kode_wtr=$this->uri->segment('3');
       // $epoch=$this->uri->segment('4');

        $sql_2="SELECT a.kode_barang, a.uid_picked, SUM(a.qty) as qty
                from nfc_transaksi a
                where no_WTR_pick='$kode_wtr' 
                GROUP BY a.kode_barang, a.uid_picked
                ";
        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        //var_dump($data2);
       
        $this->response($data2, REST_Controller::HTTP_OK);

    } 
}
 ?>