<?php
//todo - cips officecentral

$xml_builder = '
<?xml version="1.0" encoding="utf-8"?>
<Test>
<String>I like Bharath.co.uk!</String>
</Test>';

$service_url = 'localhost/corrad/test_xmlrecv.php';
$ch = curl_init($service_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_builder);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$ch_result = curl_exec($ch);
curl_close($ch);
echo $ch_result;



/*
//POST
$service_url = 'localhost/corrad/test_xmlrecv.php';
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS,'CountryCode=MY');
$curl_response = curl_exec($curl);
curl_close($curl);
echo $curl_response;
*/
/*
//GET - mcm tak jalan
$service_url = 'localhost/corrad/test_xmlrecv.php?id=12222';
$curl = curl_init($service_url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$curl_response = curl_exec($curl);
curl_close($curl);
echo $curl_response;
*/

?>
