<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Template extends REST_Controller {
	public function __construct() {
       parent::__construct();
       $this->load->database();
    }

    //select
	public function index_get(){
		// $address=$this->uri->segment('3');

		// $sql_2="SELECT * FROM  dbo.nfc_device a where a.alamat_device=$address ";

  //       $query2 = $this->db->query($sql_2);

		  //select 
	    	$cmp=2;
	    	$length=7;
		     $whs=30;
		     $tgl=date("yn");
		     $doc_no = 'WHT'.$cmp.$whs.$tgl.'-';
		     $nik_user="UMS16050172";	

	    	$sqlno  =" SELECT LastNumber+1 akhir FROM TAccPattern  WHERE PatternGroup = 'WarehouseTransfer' AND isnull(wh_id,0) = 0 AND Company_ID = $cmp and FieldName='DiffNumber'";

			$qGetNo = $this->db->query($sqlno);

        	$arr_query = $qGetNo->result_array();

        	foreach ($arr_query as $key ) {
        		$urut=$key["akhir"];
        	}

        	$nolnya = $length-strlen($urut);
	        $wht_no = $doc_no.str_repeat("0",$nolnya).$urut;

        	//$hasil = 'hasilnya :'.$urut['akhir'];
        	$updPattern  = " SELECT TOP 1 * from TAccPattern 
							 WHERE PatternGroup = 'WarehouseTransfer' and isnull(wh_id,0) = 0
							 And Company_ID = $cmp and FieldName='DiffNumber' and LastNumber < $urut";
			$query = $this->db->query($updPattern);
			$arr_pattern = $query->result_array();

			$sqll="SELECT a.Bin_ID, a.Bin_Code, a.Bin_Name, a.WH_ID, b.company_id from dbo.TAccWHBin a
				   left join dbo.TAccWHLocation b on a.WH_ID=b.wh_id
				   where a.WH_ID= $whs";
		    $queryy= $this->db->query($sqll);
		    $arr_whs=$queryy->result_array();
		    foreach ($arr_whs as $key) {
		    	$cmp2= $key["company_id"];
		    	$wh_bin=$key["Bin_ID"];
		    }

		    $sql5="SELECT User_ID from dbo.THRMEmpPersonalData
				where Emp_ID= '$nik_user'";
			$querry=$this->db->query($sql5);
			$resultz=$querry->result_array();
			 foreach ($resultz as $key) {
				$user_id=$key["User_ID"];
			}

        //$data2=$query2->result_array();
        $this->response($user_id." | ", REST_Controller::HTTP_OK);
	}

  //Post
  	public function index_post()
  	{
	    $input = $this->input->post();
	   // $cmp=2;
	    $urut;
	     $length=7;
	    // $cmp=2;
	     //whs dapat dari post android, wh penginput android
	     $whs=30;
	     $tgl=date("yn");
	     //post dari login android
	     $nik_user="UMS16050172";
	    	    

	    $this->db->trans_start();
	    //================= Ambil user_id dari nik ------------------//
	    $sql5="SELECT User_ID from dbo.THRMEmpPersonalData
				where Emp_ID= '$nik_user'";
			$querry=$this->db->query($sql5);
			$resultz=$querry->result_array();
			 foreach ($resultz as $key) {
				$user_id=$key["User_ID"];
			}
	    //**************** Ambil company, wh_bin *******************//
	    	$sqll="SELECT a.Bin_ID, a.Bin_Code, a.Bin_Name, a.WH_ID, b.company_id from dbo.TAccWHBin a
				   left join dbo.TAccWHLocation b on a.WH_ID=b.wh_id
				   where a.WH_ID= $whs";
		    $queryy= $this->db->query($sqll);
		    $arr_whs=$queryy->result_array();
		    foreach ($arr_whs as $key) {
		    	$cmp= $key["company_id"];
		    	$wh_bin=$key["Bin_ID"];
		    }
		     $doc_no = 'WHT'.$cmp.$whs.$tgl.'-';

	   //***************** Ambil Last Number ***********************//

	    	$sqlno  =" SELECT LastNumber+1 akhir FROM TAccPattern  
	    	WHERE PatternGroup = 'WarehouseTransfer' AND isnull(wh_id,0) = 0 AND Company_ID = $cmp and FieldName='DiffNumber'";

			$qGetNo = $this->db->query($sqlno);

        	$arr_query = $qGetNo->result_array();

        	foreach ($arr_query as $key ) {
        		$urut=$key["akhir"];
        	}


		//******** update booking pattern ****************//
        	$updPattern  = " UPDATE TAccPattern 
							 SET LastNumber = $urut
							 WHERE PatternGroup = 'WarehouseTransfer' and isnull(wh_id,0) = 0
							 And Company_ID = $cmp and FieldName='DiffNumber' ";
	        $query2 = $this->db->query($updPattern);

	        $nolnya = $length-strlen($wht_urut);
	        $wht_no = $doc_no.str_repeat("0",$nolnya).$urut;

	    ///*******************  insert ke ***************///
	        //===  WTR Header ==//
	       $sql=" INSERT into nfc_wtr_head
			(h_wtr_id, wtr_numb, whtreqnum, WHTReqDate, Status, ApprovalStatus, ItemCategoryType, TransferBy, RequestBy, 
			whtrequisitionid, SourceWH_ID, DestWH_ID, memo, ket_ret, driver, kontainer, nopol, noseal)
			select top 1 epoch,no_WTR_pick,'$wht_no',GETDATE(),2,0,'CAT','USER_NFC','USER_WTR',
			'111',$whs,$wh_bin,'FORM_MEMO','PB','SUPIR','KON','NOPOL','NOSEAL' from nfc_transaksi WHERE epoch='1568883518118'";
			$query = $this->db->query($sql);

			// == WTR Detail ==//
			//** 30 = bin **//
			$sql2="INSERT into nfc_wtr_det
			(d_wtr_id, WHTReqNum, Item_code, AvlQty, Qty, Approved_qty, LstBinQty)
			select epoch,no_WTR_pick,kode_barang,sum(qty),sum(qty),sum(qty), '$wh_bin|'+CAST(sum(qty) as VARCHAR(20)) 
			from nfc_transaksi WHERE epoch='1568883518118'
			group by epoch,no_WTR_pick,kode_barang;";
			$query2=$this->db->query($sql2);

			// == WHT Header sunfish ==//
			$sql3="INSERT INTO TAccWHTransfer_Header
			(WHTReqNum, WHTReqDate, RequestBy, Status, ApprovalStatus, SourceWH_ID, DestWH_ID,
			 isTaxAble, transferby, whtrequisitionid, currency_id, Desc_Doc, ItemCategoryType, IsScanned, whtrequisitionisfinal,
			   RR_No, isFromTransit, JO_No, isFromProductionOutput, Memo, ket_ret, driver, kontainer, nopol, noseal)
			select WHTReqNum, WHTReqDate, RequestBy, Status, ApprovalStatus, SourceWH_ID, DestWH_ID,
			 isTaxAble, transferby, whtrequisitionid, currency_id, Desc_Doc, ItemCategoryType, IsScanned, whtrequisitionisfinal,
			   RR_No, isFromTransit, JO_No, isFromProductionOutput, Memo, ket_ret, driver, kontainer, nopol, noseal
			from nfc_wtr_head where h_wtr_id='$epoch' ";
			$query3=$this->db->query($sql3);

			// == WHT Detail sunfish ==//
			$sql4="INSERT INTO TAccWHTransfer_Detail
						(WHTReqNum, Item_code, AvlQty, Qty, Approved_qty, Description, LstBinQty, Dimension_ID, isLabel, LabelOut, CostingMethod )
						select WHTReqNum, Item_code, AvlQty, Qty, Approved_qty, Description, LstBinQty, Dimension_ID, isLabel, LabelOut, CostingMethod 
						from nfc_wtr_det where d_wtr_id='$epoch' ";
			$query4=$this->db->query($sql4);

			
			//== insert ke BIN ==//
			$sql21="INSERT Into TAccSerialNumber_Bin
                              (	TrxId,Item_Code,Bin_Id,Qty,SerialNumber,CreatorId,
                                CreatorDateTime,CreatorIP,Item_Barcode,Dimension_ID,ADJ_TYPE,TYPE, Expired_Date
                              )SELECT whtreqnum,kode_barang,$wh_bin,sum(qty) qty,no_lot,$user_id,WHTReqDate,'10.1.250.254',
					no_lot,1,'-','WHT', a.exp_date from nfc_transaksi a,nfc_wtr_head b 
					WHERE a.epoch=h_wtr_id
					and epoch='$epoch'
					GROUP BY whtreqnum,kode_barang,no_lot,WHTReqDate";
			$query2=$this->db->query($sql21);

				

			//== Insert ke nfc_Item_hist ==//
			$sql23="INSERT Into dbo.nfc_item_hist (nih_trx,nih_date,nih_item,nih_lot,nih_expired,nih_qty,nih_trx_type,nih_wh,nih_bin,nih_flag,nih_uid_nfc,nih_nik)
				Select a.no_WTR_pick,a.kode_barang,a.no_lot,a.exp_date,a.qty,'-','$whs','$wh_bin',3,a.uid_picked,'$nik_user' from dbo.nfc_transaksi a where a.epoch='$epoch'";
			$query2=$this->db->query($sql23);

	        //insert data transaksi
	        $nputz=$this->input->post();
	        $trans = $this->db->insert('nfc_Item_hist',$inputz);

	        
	        $this->db->trans_complete();
	        //get status transaksi
	        $status=$this->db->trans_status();
	        
	       if($status){
	            $this->response(['Item created successfully.'], REST_Controller::HTTP_OK);
	        }else{}

    }


}