<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
Class Dest_wh extends REST_Controller {


public function __construct() {
       parent::__construct();
       $this->load->database();
    }

//get information about latest expired date. Cek and check
public function index_get(){
  $company=$this->uri->segment('3');
	 $sql_2="SELECT wh_id,wh_name 
			from dbo.TAccWHLocation
			where company_id=$company and (wh_name LIKE '%Packing%' or wh_name LIKE '%Baku%' ) ";

     $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        $data_result["result"]=$data2;
        $this->response($data_result, REST_Controller::HTTP_OK);
	}
}
?>