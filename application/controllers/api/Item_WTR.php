<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
Class Item_WTR extends REST_Controller {


public function __construct() {
       parent::__construct();
       $this->load->database();
    }

//get information about latest expired date. Cek and check
public function index_get(){
        //ambil data yang punya item code tsb dan 
        //jika ada dibandingkan yang paling terkini
        // samakan uid_nfc, kalo sama => return true, slah return false & uid nya
        $no_wtr=$this->uri->segment('3');
       // $uid=$this->uri->segment('6');
    $sql_3=" 
                
                    SELECT * from (
                    SELECT b.no_WTR,c.kode_barang,c.nama_barang,c.qty,ISNULL((c.qty-trans.qty_trans),c.qty) as sisa_qty, (trans.qty_trans) as picked_qty
                    from nfc_wtr_header b
                    left join nfc_wtr_detail c on c.id_WTR_header=b.id
                    left join (
                    select a.no_WTR_pick,a.kode_barang, SUM(a.qty) qty_trans from nfc_transaksi a
                    GROUP by a.kode_barang,a.no_WTR_pick,a.kode_barang ) as trans on b.no_WTR=trans.no_WTR_pick and trans.kode_barang=c.kode_barang
                    where b.no_WTR='$no_wtr'
                    ) A 
                    where A.sisa_qty > 0
                    ";

    //============= Now Using This query ============================//                
    $sql_2="SELECT * from (     
            select z.WHTRequisitionNum as no_WTR, z.Item_code as kode_barang, y.Item_Name as nama_barang, CAST (Round(z.request_qty,2) as DECIMAL(12,2)) as qty, 
            ISNULL((CAST (Round(z.request_qty,2) as DECIMAL(12,2))-trans.qty_trans),z.request_qty) as sisa_qty, (trans.qty_trans) as picked_qty 
            from dbo.TAccWHTRequisition_Detail z
            left join tItem y on z.Item_code = y.Item_Code
            left join (
                        select a.no_WTR_pick,a.kode_barang, SUM(a.qty) qty_trans from nfc_transaksi a
                        GROUP by a.kode_barang,a.no_WTR_pick,a.kode_barang 
                 ) as trans on z.WHTRequisitionNum=trans.no_WTR_pick and trans.kode_barang=z.Item_code
            where z.WHTRequisitionNum ='$no_wtr'
            ) A 
            where A.sisa_qty > 0";

        $query2 = $this->db->query($sql_2);
        $data2=$query2->result_array();
        $this->response($data2, REST_Controller::HTTP_OK);

    } 
}
 ?> 