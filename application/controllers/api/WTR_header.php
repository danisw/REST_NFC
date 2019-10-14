<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class WTR_header extends REST_Controller {
	public function __construct() {
       parent::__construct();
       $this->load->database();
    }
	public function index_get(){

		$category=$this->uri->segment('3');
		$destwh_id=$this->uri->segment('4');
		$scwh_id=$this->uri->segment('5');

		/****** Untuk get Data list WTR yang ditampilkan di awal ****/
		$sql="SELECT DISTINCT
        TACCWHTREQUISITION.whtrequisitionnum AS no_wtr,
        ISNULL( (Select DISTINCT epoch from dbo.nfc_transaksi a
       where a.no_WTR_pick=TACCWHTREQUISITION.whtrequisitionnum and a.epoch NOT IN (Select h_wtr_id from nfc_wtr_head) ) , 0) as epoch,
        TACCWHLOCATION.WH_NAME AS wh_sc, 
        CONVERT(VARCHAR(11), TACCWHTREQUISITION.Creation_DateTime, 106) AS waktu, 
        TACCWHTREQUISITION.whtrequisitionid AS id_wtr 
      FROM 
        TACCWHTREQUISITION 
        left JOIN TACCWHLOCATION 
          ON TACCWHLOCATION.WH_ID = TACCWHTREQUISITION.SOURCEWH_ID 
        left JOIN TACCWHTREQUISITION_Detail
          ON TACCWHTREQUISITION.whtrequisitionnum = TACCWHTREQUISITION_Detail.whtrequisitionnum
          WHERE 
      	 TACCWHTREQUISITION.destwh_id = $destwh_id
      	 AND  TACCWHTREQUISITION.SOURCEWH_ID = $scwh_id
        AND TACCWHTREQUISITION.ItemCategoryType = '$category'
        AND TACCWHTREQUISITION.Status = 1 
        AND (
              TACCWHTREQUISITION.whtrequisitionnum LIKE 'WTR%' 
            
        )
        AND ISNULL(TACCWHTREQUISITION.Whtransfer_status,0) = 0 
        AND TACCWHTREQUISITION.whtrequisitionid not in 
        	(
            	select
                	TAccWHTransfer_Header.whtrequisitionid
                from
                	TAccWHTransfer_Header
                where
         
                 TAccWHTransfer_Header. approvalstatus not in (3,4)  and isnull(TAccWHTransfer_Header.isVoid,0)=0
            )
       GROUP BY
       	TACCWHTREQUISITION.whtrequisitionnum,
        TACCWHLOCATION.WH_NAME,
        TACCWHTREQUISITION.Creation_DateTime,
        TACCWHTREQUISITION.whtrequisitionid,
        TACCWHTREQUISITION_Detail.Item_code,
        TACCWHTREQUISITION_Detail.Dimension_ID
        
        HAVING (SUM(TACCWHTREQUISITION_detail.Request_Qty) > ISNULL((
        SELECT 
          SUM(TAccWHTransfer_Detail.Approved_Qty) 
        FROM 
          TAccWHTransfer_Detail 
          left JOIN TAccWHTransfer_Header 
            ON TAccWHTransfer_Header.WHTReqNum = TAccWHTransfer_Detail.WHTReqNum
        WHERE 
          TAccWHTransfer_Header.whtrequisitionid = TACCWHTREQUISITION.WHTRequisitionID 
           AND isNull(TAccWHTransfer_Header.isVoid,0)=0
          AND TAccWHTransfer_Detail.Item_code = TACCWHTREQUISITION_Detail.Item_Code 
          AND TAccWHTransfer_Detail.Dimension_ID = TACCWHTREQUISITION_Detail.Dimension_ID 
          AND TAccWHTransfer_Header.ApprovalStatus IN (0,1,2,3,5)), 0))
        order by TACCWHLOCATION.WH_NAME desc,TACCWHTREQUISITION.whtrequisitionnum asc";

        $query2 = $this->db->query($sql);
        $data2=$query2->result_array();
        $data_final=array();

        foreach($data2 as $key){
          $NO_WTR=$key["no_wtr"];
            $sql27="SELECT * , ISNULL(CAST ((Approved_qty/request_qty)*100 as int ),0) AS presentase from
                    (select a.WHTRequisitionID, SUM(b.request_qty) as request_qty from dbo.TAccWHTRequisition a 
                    left join dbo.TAccWHTRequisition_Detail b on a.WHTRequisitionNum=b.WHTRequisitionNum
                    where a.WHTRequisitionNum='$NO_WTR' 
                    group by a.WHTRequisitionID
                    ) A 
                    left join
                   (select a.whtrequisitionid, SUM(b.Approved_qty) as Approved_qty
                   from dbo.TAccWHTransfer_Header a 
                   left join dbo.TAccWHTransfer_Detail b on a.WHTReqNum=b.WHTReqNum
                   where a.ApprovalStatus=3 and ISNULL(a.isVoid,0)=0
                   group BY a.whtrequisitionid
                   ) B on A.WHTRequisitionID=B.whtrequisitionid";

                  $query = $this->db->query($sql27);
                  $data=$query->result_array();
                  foreach ($data as $key2) {
                    $presentase = $key2["presentase"];
                    $request_qty = round($key2["request_qty"],2);
                    $Approved_qty = round($key2["Approved_qty"],2);
                  }
                  $data3["no_wtr"]=$NO_WTR;
                  $data3["epoch"]=$key["epoch"];
                  $data3["wh_sc"]=$key["wh_sc"];
                  $data3["waktu"]=$key["waktu"];
                  $data3["id_wtr"]=$key["id_wtr"];
                  $data3["presentase"]=$presentase;
                  $data3["request_qty"]=$request_qty;
                  $data3["Approved_qty"]=$Approved_qty;
                  array_push($data_final, $data3);
              }

        			 $this->response($data_final, REST_Controller::HTTP_OK);
       
	}
}
?>