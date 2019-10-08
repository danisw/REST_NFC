<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class TES extends REST_Controller {
    
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
    public function index_get($id = 0){

        $kode  = '01020101000339';
        $uid   = 'BC348E04';
        $qty   = '2';
        //select qty di rcv
        $this->db->select('qty_rcv');
        $this->db->where('kode_item_rcv',$kode);
        $this->db->where('uid_nfc',$uid);
        $this->db->where('qty_rcv >','0');
        $query = $this->db->get('nfc_rcv');
        $q1=$query->result_array();
        //dikurangi qty input
        foreach ($q1 as $key) {
            $qty_rcv=$key["qty_rcv"];
        }
        $new_qty = $qty_rcv-$qty;
        var_dump($q1);
    }

}

?>