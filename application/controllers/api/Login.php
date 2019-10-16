<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class Login extends REST_Controller {
	public function __construct() {
       parent::__construct();
       $this->load->database();
    }
public function index_get(){}
    public function index_post(){
    				//$username = 'UMS17110197';
					//$password = 'Kokola2019';
    				$username=$this->input->post("username");
    				$password=$this->input->post("password");
    				//$username="UMS17110197";
    				//$password = 'Kokola2019';

					$today  = date("Ymd");
					$today2 = date("Y-m-d");

					$ldap_con = @ldap_connect("kokola.local");
					ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
					ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);
					define(LDAP_OPT_DIAGNOSTIC_MESSAGE, 0x0032);

					$status_AD="";
					$ldap_user = $username."@kokola.local";
					$ldap_password = $password;

					if($ldap_con){
						$ldapbind = @ldap_bind($ldap_con, $ldap_user, $ldap_password);
				if(!$ldapbind)
				{
					if (ldap_get_option($ldap_con, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) 
					{
						if (strpos($extended_error,"data 531")> 0) 
						{
							//echo "Authenticated1";
							$status_AD=1;
							//$status_AD=0;
							// $sql = "SELECT id, username, nama FROM nfc_users WHERE username = '$username' ";
							//  $query2 = $this->db->query($sql);
		     //    			 $data2=$query2->result_array();
							// $this->response($data2, REST_Controller::HTTP_OK);	
							
						}
						else if(strpos($extended_error,"data 52e")>0)
						{
							 //$status_AD=1;
							$status_AD=0;
							
						}
						else if(strpos($extended_error,"data 525")>0)
						{
							$status_AD=0;
							$this->response($data2, REST_Controller::HTTP_OK);	
						}
						else if(strpos($extended_error,"data 532")>0)
						{
							$status_AD=0;
						}
						else if(strpos($extended_error,"data 49")>0)
						{
							$status_AD==0;
						}
						else if(strpos($extended_error,"data 533")>0)
						{
							$status_AD=0;
						}
						else 
						{
							$status_AD=0;
						}
					}//end if ldap get option
					else
					{
						$status_AD=0;
					}//end else ldap get option
			}//end if !ldapbind
			else
			{
				//echo "Authenticated2";
				$status_AD=1;
				
			}
		}else{
			$status_AD=0;
	}
				// $sql = "SELECT id, username, nama FROM nfc_users WHERE username = '$username' AND password = '$password' ";
				// 			 $query2 = $this->db->query($sql);
		  //       			 $data2=$query2->result_array();
				// 			$this->response($data2, REST_Controller::HTTP_OK);	
	if($status_AD==1){
		$sql = "SELECT a.User_ID as id, a.Emp_ID as username, a.First_name+' '+a.Middle_Name+' '+a.Last_Name as nama
				from dbo.THRMEmpPersonalData a
				where a.Emp_ID='$username' ";
		$sql2="SELECT DISTINCT TOP 1 a.Emp_ID as username,a.User_ID as id,a.First_name+' '+a.Middle_Name+' '+a.Last_Name as nama,d.WH_ID,e.wh_name , b.Company_Name as company, a.Company_ID as company_id
				from THRMEmpPersonalData a
				left join dbo.THRMCompany b on a.Company_ID=b.Company_ID
				left join TAppGroupUser c on a.User_ID=c.User_ID
				left join TappGroupData d on c.AppGroup_ID=d.AppGroup_ID
				INNER JOIN TAccWHLocation e on d.WH_ID=e.wh_id
				where a.Emp_ID='$username' and d.WH_ID in (42,25,43,40,37,45,28,33,35,65) ";
		$query2 = $this->db->query($sql2);
		 $data2=$query2->result_array();
		$this->response($data2, REST_Controller::HTTP_OK);	
	}else{

			$this->response([], REST_Controller::HTTP_OK);	
	 }
					
    }
}
?>