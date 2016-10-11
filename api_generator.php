<?php
include('system_prerequisite.php');

if($_GET['api_url'] != '' || $_GET['api_url'] != null)
	$condtition = "API_URL = '".$_GET['api_url']."'";		
else
	$condtition = "API_NAME = '".$_GET['api_name']."'";	

//get api info
$api = "select API_ID, API_NAME, API_DESC, API_URL, API_RETURN_FORMAT, API_BL, API_STATUS 
		from FLC_API 
		where ".$condtition;
$apiRs = $myQuery->query($api,'SELECT','NAME');

//if api exist
if($apiRs)
{
	//get api domain/key restriction
	$getPerm = "select API_PERM_KEY,API_PERM_DOMAIN 
				from FLC_API_PERMISSION where API_ID = ".$apiRs[0]['API_ID'];
	$getPermRs = $myQuery->query($getPerm,'SELECT','NAME');
	$getPermRsCount = count($getPermRs);
		
	//if key assigned
	if($getPermRsCount)
	{		
		//get header info
		$headers = apache_request_headers();		
		
		//loop on count of permission
		for($x=0; $x < $getPermRsCount; $x++)
		{
			//check permission
			$key = $getPermRs[$x]['API_PERM_KEY'];
			$domainName = $getPermRs[$x]['API_PERM_DOMAIN'];			
			$domain = gethostbyname($domainName);			
			//echo $headers['OPEN-API-Key'];
			if($domain  && $key)
			{
				//if both domain $ key was use
				if($headers['OPEN-API-Key'] == $key) 
				{
					$allowedTempKey = true;
				}//eof if
				
				if($_SERVER['REMOTE_ADDR']== $domain)
				{
					$allowedTempDomain = true;
				}//eof if
				
				if($allowedTempKey && $allowedTempDomain)
					$allowed = true;
				break;
			
			}
			else if(!$domain && $key)
			{
				//if key was use
				if($headers['OPEN-API-Key'] == $key)
				{
					$allowed = true;
				}			
			}
			else if ($domain && !$key)
			{ 
				//if domain was use
				if($_SERVER['REMOTE_ADDR']== $domain) 
				{
					$allowed = true;
				}//eof if
			}
			else
			{
				//if both domain $ key was not use
				$allowed = true;
			}
		}//eof for
	}//eof if
	else
		$allowed = true;
	
	//execute if allowed
	if($allowed)
	{
		//if correct api
		if(count($apiRs))
		{
			//if have BL
			if($apiRs[0]['API_BL'])
			{
				//create global BL, and execute api's BL
				createPhpBl('');
				$result = executeBL($apiRs[0]['API_BL']);				
				
				if(is_array($result))
				//check array is index or associative
				$isIndexed = array_values($result) === $result;				
				
				if($isIndexed)
				{
					//if array is index
					// reformat result to valid structure (associative)
					$newStruct = reformatStructureArr($result);
				}
				else
				{
					//if arry is already associative just assign it to new array var
					$newStruct = $result;
				}
				
			}//eof if
		}//eof if

		//return type
		switch ($apiRs[0]['API_RETURN_FORMAT'])
		{
			case 'JSON':
				header('Content-Type: application/json; charset=utf-8');
				$output = json_encode($result);
				break;

			case 'XML':
				 include('class/Array2XML.php');
				 $xml = Array2XML::createXML('root', $newStruct);
				 header("Content-type: text/xml; charset=utf-8");
				 $output = $xml->saveXML();

				 break;
			
			default:
				$output = $result;
				break;
		}

		echo $output; 
	}//eof if
}//eof if
?>


<?php
/*
HTTP/1.1 401 Unauthorized
Content-Type: text/json
{
    "error" : {
        "code" : 401
        "message" : "The credentials provided are invalid."
        "more_info_url" : "http://example.com/help/errors/401"
    }
}
*/
?>
