<?php
//get sent data
$data = file_get_contents('php://input');
$data .= implode(',',$_GET);

//write to file
$file = 'received_oc_'.date('YmdHis').rand().'.xml';
$fh = fopen($file,'w') or die();
fwrite($fh,$data);
fclose($fh);

$returnValue = array('result'=>0,'message'=>'test ','octransactionno'=>'1111222');

echo json_encode($returnValue);

function cipsJSONReceive_updateJournal($myQuery,$qry)
{	
	//receive acknowledge, octransactionid, cipstransactionno
	$result = OCWebSvcJSONPost($myQuery,$qry,'cips.com/ws/issuePayment');
}


?>
