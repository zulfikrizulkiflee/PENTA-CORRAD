<?php
require_once('system_prerequisite.php');
require_once('system_impexp_func.php');

//validate that user have session
validateUserSession();

//last update
$revisedDate = '2015-04-06';
$remapUserMode = 'auto';		//interface or auto
ini_set('memory_limit','500M');
ini_set('max_execution_time','6000');
ini_set('max_input_time','6000');
$clobLength = 4000;
					
//todo
//component->componentquery
//	still ada {{{menuid}}}

//sql line ending delimiter
if(strtoupper(DBMS_NAME) == 'ORACLE')
	$sqlNewLine = '';
else if(strtoupper(DBMS_NAME) == 'MYSQL')
	$sqlNewLine = '[ENDSQLLINE]';
else if(strtoupper(DBMS_NAME) == 'SYBASE_ASE')
	$sqlNewLine = '';
else if(strtoupper(DBMS_NAME) == 'INFORMIX')
	$sqlNewLine = '[ENDSQLLINE]';

//if hidden impexp type = exp
if($_POST['hidden_impexp_type'] == 'exp' && !isset($_POST['impexp_type']))
	$_POST['impexp_type'] = $_POST['hidden_impexp_type'];

if($_POST['hidden_exp_type'] == 'exp' && !isset($_POST['hidden_exp_type']))
	$_POST['exp_type'] = $_POST['hidden_exp_type'];

if($_POST['exp_type'] == 'replace')
	$mode = 'replace';
else
	$mode = 'append';

if($_POST['remapStart'])
{
	//count how many userGroups_new[], users_new[]
	$groupsCnt = count($_POST['userGroups_new']);
	$usersCnt = count($_POST['users_new']);
	
	$debugStr = array();
	
	$deleteMappingCnt = 0;
	
	//users remapping
	for($x=0; $x < $usersCnt; $x++)
	{
		if($_POST['users'][$x] == 'nothing')
		{
			//do nothing
		}
		else if($_POST['users'][$x] == 'delete')
		{
			$deleteUser = "delete from PRUSER where USERID = ".$_POST['users_new'][$x];
			$deleteUserRs = $myQuery->query($deleteUser,'RUN');
			
			$deleteMap = "delete from FLC_USER_GROUP_MAPPING where USER_ID = ".$_POST['users_new'][$x];
			$deleteMapRs = $myQuery->query($deleteMap,'RUN');
			
			//store debug info
			$debugStr[] = $deleteUser;
			$debugStr[] = $deleteMap;
		}
		else
		{
			//check if unique constraint violated or not
			$checkTargetData = "select * from FLC_USER_GROUP_MAPPING where USER_ID = ".$_POST['users'][$x];
			$checkTargetDataRs = $myQuery->query($checkTargetData,'SELECT','NAME');
			
			$checkSourceData = "select * from FLC_USER_GROUP_MAPPING where USER_ID = ".$_POST['users_new'][$x];
			$checkSourceDataRs = $myQuery->query($checkSourceData,'SELECT','NAME');
			
			for($a=0; $a < count($checkTargetDataRs); $a++)
			{
				for($b=0; $b < count($checkTargetDataRs); $b++)
				{
					//if there's conflicting group_id, remove the new one
					if($checkTargetDataRs[$a]['GROUP_ID'] == $checkSourceDataRs[$b]['GROUP_ID'])
					{
						$deleteMapping = "delete from FLC_USER_GROUP_MAPPING 
											where USER_ID = ".$_POST['users_new'][$x]." 
											and GROUP_ID = ".$checkSourceDataRs[$b]['GROUP_ID'] ;
						$myQuery->query($deleteMapping,'RUN');
						$deleteMappingCnt++;
					}
				}
			}
			
			//remap to selected user
			$updateMap = "update FLC_USER_GROUP_MAPPING 
							set USER_ID = ".$_POST['users'][$x]." 
							where USER_ID = ".$_POST['users_new'][$x];
			$updateMapRs = $myQuery->query($updateMap,'RUN');
			
			//finally delete the user
			$deleteUser = "delete from PRUSER where USERID = ".$_POST['users_new'][$x];
			$deleteUserRs = $myQuery->query($deleteUser,'RUN');
			
			//$deleteUser = "delete from FLC_USER_GROUP_MAPPING where USERID = ".$_POST['users_new'][$x];
			//$deleteUserRs = $myQuery->query($deleteUser,'RUN');
			
			//store debug info
			$debugStr[] = 'user:'.$updateMap;
			$debugStr[] = $deleteUser;
		}
	}
	
	//groups remapping
	for($x=0; $x < $groupsCnt; $x++)
	{
		if($_POST['userGroups'][$x] == 'nothing')
		{
			//do nothing
		}
		else if($_POST['userGroups'][$x] == 'delete')
		{
			$deleteGrp = "delete from FLC_USER_GROUP where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$deleteGrpRs = $myQuery->query($deleteGrp,'RUN');
			
			$deleteMap = "delete from FLC_USER_GROUP_MAPPING where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$deleteMapRs = $myQuery->query($deleteMap,'RUN');
			
			$deletePerm = "delete from FLC_PERMISSION where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$deletePermRs = $myQuery->query($deletePerm,'RUN');
			
			//store debug info
			$debugStr[] = $deleteGrp;
			$debugStr[] = $deleteMap;
			$debugStr[] = $deletePerm;
		}
		else
		{
			//check if unique constraint violated or not
			$checkTargetData = "select * from FLC_USER_GROUP_MAPPING where GROUP_ID = ".$_POST['userGroups'][$x];
			$checkTargetDataRs = $myQuery->query($checkTargetData,'SELECT','NAME');
			
			$checkSourceData = "select * from FLC_USER_GROUP_MAPPING where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$checkSourceDataRs = $myQuery->query($checkSourceData,'SELECT','NAME');
			
			for($a=0; $a < count($checkTargetDataRs); $a++)
			{
				for($b=0; $b < count($checkSourceDataRs); $b++)
				{
					//if there's conflicting user_id, remove the new one
					if($checkTargetDataRs[$a]['USER_ID'] == $checkSourceDataRs[$b]['USER_ID'])
					{
						$deleteMapping = "delete from FLC_USER_GROUP_MAPPING 
											where GROUP_ID = ".$_POST['userGroups_new'][$x]." 
											and USER_ID = ".$checkSourceDataRs[$b]['USER_ID'] ;
						$myQuery->query($deleteMapping,'RUN');
						$deleteMappingCnt++;
					}
				}
			}
			
			//remap to selected group
			$updateMap = "update FLC_USER_GROUP_MAPPING 
							set GROUP_ID = ".$_POST['userGroups'][$x]." 
							where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$updateMapRs = $myQuery->query($updateMap,'RUN');
			
			$updatePerm = "update FLC_PERMISSION 
							set GROUP_ID = ".$_POST['userGroups'][$x]." 
							where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$updatePermRs = $myQuery->query($updatePerm,'RUN');
			
			//finally delete the group
			$deleteGrp = "delete from FLC_USER_GROUP where GROUP_ID = ".$_POST['userGroups_new'][$x];
			$deleteGrpRs = $myQuery->query($deleteGrp,'RUN');
			
			//store debug info
			$debugStr[] = $updateMap;
			$debugStr[] = $updatePerm;
			$debugStr[] = $updatePermRs;
		}
	}
	
	//update all 999999 group_id
	if(strtoupper(DBMS_NAME) == 'MYSQL' || strtoupper(DBMS_NAME) == 'ORACLE' || strtoupper(DBMS_NAME) == 'INFORMIX')
	$update99 = "update FLC_USER_GROUP_MAPPING set GROUP_ID = substr(GROUP_ID";
	
	echo '<p>';
	echo 'Total deleted unique violations: '.$deleteMappingCnt;
	echo '<br>Import and remapping process complete.';
	echo '</p>';
	
}

//if rollback is selected
if($_POST['impexp_type'] == 'roll')
{
	$rollbackList = getRollbackList();			//get list of rollback data
}

//if edit ke menu akses
if($_POST["editPermission"])
{
	$_POST['hidden_exp_type'] = $_POST['exp_type'];

	$menuList = assambleRecursiveMenu($mySQL->getMenuList($_POST['hiddenCode'],false));
	$menuListCnt = count($menuList);
}

else if($_POST['impexp_type'] == 'structure_sync')
{
	//get list of table
	$getTableList = $mySQL->listTable(DB_DATABASE);
	$getTableListCnt = count($getTableList);

	//create db sync script
	if($_POST['dbsync_create'])
	{
		$dbsync_script = '';

		//for all selected tables, get datatype, columnsize
		for($x=0; $x < count($_POST['dbsync_table']); $x++)
		{
			$getTableColumn = $mySQL->columnDatatype(DB_USERNAME,$user,$_POST['dbsync_table'][$x],'',array('size'));

			for($y=0; $y < count($getTableColumn); $y++)
				$dbsync_script .= $_POST['dbsync_table'][$x].'||'.implode('||',$getTableColumn[$y])."\r\n";
		}

		//write to file
		createDir('export_import/dbsync');
		$timestamp = date('YmdHis');
		writeDBSyncScript($timestamp,$dbsync_script);
		$dumpLink = $_SESSION['userID'].$timestamp;
		$dbSyncSuccessFlag = true;
	}
	//import db sync script
	else if($_POST['dbsync_import'])
	{
		//read the script
		$timestamp = date('YmdHis');
		$uploaddir = 'export_import/dbsync/';
		$uploadfile = $uploaddir . 'dbsync_import_'.$_SESSION['userID'].$timestamp.'.fsyc';

		if(move_uploaded_file($_FILES['importFile']['tmp_name'], $uploadfile)) {}

		$dbsync_content = explode("\r\n",file_get_contents($uploadfile));
		array_pop($dbsync_content);
		$dbsync_contentCnt = count($dbsync_content);
	}
}

//if update menu permission screen button is clicked
else if($_POST["saveScreenRefEdit"])
{
	$postCount = count($_POST);																		//count number of POST

	$getColumn_FLC_MENU = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_MENU');
	$theMenuID = getMenuID($_POST);																	//get list of menu ids
	$countTheMenuID = count($theMenuID);															//count the menu id

	//if not set, set the array
	if(!isset($_SESSION['system_impexp']))
	{
		$_SESSION['system_impexp'] = array();
		$_SESSION['system_impexp_full'] = array();
	}

	//reset the array
	$_SESSION['system_impexp'][$_POST['hiddenCode']] = array();

	//store menu ids in session
	for($x=0; $x < $countTheMenuID; $x++)
	{
		//if not in array, add to array
		if(!in_array($theMenuID[$x],$_SESSION['system_impexp'][$_POST['hiddenCode']]))
		{
			$_SESSION['system_impexp'][$_POST['hiddenCode']][] =  $theMenuID[$x];
			$_SESSION['system_impexp_full'][] = $theMenuID[$x];
		}
	}

	$_POST["showScreen"] = "some value";
}

//if reset export selection button
else if($_POST['resetExportSelection'])
	resetExportSelection();

//if export module button
else if($_POST['exportModule'])
{
	$menuIDList = array();
	$pageIDList = array();
	$componentIDList = array();
	$componentItemIDList = array();
	$chartIDList = array();
	$controlIDList = array();
	$triggerPageIDList = array();
	$triggerComponentIDList = array();
	$triggerItemIDList = array();
	$triggerControlIDList = array();
	$groupIDList = array();
	$userIDList = array();

	$allBlName = array();
	$allBlID = array();
	$allPermID = array();

	$pageToPrepend = array();
	$compQueryToPrepend = array();
	$itemToPrepend = array();
	$blToPrepend = array();

	$clobUpdate = array();

	createDir('export_import');
	$timestamp = date('YmdHis');

	//to differentiate export type
	if($_POST['exp_type'] == 'append')
		$fileExtension = 'a';
	else if($_POST['exp_type'] == 'replace')
		$fileExtension = 'r';
	
	//PRE EXPORT SYSTEM OPTIMIZATION
	$deleteOrphanGroupMapping = "delete from FLC_USER_GROUP_MAPPING where GROUP_ID not in (select GROUP_ID from FLC_USER_GROUP)";	
	$myQuery->query($deleteOrphanGroupMapping,'RUN');
	
	$deleteOrphanUserMapping = "delete from FLC_USER_GROUP_MAPPING where USER_ID not in (select USERID from PRUSER)";
	$myQuery->query($deleteOrphanUserMapping,'RUN');
	
	$deleteOrphanPermission = "delete from FLC_PERMISSION where GROUP_ID not in (select GROUP_ID from FLC_USER_GROUP)";
	$myQuery->query($deleteOrphanPermission,'RUN');
	
	//------------------------------------------------------------------------------------------------------------------------------
	//EXPORTING THE MENU
	//------------------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_MENU = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_MENU');

	$tableKeyName = array('MENUID','MENUPARENT');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_MENU); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_MENU[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_MENU[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_MENU[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_MENU[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_MENU[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_MENU[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_MENU[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_MENU[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_MENU[$x]['DATA_TYPE'] = 'VARCHAR';

			if($getColumn_FLC_MENU[$x]['DATA_TYPE'] == '12')
				$getColumn_FLC_MENU[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_MENU[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_MENU[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_MENU[$x]['DATA_TYPE'];
	}

	//count all menu ids
	$countTheMenuID = count($_SESSION['system_impexp_full']);

	//for all menu ids
	for($x=0; $x < $countTheMenuID; $x++)
	{
		$menuIDList[] = $_SESSION['system_impexp_full'][$x];
		$theItemRs = $myQuery->query("select ".implode(',',$columnName)." from FLC_MENU where MENUID = ".$_SESSION['system_impexp_full'][$x],'SELECT','INDEX');

		//get prepared values
		$values = padWithQuote($dataType,$theItemRs,$mode,$tableKeyIndex,$columnName);

		$getQry_FLC_MENU[] = "insert into FLC_MENU (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
	}
	writeToDump($timestamp,$getQry_FLC_MENU,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING THE PAGE
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_PAGE = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_PAGE');

	$tablePKName = 'PAGEID';
	$tablePKIndex = 0;

	$tableKeyName = array('PAGEID','MENUID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_PAGE); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_PAGE[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_PAGE[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_PAGE[$x]['DATA_TYPE'] = 'VARCHAR';

			if($getColumn_FLC_PAGE[$x]['DATA_TYPE'] == '0')
				$getColumn_FLC_PAGE[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_PAGE[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		if($getColumn_FLC_PAGE[$x]['COLUMN_NAME'] == $tablePKName)
			$tablePKIndex = $x;

		$columnName[] = $getColumn_FLC_PAGE[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_PAGE[$x]['DATA_TYPE'];
	}

	//for all menu ids
	for($x=0; $x < $countTheMenuID; $x++)
	{
		$theItemRs = $myQuery->query("select ".implode(',',$columnName)." from FLC_PAGE where MENUID = ".$_SESSION['system_impexp_full'][$x],'SELECT','INDEX');
		$getPageIDRs = $myQuery->query("select PAGEID from FLC_PAGE where MENUID = ".$_SESSION['system_impexp_full'][$x],'SELECT','INDEX');

		//if page id is exist
		if($getPageIDRs[0][0] != '')
			$pageIDList[] = $getPageIDRs[0][0];

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			//get prepared values
			$values = padWithQuote($dataType,$theItemRs,$mode,$tableKeyIndex,$columnName);

			for($a=0; $a < count($values); $a++)
			{
				if(strlen($values[$a]) > $clobLength)
				{
					$pageToPrepend[] = "myClobVarPage".$theItemRs[$y][0]." CLOB := ".substr($values[$a],0,$clobLength)."';\r\n";
					$clobUpdate[] = createUpdateLoopStatement('FLC_PAGE',$columnName[$a],$values[$a],$clobLength,'PAGEID',$values[array_search('PAGEID',$columnName)]);
					$values[$a] = "myClobVarPage".$theItemRs[$y][0];
				}
			}

			//prepare the insert statement
			$getQry_FLC_PAGE[] = "insert into FLC_PAGE (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
		}
	}
	writeToDump($timestamp,$getQry_FLC_PAGE,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING THE PAGE COMPONENT
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_PAGE_COMPONENT = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_PAGE_COMPONENT');

	$tablePKName = 'COMPONENTID';
	$tablePKIndex = 0;

	$tableKeyName = array('COMPONENTID','PAGEID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_PAGE_COMPONENT); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] = 'VARCHAR';

			if($getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] == '12' )
				$getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_PAGE_COMPONENT[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_PAGE_COMPONENT[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_PAGE_COMPONENT[$x]['DATA_TYPE'];
	}

	//for all page ids
	for($x=0; $x < count($pageIDList); $x++)
	{
		//get the page component associated with the pageid
		$theItem = "select ".implode(',',$columnName)." from FLC_PAGE_COMPONENT where PAGEID = ".$pageIDList[$x];
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//get component id
		$getComponentID = "select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$pageIDList[$x];
		$getComponentIDRs = $myQuery->query($getComponentID,'SELECT','INDEX');

		for($y=0; $y < count($getComponentIDRs); $y++)
		{
			//if component id is exist
			if($getComponentIDRs[$y][0] != '')
				$componentIDList[] = $getComponentIDRs[$y][0];
		}

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];

				//get prepared values
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				if(DBMS_NAME == 'mysql' || DBMS_NAME == 'oracle')
				{
				for($a=0; $a < count($values); $a++)
				{
					if(strlen($values[$a]) > $clobLength)
					{
						$compQueryToPrepend[] = "myClobVarCompQry".$theItemRs[$y][0]." CLOB := ".substr($values[$a],0,$clobLength)."';\r\n";
						$clobUpdate[] = createUpdateLoopStatement('FLC_PAGE_COMPONENT',$columnName[$a],$values[$a],$clobLength,'COMPONENTID',$values[array_search('COMPONENTID',$columnName)]);
						$values[$a] = "myClobVarCompQry".$theItemRs[$y][0];
					}
				}

				//prepare the insert statement
				$getQry_FLC_PAGE_COMPONENT[] = "insert into FLC_PAGE_COMPONENT (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
				else if(DBMS_NAME == 'informix')
				{
					
					$valueID = 0;
					
					for($i=0; $i<count($columnName); $i++)
					{
						if($columnName[$i]=='COMPONENTID')
						{
							$valueID = $values[$i];	
						}
					}

					for($a=0; $a<count($columnName); $a++)
					{
						if($columnName[$a]=='COMPONENTTYPEQUERY')
						{
							$value = array_splice($values, $a, 1)[0];
						}
					}

					$columnN = implode(',',$columnName);
					$columnN = str_replace("COMPONENTTYPEQUERY,"," ", $columnN);
					//prepare the insert statement
					$getQry_FLC_PAGE_COMPONENT[] = "%%%% insert into FLC_PAGE_COMPONENT (".$columnN.") 
					values (".implode(',',$values)."); \r\n";		//append insert stmt to arr
					$getQry_FLC_PAGE_COMPONENT[] = " |||| ".substr($value, 1, -1)." \r\n";
					$getQry_FLC_PAGE_COMPONENT[] = " #### update FLC_PAGE_COMPONENT set COMPONENTTYPEQUERY = :unlimitedChar where COMPONENTID = ".$valueID.";".$sqlNewLine."\r\n";


				}// eof else if(DBMS_NAME == 'informix')
				
			} // eof for($y=0; $y < count($theItemRs); $y++)
		}
	}
	writeToDump($timestamp,$getQry_FLC_PAGE_COMPONENT,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING THE PAGE COMPONENT ITEMS
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_PAGE_COMPONENT_ITEMS = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_PAGE_COMPONENT_ITEMS');

	$tablePKName = 'ITEMID';
	$tablePKIndex = 0;

	$tableKeyName = array('COMPONENTID','ITEMID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_PAGE_COMPONENT_ITEMS); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] = 'VARCHAR';

			if($getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] == '12' )
				$getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_PAGE_COMPONENT_ITEMS[$x]['DATA_TYPE'];
	}

	//for all component, get ITEM
	for($x=0; $x < count($componentIDList); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_PAGE_COMPONENT_ITEMS where COMPONENTID = ".$componentIDList[$x];
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//get component id
		$getComponentItemID = "select ITEMID from FLC_PAGE_COMPONENT_ITEMS where COMPONENTID = ".$componentIDList[$x];
		$getComponentItemIDRs = $myQuery->query($getComponentItemID,'SELECT','INDEX');

		for($y=0; $y < count($getComponentItemIDRs); $y++)
		{
			if($getComponentItemIDRs[$y][0] != '')
				$componentItemIDList[] = $getComponentItemIDRs[$y][0];
		}

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				if(DBMS_NAME == 'mysql' || DBMS_NAME == 'oracle')
				{
				for($a=0; $a < count($values); $a++)
				{
					if(strlen($values[$a]) > $clobLength)
					{
						$randomStr = rand();
						$itemToPrepend[] = "myClobVarItem_".$randomStr." CLOB := ".substr($values[$a],0,$clobLength)."';\r\n";
						$clobUpdate[] = createUpdateLoopStatement('FLC_PAGE_COMPONENT_ITEMS',$columnName[$a],$values[$a],$clobLength,'ITEMID',$values[array_search('ITEMID',$columnName)]);
						$values[$a] = "myClobVarItem_".$randomStr;
					}
				}

				//prepare the insert statement
				$getQry_FLC_PAGE_COMPONENT_ITEMS[] = "insert into FLC_PAGE_COMPONENT_ITEMS (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
				else if(DBMS_NAME == 'informix')
				{
					$valueID = 0;
					
					for($i=0; $i<count($columnName); $i++)
					{
						if($columnName[$i]=='ITEMID')
						{
							$valueID = $values[$i];	
						}
					}

					for($a=0; $a<count($columnName); $a++)
					{
						if($columnName[$a]=='ITEMLOOKUP')
						{
							$value = array_splice($values, $a, 1)[0];
						}
					}

					for($b=0; $b<count($columnName); $b++)
					{
						if($columnName[$b]=='ITEMDEFAULTVALUE')
						{
							$value2 = array_splice($values, $b, 1)[0];
						}
					}

					$columnN = implode(',',$columnName);
					$columnReplace = array("ITEMLOOKUP,", "ITEMDEFAULTVALUE,");
					$columnN = str_replace($columnReplace," ", $columnN);
					//prepare the insert statement
					$getQry_FLC_PAGE_COMPONENT_ITEMS[] = "%%%% insert into FLC_PAGE_COMPONENT_ITEMS (".$columnN.") 
					values (".implode(',',$values)."); \r\n";		//append insert stmt to arr
					$getQry_FLC_PAGE_COMPONENT_ITEMS[] = " |||| ".substr($value, 1, -1)." \r\n";
					$getQry_FLC_PAGE_COMPONENT_ITEMS[] = " #### update FLC_PAGE_COMPONENT_ITEMS set ITEMLOOKUP = :unlimitedChar where ITEMID = ".$valueID.";\r\n";
					$getQry_FLC_PAGE_COMPONENT_ITEMS[] = " |||| ".substr($value2, 1, -1)." \r\n";
					$getQry_FLC_PAGE_COMPONENT_ITEMS[] = " #### update FLC_PAGE_COMPONENT_ITEMS set ITEMDEFAULTVALUE = :unlimitedChar where ITEMID = ".$valueID.";".$sqlNewLine."\r\n";


				}// eof else if(DBMS_NAME == 'informix')

			} //  eof for($y=0; $y < count($theItemRs); $y++)
		}
	}
	writeToDump($timestamp,$getQry_FLC_PAGE_COMPONENT_ITEMS,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING PAGE CONTROL
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_PAGE_CONTROL = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_PAGE_CONTROL');

	$tablePKName = 'CONTROLID';
	$tablePKIndex = 0;

	$tableKeyName = array('PAGEID','CONTROLID','COMPONENTID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_PAGE_CONTROL); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'] = 'VARCHAR';
		}
		if(in_array($getColumn_FLC_PAGE_CONTROL[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_PAGE_CONTROL[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_PAGE_CONTROL[$x]['DATA_TYPE'];
	}

	//for all page ids
	for($x=0; $x < count($pageIDList); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_PAGE_CONTROL where PAGEID = ".$pageIDList[$x];
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//get control id
		$getControlID = "select CONTROLID from FLC_PAGE_CONTROL where PAGEID = ".$pageIDList[$x];
		$getControlIDRs = $myQuery->query($getControlID,'SELECT','INDEX');

		for($y=0; $y < count($getControlIDRs); $y++)
		{
			if($getControlIDRs[$y][0] != '')
				$controlIDList[] = $getControlIDRs[$y][0];
		}

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				//prepare the insert statement
				$getQry_FLC_PAGE_CONTROL[] = "insert into FLC_PAGE_CONTROL (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
		}
	}
	writeToDump($timestamp,$getQry_FLC_PAGE_CONTROL,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING CHART
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_CHART = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_CHART');

	$tablePKName = 'ITEM_ID';
	$tablePKIndex = 0;

	$tableKeyName = array('ITEM_ID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_CHART); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_CHART[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_CHART[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_CHART[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_CHART[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_CHART[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_CHART[$x]['DATA_TYPE'] = 'VARCHAR';
		}
		if(in_array($getColumn_FLC_CHART[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_CHART[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_CHART[$x]['DATA_TYPE'];
	}

	//for all item ids
	for($x=0; $x < count($componentItemIDList); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_CHART where ITEM_ID = ".$componentItemIDList[$x];
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//get chart id
		$getChartID = "select ITEM_ID from FLC_CHART where ITEM_ID = ".$componentItemIDList[$x];
		$getChartIDRs = $myQuery->query($getChartID,'SELECT','INDEX');

		for($y=0; $y < count($getChartIDRs); $y++)
		{
			if($getChartIDRs[$y][0] != '')
				$chartIDList[] = $getChartIDRs[$y][0];
		}

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				//prepare the insert statement
				$getQry_FLC_CHART[] = "insert into FLC_CHART (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
		}
	}
	writeToDump($timestamp,$getQry_FLC_CHART,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING TRIGGER
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_TRIGGER = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_TRIGGER');

	$tablePKName = 'TRIGGER_ID';
	$tablePKIndex = 0;
	$tableKeyName = array('TRIGGER_ID');
	$tableKeyIndex = array();
	$tableSpecialKey = array('TRIGGER_ITEM_TYPE');
	$tableSpecialKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_TRIGGER); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_TRIGGER[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_TRIGGER[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_TRIGGER[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_TRIGGER[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_TRIGGER[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_TRIGGER[$x]['DATA_TYPE'] = 'VARCHAR';
		}
		if(in_array($getColumn_FLC_TRIGGER[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		if(in_array($getColumn_FLC_TRIGGER[$x]['COLUMN_NAME'],$tableSpecialKey))
			$tableSpecialKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_TRIGGER[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_TRIGGER[$x]['DATA_TYPE'];
	}

	//get page triggerid
	for($x=0; $x < count($pageIDList); $x++)
	{
		$getTriggerPageID = "select TRIGGER_ID from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'page' and TRIGGER_ITEM_ID = ".$pageIDList[$x];
		$getTriggerPageIDRs = $myQuery->query($getTriggerPageID,'SELECT','INDEX');

		for($y=0; $y < count($getTriggerPageIDRs); $y++)
			$triggerPageIDList[] = $getTriggerPageIDRs[$y][0];
	}

	//get component triggerid
	for($x=0; $x < count($componentIDList); $x++)
	{
		$getTriggerComponentID = "select TRIGGER_ID from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'component' and TRIGGER_ITEM_ID = ".$componentIDList[$x];
		$getTriggerComponentIDRs = $myQuery->query($getTriggerComponentID,'SELECT','INDEX');

		for($y=0; $y < count($getTriggerComponentIDRs); $y++)
			$triggerComponentIDList[] = $getTriggerComponentIDRs[$y][0];
	}

	//get item triggerid
	for($x=0; $x < count($componentItemIDList); $x++)
	{
		$getTriggerComponentItemID = "select TRIGGER_ID from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'item' and TRIGGER_ITEM_ID = ".$componentItemIDList[$x];
		$getTriggerComponentItemIDRs = $myQuery->query($getTriggerComponentItemID,'SELECT','INDEX');

		for($y=0; $y < count($getTriggerComponentItemIDRs); $y++)
			$triggerItemIDList[] = $getTriggerComponentItemIDRs[$y][0];
	}

	//get control triggerid
	for($x=0; $x < count($controlIDList); $x++)
	{
		$getTriggerControlID = "select TRIGGER_ID from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'control' and TRIGGER_ITEM_ID = ".$controlIDList[$x];
		$getTriggerControlIDRs = $myQuery->query($getTriggerControlID,'SELECT','INDEX');

		for($y=0; $y < count($getTriggerControlIDRs); $y++)
			$triggerControlIDList[] = $getTriggerControlIDRs[$y][0];
	}

	$allTrigger = array_merge($triggerPageIDList,$triggerComponentIDList,$triggerItemIDList,$triggerControlIDList);

	//for all page ids
	for($x=0; $x < count($allTrigger); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_TRIGGER where TRIGGER_ID = ".$allTrigger[$x];
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];
				$allBlName[] = $theItemRs[$y][3];

				//get prepared values
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName,'FLC_TRIGGER',$tableSpecialKeyIndex);

				//prepare the insert statement
				$getQry_FLC_TRIGGER[] = "insert into FLC_TRIGGER (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
		}
	}
	writeToDump($timestamp,$getQry_FLC_TRIGGER,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING TRIGGER PARAMETER
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_TRIGGER_PARAMETER = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_TRIGGER_PARAMETER');

	$tablePKName = 'TRIGGER_ID';
	$tablePKIndex = 0;

	$tempArray = array();
	$tempArrayIndex = array();

	$tableKeyName = array('TRIGGER_ID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_TRIGGER_PARAMETER); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] == '12')
				$getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'] = 'TEXT';
		}		
		if(in_array($getColumn_FLC_TRIGGER_PARAMETER[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_TRIGGER_PARAMETER[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_TRIGGER_PARAMETER[$x]['DATA_TYPE'];
	}

	//for all page ids
	for($x=0; $x < count($allTrigger); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_TRIGGER_PARAMETER where TRIGGER_ID = ".$allTrigger[$x];
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];

				//get prepared values
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				//prepare the insert statement
				$getQry_FLC_TRIGGER_PARAMETER[] = "insert into FLC_TRIGGER_PARAMETER (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
		}
	}
	writeToDump($timestamp,$getQry_FLC_TRIGGER_PARAMETER,0,0,$fileExtension);			//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING BL
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_BL = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_BL');

	$tableKeyName = array('BLID');
	$tableKeyIndex = array();

	$allBlName = array_unique($allBlName); 		//remove duplicates
	$allBlName = array_values($allBlName); 		//remove duplicates

	//get columnName
	for($x=0; $x < count($getColumn_FLC_BL); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_BL[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_BL[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_BL[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_BL[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_BL[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_BL[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_BL[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_BL[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_BL[$x]['DATA_TYPE'] = 'VARCHAR';

			if($getColumn_FLC_BL[$x]['DATA_TYPE'] == '10')
				$getColumn_FLC_BL[$x]['DATA_TYPE'] = 'DATETIME';

			if($getColumn_FLC_BL[$x]['DATA_TYPE'] == '7')
				$getColumn_FLC_BL[$x]['DATA_TYPE'] = 'DATE';

			if($getColumn_FLC_BL[$x]['DATA_TYPE'] == '12')
				$getColumn_FLC_BL[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_BL[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_BL[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_BL[$x]['DATA_TYPE'];
	}

	for($x=0; $x < count($allBlName); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_BL where BLNAME = '".$allBlName[$x]."' and BLPARENT is null";
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];
				$allBlID[] = $theItemRs[$y][0];

				//get prepared values
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				if(DBMS_NAME == 'mysql' || DBMS_NAME == 'oracle')
				{
				for($a=0; $a < count($values); $a++)
				{
					if(strlen($values[$a]) > $clobLength)
					{
						$blToPrepend[] = "myClobVarBL".$theItemRs[$y][0]." CLOB := ".substr($values[$a],0,$clobLength)."';\r\n";
						$clobUpdate[] = createUpdateLoopStatement('FLC_BL',$columnName[$a],$values[$a],$clobLength,'BLID',$values[array_search('BLID',$columnName)]);
						$values[$a] = "myClobVarBL".$theItemRs[$y][0];
					}
					}
				}

				if(DBMS_NAME == 'mysql' || DBMS_NAME == 'oracle')
				{
				//prepare the insert statement
				$getQry_FLC_BL[] = "insert into FLC_BL (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
				else if(DBMS_NAME == 'informix')
				{
					$valueID = 0;
					for($i=0; $i<count($columnName); $i++)
					{
						if($columnName[$i]=='BLID')
						{
							$valueID = $values[$i];	
						}
					}

					for($a=0; $a<count($columnName); $a++)
					{
						if($columnName[$a]=='BLDETAIL')
						{
							$value = array_splice($values, $a, 1)[0];					
						}
					}

					$columnN = implode(',',$columnName);
					$columnN = str_replace(",BLDETAIL"," ", $columnN);
					//prepare the insert statement
					$getQry_FLC_BL[] = "%%%% insert into FLC_BL (".$columnN.") 
					values (".implode(',',$values)."); \r\n";		//append insert stmt to arr
					$getQry_FLC_BL[] = " |||| ".substr($value, 1, -1)."; \r\n";
					$getQry_FLC_BL[] = " #### update FLC_BL set BLDETAIL = :unlimitedChar where BLID = ".$valueID.";".$sqlNewLine."\r\n";


				}// end of else if(DBMS_NAME == 'informix')
				
			}
		}
	}

	writeToDump($timestamp,$getQry_FLC_BL,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING BL PARAMETER
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_BL_PARAMETER = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_BL_PARAMETER');

	$tempArray = array();
	$tempArrayIndex = array();

	$tableKeyName = array('BL_ID');
	$tableKeyIndex = array();

	$allBlID = array_unique($allBlID); //remove duplicates

	//get columnName
	for($x=0; $x < count($getColumn_FLC_BL_PARAMETER); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] = 'INTEGER';
			
			if($getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] == '12')
				$getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'] = 'TEXT';
	
		}
		if(in_array($getColumn_FLC_BL_PARAMETER[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_BL_PARAMETER[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_BL_PARAMETER[$x]['DATA_TYPE'];
	}

	for($x=0; $x < count($allBlID); $x++)
	{
		//get the item
		$theItem = "select ".implode(',',$columnName)." from FLC_BL_PARAMETER where BL_ID = '".$allBlID[$x]."'";
		$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

		//if theres result, create insert statement
		if(count($theItemRs) > 0)
		{
			for($y=0; $y < count($theItemRs); $y++)
			{
				$tempItem[0] = $theItemRs[$y];

				//get prepared values
				$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

				//prepare the insert statement
				$getQry_FLC_BL_PARAMETER[] = "insert into FLC_BL_PARAMETER (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
			}
		}
	}
	writeToDump($timestamp,$getQry_FLC_BL_PARAMETER,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING FLC_PERMISSION (FOR MENU/COMPONENT/ITEM/CONTROL)
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_PERMISSION = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_PERMISSION');

	$tableKeyName = array('PERM_ID','GROUP_ID','PERM_ITEM');
	$tableKeyIndex = array();
	$tableSpecialKey = array('PERM_TYPE');
	$tableSpecialKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_PERMISSION); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_MENU[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '6')
				$getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] = 'SERIAL';

			if($getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] = 'VARCHAR';

			if($getColumn_FLC_PERMISSION[$x]['DATA_TYPE'] == '12')
				$getColumn_FLC_MENU[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_PERMISSION[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		if(in_array($getColumn_FLC_PERMISSION[$x]['COLUMN_NAME'],$tableSpecialKey))
			$tableSpecialKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_PERMISSION[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_PERMISSION[$x]['DATA_TYPE'];
	}

	//get all related id lists and combine in an array
	$allRelatedIDS = array($menuIDList,$componentIDList,$componentItemIDList,$controlIDList);
	$allRelatedIDSKey = array('menu','component','item','control');

	for($a=0; $a < count($allRelatedIDS); $a++)
	{
		for($x=0; $x < count($allRelatedIDS[$a]); $x++)
		{
			//get the item
			$theItem = "select ".implode(',',$columnName)." from FLC_PERMISSION where PERM_ITEM = '".$allRelatedIDS[$a][$x]."' and PERM_TYPE = '".$allRelatedIDSKey[$a]."'";
			$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

			//if theres result, create insert statement
			if(count($theItemRs) > 0)
			{
				for($y=0; $y < count($theItemRs); $y++)
				{
					$tempItem[0] = $theItemRs[$y];
					$allPermID[] = $theItemRs[$y][0];

					//get prepared values
					$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName,'FLC_PERMISSION',$tableSpecialKeyIndex);

					//prepare the insert statement
					$getQry_FLC_PERMISSION[] = "insert into FLC_PERMISSION (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
				}
			}
		}
	}
	writeToDump($timestamp,$getQry_FLC_PERMISSION,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();
	
	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING FLC_USER_GROUP_MAPPING
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_USER_GROUP_MAPPING = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_USER_GROUP_MAPPING');
	
	$tableKeyName = array('GROUP_ID','USER_ID');
	$tableKeyIndex = array('GROUP_ID');
	
	//get columnName
	for($x=0; $x < count($getColumn_FLC_USER_GROUP_MAPPING); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_USER_GROUP_MAPPING[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_USER_GROUP_MAPPING[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_USER_GROUP_MAPPING[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_USER_GROUP_MAPPING[$x]['DATA_TYPE'] == '7')
				$getColumn_FLC_USER_GROUP_MAPPING[$x]['DATA_TYPE'] = 'DATE';
		}
		if(in_array($getColumn_FLC_USER_GROUP_MAPPING[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_USER_GROUP_MAPPING[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_USER_GROUP_MAPPING[$x]['DATA_TYPE'];
	}
	
	$theGroupMappingRs = $myQuery->query("select ".implode(',',$columnName)." from FLC_USER_GROUP_MAPPING",'SELECT','INDEX');
		
	//if theres result, create insert statement
	if(count($theGroupMappingRs) > 0)
	{
		for($y=0; $y < count($theGroupMappingRs); $y++)
		{
			$tempItem[0] = $theGroupMappingRs[$y];

			//get prepared values
			$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

			//prepare the insert statement
			$getQry_FLC_USER_GROUP_MAPPING[] = "insert into FLC_USER_GROUP_MAPPING (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
		}
	}

	writeToDump($timestamp,$getQry_FLC_USER_GROUP_MAPPING,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();
	
	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING PRUSER
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_PRUSER = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'PRUSER');
	
	$tableKeyName = array('USERID');
	$tableKeyIndex = array();
	
	//get columnName
	for($x=0; $x < count($getColumn_PRUSER); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_PRUSER[$x]['DATA_TYPE'] == '2' || $getColumn_PRUSER[$x]['DATA_TYPE'] == '258')
				$getColumn_PRUSER[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_PRUSER[$x]['DATA_TYPE'] == '7')
				$getColumn_PRUSER[$x]['DATA_TYPE'] = 'DATE';

			if($getColumn_PRUSER[$x]['DATA_TYPE'] == '13' || $getColumn_PRUSER[$x]['DATA_TYPE'] == '269')
				$getColumn_PRUSER[$x]['DATA_TYPE'] = 'VARCHAR';
		}
		if(in_array($getColumn_PRUSER[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_PRUSER[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_PRUSER[$x]['DATA_TYPE'];
	}
	
	//get all userid
	$userRs = $myQuery->query("select USERID from PRUSER",'SELECT','INDEX');
	
	for($x=0; $x < count($userRs); $x++)
		$userIDList[] = $userRs[$x][0];
		
	//get all userid details
	$userRs = $myQuery->query("select ".implode(',',$columnName)." from PRUSER",'SELECT','INDEX');
		
	//if theres result, create insert statement
	if(count($userRs) > 0)
	{
		for($y=0; $y < count($userRs); $y++)
		{
			$tempItem[0] = $userRs[$y];

			//get prepared values
			$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName,'PRUSER','USERNAME','FLCIMPTEMP_');

			//prepare the insert statement
			$getQry_PRUSER[] = "insert into PRUSER (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
		}
	}

	writeToDump($timestamp,$getQry_PRUSER,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();
	
	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING FLC_USER_GROUP
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_USER_GROUP = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_USER_GROUP');
	
	$tableKeyName = array('GROUP_ID');
	$tableKeyIndex = array('GROUP_ID');
	
	//get columnName
	for($x=0; $x < count($getColumn_FLC_USER_GROUP); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] == '7')
				$getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] = 'DATE';

			if($getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'] = 'VARCHAR';
		}
		if(in_array($getColumn_FLC_USER_GROUP[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_USER_GROUP[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_USER_GROUP[$x]['DATA_TYPE'];
	}
	
	//get all user groups 
	$theGroupRs = $myQuery->query("select ".implode(',',$columnName)." from FLC_USER_GROUP",'SELECT','INDEX');
	
	//get group id
	$getGroupID = "select GROUP_ID from FLC_USER_GROUP";
	$getGroupIDRs = $myQuery->query($getGroupID,'SELECT','NAME');
	
	for($x=0; $x < count($getGroupIDRs); $x++)
		$groupIDList[] = $getGroupIDRs[$x]['GROUP_ID'];
	
	//if theres result, create insert statement
	if(count($theGroupRs) > 0)
	{
		for($y=0; $y < count($theGroupRs); $y++)
		{
			$tempItem[0] = $theGroupRs[$y];

			//get prepared values
			$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

			//prepare the insert statement
			$getQry_FLC_USER_GROUP[] = "insert into FLC_USER_GROUP (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
		}
	}

	writeToDump($timestamp,$getQry_FLC_USER_GROUP,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING FLC_EXTENDED_ATTR
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_EXTENDED_ATTR = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_EXTENDED_ATTR');

	$tableKeyName = array('ATTR_ID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_EXTENDED_ATTR); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] == '7')
				$getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] = 'DATE';
		
			if($getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] == '13' || $getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] == '269')
				$getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'] = 'VARCHAR';
		}
		if(in_array($getColumn_FLC_EXTENDED_ATTR[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_EXTENDED_ATTR[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_EXTENDED_ATTR[$x]['DATA_TYPE'];
	}

	//get the item
	$theItem = "select ".implode(',',$columnName)." from FLC_EXTENDED_ATTR";
	$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

	//if theres result, create insert statement
	if(count($theItemRs) > 0)
	{
		for($y=0; $y < count($theItemRs); $y++)
		{
			$tempItem[0] = $theItemRs[$y];
			$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

			//prepare the insert statement
			$getQry_FLC_EXTENDED_ATTR[] = "insert into FLC_EXTENDED_ATTR (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
		}
	}

	writeToDump($timestamp,$getQry_FLC_EXTENDED_ATTR,0,0,$fileExtension);

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//EXPORTING FLC_EXTENDED_ATTR_VAL
	//---------------------------------------------------------------------------------------------------------------------
	$getColumn_FLC_EXTENDED_ATTR_VAL = getColumnList($myQuery,DBMS_NAME,DB_DATABASE,DB_USERNAME,'FLC_EXTENDED_ATTR_VAL');

	$tableKeyName = array('ATTR_ID','ATTR_PARENT_ID');
	$tableKeyIndex = array();

	//get columnName
	for($x=0; $x < count($getColumn_FLC_EXTENDED_ATTR_VAL); $x++)
	{
		if(DBMS_NAME == 'informix')
		{
			if($getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] == '0' || $getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] == '256')
				$getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] = 'CHAR';

			if($getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] == '2' || $getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] == '258')
				$getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] = 'INTEGER';

			if($getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] == '12')
				$getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'] = 'TEXT';
		}
		if(in_array($getColumn_FLC_EXTENDED_ATTR_VAL[$x]['COLUMN_NAME'],$tableKeyName))
			$tableKeyIndex[] = $x;

		$columnName[] = $getColumn_FLC_EXTENDED_ATTR_VAL[$x]['COLUMN_NAME'];
		$dataType[] = $getColumn_FLC_EXTENDED_ATTR_VAL[$x]['DATA_TYPE'];
	}

	//combine all IDs
	$allID = array($menuIDList,$pageIDList,$componentIDList,$componentItemIDList,$controlIDList);
	$allIDKey = array('FLC_MENU','FLC_PAGE','FLC_PAGE_COMPONENT','FLC_PAGE_COMPONENT_ITEMS','FLC_PAGE_CONTROL');

	for($k=0; $k < count($allID); $k++)
	{
		//for all related ids, get data from the table
		for($x=0; $x < count($allID[$k]); $x++)
		{
			//get the item
			$theItem = "select ".implode(',',$columnName)." from FLC_EXTENDED_ATTR_VAL
						where ATTR_ID in (select ATTR_ID from FLC_EXTENDED_ATTR where ATTR_PARENT_TABLE = '".$allIDKey[$k]."')
						and ATTR_PARENT_ID = ".$allID[$k][$x];
			$theItemRs = $myQuery->query($theItem,'SELECT','INDEX');

			//if theres result, create insert statement
			if(count($theItemRs) > 0)
			{
				for($y=0; $y < count($theItemRs); $y++)
				{
					$tempItem[0] = $theItemRs[$y];
					$values = padWithQuote($dataType,$tempItem,$mode,$tableKeyIndex,$columnName);

					for($a=0; $a < count($values); $a++)
					{
						if(strlen($values[$a]) > $clobLength)
						{
							$randomStr = rand();
							$itemToPrepend[] = "myClobExtVal_".$randomStr." CLOB := ".substr($values[$a],0,$clobLength)."';\r\n";
							$clobUpdate[] = createUpdateLoopStatement('FLC_EXTENDED_ATTR_VAL',$columnName[$a],$values[$a],$clobLength,'ITEMID',$values[array_search('ITEMID',$columnName)]);
							$values[$a] = "myClobExtVal_".$randomStr;
						}
					}

					//prepare the insert statement
					$getQry_FLC_EXTENDED_ATTR_VAL[] = "insert into FLC_EXTENDED_ATTR_VAL (".implode(',',$columnName).") values (".implode(',',$values).");".$sqlNewLine."\r\n";		//append insert stmt to arr
				}
			}
		}
	}

	writeToDump($timestamp,$getQry_FLC_EXTENDED_ATTR_VAL,0,0,$fileExtension);				//write to file

	//reset
	$columnName = array();
	$dataType = array();

	//---------------------------------------------------------------------------------------------------------------------
	//DELETE ALL EXISTING IDS
	//---------------------------------------------------------------------------------------------------------------------
	//merge all trigger_id
	$allTriggerID = array_merge($triggerPageIDList,$triggerComponentIDList,$triggerItemIDList,$triggerControlIDList);

	for($x=0; $x < count($menuIDList); $x++)
		$deleteMenu[] = "delete from FLC_MENU where MENUID=".$menuIDList[$x].";".$sqlNewLine."\r\n";

	for($x=0; $x < count($pageIDList); $x++)
		$deletePage[] = "delete from FLC_PAGE where PAGEID=".$pageIDList[$x].";".$sqlNewLine."\r\n";

	for($x=0; $x < count($componentIDList); $x++)
		$deleteComponent[] = "delete from FLC_PAGE_COMPONENT where COMPONENTID=".$componentIDList[$x].";".$sqlNewLine."\r\n";

	for($x=0; $x < count($componentItemIDList); $x++)
		$deleteItem[] = "delete from FLC_PAGE_COMPONENT_ITEMS where ITEMID=".$componentItemIDList[$x].";".$sqlNewLine."\r\n";

	for($x=0; $x < count($controlIDList); $x++)
		$deleteControl[] = "delete from FLC_PAGE_CONTROL where CONTROLID=".$controlIDList[$x].";".$sqlNewLine."\r\n";

	for($x=0; $x < count($chartIDList); $x++)
		$deleteChart[] = "delete from FLC_CHART where ITEM_ID=".$chartIDList[$x].";".$sqlNewLine."\r\n";

	for($x=0; $x < count($allTriggerID); $x++)
	{
		$deleteTrigger[] = "delete from FLC_TRIGGER where TRIGGER_ID=".$allTriggerID[$x].";".$sqlNewLine."\r\n";
		$deleteTriggerParam[] = "delete from FLC_TRIGGER_PARAMETER where TRIGGER_ID=".$allTriggerID[$x].";".$sqlNewLine."\r\n";
	}

	for($x=0; $x < count($allBlID); $x++)
	{
		$deleteBL[] = "delete from FLC_BL where BLID=".$allBlID[$x].";".$sqlNewLine."\r\n";
		$deleteBLParam[] = "delete from FLC_BL_PARAMETER where BL_ID=".$allBlID[$x].";".$sqlNewLine."\r\n";
	}

	for($x=0; $x < count($allPermID); $x++)
		$deletePerm[] = "delete from FLC_PERMISSION where PERM_ID=".$allPermID[$x].";".$sqlNewLine."\r\n";
		
	for($x=0; $x < count($groupIDList); $x++)
		$deleteGroup[] = "delete from FLC_USER_GROUP where GROUP_ID=".$groupIDList[$x].";".$sqlNewLine."\r\n";
	
	for($x=0; $x < count($userIDList); $x++)
		$deleteUser[] = "delete from PRUSER where USERID=".$userIDList[$x].";".$sqlNewLine."\r\n";
		
	//TODO - DELETE FLC_USER_GROUP_MAPPING AND PRUSER?

	/*
	$deleteExtendedAttr[] = "delete from FLC_EXTENDED_ATTR;".$sqlNewLine."\r\n";

	for($x=0; $x < count($allID); $x++)
		for($y=0; $y < count($allID[$x]); $y++)
			$deleteExtendedAttrVal[] = "delete from FLC_EXTENDED_ATTR where ATTR_PARENT_ID=".$allID[$x][$y]." and ATTR_ID in (select ATTR_ID from FLC_EXTENDED_ATTR where ATTR_PARENT_TABLE = '".$allIDKey[$x]."');".$sqlNewLine."\r\n";
	*/
	//---------------------------------------------------------------------------------------------------------------------
	//FOR ROLLBACK PURPOSES
	//---------------------------------------------------------------------------------------------------------------------
	$rollbackAddition = array();
	$rollbackAddition[] = "/*\r\nROLLBACK BLOCK ROLLBACK BLOCK ROLLBACK BLOCK ROLLBACK BLOCK \r\n";
	$rollbackAddition[] = 'MENU|||'.implode(',',$menuIDList)."\r\n";
	$rollbackAddition[] = 'PAGE|||'.implode(',',$pageIDList)."\r\n";
	$rollbackAddition[] = 'COMP|||'.implode(',',$componentIDList)."\r\n";
	$rollbackAddition[] = 'ITEM|||'.implode(',',$componentItemIDList)."\r\n";
	$rollbackAddition[] = 'CTRL|||'.implode(',',$controlIDList)."\r\n";
	$rollbackAddition[] = 'CHRT|||'.implode(',',$chartIDList)."\r\n";
	$rollbackAddition[] = 'TRIG|||'.implode(',',$allTriggerID)."\r\n";
	$rollbackAddition[] = 'BLOG|||'.implode(',',$allBlID)."\r\n";
	$rollbackAddition[] = 'PERM|||'.implode(',',$allPermID)."\r\n";
	$rollbackAddition[] = 'UGRP|||'.implode(',',$groupIDList)."\r\n";
	$rollbackAddition[] = 'PRUS|||'.implode(',',$userIDList)."\r\n";
	$rollbackAddition[] = "ROLLBACK BLOCK ROLLBACK BLOCK ROLLBACK BLOCK ROLLBACK BLOCK \r\n*/\r\n";
	//---------------------------------------------------------------------------------------------------------------------
	//PREPEND CLOB COLUMNS VARIABLES
	//---------------------------------------------------------------------------------------------------------------------

	$currentFileContents = array(file_get_contents('export_import/exp_'.$_SESSION['userID'].$timestamp.'.fdmp'.$fileExtension));

	//if clob values exists
	if(count($blToPrepend) || count($compQueryToPrepend) || count($pageToPrepend) || count($itemToPrepend))
	{
		if(DBMS_NAME == 'mysql' || DBMS_NAME == 'mysql')
	{
		$allToPrepend = array_merge($blToPrepend,$compQueryToPrepend,$pageToPrepend,$itemToPrepend);

		writeToDump($timestamp,array("declare \r\n"),1,0,$fileExtension);		//TRUNCATE
		writeToDump($timestamp,$allToPrepend,0,0,$fileExtension);
		writeToDump($timestamp,array("begin \r\n"),0,0,$fileExtension);
	}
	}
	else
		writeToDump($timestamp,array("begin \r\n"),1,0,$fileExtension);			//TRUNCATE

	//write rollback info to file
	writeToDump($timestamp,$rollbackAddition,0,0,$fileExtension);

	//if export mode is replace, add delete portion
	if($_POST['exp_type'] == 'replace')
	{
		writeToDump($timestamp,$deleteMenu,0,0,$fileExtension);
		writeToDump($timestamp,$deletePage,0,0,$fileExtension);
		writeToDump($timestamp,$deleteComponent,0,0,$fileExtension);
		writeToDump($timestamp,$deleteItem,0,0,$fileExtension);
		writeToDump($timestamp,$deleteControl,0,0,$fileExtension);
		writeToDump($timestamp,$deleteChart,0,0,$fileExtension);
		writeToDump($timestamp,$deleteTrigger,0,0,$fileExtension);
		writeToDump($timestamp,$deleteTriggerParam,0,0,$fileExtension);
		writeToDump($timestamp,$deleteBL,0,0,$fileExtension);
		writeToDump($timestamp,$deleteBLParam,0,0,$fileExtension);
		writeToDump($timestamp,$deletePerm,0,0,$fileExtension);
		writeToDump($timestamp,$deleteGroup,0,0,$fileExtension);
		writeToDump($timestamp,$deleteExtendedAttr,0,0,$fileExtension);
		writeToDump($timestamp,$deleteUser,0,0,$fileExtension);
		//writeToDump($timestamp,$deleteExtendedAttrVal,0,0,$fileExtension);
	}

	writeToDump($timestamp,$currentFileContents,0,0,$fileExtension);
	writeToDump($timestamp,$clobUpdate,0,0,$fileExtension);
	writeToDump($timestamp,array('end;'),0,0,$fileExtension);

	//reset
	$columnName = array();
	$dataType = array();

	//post export thingy
	resetExportSelection();							//reset selection
	$exportSuccessFlag = true;						//show export msg
	$dumpLink = $_SESSION['userID'].$timestamp;		//for data dump download link
}

//if import module button
else if($_POST['importModule'])
{
	$timestamp = date('YmdHis');
	$uploaddir = 'export_import/';

	$ext = pathinfo($_FILES['importFile']['name'],PATHINFO_EXTENSION);
	$uploadfile = $uploaddir . 'imp_'.$_SESSION['userID'].$timestamp.'.'.$ext;

	if(move_uploaded_file($_FILES['importFile']['tmp_name'], $uploadfile)) {}
	else
	{
		echo 'File failed to upload. Please check file and folder permissions and upload_max_filesize setting in php.ini';
		exit;
	}

	//get uploaded file content
	$uploadedFileStr = file_get_contents($uploaddir . 'imp_'.$_SESSION['userID'].$timestamp.'.'.$ext);

	//get file mode
	if($ext == 'fdmpa')
		$mode = 'append';
	else if($ext == 'fdmpr')
		$mode = 'replace';

	//================================
	//ROLLBACK
	//================================
	//get rollback block
	$rollbackFlag = 'ROLLBACK BLOCK ROLLBACK BLOCK ROLLBACK BLOCK ROLLBACK BLOCK';
	$rollbackBlkStart = strpos_offset_recursive($rollbackFlag,$uploadedFileStr,1);
	$rollbackBlkEnd = strpos_offset_recursive($rollbackFlag,$uploadedFileStr,2);
	$rollbackFlagStrlen = strlen($rollbackFlag);
	$rollbackBlkContent = substr($uploadedFileStr,($rollbackBlkStart+4+$rollbackFlagStrlen),($rollbackBlkEnd-4-$rollbackBlkStart-$rollbackFlagStrlen));

	$rollbackBlkSplit = explode("\r\n",$rollbackBlkContent);
	$rollbackBlkSplit = array_filter($rollbackBlkSplit,'strlen');
	$rollbackBlkSplitCnt = count($rollbackBlkSplit);

	//remove table name
	for($x=0; $x < $rollbackBlkSplitCnt; $x++)
	{
		$exp = explode('|||',$rollbackBlkSplit[$x]);
		$rollbackBlkSplit[$x] = $exp[1];
	}
	
	//PRE IMPORT SYSTEM OPTIMIZATION
	$deleteOrphanGroupMapping = "delete from FLC_USER_GROUP_MAPPING where GROUP_ID not in (select GROUP_ID from FLC_USER_GROUP)";	
	$myQuery->query($deleteOrphanGroupMapping,'RUN');
	
	$deleteOrphanUserMapping = "delete from FLC_USER_GROUP_MAPPING where USER_ID not in (select USERID from PRUSER)";
	$myQuery->query($deleteOrphanUserMapping,'RUN');
	
	$deleteOrphanPermission = "delete from FLC_PERMISSION where GROUP_ID not in (select GROUP_ID from FLC_USER_GROUP)";
	$myQuery->query($deleteOrphanPermission,'RUN');
	
	$deleteTempImpUser = "delete from PRUSER where USERNAME like 'FLCIMPTEMP_%'";
	$myQuery->query($deleteTempImpUser,'RUN');
	
	//=====================================
	//UPDATING PRIMARY KEY VALUES
	//=====================================
	//get max running number for all tables
	$primaryKeyMaxValues = array(	$mySQL->maxValue('FLC_MENU','MENUID')+1,
									$mySQL->maxValue('FLC_PAGE','PAGEID')+1,
									$mySQL->maxValue('FLC_PAGE_COMPONENT','COMPONENTID')+1,
									$mySQL->maxValue('FLC_PAGE_COMPONENT_ITEMS','ITEMID')+1,
									$mySQL->maxValue('FLC_PAGE_CONTROL','CONTROLID')+1,
									$mySQL->maxValue('FLC_CHART','ITEM_ID')+1,
									$mySQL->maxValue('FLC_TRIGGER','TRIGGER_ID')+1,
									$mySQL->maxValue('FLC_BL','BLID')+1,
									$mySQL->maxValue('FLC_USER_GROUP','GROUP_ID')+1,
									$mySQL->maxValue('FLC_PERMISSION','PERM_ID')+1,
									$mySQL->maxValue('PRUSER','USERID')+1);
	
	//print_r($primaryKeyMaxValues);
	
	//todo here - flc_chart is not updated after import append
	$primaryKeyToUpdate = array('MENUID','PAGEID','COMPONENTID','ITEMID','CONTROLID','ITEM_ID','TRIGGER_ID','BLID','GROUP_ID','PERM_ID','USERID','USER_ID');
	$primaryKeyToUpdateCnt = count($primaryKeyToUpdate);

	$newIDValueCollection = array();

	for($a=0; $a < $primaryKeyToUpdateCnt; $a++)
	{
		//scan for primary key values positions
		$primaryKeyValPosition = find_occurences($uploadedFileStr,'{{{'.$primaryKeyToUpdate[$a].'|');
		$primaryKeyValPositionCnt = count($primaryKeyValPosition);

		$idValue = array();
		$newIDValue = array();

		//for all ID positions, get id value
		for($x=0; $x < $primaryKeyValPositionCnt; $x++)
		{
			$endingPos = strpos(substr($uploadedFileStr,$primaryKeyValPosition[$x],30),'}}}');
			$menuIDStr = substr($uploadedFileStr,$primaryKeyValPosition[$x],$endingPos);
			$idValue[] = substr($menuIDStr,strpos($menuIDStr,'|')+1);
		}

		$idValue = array_unique($idValue);		//create unique array values
		$idValueCnt = count($idValue);

		//remove null
		for($x=0; $x < $idValueCnt; $x++)
		{
			if($idValue[$x] == '' || is_null($idValue[$x]))
				unset($idValue[$x]);
		}

		sort($idValue,SORT_NUMERIC);
		$idValueCnt = count($idValue);

		//update id value to new id value
		for($x=0; $x < $idValueCnt; $x++)
		{
			//if($idValue[$x] != '' && !is_null($idValue[$x]))				//new!!!!
			//{
				if((int)$idValue[$x] > 0)
					$newIDValue[] = $idValue[$x]+$primaryKeyMaxValues[$a];
				else
					$newIDValue[] = $idValue[$x];
			//}
		}

		$idValueCnt = count($idValue);
		$newIDValueCollection[] = $newIDValue;

		for($x=0; $x < $idValueCnt; $x++)
			$uploadedFileStr = str_replace('{{{'.$primaryKeyToUpdate[$a].'|'.$idValue[$x].'}}}',$newIDValue[$x],$uploadedFileStr);
	}
		
	//=====================================
	//END UPDATING PRIMARY KEY VALUES BLOCK
	//=====================================

	//=====================================
	//RUN THE PREPARED SQL IMPEXP SCRIPT
	//=====================================
	
	//echo '<pre>'.$uploadedFileStr.'</pre>';
	
	if(strtoupper(DBMS_NAME) == 'ORACLE')
		$insertCatRs = $myQuery->query(str_replace("\r\n","",$uploadedFileStr),'RUN');

	else if(strtoupper(DBMS_NAME) == 'MYSQL')
	{
		$uploadedFileStr = preg_replace('/begin/','',$uploadedFileStr, 1);					//remove begin
		$uploadedFileStr =  str_lreplace('end;','',$uploadedFileStr);

		$tempArr = explode(";[ENDSQLLINE]\r\n",$uploadedFileStr);
		$tempArrCount = count($tempArr);

		for($x=0; $x < $tempArrCount; $x++)
		{
			if($tempArr[$x] != '')
				$insertCatRs = $myQuery->query($tempArr[$x],'RUN');
		}
		$insertCatRs = true;
	}
	else if(strtoupper(DBMS_NAME) == 'INFORMIX')
	{
		$replace = array('%%%%', '||||');
		//$uploadedFileStr = "EXECUTE PROCEDURE IFX_ALLOW_NEWLINE('T');";
		$uploadedFileStr = preg_replace("/begin/","EXECUTE PROCEDURE IFX_ALLOW_NEWLINE('T');",$uploadedFileStr, 1);
		$uploadedFileStr = str_lreplace('end;','',$uploadedFileStr);

		$tempArr = explode(";[ENDSQLLINE]\r\n",$uploadedFileStr);
		
		$tempArrCount = count($tempArr);

		for($x=0; $x < $tempArrCount; $x++)
		{	
		
			if(strpos($tempArr[$x], '%%%%') !== false)
			{
				$textInput = explode("||||",$tempArr[$x]);
				$textInputCount = count($textInput); 
				
				for($i=0; $i < $textInputCount; $i++)
				{
				
					if(strpos($textInput[$i], '####') == false)
					{
						$textInput[$i] = str_replace($replace,' ', $textInput[$i]);	
						$insertCatRs = $myQuery->query($textInput[$i],'RUN');
					}
					else
					{
						$insertCatRs = $myQuery->query($textInput[$i],'RUNUP');
					}
				}

			}	
			else
			{	
				if(trim($tempArr[$x]) != '')
					$insertCatRs = $myQuery->query($tempArr[$x],'RUN');
			}
			
		}
		$insertCatRs = true;
	}

	//import - REPLACE mode
	if($mode == 'replace')
	{
		//collect all ids
		$menuIDList 			= $rollbackBlkSplit[0];
		$pageIDList 			= $rollbackBlkSplit[1];
		$componentIDList 		= $rollbackBlkSplit[2];
		$componentItemIDList 	= $rollbackBlkSplit[3];
		$controlIDList 			= $rollbackBlkSplit[4];
		$chartIDList 			= $rollbackBlkSplit[5];
		$allTriggerID 			= $rollbackBlkSplit[6];
		$allBlID 				= $rollbackBlkSplit[7];
		$allPermID 				= $rollbackBlkSplit[8];
		$groupIDList			= $rollbackBlkSplit[9];
		$userIDList				= $rollbackBlkSplit[10];
	}

	//import - APPEND mode
	else if($mode == 'append')
	{
		$menuIDList = array();
		$pageIDList = array();
		$componentIDList = array();
		$componentItemIDList = array();
		$controlIDList = array();
		$chartIDList = array();
		$allTriggerID = array();
		$allBlID = array();
		$allPermID = array();
		$userIDList = array();

		$menuIDList 			= implode(',',$newIDValueCollection[0]);
		$pageIDList 			= implode(',',$newIDValueCollection[1]);
		$componentIDList 		= implode(',',$newIDValueCollection[2]);
		$componentItemIDList 	= implode(',',$newIDValueCollection[3]);
		$controlIDList 			= implode(',',$newIDValueCollection[4]);
		$chartIDList 			= implode(',',$newIDValueCollection[5]);
		$allTriggerID 			= implode(',',$newIDValueCollection[6]);
		$allBlID 				= implode(',',$newIDValueCollection[7]);
		$groupIDList			= implode(',',$newIDValueCollection[8]);
		$allPermID 				= implode(',',$newIDValueCollection[9]);
		$userIDList 			= implode(',',$newIDValueCollection[10]);
	}

	//==================================================================
	//POST EXPORT CLEANSING - START BLOCK
	//==================================================================

	$inLimit = 900;			//in clause limit

	//UPDATE MENU
	$menuIDListArr = explode(',',$menuIDList);
	$cnt = ceil(count($menuIDListArr)/$inLimit);

	if($menuIDList != '' && count($menuIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updateMenuContent = "update FLC_MENU set
									".createReplaceStr('MENUTITLE').",
									".createReplaceStr('MENUNOTES').",
									".createReplaceStr('MENUHINTS').",
									".createReplaceStr('MENULINK')."
									where MENUID in (".implode(',',array_slice($menuIDListArr,($x*$inLimit),$inLimit)).")";
			$updateMenuContentRs = $myQuery->query($updateMenuContent,'RUN');
		}
	}

	//UPDATE PAGE
	$pageIDListArr = explode(',',$pageIDList);
	$cnt = ceil(count($pageIDListArr)/$inLimit);

	if($pageIDList != '' && count($pageIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updatePageContent = "update FLC_PAGE set
									".createReplaceStr('PAGENAME').",
									".createReplaceStr('PAGETITLE').",
									".createReplaceStr('PAGEBREADCRUMBS').",
									".createReplaceStr('PAGEDESC').",
									".createReplaceStr('PAGEPRESCRIPT').",
									".createReplaceStr('PAGEPOSTSCRIPT').",
									".createReplaceStr('PAGENOTES')."
									where PAGEID in (".implode(',',array_slice($pageIDListArr,($x*$inLimit),$inLimit)).")";
			$updatePageContentRs = $myQuery->query($updatePageContent,'RUN');
		}
	}

	//UPDATE COMPONENT
	$componentIDListArr = explode(',',$componentIDList);
	$cnt = ceil(count($componentIDListArr)/$inLimit);

	// replace cara php
	if(strtoupper(DBMS_NAME) == 'MYSQL' || strtoupper(DBMS_NAME) == 'ORACLE')
	{
	if($componentIDList != '' && count($componentIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updateComponentContent = "update FLC_PAGE_COMPONENT set
										".createReplaceStr('COMPONENTNAME').",
										".createReplaceStr('COMPONENTTITLE').",
										".createReplaceStr('COMPONENTTYPEQUERY').",
										".createReplaceStr('COMPONENTPATH').",
										".createReplaceStr('COMPONENTPRESCRIPT').",
										".createReplaceStr('COMPONENTPOSTSCRIPT').",
										".createReplaceStr('COMPONENTUPLOADCOLUMN').",
										".createReplaceStr('COMPONENTADDROWJAVASCRIPT').",
										".createReplaceStr('COMPONENTDELETEROWJAVASCRIPT')."
										where COMPONENTID in (".implode(',',array_slice($componentIDListArr,($x*$inLimit),$inLimit)).")";
			$updateComponentContentRs = $myQuery->query($updateComponentContent,'RUN');
		}
	}
	}

	//UPDATE ITEM
	$componentItemIDListArr = explode(',',$componentItemIDList);
	$cnt = ceil(count($componentItemIDListArr)/$inLimit);

	if(strtoupper(DBMS_NAME) == 'MYSQL' || strtoupper(DBMS_NAME) == 'ORACLE')
	{
	if($componentItemIDList != '' && count($componentItemIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updateItemContent = "update FLC_PAGE_COMPONENT_ITEMS set
									".createReplaceStr('ITEMDEFAULTVALUE').",
									".createReplaceStr('ITEMLOOKUP').",
									".createReplaceStr('ITEMLOOKUPSECONDARY').",
									".createReplaceStr('ITEMAGGREGATECOLUMNLABEL').",
									".createReplaceStr('ITEMUPLOAD').",
									".createReplaceStr('ITEMDELIMITER').",
									".createReplaceStr('ITEMLOOKUPUNLIMITED').",
									".createReplaceStr('ITEMNAME').",
									".createReplaceStr('ITEMHINTS').",
									".createReplaceStr('ITEMTITLE').",
									".createReplaceStr('ITEMNOTES')."
									where ITEMID in (".implode(',',array_slice($componentItemIDListArr,($x*$inLimit),$inLimit)).")";
			$updateItemContentRs = $myQuery->query($updateItemContent,'RUN');
		}
	}
	}

	//UPDATE CONTROL
	$controlIDListArr = explode(',',$controlIDList);
	$cnt = ceil(count($controlIDListArr)/$inLimit);

	if($controlIDList != '' && count($controlIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updateControlContent = "update FLC_PAGE_CONTROL set
										".createReplaceStr('CONTROLNAME').",
										".createReplaceStr('CONTROLREDIRECTURL').",
										".createReplaceStr('CONTROLIMAGEURL').",
										".createReplaceStr('CONTROLTITLE').",
										".createReplaceStr('CONTROLNOTES')."
										where CONTROLID in (".implode(',',array_slice($controlIDListArr,($x*$inLimit),$inLimit)).")";
			$updateControlContentRs = $myQuery->query($updateControlContent,'RUN');
		}
	}

	//UPDATE CHART
	//use item id for the charts id!! (by design)
	$componentItemIDListArr = explode(',',$componentItemIDList);
	$cnt = ceil(count($componentItemIDListArr)/$inLimit);

	if($componentItemIDList != '' && count($componentItemIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updateChartContent = "update FLC_CHART set
										".createReplaceStr('CHART_X_AXIS_LABEL').",
										".createReplaceStr('CHART_PY_AXIS_LABEL').",
										".createReplaceStr('CHART_PSQL').",
										".createReplaceStr('CHART_PURL').",
										".createReplaceStr('CHART_SY_AXIS_LABEL').",
										".createReplaceStr('CHART_SSQL').",
										".createReplaceStr('CHART_SURL').",
										".createReplaceStr('CHART_TREND_LABEL')."
										where ITEM_ID in (".implode(',',array_slice($componentItemIDListArr,($x*$inLimit),$inLimit)).")";
			$updateChartContentRs = $myQuery->query($updateChartContent,'RUN');
		}
	}

	//UPDATE TRIGGER_PARAMETER
	$allTriggerIDArr = explode(',',$allTriggerID);
	$cnt = ceil(count($allTriggerIDArr)/$inLimit);

	if($allTriggerID != '' && count($allTriggerIDArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			$updateTriggerParamContent = "update FLC_TRIGGER_PARAMETER
											set ".createReplaceStr('PARAMETER_VALUE')."
											where TRIGGER_ID in (".implode(',',array_slice($allTriggerIDArr,($x*$inLimit),$inLimit)).")";
			$updateTriggerParamContentRs = $myQuery->query($updateTriggerParamContent,'RUN');
			
			//update BLNAME with import prefix
			if(trim($_POST['importPrefix']) != '')
			{	
				if(strtoupper(DBMS_NAME) == 'MYSQL')
				{
					$updateTriggerBLName = "update FLC_TRIGGER 
											set TRIGGER_BL = concat('".trim($_POST['importPrefix'])."_',TRIGGER_BL)
											where TRIGGER_ID in (".implode(',',array_slice($allTriggerIDArr,($x*$inLimit),$inLimit)).")";
					$updateTriggerBLNameRs = $myQuery->query($updateTriggerBLName,'RUN');
				}
				else if(strtoupper(DBMS_NAME) == 'ORACLE' || strtoupper(DBMS_NAME) == 'INFORMIX')
				{
					$updateTriggerBLName = "update FLC_TRIGGER 
											set TRIGGER_BL = '".trim($_POST['importPrefix'])."_' || TRIGGER_BL
											where TRIGGER_ID in (".implode(',',array_slice($allTriggerIDArr,($x*$inLimit),$inLimit)).")";
					$updateTriggerBLNameRs = $myQuery->query($updateTriggerBLName,'RUN');
				}
			}
		}
	}

	//UPDATE FLC_BL  // tukar QS kepada inverted coma
	$allBlIDArr = explode(',',$allBlID);
	$cnt = ceil(count($allBlIDArr)/$inLimit);

	if($allBlID != '' && count($allBlIDArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			
			if(strtoupper(DBMS_NAME) == 'MYSQL' || strtoupper(DBMS_NAME) == 'ORACLE')
		{
			//update BL content
			$updateBLContent = "update FLC_BL set
								".createReplaceStr('BLNAME').",
								".createReplaceStr('BLDESCRIPTION').",
								".createReplaceStr('BLDETAIL')."
								where BLID in (".implode(',',array_slice($allBlIDArr,($x*$inLimit),$inLimit)).")";
			$updateBLContentRs = $myQuery->query($updateBLContent,'RUN');
			}
			
			//update BLNAME with import prefix
			if(trim($_POST['importPrefix']) != '')
			{	
				if(strtoupper(DBMS_NAME) == 'MYSQL')
				{
					$updateBLName = "update FLC_BL 
									set BLNAME = concat('".trim($_POST['importPrefix'])."_',BLNAME)
									where BLID in (".implode(',',array_slice($allBlIDArr,($x*$inLimit),$inLimit)).")";
					$updateBLNameRs = $myQuery->query($updateBLName,'RUN');
				}
				else if(strtoupper(DBMS_NAME) == 'ORACLE' || strtoupper(DBMS_NAME) == 'INFORMIX')
				{
					$updateBLName = "update FLC_BL 
									set BLNAME = '".trim($_POST['importPrefix'])."_' || BLNAME
									where BLID in (".implode(',',array_slice($allBlIDArr,($x*$inLimit),$inLimit)).")";
					$updateBLNameRs = $myQuery->query($updateBLName,'RUN');
				}
			}
			
			//update BL_PARAMETER content
			$updateBLParamContent = "update FLC_BL_PARAMETER
										set ".createReplaceStr('PARAMETER_VALUE')."
										where BL_ID in (".implode(',',array_slice($allBlIDArr,($x*$inLimit),$inLimit)).")";
			$updateBLParamContentRs = $myQuery->query($updateBLParamContent,'RUN');
		
		
			/*
			if($_POST['deleteBLImport'])
			{
				//TODO HERE
				//get latest bl of the same name
				/*
				select * from FLC_BL a, FLC_BL b
				where a.BLNAME = b.BLNAME
				and a.BLID <> b.BLID
				and a.BLPARENT is null
				group by a.BLNAME
				having max(a.BLID)
				*/
				
				//get latest version of bl based on name and maximum blid
				 $getLatestBL = "select a.BLID, a.BLNAME from FLC_BL a, FLC_BL b
									where a.BLNAME = b.BLNAME
									and a.BLID in (".implode(',',array_slice($allBlIDArr,($x*$inLimit),$inLimit)).")
									and a.BLID <> b.BLID
									and a.BLPARENT is null
									group by a.BLNAME
									having max(a.BLID)";
				$getLatestBLRs = $myQuery->query($getLatestBL,'SELECT','NAME');
				
				$latestBLID = array();
				$latestBLName = array();
				
				for($a=0; $a < count($getLatestBLRs); $a++)
				{
					$latestBLID[] = $getLatestBLRs[$a]['BLID'];
					$latestBLName[] = "'".$getLatestBLRs[$a]['BLNAME']."'";
				}
				
				//delete previous version of bl of the same name (lower blid than the newly imported ones)
				$deleteTargetBL = "delete from FLC_BL 
									where BLID not in (".implode(',',$latestBLID).")
									and BLNAME in (".implode(',',$latestBLName).")";
				$myQuery->query($deleteTargetBL,'RUN');
				
				//delete orphan bl parameter
				$deleteTargetBLParam = "delete from FLC_BL_PARAMETER where BL_ID not in (select BLID from FLC_BL)";
				$deleteTargetBLParamRs = $myQuery->query($deleteTargetBLParam,'RUN');
			}*/
		}
	}
	
	//UPDATE FLC_USER_GROUP
	$groupIDListArr = explode(',',$groupIDList);
	$cnt = ceil(count($groupIDListArr)/$inLimit);

	if($groupIDList != '' && count($groupIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			//update FLC_BL with import prefix
			if(trim($_POST['importPrefix']) != '')
			{	
				if(strtoupper(DBMS_NAME) == 'MYSQL')
				{
					$updateGroup = "update FLC_USER_GROUP 
									set 
										DESCRIPTION = concat('".trim($_POST['importPrefix'])."_',DESCRIPTION),
										GROUP_CODE = concat('".trim($_POST['importPrefix'])."_',GROUP_CODE)
									where GROUP_ID in (".implode(',',array_slice($groupIDListArr,($x*$inLimit),$inLimit)).")";
					$updateGroupRs = $myQuery->query($updateGroup,'RUN');
				}
				else if(strtoupper(DBMS_NAME) == 'ORACLE' || strtoupper(DBMS_NAME) == 'INFORMIX')
				{
					$updateGroup = "update FLC_USER_GROUP 
									set 
										DESCRIPTION = '".trim($_POST['importPrefix'])."_' || DESCRIPTION,
										GROUP_CODE = '".trim($_POST['importPrefix'])."_' || GROUP_CODE
									where GROUP_ID in (".implode(',',array_slice($groupIDListArr,($x*$inLimit),$inLimit)).")";
					$updateGroupRs = $myQuery->query($updateGroup,'RUN');
				}
			}
		}
	}

	//UPDATE PRUSER
	$userIDListArr = explode(',',$userIDList);
	$cnt = ceil(count($userIDListArr)/$inLimit);
	
	//rename all with FLCIMPTEMP_ to importPrefix
	/*
	$renamePruser = "update PRUSER 
						set USERNAME = '".trim($_POST['importPrefix'])."' || SUBSTR(USERNAME,12) 
					where USERID in (".implode(',',array_slice($userIDListArr,($x*$inLimit),$inLimit)).")";
	*/
	
	if($userIDList != '' && count($userIDListArr) > 0)
	{
		for($x=0; $x < $cnt; $x++)
		{
			//update PRUSER content
			$updatePRUSERContent = "update PRUSER set
								".createReplaceStr('NAME').",
								".createReplaceStr('USERNAME')."
								where USERID in (".implode(',',array_slice($userIDListArr,($x*$inLimit),$inLimit)).")";
			$updatePRUSERContentRs = $myQuery->query($updatePRUSERContent,'RUN');
			
			//update PRUSER with import prefix
			if(trim($_POST['importPrefix']) != '')
			{	
				if(strtoupper(DBMS_NAME) == 'MYSQL')
				{
					$updateUser = "update PRUSER 
									set 
										USERNAME = concat('".trim($_POST['importPrefix'])."_',SUBSTR(USERNAME,12))
									where USERID in (".implode(',',array_slice($userIDListArr,($x*$inLimit),$inLimit)).")";
					$updateUserRs = $myQuery->query($updateUser,'RUN');
				}
				else if(strtoupper(DBMS_NAME) == 'ORACLE' || strtoupper(DBMS_NAME) == 'INFORMIX')
				{
					$updateUser = "update PRUSER 
									set 
										USERNAME = '".trim($_POST['importPrefix'])."_' || SUBSTR(USERNAME,12)
									where USERID in (".implode(',',array_slice($userIDListArr,($x*$inLimit),$inLimit)).")";
					$updateUserRs = $myQuery->query($updateUser,'RUN');
				}
			}
		}
	}

	//==================================================================
	//POST EXPORT CLEANSING - END BLOCK
	//==================================================================

	//create rollback data
	$rollbackData = createRollbackData($mySQL,$menuIDMax,$pageIDMax,$componentIDMax,$itemIDMax,$controlIDMax);
	dumpRollbackData($timestamp,$rollbackData);

	//refresh side menu
	$_POST['menuForceRefresh'] ="Refresh Side Menu" ;
	
	//----------------------------------
	//user group and user remap options
	//----------------------------------
	if($_POST['impexp_type'] == 'imp' && $_POST['importModule'])
	{
		$group = "select GROUP_ID, DESCRIPTION, GROUP_CODE from FLC_USER_GROUP 
					where 
					DESCRIPTION not like '".$_POST['importPrefix']."%'
					order by DESCRIPTION";
		$groupRs = $myQuery->query($group,'SELECT','NAME');
		
		$groupNew = "select GROUP_ID, DESCRIPTION, GROUP_CODE from FLC_USER_GROUP 
					where 
					DESCRIPTION like '".$_POST['importPrefix']."%'
					order by DESCRIPTION";
		$groupNewRs = $myQuery->query($groupNew,'SELECT','NAME');
		
		$users = "select USERID, USERNAME from PRUSER
					where 
					USERNAME not like '".$_POST['importPrefix']."%'
					order by USERNAME";
		$usersRs = $myQuery->query($users,'SELECT','NAME');
		
		$usersNew = "select USERID, USERNAME, NAME, USERPASSWORD 
					from PRUSER 
					where 
					USERNAME like '".$_POST['importPrefix']."%'
					order by USERNAME";
		$usersNewRs = $myQuery->query($usersNew,'SELECT','NAME');
		
		/*
		//remap to selected user
			$updateMap = "update FLC_USER_GROUP_MAPPING 
							set USER_ID = ".$_POST['users'][$x]." 
							where USER_ID = ".$_POST['users_new'][$x];
			$updateMapRs = $myQuery->query($updateMap,'RUN');
			
			//finally delete the user
			$deleteUser = "delete from PRUSER where USERID = ".$_POST['users_new'][$x];
			$deleteUserRs = $myQuery->query($deleteUser,'RUN');
			
			//$deleteUser = "delete from FLC_USER_GROUP_MAPPING where USERID = ".$_POST['users_new'][$x];
			//$deleteUserRs = $myQuery->query($deleteUser,'RUN');
			
			//store debug info
			$debugStr[] = 'user:'.$updateMap;
			$debugStr[] = $deleteUser;
		*/
		
		//best effort auto remaping off pruser data
		if($remapUserMode == 'auto')
		{
			$prefixLen = strlen($_POST['importPrefix']);
			
			$updateCnt = 0;
			$deleteCnt = 0;
			
			for($x=0; $x < count($usersNewRs); $x++)
			{
				//find if there is similar name, username, pwd to newly added user
				$checkSimilar = "select * from PRUSER 
								where NAME = '".str_replace("'","''",$usersNewRs[$x]['NAME'])."'
								and USERNAME = '".str_replace("'","''",substr($usersNewRs[$x]['USERNAME'],$prefixLen+1))."'
								and USERPASSWORD = '".$usersNewRs[$x]['USERPASSWORD']."'";
				$checkSimilarRs = $myQuery->query($checkSimilar,'SELECT','NAME');
				
				if(count($checkSimilarRs))
				{
					//remap to similar user in target
					$updateMap = "update FLC_USER_GROUP_MAPPING 
									set USER_ID = ".$checkSimilarRs[0]['USERID']." 
									where USER_ID = ".$usersNewRs[$x]['USERID'];
					$updateMapRs = $myQuery->query($updateMap,'RUN');
					$updateCnt++;
					
					if($updateMapRs)
					{
						//finally delete the user
						$deleteUser = "delete from PRUSER where USERID = ".$usersNewRs[$x]['USERID'];
						$deleteUserRs = $myQuery->query($deleteUser,'RUN');
						$deleteCnt++;
					}
				}
			}
			
			echo 'Total users auto-merged: '.$updateCnt;
			echo '<br>';
			echo 'Total new users auto-deleted: '.$deleteCnt;
		}
		else if($remapUserMode == 'interface')
		{
			//show the interface
		}
		
		//requery
		$usersNew = "select USERID, USERNAME, NAME, USERPASSWORD 
					from PRUSER 
					where 
					USERNAME like '".$_POST['importPrefix']."%'
					order by USERNAME";
		$usersNewRs = $myQuery->query($usersNew,'SELECT','NAME');
	}
}

//if rollback module is selected
else if($_POST['rollbackModule'])
{
	$rollbackRs = rollbackData($mySQL);									//rollback the data
	@unlink('export_import/rollback/'.$_POST['rollbackList']);			//remove the rollback data file
	$rollbackList = getRollbackList();									//get list of rollback data
}

//get menu permission
$menuPermission = "select MENUID, MENUTITLE, MENUSTATUS from FLC_MENU where MENUROOT is null
						and MENUPARENT = 0
						order by MENUSTATUS desc, MENUTITLE asc";
$menuPermissionRsArr = $myQuery->query($menuPermission,'SELECT','NAME');
$countMenuPermissionRsArr = count($menuPermissionRsArr);

?>
<script language="javascript">
function selectAssocCheckbox(elem)
{
	selectChildCheckbox(elem);
	selectMasterCheckbox(elem);
}

function selectChildCheckbox(elem)
{
	//start traversing
	var tr = jQuery(elem).parent().parent().parent();
	var originalInputLevel = jQuery(elem).attr('class').replace('impexp_level','');
	var newTR = jQuery(tr).next();
	var newInput = newTR.children().eq(0).children().eq(0).children().eq(0);
	var newInputLevel = newInput.attr('class').replace('impexp_level','');

	if(newInputLevel > originalInputLevel)
	{
		if(jQuery(elem).attr('checked') == 'checked')
			jQuery(newInput).attr('checked','checked');
		else
			jQuery(newInput).attr('checked',false);
	}

	while(originalInputLevel < newInputLevel)
	{
		newTR = jQuery(newTR).next();
		newInput = newTR.children().eq(0).children().eq(0).children().eq(0);
		newInputLevel = newInput.attr('class').replace('impexp_level','');

		if(newInputLevel > originalInputLevel)
		{
			if(jQuery(elem).attr('checked') == 'checked')
				jQuery(newInput).attr('checked','checked');
			else
				jQuery(newInput).attr('checked',false);
		}
	}
}

//to check master checkbox
function selectMasterCheckbox(elem)
{
	//start traversing
	var tr = jQuery(elem).parent().parent().parent();
	var originalInputLevel = jQuery(elem).attr('class').replace('impexp_level','');
	var newTR = jQuery(tr).prev();
	var newInputLevel = 0;

	var newInput = newTR.children().eq(0).children().eq(0).children().eq(0);
	var newInputClass = newInput.attr('class');

	if(newInputClass == undefined){}
	else
		newInputLevel = newInput.attr('class').replace('impexp_level','');

	if(newInputLevel < originalInputLevel)
	{
		if(jQuery(elem).attr('checked') == 'checked')
			jQuery(newInput).attr('checked','checked');
		else
			jQuery(newInput).attr('checked',false);

		originalInputLevel = newInputLevel;
	}

	while(newInputLevel > 0)
	{
		newTR = jQuery(newTR).prev();
		newInput = newTR.children().eq(0).children().eq(0).children().eq(0);
		newInputClass = newInput.attr('class');

		if(newInputClass == undefined)
			break;
		else
			newInputLevel = newInput.attr('class').replace('impexp_level','');

		if(newInputLevel < originalInputLevel )
		{
			if(jQuery(elem).attr('checked') == 'checked')
				jQuery(newInput).attr('checked','checked');
			else
				jQuery(newInput).attr('checked',false);

			originalInputLevel = newInputLevel;
		}
	}
}

function checkExtension(elem,type)
{
	var okFlag = false;
	typeSplit = type.split('|');

	for(var a=0; a < typeSplit.length; a++)
	{
		if(typeSplit[a] == 'fdmpr' && elem.value.match(/.fdmpr$/) && elem.value.match(/exp_/))
			okFlag = true;
		else if(typeSplit[a] == 'fdmpa' && elem.value.match(/.fdmpa$/) && elem.value.match(/exp_/))
			okFlag = true;
		else if(typeSplit[a] == 'fsyc' && elem.value.match(/.fsyc$/) && elem.value.match(/dbsync_/))
			okFlag = true;
	}

	if(okFlag)
	{
		//console.log((parseInt(elem.files[0].size/1024))+' kB');
		//console.log((elem.files[0].size/1024/1024).toFixed(2)+' MB');

		jQuery.get('system_impexp_func.php?type=ajax_upload&file=' + jQuery(elem),
			function(data)
			{
				//if label, use thisElem!!
				if(data.indexOf('divlabel') != -1)
				{
					var dataArr = data.split('|||');
					var dataOld = dataArr[0].split('_');
					var dataNew = dataArr[1].split('|');
					var newID = dataOld[0]+'_'+dataOld[1]+'_'+dataOld[2]+ '_'+ dataNew[0] +  '_' + dataNew[1];

					//recreate id for label
					jQuery('#' + dataArr[0]).attr('id',newID);
					jQuery('#currentItem').val(newID);
				}
			}
		);
	}
	else
	{
		var t = '';

		//if type fdmpr / fdmpa
		if(typeSplit.length > 1)
		{
			for(var a=0; a < typeSplit.length; a++)
			{
				t += '.'+typeSplit[a].toUpperCase();

				if(a+1 < typeSplit.length)
					t += ' ';
			}
			window.alert('Please choose ONLY valid and verified ' + t + ' files!');
		}
		else
			window.alert('Please choose ONLY valid and verified .'+type.toUpperCase()+' files!');

		jQuery('#importFile').val('');
	}
}
</script>
<script language="javascript" src="js/editor.js"></script>
<div id="breadcrumbs">System Administrator / Configuration / System Import and Export
</div>
<h1>System Import and Export</h1>
<?php //if update successful
if($insertCatRs) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="userNotification">
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;System import has been successfully performed on <?php echo date('Y-m-d H:i:s')?> by <?php echo $_SESSION['userName']?>. Please <strong>CHECK</strong> the imported module(s) for consistency. </td>
	</tr>
</table>
<br />
<?php }
//if export success
if($exportSuccessFlag) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="userNotification">
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The selected module(s) has been successfully exported. Please download the data file <a href="system_impexp_downloader.php?file=<?php echo $dumpLink?>.fdmp<?php echo $fileExtension; ?>&type=exp" target="_blank"><strong>HERE</strong></a>.</td>
	</tr>
</table>
<br />
<?php }
else if($dbSyncSuccessFlag) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="userNotification">
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Database Sync script has been successfully generated. Please download the data script file <a href="system_impexp_downloader.php?file=<?php echo $dumpLink?>.fsyc&type=dbsync" target="_blank"><strong>HERE</strong></a>.</td>
	</tr>
</table>
<br />
<?php }
//if rollback successfull
if($rollbackRs) { ?>
<table width="750" border="0" cellspacing="0" cellpadding="0" id="userNotification">
	<tr>
		<td>A ROLLBACK using rollback data <strong><?php echo $_POST['rollbackList']?></strong> has been performed.</td>
	</tr>
</table>
<br />
<?php } ?>
<?php if(!isset($_POST["editScreen"]))  { ?>
<!--<p style="font-size:20px; color:red;">JANGAN GUNA DULU. TGH EDIT</p>-->
<form action="" method="post" name="form1" id="form1" enctype="multipart/form-data">
	
	<?php if($_POST['impexp_type'] == 'imp' && $_POST['importModule']) { ?>
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="3">Remap Options - User Groups</th>
		</tr>
		<tr>
			<th width="15" class="listingHead">#</th>
			<th width="" class="listingHead">Source</th>
			<th width="" class="listingHead">Destination</th>
		</tr>
		<?php for($a=0; $a < count($groupNewRs); $a++) { ?>
		<tr>
			<td><?php echo $a+1; ?>.</td>
			<td>
				<select name="userGroups_new[]" class="inputList" style="max-width:500px;">
					<option></option>
					<?php for($x=0; $x < count($groupNewRs); $x++) { ?>
					<option value="<?php echo $groupNewRs[$x]['GROUP_ID']; ?>" <?php if($x == $a) echo 'selected';?>><?php echo $groupNewRs[$x]['GROUP_CODE']; ?></option>
					<?php } ?>
				</select>
			</td>
			<td>
				<select name="userGroups[]" class="inputList" style="max-width:500px;">
					<option value="nothing">Do nothing</option>
					<?php for($x=0; $x < count($groupRs); $x++) { ?>
					<option value="<?php echo $groupRs[$x]['GROUP_ID']; ?>"><?php echo 'Remap to group -> '.$groupRs[$x]['GROUP_CODE']; ?></option>
					<?php } ?>
					<option value="delete">Delete this group</option>
				</select>
			</td>
		</tr>
		<?php } ?>
	</table>	
	<br>
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent remapUsers">
		<tr>
			<th colspan="3">Remap Options - Users</th>
		</tr>
		<tr>
			<th width="15" class="listingHead">#</th>
			<th width="" class="listingHead">Source</th>
			<th width="" class="listingHead">Destination</th>
		</tr>
		<?php for($a=0; $a < count($usersNewRs); $a++) { ?>
		<tr class="systemUsers">
			<td><?php echo $a+1; ?>.</td>
			<td>
				<select name="users_new[]" class="inputList" style="max-width:500px;">
					<option></option>
					<?php for($x=0; $x < count($usersNewRs); $x++) { ?>
					<option value="<?php echo $usersNewRs[$x]['USERID']; ?>" <?php if($x == $a) echo 'selected';?>><?php echo $usersNewRs[$x]['USERNAME']; ?></option>
					<?php } ?>
				</select>
			</td>
			<td>
				<select name="users[]" class="inputList" style="max-width:500px;">
					<option value="nothing">Do nothing</option>
					<?php for($x=0; $x < count($usersRs); $x++) { ?>
					<option value="<?php echo $usersRs[$x]['USERID']; ?>"><?php echo 'Remap to user -> '.$usersRs[$x]['USERNAME']; ?></option>
					<?php } ?>
					<option value="delete">Delete this user</option>
				</select>
			</td>
		</tr>
		<?php } ?>
		<tr class="usersFooterButton">
			<td colspan="3" class="contentButtonFooter">
				<input name="cancelRemap" type="button" class="inputButton" id="cancelRemap" value="&lt;&lt; Back To Main" onclick="window.location = 'index.php?page=system_impexp'" />
				<input name="remapStart" type="submit" class="inputButton" id="remapStart" onclick="if(window.confirm('Are you sure?')) return true; else return false;" value="Remap Selected Items" />
			</td>
		</tr>
	</table>
	<br>
	<?php } else { ?>
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="2">What Do You Want To Do? </th>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Important Note : </td>
			<td>
				<strong>Please use/update to the latest system database structure before importing or exporting data. </strong>
			</td>
		</tr>
		<tr>
			<td width="74" nowrap="nowrap" class="inputLabel">Type : </td>
			<td width="662">
				<label>
					<input name="impexp_type" type="radio" onChange="form1.submit()" value="imp" <?php if($_POST['impexp_type'] == 'imp') { ?> checked<?php } else if($_POST['impexp_type'] != 'imp' && $_POST['impexp_type'] != 'exp') { ?>checked<?php } ?>>
					IMPORT Module
				</label>
				<br />
				<label>
					<input name="impexp_type" type="radio" value="exp" onChange="form1.submit()" <?php if($_POST['impexp_type'] == 'exp') { ?> checked<?php }?>>
					EXPORT Module
					<?php if($_POST['impexp_type'] == 'exp') { ?>
					<br />
					<span style="padding-left:20px;">
						<label>
							<input checked name="exp_type" type="radio" onChange="" value="replace" <?php if($_POST['dbsync_import']) { echo 'disabled ';} ?><?php if($_POST['exp_type'] == 'replace' || $_POST['hidden_exp_type'] == 'replace') { ?>checked<?php } ?> >Replace Mode
						</label>
						<label>
							<input name="exp_type" type="radio"  onChange="" value="append" <?php if($_POST['dbsync_import']) { echo 'disabled ';} ?><?php if($_POST['exp_type'] == 'append' || $_POST['hidden_exp_type'] == 'append') { ?>checked<?php } ?>>Append Mode
						</label>
					</span>
					<?php } ?>
				</label>
				<br />
				<!--
				<label>
					<input name="impexp_type" type="radio" value="structure_sync" onChange="form1.submit()" <?php if($_POST['impexp_type'] == 'structure_sync') { ?> checked<?php }?>>
					DB Table Structure Sync
					<?php if($_POST['impexp_type'] == 'structure_sync') { ?>
					<br />
					<span style="padding-left:20px;">
						<label>
							<input name="dbsync" type="radio" value="source" <?php if($_POST['dbsync_import']) { echo 'disabled ';} ?><?php if($_POST['dbsync'] == 'source') { ?>checked<?php } ?> onclick="if(this.checked) { jQuery('#dbsync_import').hide(); jQuery('#dbsync_create').show(); jQuery('#dbSyncRow').hide(); jQuery('.senaraiTableUser').show(); jQuery('.senaraiTableImport').show();} ">Source
						</label>
						<label>
							<input name="dbsync" type="radio" value="destination" <?php if($_POST['dbsync_import']) { echo 'disabled ';} ?><?php if($_POST['dbsync'] == 'destination') { ?>checked<?php } ?> onclick="if(this.checked) { jQuery('#dbsync_import').show(); jQuery('#dbsync_create').hide(); jQuery('#dbSyncRow').show(); jQuery('.senaraiTableUser').hide(); jQuery('.senaraiTableImport').show();}">Destination
						</label>
					</span>
					<?php } ?>
				</label>
				<br />
				<label style="color:#666666">
					<input name="impexp_type" disabled="disabled" type="radio" value="roll" onChange="form1.submit()" <?php if($_POST['impexp_type'] == 'roll') { ?> checked<?php }?>>
					ROLLBACK Module
				</label>
				-->
				<!--
				<br />
				<label style="color:#666666">
					<input disabled="disabled" name="impexp_type" type="radio" value="reference" >
					User System Reference
				</label>
				-->
			</td>
		</tr>
		<?php if($_POST['impexp_type'] == 'imp' || !isset($_POST['impexp_type'])) {  ?>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Options : </td>
			<td>
				<label><input type="checkbox" name="deleteBLImport" value="1" /><strong>DELETE</strong> BL with same name at import target</label><br>
			</td>
		</tr>
		<tr id="importRow">
			<td nowrap="nowrap" class="inputLabel">Import Prefix : </td>
			<td>
				<input id="importPrefix" name="importPrefix" type="text" class="inputInput" size="30" value="IMP_<?php echo date('YmdHis').'_'.strtoupper($_SESSION['userName']); ?>" onchange="trim(this);">
			</td>
		</tr>
		<tr id="importRow">
			<td nowrap="nowrap" class="inputLabel">Import File : </td>
			<td>
				<label style="color:red;"><input type="checkbox" onchange="if(this.checked == true) jQuery('#importFile').attr('disabled',false); else jQuery('#importFile').attr('disabled','disabled')" /> I have backed up my DB, please let me IMPORT into <strong><?php echo strtoupper(DB_USERNAME); ?></strong></label><br>
				<input name="importFile" type="file" id="importFile" size="40" onchange="checkExtension(this,'fdmpr|fdmpa')" disabled>
			</td>
		</tr>
		<?php } ?>
		<?php if($_POST['impexp_type'] == 'structure_sync') { ?>
		<tr id="dbSyncRow" style="display:none;">
			<td nowrap="nowrap" class="inputLabel">DB Sync File : </td>
			<td>
				<label style="color:red;"><input type="checkbox" onchange="if(this.checked == true) jQuery('#importFile').attr('disabled',false); else jQuery('#importFile').attr('disabled','disabled')" /> I have backed up my DB, please let me SYNC into <strong><?php echo strtoupper(DB_USERNAME); ?></strong></label><br>
				<input name="importFile" type="file" id="importFile" size="40" onchange="checkExtension(this,'fsyc')" disabled>
			</td>
		</tr>
		<?php } ?>
		<?php if($_POST['impexp_type'] == 'roll') { ?>
		<tr id="rollbackRow">
			<td nowrap="nowrap" class="inputLabel">Rollback Data : </td>
			<td>
				<?php if(count($rollbackList) > 0) { ?>
				<select name="rollbackList" id="rollbackList" class="inputList">
					<?php for($x=0; $x < count($rollbackList); $x++) { ?>
					<option value="<?php echo $rollbackList[$x]?>"><?php echo $rollbackList[$x]?></option>
					<?php } ?>
				</select>
				<?php } else { ?>
				&nbsp;<em>No rollback data available..</em>
			<?php } ?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="2" class="contentButtonFooter"><div align="right">
				<input name="back" type="submit" class="inputButton" id="back" value="Cancel" />
				<?php if($_POST['impexp_type'] == 'exp') { ?>
					<input name="resetExportSelection" type="submit" class="inputButton" id="resetExportSelection" value="Reset Selection" onclick="if(window.confirm('Are you sure you want to reset the selection?')) return true; else return false;"  />
					<input name="exportModule" type="submit" class="inputButton" id="exportModule" value="Export Module &gt;&gt;" <?php if(count($_SESSION['system_impexp_full']) == 0 ){ ?> onclick="window.alert('Please select module(s) to export!'); return false;"<?php } ?> />
				<?php } ?>
				<?php if($_POST['impexp_type'] == 'imp' || !isset($_POST['impexp_type'])) { ?>
					<input name="importModule" type="submit" class="inputButton" id="importModule" value="Import Module &gt;&gt;" onClick="if(jQuery('#importFile') == '') {window.alert('Please choose your import file! Thank you.'); return false; } else return true;" />
				<?php } ?>
					<?php if($_POST['impexp_type'] == 'roll' && count($rollbackList) > 0) { ?>
					<input name="rollbackModule" type="submit" class="inputButton" id="rollbackModule" value="Rollback Module &gt;&gt;" onclick="if(jQuery('#rollbackList') == '') { window.alert('Please choose rollback data.'); return false;} else {if(window.confirm('Are you SURE to ROLLBACK using this data? This action is irreversible!')) return true; else return false; }" />
				<?php } ?>
					<?php if($_POST['impexp_type'] == 'structure_sync') { ?>
					<?php if(!isset($_POST['dbsync_import'])) { ?>
					<input name="dbsync_create" type="submit" class="inputButton" id="dbsync_create" value="Create DB Sync Script &gt;&gt;" />
				<?php } ?>
					<input name="dbsync_import" type="submit" class="inputButton" id="dbsync_import" value="Import DB Sync Script &gt;&gt;" style="display:none" onclick="if(jQuery('#importFile').val() == '') { window.alert('Please select a DB Sync file!'); return false; }" />
					<?php if($_POST['dbsync_import']) { ?>
					<input name="dbsync_sync" type="submit" class="inputButton" id="dbsync_sync" value="Start Sync &gt;&gt;" onclick="if(window.confirm('Are you sure to start syncing?') && window.confirm('Confirm? This action is not reversible!')) return true; else return false;" />
				<?php } ?>
				<?php } ?>
			</div></td>
		</tr>
  </table>
	<?php } ?>
  <?php } ?>
  <?php if($_POST['impexp_type'] == 'exp') { ?>
  <?php if($_POST['editPermission']) { ?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Sub-Menus in Main Menu  : <?php echo strtoupper($_POST['hiddenMenuTitle']); ?>
        <input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $_POST['hiddenCode']?>" /></th>
    </tr>
    <tr>
      <th class="listingHead" >Sub Menu Name </th>
      <th width="50" class="listingHead" >Status</th>
    </tr>
    <?php for($x=0; $x < $menuListCnt; $x++) { ?>
	 <tr>
      <td class="listingContent">
		<label style="display:block; cursor:pointer;">
			<input type="checkbox" name="moduleSelection_<?php echo $menuList[$x]['MENUID']?>" class="impexp_level<?php echo $menuList[$x]['MENULEVEL'] ?>" id="moduleSelection_<?php echo $menuList[$x]['MENUID']?>" style="margin-left:<?php echo $menuList[$x]['MENULEVEL']*15; ?>px" value="1" onChange="selectAssocCheckbox(this)" <?php checkSelectedCheckbox($menuList[$x]['MENUID'],$_SESSION['system_impexp'][$_POST['hiddenCode']]) ?> />
			<?php echo $menuList[$x]['MENUTITLE'];?>
		</label>
	</td>
    <td class="listingContentRight" <?php if($menuList[$x]['MENUSTATUS'] == 0) { ?>style="color:#FF0000"<?php } ?> ><?php if($menuList[$x]['MENUSTATUS'] == 0) echo 'Hidden';else echo 'Visible';?></td>
    </tr>
	<?php } ?>
    <?php if($menuListCnt == 0) { ?>
	<tr>
		<td class="myContentInput">&nbsp;Tiada sub-modul ditemui.. </td>
		<td class="myContentInput">&nbsp;</td>
	</tr>
    <?php } ?>
		<tr>
			<td colspan="2" class="contentButtonFooter">
				<div style="float:left">
					<input name="selectAll" type="button" class="inputButton" id="selectAll" value="Check All" onClick="prototype_selectAllCheckbox()" />
					<input name="unselectAll" type="button" class="inputButton" id="unselectAll" value="Uncheck All" onClick="prototype_unselectAllCheckbox()" />
				</div>
				<div style="float:right">
					<input name="cancelScreen" type="submit" class="inputButton" id="cancelScreen" value="<< Back" />
					<input name="saveScreenRefEdit" type="submit" class="inputButton" id="saveScreenRefEdit" value="Save Selection" />
				</div>
			</td>
		</tr>
	</table>
</form>
<br>
<?php } else { ?>
<br>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<tr>
		<th colspan="7">
		Menu List - <?php echo '('.$countMenuPermissionRsArr.')'; ?></th>
	</tr>
</table>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<?php if($countMenuPermissionRsArr > 0) { ?>
	<tr>
		<th width="15" class="listingHead">#</th>
		<th class="listingHead">Main Menu</th>
		<th width="50" class="listingHead">Status</th>
		<th width="54" class="listingHead">Selected</th>
		<th width="60" class="listingHead">&nbsp;</th>
	</tr>
	<?php for($x=0; $x < $countMenuPermissionRsArr; $x++) { ?>
	<tr <?php if(countSelected($menuPermissionRsArr[$x]['MENUID']) != '') { ?>style="background-color: #FFFFCC" <?php } ?>>
		<td class="listingContent"><?php echo $x+1;?>.</td>
		<td class="listingContent"><?php echo ucwords(strtolower($menuPermissionRsArr[$x]['MENUTITLE']));?></td>
		<td class="listingContent"  <?php if($menuPermissionRsArr[$x]['MENUSTATUS'] == 0) { ?>style="color:#FF0000" <?php } ?>><?php if($menuPermissionRsArr[$x]['MENUSTATUS'] == 0) echo 'Hidden';else echo 'Enabled'?></td>
		<td class="listingContent"><?php echo countSelected($menuPermissionRsArr[$x]['MENUID'])?>&nbsp;</td>
		<td nowrap="nowrap" class="listingContentRight">
			<form id="formReference<?php echo $x;?>" name="formReference<?php echo $x;?>" method="post" action="" style="padding-bottom:0px; margin-bottom:0px">
				<input name="editPermission" type="submit" class="inputButton" id="editPermission" value="Details" />
				<input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $menuPermissionRsArr[$x]['MENUID'];?>" />
				<input name="hiddenMenuTitle" type="hidden" id="hiddenMenuTitle" value="<?php echo $menuPermissionRsArr[$x]['MENUTITLE'];?>" />
				<input name="hidden_impexp_type" type="hidden" id="hidden_impexp_type" value="<?php echo $_POST['impexp_type'];?>" />
				<input name="hidden_exp_type" type="hidden" id="hidden_exp_type" value="<?php echo $_POST['exp_type'];?>" />
			</form>
		</td>
	</tr>
	<?php } //end for
	}//end if
	else { ?>
	<tr>
		<td colspan="5" class="myContentInput">&nbsp;Tiada modul ditemui.. </td>
	</tr>
	<?php } //end else ?>
</table>
<?php }//end else
}//end if exp
else if($_POST['impexp_type'] == 'structure_sync' && !isset($_POST['dbsync_table']) && !isset($_POST['dbsync_import'])) { ?>
<br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" class="senaraiTableUser">
    <tr>
      <th colspan="2">Senarai Table User : <?php echo strtoupper(DB_USERNAME); ?></th>
    </tr>
    <tr>
      <th class="listingHead" >#</th>
      <th class="listingHead" >Table Name</th>
    </tr>
	<?php for($x=0; $x < $getTableListCnt; $x++) { ?>
	<tr>
		<td class="listingContent" style="width:30px;"><?php echo $x+1?>.</td>
		<td class="listingContent">
			<label style="display:block; cursor:pointer;">
				&nbsp;<input type="checkbox" name="dbsync_table[]" value="<?php echo $getTableList[$x]['TABLE_NAME'];?>" />
				<?php echo $getTableList[$x]['TABLE_NAME'];?>
			</label>
		</td>
	</tr>
	<?php } ?>
    <?php if($getTableListCnt == 0) { ?>
	<tr>
		<td class="myContentInput" colspan="2">&nbsp;Tiada table ditemui.. </td>
	</tr>
    <?php } ?>
		<tr>
			<td colspan="2" bgcolor="#F7F3F7">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td>&nbsp;&nbsp;</td>
						<td>
							<div align="right">
								<input name="selectAll" type="button" class="inputButton" id="selectAll" value="Select All" onClick="prototype_selectAllCheckbox()" />
								<input name="unselectAll" type="button" class="inputButton" id="unselectAll" value="Unselect All" onClick="prototype_unselectAllCheckbox()" />
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
<?php }//end if structure sync
else if($_POST['impexp_type'] == 'structure_sync' && $_POST['dbsync_import']) { ?>
<br />
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" class="senaraiTableImport">
	<tr>
		<th colspan="7">Senarai Table dan Column</th>
	</tr>
	<tr>
		<th class="listingHead" colspan="5">Source</th>
		<th class="listingHead" colspan="2">Destination</th>
	</tr>
	<tr>
		<th class="listingHead">#</th>
		<th class="listingHead">Table Name</th>
		<th class="listingHead">Column Name</th>
		<th class="listingHead">Data Type</th>
		<th class="listingHead">Len.</th>
		<th class="listingHead">Data Type</th>
		<th class="listingHead">Len.</th>
	</tr>
	<?php
	$dbCompareArr = array();

	//source
	for($x=0; $x < $dbsync_contentCnt; $x++)
	{
		$exploded = explode('||',$dbsync_content[$x]);
		$exploded_0 = explode('.',$exploded[0]);
		$dbCompareArr['SOURCE'][$exploded_0[1]][$exploded[1]] = array('DATATYPE'=>$exploded[2],'DATALENGTH'=>$exploded[3]);
	}

	//destination
	$getTableList = $mySQL->listTable(DB_USERNAME);
	$getTableListCnt = count($getTableList);

	for($x=0; $x < $getTableListCnt; $x++)
	{
		$getTableColumn = $mySQL->listTableColumn(DB_USERNAME,$user,$getTableList[$x]['TABLE_NAME'],'',array('DATA_TYPE','DATA_LENGTH'));
		$getTableColumnCnt = count($getTableColumn);

		for($y=0; $y < $getTableColumnCnt; $y++)
		{
			$exploded = explode('.',$getTableList[$x]['TABLE_NAME']);
			$dbCompareArr['DESTINATION'][$exploded[1]][$getTableColumn[$y]['COLUMN_NAME']] = array(	'DATATYPE'=>$getTableColumn[$y]['DATA_TYPE'],'DATALENGTH'=>$getTableColumn[$y]['DATA_LENGTH']);
		}
	}

	//total table in source and destination
	$totalSrcTable = count($dbCompareArr['SOURCE']);
	$totalDestTable = count($dbCompareArr['DESTINATION']);

	//$totalSrcArrHeight = count($dbCompareArr['SOURCE'])+count($dbCompareArr['SOURCE']);

	/*
	alter table table_name modify (column_name varchar2(30));
	alter table sales rename column order_date to date_of_order;
	*/

	$x=0;
	foreach($dbCompareArr['SOURCE'] as $key => $val)
	{
		foreach($dbCompareArr['SOURCE'][$key] as $key2 => $val2)
		{
			$x++;
		?>
		<tr <?php if($x%2) {?>style="background-color:white;"<?php } ?>>
			<td class="listingContent" style="width:25px;"><?php echo $x;?>.</td>
			<td class="listingContent">
				<label style="display:block; cursor:pointer;">
					&nbsp;<input type="checkbox" name="dbsyncxxx_table[]" value="<?php echo $dbsync_content[$x];?>" />
					<?php echo $key;?>
				</label>
			</td>
			<td class="listingContent"><?php echo $key2;?></td>
			<td class="listingContent"><?php echo $val2['DATATYPE'];?></td>
			<td class="listingContent"><?php echo $val2['DATALENGTH'];?></td>
			<!--
			<td class="listingContent"><?php if(isset($dbCompareArr['DESTINATION'][$key][$key2]))
											{
												if($key)
													echo $key;
												else
													echo '<span style="font-weight:bold;color:red;">'.$key.'</span>';
											} else { ?><div style="background-color:red;">&nbsp;</div><?php } ?>
			</td>
			<td class="listingContent"><?php if(isset($dbCompareArr['DESTINATION'][$key][$key2]))
											{
												if($key)
													echo $key2;
												else
													echo '<span style="font-weight:bold;color:red;">'.$key2.'</span>';
											} else { ?><div style="background-color:red;">&nbsp;</div><?php } ?>
			</td>
			-->
			<td class="listingContent"><?php if(isset($dbCompareArr['DESTINATION'][$key][$key2]))
											{
												if($val2['DATATYPE'] == $dbCompareArr['DESTINATION'][$key][$key2]['DATATYPE'])
													echo $dbCompareArr['DESTINATION'][$key][$key2]['DATATYPE'];
												else
													echo '<span style="font-weight:bold;color:red;">'.$dbCompareArr['DESTINATION'][$key][$key2]['DATATYPE'].'</span>';
											} else { ?><div style="background-color:red;">&nbsp;</div><?php } ?>
			</td>
			<td class="listingContent"><?php if(isset($dbCompareArr['DESTINATION'][$key][$key2]))
											{
												if($val2['DATALENGTH'] == $dbCompareArr['DESTINATION'][$key][$key2]['DATALENGTH'])
													echo $dbCompareArr['DESTINATION'][$key][$key2]['DATALENGTH'];
												else
													echo '<span style="font-weight:bold;color:red;">'.$dbCompareArr['DESTINATION'][$key][$key2]['DATALENGTH'].'</span>';
											} else { ?><div style="background-color:red;">&nbsp;</div><?php } ?>
			</td>
		</tr>
		<?php
			}
		}
		?>
		<?php
		if($dbsync_contentCnt == 0) { ?>
		<tr>
			<td class="myContentInput" colspan="2">&nbsp;Tiada table ditemui.. </td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="7" bgcolor="#F7F3F7">
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td>&nbsp;&nbsp;</td>
						<td>
							<div align="right">
								<input name="selectAll" id="selectAll" type="button" class="inputButton" value="Select All" onClick="prototype_selectAllCheckbox()" />
								<input name="unselectAll" id="unselectAll" type="button" class="inputButton" value="Unselect All" onClick="prototype_unselectAllCheckbox()" />
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
</table>
<?php }//end if structure sync ?>
<br>
