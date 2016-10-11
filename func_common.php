<?php
//function to validate user session
function validateUserSession($defaultUrl='index.php')
{
	$sessionFlag = true;

	//check session
	if(!$_SESSION['userID'])
	{
		$sessionFlag = false;
		redirect($defaultUrl);
	}//eof if

	return $sessionFlag;
}//eof function

//function to convert english day to malay
function dayToMalay($str)
{
	//uppercase month name
	$str = strtoupper($str);

	switch($str)
	{
		case 'MONDAY':
			return 'Isnin';
			break;

		case 'TUESDAY':
			return 'Selasa';
			break;

		case 'WEDNESDAY':
			return 'Rabu';
			break;

		case 'THURSDAY':
			return 'Khamis';
			break;

		case 'FRIDAY':
			return 'Jumaat';
			break;

		case 'SATURDAY':
			return 'Sabtu';
			break;

		case 'SUNDAY':
			return 'Ahad';
			break;

		default:
			//do nothing
	}//end switch
}

//function to convert english month to malay
//parameter: month name, type:short/long
function monthToMalay($str,$type)
{
	//uppercase month name
	$str = strtoupper($str);

	//if month name is short
	if($type == 'short')
	{
		switch($str)
		{
			case 'JAN':
				return 'Jan';
				break;

			case 'FEB':
				return 'Feb';
				break;

			case 'MAR':
				return 'Mac';
				break;

			case 'APR':
				return 'Apr';
				break;

			case 'MAY':
				return 'Mei';
				break;

			case 'JUN':
				return 'Jun';
				break;

			case 'JUL':
				return 'Jul';
				break;

			case 'AUG':
				return 'Ogo';
				break;

			case 'SEP':
				return 'Sep';
				break;

			case 'OCT':
				return 'Okt';
				break;

			case 'NOV':
				return 'Nov';
				break;

			case 'DEC':
				return 'Dis';
				break;

			default:
				//do nothing
		}//end switch
	}//end month name is short

	//if month name is long
	else if($type == 'long')
	{
		switch($str)
		{
			case 'JANUARY':
				return 'Januari';
				break;

			case 'FEBRUARY':
				return 'Februari';
				break;

			case 'MARCH':
				return 'Mac';
				break;

			case 'APRIL':
				return 'April';
				break;

			case 'MAY':
				return 'Mei';
				break;

			case 'JUNE':
				return 'Jun';
				break;

			case 'JULY':
				return 'Julai';
				break;

			case 'AUGUST':
				return 'Ogos';
				break;

			case 'SEPTEMBER':
				return 'September';
				break;

			case 'OCTOBER':
				return 'Oktober';
				break;

			case 'NOVEMBER':
				return 'November';
				break;

			case 'DECEMBER':
				return 'Disember';
				break;
		}//end switch
	}//end month name is long
}

//function to trim, strip html tags, php tags
function cleanData($str)
{
	//characters to remove
	$charToRemove = array("'",'/','"','\\');
	$charToReplace = array('','','','');

	//to replace
	$str = str_replace($charToRemove,$charToReplace,$str);

	//remove whitespace, php and html tags
	return trim(strip_tags($str));
}

//to refresh page
function refresh()
{
	die('<meta http-equiv="refresh" content="0">');
}

//to refresh page
function metaRedirect($time,$url)
{	//content="5;URL=http://www.indiana.edu/~smithclas/l200/
	die('<meta http-equiv="refresh" content="'.$time.';'.$url.'">');
}

//to redirect page
function redirect($redirectPath)
{
	if(OTHERS_URL_SECURITY) {
		$str = explode('?',$redirectPath);
		$redirectPath = $str[0] . "?a=" . flc_url_encode($str[1]);
	}

	//check http protocol
	if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
		$httpProtocol = 'https';
	else
		$httpProtocol = 'http';

	//path to be redirected	
	if(dirname($_SERVER['PHP_SELF']) == '/')
		$dirname = '';
	else
		$dirname = dirname($_SERVER['PHP_SELF']);
		
	$path = $httpProtocol.'://'.$_SERVER['HTTP_HOST'].$dirname.'/'.$redirectPath;
    echo '<script>window.location="'.$path.'"</script>';
}

//to add any number by 1 and repad according to original
function addAndRepad($num)
{
	$newNum = sprintf("%0d",$num) + 1;						//unpad number, add by 1
	$length = strlen($num);									//get length
	$newNum = sprintf("%0".$length."d",$newNum);			//repad number using length

	return $newNum;
}

//to limit text display
function limitText($text, $length)
{
	$textLength = strlen($text);

	//substr if exceed limit
	if($textLength > $length)
		$text = substr($text,0,250).'...';

	return $text;
}

//to substring a string by start and end char
function substrByChar($str, $startChar, $endChar='')
{
	$strLength = strlen($str);
	$startCharLength = strlen($startChar);
	$endPosition = 0;

	//check starting character
	if(strpos($str, $startChar) !== false)
	{
		$offset = strpos($str, $startChar);

		//get start position
		$startPosition = $offset + $startCharLength;

		//get end position (if have end character)
		if($endChar) $endPosition = strpos($str, $endChar, $offset);

		//if found ending character
		if($endPosition)
			$length = $endPosition-$startPosition;
		else
			$length = $strLength-$startPosition;

		return substr($str, $startPosition, $length);
	}//eof if
}//eof function

//to upload file (move from temp dir to permanent storage folder)
function upload_file($uploaddir,$uploadfilename,$allowedExtension='',$allowedSize='')
{
	$uploadError = '';

	//to list uploaded file
	if(is_string($uploadfilename['name']))
	{
		//uploaded file extension
		$uploadedFileExt = getExtension($uploadfilename['name']);
		$allowUpload = false;

		//filter extension
		if(!$allowedExtension || array_search($uploadedFileExt, $allowedExtension) !== false)
		{
			//default upload flag
			$allowUpload = true;

			//filter filesize
			if($allowedSize)
			{
				//uploaded file size in kb
				$uploadedFileSize = $uploadfilename['size'] / 1000;

				//file size bigger than max size
				if($uploadedFileSize > $allowedSize)
				{
					$allowUpload = false;
					$uploadError = $uploadfilename['name'].' tidak dimuatnaik kerana fail yang digunakan melebihi saiz yang dibenarkan!';
				}//eof if
			}//eof if
		}//eof if
		else
			$uploadError = $uploadfilename['name'].' tidak dimuatnaik kerana penggunaan jenis fail yang tidak dibenarkan!';

		//if allow to upload
		if($allowUpload)
		{
			$uploadfile = $uploaddir.date('YmdHis').rand(1,999).'.'.$uploadedFileExt;	//filename to be store
			move_uploaded_file($uploadfilename['tmp_name'], $uploadfile);				//move temp file from temp folder to upload folder
		}//eof if
		else
		{
			//if file not empty
			if($uploadfilename['name'])
				showNotificationError($uploadError);
		}//eof else
	}//eof if
	else if(is_array($uploadfilename['name']))
	{
		//count file upload
		$fileCount = count($uploadfilename['name']);

		//loop on file upload count
		for($x=0; $x < $fileCount; $x++)
		{
			//uploaded file extension
			$uploadedFileExt = getExtension($uploadfilename['name'][$x]);

			//default upload flag
			$allowUpload = false;

			//filter extension
			if(!$allowedExtension || array_search($uploadedFileExt, $allowedExtension) !== false)
			{
				$allowUpload = true;

				//filter filesize
				if($allowedSize)
				{
					//uploaded file size in kb
					$uploadedFileSize = $uploadfilename['size'][$x] / 1000;

					//file size bigger than max size
					if($uploadedFileSize > $allowedSize)
					{
						$allowUpload = false;
						$uploadError = $uploadfilename['name'][$x].' tidak dimuatnaik kerana saiz fail yang digunakan adalah tidak dibenarkan!';
					}//eof if
				}//eof if
			}//eof if
			else
				$uploadError = $uploadfilename['name'][$x].' tidak dimuatnaik kerana jenis fail yang digunakan adalah tidak dibenarkan!';

			//if allow to upload
			if($allowUpload)
			{
				$uploadfile[] = $uploaddir.date('YmdHis').rand(1,999).'.'.$uploadedFileExt;	//filename to be store
				move_uploaded_file($uploadfilename['tmp_name'][$x], $uploadfile[$x]);		//move temp file from temp folder to upload folder
			}//eof if
			else
			{
				$uploadfile[] = '';

				//if file not empty
				if($uploadfilename['name'][$x])
					showNotificationError($uploadError);
			}//eof else
		}//eof for
	}//eof else

	//return upload path
	return $uploadfile;
}

//get file extension
function getExtension($filename)
{
	$a = explode('.',$filename);
	return end($a);
}//eof function

//to escape the unsupported character
function fileEscapeCharacter($string)
{
	//replace unsupported character
	$string = str_replace(array('/','\\','*',':','?','\'','"','<','>','|'),'',$string);

	//replace space ( )
	$string = str_replace(' ','_',trim($string));

	return $string;
}//eof function

//to convert result set in vertical (DB) to horizontal set (array)
//assuming the first column in vertical dataset to be converted to column name in horizontal dataset
//required: no of rows, result set
function rowsToColumn($numRows,$rs)
{
	//clears and assign array
	$dumpArr = array();

	//by assuming the first column of result set to be the column name of to be returned new result set
	for($x=0; $x < $numRows; $x++)
	{
		$rsRows = mysql_fetch_array($rs);			//fetch result
		$dumpArr[$rsRows[0]] = $rsRows[1];			//create new result set
	}

	//return converted result
	return $dumpArr;
}

//to convert result set in vertical (ARRAY) to horizontal set (array)
//assuming the first column in vertical dataset to be converted to column name in horizontal dataset
//required: no of rows, result set
function rowsToColumnV2($arr)
{
	//clears and assign array
	$dumpArr = array();

	//count array length
	$arrLength = count($arr);

	//by assuming the first column of result set to be the column name of to be returned new result set
	for($x=0; $x < $arrLength; $x++)
	{
		//store in session array, key from col 0, value from col 1
		$dumpArr[$arr[$x][0]] = $arr[$x][1];
	}
	return $dumpArr;
}

//to evaluate source code stored in string
function getEval($var)
{
	if($var)
	{
		ob_start();
		eval("echo $var;");

		//eval("\$var = \$a; echo 'T'.$var;");
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}
}

//create gzip file
function createGzip($filename,$content)
{
	// open file for writing with maximum compression
	$handle = gzopen($filename, "w9");

	//if file handle ok
	if($handle)
		gzwrite($zp,$content);			// write string to file

	//close file
	gzclose($zp);
}

/**
TO ZIP FILE/FOLDER
$sourcePath - file/folder path
$targetpath - target file to be zipped
*/
function zip($sourcePath, $targetpath)
{
    if (!extension_loaded('zip') || !file_exists($sourcePath)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($targetpath, ZIPARCHIVE::CREATE)) {
        return false;
    }

    //$sourcePath = str_replace('\\', '/', realpath($sourcePath));

    if (is_dir($sourcePath) === true)
    {
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourcePath), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                continue;

            //$file = realpath($file);

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($sourcePath . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($sourcePath . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($sourcePath) === true)
    {
        $zip->addFromString(basename($sourcePath), file_get_contents($sourcePath));
    }

    return $zip->close();
}

/**
TO UNZIP/EXTRAXT AN UPLOADED 'ZIP' FILE
$sourcePath - file path
$targetpath - target path/location of file to be extracted
*/
function unzip($sourcePath, $targetpath)
{
	$zip = new ZipArchive;
	$openZip = $zip->open($sourcePath);

	//count file/folder
	$fileArchiveCount = $zip->numFiles;

	//loop on count of file/folder
	for($x=0; $x < $fileArchiveCount; $x++)
		$fileArchive[] = $zip->getNameIndex($x);

	//can open zip file
	if($openZip === TRUE)
	{
		$zip->extractTo($targetpath.'/');
		$zip->close();
	}//eof if

	//return array of extracted file/folder name
	return $fileArchive;
}//eof function

//copy folder from 1 destination to another (recursive)
function copyFile($src,$dst)
{
	$dir = opendir($src);
    @mkdir($dst, 0777);

	//loop on found file/folder
    while(($file=readdir($dir)) !== false)
	{
		//check valid file/folder
        if(($file != '.') && ($file != '..'))
		{
			//file/folder
            if(is_dir($src.'/'.$file))
                copyFile($src.'/'.$file, $dst.'/'.$file);
            else
                copy($src.'/'.$file, $dst.'/'.$file);
        }//eof if
    }//eof while

    closedir($dir);
}//eof function

//delete folder and it's content (recursive)
function removeFolder($dir)
{
	//loop on all files
    foreach(glob($dir . '/*') as $file)
	{
		//file/folder
        if(is_dir($file))
            removeFolder($file);
        else
            unlink($file);
    }//eof foreach

	//delete folder
    rmdir($dir);
}//eof function

//get file/folder size
function getPathSize($path)
{
	if(!file_exists($path)) return 0;
	if(is_file($path)) return filesize($path);

	$ret = 0;
	foreach(glob($path."/*") as $fn)
		$ret += getPathSize($fn);

	return $ret;
}//eof function

//calculate page generation time
function utime ()
{
	$time = explode(" ",microtime());
	$usec = (double)$time[0];
	$sec = (double)$time[1];
	return $sec + $usec;
}

function pageGenerationTime($start,$secsName)
{
	$end = utime();
	$run = $end - $start;
	return substr($run,0,5).$secsName;
}

function my_trimmer($beard,$type)
{
	//array of replacement chars
	$db_char = array("[R]","[P]","[BR]");
	$txt_char = array("\r","\n\n","\n");
	$txt_char2 = array(" ","<br><br>","<br>");

	if($type == 1)
		$beard = str_replace($txt_char, $db_char, $beard);			//convert textarea text to safe version for DB

	else if($type == 2)
		$beard = str_replace($db_char, $txt_char, $beard);			//DB text -> string (PHP SAFE)

	else if($type == 3)
		$beard = str_replace($db_char, $txt_char2, $beard);			//DB text -> string (ORIGINAL)

	else if($type == 4)
		$beard = str_replace($db_char, " ", $beard);				//DB text -> string (HTML SAFE)

	return $beard;
}

//function to re index array according to keys
function reIndexArray($arr)
{
	foreach ($arr as $key => $val)
	{
		$x++;
		$newArr[$x-1] = $val;
	}
	return $newArr;
}

//function to retreive central message by name
function flc_message($name, $timer='')
{
	include('db.php');

	//get message
	$getMessage = "select MESSAGE_ID, MESSAGE_TYPE, MESSAGE_TEXT from FLC_MESSAGE where MESSAGE_NAME = '".$name."'";
	$getMessageRs = $myQuery->query($getMessage, 'SELECT', 'NAME');

	//message type
	if($getMessageRs[0]['MESSAGE_TYPE'] == 'e')
		$msgType = 'Error';
	else
		$msgType = 'Info';

	//--START TRANSLATION
	//get the translation for message
	$tr8nStr_message = getElemTranslation($myQuery,$_SESSION['language'],7,$getMessageRs[0]['MESSAGE_ID'],'MESSAGE_TEXT');

	($tr8nStr_message[0]['TRANS_SOURCE_COLUMN'] == 'MESSAGE_TEXT') ? $msgText = $tr8nStr_message[0]['TRANS_TEXT'] : $msgText = $getMessageRs[0]['MESSAGE_TEXT'];
	//--END TRANSLATION

	//show notification
	showNotification($msgType, $msgText, $timer);
}//eof function

//function to show notification (Info)
function showNotificationInfo($message, $timer='',  $style='')
{
	showNotification('Info', $message, $timer, $style);
}//eof for

//function to show notification (Error)
function showNotificationError($message, $timer='', $style='')
{
	showNotification('Error', $message, $timer, $style);
}//eof for

//function to show notification
function showNotification($type, $message, $timer='', $style='')
{
	//display duration
	if($timer=='')
		$timer = NOTIFICATION_DURATION;

	//set default event type
	if(!$type)
		$type = 'Info';
	else
		$type = ucfirst(strtolower($type));

	//random for id
	$randId = rand(1,999);
	?>

    <div id="notification_<?php echo $randId;?>" class="notification" style="<?php echo $style; ?>" onclick="closeNotification('notification_<?php echo $randId;?>');">
      <div class="notification<?php echo $type;?>">
        <img class="notificationClose" onclick="closeNotification('notification_<?php echo $randId;?>');" />
        <table border="0" cellspacing="0" cellpadding="0" style="width:100%">
          <tr>
            <td class="notificationIcon<?php echo $type;?>"></td>
            <td><?php echo $message;?></td>
          </tr>
        </table>
      </div>
    </div>

    <?php if($timer != ''){?>
    <script>setTimeout("closeNotification('notification_<?php echo $randId;?>');",<?php echo $timer*1000;?>);</script>
    <?php }
}//eof function

//--------------------------------------------------------------------------------------------
/**
TO CREATE/RETURN OPTION TAG FOR DROP DOWN BUTTON
-$arrayList: array of values to be inserted into dropdown
-$post: post value (default is null)
*/
function createDropDown($arrayList, $selectedValue='', $init='')
{
	$arrayListCount=count($arrayList);

	//if have data
	if($arrayListCount)
	{
		//array keys
		$arrayListKeys = array_keys($arrayList[0]);

		//option tag: loop on count of row
		for($x=0; $x < $arrayListCount; $x++)
		{
			$optValue = $arrayList[$x][$arrayListKeys[0]];

			//if have 2nd index in array
			if(isset($arrayList[$x][$arrayListKeys[1]]))
				$optTitle = $arrayList[$x][$arrayListKeys[1]];
			else
				$optTitle = $arrayList[$x][$arrayListKeys[0]];

			//create the option tag
			$option .= '<option value="'.$optValue.'"';

			if($selectedValue==$optValue)
				$option .= ' selected="selected"';

			$option .= '>'.$optTitle.'</option>';
		}//eof for
	}//eof if

	//initial value
	$initOption = '<option value="">'.$init.'</option>';

	//return value, add initial option (value)
	$returnValue = $initOption.$option;

	return $returnValue;
}//eof function

/**
TO CHECK IF VARIABLE(S) NULL OR NOT
-var args: can have as many variable passed
*/
function havValue()
{
	$result=false;	//initial value is false

    $varTotal = func_num_args();	//get number of variable passed
    $valList = func_get_args();		//get list of variable passed

    for ($x = 0; $x < $varTotal; $x++)
        if($valList[$x])
			$result=true;	//if 1 variable have value return true

	return $result;
}//eof function

/**
TO CONVERT A NUMBER TO CURRENCY
-NUMBER: number to be converted
*/
function to_currency($number)
{
	return number_format($number, 2, '.', ',');
}//eof function

/**
TO CONVERT A NUMBER TO ACCOUNT STYLE
-NUMBER: number to be converted
*/
function to_account($str)
{
	if(strpos($str,'-') !== false)
	{
		$str='('.str_replace('-','',$str).')';
	}//eof if

	return $str;
}//eof function

// Function for encode an decode the URL 22th Mac 2011 @ 1.22AM by Rosli Amir
function flc_url_encode($str)
{
	// step 1 : reverse the string
	$str_reverse = "";
	for ( $i=strlen($str); $i>-1 ; $i-- ) {
		$str_reverse = $str_reverse . substr($str,$i,1) ;
	}

	// step 2 : encode the reverse the string
	$str_reverse_encode =   base64_encode($str_reverse);

	// step 3 : reverse the encoded reverse string
	$str_reverse_encode_reverse2 = "";
	for ( $i=strlen($str_reverse_encode); $i>-1 ; $i-- ) {
		$str_reverse_encode_reverse2 = $str_reverse_encode_reverse2 . substr($str_reverse_encode,$i,1) ;
	}

	// step 4 : Again encoded the reversing of "encoded reverse string"
	$str_reverse_encode_reverse2_encode2 =   base64_encode($str_reverse_encode_reverse2);

	// step 5 : getting the front random number
	$random_number_front = substr(rand(),1,3);
	$random_number_front_encode = base64_encode($random_number_front);

	// step 6 : getting the end random number
	$random_number_end = substr(rand(),1,3);
	$random_number_end_encode = base64_encode($random_number_end);
	$random_number_end_encode_no = base64_encode(strlen($random_number_end_encode));

	// Final step : combine the encoded url
	$final_encode_url = $random_number_front_encode . $str_reverse_encode_reverse2_encode2 . $random_number_end_encode;
	$split_str = substr($encoded_str,4, strlen($encoded_str) - 4);

	//	echo $final_encode_url;
	return $final_encode_url;
}

function flc_url_decode($str)
{

	// Step 1 : split the encoded url
	$split_str = substr($str,4, strlen($encoded_str) - 4);

	// step 2 : decoded the splitted string
	$decoded_split_str = base64_decode($split_str);

	// step 3 : reversed back the decoded splitted str
	$decoded_split_str_reverse_back = "";
	for ( $i=strlen($decoded_split_str); $i>-1 ; $i-- ) {
		$decoded_split_str_reverse_back = $decoded_split_str_reverse_back . substr($decoded_split_str,$i,1) ;
	}

	// step 4 : decode again the decoded splitted string reverse back
	$decoded2_decoded_split_str_reverse_back = base64_decode($decoded_split_str_reverse_back);

	// Final step : reverse the string
	$Final_str = "";
	for ( $i=strlen($decoded2_decoded_split_str_reverse_back); $i>-1 ; $i-- ) {
		$Final_str = $Final_str . substr($decoded2_decoded_split_str_reverse_back,$i,1) ;
	}
	return $Final_str;
}

// Function for convert string contain _GET and put in the _GET array on 22th Mac 2011 @ 10.12AM by Rosli Amir
function stringTo_GET($str)
{
   $str_arr = explode("&",$str);
   for ($i=0; $i<count($str_arr); $i++)  {
		$str_arr_split = explode("=", $str_arr[$i] );
		$_GET[$str_arr_split[0]] = $str_arr_split[1] ;
	}
}

// This function is use to encoding the url
function url_encoding($text_to_url,$url_command,$url_equal)
{
// $text_to_url is the url to encode
// $url_command, currently use for <a href , window.location and redirect, but however can be use for others but yet to test
// #url_equal is either, = or (
	$url_command_len =  strlen($url_command) + 1;
	$js_fond_loc = strpos(strtolower($text_to_url),strtolower($url_command)) ;
	if ($js_fond_loc == "") return $text_to_url;
	else
	{
		$controljs_len =  strlen($text_to_url);
		$newcontroljs = $text_to_url;
		$replacecontroljs = "";
		$js_status=true;
		do {
			$js_fond_loc = strpos(strtolower($newcontroljs),$url_command) ;
			if ($js_fond_loc == "") break;
			else {
				$js_equal_loc   = strpos(substr($newcontroljs,$js_fond_loc+ $url_command_len),$url_equal);
				$beforecontroljs = $beforecontroljs . substr($newcontroljs,0,$js_fond_loc + $url_command_len +  $js_equal_loc + 1);
				$newcontroljs = ltrim(substr(substr($newcontroljs,$js_fond_loc + $url_command_len ), $js_equal_loc + 1 ));
				if (  substr($newcontroljs,0,1) == "'" ) $js_quote = "'"; else $js_quote = "\"";
				$newcontroljs = substr($newcontroljs,1) ;
				$last_qoute = strpos( $newcontroljs ,$js_quote);
				$js_url = trim(substr($newcontroljs,0,$last_qoute)," ");
				$js_url_file = substr($js_url,0,strpos($js_url,"?"));
				$js_url_item = substr($js_url,strpos($js_url,"?")+1);

//				$clean_js_url = $js_url_file . "?" .$js_url_item ;
				$encode_js_url = $js_url_file. "?a=" . flc_url_encode($js_url_item) ;
				$beforecontroljs = $beforecontroljs . $js_quote . $encode_js_url . $js_quote;
				$newcontroljs = substr($newcontroljs,$last_qoute+1);
			}
		} while ($js_status);

		$js_equal_loc   = strpos(substr($newcontroljs,$js_fond_loc + $url_command_len),"=");
	//	echo $beforecontroljs = $beforecontroljs . substr($newcontroljs,0,$js_fond_loc + 16 +  $js_equal_loc + 1);
		$beforecontroljs = $beforecontroljs . $newcontroljs;

		return $beforecontroljs;
	}
}

function href_encoding($str)
{
	if(strpos($str,'<a href="') !== false && strpos($str,'<a href="index.php?a=') === false)
	{
		$charStart = '<a href="';
		$charStartLength = strlen($charStart);
		$charEnd = '">';

		//position of start char
		$offset = strpos($str, $charStart);

		//loop on position of start char exist
		do{
			$position_1[] = $offset;
			$position_2[] = strpos($str, $charEnd, $offset + $charStartLength);
		}while($offset = strpos($str, $charStart, $offset + $charStartLength));

		$pos1_cnt = count($position_1);

		//loop on count of position
		for($x=0; $x < $pos1_cnt; $x++)
		{
			//get item id sub string
			$href[$x] = substr($str,$position_1[$x]+$charStartLength,$position_2[$x]-$position_1[$x]-$charStartLength);
		}//eof for

		$hrefCnt = count($href);

		//loop on count of itemId
		for($x=0; $x < $hrefCnt; $x++)
		{
			//if using index.php
			if(strtolower(substr($href[$x],0,10)) == 'index.php?')
			{
				//echo $href[$x].'<BR>';

				$stringSearch = '<a href="'.$href[$x].'">';
				$stringReplace = '<a href="'.'index.php?a='.flc_url_encode(substr($href[$x],10)).'">';

				$str = str_replace($stringSearch, $stringReplace, $str);
			}//eof if
		}//eof for
	}//eof if

	return $str;
}

//verify connection to given url (check server down,timeout,etc)
function verifyUrlConnection($url)
{
	//use curl to check connection
    $handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_NOBODY, true);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

    //get the HTML or whatever is linked in $url
    $response = curl_exec($handle);

	//echo curl_error($handle);

    //get http code from response
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    //if the document has loaded successfully without any redirection or error
    if($httpCode >= 200 && $httpCode < 400)
        return true;
    else
        return false;
}//eof function


//build menu list
function build_menu_list($menu_array,$level=1,$open=false,$myQuery)
{
	//kalau bukan topmenu
	if($_SESSION['LAYOUT'] != '3')
	{
		$styleString = ( $open ? 'display:block' : 'display:none');
		$ulclass = "sideMenuList";
	}
	else
		$ulclass = "topMenuList";

	//apply style
	echo '<ul class="'.$ulclass.' menuLevel'.$level.'" style="'.$styleString.'">';

	//setiap menu mengikut SESSION
	foreach((array)$menu_array as $m)
	{
		//hanya yang visible
		if($m['MENUSTATUS'] == '1')
		{
			//tentukan link
			if(is_array($m['MENUCHILD'])) {
			  $link = 'href="javascript:void(0)" ';
			  $link .= "onclick=\"ajaxToggleMenu(this)\" ";
			  $class = "hasChild isOpened";

			  //icon : hanya untuk level 1
			  if($level == 1)
			  {
				$icon_div = ' <div class="menuIcons" id="menu_icon_'.$m['MENUID'].'" ></div> ';
			  }

			} else {
				//convert POST,GET,SESSION variable
				$m['MENULINK'] = convertDBSafeToQuery($m['MENULINK']);

				$menuLink = $m['MENULINK'].'&menuID='.$m['MENUID'];

			  //if menu security enabled
			  if(MENU_URL_SECURITY)
			  {
				if (strtolower(substr($menuLink,0,10)) == "index.php?")
					$menuLink = 'index.php?a='.flc_url_encode(substr($menuLink,10));
			  }//eof if

			  $link = 'href="'.$menuLink .'" ';
			  $class = "noChild";
			}


			//if($m['MENULEVEL'] == $level)
			//{
				//get translation
				if(FLC_LANGUAGE)
					$mtitle = getElemTranslation($myQuery,$_SESSION['language'],2,$m['MENUID'],'MENUTITLE');
				
				//echo 'run!!';
				if($mtitle[0]['TRANS_SOURCE_COLUMN'] == 'MENUTITLE')
					$m['MENUTITLE'] = $mtitle[0]['TRANS_TEXT'];

				echo
				'<li id="list_menu_id_'.$m['MENUID'].'" style="'.(($_SESSION['LAYOUT']=='3' && $level==1) ? 'visibility:hidden;' : '').'">'
				//'<li>'
				.$icon_div;

				if($m['MENUICON'] != '')
					echo '<img src="'.$m['MENUICON'].'" style="position:relative;padding-top:10px;" />';

				//add new class for current active menu
				$active_menu = $_GET['menuID'];
				if($active_menu)
					if($m['MENUID'] == $active_menu) $class .= " menuActive";
				
				if($m['MENUHINTS'] != '' && $m['MENUHINTS'] != null)
					$showHints = true;
				else
					$showHints = false;
				
				echo '<a ';
				
				if($showHints === true)
					echo 'title="'.$m['MENUHINTS'].'" ';
				
				echo 'class="'.$class.'" id="menu_id_'.$m['MENUID'].'" '.$link.' target="'.$m['MENUTARGET'].'">'.$m['MENUTITLE'].'</a>';
				
				if(is_array($m['MENUCHILD']))
				{
					$is_open = ($_SESSION['toggleState'][$m['MENUID']] == 1 ? true : false);
					build_menu_list((array)$m['MENUCHILD'],$level+1,$is_open,$myQuery);
				}
				echo '</li>';
			//}
		}
	}

	echo '</ul>';
  
	//menu default state
	$menuWithOpenState = $myQuery->query("select MENUID from FLC_MENU where MENUSTATE = 1",'SELECT','NAME');

	for($x=0; $x < count($menuWithOpenState); $x++)
		$_SESSION['toggleState'][$menuWithOpenState[$x]['MENUID']] = 1;
  
}//eof function

//to resize image resolution (given the file name with path,max width and max height)
function resizeImageResolution($imageFile,$maxWidth,$maxHeight)
{
	//if image exist
	if(is_file($imageFile))
	{
		//size of original image
		$percentage = 1;
		$imgSize=getimagesize($imageFile);
		$originalWidth=$imgSize[0];		//array [0] - width
		$originalHeight=$imgSize[1];	//array [1] - height

		//if image width bigger than max width
		if($originalWidth>$maxWidth)
		{
			$percentage = $maxWidth/$originalWidth;
		}//eof if

		//if image height bigger than max height
		if($originalHeight*$percentage>$maxHeight)
		{
			$percentage = $maxHeight/$originalHeight;
		}//eof if

		//new size by percentage
		$newSize[0] = floor($originalWidth*$percentage);
		$newSize[1] = floor($originalHeight*$percentage);
	}//eof if

	return $newSize;
}//eof function

//author:luqman
//generate pdf
function flc_pdf($html,$papersize='A4',$orientation='P',$filename='file.pdf',$destination='I',$headerhtml=null,$footerhtml=null)
{
	$_SESSION['tcpdf_params']['html'] = $html;
	$_SESSION['tcpdf_params']['papersize'] = $papersize;
	$_SESSION['tcpdf_params']['orientation'] = $orientation;
	$_SESSION['tcpdf_params']['filename'] = $filename;
	$_SESSION['tcpdf_params']['destination'] = $destination;
	$_SESSION['tcpdf_params']['headerhtml'] = $headerhtml;
	$_SESSION['tcpdf_params']['footerhtml'] = $footerhtml;

	echo "<script>window.open('system_pdf_writer.php')</script>";
}//eof function

//http://stackoverflow.com/questions/19907155/how-to-replace-a-nth-occurrence-in-a-string
function str_replace_nth($search, $replace, $subject, $nth)
{
    $found = preg_match_all('/'.preg_quote($search).'/', $subject, $matches, PREG_OFFSET_CAPTURE);
    if (false !== $found && $found > $nth) {
        return substr_replace($subject, $replace, $matches[0][$nth][1], strlen($search));
    }
    return $subject;
}

function convertQryToPost($rs,$group)
{
	$postData = array();
	
	foreach($rs as $key => $val)
	{
		foreach($val as $key => $val)
			$_POST[$group.'-'.$key] = $val;
	}
}

function getListId($name,$select="")
{
	$str = "connection.xml";
	$dom = new DOMDocument();
	$dom->load($str);	

	$database = $dom->getElementsByTagName('database');
	//create select dropdown
	$selectBox .= "<select id='$name' name='$name'>";
	//$selectBox .= "<option value=''> -- select database -- </option>";
	
	foreach ( $database as $db )  
	{
	$id = $db->getAttribute('id');
	$engine = $db->getAttribute('engine');
		
	if($engine == 'yes')
		$idVal='';
	else
		$idVal=$id;
	
	if($select === $idVal){	
		$selected = "selected";
	}else{
		$selected = "";
	}
	
	$dbms_name = $db->getElementsByTagName("dbms_name")->item(0)->nodeValue; 
		
	$selectBox .= "<option value='$idVal' $selected> $id </option>";
	}
	$selectBox .= "</select>";
	
	return $selectBox;

}

?>
