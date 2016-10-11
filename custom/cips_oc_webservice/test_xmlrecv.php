<?php  
//todo - cips officecentral
$xml_post = file_get_contents('php://input');

if($xml_post) 
{
	$xml_file = 'received_xml_'.date('YmdHis').rand().'.xml';
	$fh = fopen($xml_file,'w') or die();
	fwrite($fh, $xml_post);
	fclose($fh);
	return;
}
?>
