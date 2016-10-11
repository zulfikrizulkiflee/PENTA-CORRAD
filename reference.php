<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

require_once('class/Table.php');				//class Table

//===================================== variable =============================================
//table name
if($_GET['reftype'])
	$tableName = 'REF' . strtoupper($_GET['reftype']);
else
	$tableName = 'REFGENERAL';

//referenceid (get from menu link)
if($_GET['referenceid']||$_GET['referencename'])
{
	//set chosen referenceid and show listing
	$_POST['referenceID']=$mySQL->getReferenceID($_GET['referenceid'],$_GET['referencename'],$_SESSION['userID']);
	
	if($_POST['referenceID'])
		$_POST['showList']=true;
	else		
	{
		//notification
		showNotificationError('Rujukan tidak wujud atau tidak aktif!');
	}//eof else
}//eof if

//breadcrumbs
switch(strtoupper($_GET['reftype']))
{
	case 'SYSTEM':	$URLMalay='Sistem';
					$refStartCount=200000000;
					$masterStartCount=20000;
		break;
	default:		$URLMalay='Am';
					$refStartCount=100000000;
					$masterStartCount=10000;
}//eof switch

//user type
switch(strtoupper($_SESSION['userTypeCode']))
{
	default:
	case 'USER': $userType=3;
		break;
	case 'ADMIN': $userType=2;
		break;
	case 'SYSTEM': $userType=1;
		break;
}//eof switch

//remove dataid in url
$tempAction=explode('&dataid',$_SERVER['REQUEST_URI']);
$_SERVER['REQUEST_URI']=$tempAction[0];

//remove referenceid in url
$tempAction=explode('&referenceid',$_SERVER['REQUEST_URI']);
$_SERVER['REQUEST_URI']=$tempAction[0];

//remove referenceid in url
$tempAction=explode('&referencename',$_SERVER['REQUEST_URI']);
$_SERVER['REQUEST_URI']=$tempAction[0];

//location for forms
$action=$_SERVER['REQUEST_URI'];
//=================================== eof variable ===========================================

//=================================== manipulation ===========================================
//save new category
if($_POST['saveNewCat'])
{
	//notification
	$insertCatSuccessNotification = "Rujukan berjaya disimpan.<br>";
	$insertCatErrorNotification = "Rujukan gagal disimpan.<br>";
	
	//append <<REF>>
	if($_POST['groupCodeLookupTable'])
		$_POST['groupCodeLookupTable']='<<REF>> '.$_POST['groupCodeLookupTable'];
	
	if($_POST['codeLookupTable'])
		$_POST['codeLookupTable']='<<REF>> '.$_POST['codeLookupTable'];
	
	if($_POST['description1LookupTable'])
		$_POST['description1LookupTable']='<<REF>> '.$_POST['description1LookupTable'];
	
	if($_POST['description2LookupTable'])
		$_POST['description2LookupTable']='<<REF>> '.$_POST['description2LookupTable'];
	
	if($_POST['parentCodeLookupTable'])
		$_POST['parentCodeLookupTable']='<<REF>> '.$_POST['parentCodeLookupTable'];
	
	if($_POST['parentRootCodeLookupTable'])
		$_POST['parentRootCodeLookupTable']='<<REF>> '.$_POST['parentRootCodeLookupTable'];
	
	//validate referencename (unique)
	$refExist=$mySQL->getReferenceID('',$_POST['referenceName']);
	
	//if reference with same name not exist
	if(!$refExist)
	{		
		//maxreferenceid
		$maxReferenceID=$mySQL->maxValue($tableName,'REFERENCEID',$refStartCount)+1;
		
		//set default for int value to avoid error
		if(!$_POST['groupCodeUnique']) $_POST['groupCodeUnique'] = "null";
		if(!$_POST['codeUnique']) $_POST['codeUnique'] = "null";
		if(!$_POST['description1Unique']) $_POST['description1Unique'] = "null";
		if(!$_POST['description2Unique']) $_POST['description2Unique'] = "null";
		if(!$_POST['parentCodeUnique']) $_POST['parentCodeUnique'] = "null";
		if(!$_POST['parentRootCodeUnique']) $_POST['parentRootCodeUnique'] = "null";
		
		//insert into container
		$sql="insert into SYSREFCONTAINER
				(REFERENCEID, REFERENCETITLE, REFERENCENAME,
				GROUPCODENAME, GROUPCODETYPE, GROUPCODEDEFAULTVALUE, GROUPCODELOOKUPTABLE, GROUPCODEQUERY, GROUPCODEUNIQUE,
				CODENAME, CODETYPE, CODEDEFAULTVALUE, CODELOOKUPTABLE, CODEQUERY, CODEUNIQUE,
				DESCRIPTION1NAME, DESCRIPTION1TYPE, DESCRIPTION1DEFAULTVALUE, DESCRIPTION1LOOKUPTABLE, DESCRIPTION1QUERY, DESCRIPTION1UNIQUE,
				DESCRIPTION2NAME, DESCRIPTION2TYPE, DESCRIPTION2DEFAULTVALUE, DESCRIPTION2LOOKUPTABLE, DESCRIPTION2QUERY, DESCRIPTION2UNIQUE,
				PARENTCODENAME, PARENTCODETYPE, PARENTCODEDEFAULTVALUE, PARENTCODELOOKUPTABLE, PARENTCODEQUERY, PARENTCODEUNIQUE,
				PARENTROOTCODENAME, PARENTROOTCODETYPE, PARENTROOTCODEDEFAULTVALUE, PARENTROOTCODELOOKUPTABLE, PARENTROOTCODEQUERY, PARENTROOTCODEUNIQUE,
				REFERENCESTATUSCODE)
				values
				(
					".$maxReferenceID.",'".$_POST['referenceTitle']."',upper('".$_POST['referenceName']."'),
					'".$_POST['groupCodeName']."','".$_POST['groupCodeType']."','".$_POST['groupCodeDefaultValue']."','".$_POST['groupCodeLookupTable']."','".$_POST['groupCodeQuery']."',".$_POST['groupCodeUnique'].",
					'".$_POST['codeName']."','".$_POST['codeType']."','".$_POST['codeDefaultValue']."','".$_POST['codeLookupTable']."','".$_POST['codeQuery']."',".$_POST['codeUnique'].",
					'".$_POST['description1Name']."','".$_POST['description1Type']."','".$_POST['description1DefaultValue']."','".$_POST['description1LookupTable']."','".$_POST['description1Query']."',".$_POST['description1Unique'].",
					'".$_POST['description2Name']."','".$_POST['description2Type']."','".$_POST['description2DefaultValue']."','".$_POST['description2LookupTable']."','".$_POST['description2Query']."',".$_POST['description2Unique'].",
					'".$_POST['parentCodeName']."','".$_POST['parentCodeType']."','".$_POST['parentCodeDefaultValue']."','".$_POST['parentCodeLookupTable']."','".$_POST['parentCodeQuery']."',".$_POST['parentCodeUnique'].",
					'".$_POST['parentRootCodeName']."','".$_POST['parentRootCodeType']."','".$_POST['parentRootCodeDefaultValue']."','".$_POST['parentRootCodeLookupTable']."','".$_POST['parentRootCodeQuery']."',".$_POST['parentRootCodeUnique'].",
					'".$_POST['statusCode']."'
				)";
		$insert=$myQuery->query($sql,'RUN');
		
		//if container inserted
		if($insert)
		{
			//insert into reference as master
			$sql="insert into ".$tableName."
					(REFERENCEID,MASTERCODE,REFERENCECODE,DESCRIPTION1,TIMESTAMP,REFERENCESTATUSCODE,USERID)
					values
					(
						".$maxReferenceID.", 'XXX', 
						'".($mySQL->maxValue($tableName,$mySQL->convertToNumber('REFERENCECODE'),$masterStartCount,"MASTERCODE='XXX'")+1)."',
						upper('".$_POST['referenceName']."'), ".$mySQL->currentDate().", '".$_POST['statusCode']."', '".$_SESSION['userID']."'
					)";
			$insert=$myQuery->query($sql,'RUN');
			
			//if data inserted
			if($insert)
			{
				//notification
				showNotificationInfo($insertCatSuccessNotification);
			}
			else
			{
				//notification
				showNotificationError($insertCatErrorNotification);
			}//eof else
				
			//== permission =====================================================================
			$selectedGroupCount=count($_POST['selectedGroup']);
			
			//loop on count
			for($x=0;$x<$selectedGroupCount;$x++)
			{
				//insert permission
				$sql="insert into SYSREFPERMISSION (REFERENCEID,GROUPID)
						values (".$maxReferenceID.", ".$_POST['selectedGroup'][$x].")";
				$insert=$myQuery->query($sql,'RUN');
			}//eof for
			//== eof permission =============================================================
			
			$_POST['referenceID']=$maxReferenceID;
		}//eof if
		else
		{
			//notification
			showNotificationError($insertCatErrorNotification);
		}//eof else
	}//eof if
	//eif same name exist
	else
	{
		//notification
		showNotificationError('Rujukan <strong>'.$_POST['referenceName'].'</strong> telah wujud dan berstatus aktif di dalam sistem.');
	}//eof else
}//eof if

//save edit category
if($_POST['saveEditCat'])
{
	//notification
	$updateCatSuccessNotification = "Rujukan berjaya dikemaskini.<br>";
	$updateCatErrorNotification = "Rujukan gagal dikemaskini.<br>";
	
	$permissionSuccess=0;
	$permissionError=0;
	
	//append <<REF>>
	if($_POST['groupCodeLookupTable'])
		$_POST['groupCodeLookupTable']='<<REF>> '.$_POST['groupCodeLookupTable'];
	
	if($_POST['codeLookupTable'])
		$_POST['codeLookupTable']='<<REF>> '.$_POST['codeLookupTable'];
	
	if($_POST['description1LookupTable'])
		$_POST['description1LookupTable']='<<REF>> '.$_POST['description1LookupTable'];
	
	if($_POST['description2LookupTable'])
		$_POST['description2LookupTable']='<<REF>> '.$_POST['description2LookupTable'];
	
	if($_POST['parentCodeLookupTable'])
		$_POST['parentCodeLookupTable']='<<REF>> '.$_POST['parentCodeLookupTable'];
	
	if($_POST['parentRootCodeLookupTable'])
		$_POST['parentRootCodeLookupTable']='<<REF>> '.$_POST['parentRootCodeLookupTable'];
	
	//validate referencename (unique)
	$refExist=$mySQL->getReferenceID('',$_POST['referenceName']);
	
	//if reference with same name not exist or update is done on same reference
	if(!$refExist || $refExist == $_POST['referenceID'])
	{
		//set default for int value to avoid error
		if(!$_POST['groupCodeUnique']) $_POST['groupCodeUnique'] = "null";
		if(!$_POST['codeUnique']) $_POST['codeUnique'] = "null";
		if(!$_POST['description1Unique']) $_POST['description1Unique'] = "null";
		if(!$_POST['description2Unique']) $_POST['description2Unique'] = "null";
		if(!$_POST['parentCodeUnique']) $_POST['parentCodeUnique'] = "null";
		if(!$_POST['parentRootCodeUnique']) $_POST['parentRootCodeUnique'] = "null";
		
		//update container
		$sql="update SYSREFCONTAINER set
					REFERENCETITLE='".$_POST['referenceTitle']."',
					REFERENCENAME=upper('".$_POST['referenceName']."'),
					GROUPCODENAME='".$_POST['groupCodeName']."',
					GROUPCODETYPE='".$_POST['groupCodeType']."',
					GROUPCODEDEFAULTVALUE='".$_POST['groupCodeDefaultValue']."',
					GROUPCODELOOKUPTABLE='".$_POST['groupCodeLookupTable']."',
					GROUPCODEQUERY='".$_POST['groupCodeQuery']."',
					GROUPCODEUNIQUE=".$_POST['groupCodeUnique'].",
					CODENAME='".$_POST['codeName']."',
					CODETYPE='".$_POST['codeType']."',
					CODEDEFAULTVALUE='".$_POST['codeDefaultValue']."',
					CODELOOKUPTABLE='".$_POST['codeLookupTable']."',
					CODEQUERY='".$_POST['codeQuery']."',
					CODEUNIQUE=".$_POST['codeUnique'].",
					DESCRIPTION1NAME='".$_POST['description1Name']."',
					DESCRIPTION1TYPE='".$_POST['description1Type']."',
					DESCRIPTION1DEFAULTVALUE='".$_POST['description1DefaultValue']."',
					DESCRIPTION1LOOKUPTABLE='".$_POST['description1LookupTable']."',
					DESCRIPTION1QUERY='".$_POST['description1Query']."',
					DESCRIPTION1UNIQUE=".$_POST['description1Unique'].",
					DESCRIPTION2NAME='".$_POST['description2Name']."',
					DESCRIPTION2TYPE='".$_POST['description2Type']."',
					DESCRIPTION2DEFAULTVALUE='".$_POST['description2DefaultValue']."',
					DESCRIPTION2LOOKUPTABLE='".$_POST['description2LookupTable']."',
					DESCRIPTION2QUERY='".$_POST['description2Query']."',
					DESCRIPTION2UNIQUE=".$_POST['description2Unique'].",
					PARENTCODENAME='".$_POST['parentCodeName']."',
					PARENTCODETYPE='".$_POST['parentCodeType']."',
					PARENTCODEDEFAULTVALUE='".$_POST['parentCodeDefaultValue']."',
					PARENTCODELOOKUPTABLE='".$_POST['parentCodeLookupTable']."',
					PARENTCODEQUERY='".$_POST['parentCodeQuery']."',
					PARENTCODEUNIQUE=".$_POST['parentCodeUnique'].",
					PARENTROOTCODENAME='".$_POST['parentRootCodeName']."',
					PARENTROOTCODETYPE='".$_POST['parentRootCodeType']."',
					PARENTROOTCODEDEFAULTVALUE='".$_POST['parentRootCodeDefaultValue']."',
					PARENTROOTCODELOOKUPTABLE='".$_POST['parentRootCodeLookupTable']."',
					PARENTROOTCODEQUERY='".$_POST['parentRootCodeQuery']."',
					PARENTROOTCODEUNIQUE=".$_POST['parentRootCodeUnique'].",
					REFERENCESTATUSCODE='".$_POST['statusCode']."'
				where REFERENCEID=".$_POST['referenceID'];
		$update=$myQuery->query($sql,'RUN');
		
		//update reference master
		$sql="update ".$tableName." set DESCRIPTION1=upper('".$_POST['referenceName']."') where REFERENCEID=".$_POST['referenceID'];
		$update=$myQuery->query($sql,'RUN');
		
		//if success update
		if($update)
		{
			//== permission =====================================================================
			$selectedGroupCount=count($_POST['selectedGroup']);
			
			//delete permission
			$sql="delete from SYSREFPERMISSION where REFERENCEID=".$_POST['referenceID'];
			$delete=$myQuery->query($sql,'RUN');
			
			//loop on count
			for($x=0;$x<$selectedGroupCount;$x++)
			{
				//insert permission
				$sql="insert into SYSREFPERMISSION (REFERENCEID,GROUPID)
						values (".$_POST['referenceID'].", ".$_POST['selectedGroup'][$x].")";
				$insert=$myQuery->query($sql,'RUN');
			}//eof for
			
			//notification
			showNotificationInfo($updateCatSuccessNotification);
			//== eof permission =================================================================
		}
		else
		{
			//notification
			showNotificationError($updateCatErrorNotification);
		}//eof else
	}//eof if
	//ref exist
	else
	{
		//notification
		showNotificationError('Rujukan <strong>'.$_POST['referenceName'].'</strong> telah wujud. Hanya 1 rujukan yang aktif dibenarkan pada satu masa.');
	}//eof else
}//eof if

//if save new data or update data (validate duplication)
if($_POST['saveNewData'] || $_POST['saveEditData'])
{
	//name unique
	$nameUnique = $mySQL->checkUnique($tableName,$_POST['referenceID'],$_POST['groupCode'],$_POST['referenceCode'],$_POST['description1'],$_POST['description2'],$_POST['parentCode'],$_POST['parentRootCode'],$_POST['statusCode'],$_POST['dataID']);
	$nameUniqueRowCount=count($nameUnique);			//row count
	$nameUniqueColumnCount=count($nameUnique[0]);	//column count
	
	//if have duplicate in unique item
	if(is_array($nameUnique))
	{
		//loop on count unique name row
		for($x=0;$x<$nameUniqueRowCount;$x++)
		{
			//loop on count name column
			for($y=0;$y<$nameUniqueColumnCount;$y++)
			{
				//if have duplication, append column name into message
				if($y!=$columnChecked && $nameUnique[$x][$y])
				{
					//if have column name in string
					if($uniqueColumnName)
						$uniqueColumnName.=', ';
					
					//column name (to be appended into message)
					$uniqueColumnName.=$nameUnique[$x][$y];
					
					//assign column that has been checked to skip later
					$columnChecked=$y;
				}//eof if
			}//eof for
		}//eof for
	}//eof if
}//eof if

//save new data
if($_POST['saveNewData'])
{
	//notification
	$insertDataSuccessNotification = "Data berjaya disimpan.<br>";
	$insertDataErrorNotification = "Data gagal disimpan.<br>";
	
	//if don't have any duplication
	if(!$uniqueColumnName)
	{
		//get referencecode
		$getReferenceCode = $myQuery->query("select REFERENCECODE from ".$tableName." where REFERENCEID=".$_POST['referenceID']);
		
		//insert into table
		$sql="insert into ".$tableName." 
				(REFERENCEID,MASTERCODE,GROUPCODE,REFERENCECODE,DESCRIPTION1,
				DESCRIPTION2,PARENTCODE,PARENTROOTCODE,TIMESTAMP,REFERENCESTATUSCODE,USERID)
				values
				(
					(".$mySQL->maxValue($tableName,'REFERENCEID',$refStartCount).")+1,
					'".$getReferenceCode[0][0]."',
					'".$_POST['groupCode']."','".$_POST['referenceCode']."','".$_POST['description1']."',
					'".$_POST['description2']."','".$_POST['parentCode']."','".$_POST['parentRootCode']."',
					".$mySQL->currentDate().",'".$_POST['statusCode']."','".$_SESSION['userID']."'
				)";
		$insert=$myQuery->query($sql,'RUN');
		
		//if success insert
		if($insert)
		{
			//notification
			showNotificationInfo($insertDataSuccessNotification);
		}
		else
		{
			//notification
			showNotificationError($insertDataErrorNotification);
		}//eof else
	}//eof if
	//if have duplicate in unique item
	else
	{
		//notification
		showNotificationError($uniqueColumnName.' yang diisi telah wujud dan berstatus aktif di dalam sistem.<br>'.$insertDataErrorNotification);
	}//eof if
	
	//display listing
	$_POST['showList']=true;
}//eof if

//save edit data
if($_POST['saveEditData'])
{
	//notification
	$updateDataSuccessNotification = "Data berjaya dikemaskini.<br>";
	$updateDataErrorNotification = "Data gagal dikemaskini.<br>";
	
	//if don't have any duplication
	if(!$uniqueColumnName)
	{
		//update table
		$sql="update ".$tableName." set
					GROUPCODE='".$_POST['groupCode']."',
					REFERENCECODE='".$_POST['referenceCode']."',
					DESCRIPTION1='".$_POST['description1']."',
					DESCRIPTION2='".$_POST['description2']."',
					PARENTCODE='".$_POST['parentCode']."',
					PARENTROOTCODE='".$_POST['parentRootCode']."',
					TIMESTAMP=".$mySQL->currentDate().",
					REFERENCESTATUSCODE='".$_POST['statusCode']."',
					USERID='".$_SESSION['userID']."'
				where REFERENCEID=".$_POST['dataID'];
		$update=$myQuery->query($sql,'RUN');
		
		//if success update
		if($update)
		{
			//notification
			showNotificationInfo($updateDataSuccessNotification);
		}
		else
		{
			//notification
			showNotificationError($updateDataErrorNotification);
		}//eof else
	}//eof if
	//if have duplicate in unique item
	else
	{
		//notification
		showNotificationError($uniqueColumnName.' yang diisi telah wujud dan berstatus aktif di dalam sistem.<br>'.$updateDataErrorNotification);
	}//eof if
	
	//display listing
	$_POST['showList']=true;
}//eof if

//delete category
if($_POST['deleteCat'])
{
	//notification
	$delCatSuccessNotification = " rujukan telah dibuang. Data yang berkaitan turut dibuang<br>";
	$delCatErrorNotification = " rujukan gagal dibuang.<br>";
	
	$delSuccess=0;
	$delError=0;
	
	//mastercode fr referenceid
	$sql="select REFERENCECODE from ".$tableName." where REFERENCEID=".$_POST['referenceID'];
	$mastercode=$myQuery->query($sql);
	
	//delete data
	$sql="delete from ".$tableName." where MASTERCODE='".$mastercode[0][0]."'";
	$delete=$myQuery->query($sql,'RUN');
	
	//delete reference
	$sql="delete from ".$tableName." where REFERENCEID=".$_POST['referenceID'];
	$delete=$myQuery->query($sql,'RUN');
	
	//delete container
	$sql="delete from SYSREFCONTAINER where REFERENCEID=".$_POST['referenceID'];
	$delete=$myQuery->query($sql,'RUN');
	
	//if delete success
	if($delete)
	{
		//== permission =====================================================================
		//delete permission
		$sql="delete from SYSREFPERMISSION where REFERENCEID=".$_POST['referenceID'];
		$delete=$myQuery->query($sql,'RUN');
		//== eof permission =================================================================
		
		//notification
		showNotificationInfo($delCatSuccessNotification);
	}//eof if
	else
	{
		//notification
		showNotificationError($delCatErrorNotification);
	}//eof else
	
	$_POST['referenceID']=false;
}//eof if

//delete data
if($_POST['deleteData'])
{
	//notification
	$delDataSuccessNotification = " data telah dibuang.<br>";
	$delDataErrorNotification = " data gagal dibuang.<br>";
	
	//count data to be deleted
	$deleteIDCount=count($_POST['deleteID']);
	
	//loop on deleteID checked
	for($x=0;$x<$deleteIDCount;$x++)
	{
		$sql="delete from ".$tableName." where REFERENCEID=".$_POST['deleteID'][$x];
		$delete=$myQuery->query($sql,'RUN');
	}//eof for
	
	//if have error while delete
	if($delete)
	{
		//notification
		showNotificationInfo($delDataSuccessNotification);
	}//eof if
	else
	{
		//notification
		showNotificationError($delDataErrorNotification);
	}//eof else
	
	$_POST['showList']=true;
}//eof if
//================================= eof manipulation ========================================

//================================= eof manipulation ========================================
//if filter
if($_POST['filter'] || $_POST['filterName'])
{
	//switch to set the value
	switch($_POST['filterName'])
	{
		case 1: $filterName='GROUPCODE'; break;
		case 2: $filterName='REFERENCECODE'; break;
		case 3: $filterName='DESCRIPTION1'; break;
		case 4: $filterName='DESCRIPTION2'; break;
		case 5: $filterName='PARENTCODE'; break;
		case 6: $filterName='PARENTROOTCODE'; break;
		default:
			$_POST['filterValue']='';
	}//eof switch
	
	//if have value in filter
	if($_POST['filterValue'])
		$filter="upper(".$filterName.") like '%".strtoupper($_POST['filterValue'])."%'";
	
	//display listing
	$_POST['showList']=true;
}//eof filter
//================================= eof manipulation ========================================

//if cancel and hav referenceid
if($_POST['cancel'] && $_POST['referenceID'])
{
	$_POST['showList']=true;
	//show listing
}//eof if

//if post order
if($_POST['order']!='')
	$_POST['showList']=true;

//================================= tabular display =========================================
//show listing
if($_POST['showList'])
{
	//use of class TableGrid
	//==================== DECLARATION =======================
	$tg=new TableGrid('100%',0,0,0);						//set object for table class (width,border,celspacing,cellpadding)
	
	//set attribute of table
	$tg->setAttribute('class','tableContent');					//set id
	$tg->setHeader('Senarai Data Rujukan');					//set header
	$tg->setKeysStatus(true);								//use of keys (column header)
	$tg->setKeysAttribute('class','listingHead');			//set class
	$tg->setRunningStatus(true);							//set status of running number
	$tg->setRunningKeys('No');								//key / label for running number

	//set attribute of column in table
	$col = new Column();									//set object for column
	$col->setAttribute('class','listingContent');			//set attribute for table
	$tg->setColumn($col);									//insert/set class column into table
	$tg->setLimit(DEFAULT_REFERENCE_PAGING);				//set display limit (paging)
	//================== END DECLARATION =====================

	//reference data
	$refData=$mySQL->referenceData($tableName,$_POST['referenceID'],$userType,$filter);
	
	//set data 
	if(is_array($refData))
		$tg->setTableGridData($refData);
	else 
		$tg->setTableGridData('Tiada Data');
	
	//count data
	$headerCount=count($refData[0]);
	
	//header
	$tg->setHeaderAttribute('colspan',$headerCount);			//set colspan for header
	
	//footer
	$tg->setFooterAttribute('class','contentButtonFooter');		//set footer attribute
	$tg->setFooterAttribute('colspan',$headerCount);			//set footer attribute
	$tg->setFooterAttribute('align','right');					//set footer attribute
	
	//restricted if type user
	if($userType!=3)
	{
		//footer
		$footButton='<input name="referenceID" type="hidden" id="referenceID" value="'.$_POST['referenceID'].'" />
		<input id="newData" name="newData" type="submit" value="Baru" class="inputButton" style="margin:2px" />';
		
		//if have data
		if(is_array($refData))
			$footButton.='<input id="delData" name="deleteData" type="submit" value="Hapus" class="inputButton" style="margin:2px" 
							onClick="if(window.confirm(\'Anda pasti untuk MEMBUANG data ini?\')) {return true} else {return false}" />';
	}//eof if
	
	$tg->setFooter($footButton);								//set the data of footer
	
	//=== filter set section ================================================================
	//get data of reference (container name)
	$data=$mySQL->data($tableName,$_POST['referenceID']);

	$dataCount=count($data);

	for($x=0,$y=0;$x<6;$x++)
	{
		if($data['Name'][$x+1])
		{
			//set value and label for dropdown <option> 
			$filterList[$y][0]=$x+1;
			$filterList[$y][1]=$data['Name'][$x+1];
			$y++;
		}//eof if
	}//eof for
	//=== eof filter set section ============================================================
	
	//=== sorting section ===================================================================
	//if order have value
	if($_POST['order']!='')
	{
		$sortStatus=$tg->sortIndex(true,$_POST['order']);		//sort based on index
	}//eof if
	//=== sorting section ===================================================================
		
	//=== alter keys to allow sorting =======================================================
	//get keys
	$keysLabel=$tg->getKeys();
	
	//check if keys / label is create
	if($keysLabel)
	{
		$keysLabelCount=count($keysLabel);	//count keys/header label
		$tempCountLabel=0;			//set initial as 0 (count index of label)
		
		//do while not reach end of array
		do
		{
			//put label that will submit current form with index to be ordered when submitted
			$keysLabel[key($keysLabel)]='<label style="cursor:pointer; text-decoration:underline" onclick="document.getElementById(\'order\').value=\''.($tempCountLabel++).'\';this.form.submit();">'.current($keysLabel).'</label>';
		}while(next($keysLabel));
		
		$tg->setKeys($keysLabel);		//set keys in tablegrid
	}//eof if label	
	//=== eof alter keys to allow sorting ===================================================
	
}//eof if
//=============================== eof tabular display =======================================

//================================ data form display ========================================
//new container
if($_POST['newCat'])
{
	$_POST['referenceID']=false;
}//eof if

//display container
if($_POST['editCat'])
{
	//select container
	$sql="select REFERENCEID, REFERENCETITLE, REFERENCENAME,
			GROUPCODENAME, GROUPCODETYPE, GROUPCODEDEFAULTVALUE, ".$mySQL->substring('GROUPCODELOOKUPTABLE',8)." GROUPCODELOOKUPTABLE, GROUPCODEQUERY, GROUPCODEUNIQUE, 
			CODENAME, CODETYPE, CODEDEFAULTVALUE, CODELOOKUPTABLE, CODEQUERY, CODEUNIQUE,
			DESCRIPTION1NAME, DESCRIPTION1TYPE, DESCRIPTION1DEFAULTVALUE, ".$mySQL->substring('DESCRIPTION1LOOKUPTABLE',8)." DESCRIPTION1LOOKUPTABLE, DESCRIPTION1QUERY, DESCRIPTION1UNIQUE, 
			DESCRIPTION2NAME, DESCRIPTION2TYPE, DESCRIPTION2DEFAULTVALUE, ".$mySQL->substring('DESCRIPTION2LOOKUPTABLE',8)." DESCRIPTION2LOOKUPTABLE, DESCRIPTION2QUERY, DESCRIPTION2UNIQUE,
			PARENTCODENAME, PARENTCODETYPE, PARENTCODEDEFAULTVALUE, ".$mySQL->substring('PARENTCODELOOKUPTABLE',8)." PARENTCODELOOKUPTABLE, PARENTCODEQUERY, PARENTCODEUNIQUE,
			PARENTROOTCODENAME, PARENTROOTCODETYPE, PARENTROOTCODEDEFAULTVALUE, ".$mySQL->substring('PARENTROOTCODELOOKUPTABLE',8)." PARENTROOTCODELOOKUPTABLE, PARENTROOTCODEQUERY, PARENTROOTCODEUNIQUE,
			REFERENCESTATUSCODE
			from SYSREFCONTAINER
			where REFERENCEID=".$_POST['referenceID'];
	$container=$myQuery->query($sql,'SELECT','NAME');
	
	//check either check box is yes or no
	$groupCode=havValue($container[0]['GROUPCODENAME']);
	$referenceCode=havValue($container[0]['CODENAME']);
	$description1Code=havValue($container[0]['DESCRIPTION1NAME']);
	$description2Code=havValue($container[0]['DESCRIPTION2NAME']);
	$parentCode=havValue($container[0]['PARENTCODENAME']);
	$parentRootCode=havValue($container[0]['PARENTROOTCODENAME']);
}//eof if

//if new or edit container
if($_POST['newCat'] || $_POST['editCat'])
{
	//lookuptable list
	$lookupTableList=$mySQL->reference($tableName, $_SESSION['userID']);
	
	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupNonSelected($_POST['referenceID']);
	$groupListNonSelectedCount=count($groupListNonSelected);
	
	//edit reference, show current permission
	if($_POST['editCat'])
	{
		//list group user selected
		$groupListSelected=$mySQL->getUserGroupSelected($_POST['referenceID']);
		$groupListSelectedCount=count($groupListSelected);
	}//eof if
}//eof if

//display data (perincian)
if($_GET['dataid'])
{
	//get master referenceid
	$sql="select REFERENCEID from REFGENERAL 
			where REFERENCECODE=(select MASTERCODE from REFGENERAL where REFERENCEID=".$_GET['dataid'].")
			and MASTERCODE='XXX'";
	$tempID=$myQuery->query($sql);
	
	$_POST['referenceID']=$tempID[0][0];		//master reference id
}//eof if

//get container & lookup
if($_POST['newData'] || $_GET['dataid'])
{
	//get data of reference
	$data=$mySQL->data($tableName,$_POST['referenceID'],$_GET['dataid']);
	
	$groupCodeList=$mySQL->getLookupItem($tableName,'GROUPCODE',$_POST['referenceID']);
	$codeList=$mySQL->getLookupItem($tableName,'CODE',$_POST['referenceID']);
	$description1List=$mySQL->getLookupItem($tableName,'DESCRIPTION1',$_POST['referenceID']);
	$description2List=$mySQL->getLookupItem($tableName,'DESCRIPTION2',$_POST['referenceID']);
	$parentCodeList=$mySQL->getLookupItem($tableName,'PARENTCODE',$_POST['referenceID']);
	$parentRootCodeList=$mySQL->getLookupItem($tableName,'PARENTROOTCODE',$_POST['referenceID']);
}//eof if
//============================== eof data form display =======================================

//======================================= general ============================================
//list of status
$statusList=$mySQL->status();
$statusListCount=count($statusList);

//list of reference
$refList = $mySQL->reference($tableName, $_SESSION['userID']);
$refListCount = count($refList);
//===================================== eof general ==========================================
?>
<script language="javascript" type="text/javascript" src="js/reference.js"></script>
<div id="breadcrumbs">Konfigurasi Kod / <?php echo $URLMalay;?> / </div>
<h1>Rujukan <?php echo $URLMalay;?></h1>

<form id="form1" name="form1" method="post" action="<?php echo $action;?>">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">Senarai Rujukan </th>
  </tr>
<tr>
 <td width="100" class="inputLabel">Rujukan :</td>
 <td>
 <div id="updateSelectorDropdown">
	<select name="referenceID" class="inputList" id="referenceID" onchange="if(this.selectedIndex!=0){swapItemEnabled('showList|editCat|deleteCat', '');}else{swapItemEnabled('', 'showList|editCat|deleteCat');}">
		<option value="">- Pilih Kategori -</option>      
		<?php 
		for($x=0;$x<$refListCount;$x++){?>
		<option value="<?php echo $refList[$x][0];?>" <?php if($_POST['referenceID'] == $refList[$x][0]){ ?>selected<?php }?>><?php echo $refList[$x][1];?></option>
		<?php }?>
  	</select>
	<input name="showList" type="submit" class="inputButton" id="showList" value="Papar" <?php if(!$_POST['referenceID']) { ?>disabled style="color:#999999"<?php } ?>/>
  </div>
  </td>
</tr>
<?php if($userType!=3){?>
  <tr>
    <td class="contentButtonFooter" colspan="2" align="right">
      <input name="newCat" type="submit" class="inputButton" id="newCat" value="Baru" />
      <input name="editCat" type="submit" class="inputButton" id="editCat" value="Pinda" <?php if(!$_POST['referenceID']) { ?>disabled style="color:#999999"<?php } ?>/>
      <input name="deleteCat" type="submit" class="inputButton" id="deleteCat" value="Hapus" <?php if(!$_POST['referenceID']) { ?>disabled style="color:#999999"<?php } ?> onClick="if(window.confirm('Anda pasti untuk MEMBUANG rujukan ini?\nSEMUA data rujukan akan turut dibuang')) {return true} else {return false}"/></td>
  </tr>
<?php }?>
</table>
</form>
<br />

<?php if($_POST['showList']){unset($_GET['dataid']);?>

<form id="form2" name="form2" method="post" action="<?php echo $action;?>">
<!--
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<tr>
	  <th colspan="2">Carian Terperinci</th>
	</tr>
	<tr>
	  <td class="inputLabel" width="100" nowrap="nowrap">Carian : </td>
	  <td> 
	    <select name="filterName" class="inputList">
			<?php echo createDropDown($filterList,$_POST['filterName']);?>
	    </select>
	  </td>
	</tr>
	<tr>
	  <td class="inputLabel" nowrap="nowrap">Nilai : </td>
	  <td><input name="filterValue" type="text" class="inputInput" value="<?php echo $_POST['filterValue'];?>" size="50" /></td>
	</tr>
	<tr>
      <td class="contentButtonFooter" colspan="2" align="right">
      	<input name="filter" type="submit" class="inputButton" id="filter" value="Carian" />
	  </td>
  	</tr>
</table>
-->
<br />
<?php $tg->showTableGrid();?>

<!--used for sorting-->
<input id="order" name="order" type="hidden" />
	
</form>
<?php }?>

<?php if($_POST['newCat']||$_POST['editCat']){unset($_GET['dataid']);?>
<!--reference category-->
<form id="form2" name="form2" method="post" action="<?php echo $action;?>">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Bina/Ubahsuai Rujukan </th>
    </tr>
    <?php if($_POST['editCat']){?>
	<tr>
      <td class="inputLabel" width="150" nowrap="nowrap">Id : </td>
      <td><input name="referenceID" type="text" class="inputInput" id="referenceID" value="<?php echo $container[0]['REFERENCEID'];?>" size="15" readonly="yes" />
      </td>
    </tr>
	<?php }?>
    <tr>
      <td class="inputLabel">Tajuk : </td>
      <td><input name="referenceTitle" type="text" class="inputInput" id="referenceTitle" value="<?php echo $container[0]['REFERENCETITLE'];?>" size="70" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Nama :</td>
      <td><input name="referenceName" type="text" class="inputInput" id="referenceName" value="<?php echo $container[0]['REFERENCENAME'];?>" size="50" style="text-transform:uppercase;" /></td>
    </tr>
	<tr>
	  <td class="inputLabel">Status :</td>
	  <td>
          <select name="statusCode" class="inputList" id="statusCode">
            <?php 
			//if status not been set
			if(!isset($container[0]['REFERENCESTATUSCODE']))
				$container[0]['REFERENCESTATUSCODE']='00';		//set default
			
			echo createDropDown($statusList, $container[0]['REFERENCESTATUSCODE'])?>
		  </select>
	  </td>
	</tr>
    <tr>
      <td class="inputLabel"><strong>Kod Kumpulan  </strong></td>
      <td>
	  	<label onclick="swapItemDisplay('group|groupCodeUnique|groupCodeName|groupCodeDefaultValue|groupCodeLookupTable|groupCodeQuery','');
		disableLookup('no','groupCodeLookupTable','groupCodeQuery');">
		<input name="groupCode" type="radio" value="yes" <?php if($groupCode){?> checked="checked" <?php }?> />Ya 
		</label>
		<label onclick="swapItemDisplay('','group|groupCodeUnique|groupCodeName|groupCodeDefaultValue|groupCodeLookupTable|groupCodeQuery')">
		<input type="radio" name="groupCode" value="no" <?php if(!$groupCode){?> checked="checked" <?php }?> />Tidak
		</label>
	  </td>
    </tr>
    <!--group-->
    <tbody id="group" <?php if(!$groupCode){?> style="display:none"<?php }?>>
      <tr>
        <td class="inputLabel">Unik : </td>
        <td><label><input type="checkbox" name="groupCodeUnique" id="groupCodeUnique" value="1" <?php if($container[0]['GROUPCODEUNIQUE']){?> checked="checked"<?php }?> />Ya</label></td>
      </tr>
	  <tr >
        <td class="inputLabel">Nama : </td>
        <td><input name="groupCodeName" type="text" class="inputInput" id="groupCodeName" size="50" value="<?php echo $container[0]['GROUPCODENAME'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Nilai 'Default' : </td>
        <td><input name="groupCodeDefaultValue" id="groupCodeDefaultValue" type="text" class="inputInput" size="30" value="<?php echo $container[0]['GROUPCODEDEFAULTVALUE'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Lookup : </td>
        <td><label>
          <input name="groupCodeLookupType" id="groupCodeLookupType" type="radio" value="no" <?php if(!$container[0]['GROUPCODELOOKUPTABLE']&&!$container[0]['GROUPCODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('no','groupCodeLookupTable','groupCodeQuery')" />
          Tiada Lookup</label>
            <label>
            <input type="radio" name="groupCodeLookupType" id="groupCodeLookupType" value="predefined" <?php if($container[0]['GROUPCODELOOKUPTABLE']){?> checked="checked"<?php }?> onclick="disableLookup('predefined','groupCodeLookupTable','groupCodeQuery')" />
              Predefined</label>
            <label>
            <input type="radio" name="groupCodeLookupType" id="groupCodeLookupType" value="advanced" <?php if($container[0]['GROUPCODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('advanced','groupCodeLookupTable','groupCodeQuery')" />
              Advanced</label>        </td>
      </tr>
      <tr>
        <td class="inputLabel">Predefined Lookup : </td>
        <td><select name="groupCodeLookupTable" class="inputList" id="groupCodeLookupTable" <?php if(!$container[0]['GROUPCODELOOKUPTABLE']){?> disabled="disabled"<?php }?>>
          <?php echo createDropDown($lookupTableList, $container[0]['GROUPCODELOOKUPTABLE'])?>
        </select></td>
      </tr>
      <tr>
        <td class="inputLabel">Advanced Lookup : </td>
        <td>
        	<textarea name="groupCodeQuery" cols="100" rows="5" class="inputInput" id="groupCodeQuery" <?php if(!$container[0]['GROUPCODEQUERY']){?> disabled="disabled"<?php }?> onfocus="placeQuote(this)" onblur="replaceQuote(this)"><?php echo $container[0]['GROUPCODEQUERY'];?></textarea>
        </td>
      </tr>
    </tbody>
    <!--eof group-->
    <tr>
      <td class="inputLabel"><strong>Kod : </strong></td>
      <td>
	  	<label onclick="swapItemDisplay('code|codeUnique|codeName|codeDefaultValue|codeLookupTable|codeQuery','');
		disableLookup('no','codeLookupTable','codeQuery');">
		<input name="referenceCode" type="radio" value="yes" <?php if($referenceCode){?> checked="checked" <?php }?> />Ya 
        </label>
        <label onclick="swapItemDisplay('','code|codeUnique|codeName|codeDefaultValue|codeLookupTable|codeQuery')">
		<input type="radio" name="referenceCode" value="no" <?php if(!$referenceCode){?> checked="checked" <?php }?> />Tidak
        </label>
	  </td>
    </tr>
	
	<!--code-->
	<tbody id="code" <?php if(!$referenceCode){?> style="display:none"<?php }?>>
    <tr>
        <td class="inputLabel">Unik : </td>
        <td><label><input type="checkbox" name="codeUnique" id="codeUnique" value="1" <?php if($container[0]['CODEUNIQUE']){?> checked="checked"<?php }?> />Ya</label></td>
    </tr>
	<tr>
      <td class="inputLabel">Nama :</td>
      <td><input name="codeName" type="text" class="inputInput" id="codeName" size="50" value="<?php echo $container[0]['CODENAME'];?>" /></td>
    </tr>
	<tr>
        <td class="inputLabel">Nilai 'Default' : </td>
        <td><input name="codeDefaultValue" type="text" class="inputInput" id="codeDefaultValue" size="30" value="<?php echo $container[0]['CODEDEFAULTVALUE'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Lookup : </td>
        <td><label>
          <input name="codeLookupType" id="codeLookupType" type="radio" value="no" <?php if(!$container[0]['CODELOOKUPTABLE']&&!$container[0]['CODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('no','codeLookupTable','codeQuery')" />
          Tiada Lookup</label>
		  <label>
		  <input type="radio" name="codeLookupType" id="codeLookupType" value="predefined" <?php if($container[0]['CODELOOKUPTABLE']){?> checked="checked"<?php }?> onclick="disableLookup('predefined','codeLookupTable','codeQuery')" />
		  Predefined</label>
		  <label>
		  <input type="radio" name="codeLookupType" id="codeLookupType" value="advanced" <?php if($container[0]['CODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('advanced','codeLookupTable','codeQuery')" />
		  Advanced</label>
		</td>
      </tr>
      <tr>
        <td nowrap="nowrap" class="inputLabel">Predefined Lookup : </td>
        <td><select name="codeLookupTable" class="inputList" id="codeLookupTable" <?php if(!$container[0]['CODELOOKUPTABLE']){?> disabled="disabled"<?php }?>>
          <?php echo createDropDown($lookupTableList, $container[0]['CODELOOKUPTABLE'])?>
        </select></td>
      </tr>
      <tr>
        <td class="inputLabel">Advanced Lookup : </td>
        <td><textarea name="codeQuery" cols="100" rows="5" class="inputInput" id="codeQuery" <?php if(!$container[0]['CODEQUERY']){?> disabled="disabled"<?php }?> onblur="replaceQuote(this)"><?php echo $container[0]['CODEQUERY'];?></textarea></td>
      </tr>
	</tbody>
	<!--eof code-->
	
	<tr>
      <td class="inputLabel"><strong>Deskripsi 1 : </strong></td>
      <td>
	  	<label onclick="swapItemDisplay('description1|description1Unique|description1Name|description1DefaultValue|description1LookupTable|description1Query','');
		disableLookup('no','description1LookupTable','description1Query');">
		<input name="description1Code" type="radio" value="yes" <?php if($description1Code){?> checked="checked" <?php }?> />Ya 
        </label>
        <label onclick="swapItemDisplay('','description1|description1Unique|description1Name|description1DefaultValue|description1LookupTable|description1Query')">
		<input type="radio" name="description1Code" value="no" <?php if(!$description1Code){?> checked="checked" <?php }?> />Tidak
        </label>
	  </td>
    </tr>
	<!--description1-->
	<tbody id="description1" <?php if(!$description1Code){?> style="display:none"<?php }?>>
    <tr>
        <td class="inputLabel">Unik : </td>
        <td><label><input type="checkbox" name="description1Unique" id="description1Unique" value="1" <?php if($container[0]['DESCRIPTION1UNIQUE']){?> checked="checked"<?php }?> />Ya</label></td>
    </tr>
	<tr>
      <td class="inputLabel">Nama :</td>
      <td><input name="description1Name" type="text" class="inputInput" id="description1Name" size="50" value="<?php echo $container[0]['DESCRIPTION1NAME'];?>" /></td>
    </tr>
	<tr>
        <td class="inputLabel">Nilai 'Default' : </td>
        <td><input name="description1DefaultValue" type="text" class="inputInput" id="description1DefaultValue" size="30" value="<?php echo $container[0]['DESCRIPTION1DEFAULTVALUE'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Lookup : </td>
        <td><label>
          <input name="description1LookupType" id="description1LookupType" type="radio" value="no" <?php if(!$container[0]['DESCRIPTION1LOOKUPTABLE']&&!$container[0]['DESCRIPTION1QUERY']){?> checked="checked"<?php }?> onclick="disableLookup('no','description1LookupTable','description1Query')" />
          Tiada Lookup</label>
		  <label>
		  <input type="radio" name="description1LookupType" id="description1LookupType" value="predefined" <?php if($container[0]['DESCRIPTION1LOOKUPTABLE']){?> checked="checked"<?php }?> onclick="disableLookup('predefined','description1LookupTable','description1Query')" />
		  Predefined</label>
		  <label>
		  <input type="radio" name="description1LookupType" id="description1LookupType" value="advanced" <?php if($container[0]['DESCRIPTION1QUERY']){?> checked="checked"<?php }?> onclick="disableLookup('advanced','description1LookupTable','description1Query')" />
		  Advanced</label>
		</td>
      </tr>
      <tr>
        <td nowrap="nowrap" class="inputLabel">Predefined Lookup : </td>
        <td><select name="description1LookupTable" class="inputList" id="description1LookupTable" <?php if(!$container[0]['DESCRIPTION1LOOKUPTABLE']){?> disabled="disabled"<?php }?>>
          <?php echo createDropDown($lookupTableList, $container[0]['DESCRIPTION1LOOKUPTABLE'])?>
        </select></td>
      </tr>
      <tr>
        <td class="inputLabel">Advanced Lookup : </td>
        <td><textarea name="description1Query" cols="100" rows="5" class="inputInput" id="description1Query" <?php if(!$container[0]['DESCRIPTION1QUERY']){?> disabled="disabled"<?php }?> onblur="replaceQuote(this)"><?php echo $container[0]['description1query'];?></textarea></td>
      </tr>
	</tbody>
	<!--eof description1-->
    <tr>
      <td class="inputLabel"><strong>Deskripsi 2 : </strong></td>
      <td>
	  	<label onclick="swapItemDisplay('description2|description2Unique|description2Name|description2DefaultValue|description2LookupTable|description2Query','');
			disableLookup('no','description2LookupTable','description2Query');">
        <input name="description2Code" type="radio" value="yes" <?php if($description2Code){?> checked="checked" <?php }?> />Ya
        </label>
        <label onclick="swapItemDisplay('','description2|description2Unique|description2Name|description2DefaultValue|description2LookupTable|description2Query')">
        <input type="radio" name="description2Code" value="no" <?php if(!$description2Code){?> checked="checked" <?php }?> />Tidak
		</label>
	  </td>
    </tr>
    <!--description2-->
    <tbody id="description2" <?php if(!$description2Code){?> style="display:none"<?php }?>>
      <tr>
        <td class="inputLabel">Unik : </td>
        <td><label><input type="checkbox" name="description2Unique" id="description2Unique" value="1" <?php if($container[0]['DESCRIPTION2UNIQUE']){?> checked="checked"<?php }?> />Ya</label></td>
      </tr>
	  <tr>
        <td class="inputLabel"> Nama : </td>
        <td><input name="description2Name" type="text" class="inputInput" id="description2Name" size="50" value="<?php echo $container[0]['DESCRIPTION2NAME'];?>" /></td>
      </tr>
<!--      <tr>
        <td class="inputLabel">Jenis : </td>
        <td><select name="description2Type" class="inputList" id="description2Type" >
            <?php echo createDropDown($inputTypeList, $description2Type)?>
                </select></td>
      </tr>-->
      <tr>
        <td class="inputLabel">Nilai 'Default' : </td>
        <td><input name="description2DefaultValue" type="text" class="inputInput" id="description2DefaultValue" size="30" value="<?php echo $container[0]['DESCRIPTION2DEFAULTVALUE'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Lookup : </td>
        <td><label>
          <input name="description2LookupType" id="description2LookupType" type="radio" value="no" <?php if(!$container[0]['DESCRIPTION2LOOKUPTABLE']&&!$container[0]['DESCRIPTION2QUERY']){?> checked="checked"<?php }?> onclick="disableLookup('no','description2LookupTable','description2Query')" />
          Tiada Lookup</label>
            <label>
            <input type="radio" name="description2LookupType" id="description2LookupType" value="predefined" <?php if($container[0]['DESCRIPTION2LOOKUPTABLE']){?> checked="checked"<?php }?> onclick="disableLookup('predefined','description2LookupTable','description2Query')" />
              Predefined</label>
            <label>
            <input type="radio" name="description2LookupType" id="description2LookupType" value="advanced" <?php if($container[0]['DESCRIPTION2QUERY']){?> checked="checked"<?php }?> onclick="disableLookup('advanced','description2LookupTable','description2Query')" />
              Advanced</label>        </td>
      </tr>
      <tr>
        <td nowrap="nowrap" class="inputLabel">Predefined Lookup : </td>
        <td><select name="description2LookupTable" class="inputList" id="description2LookupTable" <?php if(!$container[0]['DESCRIPTION2LOOKUPTABLE']){?> disabled="disabled"<?php }?>>
          <?php echo createDropDown($lookupTableList, $container[0]['DESCRIPTION2LOOKUPTABLE'])?>
        </select></td>
      </tr>
      <tr>
        <td class="inputLabel">Advanced Lookup : </td>
        <td><textarea name="description2Query" cols="100" rows="5" class="inputInput" id="description2Query" <?php if(!$container[0]['DESCRIPTION2QUERY']){?> disabled="disabled"<?php }?> onblur="replaceQuote(this)"><?php echo $container[0]['description2query'];?></textarea></td>
      </tr>
    </tbody>
    <!--eof description2-->
    <tr>
      <td class="inputLabel"><strong>Kod Parent :</strong></td>
      <td>
	  	<label onclick="swapItemDisplay('parent|parentCodeUnique|parentCodeName|parentCodeDefaultValue|parentCodeLookupTable|parentCodeQuery','');
			disableLookup('no','parentCodeLookupTable','parentCodeQuery');">
        <input name="parentCode" type="radio" value="yes" <?php if($parentCode){?> checked="checked" <?php }?> />Ya 
        </label>
        <label onclick="swapItemDisplay('','parent|parentCodeUnique|parentCodeName|parentCodeDefaultValue|parentCodeLookupTable|parentCodeQuery')">
        <input type="radio" name="parentCode" value="no" <?php if(!$parentCode){?> checked="checked" <?php }?> />Tidak
        </label>
	  </td>
    </tr>
    <!--parent-->
    <tbody id="parent" <?php if(!$parentCode){?> style="display:none"<?php }?>>
      <tr>
        <td class="inputLabel">Unik : </td>
        <td><label><input type="checkbox" name="parentCodeUnique" id="parentCodeUnique" value="1" <?php if($container[0]['PARENTCODEUNIQUE']){?> checked="checked"<?php }?> />Ya</label></td>
      </tr>
	  <tr>
        <td class="inputLabel">Nama :</td>
        <td><input name="parentCodeName" type="text" class="inputInput" id="parentCodeName" size="50" value="<?php echo $container[0]['PARENTCODENAME'];?>" /></td>
      </tr>
<!--      <tr>
        <td class="inputLabel">Jenis :</td>
        <td><select name="parentCodeType" class="inputList" id="parentCodeType" >
            <?php echo createDropDown($inputTypeList, $parentCodeType)?>
                </select></td>
      </tr>-->
      <tr>
        <td class="inputLabel">Nilai 'Default' : </td>
        <td><input name="parentCodeDefaultValue" type="text" class="inputInput" id="parentCodeDefaultValue" size="30" value="<?php echo $container[0]['PARENTCODEDEFAULTVALUE'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Lookup : </td>
        <td>
			<label>
			<input name="parentCodeLookupType" id="parentCodeLookupType" type="radio" value="no" <?php if(!$container[0]['PARENTCODELOOKUPTABLE']&&!$container[0]['PARENTCODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('no','parentCodeLookupTable','parentCodeQuery')" />
			Tiada Lookup</label>
			<label>
			<input type="radio" name="parentCodeLookupType" id="parentCodeLookupType" value="predefined" <?php if($container[0]['PARENTCODELOOKUPTABLE']){?> checked="checked"<?php }?> onclick="disableLookup('predefined','parentCodeLookupTable','parentCodeQuery')" />
			Predefined</label>
			<label>
			<input type="radio" name="parentCodeLookupType" id="parentCodeLookupType" value="advanced" <?php if($container[0]['PARENTCODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('advanced','parentCodeLookupTable','parentCodeQuery')" />
			Advanced</label>
		</td>
      </tr>
      <tr>
        <td class="inputLabel">Predefined Lookup : </td>
        <td><select name="parentCodeLookupTable" class="inputList" id="parentCodeLookupTable" <?php if(!$container[0]['PARENTCODELOOKUPTABLE']){?> disabled="disabled"<?php }?>>
          <?php echo createDropDown($lookupTableList, $container[0]['PARENTCODELOOKUPTABLE'])?>
        </select></td>
      </tr>
      <tr>
        <td class="inputLabel">Advanced Lookup : </td>
        <td><textarea name="parentCodeQuery" cols="100" rows="5" class="inputInput" id="parentCodeQuery" <?php if(!$container[0]['PARENTCODEQUERY']){?> disabled="disabled"<?php }?> onblur="replaceQuote(this)"><?php echo $container[0]['parentcodequery'];?></textarea></td>
      </tr>
    </tbody>
    <!--eof perent-->
    <tr>
      <td class="inputLabel"><strong>Kod Parent Root :</strong></td>
      <td>
	  	<label onclick="swapItemDisplay('parentroot|parentRootCodeUnique|parentRootCodeName|parentRootCodeDefaultValue|parentRootCodeLookupTable|parentRootCodeQuery','');
			disableLookup('no','parentRootCodeLookupTable','parentRootCodeQuery');">
        <input name="parentRootCode" type="radio" value="yes" <?php if($parentRootCode){?> checked="checked" <?php }?> />Ya
        </label>
        <label onclick="swapItemDisplay('','parentroot|parentRootCodeUnique|parentRootCodeName|parentRootCodeDefaultValue|parentRootCodeLookupTable|parentRootCodeQuery')">
        <input type="radio" name="parentRootCode" value="no" <?php if(!$parentRootCode){?> checked="checked" <?php }?> />Tidak
        </label>
	  </td>
    </tr>
    <!--parent root-->
    <tbody id="parentroot" <?php if(!$parentRootCode){?> style="display:none"<?php }?>>
      <tr>
        <td class="inputLabel">Unik : </td>
        <td><label><input type="checkbox" name="parentRootCodeUnique" id="parentRootCodeUnique" value="1" <?php if($container[0]['PARENTROOTCODEUNIQUE']){?> checked="checked"<?php }?> />Ya</label></td>
      </tr>
	  <tr>
        <td class="inputLabel">Nama : </td>
        <td><input name="parentRootCodeName" type="text" class="inputInput" id="parentRootCodeName" size="50" value="<?php echo $container[0]['PARENTROOTCODENAME'];?>" /></td>
      </tr>
<!--      <tr>
        <td class="inputLabel">Jenis :</td>
        <td><select name="parentRootCodeType" class="inputList" id="parentRootCodeType" >
            <?php echo createDropDown($inputTypeList, $parentRootCodeType)?>
                </select></td>
      </tr>-->
      <tr>
        <td class="inputLabel">Default 'Nilai' : </td>
        <td><input name="parentRootCodeDefaultValue" type="text" class="inputInput" id="parentRootCodeDefaultValue" size="30" value="<?php echo $container[0]['PARENTROOTCODEDEFAULTVALUE'];?>" /></td>
      </tr>
      <tr>
        <td class="inputLabel">Lookup : </td>
        <td><label>
          <input name="parentRootCodeLookupType" id="parentRootCodeLookupType" type="radio" value="no" <?php if(!$container[0]['PARENTROOTCODELOOKUPTABLE']&&!$container[0]['PARENTROOTCODEQUERY']){?> checked="checked"<?php }?> onclick="disableLookup('no','parentRootCodeLookupTable','parentRootCodeQuery')" />
          Tiada Lookup</label>
            <label>
            <input type="radio" name="parentRootCodeLookupType" id="parentRootCodeLookupType" <?php if($container[0]['PARENTROOTCODELOOKUPTABLE']){?> checked="checked"<?php }?> value="predefined" onclick="disableLookup('predefined','parentRootCodeLookupTable','parentRootCodeQuery')" />
              Predefined</label>
            <label>
            <input type="radio" name="parentRootCodeLookupType" id="parentRootCodeLookupType" <?php if($container[0]['PARENTROOTCODEQUERY']){?> checked="checked"<?php }?> value="advanced" onclick="disableLookup('advanced','parentRootCodeLookupTable','parentRootCodeQuery')" />
              Advanced</label>        </td>
      </tr>
      <tr>
        <td class="inputLabel">Predefined Lookup : </td>
        <td><select name="parentRootCodeLookupTable" class="inputList" id="parentRootCodeLookupTable" <?php if(!$container[0]['PARENTROOTCODELOOKUPTABLE']){?> disabled="disabled"<?php }?>>
          <?php echo createDropDown($lookupTableList, $container[0]['PARENTROOTCODELOOKUPTABLE'])?>
        </select></td>
      </tr>
      <tr>
        <td class="inputLabel">Advanced Lookup : </td>
        <td><textarea name="parentRootCodeQuery" cols="100" rows="5" class="inputInput" id="parentRootCodeQuery" <?php if(!$container[0]['PARENTROOTCODEQUERY']){?> disabled="disabled"<?php }?> onblur="replaceQuote(this)"><?php echo $container[0]['parentrootcodequery'];?></textarea></td>
      </tr>
    </tbody>
    <!--eof parent root-->
	<tr>
      <td class="inputLabel">Senarai Akses : </td>
      <td><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><div align="center"><strong>Senarai Kumpulan Yang Tidak Dipilih
            </strong></div></td>
            <td>&nbsp;</td>
            <td><div align="center"><strong>Senarai Kumpulan Yang  Dipilih
            </strong></div></td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td width="250"><select style="width:250px;" name="nonSelectedGroup" size="10" multiple class="inputList" id="nonSelectedGroup" >
                <?php for($x=0; $x < $groupListNonSelectedCount; $x++) { ?>
                <option value="<?php echo $groupListNonSelected[$x][0]?>" ><?php echo $groupListNonSelected[$x][1];?></option>
                <?php } ?>
              </select></td>
            <td width="35"><div align="center">
                <input name="newMoveLTR" type="button" class="inputButton" id="newMoveLTR" value="&gt;" style="margin-bottom:2px;" onClick="moveoutid('nonSelectedGroup','selectedGroup'); " />
                <input name="newMoveRTL" type="button" class="inputButton" id="newMoveRTL" value="&lt;" style="margin-bottom:2px;"  onClick="moveinid('nonSelectedGroup','selectedGroup'); " />
                <br>
                <input name="newMoveAllLTR" type="button" class="inputButton" id="newMoveAllLTR" value="&gt;&gt;" style="margin-bottom:2px;" onClick="listBoxSelectall('nonSelectedGroup'); moveoutid('nonSelectedGroup','selectedGroup'); " />
                <input name="newMoveAllRTL" type="button" class="inputButton" id="newMoveAllRTL" value="&lt;&lt;" style="margin-bottom:2px;" onClick="listBoxSelectall('selectedGroup'); moveinid('nonSelectedGroup','selectedGroup'); " />
                <input name="newSort" type="button" class="inputButton" id="newSort" value="a-z" style="margin-bottom:2px;" onClick="sortListBox('selectedGroup');sortListBox('nonSelectedGroup')   " />
              </div></td>
            <td><select style="width:250px;" name="selectedGroup[]" size="10" multiple class="inputList" id="selectedGroup" >
                <?php for($x=0; $x < $groupListSelectedCount; $x++) { ?>
                <option value="<?php echo $groupListSelected[$x][0]?>" ><?php echo $groupListSelected[$x][1];?></option>
                <?php } ?>
              </select></td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
    </tr>
	<tbody style="display:none"> <!--hidden start here-->
    <tr>
      <td class="inputLabel"><strong>Permission Role :</strong></td>
      <td>
		
		<!--appendable field-->
        <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendRole">
		<?php for($x=0; $x<$sizeOfRoleCodeRows || $x==0; $x++){?>
          <tr>
            <td>
				<select name="RoleCode[]" class="inputList" id="RoleCode[]">
					<?php if($_POST['newCat'])$RoleCode[$x]='200000002'; echo createDropDown($RoleList, $RoleCode[$x])?>
				</select>
					
				Jarak Data Dari
				<input name="DataRangeFrom[]" type="text" class="inputInput" id="DataRangeFrom[]" size="5" value="<?php echo $DataRangeFrom[$x];?>" />
				Hingga
				<input name="DataRangeTo[]" type="text" class="inputInput" id="DataRangeTo[]" size="5" value="<?php echo $DataRangeTo[$x];?>" />
			<?php if($x==0){?>
				<input name="addRole" type="button" class="inputButton" id="addRole" value="Tambah" onclick="addRoleField('appendRole', 'RoleCode[]', roleList)" />
			<?php }?>
			</td>
          </tr>
		<?php }?>
        </table>
        <!--eof appendable field-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Baru</td>
      <td><label><input name="ButtonCodeNew" type="radio" value="yes" <?php if($ButtonCodeNew){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttonnew')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodeNew" value="no" <?php if(!$ButtonCodeNew){?> checked="checked"<?php }?> onclick="hideBlock('no','buttonnew')" />
        Tidak</label>
		<!--buttonnew-->
		<div id="buttonnew" <?php if(!$ButtonCodeNew){?>style="display:none"<?php }?>>Nama :
		  <input name="ButtonNameNew" type="text" class="inputInput" id="ButtonNameNew" size="20" value="<?php echo $ButtonNameNew;?>" />
		  Tajuk :
		  <input name="ButtonTitleNew" type="text" class="inputInput" id="ButtonTitleNew" size="20" value="<?php echo $ButtonTitleNew;?>" />
		  
		  <!--appendable field-->
		  <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendNew">
		    <tr>
		      <td>
		        <?php for($x=0; $x<$sizeOfButtonRoleNewRows || $x==0; $x++){?>
		        <select name="ButtonRoleNew[]" class="inputList" id="ButtonRoleNew[]">
		          <?php echo createDropDown($RoleList, $ButtonRoleNew[$x])?>
	            </select>
		        <?php }?>
		        
		        <input name="addButtonNew" type="button" class="inputButton" id="addButtonNew" value="Tambah" onclick="addDropDown('appendNew', 'ButtonRoleNew[]', roleList)" />			</td>
            </tr>
	      </table>
          <!--eof appendable field-->
	    </div><!--eof buttonnew-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Simpan</td>
      <td><label><input name="ButtonCodeSave" type="radio" value="yes" <?php if($ButtonCodeSave){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttonsave')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodeSave" value="no" <?php if(!$ButtonCodeSave){?> checked="checked"<?php }?> onclick="hideBlock('no','buttonsave')" />
        Tidak</label>
		
		<!--buttonsave-->
		<div id="buttonsave" <?php if(!$ButtonCodeSave){?>style="display:none"<?php }?>>Nama :
        <input name="ButtonNameSave" type="text" class="inputInput" id="ButtonNameSave" size="20" value="<?php echo $ButtonNameSave;?>" />
        Tajuk :
        <input name="ButtonTitleSave" type="text" class="inputInput" id="ButtonTitleSave" size="20" value="<?php echo $ButtonTitleSave;?>" />
		
        <!--appendable field-->
        <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendSave">
          <tr>
            <td>
				<?php for($x=0; $x<$sizeOfButtonRoleSaveRows || $x==0; $x++){?>
				<select name="ButtonRoleSave[]" class="inputList" id="ButtonRoleSave[]">
				  <?php echo createDropDown($RoleList, $ButtonRoleSave[$x]);?>
				</select>
				<?php }?>
				
				<input name="addButtonSave" type="button" class="inputButton" id="addButtonSave" value="Tambah" onclick="addDropDown('appendSave', 'ButtonRoleSave[]', roleList)" />			</td>
          </tr>
        </table>
        <!--eof appendable field-->
		</div>
		<!--eof buttonsave-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Ubah</td>
      <td><label><input name="ButtonCodeUpdate" type="radio" value="yes" <?php if($ButtonCodeUpdate){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttonupdate')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodeUpdate" value="no" <?php if(!$ButtonCodeUpdate){?> checked="checked"<?php }?> onclick="hideBlock('no','buttonupdate')" />
        Tidak</label>
		
		<!--buttonupdate-->
		<div id="buttonupdate" <?php if(!$ButtonCodeUpdate){?>style="display:none"<?php }?>>Nama :
		  <input name="ButtonNameUpdate" type="text" class="inputInput" id="ButtonNameUpdate" size="20" value="<?php echo $ButtonNameUpdate;?>" />
		  Tajuk :
		  <input name="ButtonTitleUpdate" type="text" class="inputInput" id="ButtonTitleUpdate" size="20" value="<?php echo $ButtonTitleUpdate;?>" />
		  
		  <!--appendable field-->
		  <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendUpdate">
		    <tr>
		      <td>
		        <?php for($x=0; $x<$sizeOfButtonRoleUpdateRows || $x==0; $x++){?>
		        <select name="ButtonRoleUpdate[]" class="inputList" id="ButtonRoleUpdate[]">
		          <?php echo createDropDown($RoleList, $ButtonRoleUpdate[$x]);?>
	            </select>
		        <?php }?>
		        
		        <input name="addButtonUpdate" type="button" class="inputButton" id="addButtonUpdate" value="Tambah" onclick="addDropDown('appendUpdate', 'ButtonRoleUpdate[]', roleList)" />			</td>
            </tr>
	      </table>
          <!--eof appendable field-->
	    </div><!--eof buttonupdate-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Padam</td>
      <td><label><input name="ButtonCodeDelete" type="radio" value="yes" <?php if($ButtonCodeDelete){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttondelete')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodeDelete" value="no" <?php if(!$ButtonCodeDelete){?> checked="checked"<?php }?> onclick="hideBlock('no','buttondelete')" />
        Tidak</label>
		
		<!--buttondelete-->
		<div id="buttondelete" <?php if(!$ButtonCodeDelete){?>style="display:none"<?php }?>>Nama :
		  <input name="ButtonNameDelete" type="text" class="inputInput" id="ButtonNameDelete" value="<?php echo $ButtonNameDelete;?>" size="20" />
		  Tajuk :
		  <input name="ButtonTitleDelete" type="text" class="inputInput" id="ButtonTitleDelete" size="20" value="<?php echo $ButtonTitleDelete;?>" />
		  
		  <!--appendable field-->
		  <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendDelete">
		    <tr>
		      <td>
		        <?php for($x=0; $x<$sizeOfButtonRoleDeleteRows || $x==0; $x++){?>
		        <select name="ButtonRoleDelete[]" class="inputList" id="ButtonRoleDelete[]">
		          <?php echo createDropDown($RoleList, $ButtonRoleDelete[$x]);?>
	            </select>
		        <?php }?>
		        
		        <input name="addButtonDelete" type="button" class="inputButton" id="addButtonDelete" value="Tambah" onclick="addDropDown('appendDelete', 'ButtonRoleDelete[]', roleList)" />			</td>
            </tr>
	      </table>
          <!--eof appendable field-->
	    </div><!--eof buttondelete-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Batal</td>
      <td><label><input name="ButtonCodeCancel" type="radio" value="yes" <?php if($ButtonCodeCancel){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttoncancel')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodeCancel" value="" <?php if(!$ButtonCodeCancel){?> checked="checked"<?php }?> onclick="hideBlock('no','buttoncancel')" />
        Tidak</label>
		
		<!--buttoncancel-->
		<div id="buttoncancel" <?php if(!$ButtonCodeCancel){?>style="display:none"<?php }?>>Nama :
		  <input name="ButtonNameCancel" type="text" class="inputInput" id="ButtonNameCancel" size="20" value="<?php echo $ButtonNameCancel;?>" />
		  Tajuk :
		  <input name="ButtonTitleCancel" type="text" class="inputInput" id="ButtonTitleCancel" size="20" value="<?php echo $ButtonTitleCancel;?>" />
		  
		  <!--appendable field-->
		  <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendCancel">
		    <tr>
		      <td>
		        <?php for($x=0; $x<$sizeOfButtonRoleCancelRows || $x==0; $x++){?>
		        <select name="ButtonRoleCancel[]" class="inputList" id="ButtonRoleCancel[]">
		          <?php echo createDropDown($RoleList, $ButtonRoleCancel[$x]);?>
	            </select>
		        <?php }?>
		        
		        <input name="addButtonCancel" type="button" class="inputButton" id="addButtonCancel" value="Tambah" onclick="addDropDown('appendCancel', 'ButtonRoleCancel[]', roleList)" />			</td>
            </tr>
	      </table>
          <!--eof appendable field-->
	    </div><!--eof buttoncancel-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Cetak</td>
      <td><label><input name="ButtonCodePrint" type="radio" value="<?php echo $ButtonCodePrint;?>" <?php if($ButtonCodePrint){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttonprint')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodePrint" value="no" <?php if(!$ButtonCodePrint){?> checked="checked"<?php }?> onclick="hideBlock('no','buttonprint')" />
        Tidak</label>
		
		<!--buttonprint-->
		<div id="buttonprint" <?php if(!$ButtonCodePrint){?>style="display:none"<?php }?>>
		Nama :
          <input name="ButtonNamePrint" type="text" class="inputInput" id="ButtonNamePrint" size="20" value="<?php echo $ButtonNamePrint;?>" />
        Tajuk : 
        <input name="ButtonTitlePrint" type="text" class="inputInput" id="ButtonTitlePrint" size="20" value="<?php echo $ButtonTitlePrint;?>" />
		
        <!--appendable field-->
        <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendPrint">
          <tr>
            <td>
				<?php for($x=0; $x<$sizeOfButtonRolePrintRows || $x==0; $x++){?>
				<select name="ButtonRolePrint[]" class="inputList" id="ButtonRolePrint[]">
				  <?php echo createDropDown($RoleList, $ButtonRolePrint[$x]);?>
				</select>
				<?php }?>
				
				<input name="addButtonPrint" type="button" class="inputButton" id="addButtonPrint" value="Tambah" onclick="addDropDown('appendPrint', 'ButtonRolePrint[]', roleList)" />			</td>
          </tr>
        </table>
        <!--eof appendable field-->
		</div>
		<!--eof buttonprint-->
	  </td>
    </tr>
    <tr>
      <td class="inputLabel">Reset</td>
      <td><label><input name="ButtonCodeReset" type="radio" value="yes" <?php if($ButtonCodeReset){?> checked="checked"<?php }?> onclick="hideBlock('yes','buttonreset')" />
          Ya</label>
        <label><input type="radio" name="ButtonCodeReset" value="no" <?php if(!$ButtonCodeReset){?> checked="checked"<?php }?> onclick="hideBlock('no','buttonreset')" />
        Tidak</label>
		
		<!--buttonreset-->
		<div id="buttonreset" <?php if(!$ButtonCodeReset){?> style="display:none"<?php }?>>
		Nama:
        <input name="ButtonNameReset" type="text" class="inputInput" id="ButtonNameReset" size="20" value="<?php echo $ButtonNameReset;?>" />
        Tajuk
        <input name="ButtonTitleReset" type="text" class="inputInput" id="ButtonTitleReset" size="20" value="<?php echo $ButtonTitleReset;?>" />
				
        <!--appendable field-->
        <table width="100%" border="0" cellspacing="0" cellpadding="2" id="appendReset">
          <tr>
            <td>
				<?php for($x=0; $x<$sizeOfButtonRoleResetRows || $x==0; $x++){?>
				<select name="ButtonRoleReset[]" class="inputList" id="ButtonRoleReset[]">
				  <?php echo createDropDown($RoleList, $ButtonRoleReset[$x]);?>
				</select>
				<?php }?>
				
				<input name="addButtonReset" type="button" class="inputButton" id="addButtonReset" value="Add" onclick="addDropDown('appendReset', 'ButtonRoleReset[]', roleList)" />			</td>
          </tr>
        </table>
        <!--eof appendable field-->
		</div>
		<!--eof buttonreset-->
	  </td>
    </tr>
	</tbody> <!--hidden end here-->
    <tr>
      <td class="contentButtonFooter" colspan="2">
        <input name="referenceID" type="hidden" id="referenceID" value="<?php echo $container[0]['REFERENCEID'];?>" />
        <input name="<?php if($_POST['editCat']) echo 'saveEditCat';else echo 'saveNewCat';?>" type="submit" class="inputButton" id="<?php if($_POST['editCat']) echo 'saveEditCat';else echo 'saveNewCat';?>" value="Simpan" onclick="listBoxSelectall('selectedGroup'); if(havValue(document.getElementById('referenceName').value,document.getElementById('referenceTitle').value))return true; else {alert('Tajuk dan Nama Rujukan adalah wajib');return false}"/>
        <input name="cancel" type="submit" class="inputButton" id="cancel" value="Batal" />
      </td>
    </tr>
  </table>
</form>
<!--eof reference category-->
<?php }//eof manipulation of data?>

<?php if($_POST['newData']||$_GET['dataid']){?>
<!--reference data-->
<form id="form3" name="form3" method="post" action="<?php echo $action;?>">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">Data Rujukan</th>
  </tr>
  <?php if($_GET['dataid']&&$userType==1) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][0];?></td>
    <td><input name="dataID" type="text" class="inputInput" id="dataID" value="<?php echo $data[0][0];?>" size="15" readonly="yes" /></td>
  </tr>
  <?php }?>
  <?php if($data['Name'][1]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][1]; ?> : </td>
    <td>
    <?php if(is_array($groupCodeList)){?>
		<select name="groupCode" class="inputList" id="groupCode" <?php if($userType==3){?> disabled="disabled" style="color:#000000; background-color: #FFFFFF;" <?php }?> />
		<?php echo createDropDown($groupCodeList,$data[0][1]);?>
		</select>
	<?php }else {?>
		<input name="groupCode" type="text" class="inputList" id="groupCode" value="<?php echo $data[0][1];?>" <?php if($userType==3){?> readonly <?php }?>/>
	<?php }?>
  	</td>
  </tr>
  <?php }?>
  <?php if($data['Name'][2]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][2]; ?> : </td>
	<td>
    <?php if(is_array($codeList)){?>
		<select name="referenceCode" class="inputList" id="referenceCode" <?php if($userType==3){?> disabled="disabled" style="color:#000000; background-color: #FFFFFF;" <?php }?> />
		<?php echo createDropDown($codeList,$data[0][2]);?>
		</select>
	<?php }else {?>
		<input name="referenceCode" type="text" class="inputList" id="referenceCode" value="<?php echo $data[0][2];?>" <?php if($userType==3){?> readonly <?php }?> />
	<?php }?>
  </tr>
  <?php }?>
  <?php if($data['Name'][3]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][3]; ?> :</td>
    <td>
    <?php if(is_array($description1List)){?>
		<select name="description1" class="inputList" id="description1" <?php if($userType==3){?> disabled="disabled" style="color:#000000; background-color: #FFFFFF;" <?php }?> />
		<?php echo createDropDown($description1List,$data[0][3]);?>
		</select>
	<?php }else {?>
		<input name="description1" type="text" size="50" class="inputList" id="description1" value="<?php echo $data[0][3];?>" <?php if($userType==3){?> readonly <?php }?> />
	<?php }?>
  	</td>
  </tr>
  <?php }?>
  <?php if($data['Name'][4]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][4]; ?> : </td>
    <td>
    <?php if(is_array($description2List)){?>
		<select name="description2" class="inputList" id="description2" <?php if($userType==3){?> disabled="disabled" style="color:#000000; background-color: #FFFFFF;" <?php }?> />
		<?php echo createDropDown($description2List,$data[0][4]);?>
		</select>
	<?php }else {?>
		<input name="description2" type="text" size="50" class="inputList" id="description2" value="<?php echo $data[0][4];?>" <?php if($userType==3){?> readonly <?php }?> />
	<?php }?>
  	</td>
  </tr>
  <?php }?>
  <?php if($data['Name'][5]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][5]; ?> : </td>
    <td>
    <?php if(is_array($parentCodeList)){?>
		<select name="parentCode" class="inputList" id="parentCode" <?php if($userType==3){?> disabled="disabled" style="color:#000000; background-color: #FFFFFF;" <?php }?> />
		<?php echo createDropDown($parentCodeList,$data[0][5]);?>
		</select>
	<?php }else {?>
		<input name="parentCode" type="text" class="inputList" id="parentCode" value="<?php echo $data[0][5];?>" <?php if($userType==3){?> readonly <?php }?> />
	<?php }?>
  	</td>
  </tr>
  <?php }?>
  <?php if($data['Name'][6]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][6]; ?> : </td>
    <td>
    <?php if(is_array($parentRootCodeList)){?>
		<select name="parentRootCode" class="inputList" id="parentRootCode" <?php if($userType==3){?> disabled="disabled" style="color:#000000; background-color: #FFFFFF;" <?php }?> />
		<?php echo createDropDown($parentRootCodeList,$data[0][6]);?>
		</select>
	<?php }else {?>
		<input name="parentRootCode" type="text" class="inputList"  id="parentRootCode" value="<?php echo $data[0][6];?>" <?php if($userType==3){?> readonly <?php }?> />
	<?php }?>
  	</td>
  </tr>
  <?php }?>
  <?php if(!$_POST['newData'] && $userType !=3 && $data['Name'][7]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][7]; ?> :</td>
    <td><input name="timestamp" type="text" class="inputInput" id="timestamp" value="<?php echo $data[0][7];?>" size="15" readonly="yes" /></td>
  </tr>
  <?php }?>
  <?php if($userType !=3 && $data['Name'][8]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][8]; ?> :</td>
    <td><select name="statusCode" class="inputList" id="statusCode">
		<?php
		//if status not been set
		if(!isset($data[0][8]))
			$data[0][8]='00';	//set default
			
		echo createDropDown($statusList,$data[0][8]);
		?>
      </select>
	</td>
  </tr>
  <?php }?>
  <?php if(!$_POST['newData'] && $userType !=3 && $data['Name'][9]) {?>
  <tr>
    <td class="inputLabel"><?php echo $data['Name'][9]; ?> :</td>
    <td><input name="userName" type="text" class="inputInput" id="userName" value="<?php echo $data[0][9]; ?>" size="15" readonly="yes" /></td>
  </tr>
  <?php }?>
  <tr>
    <td class="contentButtonFooter" colspan="2" align="right">
	<?php if($userType!=3){?>
        <input name="<?php if($_POST['newData']) echo 'saveNewData';else echo 'saveEditData';?>" type="submit" class="inputButton" id="<?php if($_POST['newData']) echo 'saveNewData';else echo 'saveEditData';?>" value="Simpan" />
	<?php }?>
        <input name="cancel" type="submit" class="inputButton" id="cancel" value="Batal" />
		<input name="referenceID" type="hidden" id="referenceID" value="<?php echo $_POST['referenceID'];?>" />
		<input name="dataID" type="hidden" class="inputInput" id="dataID" value="<?php echo $data[0][0];?>" />
    </td>
  </tr>
</table>
</form>
<!--eof reference data-->
<?php }?>
