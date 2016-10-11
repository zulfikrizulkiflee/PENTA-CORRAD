<?php

/*
 *
 *

select upper(a.name) as COLUMN_NAME, a.id as colid
					from syscolumns a, sysobjects b
					where a.id = b.id and b.type = 'U'
                    and b.name = 'PRUSER'
					order by 1



                    select o.name, i.indid, i.keycnt,
i.*
from sysobjects o, sysindexes i,
where o.id=i.id
and i.indid > 0
and o.type = 'U'
and o.name = 'FLC_BL'
and i.status2 & 2 = 2
order by o.name





select o.name, i.indid, i.keycnt, x.col_name
from
    sysobjects o, sysindexes i,
    (select upper(a.name) as col_name, a.colid
					from syscolumns a, sysobjects b
					where a.id = b.id and b.type = 'U'
                    and b.name = 'PRUSER') x
where o.id=i.id
    and i.indid > 0

    --THE OFFENDING LINE
    and i.indid = x.colid


    and o.type = 'U'
    and o.name = 'PRUSER'
    and i.status2 & 2 = 2
order by o.name


 *
 *
*/






//validate that user have session
require_once('func_common.php');
validateUserSession();
unset($_SESSION['PAGE_WIZARD']);

//todo - kalau mysql type float - tak buat lagi untuk page wizard
ini_set('max_execution_time','150');

$tableName = $_POST['table_name'];
$getTableRs = $mySQL->listTable(DB_OTHERS);							//list all tables
//$getViewRs = $mySQL->listView(DB_OTHERS);							//list all views

if(DBMS_NAME == 'mysql')
	$columnList = $mySQL->listTableColumn('','',$tableName,'','ORDINAL_POSITION');			//list all columns for selected tables
else if(DBMS_NAME == 'oracle')
	$columnList = $mySQL->listTableColumn('','',$tableName,'',null,'COLUMN_ID');			//list all columns for selected tables
else
	$columnList = $mySQL->listTableColumn('','',$tableName,'','','COLUMN_ID');			//list all columns for selected tables

//data type
$dataType_numeric 	= array('INT','INTEGER','NUMBER','NUMERIC','DECIMAL','INTN','MONEYN','SMALLINT','TINYINT','MONEY');
$dataType_string 	= array('VARCHAR','VARCHAR2','TEXT','CLOB','CHAR');
$dataType_datetime 	= array('DATETIME','TIMESTAMP','DATETIMN');
$dataType_date 		= array('DATE','DATEN');

//combine all data type
$dataTypeList 		= array('NUMERIC' 	=> $dataType_numeric,
							'STRING' 	=> $dataType_string,
							'DATETIME' 	=> $dataType_datetime,
							'DATE' 		=> $dataType_date);

//get all item type
$itemTypeRs = $myQuery->query("	select REFERENCECODE,DESCRIPTION2 from REFSYSTEM
								where MASTERCODE = (select REFERENCECODE
													from REFSYSTEM
													where MASTERCODE =  'XXX'
													and DESCRIPTION1 = 'SYS_INPUT_TYPE')
													order by DESCRIPTION2,REFERENCECODE",'SELECT','NAME');

//get all refgeneral
$predefinedRefs = $myQuery->query("select REFERENCECODE,DESCRIPTION1
									from REFGENERAL where MASTERCODE = 'XXX'
									order by DESCRIPTION1",'SELECT','NAME');

//$myQuery->query('delete from FLC_PAGE where PAGEID > 2','RUN');
//$myQuery->query("delete from FLC_PAGE_COMPONENT where COMPONENTNAME like '%PW_%'",'RUN');

function convertDateToStandard($format,$date)
{
	if($format == 'Y-m-d')
		$date = $date;
	else if($format == 'd-m-Y')
		$date = implode('-',array_reverse(explode('-',$date)));		//reverse the date

	return $date;
}

//get all primary keys from post data
function collectPrimaryKeys($postData)
{
	//collect primary keys
	$primaryCol = array();
	$primaryColDataType = array();
	$primaryColName = array();
	$primaryColAutoInc = array();

	//get primary key list
	for($x=0; $x < $postData['colCount']; $x++)
	{
		//get selected cols
		if($postData['sel_col_'.$x])
		{
			if($postData['attr_primary_'.$x] != '')
			{
				$primaryCol[] = $postData['attr_primary_'.$x];
				$primaryColDataType[] = $postData['coldatatype_'.$x];
				$primaryColName[] = strtolower($postData['name_'.$x]);
				$primaryColAutoInc[] = $postData['colautoinc_'.$x];
			}
		}
	}
	return array($primaryCol,$primaryColDataType,$primaryColName,$primaryColAutoInc);
}

//get all columns and data type from post data
function collectAttribute($postData)
{
	//collect primary keys
	$cols = array();
	$type = array();
	$flcType = array();
	$name = array();
	$lookup_pred = array();

	//get primary key list
	for($x=0; $x < $postData['colCount']; $x++)
	{
		//get selected cols
		if($postData['sel_col_'.$x])
		{
			$cols[] = $postData['colname_'.$x];
			$type[] = $postData['coldatatype_'.$x];
			$flcType[] = $postData['type_'.$x];
			$name[] = strtolower($postData['name_'.$x]);

			if($postData['lookup_type_'.$x] == 'predefined')
				$lookup_pred[] = $postData['lookup_predefined_'.$x];
			else
				$lookup_pred[] = '';
		}
	}
	return array($cols,$type,$flcType,$name,$lookup_pred);
}

function insertMenu($parent=0,$pageType,$table,$mySQL,$myQuery)
{
	//if parent is not selected
	if($parent == 0)
	{
		//check if FLC_PAGE_WIZARD parent menu exists
		$checkParent = $myQuery->query("select * from FLC_MENU where MENUNAME = 'FLC_PAGE_WIZARD'",'SELECT','NAME');

		//if exists, get the menuid
		if(count($checkParent))
		{
			//for childrens info
			$parent = $checkParent[0]['MENUID'];
			$level = $checkParent[0]['MENULEVEL']+1;
			$menuRoot = $checkParent[0]['MENUID'];
		}
		//else, create new parent menu
		else
		{
			//create parent menu
			$maxID = $mySQL->maxValue('FLC_MENU','MENUID',1)+1;																		//get max menuid
			$maxOrder = $myQuery->query("select MAX(MENUORDER)+1 as MAXX from FLC_MENU where MENULEVEL = 1",'SELECT','NAME');		//get max order menulevel 1

			if($maxOrder[0]['MAXX'] == '')
				$maxOrder = 1;
			else
				$maxOrder = $maxOrder[0]['MAXX'];

			//insert parent menu
			$myQuery->query($qry = "insert into FLC_MENU (MENUID,MENUNAME,MENUTITLE,MENUPARENT,MENULEVEL,MENUORDER,MENUSTATUS,LINKTYPE,MENUSTATE)
									values (".$maxID.",'FLC_PAGE_WIZARD','CORRAD PAGE WIZARD',0,1,".$maxOrder.",'1','1',0)",'RUN');

			//for childrens info
			$parent = $maxID;
			$level = 2;
			$menuRoot = $maxID;
		}
	}

	//insert children menu
	$maxID = $mySQL->maxValue('FLC_MENU','MENUID',1)+1;																		//get children max menuid
	$maxOrder = $myQuery->query("select MAX(MENUORDER)+1 as MAXX
								from FLC_MENU where MENULEVEL = ".$level."
								and MENUPARENT = ".$parent,'SELECT','NAME');		//get children max order

	if($maxOrder[0]['MAXX'] == '')
		$maxOrder = 1;
	else
		$maxOrder = $maxOrder[0]['MAXX'];

	$name = 'PW_MENU_'.strtoupper($pageType).'_'.$table;

	if($pageType == 'insert')
		$title = 'Kemasukan '.$table;

	else if($pageType == 'update')
		$title = 'Kemaskini '.$table;

	else if($pageType == 'delete')
		$title = 'Hapus '.$table;

	else if($pageType == 'select')
		$title = 'Senarai '.$table;

	//if pagetype = delete / update, hide the menu
	if($pageType == 'update' || $pageType == 'delete')
		$status = 0;
	else
		$status = 1;

	$myQuery->query("insert into FLC_MENU (MENUID,MENUNAME,MENUTITLE,MENUPARENT,MENULEVEL,MENUORDER,MENULINK,MENUSTATUS,MENUROOT,LINKTYPE,MENUSTATE)
					values (".$maxID.",'".$name."','".$title."',".$parent.",".$level.",".$maxOrder.",'index.php?page=page_wrapper','".$status."','".$menuRoot."','1',0)",'RUN');

	return $maxID;
}

function insertPage($parent,$pageType,$table,$mySQL,$myQuery)
{
	$maxID = $mySQL->maxValue('FLC_PAGE','PAGEID',1)+1;					//get page max pageid

	$name = 'PW_PAGE_'.strtoupper($pageType).'_'.$table;

	if($pageType == 'insert')
		$title = 'Kemasukan '.$table;

	else if($pageType == 'update')
		$title = 'Kemaskini '.$table;

	else if($pageType == 'delete')
		$title = 'Hapus '.$table;

	else if($pageType == 'select')
		$title = 'Senarai '.$table;

	$myQuery->query("insert into FLC_PAGE (PAGEID,PAGENAME,PAGETITLE,PAGEBREADCRUMBS,PAGEDESC,MENUID)
					values (".$maxID.",'".$name."','".$title."','".$title."','Generated using Page Wizard',".$parent.")",'RUN');

	return $maxID;
}

function insertComponent($parent,$pageType,$compType,$table,$mySQL,$myQuery)
{
	//get all columns name and data type
	$allCols = collectAttribute($_POST);
	$allColsName = $allCols[0];
	$allColsDataType = $allCols[1];
	$allColsFlcType = $allCols[2];
	$allColsPredLookup = $allCols[4];


	$maxID = $mySQL->maxValue('FLC_PAGE_COMPONENT','COMPONENTID',1)+1;										//get page max componentid
	$maxOrder = $myQuery->query("select MAX(COMPONENTORDER)+1 as MAXX
									from FLC_PAGE_COMPONENT where PAGEID = ".$parent,'SELECT','NAME');		//get max component order

	if($maxOrder[0]['MAXX'] == '')
		$maxOrder = 1;
	else
		$maxOrder = $maxOrder[0]['MAXX'];

	//$name = 'PW_COMP_'.strtoupper($pageType).'_'.$table;
	$name = $table;

	if($pageType == 'insert' || $pageType == 'update' || $pageType == 'delete')
	{
		if($compType == 'tabular')
		{
			if($pageType == 'insert')
			{
				$compPre = '';
				$compPost = $pageType;
				$compBindType = 'table';
				$compBindSrc = $table;
			}
			else if($pageType == 'update')
			{
				$compPre = 'select';
				$compPost = $pageType;
				$compBindType = 'table';
				$compBindSrc = $table;
			}
			else if($pageType == 'delete')
			{
				$compPre = '';
				$compPost = '';
				$compBindType = 'table';
				$compBindSrc = $table;
			}

			//common attributes
			$title = 'Maklumat '.$table;
			$compType = $compType;
			$compQuery = 'null';
			$compRowNo = '10';
			$compAddRow = 'null';
			$compDelRow = 'null';
			$compAddRowDisabled = 'null';
			$compDelRowDisabled = 'null';
		}

		else if($compType == 'form_1_col' || $compType == 'form_2_col')
		{
			$title = 'Maklumat '.$table;
			$compType = $compType;
			$compQuery = 'null';
			$compRowNo = 'null';
			$compAddRow = 'null';
			$compDelRow = 'null';
			$compAddRowDisabled = 'null';
			$compDelRowDisabled = 'null';
		}
	}
	else if($pageType == 'select')
	{
		if($compType != 'search_constraint')
		{
			$title = 'Senarai masuk  '.$table;
			$compType = 'report';
			$listingList = array();
			$compQuery = '';
			$fromStatement = array();
			$whereStatement = array();

			$asciiStart = 98;		//start character for FROM statement


			//collect primary keys
			$primaryArr = collectPrimaryKeys($_POST);
			$primaryCol = $primaryArr[0];
			$primaryColName = $primaryArr[2];
			$primaryColAutoInc = $primaryArr[3];

			$primaryKeyStrArr = array();

			for($x=0; $x < count($primaryCol); $x++)
			{
			if(DBMS_NAME == 'mysql')
				$primaryKeyStrArr[] = "&".$primaryColName[$x]."='',".$primaryCol[$x].",''";
			else if(DBMS_NAME == 'oracle' || strtolower(DBMS_NAME) == 'informix')
				$primaryKeyStrArr[] = "&".$primaryColName[$x]."=''||".$primaryCol[$x]."";
			else if(DBMS_NAME == 'sybase_ASE')
				$primaryKeyStrArr[] = "&".$primaryColName[$x]."=''||convert(varchar,".$primaryCol[$x].")";

			}
			$primaryKeyStr = implode('',$primaryKeyStrArr);


			//get list of all columns
			for($x=0; $x < $_POST['colCount']; $x++)
			{
				//get selected columns for listing ONLY
				if($_POST['attr_listing_'.$x])
				{
					//ITEMUPLOAD ---- jpg;gif;png|upload/pelanggan|1000

					if($allColsFlcType[$x] == 'file')
						$listingList[] = "concat(''<img src=\"'',".$_POST['attr_listing_'.$x].",''\" />'')";
					else
						$listingList[] = 'a.'.$_POST['attr_listing_'.$x];
				}
			}

			//if ada update, and delete

			//mysql
			if(strtolower(DBMS_NAME) == 'mysql')
			{
				$link  = ",\r\n\t concat(''<a href=\"index.php?page=page_wrapper&menuID=".$_SESSION['PAGE_WIZARD']['update'].$primaryKeyStr."\">Kemaskini</a> | ";
				$link .= "<a href=\"javascript:void(0)\" onclick=\"if(window.confirm(''''Anda pasti untuk membuang rekod ini?'''')) { window.location = ''''index.php?page=page_wrapper&menuID=".$_SESSION['PAGE_WIZARD']['delete'].$primaryKeyStr."'''';} else return false;\">Hapus</a>'') as Tindakan";
			}
			else if(strtolower(DBMS_NAME) == 'oracle' || DBMS_NAME == 'sybase_ASE' || strtolower(DBMS_NAME) == 'informix')
			{
				$link  = ", ''<a href=\"index.php?page=page_wrapper&menuID=".$_SESSION['PAGE_WIZARD']['update']."''||''".$primaryKeyStr."||''\">Kemaskini</a> | ";
				$link .= "<a href=\"index.php?page=page_wrapper&menuID=".$_SESSION['PAGE_WIZARD']['delete']."''||''".$primaryKeyStr."||''\">Hapus</a>'' as Tindakan";
			}
			/*
			else if(DBMS_NAME == 'sybase_ASE')
			{
				$link  = ", ''<a href=\"index.php?page=page_wrapper&menuID=".$_SESSION['PAGE_WIZARD']['update']."''||convert(varchar,''".$primaryKeyStr.",)||''\">Kemaskini</a> | ";
				$link .= "<a href=\"index.php?page=page_wrapper&menuID=".$_SESSION['PAGE_WIZARD']['delete']."''||convert(varchar,''".$primaryKeyStr.",)||''\">Hapus</a>'' as Tindakan";
			}
			*/

			//create from statement for lookup
			for($x=0; $x < count($allColsPredLookup); $x++)
			{
				if($allColsPredLookup[$x] != '')
				{
					$lookup = "(\r\n\t\tselect REFERENCECODE, DESCRIPTION1\r\n\t\tfrom REFGENERAL\r\n";
					$lookup .= "\t\twhere MASTERCODE = (select REFERENCECODE from REFGENERAL\r\n";
					$lookup .= "\t\t\twhere MASTERCODE = ''XXX'' and DESCRIPTION1 = ''".$allColsPredLookup[$x]."'')\r\n";
					$lookup .= "\t\tand REFERENCESTATUSCODE = ''00''\r\n\t)";


					$fromStatement[] = $lookup.' '.chr($asciiStart);			//customer b, and so on
					$whereStatement[] = "a.".$allColsName[$x]." = ".chr($asciiStart)."."."REFERENCECODE";
					$listingList[$x] = chr($asciiStart)."."."DESCRIPTION1 as \"".str_replace('a.','',$listingList[$x])."\"";

					$asciiStart++;
				}
			}

			//generate select statement
			$selectStmt = "'select \r\n\t".implode(",\r\n\t",$listingList);

			$compQuery .= $selectStmt;
			$compQuery .= $link;
			$compQuery .= "\r\nfrom\r\n\t".$table." a";

			if(count($fromStatement))
			{
				$compQuery .= ",\r\n\t".implode(",\r\n\t",$fromStatement);
				$compQuery .= "\r\nwhere \r\n\t".implode("\r\n\tand ",$whereStatement);
			}
			$compQuery .= " '";

			$compRowNo = 20;
			$compAddRow = 'null';
			$compDelRow = 'null';
			$compAddRowDisabled = 1;
			$compDelRowDisabled = 1;
		}
	}

	if($compType != 'search_constraint')
	{
		/*echo "insert into FLC_PAGE_COMPONENT
						(COMPONENTID,COMPONENTNAME,COMPONENTTITLE,COMPONENTORDER,COMPONENTTYPE,COMPONENTTYPEQUERY,COMPONENTABULARDEFAULTROWNO,
						COMPONENTSTATUS,COMPONENTADDROW,COMPONENTDELETEROW,COMPONENTADDROWDISABLED,COMPONENTDELETEROWDISABLED,
						COMPONENTPREPROCESS,COMPONENTPOSTPROCESS,COMPONENTBINDINGTYPE,COMPONENTBINDINGSOURCE,COMPONENTSEARCH,
						COMPONENTCOLLAPSEDEFAULT,COMPONENTCOLLAPSE,PAGEID)
						values (".$maxID.",'".$name."','".$title."',".$maxOrder.",'".$compType."',".trim($compQuery).",".$compRowNo.",
						1,".$compAddRow.",".$compDelRow.",".$compAddRowDisabled.",".$compDelRowDisabled.",
						'".$compPre."','".$compPost."','".$compBindType."','".$compBindSrc."',1,0,0,".$parent." ";*/
	//insert into flc_page_component
	$myQuery->query("insert into FLC_PAGE_COMPONENT
					(COMPONENTID,COMPONENTNAME,COMPONENTTITLE,COMPONENTORDER,COMPONENTTYPE,COMPONENTTYPEQUERY,COMPONENTABULARDEFAULTROWNO,
					COMPONENTSTATUS,COMPONENTADDROW,COMPONENTDELETEROW,COMPONENTADDROWDISABLED,COMPONENTDELETEROWDISABLED,
					COMPONENTPREPROCESS,COMPONENTPOSTPROCESS,COMPONENTBINDINGTYPE,COMPONENTBINDINGSOURCE,COMPONENTSEARCH,
					COMPONENTCOLLAPSEDEFAULT,COMPONENTCOLLAPSE,PAGEID)
						values (".$maxID.",'".$name."','".$title."',".$maxOrder.",'".$compType."',".trim($compQuery).",".$compRowNo.",
					1,".$compAddRow.",".$compDelRow.",".$compAddRowDisabled.",".$compDelRowDisabled.",
					'".$compPre."','".$compPost."','".$compBindType."','".$compBindSrc."',1,0,0,".$parent.")",'RUN');

	return $maxID;
	}
}

function insertItem($parent,$pageType,$table,$postData,$mySQL,$myQuery,$dataTypeList)
{
	//collect primary keys
	$primaryArr = collectPrimaryKeys($postData);
	$primaryCol = $primaryArr[0];
	$primaryColDataType = $primaryArr[1];
	$primaryColName = $primaryArr[2];
	$primaryColAutoInc = $primaryArr[3];

	//get data type
	$dataType_numeric = $dataTypeList['NUMERIC'];
	$dataType_string = $dataTypeList['STRING'];
	$dataType_datetime = $dataTypeList['DATETIME'];
	$dataType_date = $dataTypeList['DATE'];

	//for all selected columns
	for($x=0; $x < $postData['colCount']; $x++)
	{
		$maxID = $mySQL->maxValue('FLC_PAGE_COMPONENT_ITEMS','ITEMID',1)+1;											//get max itemid
		$maxOrder = $myQuery->query("select MAX(ITEMORDER)+1 as MAXX
										from FLC_PAGE_COMPONENT_ITEMS where COMPONENTID = ".$parent,'SELECT','NAME');		//get max item order

		if($maxOrder[0]['MAXX'] == '')
			$maxOrder = 1;
		else
			$maxOrder = $maxOrder[0]['MAXX'];

		//get selected cols
		if($postData['sel_col_'.$x])
		{
			if($postData['minlen_'.$x] == '')
				$postData['minlen_'.$x] = 'null';

			if($postData['maxlen_'.$x] == '')
				$postData['maxlen_'.$x] = 'null';

			if($postData['length_'.$x] == '')
				$postData['length_'.$x] = 'null';

			if($postData['rows_'.$x] == '')
				$postData['rows_'.$x] = 'null';

			if($postData['attr_required_'.$x] == '')
				$postData['attr_required_'.$x] = 'null';
			else
				$postData['attr_required_'.$x] = '1';

			if($postData['attr_primary_'.$x] == '')
				$postData['attr_primary_'.$x] = 'null';
			else
				$postData['attr_primary_'.$x] = '1';

			if($postData['attr_uppercase_'.$x] == '')
				$postData['attr_uppercase_'.$x] = 'null';
			else
				$postData['attr_uppercase_'.$x] = '1';

			if($postData['attr_readonly_'.$x] == '')
				$postData['attr_readonly_'.$x] = 'null';
			else
				$postData['attr_readonly_'.$x] = '1';

			if($postData['attr_disabled_'.$x] == '')
				$postData['attr_disabled_'.$x] = 'null';
			else
				$postData['attr_disabled_'.$x] = '1';

			//if page for update, set default value
			if($pageType == 'update')
			{
				$postData['default_type_'.$x] = '1';
				if(count($primaryCol))
				{
					$primaryKeyStrArr = array();

					for($f=0; $f < count($primaryCol); $f++)
					{
						if(in_array($primaryColDataType[$f],$dataType_numeric))
							$primaryKeyStrArr[] = $primaryCol[$f]."={GET|".$primaryColName[$f]."}";
						else if(in_array($primaryColDataType[$f],$dataType_string))
							$primaryKeyStrArr[] = $primaryCol[$f]."=''{GET|".$primaryColName[$f]."}''";		//append and prepend quote
						else
							$primaryKeyStrArr[] = $primaryCol[$f]."=''{GET|".$primaryColName[$f]."}''";		//unknown datatype
					}

					$primaryKeyStr = "where ".implode(' and ',$primaryKeyStrArr);
				}

				$postData['default_textarea_'.$x] = "select ".$postData['sel_col_'.$x]." from ".$table.' '.$primaryKeyStr;
			}
			else if($pageType == 'insert')
			{
				if($postData['default_type_'.$x] == 'sql')
					$postData['default_type_'.$x] = '1';
				else
					$postData['default_type_'.$x] = 'null';
			}

			//lookup
			if($_POST['lookup_type_'.$x] == 'sql')
			{
				$lookupType = 'advanced';
				$lookup = $_POST['lookup_textarea_'.$x];
			}
			else if($_POST['lookup_type_'.$x] == 'predefined')
			{
				$lookupType = 'predefined';
				$lookup = "select REFERENCECODE as FLC_ID, DESCRIPTION1 as FLC_NAME from REFGENERAL
							where MASTERCODE = (select REFERENCECODE from REFGENERAL
												where MASTERCODE = ''XXX'' and DESCRIPTION1 = ''".$_POST['lookup_predefined_'.$x]."'')
							and REFERENCESTATUSCODE = ''00'' order by DESCRIPTION1";
			}
			else
			{
				$lookupType = '';
				$lookup = '';
			}

			//if update page, change primary cols to hidden
			if($pageType == 'update' && in_array($postData['colname_'.$x],$primaryCol))
				$postData['type_'.$x] = 'hidden';

			//insert the item
			$itemInsert = "insert into FLC_PAGE_COMPONENT_ITEMS
								(ITEMID,ITEMNAME,ITEMTITLE,ITEMTYPE,ITEMORDER,ITEMMINCHAR,ITEMMAXCHAR,ITEMINPUTLENGTH,ITEMTEXTAREAROWS,ITEMSTATUS,
								ITEMDEFAULTVALUEQUERY,ITEMDEFAULTVALUE,ITEMLOOKUP,ITEMLOOKUPTYPE,ITEMREQUIRED,
								ITEMPRIMARYCOLUMN,ITEMUPPERCASE,ITEMREADONLY,ITEMDISABLED,COMPONENTID)
								values (".$maxID.",'".$postData['name_'.$x]."','".$postData['title_'.$x]."','".$postData['type_'.$x]."',
								".($x+1).",".$postData['minlen_'.$x].",".$postData['maxlen_'.$x].",".$postData['length_'.$x].",".$postData['rows_'.$x].",1,
								".$postData['default_type_'.$x].",'".$postData['default_textarea_'.$x]."','".$lookup."','".$lookupType."',".$postData['attr_required_'.$x].",
								".$postData['attr_primary_'.$x].",".$postData['attr_uppercase_'.$x].",".$postData['attr_readonly_'.$x].",".$postData['attr_disabled_'.$x].",
								".$parent.")";
			$myQuery->query($itemInsert,'RUN');
		}
	}
}

function insertLabel($parent,$postData,$mySQL,$myQuery)
{
	//for all selected columns
	for($x=0; $x < $postData['colCount']; $x++)
	{
		$maxID = $mySQL->maxValue('FLC_PAGE_COMPONENT_ITEMS','ITEMID',1)+1;											//get max itemid

		//get selected cols
		if($postData['attr_listing_'.$x])
		{
			$postData['title_'.$x] = str_replace(' :','',$postData['title_'.$x]);

			//insert the label
			$myQuery->query("insert into FLC_PAGE_COMPONENT_ITEMS (ITEMID,ITEMNAME,ITEMTITLE,ITEMTYPE,ITEMORDER,ITEMSTATUS,COMPONENTID)
								values (".$maxID.",'".$postData['name_'.$x]."','".$postData['title_'.$x]."','label',".($x+1).",1,".$parent.")",'RUN');
		}
	}
}

function insertControl($pageID,$compID,$pageType,$mySQL,$myQuery)
{
	$buttonList = array();
	$redirect = 'index.php?page=page_wrapper&menuID=';

	if($pageType == 'insert')
	{
		$buttonList[] = array('name'=>'simpan_button',	'title'=>'Simpan',	'type'=>'6',		'redirect'=>'');
		$buttonList[] = array('name'=>'batal_button',	'title'=>'Batal',	'type'=>'2',		'redirect'=>'');
	}
	else if($pageType == 'update')
	{
		$buttonList[] = array('name'=>'back_button',	'title'=>'<< Kembali',			'type'=>'6',	'redirect'=>$redirect.$_SESSION['PAGE_WIZARD']['select']);
		$buttonList[] = array('name'=>'batal_button',	'title'=>'Batal',				'type'=>'2',	'redirect'=>'');
		$buttonList[] = array('name'=>'simpan_button',	'title'=>'Simpan Perubahan',	'type'=>'6',	'redirect'=>$redirect.$_SESSION['PAGE_WIZARD']['select']);
	}
	else if($pageType == 'select')
	{
		$buttonList[] = array('name'=>'baru_button',	'title'=>'Data Baru',	'type'=>'6',	'redirect'=>$redirect.$_SESSION['PAGE_WIZARD']['insert']);
	}
	else if($pageType == 'delete')
	{
	}

	for($x=0; $x < count($buttonList); $x++)
	{
		$maxID = $mySQL->maxValue('FLC_PAGE_CONTROL','CONTROLID',1)+1;

		//insert the button
		$myQuery->query("insert into FLC_PAGE_CONTROL (CONTROLID,CONTROLNAME,CONTROLTITLE,CONTROLTYPE,CONTROLORDER,CONTROLREDIRECTURL,CONTROLNOTES,
							CONTROLPOSITION,PAGEID,COMPONENTID)
							values (".$maxID.",'".$buttonList[$x]['name']."','".$buttonList[$x]['title']."',".$buttonList[$x]['type'].",
							".($x+1).",'".$buttonList[$x]['redirect']."','Generated by Page Wizard','right',".$pageID.",".$compID.")",'RUN');
	}
}

//parenttype: menu/page/comp/item/control
function insertBL($table,$pageType,$compType,$postData,$mySQL,$myQuery,$dataTypeList)
{
	//collect primary keys
	$primaryArr = collectPrimaryKeys($postData);
	$primaryCol = $primaryArr[0];
	$primaryColDataType = $primaryArr[1];
	$primaryColName = $primaryArr[2];
	$primaryColAutoInc = $primaryArr[3];

	//get data type
	$dataType_numeric = $dataTypeList['NUMERIC'];
	$dataType_string = $dataTypeList['STRING'];
	$dataType_datetime = $dataTypeList['DATETIME'];
	$dataType_date = $dataTypeList['DATE'];

	//get all columns name and data type
	$allCols = collectAttribute($postData);
	$allColsItemName = $allCols[3];

	$allColsName = $allCols[0];
	$allColsDataType = $allCols[1];

	$maxID = $mySQL->maxValue('FLC_BL','BLID',1)+1;											//get max id for bl
	$title = 'PW_BL_'.strtoupper($pageType).'_'.strtoupper($table);							//bl title
	$name = 'PW_BL_'.strtoupper($pageType).'_'.strtoupper($table);							//bl name
	$description = 'BL for '.strtolower($pageType).' operation on '.strtoupper($table);		//bl description

	if(count($primaryCol))
	{
		$primaryKeyStrArr = array();

		for($f=0; $f < count($primaryCol); $f++)
		{
			if($pageType == 'update')
				$postDataType = 'POST';
			else if($pageType == 'delete')
				$postDataType = 'GET';

			if(in_array($primaryColDataType[$f],$dataType_numeric))
				$primaryKeyStrArr[] = $primaryCol[$f]."={".$postDataType."|".$primaryColName[$f]."}";
			else if(in_array($primaryColDataType[$f],$dataType_string))
				$primaryKeyStrArr[] = $primaryCol[$f]."=''{".$postDataType."|".$primaryColName[$f]."}''";		//append and prepend quote
			else
				$primaryKeyStrArr[] = $primaryCol[$f]."=''{".$postDataType."|".$primaryColName[$f]."}''";		//unknown datatype
		}

		$primaryKeyStr = "where ".implode(' and ',$primaryKeyStrArr);
	}

	//========================================================
	//BL FOR DELETE
	//========================================================
	if($pageType == 'delete')
	{
		//generating BL
		$bl = '/*'."\r\n";
		$bl .= '========================='."\r\n";
		$bl .= 'GENERATED BY PAGE WIZARD'."\r\n";
		$bl .= 'DATE: '.date('Y-m-d H:i:s')."\r\n";
		$bl .= '========================='."\r\n";
		$bl .= '*/'."\r\n";
		$bl .= 'include("db.php"); '."\r\n";
		$bl .= "\r\n";
		$bl .= '$myQuery->query("delete from '.$table.' '."\r\n".str_pad('',17,' ',STR_PAD_LEFT).$primaryKeyStr.'","RUN");'."\r\n\r\n";

		if(strtolower(DBMS_NAME) == 'oracle' || DBMS_NAME == 'sybase_ASE' || DBMS_NAME == 'informix')
		{
			$bl .= 'echo "<script>'."\r\n";
			$bl .= 'window.alert(\'\'Rekod telah berjaya dihapus!\'\');'."\r\n";
			$bl .= 'window.location = \'\'index.php?page=page_wrapper&menuID='.$_SESSION['PAGE_WIZARD']['select'].'\'\';'."\r\n";
			$bl .= '</script>";';
		}
		else if(strtolower(DBMS_NAME) == 'mysql')
		{
			$bl .= 'echo "<script>'."\r\n";
			$bl .= 'window.alert(\\\'Rekod telah berjaya dihapus!\\\');'."\r\n";
			$bl .= 'window.location = \\\'index.php?page=page_wrapper&menuID='.$_SESSION['PAGE_WIZARD']['select'].'\\\';'."\r\n";
			$bl .= '</script>";';
		}
	}//end if bl delete

	//========================================================
	//BL FOR UPDATE
	//========================================================
	else if($pageType == 'update')
	{
		//generate update BL
		if(count($allColsName))
		{
			$allColsStrArr = array();

			for($f=0; $f < count($allColsName); $f++)
			{
				//if primary and auto increment, do not include in insert statement
				$priColKey = array_search($allColsName[$f],$primaryCol);
				if(in_array(strtoupper($allColsName[$f]),$primaryCol) && $primaryColAutoInc[$priColKey] == 'auto_increment') {}
				else
				{
					if(in_array($allColsDataType[$f],$dataType_numeric))
						$allColsStrArr[] = str_pad('',20,' ',STR_PAD_LEFT).str_pad($allColsName[$f],30,' ',STR_PAD_RIGHT)."= {POST|".strtolower($allColsItemName[$f])."}";
					else if(in_array($allColsDataType[$f],$dataType_string))
						$allColsStrArr[] = str_pad('',20,' ',STR_PAD_LEFT).str_pad($allColsName[$f],30,' ',STR_PAD_RIGHT)."= ''{POST|".strtolower($allColsItemName[$f])."}''";		//append and prepend quote
					else if(in_array($allColsDataType[$f],$dataType_date))
					{
						if(strtolower(DBMS_NAME) == 'oracle')
						{
							$dateFormat = str_ireplace('d','dd',str_ireplace('m','mm',str_ireplace('y','yyyy',str_replace('format-','',DEFAULT_DATE_FORMAT))));
							$allColsStrArr[] = str_pad('',20,' ',STR_PAD_LEFT).str_pad($allColsName[$f],30,' ',STR_PAD_RIGHT)."= to_date(''{POST|".strtolower($allColsItemName[$f])."}'',''".$dateFormat."'')";
						}
						else if(strtolower(DBMS_NAME) == 'mysql')
						{
							$allColsStrArr[] = str_pad('',20,' ',STR_PAD_LEFT).str_pad($allColsName[$f],30,' ',STR_PAD_RIGHT)."= str_to_date(''{POST|".strtolower($allColsItemName[$f])."}'',''%".implode('-%',explode('-',str_replace('format-','',DEFAULT_DATE_FORMAT)))."'')";
						}
					}
					else
						$allColsStrArr[] = str_pad('',20,' ',STR_PAD_LEFT).str_pad($allColsName[$f],30,' ',STR_PAD_RIGHT)."= ''{POST|".strtolower($allColsItemName[$f])."}''";		//unknown datatype
				}
			}

			$allColsStr = "set\r\n".implode(",\r\n",$allColsStrArr);
		}

		//generating BL
		$bl = '/*'."\r\n";
		$bl .= '========================='."\r\n";
		$bl .= 'GENERATED BY PAGE WIZARD'."\r\n";
		$bl .= 'DATE: '.date('Y-m-d H:i:s')."\r\n";
		$bl .= '========================='."\r\n";
		$bl .= '*/'."\r\n";
		$bl .= 'include("db.php"); '."\r\n";
		$bl .= "\r\n";
		$bl .= '$myQuery->query("update '.$table.' '.$allColsStr.' '."\r\n".str_pad('',17,' ',STR_PAD_LEFT).$primaryKeyStr.'","RUN");'."\r\n\r\n";


		if(strtolower(DBMS_NAME) == 'oracle' || DBMS_NAME == 'sybase_ASE')
		{
			$bl .= 'echo "<script>'."\r\n";
			$bl .= 'window.alert(\'\'Rekod telah berjaya dikemaskini!\'\');'."\r\n";
			$bl .= 'window.location = \'\'index.php?page=page_wrapper&menuID='.$_SESSION['PAGE_WIZARD']['select'].'\'\';'."\r\n";
			$bl .= '</script>";';
		}
		else if(strtolower(DBMS_NAME) == 'mysql')
		{
			$bl .= 'echo "<script>'."\r\n";
			$bl .= 'window.alert(\\\'Rekod telah berjaya dikemaskini!\\\');'."\r\n";
			$bl .= 'window.location = \\\'index.php?page=page_wrapper&menuID='.$_SESSION['PAGE_WIZARD']['select'].'\\\';'."\r\n";
			$bl .= '</script>";';
		}
	}//end if bl update

	//========================================================
	//BL FOR INSERT
	//========================================================
	else if($pageType == 'insert')
	{
		if($compType == 'form_1_col' || $compType == 'form_2_col')
		{
			$columnForValueArr = array();
			$allColsNameAutoIncRemoved = array();

			//generate insert BL
			if(count($allColsName))
			{
				for($f=0; $f < count($allColsName); $f++)
				{
					//if primary and auto increment, do not include in insert statement
					$priColKey = array_search($allColsName[$f],$primaryCol);
					if(in_array(strtoupper($allColsName[$f]),$primaryCol) && $primaryColAutoInc[$priColKey] == 'auto_increment') {}
					else
					{
						//copy non primary key non auto increment columns
						$allColsNameAutoIncRemoved[] = $allColsName[$f];

						if(in_array($allColsDataType[$f],$dataType_numeric))
							$columnForValueArr[] = "{POST|".strtolower($allColsItemName[$f])."}";
						else if(in_array($allColsDataType[$f],$dataType_string))
							$columnForValueArr[] = "''{POST|".strtolower($allColsItemName[$f])."}''";		//append and prepend quote
						else if(in_array($allColsDataType[$f],$dataType_date))
						{
							if(strtolower(DBMS_NAME) == 'oracle')
							{
								$dateFormat = str_ireplace('d','dd',str_ireplace('m','mm',str_ireplace('y','yyyy',str_replace('format-','',DEFAULT_DATE_FORMAT))));
								$columnForValueArr[] = "to_date(''{POST|".strtolower($allColsItemName[$f])."}'',''".$dateFormat."'')";
							}
							else if(strtolower(DBMS_NAME) == 'mysql')
								$columnForValueArr[] = "str_to_date(''{POST|".strtolower($allColsItemName[$f])."}'',''%".implode('-%',explode('-',str_replace('format-','',DEFAULT_DATE_FORMAT)))."'')";
							else if(strtolower(DBMS_NAME) == 'sybase_ase')
							{
								$date = $mySQL->convertToDate(DEFAULT_DATE_FORMAT);
								//$columnForValueArr[] = "convert(date,''{POST|".strtolower($allColsItemName[$f])."}'',''".$mySQL->convertFromDate()."'')";
								$columnForValueArr[] = $mySQL->convertFromDate("''{POST|".strtolower($allColsItemName[$f])."}''");


								//"convert(date,''{POST|".strtolower($allColsItemName[$f])."}'',''".$mySQL->convertFromDate()."'')";
							}
						}
						else
							$columnForValueArr[] = "''{POST|".strtolower($allColsItemName[$f])."}''";		//unknown datatype
					}
				}
			}

			$allColsName = $allColsNameAutoIncRemoved;

			//generating BL
			$bl = '/*'."\r\n";
			$bl .= '========================='."\r\n";
			$bl .= 'GENERATED BY PAGE WIZARD'."\r\n";
			$bl .= 'DATE: '.date('Y-m-d H:i:s')."\r\n";
			$bl .= '========================='."\r\n";
			$bl .= '*/'."\r\n";
			$bl .= 'include("db.php"); '."\r\n";
			$bl .= "\r\n";
			$bl .= '$myQuery->query("insert into '.$table." \r\n".str_pad('',17,' ',STR_PAD_LEFT).'('."\r\n".str_pad('',17,' ',STR_PAD_LEFT).implode(",\r\n".str_pad('',17,' ',STR_PAD_LEFT),$allColsName)."\r\n".str_pad('',17,' ',STR_PAD_LEFT).') '."\r\n".str_pad('',17,' ',STR_PAD_LEFT).'values'."\r\n".str_pad('',17,' ',STR_PAD_LEFT)."(\r\n".str_pad('',17,' ',STR_PAD_LEFT).implode(",\r\n".str_pad('',17,' ',STR_PAD_LEFT),$columnForValueArr).')","RUN");'."\r\n\r\n";

			if(strtolower(DBMS_NAME) == 'oracle' || DBMS_NAME == 'sybase_ASE')
			{
				$bl .= '//redirect'."\r\n";
				$bl .= 'if($_GET[\'\'prevID\'\'])'."\r\n\t".'$goToMenuID = '.$_SESSION['PAGE_WIZARD']['select'].';'."\r\n".' else'."\r\n\t".'$goToMenuID = '.$_SESSION['PAGE_WIZARD']['insert'].';'."\r\n\r\n";
				$bl .= 'echo "<script>'."\r\n";
				$bl .= 'window.alert(\'\'Rekod telah berjaya disimpan!\'\');'."\r\n";
				$bl .= 'window.location = \'\'index.php?page=page_wrapper&menuID=".$goToMenuID."\'\';'."\r\n";
				$bl .= '</script>";';
			}
			else if(strtolower(DBMS_NAME) == 'mysql')
			{
				$bl .= '//redirect'."\r\n";
				$bl .= 'if($_GET[\\\'prevID\\\'])'."\r\n\t".'$goToMenuID = '.$_SESSION['PAGE_WIZARD']['select'].';'."\r\n".' else'."\r\n\t".'$goToMenuID = '.$_SESSION['PAGE_WIZARD']['insert'].';'."\r\n\r\n";
				$bl .= 'echo "<script>'."\r\n";
				$bl .= 'window.alert(\\\'Rekod telah berjaya disimpan!\\\');'."\r\n";
				$bl .= 'window.location = \\\'index.php?page=page_wrapper&menuID=".$goToMenuID."\\\';'."\r\n";
				$bl .= '</script>";';
			}
		}//end if form1col / form2col TODOOOOOOOOOOOOO
		else if($compType == 'tabular')
		{
			$columnForValueArr = array();

			//generate insert BL
			if(count($allColsName))
			{
				for($f=0; $f < count($allColsName); $f++)
				{
					if(in_array($allColsDataType[$f],$dataType_numeric))
						$columnForValueArr[] = '$'.strtolower($postData['name_'.$f]);
					else if(in_array($allColsDataType[$f],$dataType_string))
						$columnForValueArr[] = "'$".strtolower($postData['name_'.$f])."'";		//append and prepend quote
					else
						$columnForValueArr[] = "'$".strtolower($postData['name_'.$f])."'";		//unknown datatyp

					//$columnForSQLArr[] = $allColsName[$f];
					/*
					if(in_array($allColsDataType[$f],$dataType_numeric))
						$columnForValueArr[] = "{POST|".strtolower($postData['name_'.$f])."}";
					else if(in_array($allColsDataType[$f],$dataType_string))
						$columnForValueArr[] = "''{POST|".strtolower($postData['name_'.$f])."}''";		//append and prepend quote
					else
						$columnForValueArr[] = "''{POST|".strtolower($postData['name_'.$f])."}''";		//unknown datatyp
					*/
				}
			}

			$bl = '
/*
=========================
GENERATED BY PAGE WIZARD
DATE: '.date('Y-m-d H:i:s').'
=========================
*/
include("db.php");
//ni untuk tabularrr

print_r($_POST);

$myQuery->query("insert into '.$table." \r\n".str_pad('',17,' ',STR_PAD_LEFT).'('."\r\n".str_pad('',17,' ',STR_PAD_LEFT).implode(",\r\n".str_pad('',17,' ',STR_PAD_LEFT),$allColsName)."\r\n".str_pad('',17,' ',STR_PAD_LEFT).') '."\r\n".str_pad('',17,' ',STR_PAD_LEFT).'values'."\r\n".str_pad('',17,' ',STR_PAD_LEFT)."(\r\n".str_pad('',17,' ',STR_PAD_LEFT).implode(",\r\n".str_pad('',17,' ',STR_PAD_LEFT),$columnForValueArr).')","RUN");

echo "<script>
window.alert(\\\'Rekod telah berjaya disimpan!\\\');
window.location = \\\'index.php?page=page_wrapper&menuID='.$_SESSION['PAGE_WIZARD']['insert'].'\\\';
</script>";';
		}
	}//end if insert

	//delete bl with the same name first
	$myQuery->query("delete from FLC_BL where BLNAME = '".$name."'",'RUN');

	//insert the bl
	$myQuery->query("insert into FLC_BL (BLID,BLTYPE,BLNAME,BLTITLE,BLDESCRIPTION,BLDETAIL,BLSTATUS,CREATEBY,CREATEDATE)
					values (".$maxID.",'PHP','".$name."','".$title."','".$description."','".$bl."','00',".$_SESSION['userID'].",".$mySQL->currentDate().")",'RUN');
	return $maxID;
}

//ok
function insertTrigger($pageID,$pageType,$bl,$mySQL,$myQuery)
{
	$maxID = $mySQL->maxValue('FLC_TRIGGER','TRIGGER_ID',1)+1;

	//get blname
	$blName = $myQuery->query("select BLNAME from FLC_BL where BLID = ".$bl,'SELECT','NAME');
	$bl = $blName[0]['BLNAME'];

	//if insert / update, get all buttons with simpan_button name
	if($pageType == 'insert' || $pageType == 'update')
	{
		$getButtons = $myQuery->query("select CONTROLID from FLC_PAGE_CONTROL
										where CONTROLNAME = 'simpan_button'
										and PAGEID = ".$pageID,'SELECT','NAME');
		$parent = $getButtons[0]['CONTROLID'];
		$parentType = 'control';
		$process = 'postprocess';
	}
	else if($pageType == 'delete')
	{
		$parent = $pageID;
		$parentType = 'page';
		$process = 'preprocess';
	}

	$myQuery->query("insert into FLC_TRIGGER
					(TRIGGER_ID,TRIGGER_TYPE,TRIGGER_EVENT,TRIGGER_BL,TRIGGER_ITEM_TYPE,TRIGGER_ITEM_ID,TRIGGER_ORDER,TRIGGER_STATUS)
					values
					(".$maxID.",'PHP','".$process."','".$bl."','".$parentType."',".$parent.",1,1)",'RUN');
}

if($_POST['saveSelection'])
{
	//create menu and page first
	for($x=0; $x < count($_POST['crud_option']); $x++)
	{
		//create menu and page
		$menuID = insertMenu('',$_POST['crud_option'][$x],$tableName,$mySQL,$myQuery);
		$menuIDArr[] = $menuID;
		$menuIDArrCnt = count($menuIDArr);

		$pageIDArr[] = insertPage($menuID,$_POST['crud_option'][$x],$tableName,$mySQL,$myQuery);
		$crud[] = $_POST['crud_option'][$x];

		//store menuid in session
		$_SESSION['PAGE_WIZARD'][$_POST['crud_option'][$x]] = $menuID;
	}
	for($x=0; $x < $menuIDArrCnt; $x++)
	{
		$menuID = $menuIDArr[$x];
		$pageID = $pageIDArr[$x];
		$_POST['crud_option'][$x] = $crud[$x];

		if(in_array($crud[$x],array('insert','update')))
		{
			$compID = insertComponent($pageID,$crud[$x],$_POST['comp_type'],$tableName,$mySQL,$myQuery);
			insertItem($compID,$crud[$x],$tableName,$_POST,$mySQL,$myQuery,$dataTypeList);
			insertControl($pageID,$compID,$crud[$x],$mySQL,$myQuery);
		}
		else if($crud[$x] == 'select')
		{
			$compID = insertComponent($pageID,$crud[$x],$_POST['comp_type'],$tableName,$mySQL,$myQuery);
			insertComponent($pageID,$crud[$x],'search_constraint',$tableName,$mySQL,$myQuery);				//add search constraint
			//insertComponent($pageID,$crud[$x],'search_constraint_extended_items',$tableName,$mySQL,$myQuery);				//add search constraint
			insertLabel($compID,$_POST,$mySQL,$myQuery);																	//for the report listing
			insertControl($pageID,$compID,$crud[$x],$mySQL,$myQuery);
		}

		if($crud[$x] != 'select')
			$blID = insertBL($tableName,$crud[$x],$_POST['comp_type'],$_POST,$mySQL,$myQuery,$dataTypeList);

		if($crud[$x] == 'insert' || $crud[$x] == 'update' || $crud[$x] == 'delete')
			insertTrigger($pageID,$crud[$x],$blID,$mySQL,$myQuery);
	}

	//echo "<script>window.location = 'index.php?page=page_wizard&menuID=".$_GET['menuID']."&menuForceRefresh=1&lang=1';
	//		window.alert('Page successfully generated!');</script>";
}
?>
<script>
//http://stackoverflow.com/questions/1026069/capitalize-the-first-letter-of-string-in-javascript
function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}
//http://phpjs.org/functions/similar_text/
function similar_text(first, second, percent)
{
  // http://kevin.vanzonneveld.net
  // +   original by: Rafał Kukawski (http://blog.kukawski.pl)
  // +   bugfixed by: Chris McMacken
  // +   added percent parameter by: Markus Padourek (taken from http://www.kevinhq.com/2012/06/php-similartext-function-in-javascript_16.html)
  // *     example 1: similar_text('Hello World!', 'Hello phpjs!');
  // *     returns 1: 7
  // *     example 2: similar_text('Hello World!', null);
  // *     returns 2: 0
  // *     example 3: similar_text('Hello World!', null, 1);
  // *     returns 3: 58.33
  if (first === null || second === null || typeof first === 'undefined' || typeof second === 'undefined') {
    return 0;
  }

  first += '';
  second += '';

  var pos1 = 0,
    pos2 = 0,
    max = 0,
    firstLength = first.length,
    secondLength = second.length,
    p, q, l, sum;

  max = 0;

  for (p = 0; p < firstLength; p++) {
    for (q = 0; q < secondLength; q++) {
      for (l = 0;
      (p + l < firstLength) && (q + l < secondLength) && (first.charAt(p + l) === second.charAt(q + l)); l++);
      if (l > max) {
        max = l;
        pos1 = p;
        pos2 = q;
      }
    }
  }

  sum = max;

  if (sum) {
    if (pos1 && pos2) {
      sum += this.similar_text(first.substr(0, pos2), second.substr(0, pos2));
    }

    if ((pos1 + max < firstLength) && (pos2 + max < secondLength)) {
      sum += this.similar_text(first.substr(pos1 + max, firstLength - pos1 - max), second.substr(pos2 + max, secondLength - pos2 - max));
    }
  }

  if (!percent) {
    return sum;
  } else {
    return (sum * 200) / (firstLength + secondLength);
  }
}
//http://stackoverflow.com/questions/4878756/javascript-how-to-capitalize-first-letter-of-each-word-like-a-2-word-city
function capitaliseWords(str)
{
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}
function showTableList(elem)
{
	elemVal = elem.value;

	if(elemVal == 'single_batch' || elemVal == 'master_detail')
		jQuery('#table_name').attr('size','10').attr('multiple','multiple');
	else if(elemVal == 'single_table')
		jQuery('#table_name').removeAttr('size').removeAttr('multiple');
}
function checkAll(elem)
{
	var allCbox = jQuery('[name^="sel_col_"]');

	for(var a=0; a < allCbox.length; a++)
	{
		if(jQuery(elem).attr('checked'))
			jQuery(allCbox[a]).attr('checked','checked');
		else
			jQuery(allCbox[a]).attr('checked',false);
	}
}
function autoSuggest(type)
{
	//if datasize > 260, change to textarea
	var typeToTextAreaSize = 260;

	if(type == 'type')
	{
		var elem 		= jQuery('[name^="coldatatype_"]');
		var elem2 		= jQuery('[name^="coldatasize_"]');
		var elem3 		= jQuery('[name^="type_"]');
		var pk 			= jQuery('[name^="colprimary_"]');
		var pk_auto 	= jQuery('[name^="colautoinc_"]');
		var col 		= jQuery('[name^="colname_"]');
		var def_pred 	= jQuery('[name^="lookup_predefined_"]');
		var def_type 	= jQuery('[name^="lookup_type_"]');

		for(var a=0; a < elem.length; a++)
		{
			var dataType = jQuery(elem[a]).val().toUpperCase();
			var dataSize = parseInt(jQuery(elem2[a]).val(),10);
			var alreadySet = false;

			//kalau datatype < 5 chars, selalunye as reference code
			if(parseInt(jQuery(elem2[a]).val(),10) <= 30)
			{
				var similarList = new Array();
				var similarHighest = 0;
				var similarHighestIndex = 0;

				//check for reference code
				for(var b=0; b < jQuery(def_pred).length; b++)
				{
					//value of predefined items
					var theInnerHTML = jQuery(def_pred[a][b]).html();

					if(similar_text(theInnerHTML,jQuery(col[a]).val(),1) > similarHighest)
					{
						similarHighest = similar_text(theInnerHTML,jQuery(col[a]).val(),1);
						similarHighestIndex = b;
					}
				}

				//if similar to column name (more than 70% similarity)
				if(similarHighest > 70)
				{
					jQuery(def_type[a]).val('predefined').change();
					jQuery(def_pred[a]).find('option:eq('+similarHighestIndex+')').attr('selected', 'selected');
					jQuery('[name="type_'+a+'"]').val('dropdown');
					alreadySet = true;
				}
			}

			if(alreadySet === false)
			{
				if(dataType == 'DATE' || dataType == 'DATETIME' || dataType == 'TIMESTAMP' || dataType == 'DATEN' || dataType == 'DATETIMN')
				{
					jQuery('[name="type_'+a+'"]').val('date');
					jQuery('[name="length_'+a+'"]').val('11');
					jQuery('[name="minlen_'+a+'"]').val('10');
					jQuery('[name="maxlen_'+a+'"]').val('10');
				}
				else if(dataType == 'VARCHAR' || dataType == 'VARCHAR2' || dataType == 'TEXT' || dataType == 'CLOB' || dataType == 'CHAR')
				{
					jQuery('[name="maxlen_'+a+'"]').val(dataSize);

					//if tabular
					if(jQuery('#comp_type').val() == 'tabular')
					{
						if(dataSize > typeToTextAreaSize)
						{
							jQuery('[name="type_'+a+'"]').val('textarea');
							jQuery('[name="length_'+a+'"]').val('40');

							var rows = Math.ceil(dataSize / (typeToTextAreaSize/2))+1;

							if(rows > 3)
								rows = 3;

							jQuery('[name="rows_'+a+'"]').val(rows);

							jQuery('[name="maxlen_'+a+'"]').val(dataSize);
						}
						else
						{
							jQuery('[name="length_'+a+'"]').val(dataSize+1);
							jQuery('[name="type_'+a+'"]').val('text');
						}
					}
					else
					{
						//if keyword matches
						if(jQuery('[name="colname_'+a+'"]').val().toUpperCase().indexOf('GAMBAR') 	!= -1 ||
						   jQuery('[name="colname_'+a+'"]').val().toUpperCase().indexOf('MUATNAIK') 	!= -1 ||
						   jQuery('[name="colname_'+a+'"]').val().toUpperCase().indexOf('UPLOAD')   	!= -1)
						{
							jQuery('[name="type_'+a+'"]').val('file');
							jQuery('[name="length_'+a+'"]').val('50');
						}
						else
						{
							if(dataSize > typeToTextAreaSize)
							{
								jQuery('[name="type_'+a+'"]').val('textarea');
								jQuery('[name="length_'+a+'"]').val('80');

								var rows = Math.ceil(dataSize / typeToTextAreaSize)+1;

								if(rows > 3)
									rows = 3;

								jQuery('[name="rows_'+a+'"]').val(rows);

								jQuery('[name="maxlen_'+a+'"]').val(dataSize);
							}
							else
							{
								jQuery('[name="length_'+a+'"]').val(dataSize+1);
								jQuery('[name="type_'+a+'"]').val('text');
							}
						}
					}
				}
				else if(dataType == 'INT' || dataType == 'INTEGER'  || dataType == 'NUMBER' || dataType == 'NUMERIC' || dataType == 'DECIMAL' || dataType == 'INTN' || dataType == 'MONEYN' || dataType == 'SMALLINT' || dataType == 'TINYINT' || dataType == 'MONEY')
				{
					if(dataType == 'INT' || dataType == 'INTEGER' || dataType == 'NUMBER')
					{
						if(jQuery('[name="colprimary_'+a+'"]').val() == 'PRI')
						{
							//if auto increment PK
							if(jQuery('[name="colprimary_'+a+'"]').val().toLowerCase() == 'auto_increment')
								jQuery('[name="type_'+a+'"]').val('hidden');
							else
							{
								jQuery('[name="length_'+a+'"]').val('10');
								jQuery('[name="type_'+a+'"]').val('text');
							}

							jQuery('[name="attr_primary_'+a+'"]').attr('checked','checked');
						}
						else
						{
							jQuery('[name="length_'+a+'"]').val('10');
							jQuery('[name="type_'+a+'"]').val('text');
						}
					}
					else
					{
						jQuery('[name="length_'+a+'"]').val('10');
						jQuery('[name="type_'+a+'"]').val('text');
					}
				}
			}

			itemTypeChange(elem3[a]);
		}
	}
	else if(type == 'title')
	{
		var elem = jQuery('[name^="title_"]');
		var col = jQuery('[name^="colname_"]');

		for(var a=0; a < elem.length; a++)
		{
			var theVal = col[a].value.replace(/_/g," ").replace(/-/g," ");

			if(jQuery('#comp_type').val() == 'form_1_col' || jQuery('#comp_type').val() == 'form_2_col')
				theVal = capitaliseWords(theVal)+' :';
			else
				theVal = capitaliseWords(theVal);

			jQuery(elem[a]).val(theVal);
		}
	}
	else if(type == 'name')
	{
		var elem = jQuery('[name^="name_"]');
		var col = jQuery('[name^="colname_"]');

		for(var a=0; a < elem.length; a++)
		{
			var theVal = col[a].value.replace(/_/g,"").replace(/-/g,"");
			theVal = theVal.toLowerCase();

			jQuery(elem[a]).val(theVal);
		}
	}
}
function expandAddAttributes(elem)
{
	var actualHeight = jQuery(elem).children().eq(0).prop('scrollHeight');
	jQuery(elem).css('height',actualHeight+'px');
	jQuery(elem).children().eq(0).css('height',actualHeight+'px');
}
function selectCheckboxOnClick(elem)
{
	if(jQuery(elem).prev().attr('checked') == 'checked')
		jQuery(elem).prev().attr('checked',false);
	else
		jQuery(elem).prev().attr('checked','checked');
}
function lookupTypeSelector(elem)
{
	if(jQuery(elem).val() == '')
	{
		jQuery(elem).next().hide();
		jQuery(elem).nextAll().eq(1).hide();
	}
	else if(jQuery(elem).val() == 'sql')
	{
		jQuery(elem).next().hide();
		jQuery(elem).nextAll().eq(1).show().css('display','block');
	}
	else if(jQuery(elem).val() == 'predefined')
	{
		jQuery(elem).next().show().css('display','block');
		jQuery(elem).nextAll().eq(1).hide();
	}
}
function itemTypeChange(elem)
{
	var elemVal = elem.value;
	var index = jQuery(elem).attr('name').split('_')[1];

	if(elemVal == 'file')
	{
		jQuery('[name^="typeUploadSelect_'+index+'"]').show();
	}
	else
	{
		jQuery('[name^="typeUploadSelect_'+index+'"]').hide();
	}

	//reset dulu
	jQuery('[name="length_'+index+'"]').removeAttr('readonly').css('background-color','');
	jQuery('[name="rows_'+index+'"]').removeAttr('readonly').css('background-color','');
	jQuery('[name="minlen_'+index+'"]').removeAttr('readonly').css('background-color','');
	jQuery('[name="maxlen_'+index+'"]').removeAttr('readonly').css('background-color','');

	//no rows
	if(elemVal == 'text' || elemVal == 'password' || elemVal == 'password_md5' || elemVal == 'date' || elemVal == 'file')
	{
		jQuery('[name="rows_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4').val('');
	}
	else if(elemVal == 'textarea')
	{
	}
	else if(elemVal == 'lov' || elemVal == 'radio' || elemVal == 'dropdown' || elemVal == 'checkbox' || elemVal == 'hidden')
	{
		jQuery('[name="length_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4').val('');
		jQuery('[name="rows_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4').val('');
		jQuery('[name="minlen_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4').val('');
		jQuery('[name="maxlen_'+index+'"]').attr('readonl20y','readonly').css('background-color','#EBEBE4').val('');
	}
	else if(elemVal == 'listbox')
	{
		jQuery('[name="length_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4');
		jQuery('[name="minlen_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4');
		jQuery('[name="maxlen_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4');
	}
	else if(elemVal == 'label' || elemVal == 'label_with_hidden')
	{
		jQuery('[name="minlen_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4');
		jQuery('[name="maxlen_'+index+'"]').attr('readonly','readonly').css('background-color','#EBEBE4');
	}
}
function resetForm()
{
	jQuery('[name^="sel_col_"]').attr('checked',false);
	jQuery('[name^="length_"]').removeAttr('readonly').css('background-color','');
	jQuery('[name^="rows_"]').removeAttr('readonly').css('background-color','');
	jQuery('[name^="minlen_"]').removeAttr('readonly').css('background-color','');
	jQuery('[name^="maxlen_"]').removeAttr('readonly').css('background-color','');
	jQuery('[name^="default_type_"]').val('text').change();
	jQuery('[name^="lookup_type_"]').val('text').change();
}
function addAttributesSelector(elem)
{
	if(elem.checked)
		jQuery('[name^="attr_'+jQuery(elem).val()+'_"]').click();
	else
		jQuery('[name^="attr_'+jQuery(elem).val()+'_"]').attr('checked',false);
}
</script>
<div id="breadcrumbs">System Administrator / Configuration / Page Wizard</div>
<h1>Page Wizard </h1>
<?php
if($insertCatRs)
	showNotificationInfo('New page has been added');
else if($duplicatePageRs)
	showNotificationInfo('Page has been duplicated');
?>
<form method="post" name="form1">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="2">Table / View Selection</th>
		</tr>
		<tr>
			<td class="inputLabel">Wizard Type :</td>
			<td>
				<select name="wizard_type" id="wizard_type" class="inputList" onchange="showTableList(this)">
					<option value="single_table" <?php if($_POST['wizard_type'] == 'single_table') echo 'selected'; ?>>Single table page</option>
					<!--<option value="single_batch">Single table page - BATCH</option>-->
					<!--<option value="master_detail" <?php if($_POST['wizard_type'] == 'master_detail') echo 'selected'; ?>>Master-detail table page</option>-->
				</select>
				<br />
				<select id="table_name" name="table_name"  class="inputList" style="margin-top:5px;">
					<option value="">-- Please select table(s) --</option>
					<?php for($x=0; $x < count($getTableRs); $x++) {?>
					<option value="<?php echo $getTableRs[$x]['TABLE_NAME']?>" <?php if($getTableRs[$x]['TABLE_NAME'] == $_POST['table_name']) echo 'selected'; ?>><?php echo $getTableRs[$x]['TABLE_NAME']?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="inputLabel">Component Type :</td>
			<td class="">
				<select id="comp_type" name="comp_type"  class="inputList">
					<option value="form_1_col" <?php if($_POST['comp_type'] == 'form_1_col') echo 'selected'; ?>>Form 1 Column</option>
					<option value="form_2_col" <?php if($_POST['comp_type'] == 'form_2_col') echo 'selected'; ?>>Form 2 Column</option>
					<!--<option value="tabular" <?php if($_POST['comp_type'] == 'tabular') echo 'selected'; ?>>Tabular</option>-->
				</select>
			</td>
		</tr>
		<tr>
			<td class="inputLabel">Create Scaffold (CRUD) : </td>
			<td>
				<label><input type="checkbox" name="crud_option[]" value="insert" checked />INSERT</label>
				<label><input type="checkbox" name="crud_option[]" value="update" checked />UPDATE</label>
				<label><input type="checkbox" name="crud_option[]" value="delete" checked />DELETE</label>
				<label><input type="checkbox" name="crud_option[]" value="select" checked />SELECT</label>
			</td>
		</tr>
		<tr>
			<td class="inputLabel">Additional Attributes : </td>
			<td>
				<label><input type="checkbox" name="constraint_option[]" value="listing"  onchange="addAttributesSelector(this)" />Show all columns in listing</label><br />
				<label><input type="checkbox" name="constraint_option[]" value="search"  onchange="addAttributesSelector(this)" />Make all columns searchable</label><br />
				<label><input type="checkbox" name="constraint_option[]" value="required"  onchange="addAttributesSelector(this)" />Make all columns compulsory (required)</label>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="contentButtonFooter">
				<input name="next_step"  id="next_step" type="submit" class="inputButton" value="Get Columns >>" onclick="if(jQuery('#table_name').val() == '') { window.alert('Please choose table!'); return false;}  else return true;" />
			</td>
		</tr>
	</table>
	<?php if($_POST['next_step']) { ?>
    <br />
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="11">Columns for <?php echo $tableName; ?>
				<input type="hidden" name="colCount" value="<?php echo count($columnList); ?>" />
			</th>
		</tr>
		<?php if($columnList){ ?>
		<tr>
			<th class="listingHead" rowspan="2" width="10">#</th>
			<th class="listingHead" rowspan="2" width="4"><input type="checkbox" onchange="checkAll(this)" /></th>
			<th class="listingHead" colspan="2">Column Info</th>
			<th class="listingHead" colspan="6">Corrad Attributes</th>
			<th class="listingHead" rowspan="2">Additional Attibutes</th>
		</tr>
		<tr>
			<th class="listingHead">Order</th>
			<th class="listingHead">Name<br />Type &amp; Length</th>
			<th class="listingHead">Type <a href="javascript:void(0)" onclick="autoSuggest('type')" style="font-size:10px;">Auto suggest</a></th>
			<th class="listingHead">Title <a href="javascript:void(0)" onclick="autoSuggest('title')" style="font-size:10px;">Auto suggest</a><br />
				Name <a href="javascript:void(0)" onclick="autoSuggest('name')" style="font-size:10px;">Auto suggest</a>
			</th>
			<th class="listingHead">Default Value</th>
			<th class="listingHead">Lookup</th>
			<th class="listingHead">Length / <br />Rows</th>
			<th class="listingHead">Min / Max<br />Length</th>
		</tr>
		<?php for($x=0; $x< count($columnList); $x++)
		{
			$tableNameSplit = explode('.',$_POST['table_name']);

			//get column data type
			if(DBMS_NAME == 'mysql' || DBMS_NAME == 'informix')
				$dataType = $mySQL->columnDatatype('','',$tableName,$columnList[$x]['COLUMN_NAME'],array('size','primary','extra'));

			else if(DBMS_NAME == 'oracle')
				$dataType = $mySQL->columnDatatype('',$tableNameSplit[0],$tableName,$columnList[$x]['COLUMN_NAME'],array('size','primary','extra'));

			else if(DBMS_NAME == 'sybase_ASE')
				$dataType = $mySQL->columnDatatype('',DB_USERNAME,$tableName,$columnList[$x]['COLUMN_NAME'],array('size','primary','extra'));

			if(DBMS_NAME == 'sybase_ASE')
			{
				if($dataType[0]['DATA_TYPE'] == 'TEXT')
					$dataType[0]['SIZE'] = '4000';

				if($dataType[0]['DATA_TYPE'] == 'DATE')
					$dataType[0]['SIZE'] = '10';
			}
			else if(DBMS_NAME == 'informix')
			{
				if($dataType[0]['DATA_TYPE'] == '0' || $dataType[0]['DATA_TYPE'] == '256')
				$dataType[0]['DATA_TYPE'] = 'CHAR';

				if($dataType[0]['DATA_TYPE'] == '2' || $dataType[0]['DATA_TYPE'] == '258')
					$dataType[0]['DATA_TYPE'] = 'INTEGER';

				if($dataType[0]['DATA_TYPE'] == '3')
					$dataType[0]['DATA_TYPE'] = 'FLOAT';

				if($dataType[0]['DATA_TYPE'] == '5')
					$dataType[0]['DATA_TYPE'] = 'DECIMAL';

				if($dataType[0]['DATA_TYPE'] == '6')
					$dataType[0]['DATA_TYPE'] = 'SERIAL 1';

				if($dataType[0]['DATA_TYPE'] == '7')
					$dataType[0]['DATA_TYPE'] = 'DATE';

				if($dataType[0]['DATA_TYPE'] == '10')
					$dataType[0]['DATA_TYPE'] = 'DATETIME';

				if($dataType[0]['DATA_TYPE'] == '11')
					$dataType[0]['DATA_TYPE'] = 'BYTE';

				if($dataType[0]['DATA_TYPE'] == '12')
					$dataType[0]['DATA_TYPE'] = 'TEXT';

				if($dataType[0]['DATA_TYPE'] == '13' || $dataType[0]['DATA_TYPE'] == '269')
					$dataType[0]['DATA_TYPE'] = 'VARCHAR';

				if($dataType[0]['DATA_TYPE'] == '41')
					$dataType[0]['DATA_TYPE'] = 'BLOB, BOOLEAN, CLOB';

				if($dataType[0]['DATA_TYPE'] == '45')
					$dataType[0]['DATA_TYPE'] = 'BOOLEAN';

				if($dataType[0]['DATA_TYPE'] == '52')
					$dataType[0]['DATA_TYPE'] = 'BIGINT';
			}

			//sybase
			if($dataType[0]['SIZE'] != '')
				$dataTypeStr = $dataType[0]['DATA_TYPE'].'('.$dataType[0]['SIZE'].')';
			else
				$dataTypeStr = $dataType[0]['DATA_TYPE'];

			if(DBMS_NAME == 'informix')
			{	
				if($dataType[0]['PRI_KEY'] == 'P')
					$dataTypeStr .= '<br>PRIMARY KEY';
			}
			else
			{
			if($dataType[0]['PRI_KEY'] == 'PRI')
				$dataTypeStr .= '<br>PRIMARY KEY';
			}
			if($dataType[0]['AUTO_INC'] == 'auto_increment')
				$dataTypeStr .= '<br>AUTO INCREMENT';
		?>
		<tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?><?php if($reference_2Rs[$x]['ITEMPRIMARYCOLUMN'] == '1') { ?>style="font-weight:bold;"<?php } ?> onmouseout="this.style.background = '#ffffff'"<?php } ?> >
			<td class="listingContent" valign="top" style="padding-top:6px;"><?php echo ($x+1).".";?></td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<input type="hidden" name="colname_<?php echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" />
				<input type="checkbox" name="sel_col_<?php echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" style="margin:0px; padding-left:2px;" />
			</td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<input name="colorder_<?php echo $x; ?>" type="text" class="inputInput" maxlength="2" value="<?php echo $x+1;?>" style="width:15px;" />
			</td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<input type="hidden" name="coldatatype_<?php echo $x; ?>" value="<?php echo $dataType[0]['DATA_TYPE']?>" />
				<input type="hidden" name="coldatasize_<?php echo $x; ?>" value="<?php echo $dataType[0]['SIZE']?>" />
				<input type="hidden" name="colprimary_<?php echo $x; ?>" value="<?php echo $dataType[0]['PRI_KEY']?>" />
				<input type="hidden" name="colautoinc_<?php echo $x; ?>" value="<?php echo $dataType[0]['AUTO_INC']?>" />
				<?php
					echo substr($columnList[$x]['COLUMN_NAME'],0,15);
					if(strlen($columnList[$x]['COLUMN_NAME'])> 15)
						echo '<a href="javascript:void(0)" style="text-decoration:none; border-bottom: 1px blue dotted;" alt="'.$columnList[$x]['COLUMN_NAME'].'" title="'.$columnList[$x]['COLUMN_NAME'].'">&nbsp;..</a>';
				?>
				<br><span style="font-size:10px;color:#797979"><?php echo $dataTypeStr; ?></span>
			</td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<select class="inputList" style="width:130px;font-size:11px;" name="type_<?php echo $x; ?>" onchange="itemTypeChange(this)">
					<option>&nbsp;</option>
					<?php for($y=0; $y < count($itemTypeRs); $y++){ ?>
					<option value="<?php echo $itemTypeRs[$y]["REFERENCECODE"]; ?>"><?php echo $itemTypeRs[$y]["DESCRIPTION2"]?></option>
					<?php }	?>
				</select>
				<select class="inputList" style="width:130px;font-size:11px;margin-top:1px;display:none;" name="typeUploadSelect_<?php echo $x; ?>">
					<option value="image">Image</option>
					<option value="image">Others</option>
				</select>
			</td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<input name="title_<?php echo $x; ?>" type="text" class="inputInput" style="width:120px;" /><br />
				<input name="name_<?php echo $x; ?>" type="text" class="inputInput" style="width:120px;" />
			</td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<select name="default_type_<?php echo $x; ?>" class="inputList" style="font-size:11px;width:130px;" >
					<option value="text">Text</option>
					<option value="sql">SQL</option>
				</select>
				<textarea name="default_textarea_<?php echo $x; ?>" rows="1" class="inputInput" style="display:block; margin-left:0px; width:118px;" onclick="jQuery(this).attr('rows','10');" onblur="jQuery(this).attr('rows','1');"></textarea>
			</td>
			<td class="listingContent" valign="top" style="padding-top:6px;">
				<select name="lookup_type_<?php echo $x; ?>" class="inputList" style="font-size:11px;width:130px;" onchange="lookupTypeSelector(this)">
					<option value="">None</option>
					<option value="sql">SQL</option>
					<option value="predefined">Predefined</option>
				</select>
				<select name="lookup_predefined_<?php echo $x; ?>" class="inputList" style="display:none;font-size:11px;width:130px;margin-top:2px;">
					<?php for($a=0; $a < count($predefinedRefs); $a++) { ?>
					<option value="<?php echo $predefinedRefs[$a]['DESCRIPTION1']?>"><?php echo $predefinedRefs[$a]['DESCRIPTION1']?></option>
					<?php } ?>
				</select>
				<textarea name="lookup_textarea_<?php echo $x; ?>" rows="1" class="inputInput" style="display:none; margin-left:0px; width:118px;" onclick="jQuery(this).attr('rows','10');" onblur="jQuery(this).attr('rows','1');"></textarea>
			</td>
			<td class="listingContent"  valign="top" style="padding-top:6px;text-align:center">
				<input name="length_<?php echo $x; ?>" type="text" class="inputInput" maxlength="3" style="width:25px;" /><br />
				<input name="rows_<?php echo $x; ?>" type="text" class="inputInput" maxlength="3" style="width:25px;" />
			</td>
			<td class="listingContent"  valign="top" style="padding-top:6px;text-align:center">
				<input name="minlen_<?php echo $x; ?>" type="text" class="inputInput" maxlength="3" style="width:25px;" /><br />
				<input name="maxlen_<?php echo $x; ?>" type="text" class="inputInput" maxlength="3" style="width:25px;" />
			</td>
			<td class="listingContentRight"  valign="top" style="padding-top:6px;padding-left:1px;padding-right:1px;" onclick="expandAddAttributes(this)">
				<div style="height:58px;overflow:auto;margin-right:0px;">
					<label><input type="checkbox" name="attr_search_<?php 		echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" /><a href="javascript:void(0)" onclick="selectCheckboxOnClick(this)" style="text-decoration:none; border-bottom: 1px blue dotted;" title="If selected, this column will be added to the search constraint">Searchable</a></label><br />
					<label><input type="checkbox" name="attr_listing_<?php 		echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" /><a href="javascript:void(0)" onclick="selectCheckboxOnClick(this)" style="text-decoration:none; border-bottom: 1px blue dotted;" title="If selected, this column will be added to the tabular search result">Listable</a></label><br />
					<label><input type="checkbox" name="attr_primary_<?php 		echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" /><a href="javascript:void(0)" onclick="selectCheckboxOnClick(this)" style="text-decoration:none; border-bottom: 1px blue dotted;" title="If selected, this column will be treated as primary key. This is important in UPDATE and DELETE interface.">Primary Key</a></label><br />
					<label><input type="checkbox" name="attr_uppercase_<?php 	echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" />Uppercase</label><br />
					<label><input type="checkbox" name="attr_required_<?php 	echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" />Required</label><br />
					<label><input type="checkbox" name="attr_readonly_<?php 	echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" />Readonly</label><br />
					<label><input type="checkbox" name="attr_disabled_<?php 	echo $x; ?>" value="<?php echo $columnList[$x]['COLUMN_NAME']; ?>" />Disabled</label>
				</div>
			</td>
		</tr>
		<?php }//end for ?>
		<?php } else { ?>
		<tr>
			<td colspan="10" class="myContentInput">&nbsp;&nbsp;No column(s) found.. </td>
		</tr>
		<?php }//end else ?>
		<tr>
			<td colspan="11" class="contentButtonFooter">
				<form id="form2" name="form2" method="post" action="">
					<input type="reset" value="Reset" class="inputButton" onclick="resetForm()" />
					<input name="saveSelection" type="submit" class="inputButton" id="saveSelection" onclick="if(window.confirm('Generate this page?')) return true; else return false;" value="Generate Page" />
					<input id="menuForceRefresh" name="menuForceRefresh" type="hidden" value="RELOAD Side Menu" />
				</form>
			</td>
		</tr>
	</table>
	<?php } ?>
</form>
