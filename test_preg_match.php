<?
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);
//error_reporting(E_ALL);

//http://hungred.com/how-to/regular-expression-find-position-strpos-stripos-php/
//preg_match_all('/barxxx/i', 'Fooasd asd asd as asdbarxxxzxczxc zxc barxxxzzc barxxxzxasdasd asds aasasdbarxxxad asdas dasd asdbarxxx',

/*
 * --------------------------
 * TODO
 * --------------------------
1. SCAN FOR ALL VARIABLES IN DECLARE BLOCK
* - FIND DECLARE BLOCK, GET ALL CONTENT FROM DECLARE START TO NEXT BEGIN STATEMENT
* - SEPARATE ALL DECLARE STATEMENT BY SEMICOLONS

PATTERN 1 
----------
DECLARE
  l_message  
  VARCHAR2 (100) := 'Hello World!';

PATTERN 2
---------
DECLARE
  l_message   VARCHAR2 (100) := 'Hello';
  l_message2  VARCHAR2 (100) := ' World!';

PATTERN 3
---------
DECLARE
  l_message  
  VARCHAR2 (100) := 'Hello';

PATTERN 4
---------
* DECLARE
  l_dept_id  
  employees.department_id%TYPE := 10;


 * 
 * 
 * 
 * 
 * */



echo '<pre>';
echo $functionStr = "

DECLARE
  l_message   VARCHAR2 (100) := 'Hello';
  l_message2  VARCHAR2 (100) := ' World!';
begin
	:system.message_level :=5;
	 set_window_property(forms_mdi_window,window_state,maximize);
	 set_window_property('tingkap',window_state,maximize);
	 set_window_property(forms_mdi_window, title, 'FAKADEMI - MAKLUMAT PELAJAR');
	 set_window_property('tingkap', title, property_false);
	 set_window_property('tingkap', xxxx, property_false);
	:system.message_level :=3;
	:b1.nokp := :global.nokp_form;

	test_var varchar2(40);
	test_int int(40);


	if :b1.nama is null then 
	message('Masukkan Nama Penuh / Singkatan Pelajar');
		set_window_property(forms_mdi_window,window_state,maximize);
	else
	 begin
	   semak_nama;
	   :system.message_level :=3;
	   message('alalalalalal');
	   message('bbbbbbbbbbb');
	   message('cccccccccc');
	   message('ddddddd');
	   go_block ('lperibd_cari'); execute_query;
	   if :lperibd_cari.lpnokp is null then
		  :lperibd_cari.lpnama := 'TIADA REKOD';
	   end if;
	 end;
	end if;
end;
";
echo '</pre>';
echo '<pre>';
echo 'All variables :<br>';

//variables for input
preg_match_all('/[:][0-9a-zA-Z._]{1,32}/i', $functionStr, $matches,PREG_OFFSET_CAPTURE);
echo '--------------<br>';

for($x=0; $x < count($matches[0]); $x++)
	$_SESSION['variables']['original'][] = $matches[0][$x][0];

//variables defined in procedure
//-------------------------------
//find declare block
//[\s\S] - match all char



/*

//$txt='declare asdasdasda adasdasdd begin asdasczxzxczxc begin xxxxxx end';

  $re1='((?:[a-z][a-z]+))';	# Word 1
  $re2='.*?';	# Non-greedy match on filler
  $re3='(?:[a-z][a-z]+)';	# Uninteresting: word
  $re4='.*?';	# Non-greedy match on filler
  $re5='(?:[a-z][a-z]+)';	# Uninteresting: word
  $re6='.*?';	# Non-greedy match on filler
  $re7='((?:[a-z][a-z]+))';	# Word 2
  */
/*
  if ($c=preg_match_all ("/".$re1.$re2.$re3.$re4.$re5.$re6.$re7."/is", $txt, $matches))
*/


//trace DECLARE block, get all contents, cut and put in header, and remove the declare block
function processDeclaredVariables($str)
{
	$returnArr = array();
	
	//case insensitive, multiline, non greedy
	preg_match_all('/declare[\s\S]{0,}begin/imU', $str, $matches);
	
	$returnArr['originalBlock'] = trim($matches[0][0]);
	
	if(($matches[0][0]))
	{
		$str = $matches[0][0];
		
		$str = preg_replace('/declare/i','', $str, 1);
		$str = preg_replace('/begin/i','', $str, 1);
		
		$returnArr['trimmedBlock'] = trim($str);
	}
	
	
	return $returnArr;

	
}

$matches = processDeclaredVariables($functionStr);

//for($x=0; $x < count($matches[0]); $x++)
	//$_SESSION['variables']['original'][] = $matches[0][$x][0];

echo '<pre>Defined variables :<br>';
print_r($matches);
echo '</pre>';



echo '</pre>';

function find_str_occurences($string,$find)
{
	if(stripos($string,$find) !== false)
	{
		$pos = -1;
		$substrCnt = substr_count(strtoupper($string), strtoupper($find));

		for($i=0; $i < $substrCnt; $i++)
		{
			$pos = stripos($string,$find,$pos+1);
			$positionarray[] = $pos;
		}
		return $positionarray;
	}
	else
		return false;
}

function isOraFormsFunctions($check)
{
	$check = str_replace(';','',$check);
	$arr = array('go_block','show_view','execute_query');
	
	if(in_array($check,$arr))
		return true;
	else
		return false;
}

function filterOut($arr)
{
	$newArr = array();
	
	for($x=0; $x < count($arr); $x++)
	{
		if(trim($arr[$x][0]) == 'end;')
		{}
		else if(trim($arr[$x][0]) == 'if;')
		{
		}
		else
			$newArr[] = $arr[$x];
			
		
	}
	return $newArr;
}

function renameFunctions($str)
{		
	//-----------------------
	//function with arguments
	//----------------------- 
	
	//not including function with ; delimeter with no space
	preg_match_all('/[ \n]{1,99}[a-zA-Z]{1}[0-9a-zA-Z._]{1,50}[ ]{0,999}[(]/i', $str, $matches,PREG_OFFSET_CAPTURE);

	for($x=0; $x < count($matches[0]); $x++)
	{
		if(strstr($matches[0][$x][0][0], PHP_EOL)) 
			$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'FLC_'.trim($matches[0][$x][0]),$str);	
		else if($matches[0][$x][0][0] == ' ') 
			$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'FLC_'.trim($matches[0][$x][0]),$str);	
	}
	
	//for function with ; as delimiter with no space
	preg_match_all('/[;]{1,99}[a-zA-Z]{1}[0-9a-zA-Z._]{1,50}[ ]{0,999}[(]/i', $str, $matches,PREG_OFFSET_CAPTURE);
	
	for($x=0; $x < count($matches[0]); $x++)
	{
		if($matches[0][$x][0][0] == ';') 
			$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'FLC_'.substr($matches[0][$x][0],1),$str);	
		else
			$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'unknownFLC_'.substr($matches[0][$x][0],1),$str);	
	}
	
	//--------------------------
	//function without arguments
	//--------------------------
	
	//not including function with ; delimeter with no space
	preg_match_all('/[ \n]{1,99}[a-zA-Z]{1}[0-9a-zA-Z_]{1,50}[ ]{0,999}[;]{1}|[;]{1}[a-zA-Z]{1}[0-9a-zA-Z_]{1,50}[ ]{0,999}[;]{1}/i', $str, $matches,PREG_OFFSET_CAPTURE);
	
	echo '<pre>';
	print_r($matches);
	echo '</pre>';
	
	$matches[0] = filterOut($matches[0]);
			
	for($x=0; $x < count($matches[0]); $x++)
	{
	//	echo 'xxxxxxxxxxx'.trim($matches[0][$x][0]).'xxxxxxxxxx';
		if(isOraFormsFunctions(trim($matches[0][$x][0])))
		{
			//echo 'yesss'.trim($matches[0][$x][0]).'yesss';
		
			//append this
			if(trim(str_replace(';','',$matches[0][$x][0])) == 'execute_query')
			{
				$tableName = "LPERIBD_CARI";
				$formName = "fakademi";
				
				$executeQueryStr = "\n\n/** --- EXECUTE QUERY --- **/";
				$executeQueryStr .= "\n".str_pad("idxNo1",35,' ')." := IdxNo1+1;";
				$executeQueryStr .= "\n".str_pad("RESULT_NAME_ARRAY(IdxNo1)",35,' ')." := 'execute_query';";
				$executeQueryStr .= "\n".str_pad("RESULT_VALUE_ARRAY(IdxNo1)",35,' ')." := flc_go_block;";
				//$executeQueryStr .= "\n".str_pad("wherestmt",35,' ')." := NULL;";
				$executeQueryStr .= "\n".str_pad("wherestmt",35,' ')." := ' where 0 = 0 ';";
				//$executeQueryStr .= "\nxxxxxxxxxxxfakademi_trigger_pkg.fakademi_C1035_PRQ(name_array,value_array,flc_name_array_qry,flc_value_array_qry);";
				$executeQueryStr .= "\n".str_pad("idxno",35,' ')." := flc_name_array_qry.count;";
				$executeQueryStr .= "\n\nFOR IdxNo IN 1 .. flc_name_array_qry.count";
				$executeQueryStr .= "\n  LOOP";
				$executeQueryStr .= "\n  IF flc_value_array_qry(idxNo) IS NOT NULL AND instr(upper(flc_name_array_qry(idxno)),'".$tableName."',1)=1 THEN";
				$executeQueryStr .= "\n    ".str_pad("wherestmt",35,' ')." := whereStmt||' and '|| SUBSTR(flc_name_array_qry(idxno),instr(flc_name_array_qry(idxno),'--',1)+2,LENGTH(flc_name_array_qry(idxno)));";
				$executeQueryStr .= "\n    ".str_pad("wherestmt",35,' ')." := whereStmt||' like '''||flc_value_array_qry(idxNo)||'''';";
				$executeQueryStr .= "\n  END IF;";
				$executeQueryStr .= "\nEND LOOP;";
				$executeQueryStr .= "\n".str_pad("IdxNo2",35,' ')." :=idxno2+1;";
				$executeQueryStr .= "\n".str_pad("name_array(idxno2)",35,' ')." := '".$tableName."_qry';";
				$executeQueryStr .= "\n".str_pad("value_array(idxno2)",35,' ')." := wherestmt;";
				$executeQueryStr .= "\nflc_migration_pkg.".$formName."_".$tableName."_qry (name_array,value_array,flc_name_array_qry,flc_value_array_qry);";
				$executeQueryStr .= "\nFOR IdxNo IN 1 .. flc_name_array_qry.count";
				$executeQueryStr .= "\nLOOP";
				$executeQueryStr .= "\n".str_pad("idxNo1",35,' ')." := IdxNo1+1;";
				$executeQueryStr .= "\n".str_pad("RESULT_NAME_ARRAY(IdxNo1)",35,' ')." := flc_name_array_qry(idxNo);";
				$executeQueryStr .= "\n".str_pad("RESULT_VALUE_ARRAY(IdxNo1)",35,' ').":= flc_value_array_qry(idxNo);";
				$executeQueryStr .= "\nEND LOOP;";
				
				
				
				//echo 'sssssssssss'.$matches[0][$x][0].'ssssssssss';
				$str = str_replace(trim($matches[0][$x][0]),$executeQueryStr,$str);
				//str_replace(
				
			}
			else if(trim($matches[0][$x][0]) == 'go_block;')
			{
				echo '2222222222222222222222';
				$str .= "FLC_go_block  := 'the_block_name';";
				$str .= "IdxNo1 := IdxNo1 + 1;";
				$str .= "Result_name_array(idxNo1) := 'go_block ';";
				$str .= "Result_value_array(idxNo1) := 'the_block_name';";
				$str .= "                                               ";
				$str .= "FLC_current_block := FLC_go_block;";
				$str .= "IdxNo1 := IdxNo1 + 1;";
				$str .= "Result_name_array(idxNo1) := 'current_block';";
				$str .= "Result_value_array(idxNo1) := FLC_go_block";
			}
			
				if($matches[0][$x][0][0] == ';')
				$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'FLC_'.substr($matches[0][$x][0],1)."",$str);				
			else
				$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'FLC_'.trim($matches[0][$x][0])."",$str);			
				
			

	
		}
		else
		{
			$replaceStr = "\n  form_name_PROGUNIT_PKG.form_name_uxxxx(name_array,value_array,flc_name_array,flc_value_array);\n";
			$replaceStr .= "  for idxno in 1 .. flc_name_array.count\n";
			$replaceStr .= "    loop\n";
			$replaceStr .= "    idxno1:=idxno1+1;\n";
			$replaceStr .= "    result_name_array(idxno1):=flc_name_array(idxno);\n";
			$replaceStr .= "    result_value_array(idxno1):=flc_value_array(idxno);\n";
			$replaceStr .= "  end loop;\n";
			$str = str_replace($matches[0][$x][0],$replaceStr,$str);				
		}
		
	}
	
	//for function with ; as delimiter with no space
	preg_match_all('/[;]{1}[a-zA-Z]{1}[0-9a-zA-Z_]{1,50}[ ]{0,999}[;]{1}[\n]{1}/i', $str, $matches,PREG_OFFSET_CAPTURE);
	
	for($x=0; $x < count($matches[0]); $x++)
		$str = str_replace($matches[0][$x][0], $matches[0][$x][0][0].'FLC_'.substr($matches[0][$x][0],1),$str);				
	
	
	
	return $str;
}

function renameVariables($str)
{
	preg_match_all('/[:][0-9a-zA-Z._]{1,32}/i', $str, $matches,PREG_OFFSET_CAPTURE);
	
	//echo '<pre>';
	//print_r($matches);
	//echo '</pre>';
	
	unset($_SESSION['variables']['renamed']);
	
	for($x=0; $x < count($matches[0]); $x++)
	{
		$newStr = 'FLC_'.substr(str_replace('.','__',$matches[0][$x][0]),1);
		$str = str_replace($matches[0][$x][0], $newStr, $str);	
		$_SESSION['variables']['renamed'][$x] = $newStr;
	}
	
	return $str;
}

function renameDeclaredVariables($str)
{
	preg_match_all('/[:][0-9a-zA-Z._]{1,32}/i', $str, $matches,PREG_OFFSET_CAPTURE);
	
	//echo '<pre>';
	//print_r($matches);
	//echo '</pre>';
	
	unset($_SESSION['variables']['renamed']);
	
	for($x=0; $x < count($matches[0]); $x++)
	{
		$newStr = 'FLC_'.substr(str_replace('.','__',$matches[0][$x][0]),1);
		$str = str_replace($matches[0][$x][0], $newStr, $str);	
		$_SESSION['variables']['renamed'][$x] = $newStr;
	}
	
	return $str;
}

function addHeader($str)
{
	$headerStart = "/** [ -------- BEGIN HEADER SECTION -------- ] **/";
	
	if(stripos($str,'FLC_MESSAGE('))
		$headerStart .= "\n".str_pad("FLC_MESSAGE",35,' ')." varchar2(4000);";
	
	if(stripos($str,'FLC_SET_WINDOW_PROPERTY'))
		$headerStart .= "\n".str_pad("FLC_SET_WINDOW_PROPERTY",35,' ')." varchar2(4000);";
	
	$uniqueArr_1 = array_unique($_SESSION['variables']['original']);
	$uniqueArr_1 = array_values($uniqueArr_1);
	
	$uniqueArr_2 = array_unique($_SESSION['variables']['renamed']);
	$uniqueArr_2 = array_values($uniqueArr_2);
	
	for($x=0; $x < count($uniqueArr_1); $x++)
		$headerStart .= "\n".str_pad($uniqueArr_2[$x],35,' ')." varchar2(4000);";
	
	$headerStart .= "\n";		
			
	$str = 	$headerStart .	
			"\nBEGIN\n/** [ -------- BEGIN BODY SECTION -------- ] **/\n" . 
			"execute immediate 'alter session set nls_date_format=''dd-mm-yyyy''';\n\n" . 
			"/** [ -------- BEGIN MIGRATION VALUE ASSIGNMENT SECTION -------- ] **/\n" . 
			"\n/** [ -------- BEGIN LOGIC SECTION -------- ] **/\n" . 
			$str . "\n";
	
	$str = $str."\n/** [ -------- BEGIN END SECTION -------- ] **/\nEND;\n";
	
	return $str;
}

function addFooter($str)
{
	$footerStart = "\n/** [ -------- BEGIN END SECTION -------- ] **/";
	$newStr = '';
	
	$uniqueArr_1 = array_unique($_SESSION['variables']['original']);
	$uniqueArr_1 = array_values($uniqueArr_1);
	
	$uniqueArr_2 = array_unique($_SESSION['variables']['renamed']);
	$uniqueArr_2 = array_values($uniqueArr_2);
	
	for($x=0; $x < count($uniqueArr_1); $x++)
	{
		//str_pad("FLC_SET_WINDOW_PROPERTY",35,' ')
		
		
		$newStr .= "\n".str_pad("IdxNo1",30,' ')." := IdxNo1+1;";
		$newStr .= "\n".str_pad("RESULT_NAME_ARRAY(IdxNo1)",30,' ')." := '".str_replace('.','--',str_replace(':','',$uniqueArr_1[$x]))."';";
		$newStr .= "\n".str_pad("RESULT_VALUE_ARRAY(IdxNo1)",30,' ')." := ".$uniqueArr_2[$x].";\n";
	}
	
	$str = str_replace($footerStart, $footerStart . $newStr, $str);	
	return $str;
}

function flc_ora_migration_value_func($str)
{
	$searchStr = "/** [ -------- BEGIN MIGRATION VALUE ASSIGNMENT SECTION -------- ] **/\n";
	$pos = stripos($str,$searchStr);
	
	//reverse order as in add header
	if(stripos($str,'FLC_SET_WINDOW_PROPERTY('))
		$str = str_replace($searchStr, $searchStr . str_pad("FLC_SET_WINDOW_PROPERTY",35,' ')." := FLC_MIGRATION_PKG.FLC_MIGRATION_VALUE(NAME_ARRAY,VALUE_ARRAY,'set_window_property');\n",$str);
	
	if(stripos($str,'FLC_MESSAGE('))
		$str = str_replace($searchStr, $searchStr . str_pad("FLC_MESSAGE",35,' ')." := FLC_MIGRATION_PKG.FLC_MIGRATION_VALUE(NAME_ARRAY,VALUE_ARRAY,'message');\n",$str);
	
	$uniqueArr_1 = array_unique($_SESSION['variables']['original']);
	$uniqueArr_1 = array_values($uniqueArr_1);
	
	$uniqueArr_2 = array_unique($_SESSION['variables']['renamed']);
	$uniqueArr_2 = array_values($uniqueArr_2);
	
	for($x=0; $x < count($uniqueArr_1); $x++)
		$str = str_replace($searchStr, $searchStr . str_pad($uniqueArr_2[$x],35,' ')." := FLC_MIGRATION_PKG.FLC_MIGRATION_VALUE(NAME_ARRAY,VALUE_ARRAY,'".str_replace('.','--',str_replace(':','',$uniqueArr_1[$x]))."');\n",$str);
	
	return $str."\n";
}

function flc_ora_builtin_message($str)
{	
	$occurencesInitial = find_str_occurences($str,'FLC_MESSAGE(');
	
	if(count($occurencesInitial))
	{
		for($a=0; $a < count($occurencesInitial); $a++)
		{	
			$funcToSearch = 'FLC_MESSAGE(';
			$funcToSearchLen = strlen($funcToSearch);
			
			if($a == 0)
				$pos = $occurencesInitial[0];
			
			$msgStr = '';
			
			for($x=$pos+$funcToSearchLen+1; $x < $pos+100; $x++)
			{
				if($str[$x] == "'")
					break;
				else
					$msgStr .= $str[$x];
			}
			
			$returnStr = "\n".str_pad("FLC_MESSAGE",30,' ')." := '".$msgStr."';";
			$returnStr .= "\n".str_pad("IdxNo1",30,' ')." := IdxNo1 + 1; ";
			$returnStr .= "\n".str_pad("RESULT_NAME_ARRAY(idxNo1)",30,' ')." := 'message ';";
			$returnStr .= "\n".str_pad("FLC_VALUE_ARRAY(idxNo1)",30,' ')." := '".$msgStr."'";
			
			//replace the string
			$str = str_ireplace($funcToSearch."'".$msgStr."')",$returnStr,$str);
			
			$occurences = find_str_occurences($str,'FLC_MESSAGE(');
			$pos = $occurences[0];	
		}
	}
	
	return $str;
}

function flc_ora_builtin_set_window_property($str)
{	
	$occurencesInitial = find_str_occurences($str,'FLC_SET_WINDOW_PROPERTY(');
		
	if(count($occurencesInitial))
	{
		for($a=0; $a < count($occurencesInitial); $a++)
		{				
			$funcToSearch = 'FLC_SET_WINDOW_PROPERTY(';
			$funcToSearchLen = strlen($funcToSearch);
			
			if($a == 0)
				$pos = $occurencesInitial[0];

			$msgStr = '';
			
			for($x=$pos+$funcToSearchLen; $x < $pos+100; $x++)
			{
				if($str[$x] == ")")
					break;
				else
					$msgStr .= $str[$x];
			}
			
			$params = explode(',',$msgStr);
			$params = array_map('trim',$params);		//trim whitespace
							
			$returnStr = "\n".str_pad("FLC_SET_WINDOW_PROPERTY",30,' ')." := '".implode('||',$params)."';";
			$returnStr .= "\n".str_pad("IdxNo1",30,' ')." := IdxNo1 + 1;"; 
			$returnStr .= "\n".str_pad("RESULT_NAME_ARRAY(idxNo1)",30,' ')." := 'set_window_property';";
			$returnStr .= "\n".str_pad("RESULT_VALUE_ARRAY(idxNo1)",30,' ')." := '".implode('||',$params)."'";
			
			//replace the string
			$str = str_ireplace($funcToSearch.$msgStr.")",$returnStr,$str);
			$occurences = find_str_occurences($str,'FLC_SET_WINDOW_PROPERTY(');
			$pos = $occurences[0];	
		}
	}
	
	return $str;
}

function createFlcMigrationQryProcedure($schemaName, $formName,$tableName,$orderBy)
{
	$str = "procedure ".$formName."_".$tableName."_qry (NAME_ARRAY in FLC_MIGRATION_PKG.CUSTOM_ARRAY, VALUE_ARRAY in FLC_MIGRATION_PKG.CUSTOM_ARRAY,RESULT_NAME_ARRAY out FLC_MIGRATION_PKG.CUSTOM_ARRAY,RESULT_VALUE_ARRAY out FLC_MIGRATION_PKG.CUSTOM_ARRAY) is\n";
	$str = "FLC_NAME_ARRAY FLC_MIGRATION_PKG.CUSTOM_ARRAY;\n";
	$str = "FLC_VALUE_ARRAY FLC_MIGRATION_PKG.CUSTOM_ARRAY;\n";
	$str = "IdxNo number(6) := 0;\n";
	$str = "IdxNo1 number(6) := 0;\n";
	$str = "WhereStmt varchar2(4000) := null;\n";
	$str = "l_query varchar2(4000) := null;\n";
	$str = "BEGIN\n";
	$str = "WhereStmt := flc_migration_pkg.flc_migration_value(NAME_ARRAY,VALUE_ARRAY,'".$tableName."_qry');\n";
	$str = "l_query := ' select ROWIDTOCHAR(rowid) FLC_ROWID,''V'' FLC_ROWSTATUS, a.* from ".$schemaName.".".$tableName." a  '|| WhereStmt ||'  order by ".$orderBy."';\n";
	$str = "flc_migration_pkg.flc_migration_query(l_query,flc_name_array,FLC_VALUE_ARRAY);\n";
	$str = "for idxno in 1 .. flc_name_array.count\n";
	$str = "LOOP\n";
	$str = "RESULT_NAME_ARRAY(idxno) := '".$tableName."--'||flc_name_array(idxno);\n";
	$str = "RESULT_VALUE_ARRAY(idxno) := FLC_VALUE_ARRAY(idxno);\n";
	$str = "END LOOP;\n";
	$str = "END;\n";
}

$cc = renameFunctions($functionStr);
$cc = renameVariables($cc);
$cc = addHeader($cc);
$cc = addFooter($cc);
$cc = flc_ora_migration_value_func($cc);
$cc = flc_ora_builtin_message($cc);
$cc = flc_ora_builtin_set_window_property($cc);

echo '<pre>';
print_r($_SESSION['variables']);
echo '</pre>';

echo '<pre>';
print_r($cc);
echo '</pre>';


?>
