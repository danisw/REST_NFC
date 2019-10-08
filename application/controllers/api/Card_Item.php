<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Card_Item extends REST_Controller {
    
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
        $this->db->select('*');
            $this->db->from('items');
            // $this->db->join('wtr_detail', 'wtr_detail.id_WTR_header = wtr_header.id');
              $this->db->where(array('items.uid'=> $id));
        // $sql='SELECT * from wtr_header a
        //     left join wtr_detail b on a.id=b.id_WTR_header
        //     where b.id_WTR_header=2';
            $query = $this->db->get();
            $data=$query->result();

    //     $query = $this->db->select('wtr_header.no_WTR, wtr_detil.',false)
    // ->from('wtr_header')
    // ->join('wtr_detil', 'wtr_header.id = wtr_detil.id_WTR_header', 'left')
    // ->where_in('order_id', $order_id)
    // ->get();
    // return $query->result();
     if(!empty($id)){
            $this->db->select('*');
            $this->db->from('items');
            // $this->db->join('wtr_detail', 'wtr_detail.id_WTR_header = wtr_header.id');
              $this->db->where(array('items.id'=> $id));
        // $sql='SELECT * from wtr_header a
        //     left join wtr_detail b on a.id=b.id_WTR_header
        //     where b.id_WTR_header=2';
            $query = $this->db->get();
            $data=$query->result();

        }else{

            $this->db->select('*');
            $this->db->from('items');
           // $this->db->join('wtr_detail', 'wtr_header.id = wtr_detail.id_WTR_header');
            $query = $this->db->get();
            $data=$query->result();
     //        //$data = $this->db->get("wtr_header")->result();
     //        //$data1 = $this->db->get("wtr_header")->result();
     //        //var_dump($data);
         }
     
        $this->response($data, REST_Controller::HTTP_OK);
	}
      
    /**
     * Get All Data from this method.
     * 
     * @return Response
    */
    public function index_post()
    {
        $input = $this->input->post();
        $this->db->insert('items',$input);
     
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
        $this->db->update('items', $input, array('id'=>$id));
     
        $this->response(['Item updated successfully.'], REST_Controller::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_delete($id)
    {
        $this->db->delete('items', array('id'=>$id));
       
        $this->response(['Item deleted successfully.'], REST_Controller::HTTP_OK);
    }
    	
}