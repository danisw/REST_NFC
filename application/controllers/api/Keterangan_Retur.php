<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
Class Keterangan_Retur extends REST_Controller {


public function __construct() {
       parent::__construct();
       $this->load->database();
    }

//get information about latest expired date. Cek and check
public function index_get(){
	 $sql_2="SELECT kd_ket, nama_ket from dbo.unm_katagori_retur";
     $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        $data_result["result"]=$data2;
        $this->response($data_result, REST_Controller::HTTP_OK);
}
}
?>