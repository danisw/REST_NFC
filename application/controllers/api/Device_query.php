<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Device_query extends REST_Controller {
	public function __construct() {
       parent::__construct();
       $this->load->database();
    }
	public function index_get(){
		$address=$this->uri->segment('3');

		$sql_2="SELECT * FROM  dbo.nfc_device a where a.alamat_device='$address' ";

        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        $this->response($data2, REST_Controller::HTTP_OK);
	}
}