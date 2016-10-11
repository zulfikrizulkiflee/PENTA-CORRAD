<?php
//get json data
$jsonData = file_get_contents('php://input');

//write to file
$json_file = 'received_json_'.date('YmdHis').rand().'.xml';
$fh = fopen($json_file,'w') or die();
fwrite($fh, $jsonData);
fclose($fh);

function cipsJSONReceive_updateJournal($myQuery,$qry)
{	
	//receive acknowledge, octransactionid, cipstransactionno
	$result = OCWebSvcJSONPost($myQuery,$qry,'cips.com/ws/issuePayment');
}


?>
