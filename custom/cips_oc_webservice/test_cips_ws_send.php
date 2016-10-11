<?php 
//include files
require_once('../../system_prerequisite.php');

//cips.com/ws
define('OC_URL','localhost/corrad/custom/cips_oc_webservice');
define('OCWS_ISSUEPAYMENT',		OC_URL.'/test_cips_ws_recv.php/');
define('OCWS_RECEIVEPAYMENT',	OC_URL.'/test_cips_ws_recv.php/');
define('OCWS_UDPATEJOURNAL',	OC_URL.'/test_cips_ws_recv.php/');

function writeToLog($type,$response)
{
	$folder = 'log/';
	
	if(in_array($type,array('issuePayment','receivePayment','updateJournal')))
	{
		$file = $folder.$type.'.log';
	
		$fh = fopen($file,'a') or die();
		fwrite($fh,date('Y-m-d H:i:s A : '));
		
		//success
		if((int)$response->result == 0)
			$resultStr = '[SUCCESS]';
		else if((int)$response->result == 1)
			$resultStr = '[ERROR]';
		
		//write to log
		fwrite($fh,$resultStr.' : ');
		fwrite($fh,$response->message.' - ');
		fwrite($fh,$response->octransactionno);
		fwrite($fh,"\r\n");
		fclose($fh);
	}
}

function generateSecretKey($str)
{
	return md5($str);
}

function OCWebSvcPost($type,$myQuery,$qry,$url)
{
	$qryRs = $myQuery->query($qry,'SELECT','NAME');						//run query
	$secretKey = generateSecretKey(date('YmdHis'));						//generate secret key	
	$url .= '?secretkey='.$secretKey;
	
	//generate url
	foreach($qryRs as $key => $value)
		foreach($value as $key => $value)
			$url .= '&'.$key.'='.$value;
	
	//open connection via curl
	$ch = curl_init($url);          	
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);                                                                      
	
	$result = json_decode(curl_exec($ch));								//return value in JSON
	writeToLog($type,$result);											//store response in log
}



//----------------------------
//CIPS HANTAR KE OFFICECENTRAL
//----------------------------

cikkim ttg debit_code n credit_code
dekat dlm tra_account_ledger ada tambahan column nama
transaction_code
1 = debit
2  = kredit



//issue payment
select
	a.account_process_id as externaltransactionno,
	a.reference_code as transactionType,			
	a.reference_datetime as transactionDate,
	a.reference_no as receiptNo,
	a.amaun as total,
	'' as debitCode,
	'' as creditCode
from
	cips_user.dbo.cips_tra_account_process a,
	cips_user.dbo.cips_tra_account_ledger b
where
	a.account_process_id = b.account_process_id
	and a.account_process_id = xxxx

//receive payment
select
	a.account_process_id as transactionno,
	a.reference_code as transactionType,			
	a.reference_datetime as transactionDate,
	a.reference_no as receiptNo,
	a.amaun as total,
	'' as debitCode,
	'' as creditCode,
	a.userid as StaffNo
from
	cips_user.dbo.cips_tra_account_process a
where
	a.account_process_id = xxxx

//update journal
select
	a.account_process_id as transactionno,
	a.reference_code as transactionType,			
	a.reference_datetime as transactionDate,
	a.reference_no as receiptNo,
	a.amaun as total,
	'' as debitCode,
	'' as creditCode
from
	cips_user.dbo.cips_tra_account_process a
where
	a.account_process_id = xxxx


//----------------------------
//OFFICECENTRAL HANTAR KE CIPS
//----------------------------

//update journal
//--------------
insert into cips_user.dbo.cips_tra_account_process
	(account_process_id,reference_code,reference_datetime,reference_no,amaun,debit_code?,credit_code?) 
values ();

//response
{
result: 0,
message: ‘’
}



//update voucher
//--------------
secretkey={secretkey}&transactionno=
{transactionno}&paymentvoucherno={paymentvoucherno }
		

{
result: 0,
message:

//update staff
//------------
&staffno= {staffno}&name= {name}&ic={ ic }&birthdate=
{birthdate}&address={address}&postcode={postcode}&work={work}&sex={sex}&phone={phone}&offi
cephone= {officephone }&email= {email}

{
result: 0,
message: ‘’
}

//bounce cheque
//-------------
transactionno= {transactionno}&chequeno={ chequeno }

{
result: 0,
message: ‘’
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
	$result = OCWebSvcPost($myQuery,$qry,TARGET_WEBSERVICE_URL);
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
	$result = OCWebSvcPost($myQuery,$qry,TARGET_WEBSERVICE_URL);
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
	$result = OCWebSvcPost($myQuery,$qry,TARGET_WEBSERVICE_URL);
}


$qry = 'select userid,username,userpassword,name from pruser where userid=1';
OCWebSvcPost('issuePayment',$myQuery,$qry,OCWS_ISSUEPAYMENT);
OCWebSvcPost('receivePayment',$myQuery,$qry,OCWS_RECEIVEPAYMENT);
OCWebSvcPost('updateJournal',$myQuery,$qry,OCWS_UDPATEJOURNAL);





?>
