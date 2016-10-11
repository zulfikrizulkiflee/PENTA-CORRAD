<?php 

//cips.com/ws
define('TARGET_WEBSERVICE_URL','localhost/corrad/test_jsonrecv.php');
require_once('system_prerequisite.php');

function OCWebSvcJSONPost($myQuery,$qry,$url)
{
	$jsonData = json_encode($myQuery->query($qry,'SELECT','NAME'));
	
	$ch = curl_init($url);          	
	curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");                                                                     
	curl_setopt($ch,CURLOPT_POSTFIELDS,$jsonData);                                                                  
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);                                                                      
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json','Content-Length: ' .strlen($jsonData)));                                                                                                                    
	return $result = curl_exec($ch);
}

//data bayaran
//triggered upon receiving payment
function cipsJSONSend_receivePayment($myQuery,$id)
{
	$qry = "select
				a.no_account_process_id as no_transaksi,
				a.referencecode as jenis_bayaran,	
				a.reference_datetime as tarikh_transaksi,
				a.reference_no as no_resit,
				a.amaun as jumlah,
				b.collection_code as kod_akaun,
				a.check_no as no_cek,
				a.financial_institute_code as kod_bank
			from
				cips_user.dbo.cips_tra_account_process a,
				cips_user.dbo.cips_tra_account_ledger b
			where 
				a.account_process_id = b.account_process_id
				and a.no_account_process_id = ".$id."
				and a.account_process_code in ('030101','030102','030103','030104','030105')";
	
	//receive acknowledge, octransactionid, cipstransactionno
	$result = OCWebSvcJSONPost($myQuery,$qry,TARGET_WEBSERVICE_URL);
}

//data pengeluaran
//triggered upon user requesting for vouchers
function cipsJSONSend_requestVoucher($myQuery,$id)
{
	$qry = "select 
				a.account_process_id as no_transaksi,
				a.reference_datetime as tarikh_transaksi,
				a.reference_no as no_resit,
				a.amaun as jumlah,
				b.collection_code as kod_akaun
			from
				cips_user.dbo.cips_tra_account_process a,
				cips_user.dbo.cips_tra_account_ledger b
			where 
				a.account_process_id = b.account_process_id
				and a.account_process_code in ('030204','030304','030404','030504','030604')
				and a.no_account_process_id = ".$id.";";
	
	//receive acknowledge, octransactionid, cipstransactionno
	$result = OCWebSvcJSONPost($myQuery,$qry,TARGET_WEBSERVICE_URL);
}

//triggered upon user transaction
function cipsJSONSend_userJournal($myQuery,$id)
{
	$qry = "select
				a.account_process_id as no_transaksi,
				a.referencecode as jenis_bayaran,
				a.reference_datetime as tarikh_transaksi,
				a.reference_no as no_resit,
				a.amaun as jumlah,
				b.collection_code as kod_akaun
			from
				cips_user.dbo.cips_tra_account_process a,
				cips_user.dbo.cips_tra_account_ledger b
			where 
				a.account_process_id = b.account_process_id
				and a.account_process_code = '031001'
				and a.no_account_process_id = ".$id.";	";
	
	//receive acknowledge, octransactionid, cipstransactionno
	$result = OCWebSvcJSONPost($myQuery,$qry,TARGET_WEBSERVICE_URL);
}

$qry = 'select * from pruser';
OCWebSvcJSONPost($myQuery,$qry,TARGET_WEBSERVICE_URL);



?>
