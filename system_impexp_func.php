<?php
//========= LIST OF FUNCTIONS ==================================================================
//get menu id posted
function getMenuID($postData)
{
	//insert into flc_permission table
	foreach ($_POST as $key => $value)
	{
		//if POST name contains menuPermission
		if(preg_match("/moduleSelection/i",$key))
		{
			$x = $x + 1;

			if($x == 1)
				$postArr[] = $_POST['hiddenCode'];

			//replace the 'menuPermission' string to empty string
			$key = trim(str_replace('moduleSelection_','',$key));

			//explode string to 2 parts, parent and sub
			$keyArr = explode('_',$key);

			$theKey = $keyArr[count($keyArr)-1];
			$postArr[] = $theKey;
		}
	}

	return $postArr;
}

//function to check selected checkbox
function checkSelectedCheckbox($val,$haystack)
{
	//if in array, echo checked
	if(is_array($haystack) && in_array($val,$haystack))
		echo ' checked ';
}

//function to count selected items
function countSelected($parent)
{
	$theCount = count($_SESSION['system_impexp'][$parent]);

	if($theCount == 0)
		return 0;
	else
		return $theCount - 1;
}

//function to reset export selection
function resetExportSelection()
{
	unset($_SESSION['system_impexp']);
	unset($_SESSION['system_impexp_full']);
}

//pad values with quote
function padWithQuote($dataTypeArr,$valuesArr,$mode='',$tableKeyIndex=array(),$columnName=array(),$specialMode='',$specialModeKey=array(),$exportPrefix='')
{
	//reset values arr
	$values = array();

	for($y=0; $y < count($dataTypeArr); $y++)
	{
		if(strtolower(DBMS_NAME) == 'informix')
		{
			if($columnName[$y] == 'BLDETAIL' || $columnName[$y] == 'COMPONENTTYPEQUERY' || $columnName[$y] == 'ITEMLOOKUP' || $columnName[$y] == 'ITEMDEFAULTVALUE')
			{	
				$theValue = $valuesArr[0][$y];
			}else{
		$theValue = replaceDangerousChar($valuesArr[0][$y]);
			}
		}else{
			$theValue = replaceDangerousChar($valuesArr[0][$y]);
		}
		$dataTypeArr[$y] = strtoupper($dataTypeArr[$y]);

		//append mode
		if($mode == 'append')
		{
			//rename column for importing purpose
			if($columnName[$y] == 'BL_ID')
				$columnName[$y] = 'BLID';
			else if($columnName[$y] == 'ITEM_ID')
				$columnName[$y] = 'ITEMID';
			else if($columnName[$y] == 'MENUPARENT')
				$columnName[$y] = 'MENUID';
			else if($columnName[$y] == 'USER_ID')
				$columnName[$y] = 'USERID';

			//if for flc_permission, change
			if($specialMode == 'FLC_PERMISSION')
			{
				if($columnName[$y] == 'PERM_ITEM')
				{
					$theValueTemp = replaceDangerousChar($valuesArr[0][$specialModeKey[0]]);

					if($theValueTemp == 'menu')
						$theValue = '{{{MENUID|'.$theValue.'}}}';
					else if($theValueTemp == 'page')
						$theValue = '{{{PAGEID|'.$theValue.'}}}';
					else if($theValueTemp == 'component')
						$theValue = '{{{COMPONENTID|'.$theValue.'}}}';
					else if($theValueTemp == 'item')
						$theValue = '{{{ITEMID|'.$theValue.'}}}';
					else if($theValueTemp == 'control')
						$theValue = '{{{CONTROLID|'.$theValue.'}}}';
				}
				else
				{
					if(in_array($y,$tableKeyIndex))
						$theValue = '{{{'.$columnName[$y].'|'.$theValue.'}}}';
				}
			}
			//if for flc_trigger, change
			else if($specialMode == 'FLC_TRIGGER')
			{
				if($columnName[$y] == 'TRIGGER_ITEM_ID')
				{
					$theValueTemp = replaceDangerousChar($valuesArr[0][$specialModeKey[0]]);

					if($theValueTemp == 'menu')
						$theValue = '{{{MENUID|'.$theValue.'}}}';
					else if($theValueTemp == 'page')
						$theValue = '{{{PAGEID|'.$theValue.'}}}';
					else if($theValueTemp == 'component')
						$theValue = '{{{COMPONENTID|'.$theValue.'}}}';
					else if($theValueTemp == 'item')
						$theValue = '{{{ITEMID|'.$theValue.'}}}';
					else if($theValueTemp == 'control')
						$theValue = '{{{CONTROLID|'.$theValue.'}}}';
				}
				else
				{
					if(in_array($y,$tableKeyIndex))
						$theValue = '{{{'.$columnName[$y].'|'.$theValue.'}}}';
				}
			}
			else
			{
				//todo here!!!!! issue perkeso
				//if column is BLDETAIL, scan for redirect script menuid, and prepend/append the menuid with {{{ }}}
				if($columnName[$y] == 'BLDETAIL' || $columnName[$y] == 'CONTROLREDIRECTURL' || $columnName[$y] == 'COMPONENTTYPEQUERY' || $columnName[$y] == 'ITEMLOOKUP' || $columnName[$y] == 'ITEMDEFAULTVALUE')
				{
					//pattern 1 - menuid 
					$toFind = '[AMP]menuID=';
					
					//first, find the occurences of this pattern
					$menuIDPos = find_occurences($theValue,$toFind);

					//for all menuid variable positions, get the menuid
					for($z=0; $z < count($menuIDPos); $z++)
					{
						$start = $menuIDPos[$z]+strlen($toFind);
						$endPos = walkStringForNonNumeric($theValue,$start)-1;
						$menuID = substr($theValue,$start,$endPos-$start);
						
						if($menuID !== false && $menuID != '')
						{
							$menuIDReplaced = $toFind.'{{{MENUID|'.$menuID.'}}}';
							$theValue = str_replace($toFind.$menuID,$menuIDReplaced,$theValue);
							
							//re-search the menuID pattern
							$menuIDPos = find_occurences($theValue,$toFind);
						}
					}
					
					//todo here 
					//pattern 2 - column_xxx_yyy
					$toFind = '[QS]column_';
					
					//todo - for 100% matching
					//use regex to find this pattern [QS]column_xxx_yyy[QS]
					
					//first, find the occurences of this pattern
					$rowRefIDPos = find_occurences($theValue,$toFind);

					//two pass checking
					for($x=0; $x < 2; $x++)
					{
						if($x == 0)
							$quote = '[QS]';
						else if($x == 1)
							$quote = '[QD]';	
						
						$toFind = $quote.'column_';
						
						//todo - for 100% matching
						//use regex to find this pattern [QS]column_xxx_yyy[QS] or [QD]
						
						//first, find the occurences of this pattern
						$rowRefIDPos = find_occurences($theValue,$toFind);

						//for all compid and itemid variable positions, get the compid and the itemid
						for($z=0; $z < count($rowRefIDPos); $z++)
						{
							$start = $rowRefIDPos[$z]+strlen($toFind);
							$endPos = walkStringForNonNumericAndUnderscore($theValue,$start)-1;
							$compIDItemID = substr($theValue,$start,$endPos-$start);
							
							$compIDItemIDArr = explode('_',$compIDItemID);
							$compID = '{{{COMPONENTID|'.$compIDItemIDArr[0].'}}}';
							$itemID = '{{{ITEMID|'.$compIDItemIDArr[1].'}}}';
							
							if($compIDItemID !== false && $compIDItemID != '')
							{
								$compIDItemIDReplaced = $toFind.$compID.'_'.$itemID.$quote;
								$theValue = str_replace($toFind.$compIDItemID.$quote,$compIDItemIDReplaced,$theValue);
								
								//re-search the compid pattern
								$rowRefIDPos = find_occurences($theValue,$toFind);
							}
						}
					}
				}
				else if($columnName[$y] == 'USERNAME')
				{
					if($exportPrefix != '')
						$theValue = 'FLCIMPTEMP_'.$theValue;
				}
				
				else 
					if(in_array($y,$tableKeyIndex))
						$theValue = '{{{'.$columnName[$y].'|'.$theValue.'}}}';
			}
		}//end if append mode

		if(in_array($dataTypeArr[$y],array('VARCHAR2','VARCHAR','LONG','TEXT','LONGTEXT','BLOB','CLOB')))
		{
			if($valuesArr[0][$y] == '')
			{
				if(DBMS_NAME == 'informix')
					$values[] = "'".$theValue."'";
				else	
				$values[] = 'null';
			}
			else
				//$values[] = $theValue;
				$values[] = "'".$theValue."'";
		}
		else if($dataTypeArr[$y] == 'DATETIME')
		{
			$values[] = "'".$theValue."'";
		}
		else if($dataTypeArr[$y] == 'CHAR')
		{
			if(DBMS_NAME == 'informix')
			{
				$values[] = "'".trim($theValue)."'";
			}	
		}
		else if($dataTypeArr[$y] == 'DATE')
		{
			if(DBMS_NAME == 'oracle')
			{
				//convert the date to string
				$exploded = explode('-',$theValue);

				if($exploded[1] == 'JAN')
					$exploded[1] = '01';
				else if($exploded[1] == 'FEB')
					$exploded[1] = '02';
				else if($exploded[1] == 'MAR')
					$exploded[1] = '03';
				else if($exploded[1] == 'APR')
					$exploded[1] = '04';
				else if($exploded[1] == 'MAY')
					$exploded[1] = '05';
				else if($exploded[1] == 'JUN')
					$exploded[1] = '06';
				else if($exploded[1] == 'JUL')
					$exploded[1] = '07';
				else if($exploded[1] == 'AUG')
					$exploded[1] = '08';
				else if($exploded[1] == 'SEP')
					$exploded[1] = '09';
				else if($exploded[1] == 'OCT')
					$exploded[1] = '10';
				else if($exploded[1] == 'NOV')
					$exploded[1] = '11';
				else if($exploded[1] == 'DEC')
					$exploded[1] = '12';

				$theValue = $exploded[0].'-'.$exploded[1].'-20'.$exploded[2];				//todo - 20 means 20'ish year

				//if valid date
				if(count($exploded) == 3)
					$values[] = "to_date('".$theValue."','dd-mm-yyyy')";
				else
					$values[] = 'null';
			}
			else if(DBMS_NAME == 'mysql')
			{
				$values[] = "'".$theValue."'";
			}
			else if(DBMS_NAME == 'informix')
			{
				$values[] = date('d-m-Y', strtotime($theValue));
			}
		}
		else
		{
			if($valuesArr[0][$y] == '')
				$values[] = 'null';
			else
				$values[] = trim($theValue);
		}
	}
	return $values;
}

//pad key column values with string
function padKeyColumnWithStr($values,$location,$str)
{
	$tempValues = array();

	for($x=0; $x < count($values); $x++)
	{
		if($x == $location)
			$tempValues[] = $str.$values[$x].$str;
		else
			$tempValues[] = $values[$x];
	}

	return $tempValues;
}

//to pad string column with [GLOBAL.XXXX]
//EG: CONTROLREDIRECTURL = index.php?page=page_wrapper&menuID=222
//result: index.php?page=page_wrapper&menuID=[GLOBAL.MENUID]222[GLOBAL.MENUID]
function padStringColWithStr($values,$location,$str)
{
	$tempValues = array();

	for($x=0; $x < count($values); $x++)
	{
		if($x == $location)
		{
			//get url param value
			$URLParamValue = getURLParamValue(str_replace("'","",str_replace('[AMPERSAND]','&',$values[$x])),$str);

			if($URLParamValue != '')
				$tempValues[] = str_replace($str.'='.$URLParamValue,$str.'='.'[GLOBAL.MENUID]'.$URLParamValue.'[GLOBAL.MENUID]',$values[$x]);
			else
				$tempValues[] = $values[$x];
		}
		else
			$tempValues[] = $values[$x];
	}
	return $tempValues;
}

//to pad passing parameter ({POST|input_map_xxx_yyy},{GET|xxxx}) to {POST|input_map_[GLOBAL.COMPONENTID]xxx[GLOBAL.COMPONENTID]_[GLOBAL.ITEMID]yyy[GLOBAL.ITEMID]}
function padParamWithStr($values,$location)
{
	$tempValues = array();
	$valCount = count($values);

	for($x=0; $x < $valCount; $x++)
	{
		$position_1 = array();
		$position_2 = array();

		if($x == $location)
		{
			//if string is not null
			if($values[$x] != '')
			{
				//check query for {
				while($offset = strpos($values[$x], "{", $offset + 1))
					$position_1[] = $offset;

				//check query for }
				while($offset = strpos($values[$x], "}", $offset + 1))
					$position_2[] = $offset;

				$tempOriginal = array();
				$tempReplacement = array();

				$countPos1 = count($position_1);

				//for all number of { and }
				for($y=0; $y < $countPos1; $y++)
				{
					//get original sub string
					$original[$y] = substr($values[$x],$position_1[$y],$position_2[$y]-$position_1[$y]+1);

					//define things to be replaced
					$str_1 = array('{','}');
					$str_2 = array('','');

					//start processing the string
					$replaced = str_replace($str_1,$str_2,$original[$y]);

					//split to chunks
					$replacedSplit = explode('|',$replaced);

					//split to componentid and itemid
					$splitComponentAndItem = explode('_',$replacedSplit[1]);

					//if post
					if($replacedSplit[0] == 'POST')
					{
						$tempOriginal[] = $original[$y];
						$tempReplacement[] = '{POST|input_map_'.'[GLOBAL.COMPONENTID]'.$splitComponentAndItem[2].'[GLOBAL.COMPONENTID]'.'_'.'[GLOBAL.ITEMID]'.$splitComponentAndItem[3].'[GLOBAL.ITEMID]}';
					}
					//else($replacedSplit[0] == 'GET'
					//	$tempReplacement[] = $values[$x];
				}

				$tempValues[] = str_replace($tempOriginal,$tempReplacement,$values[$x]);

			}//end if values != ''
			else
				$tempValues[] = $values[$x];
		}
		else
			$tempValues[] = $values[$x];
	}
	return $tempValues;
}

//to pad component item ID eg: $('input_map_xxx_yyy') = > $('input_map_[GLOBAL.COMPONENTID]xxx[GLOBAL.COMPONENTID]_[GLOBAL.ITEMID]yyy[GLOBAL.ITEMID]')
function padJSParamWithStr($values,$location)
{
	$tempValues = array();
	$pattern = "/input_map_(\d+)_(\d+)/";									//for input_map_xxx_yyy (javascript)
	$replacement = "input_map_[GLOBAL.COMPONENTID]$1[GLOBAL.COMPONENTID]_[GLOBAL.ITEMID]$2[GLOBAL.ITEMID]";

	for($x=0; $x < count($values); $x++)
	{
		if($x == $location)
			$tempValues[] = preg_replace($pattern,$replacement,$values[$x]);
		else
			$tempValues[] = $values[$x];
	}
	return $tempValues;
}

//replace dangerous char
function replaceDangerousChar($str)
{
	//if database is oracle
	if(strtoupper(DBMS_NAME) == 'ORACLE')
	{
		$dangerous = array("\r\n","'",'"',"&",":","<",">","%","\r","\n","\\","\t","[TB][TB]","[TB2][TB2]","[TB4][TB4]","[TB4][TB2][TB]","[TB7][NL]","[NL][NL]","[TB4][TB2]","[NL][TB7]","[NL][TB8]");
		$safe = array("[NL]","[QS]","[QD]","[AMP]","[CLN]","[OBR]","[CBR]","[PCT]","[RTN]","[NLX]","[BSL]","[TB]","[TB2]","[TB4]","[TB8]","[TB7]","[TB7N]","[NL2]","[TB6]","[NTB7]","[NTB8]");
	}
	else if(strtoupper(DBMS_NAME) == 'MYSQL')
	{
		//diff between mysql and oracle, no replacement for colon :
		$dangerous = array("\r\n","'",'"',"&","<",">","%","\r","\n","\\","\t","[TB][TB]","[TB2][TB2]","[TB4][TB4]","[TB4][TB2][TB]","[TB7][NL]","[NL][NL]","[TB4][TB2]","[NL][TB7]","[NL][TB8]");
		$safe = array("[NL]","[QS]","[QD]","[AMP]","[OBR]","[CBR]","[PCT]","[RTN]","[NLX]","[BSL]","[TB]","[TB2]","[TB4]","[TB8]","[TB7]","[TB7N]","[NL2]","[TB6]","[NTB7]","[NTB8]");
	}
	else if(strtoupper(DBMS_NAME) == 'INFORMIX')
	{
		
		$dangerous = array("\r\n","'",'"',"&",":","<",">","%","\r","\n","\\","\t","[TB][TB]","[TB2][TB2]","[TB4][TB4]","[TB4][TB2][TB]","[TB7][NL]","[NL][NL]","[TB4][TB2]","[NL][TB7]","[NL][TB8]");
		$safe = array("[NL]","[QS]","[QD]","[AMP]","[CLN]","[OBR]","[CBR]","[PCT]","[RTN]","[NLX]","[BSL]","[TB]","[TB2]","[TB4]","[TB8]","[TB7]","[TB7N]","[NL2]","[TB6]","[NTB7]","[NTB8]");
	
	}

	return str_replace($dangerous, $safe, $str);
}

//replace dangerous char
function replaceCharTriggerParam($str)
{
	$dangerous = array("\r\n","'",'"',"&",":","<",">","%","\r","\n","\\","\t","[TB][TB]","[TB2][TB2]","[TB4][TB4]","[TB4][TB2][TB]","[TB7][NL]","[NL][NL]","[TB4][TB2]","[NL][TB7]","[NL][TB8]");
		$safe = array("[NL]","[QS]","[QD]","[AMP]","[CLN]","[OBR]","[CBR]","[PCT]","[RTN]","[NLX]","[BSL]","[TB]","[TB2]","[TB4]","[TB8]","[TB7]","[TB7N]","[NL2]","[TB6]","[NTB7]","[NTB8]");

	return str_replace($dangerous, $safe, $str);
}

//to create dump directory
function createDir($dir)
{
	//file path to save
	if(is_dir($dir) == false)
		mkdir($dir);
}

//write to file
function writeToDump($timestamp,$data,$truncate=0,$commit=1,$dumpType)
{
	$filePath = 'export_import/exp_'.$_SESSION['userID'].$timestamp.'.fdmp'.$dumpType;

	if($truncate == 1)
		$fopenX = 'w+';
	else
		$fopenX = 'a';

    if(!$handle = fopen($filePath,$fopenX))
	{
		echo "Cannot open file ($filename)";
       	exit;
   	}

	//if data is not empty string
	if($data != '')
	{
		//sql line ending delimiter
		if(strtoupper(DBMS_NAME) == 'ORACLE')
			$sqlNewLine = '';
		else if(strtoupper(DBMS_NAME) == 'MYSQL')
			$sqlNewLine = '[ENDSQLLINE]';
		else if(strtoupper(DBMS_NAME) == 'INFORMIX')
			$sqlNewLine = '';

		if($commit == 1)
			$commit = "commit;".$sqlNewLine."\r\n";
		else
			$commit = '';

		//write to file
		if(fwrite($handle,implode('',$data).$commit) === FALSE)
		{
			echo "Cannot write to file ($filename)";
			exit;
		}
	}

   	fclose($handle);
}

function writeDBSyncScript($timestamp,$data)
{
	$filePath = 'export_import/dbsync/dbsync_'.$_SESSION['userID'].$timestamp.'.fsyc';

    if(!$handle = fopen($filePath,'w'))
	{
		echo "Cannot open file ($filename)";
       	exit;
   	}

	//write to file
	if(fwrite($handle,$data) === FALSE)
	{
		echo "Cannot write to file ($filename)";
		exit;
	}
   	fclose($handle);
}

//src: http://www.phpro.org/examples/Find-Position-Of-Nth-Occurrence-Of-String.html
//function to find position of string in a string, given the number of offset
function strposOffset($search, $string, $offset)
{
    /*** explode the string ***/
    $arr = explode($search, $string);
    /*** check the search is not out of bounds ***/
    switch( $offset )
    {
        case $offset == 0:
        return false;
        break;

        case $offset > max(array_keys($arr)):
        return false;
        break;

        default:
        return strlen(implode($search, array_slice($arr, 0, $offset)));
    }
}

//get url parameter value based on given component name
function getURLParamValue($url,$component)
{
	$URLArr = parse_url($url);
	$query = $URLArr['query'];
	$queryArr = explode('&',$query);

	for($x=0; $x < count($queryArr); $x++)
	{
		$bitsArr = explode('=',$queryArr[$x]);

		//if component selected is matched, return value
		if($bitsArr[0] == $component)
			return $bitsArr[1];
	}
}

//create rollback data to write
function createRollbackData($mySQL,$menuID,$pageID,$componentID,$itemID,$controlID)
{
	$newMenuIDMax = $mySQL->maxValue('FLC_MENU','MENUID');
	$newPageIDMax = $mySQL->maxValue('FLC_PAGE','PAGEID');
	$newComponentIDMax = $mySQL->maxValue('FLC_PAGE_COMPONENT','COMPONENTID');
	$newItemIDMax = $mySQL->maxValue('FLC_PAGE_COMPONENT_ITEMS','ITEMID');
	$newControlIDMax = $mySQL->maxValue('FLC_PAGE_CONTROL','CONTROLID');

	return 'MENU:'.$menuID.'|'.$newMenuIDMax."\r\n".'PAGE:'.$pageID.'|'.$newPageIDMax."\r\n".'COMPONENT:'.$componentID.'|'.$newComponentIDMax."\r\n".'ITEM:'.$itemID.'|'.$newItemIDMax."\r\n".'CONTROL:'.$controlID.'|'.$newControlIDMax;
}

//to write rollback data to file
function dumpRollbackData($timestamp,$rollbackData)
{
	createDir('export_import/rollback');
	file_put_contents('export_import/rollback/' . 'rollback_'.$_SESSION['userID'].$timestamp,$rollbackData);
}

//get list of files in rollback directory
function getRollbackList()
{
	//get list of files in the rollback directory
	$rollbackList = scandir('export_import/rollback',1);
	array_pop($rollbackList);		//remove .
	array_pop($rollbackList);		//remove ..

	return $rollbackList;
}

//function to rollback changes
function rollbackData($mySQL)
{
	$rollbackData = file_get_contents('export_import/rollback/'.$_POST['rollbackList']);

	if(strlen($rollbackData) > 0)
	{
		$explode = explode("\r\n",$rollbackData);

		$rollbackQry = '';

		for($x=0; $x < count($explode); $x++)
		{
			$explodeMore = explode(':',$explode[$x]);
			$val = explode('|',$explodeMore[1]);

			if($explodeMore[0] == 'MENU')
				$qry .= 'delete from FLC_MENU where MENUID between '.($val[0]+1). ' and '.$val[1].';';
			else if($explodeMore[0] == 'PAGE')
				$qry .= 'delete from FLC_PAGE where PAGEID between '.($val[0]+1). ' and '.$val[1].';';
			else if($explodeMore[0] == 'COMPONENT')
				$qry .= 'delete from FLC_PAGE_COMPONENT where COMPONENTID between '.($val[0]+1). ' and '.$val[1].';';
			else if($explodeMore[0] == 'ITEM')
				$qry .= 'delete from FLC_PAGE_COMPONENT_ITEMS where ITEMID between '.($val[0]+1). ' and '.$val[1].';';
			else if($explodeMore[0] == 'CONTROL')
				$qry .= 'delete from FLC_PAGE_CONTROL where CONTROLID between '.($val[0]+1). ' and '.$val[1].';';
		}

		//replace carriage return with empty string to work around oracle script carriage return problem
		return $rollbackRs = $mySQL->dbExecute(DB_CONNECTION,DB_DATABASE,DB_USERNAME,DB_PASSWORD,str_replace("\r\n","",$qry));
	}
}

function createReplaceStr($col)
{
	if(DBMS_NAME == 'oracle' || DBMS_NAME == 'informix')
	{
		$arr1 = array('[NTB8]','[NTB7]','[TB7N]','[TB7]','[TB8]','[TB6]','[TB4]','[TB2]','[NL2]','[NL]','[NLX]','[BSL]','[TB]',
						'[AMP]','[CLN]','[OBR]','[CBR]','[PCT]','[RTN]','[NL]','[QS]','[QD]');
		$arr2 = array('[NL][TB8]','[NL][TB7]','[TB7][NL]','[TB4][TB2][TB]','[TB4][TB4]','[TB4][TB2]','[TB2][TB2]','[TB][TB]',
						'[NL][NL]',"\r\n","\n","\\","\t","&",":",'<','>','%',"\n","\r\n","''",'"');
	}
	else if(DBMS_NAME == 'mysql')
	{
		$arr1 = array('[NTB8]','[NTB7]','[TB7N]','[TB7]','[TB8]','[TB6]','[TB4]','[TB2]','[NL2]','[NL]','[NLX]','[BSL]','[TB]',
						'[AMP]','[OBR]','[CBR]','[PCT]','[RTN]','[NL]','[QS]','[QD]');
		$arr2 = array('[NL][TB8]','[NL][TB7]','[TB7][NL]','[TB4][TB2][TB]','[TB4][TB4]','[TB4][TB2]','[TB2][TB2]','[TB][TB]',
						'[NL][NL]',"\r\n","\n","\\\\","\t","&",'<','>','%',"\n","\r\n","''",'"');
	}

	$str1 = '';
	$str2 = '';

	for($x=0; $x < count($arr1); $x++)
	{
		$str1 .= ' replace(';

		if($x==0)
			$str2 .= $col.",'".$arr1[$x]."','".$arr2[$x]."')";
		else
			$str2 .= ",'".$arr1[$x]."','".$arr2[$x]."')";
	}

	return $col.'='.$str1.$str2;
}

//http://frankkoehl.com/2009/03/second-third-fourth-occurence-string/
function strpos_offset_recursive($needle,$haystack,$occurence)
{
	if(($o=strpos($haystack,$needle))===false) return false;
	if($occurence>1)
	{
		$found=strpos_offset_recursive($needle,substr($haystack,$o+strlen($needle)),$occurence-1);
		return ($found!==false)?$o+$found+strlen($needle):false;
	}
	return $o;
}

function createUpdateLoopStatement($tbl,$col,$val,$clobLength,$key,$keyVal)
{
	$valLen = strlen($val);
	$fragment = ceil($valLen/$clobLength);
	$str = array();

	for($x=1; $x < $fragment; $x++)
	{
		$temp = "update ".$tbl." set ".$col."=".$col."||'".substr($val,($clobLength*$x),$clobLength);
		if($x+1 < $fragment)
			$temp .= "'";
		$temp .= " where ".$key."=".$keyVal.";\r\n";
		$str[] = $temp;
	}
	return implode('',$str);
}

//http://forums.phpfreaks.com/topic/104083-find-all-occurences-of-a-string-inside-of-another-string/
//author: darkWater
function find_occurences($string,$find)
{
	if(strpos($string,$find) !== false)
	{
		$pos = -1;

		$substrCnt = substr_count($string,$find);

		for($i=0; $i < $substrCnt; $i++)
		{
			$pos = strpos($string,$find,$pos+1);
			$positionarray[] = $pos;
		}
		return $positionarray;
	}
	else
		return false;

	/*
	if(strpos(strtolower($string), strtolower($find)) !== false)
	{
		$pos = -1;
		for($i=0; $i<substr_count(strtolower($string), strtolower($find)); $i++)
		{
			$pos = strpos(strtolower($string), strtolower($find), $pos+1);
			$positionarray[] = $pos;
		}
		return $positionarray;
	}
	else
		return false;
	*/
}

function removeDuplicateColumns($arr)
{
	$tempArray = array();
	$tempArrayIndex = array();

	for($x=0; $x < count($arr); $x++)
	{
		if(!in_array($arr[$x]['COLUMN_NAME'],$tempArray))
		{
			$tempArray[] = $arr[$x]['COLUMN_NAME'];
			$tempArrayIndex[] = $x;
		}
	}

	$arr_COPY = $arr;
	$arr = array();

	for($x=0; $x < count($tempArray); $x++)
		$arr[] = $arr_COPY[$tempArrayIndex[$x]];

	return $arr;
}

function getColumnList($myQuery,$dbType,$db,$user,$table)
{
	if(strtolower($dbType) == 'oracle')
	{
		$qry = "select upper(a.COLUMN_NAME) COLUMN_NAME, upper(a.DATA_TYPE) DATA_TYPE
				from all_tab_columns A
				WHERE upper(a.TABLE_NAME) = upper('".$table."')
				and upper(a.OWNER) in ('".strtoupper($user)."') order by a.column_id";
	}
	else if(strtolower($dbType) == 'mysql')
	{
		$qry = "select upper(COLUMN_NAME) COLUMN_NAME, upper(DATA_TYPE) DATA_TYPE 
					from information_schema.columns a
					where upper(TABLE_NAME) = upper('".$table."')
					and TABLE_SCHEMA = '".DB_DATABASE."'
					group by COLUMN_NAME
					order by ORDINAL_POSITION";
	}
	else if(strtolower($dbType) == 'informix')
	{
		//check for tablespace
		$temp=explode('.',$table);
		
		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if
		
		if(count($extraInfo))
		{
			if(in_array('size',$extraInfo))
			{
				$extraInfoCol .= ', b.COLLENGTH as SIZE';
			}
			if(in_array('primary',$extraInfo))
			{
				$extraInfoCol .= ', d.CONSTRTYPE as PRI_KEY';
			}
		}
		
		if($column)
			$extra=" and upper(b.COLNAME) = '".$column."' ";
		

		$qry="SELECT upper(b.COLNAME) COLUMN_NAME, b.COLTYPE DATA_TYPE, b.COLNO COLUMN_NO ".$extraInfoCol."
			   FROM systables a, syscolumns b , sysindexes c , sysconstraints d
			   WHERE a.tabid = b.tabid AND a.tabid = c.tabid AND a.tabid = d.tabid
			   AND upper(TABNAME) = upper('".$table."') ".$extra." GROUP BY COLUMN_NAME, DATA_TYPE, COLUMN_NO 
			   ORDER BY COLUMN_NO";
	}

	$qryRs = $myQuery->query($qry,'SELECT','NAME');

	return $qryRs;
}

//http://stackoverflow.com/questions/3835636/php-replace-last-occurence-of-a-string-in-a-string
//by Mischa
function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if($pos !== false)
        $subject = substr_replace($subject, $replace, $pos, strlen($search));

    return $subject;
}

function walkStringForNonNumeric($str,$start)
{
	$currentChar = substr($str,$start,1);
	$start++;

	if(is_numeric($currentChar))
		return walkStringForNonNumeric($str,$start);
	else
		return $start;
}

function walkStringForNonNumericAndUnderscore($str,$start)
{
	$currentChar = substr($str,$start,1);
	$start++;

	if(is_numeric($currentChar) ||$currentChar == '_' )
		return walkStringForNonNumericAndUnderscore($str,$start);
	else
		return $start;
}

if($_GET['type'] == 'ajax_upload')
{
echo "aaaa";
print_r($_FILES);
print_r($_POST);
print_r($_GET);
}
?>
