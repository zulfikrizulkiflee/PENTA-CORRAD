<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

//==================================== PAGE ============================================
//if new page added
if($_POST['saveScreenNew'])
{
	//to prevent error
	if(trim($_POST['newMenuID']) == '')
		$_POST['newMenuID'] = 0;

	//get max pageid
	$getMaxPageRs = $mySQL->maxValue('FLC_PAGE','PAGEID') + 1;

	//set default name, if blank
	if(!$_POST['newPageName'])
		$_POST['newPageName'] = 'page_'.$getMaxPageRs;

	//insert page
	$insertCat = "insert into FLC_PAGE
					(PAGEID, PAGENAME, PAGETITLE, PAGEBREADCRUMBS, PAGEPREPROCESS, PAGEPOSTPROCESS, PAGEPRESCRIPT, PAGEPOSTSCRIPT, PAGENOTES, MENUID)
					values (".$getMaxPageRs.", '".$_POST['newPageName']."', '".$_POST['newPageTitle']."', '".$_POST['newPageBreadcrumbs']."',
						'".$_POST['newPagePreProcess']."', '".$_POST['newPagePostProcess']."', '".$_POST['newPagePreScript']."',
						'".$_POST['newPagePostScript']."', '".$_POST['newPageNotes']."', ".$_POST['newMenuID'].")";
	$insertCatRs = $myQuery->query($insertCat,'RUN');

	//display showScreen
	$_POST['showScreen'] = true;
	$_POST['code'] = $getMaxPageRs;
}//eof if

//if modify PAGE submitted
else if($_POST['saveScreenEdit'])
{
	//set default name, if blank
	if(!$_POST['editPageName'])
		$_POST['editPageName'] = 'page_'.$_POST['editMenuID'];

	//update category
	$updateCat = "update FLC_PAGE set
					PAGENAME = '".$_POST['editPageName']."',
					PAGETITLE = '".$_POST['editPageTitle']."',
					PAGEBREADCRUMBS = '".$_POST['editPageBreadcrumbs']."',
					PAGEPREPROCESS = '".$_POST['editPagePreProcess']."',
					PAGEPOSTPROCESS = '".$_POST['editPagePostProcess']."',
					PAGEPRESCRIPT = '".$_POST['editPagePreScript']."',
					PAGEPOSTSCRIPT = '".$_POST['editPagePostScript']."',
					PAGENOTES = '".$_POST['editPageNotes']."',
					MENUID = ".$_POST['editMenuID']."
					where PAGEID = ".$_POST['hiddenPageID'];
	$updateCatRs = $myQuery->query($updateCat,'RUN');

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if duplicate
else if($_POST['savePageDuplicate'])
{
	//duplicate the page
	$duplicatePageRs = $mySQL->duplicatePage($_POST['duplicatePageFrom'], $_POST['newMenuID']);

	//if successfully duplicated, show the edit screen
	if($duplicatePageRs)
	{
		$_POST['modifyCategory'] = true;
		$_POST['code'] = $duplicatePageRs;
	}//eof if
}//eof if

//if page deleted
else if($_POST['deleteCategory'])
{
	//delete page and everything related to it
	$deletePageRs = $mySQL->deletePage($_POST['code']);
}//eof if

//if new page
if($_POST['newCategory'])
{
	unset($_POST['code']);
}//eof if

//if page is to be modified
else if($_POST['modifyCategory'])
{
	//get page info
	$getPageInfo = "select * from FLC_PAGE where PAGEID = ".$_POST['code'];
	$getPageInfoRs = $myQuery->query($getPageInfo,'SELECT','NAME');
}//eof else if

//if new/edit/duplicate page
if($_POST['newCategory'] || $_POST['modifyCategory'] || $_POST['duplicatePage'])
{
	//if modify page
	if($_POST['modifyCategory'])
		$selectedPageId=$_POST['code'];

	//get list of page menu
	$getMenuRs = $mySQL->menuExcludePage($selectedPageId);
	$getMenuRsCount = count($getMenuRs);
}//eof if
//=================================== //PAGE ===========================================

//================================== COMPONENT =========================================
//if new component added
if($_POST['saveScreenRefNew'])
{
	//get max componentid
	$maxComponent = 'select max(COMPONENTID) from FLC_PAGE_COMPONENT';
	$maxComponentRs = $myQuery->query($maxComponent,'COUNT') + 1;

	//set default name, if blank
	if(!$_POST['newComponentName'])
		$_POST['newComponentName'] = 'component_'.$maxComponentRs;

	//set minimum order value
	if($_POST['newComponentOrder']==0||$_POST['newComponentOrder']=='')
		$_POST['newComponentOrder']=1;

	//increment order if option is after
	if($_POST['orderOption']=='++')
		$_POST['newComponentOrder']++;

	if($_POST['enableCollapse'] == '')
	{
		$_POST['enableCollapse'] = "0";
	}

	if($_POST['collapseDefault'] == '')
	{
		$_POST['collapseDefault'] = "0";
	}

	/*
	if($_POST['searchableData'] == '')
	{
		$_POST['searchableData'] = "null";
	}
	*/

	//prepare insert statement
	//insert statement part 1
	$newComponent_1 = "insert into FLC_PAGE_COMPONENT (COMPONENTID,COMPONENTNAME,COMPONENTTITLE,COMPONENTORDER,PAGEID,COMPONENTTYPE,
						COMPONENTSTATUS,COMPONENTPATH,COMPONENTPREPROCESS,COMPONENTPOSTPROCESS,COMPONENTPOSTSCRIPT,
						COMPONENTBINDINGTYPE,COMPONENTBINDINGSOURCE,COMPONENTPOSITION,COMPONENTSEARCH,
						COMPONENTCOLLAPSE,COMPONENTCOLLAPSEDEFAULT,COMPONENTQUERYMAXFETCH,COMPONENTQUERYDB";

	settype($_POST['newComponentSearch'],'integer');
	settype($_POST['newTabularMaxFetch'],'integer');

	//insert statement part 2
	$newComponent_2 = "values (".$maxComponentRs.",'".$_POST['newComponentName']."','".$_POST['newComponentTitle']."',
						".$_POST['newComponentOrder'].", ".$_POST['code'].",'".$_POST['newComponentType']."',".$_POST['newComponentStatus'].",
						'".$_POST['newComponentPath']."', '".$_POST['newComponentPreProcess']."','".$_POST['newComponentPostProcess']."',
						'".$_POST['newComponentPostScript']."','".$_POST['newDataBindingType']."',
						'".$_POST['newDataSource']."','".$_POST['newComponentPosition']."',".$_POST['newComponentSearch'].",".$_POST['enableCollapse'].",
						".$_POST['collapseDefault'].",".$_POST['newTabularMaxFetch'].",'".$_POST['newQueryType']."'";

	//default row number
	if($_POST['newComponentType'] == 'tabular'||$_POST['newComponentType'] == 'report')
	{
		//trim whitespaces
		$_POST['newTabularDefaultRowNo'] = trim($_POST['newTabularDefaultRowNo']);

		//set default value
		if($_POST['newTabularDefaultRowNo'] == '')
			$_POST['newTabularDefaultRowNo'] = 5;

		//default row
		$newComponent_1 .= ",COMPONENTABULARDEFAULTROWNO";
		$newComponent_2 .= ",".$_POST['newTabularDefaultRowNo'];

		//default int value to null to prevent error
		if($_POST['newAddRow']=='') $_POST['newAddRow'] = "null";
		if($_POST['newDeleteRow']=='') $_POST['newDeleteRow'] = "null";
		if($_POST['newAddRowDisabled']=='') $_POST['newAddRowDisabled'] = "null";
		if($_POST['newDeleteRowDisabled']=='') $_POST['newDeleteRowDisabled'] = "null";

		//add row
		$newComponent_1 .= ",COMPONENTADDROW";
		$newComponent_2 .= ",".$_POST['newAddRow'];
		$newComponent_1 .= ",COMPONENTDELETEROW";
		$newComponent_2 .= ",".$_POST['newDeleteRow'];
		$newComponent_1 .= ",COMPONENTADDROWDISABLED";
		$newComponent_2 .= ",".$_POST['newAddRowDisabled'];
		$newComponent_1 .= ",COMPONENTDELETEROWDISABLED";
		$newComponent_2 .= ",".$_POST['newDeleteRowDisabled'];

		//add row javascript
		$newComponent_1 .= ",COMPONENTADDROWJAVASCRIPT";
		$newComponent_2 .= ",'".$_POST['newAddJavascript']."'";

		//delete row javascript
		$newComponent_1 .= ",COMPONENTDELETEROWJAVASCRIPT";
		$newComponent_2 .= ",'".$_POST['newDeleteJavascript']."'";
	}

	//if form is query or report, append this to sql
	if($_POST['newComponentType'] == 'query' || $_POST['newComponentType'] == 'query_2_col' || $_POST['newComponentType'] == 'report' || $_POST['newComponentType'] == 'search_constraint')
	{
		/*$newComponent_1 .= ",COMPONENTTYPEQUERY";
		$newComponent_2 .= ",'". substr($_POST['newTypeQuery'],0,4000)."'";		//limit 4000 character*/

		//if type is search constraint, append this to sql
		if($_POST['newComponentType'] == 'search_constraint')
		{
			$newComponent_1 .= ",COMPONENTMASTERID";
			$newComponent_2 .= ",".$_POST['newMasterID'];
		}

		//if query unlimited is set
		if($_POST['newQueryLimit'])
		{
			$newComponent_1 .= ",COMPONENTQUERYUNLIMITED";
			$newComponent_2 .= ",".$_POST['newQueryLimit']."";
		}
	}

	//combine query statement
	$newComponent = $newComponent_1.")".$newComponent_2.")";

	//clean up sql
	$toReplace = array('[qs]','[Qs]','[qS]','[qd]','[Qd]','[qD]');
	$theReplacement = array('[QS]','[QS]','[QS]','[QD]','[QD]','[QD]');
	$newComponent = str_replace($toReplace,$theReplacement,$newComponent);

	//run the statement
	$newComponentRs = $myQuery->query($newComponent,'RUN');

	//if character too long, execute update to append
	$mySQL->storeUnlimitedChar('FLC_PAGE_COMPONENT', 'COMPONENTTYPEQUERY', $_POST['newTypeQuery'], " where COMPONENTID = ".$maxComponentRs);
	//appendTooLongCharacter('FLC_PAGE_COMPONENT', 'COMPONENTTYPEQUERY', $_POST['newTypeQuery'], "COMPONENTID = ".$maxComponentRs);

	//== permission =================================================================
	if($newComponentRs)
	{
		$selectedGroupCount=count($_POST['selectedGroup']);

		//loop on count
		for($x=0; $x<$selectedGroupCount; $x++)
		{
			//insert permission data into FLC_PERMISSION
			$insertPerm = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM, PERM_VALUE)
							values ('component', ".$_POST['selectedGroup'][$x].", ".$maxComponentRs.", '1')";
			$insertPermRs = $myQuery->query($insertPerm,'RUN');
		}//eof for
	}//eof if
	//== eof permission =============================================================

	/*===insert at specified position===*/
	//get current orders for component by page
	$getOrder = "select COMPONENTORDER, COMPONENTID
					from FLC_PAGE_COMPONENT
					where PAGEID=".$_POST['code']." and COMPONENTID != ".$maxComponentRs."
					order by COMPONENTORDER,COMPONENTID";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$getOrderRsCount = count($getOrderRs);

	//increment current component order
	$orderIncrement=false;

	for($x=0; $x<$getOrderRsCount; $x++)
	{
		if($getOrderRs[$x][0]==$_POST['newComponentOrder'])
		{
			$orderIncrement=true;
		}//eof if

		if($orderIncrement)
		{
			$orderUpdate = "update FLC_PAGE_COMPONENT
							set COMPONENTORDER=".(int)++$getOrderRs[$x][0]."
							where COMPONENTID = ".$getOrderRs[$x][1];
			$orderUpdateRs = $myQuery->query($orderUpdate,'RUN');
		}//eof if
	}//eof for

	//reset order
	$getOrder = "select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$_POST['code']." order by COMPONENTORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT','NAME');

	for($x=0; $x < count($getOrderRs); $x++)
	{
		$updateOrder = "update FLC_PAGE_COMPONENT set COMPONENTORDER = ".($x+1)."
								where PAGEID = ".$_POST['code']."
								and COMPONENTID = ".$getOrderRs[$x]['COMPONENTID'];
		$myQuery->query($updateOrder,'RUN');
	}

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if save screen edit component
else if($_POST['saveScreenRefEdit'])
{
	//set default name, if blank
	if(!$_POST['editComponentName'])
		$_POST['editComponentName'] = 'component_'.$_POST['hiddenComponentID'];

	//set minimum order value
	if($_POST['editComponentOrder']==0||$_POST['editComponentOrder']=='')
		$_POST['editComponentOrder']=1;

	//increment order if option is after
	if($_POST['orderOption']=='++')
		$_POST['editComponentOrder']++;

	//default int value to null to avoid error
	if($_POST['editComponentOrder'] == '')
		$_POST['editComponentOrder'] = "null";

	if($_POST['editComponentStatus'] == '')
		$_POST['editComponentStatus'] = "null";

	if($_POST['enableCollapse'] == '')
	{
		$_POST['enableCollapse'] = "0";
	}

	if($_POST['collapseDefault'] == '')
	{
		$_POST['collapseDefault'] = "0";
	}

	if($_POST['editTabularMaxFetch'] == '')
	{
		$_POST['editTabularMaxFetch'] = "0";
	}

	//prepare update statement
	//update statement part 1
	$updateRef = "update FLC_PAGE_COMPONENT
						set COMPONENTNAME = '".$_POST['editComponentName']."',
						COMPONENTTITLE = '".$_POST['editComponentTitle']."',
						COMPONENTORDER = ".$_POST['editComponentOrder'].",
						COMPONENTTYPE = '".$_POST['editComponentType']."',
						COMPONENTSTATUS = ".$_POST['editComponentStatus'].",
						COMPONENTPATH = '".$_POST['editComponentPath']."',
						COMPONENTPREPROCESS = '".$_POST['editComponentPreProcess']."',
						COMPONENTPOSTPROCESS = '".$_POST['editComponentPostProcess']."',
						COMPONENTPOSTSCRIPT = '".$_POST['editComponentPostScript']."',
						COMPONENTBINDINGTYPE = '".$_POST['editDataBindingType']."',
						COMPONENTBINDINGSOURCE = '".$_POST['editDataSource']."',
						COMPONENTPOSITION = '".$_POST['editComponentPosition']."',
						COMPONENTSEARCH = ".$_POST['editComponentSearch'].",
						COMPONENTCOLLAPSE = ".$_POST['enableCollapse'].",
						COMPONENTCOLLAPSEDEFAULT = ".$_POST['collapseDefault'].",
						COMPONENTQUERYMAXFETCH = ".$_POST['editTabularMaxFetch'];

	//default row number
	if($_POST['editComponentType'] == 'tabular'||$_POST['editComponentType'] == 'report')
	{
		//trim whitespaces
		$_POST['editTabularDefaultRowNo'] = trim($_POST['editTabularDefaultRowNo']);

		//set default value and
		if($_POST['editTabularDefaultRowNo'] == '')
			$_POST['editTabularDefaultRowNo'] = 5;

		//default int value to null to avoid error
		if($_POST['editAddRow'] == '') $_POST['editAddRow'] = "null";
		if($_POST['editDeleteRow'] == '') $_POST['editDeleteRow'] = "null";
		if($_POST['editAddRowDisabled'] == '') $_POST['editAddRowDisabled'] = "null";
		if($_POST['editDeleteRowDisabled'] == '') $_POST['editDeleteRowDisabled'] = "null";

		//append this to sql
		$updateRef .= ",COMPONENTABULARDEFAULTROWNO = ".$_POST['editTabularDefaultRowNo'];
		$updateRef .= ",COMPONENTADDROW = ".$_POST['editAddRow'];
		$updateRef .= ",COMPONENTDELETEROW = ".$_POST['editDeleteRow'];
		$updateRef .= ",COMPONENTADDROWDISABLED = ".$_POST['editAddRowDisabled'];
		$updateRef .= ",COMPONENTDELETEROWDISABLED = ".$_POST['editDeleteRowDisabled'];
		$updateRef .= ",COMPONENTADDROWJAVASCRIPT = '".$_POST['editAddJavascript']."'";
		$updateRef .= ",COMPONENTDELETEROWJAVASCRIPT = '".$_POST['editDeleteJavascript']."'";
	}

	//if form is form, remove component searchtype -> append this to sql
	if($_POST['editComponentType'] == 'form')
	{
		$updateRef .= ",COMPONENTSEARCHTYPE = null ";
	}

	//if form is query or report
	if($_POST['editComponentType'] == 'query' || $_POST['editComponentType'] == 'query_2_col' || $_POST['editComponentType'] == 'report' || $_POST['editComponentType'] == 'search_constraint')
	{
		//default int value to null to avoid error
		if($_POST['editQueryLimit'] == '')
			$_POST['editQueryLimit'] = "null";

		if($_POST['editMasterID'] == '')
			$_POST['editMasterID'] = "null";

		//$updateRef .= ",COMPONENTTYPEQUERY = '".substr($_POST['editTypeQuery'],0,4000)."'";		//limit 4000 character

		if($_POST['editQueryLimit'])
			$updateRef .= ",COMPONENTQUERYUNLIMITED = ".$_POST['editQueryLimit'];

		//if type is search constraint, append this to sql
		if($_POST['editComponentType'] == 'search_constraint')
			$updateRef .= ",COMPONENTMASTERID = ".$_POST['editMasterID'];
		//UPDATE COMPONENTQUERYDB		
			$updateRef .= ",COMPONENTQUERYDB = '".$_POST['editQueryType']."'";
	}

	//finalize update reference
	$updateRef .= " where COMPONENTID = ".$_POST['hiddenComponentID'];

	//clean up sql
	$toReplace = array('[qs]','[Qs]','[qS]','[qd]','[Qd]','[qD]');
	$theReplacement = array('[QS]','[QS]','[QS]','[QD]','[QD]','[QD]');
	$updateRef = str_replace($toReplace,$theReplacement,$updateRef);

	//run update statement
	$updateRefRs = $myQuery->query($updateRef,'RUN');

	//if character too long, execute update to append
	$mySQL->storeUnlimitedChar('FLC_PAGE_COMPONENT', 'COMPONENTTYPEQUERY', $_POST['editTypeQuery'], " where COMPONENTID = ".$_POST['hiddenComponentID']);
	//appendTooLongCharacter('FLC_PAGE_COMPONENT', 'COMPONENTTYPEQUERY', $_POST['editTypeQuery'], "COMPONENTID = ".$_POST['hiddenComponentID']);

	//== permission =================================================================
	if($updateRefRs)
	{
		//delete previous permission
		$deletePermRs = $mySQL->deletePermission('component', $_POST['hiddenComponentID']);

		$selectedGroupCount=count($_POST['selectedGroupEdit']);

		//loop on count
		for($x=0; $x<$selectedGroupCount; $x++)
		{
			//insert permission data into FLC_PERMISSION
			$insertPerm = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM, PERM_VALUE)
							values ('component', ".$_POST['selectedGroupEdit'][$x].", ".$_POST['hiddenComponentID'].", '1')";
			$insertPermRs = $myQuery->query($insertPerm,'RUN');
		}//eof for
	}//eof if
	//== eof permission =============================================================

	/*===insert at specified position===*/
	//get current orders for component by page
	$getOrder = "select COMPONENTORDER, COMPONENTID from FLC_PAGE_COMPONENT
					where PAGEID = ".$_POST['code']." and COMPONENTID != ".$_POST['hiddenComponentID']."
					order by COMPONENTORDER,COMPONENTID";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$getOrderRsCount = count($getOrderRs);

	//increment current component order
	$orderIncrement=false;
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		if($getOrderRs[$x][0]==$_POST['editComponentOrder'])
		{
			$orderIncrement=true;
		}//eof if

		if($orderIncrement)
		{
			$orderUpdate = "update FLC_PAGE_COMPONENT
							set COMPONENTORDER = ".(int)++$getOrderRs[$x][0]."
							where COMPONENTID = ".$getOrderRs[$x][1];
			$orderUpdateRs = $myQuery->query($orderUpdate,'RUN');
		}//eof if
	}//eof for

	//reset order
	$getOrder = "select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$_POST['code']." order by COMPONENTORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT','NAME');

	for($x=0; $x < count($getOrderRs); $x++)
	{
		$updateOrder = "update FLC_PAGE_COMPONENT set COMPONENTORDER = ".($x+1)."
								where PAGEID = ".$_POST['code']."
								and COMPONENTID = ".$getOrderRs[$x]['COMPONENTID'];
		$myQuery->query($updateOrder,'RUN');
	}

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if duplicate component clicked
else if($_POST['duplicateComponent'])
{
	//duplicate the component
	$duplicateComponentRs = $mySQL->duplicateComponent($_POST['hiddenComponentID'], $_POST['code']);

	//if successfully duplicated, show the edit screen
	if($duplicateComponentRs)
	{
		$_POST['editComponent']  = true;
		$_POST['hiddenComponentID'] = $duplicateComponentRs;
	}//eof if
}//eof else if

//if component deleted
else if($_POST['deleteComponent'])
{
	//delete component and everything related to it
	$deleteComponentRs = $mySQL->deleteComponent($_POST['hiddenComponentID']);

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if order - move up clicked
else if($_POST['moveUpComponent'])
{
	//check current ORDER position
	$checkPos = "select COMPONENTORDER from FLC_PAGE_COMPONENT where COMPONENTID = ".$_POST['hiddenComponentID'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//if current position is NOT 1, move up
	if($checkPosRs[0]['COMPONENTORDER'] != 1)
	{
		$updatePos = "update FLC_PAGE_COMPONENT set COMPONENTORDER = ".($checkPosRs[0]['COMPONENTORDER'] - 1)."
						where COMPONENTID = ".$_POST['hiddenComponentID'];
		$updatePosFlag = $myQuery->query($updatePos,'RUN');
	}//eof if

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if order - move down clicked
else if($_POST['moveDownComponent'])
{
	//check current ORDER position
	$checkPos = "select COMPONENTORDER from FLC_PAGE_COMPONENT where COMPONENTID = ".$_POST['hiddenComponentID'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//update the position
	$updatePos = "update FLC_PAGE_COMPONENT set COMPONENTORDER = ".($checkPosRs[0]['COMPONENTORDER'] + 1)."
					where COMPONENTID = ".$_POST['hiddenComponentID'];
	$updatePosFlag = $myQuery->query($updatePos,'RUN');

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if reset ordering button clicked
else if($_POST['resetOrderingComponent'])
{
	//get menu ordering level 2
	$getOrder = "select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$_POST['code']." order by COMPONENTORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT','NAME');
	$getOrderRsCount = count($getOrderRs);

	//for all menus level 2, update menu
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		$updateOrderComponent = "update FLC_PAGE_COMPONENT set COMPONENTORDER = ".($x+1)."
									where COMPONENTID = ".$getOrderRs[$x]['COMPONENTID'];
		$updateOrderComponentFlag = $myQuery->query($updateOrderComponent,'RUN');
	}//eof for

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if new component button clicked
if($_POST['newComponent'] || $_POST['editComponent'])
{
	//get latest ordering index
	$getOrder = "select max(COMPONENTORDER) as MAXORDER from FLC_PAGE_COMPONENT where PAGEID = ".$_POST['code'];
	$getOrderRs = $myQuery->query($getOrder,'COUNT') + 1;

	//get list of table
	$getTableRs = $mySQL->listTable(DB_OTHERS);

	//get list of views
	$getViewRs = $mySQL->listView(DB_OTHERS);

	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupPermissionNonSelected();
	$groupListNonSelectedCount=count($groupListNonSelected);
}//eof if

//if edit component button clicked
if($_POST['editComponent'])
{
	//get component info
	$getEditComponent = "select * from FLC_PAGE_COMPONENT where COMPONENTID = ".$_POST['hiddenComponentID'];
	$getEditComponentRs = $myQuery->query($getEditComponent,'SELECT','NAME');

	//get list of columns to be mapped (database mapping)
	$getColumnsRs = $mySQL->listTableColumn('',DB_OTHERS,$getEditComponentRs[0]['COMPONENTBINDINGSOURCE']);
	$getColumnsRsCount = count($getColumnsRs);

	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupPermissionNonSelected('component',$_POST['hiddenComponentID']);
	$groupListNonSelectedCount=count($groupListNonSelected);

	//list group user selected
	$groupListSelected=$mySQL->getUserGroupPermissionSelected('component',$_POST['hiddenComponentID']);
	$groupListSelectedCount=count($groupListSelected);
}//eof if
//================================= //COMPONENT ========================================

//================================= COMPONENT ITEMS ====================================
//if new component item button save clicked
if($_POST['saveScreenItemNew'])
{
	//get max item id
	$maxID = "select max(ITEMID) from FLC_PAGE_COMPONENT_ITEMS";
	$maxIDRs = $myQuery->query($maxID,'COUNT') + 1;

	//set default name, if blank
	if(!$_POST['newItemName'])
		$_POST['newItemName'] = 'item_'.$maxIDRs;

	//set minimum order value
	if($_POST['newItemOrder']==0||$_POST['newItemOrder']=='')
		$_POST['newItemOrder']=1;

	//increment order if option is after
	if($_POST['orderOption']=='++')
		$_POST['newItemOrder']++;

	//if item lookup type = predefined
	if($_POST['newLookupType'] == "predefined")
	{
		//make query template for item lookup
		$itemLookup = "select REFERENCECODE as FLC_ID, DESCRIPTION1 as FLC_NAME from REFGENERAL where MASTERCODE = (select REFERENCECODE from REFGENERAL where MASTERCODE = ''XXX'' and DESCRIPTION1 = ''".strtoupper($_POST['newPredefinedLookup'])."'') and REFERENCESTATUSCODE = ''00'' order by DESCRIPTION1";
	}
	else if($_POST['newLookupType'] == "advanced")
	{
		$itemLookup = $_POST['newAdvancedLookup'];
		//$itemLookup = substr($_POST['newAdvancedLookup'],0,4000);
	}

	//default int value to null to prevent error
	if($_POST['newItemOrder'] == '')
		$_POST['newItemOrder'] = "null";

	if($_POST['newItemInputLength'] == '')
		$_POST['newItemInputLength'] = "null";

	if($_POST['newItemTextAreaRows'] == '')
		$_POST['newItemTextAreaRows'] = "null";

	if($_POST['newTabIndex'] == '')
		$_POST['newTabIndex'] = "null";

	if($_POST['newCheckAll'] == '')
		$_POST['newCheckAll'] = "null";

	if($_POST['newItemMinChar'] == '')
		$_POST['newItemMinChar'] = "null";

	if($_POST['newItemMaxChar'] == '')
		$_POST['newItemMaxChar'] = "null";

	if($_POST['newItemDefaultValueQuery'] == '')
		$_POST['newItemDefaultValueQuery'] = "null";

	if($_POST['newAdvQueryLimit'] == '')
		$_POST['newAdvQueryLimit'] = "null";

	if($_POST['newItemStatus'] == '')
		$_POST['newItemStatus'] = "null";

	if($_POST['newItemUnique'] == '')
		$_POST['newItemUnique'] = "null";

	if($_POST['newItemPrimaryColumn'] == '')
		$_POST['newItemPrimaryColumn'] = "null";

	if($_POST['newItemDisabled'] == '')
		$_POST['newItemDisabled'] = "null";

	if($_POST['newItemReadonly'] == '')
		$_POST['newItemReadonly'] = "null";

	if($_POST['newItemAppend'] == '')
		$_POST['newItemAppend'] = "null";

	//item type is file
	if($_POST['newItemType'] == 'file')
	{
		//concat all inputs for upload separated by '|'
		$_POST['newUpload']=$_POST['newFileExtension'].'|'.$_POST['newUploadFolder'].'|'.$_POST['newMaxSize'];
	}//eof if

	//save new component
	$newItem = "insert into FLC_PAGE_COMPONENT_ITEMS
				(ITEMID, ITEMNAME, ITEMTITLE, ITEMTYPE, ITEMORDER, ITEMHINTS, ITEMNOTES, ITEMINPUTLENGTH, ITEMTEXTAREAROWS,
					ITEMDEFAULTVALUE, ITEMDEFAULTVALUEQUERY, ITEMLOOKUPDB,ITEMLOOKUPTYPE,ITEMDEFAULTVALUEDB, COMPONENTID,
					ITEMAGGREGATECOLUMN, ITEMAGGREGATECOLUMNLABEL, ITEMCHECKALL, ITEMTABINDEX, ITEMSTATUS, ITEMMINCHAR, ITEMMAXCHAR,
					ITEMUNIQUE, MAPPINGID, ITEMPRIMARYCOLUMN, ITEMUPLOAD, ITEMTEXTALIGN,
					ITEMLOOKUPUNLIMITED, ITEMUPPERCASE, ITEMDISABLED, ITEMREADONLY, ITEMAPPENDTOBEFORE, ITEMMAPPING, ITEMPLACEHOLDER)
				values (".$maxIDRs.", '".$_POST['newItemName']."', '".$_POST['newItemTitle']."', '".$_POST['newItemType']."',
					".$_POST['newItemOrder'].", '".$_POST['newItemHints']."', '".$_POST['newItemNotes']."', ".$_POST['newItemInputLength'].",
					".$_POST['newItemTextAreaRows'].", '".$_POST['newItemDefaultValue']."', ".$_POST['newItemDefaultValueQuery'].",'".$_POST['newAdvanceDB']."',
					'".$_POST['newLookupType']."','".$_POST['newDefaultDB']."', ".$_POST['componentID'].",
					'".$_POST['newAggregateColumn']."', '".$_POST['newAggregateColumnLabel']."', ".$_POST['newCheckAll'].",
					".$_POST['newTabIndex'].", ".$_POST['newItemStatus'].", ".$_POST['newItemMinChar'].", ".$_POST['newItemMaxChar'].",
					".$_POST['newItemUnique'].", '".$_POST['newMappingID']."',
					".$_POST['newItemPrimaryColumn'].", '".$_POST['newUpload']."',
					'".$_POST['newItemTextAlign']."', ".$_POST['newAdvQueryLimit'].", '".$_POST['newItemUppercase']."',
					".$_POST['newItemDisabled'].", ".$_POST['newItemReadonly'].", ".$_POST['newItemAppend'].", '".$_POST['itemMapping']."','".$_POST['newItemPlaceholder']."')";
	$newItemRs = $myQuery->query($newItem,'RUN');

	//if character too long, execute update to append
	$mySQL->storeUnlimitedChar('FLC_PAGE_COMPONENT_ITEMS', 'ITEMLOOKUP', $itemLookup, " where ITEMID = ".$maxIDRs);
	//appendTooLongCharacter('FLC_PAGE_COMPONENT_ITEMS', 'ITEMLOOKUP', $itemLookup, "ITEMID = ".$maxIDRs);

	//if item is chart and item is inserted
	if($_POST['newItemType'] == 'chart' && $newItemRs)
	{
		//default int value to null to prevent error
		if($_POST['chartDecimalPrecision'] == '')
			$_POST['chartDecimalPrecision'] = "0";
		if($_POST['chartPshowValue'] == '')
			$_POST['chartPshowValue'] = "0";
		if($_POST['chartSshowValue'] == '')
			$_POST['chartSshowValue'] = "0";
		if($_POST['chartTrendValue'] == '')
			$_POST['chartTrendValue'] = "null";

		//insert chart
		$insertChart = "insert into FLC_CHART (ITEM_ID, CHART_BG_COLOR, CHART_NO_PREFIX, CHART_DECIMAL_PRECISION,
							CHART_PTYPE, CHART_X_AXIS_LABEL, CHART_PY_AXIS_LABEL,CHART_PSQL_DB, CHART_PSHOW_VALUE,
							CHART_SY_AXIS_LABEL, CHART_SSQL_DB,CHART_SSHOW_VALUE, CHART_TREND_LABEL, CHART_TREND_VALUE, CHART_TREND_COLOR)
						values
							(".$maxIDRs.", '".$_POST['chartBgColor']."', '".$_POST['chartNoPrefix']."',
							".$_POST['chartDecimalPrecision'].", '".$_POST['chartPtype']."', '".$_POST['chartXAxisLabel']."',
							'".$_POST['chartPyAxisLabel']."','".$_POST['chartPsqlDB']."', ".$_POST['chartPshowValue'].",
							'".$_POST['chartSyAxisLabel']."','".$_POST['chartSsqlDB']."', ".$_POST['chartSshowValue'].",
							'".$_POST['chartTrendLabel']."', ".$_POST['chartTrendValue'].", '".$_POST['chartTrendColor']."')";
		$insertChartRs = $myQuery->query($insertChart,'RUN');

		//if character too long, execute update to append
		$mySQL->storeUnlimitedChar('FLC_CHART', 'CHART_PSQL', $_POST['chartPsql'], " where ITEM_ID = ".$maxIDRs);

		//if character too long, execute update to append
		$mySQL->storeUnlimitedChar('FLC_CHART', 'CHART_SSQL', $_POST['chartSsql'], " where ITEM_ID = ".$maxIDRs);
	}//eof if

	//save validation

	//print_r ($_POST);
	if($_POST['newItemValidation'])
	{
		$maxTriggerRs = $mySQL->maxValue('FLC_TRIGGER','TRIGGER_ID',0) + 1;

		$validation = "insert into FLC_TRIGGER
					(TRIGGER_ID, TRIGGER_TYPE, TRIGGER_EVENT, TRIGGER_BL, TRIGGER_ITEM_TYPE, TRIGGER_ORDER, TRIGGER_ITEM_ID, TRIGGER_STATUS)
					values (".$maxTriggerRs.",'".'JS'."', '".'onchange'."', '".'FLC_VALIDATION_CALLER'."', '".'item'."', 1,".$maxIDRs.", 1)";
		$validationRs = $myQuery->query($validation,'RUN');

		$validateAgainst = array();

		for ($i=0 ; $i< count($_POST['newItemValidation']); $i++)
		{
			$validateAgainst[] = $_POST['newItemValidation'][$i];

		}

		$validation_parameter = "insert into FLC_TRIGGER_PARAMETER
										(TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
										values (".$maxTriggerRs.",1, '''".implode('|',$validateAgainst)."''')";

		$validation_parameterRs = $myQuery->query($validation_parameter,'RUN');

		$validation_parameter2 = "insert into FLC_TRIGGER_PARAMETER
									(TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
									values (".$maxTriggerRs.",2, '".'this.value'."')";
		$validation_parameterRs2 = $myQuery->query($validation_parameter2,'RUN');

		$validation_parameter3 = "insert into FLC_TRIGGER_PARAMETER
									(TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
								 values (".$maxTriggerRs.",3, '".'this'."')";
		$validation_parameterRs3 = $myQuery->query($validation_parameter3,'RUN');
	}

	//== permission =================================================================
	if($newItemRs)
	{
		$selectedGroupCount=count($_POST['selectedGroup']);

		//loop on count
		for($x=0; $x<$selectedGroupCount; $x++)
		{
			//insert permission data into FLC_PERMISSION
			$insertPerm = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM, PERM_VALUE)
							values ('item', ".$_POST['selectedGroup'][$x].", ".$maxIDRs.", '1')";
			$insertPermRs = $myQuery->query($insertPerm,'RUN');
		}//eof for
	}//eof if
	//== eof permission =============================================================

	/*===insert component item at specified position===*/
	//get current orders for component by page
	$getOrder = "select b.ITEMORDER, b.ITEMID, a.COMPONENTID
					from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b
					where a.COMPONENTID = b.COMPONENTID
					and a.PAGEID = ".$_POST['code']."
					and a.COMPONENTID = ".$_POST['componentID']."
					and b.ITEMID != ".$maxIDRs."
					order by a.COMPONENTORDER, b.ITEMORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$getOrderRsCount = count($getOrderRs);

	//increment current component order
	$orderIncrement=false;
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		if($getOrderRs[$x][0]==$_POST['newItemOrder'])
		{
			$orderIncrement=true;
		}//eof if

		if($orderIncrement)
		{
			$orderUpdate = "update FLC_PAGE_COMPONENT_ITEMS
							set ITEMORDER=".(int)++$getOrderRs[$x][0]."
							where ITEMID=".$getOrderRs[$x][1]." and COMPONENTID=".$getOrderRs[$x][2];
			$orderUpdateRs = $myQuery->query($orderUpdate,'RUN');
		}//eof if
	}//eof for

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if component item edit, save screen clicked, save ler
else if($_POST['saveScreenItemEdit'])
{
	//set default name, if blank
	if(!$_POST['editItemName'])
		$_POST['editItemName'] = 'item_'.$_POST['hiddenItemID'];

	//set minimum order value
	if($_POST['editItemOrder']==0||$_POST['editItemOrder']=='')
		$_POST['editItemOrder']=1;

	//increment order if option is after
	if($_POST['orderOption']=='++')
		$_POST['editItemOrder']++;

	//if item lookup type = predefined
	if($_POST['editLookupType'] == "predefined")
	{
		//make query template for item lookup
		$itemLookup = "select REFERENCECODE as FLC_ID, DESCRIPTION1 as FLC_NAME from REFGENERAL where MASTERCODE = (select REFERENCECODE from REFGENERAL where MASTERCODE = ''XXX'' and DESCRIPTION1 = ''".strtoupper($_POST['editPredefinedLookup'])."'') and REFERENCESTATUSCODE = ''00'' order by DESCRIPTION1";
	}
	else if($_POST['editLookupType'] == "advanced")
	{
		//$itemLookup = substr($_POST['editAdvancedLookup'], 0, 4000);
		$itemLookup = $_POST['editAdvancedLookup'];
	}

	//default int value to null to prevent error
	if($_POST['editItemOrder'] == '')
		$_POST['editItemOrder'] = "null";

	if($_POST['editTabIndex'] == '')
		$_POST['editTabIndex'] = "null";

	if($_POST['editCheckAll'] == '')
		$_POST['editCheckAll'] = "null";

	if($_POST['editItemMinChar'] == '')
		$_POST['editItemMinChar'] = "null";

	if($_POST['editItemMaxChar'] == '')
		$_POST['editItemMaxChar'] = "null";

	if($_POST['editItemInputLength'] == '')
		$_POST['editItemInputLength'] = "null";

	if($_POST['editTextareaRows'] == '')
		$_POST['editTextareaRows'] = "null";

	if($_POST['editItemDefaultValueQuery'] == '')
		$_POST['editItemDefaultValueQuery'] = "null";

	if($_POST['editAdvQueryLimit'] == '')
		$_POST['editAdvQueryLimit'] = "null";

	if($_POST['editItemStatus'] == '')
		$_POST['editItemStatus'] = "null";

	if($_POST['editItemUnique'] == '')
		$_POST['editItemUnique'] = "null";

	if($_POST['editItemPrimaryColumn'] == '')
		$_POST['editItemPrimaryColumn'] = "null";

	if($_POST['editItemDisabled'] == '')
		$_POST['editItemDisabled'] = "null";

	if($_POST['editItemReadonly'] == '')
		$_POST['editItemReadonly'] = "null";

	if($_POST['editItemAppend'] == '')
		$_POST['editItemAppend'] = "null";

	//item type is file
	if($_POST['editItemType'] == 'file')
	{
		//concat all inputs for upload separated by '|'
		$_POST['editUpload']=$_POST['editFileExtension'].'|'.$_POST['editUploadFolder'].'|'.$_POST['editMaxSize'];
	}//eof if

	//update component edit changes
	$updateComponentItem = "update FLC_PAGE_COMPONENT_ITEMS
							set ITEMNAME = '".$_POST['editItemName']."',
							ITEMTITLE = '".$_POST['editItemTitle']."',
							ITEMTYPE = '".$_POST['editItemType']."',
							ITEMORDER = ".$_POST['editItemOrder'].",
							ITEMHINTS = '".$_POST['editItemHints']."',
							ITEMNOTES = '".$_POST['editItemNotes']."',
							ITEMINPUTLENGTH = ".$_POST['editItemInputLength'].",
							ITEMTEXTAREAROWS = ".$_POST['editTextareaRows'].",
							ITEMDEFAULTVALUE = '".$_POST['editItemDefaultValue']."',
							ITEMDEFAULTVALUEQUERY = ".$_POST['editItemDefaultValueQuery'].",
							ITEMDEFAULTVALUEDB = '".$_POST['editDefaultDB']."',
							ITEMLOOKUPTYPE = '".$_POST['editLookupType']."',
							ITEMLOOKUPDB = '".$_POST['editAdvanceDB']."',
							COMPONENTID = ".$_POST['componentID'].",
							ITEMTABINDEX = ".$_POST['editTabIndex'].",
							ITEMAGGREGATECOLUMN = '".$_POST['editAggregateColumn']."',
							ITEMAGGREGATECOLUMNLABEL = '".$_POST['editAggregateColumnLabel']."',
							ITEMCHECKALL = ".$_POST['editCheckAll'].",
							ITEMSTATUS = ".$_POST['editItemStatus'].",
							ITEMMINCHAR = ".$_POST['editItemMinChar'].",
							ITEMMAXCHAR = ".$_POST['editItemMaxChar'].",
							ITEMUNIQUE = ".$_POST['editItemUnique'].",
							MAPPINGID = '".$_POST['editMappingID']."',
							ITEMPRIMARYCOLUMN = ".$_POST['editItemPrimaryColumn'].",
							ITEMUPLOAD = '".$_POST['editUpload']."',
							ITEMTEXTALIGN = '".$_POST['editItemTextAlign']."',
							ITEMLOOKUPUNLIMITED = ".$_POST['editAdvQueryLimit'].",
							ITEMUPPERCASE = '".$_POST['editItemUppercase']."',
							ITEMDISABLED = ".$_POST['editItemDisabled'].",
							ITEMREADONLY = ".$_POST['editItemReadonly'].",
							ITEMAPPENDTOBEFORE = ".$_POST['editItemAppend'].",
							ITEMMAPPING = '".$_POST['itemMapping']."',
							ITEMPLACEHOLDER = '".$_POST['editItemPlaceholder']."'
							where ITEMID = ".$_POST['hiddenItemID'];
	$updateComponentItemRs = $myQuery->query($updateComponentItem,'RUN');

	//if character too long, execute update to append
	$mySQL->storeUnlimitedChar('FLC_PAGE_COMPONENT_ITEMS', 'ITEMLOOKUP', $itemLookup, " where ITEMID = ".$_POST['hiddenItemID']);
	//appendTooLongCharacter('FLC_PAGE_COMPONENT_ITEMS', 'ITEMLOOKUP', $itemLookup, "ITEMID = ".$_POST['hiddenItemID']);

	//if item is chart and item is updated
	if($_POST['editItemType'] == 'chart' && $updateComponentItemRs)
	{
		//default int value to null to prevent error
		if($_POST['chartDecimalPrecision'] == '')
			$_POST['chartDecimalPrecision'] = "0";
		if($_POST['chartPshowValue'] == '')
			$_POST['chartPshowValue'] = "0";
		if($_POST['chartSshowValue'] == '')
			$_POST['chartSshowValue'] = "0";
		if($_POST['chartTrendValue'] == '')
			$_POST['chartTrendValue'] = "null";

		//delete if previously chart exist
		$deleteChartRs = $mySQL->deleteChart($_POST['hiddenItemID']);

		//insert chart
		$insertChart = "insert into FLC_CHART (ITEM_ID, CHART_BG_COLOR, CHART_NO_PREFIX, CHART_DECIMAL_PRECISION,
							CHART_PTYPE, CHART_X_AXIS_LABEL, CHART_PY_AXIS_LABEL,CHART_PSQL_DB, CHART_PSHOW_VALUE,
							CHART_SY_AXIS_LABEL, CHART_SSQL_DB,CHART_SSHOW_VALUE, CHART_TREND_LABEL, CHART_TREND_VALUE, CHART_TREND_COLOR)
						values
							(".$_POST['hiddenItemID'].", '".$_POST['chartBgColor']."', '".$_POST['chartNoPrefix']."',
							".$_POST['chartDecimalPrecision'].", '".$_POST['chartPtype']."', '".$_POST['chartXAxisLabel']."',
							'".$_POST['chartPyAxisLabel']."','".$_POST['chartPsqlDB']."', ".$_POST['chartPshowValue'].",
							'".$_POST['chartSyAxisLabel']."','".$_POST['chartSsqlDB']."', ".$_POST['chartSshowValue'].",
							'".$_POST['chartTrendLabel']."', ".$_POST['chartTrendValue'].", '".$_POST['chartTrendColor']."')";
		$insertChartRs = $myQuery->query($insertChart,'RUN');

		//if character too long, execute update to append
		$mySQL->storeUnlimitedChar('FLC_CHART', 'CHART_PSQL', $_POST['chartPsql'], " where ITEM_ID = ".$_POST['hiddenItemID']);

		//if character too long, execute update to append
		$mySQL->storeUnlimitedChar('FLC_CHART', 'CHART_SSQL', $_POST['chartSsql'], " where ITEM_ID = ".$_POST['hiddenItemID']);
	}//eof if

	//TODO - ADD NAMA BL
	//delete if previous validation exist
	$trigger_id = "select b.TRIGGER_ID from FLC_TRIGGER a, FLC_TRIGGER_PARAMETER b
						where a.TRIGGER_ID = b.TRIGGER_ID 
						and a.TRIGGER_ITEM_ID=".$_POST['hiddenItemID']."
						and a.TRIGGER_ITEM_TYPE = 'item'
						and UPPER(a.TRIGGER_BL) = 'FLC_VALIDATION_CALLER'";
	$trigger_idRs = $myQuery->query($trigger_id,'SELECT', 'NAME');

	if(count($trigger_idRs) > 0)
	{
		$id = $trigger_idRs[0]['TRIGGER_ID'];
		$deleteValidationParameter = "delete from FLC_TRIGGER_PARAMETER where TRIGGER_ID =".$id;
		$deleteValidationParameterRs = $myQuery->query($deleteValidationParameter,'RUN');

		$deleteValidation = "delete from FLC_TRIGGER 
								where TRIGGER_ITEM_ID = ".$_POST['hiddenItemID']." 
								and TRIGGER_ITEM_TYPE = 'item'
								and UPPER(TRIGGER_BL) = 'FLC_VALIDATION_CALLER'";
		$deleteValidationRs = $myQuery->query($deleteValidation,'RUN');
	}

	//insert validation
	if($_POST['ItemValidation'])
	{
		$maxTriggerRs = $mySQL->maxValue('FLC_TRIGGER','TRIGGER_ID',0) + 1;

		//$maxID = "select max(ITEMID) from FLC_PAGE_COMPONENT_ITEMS";
		//$MAXID = $myQuery->query($maxID,'SELECT','INDEX');
		$validation = "insert into FLC_TRIGGER
					(TRIGGER_ID, TRIGGER_TYPE, TRIGGER_EVENT, TRIGGER_BL, TRIGGER_ITEM_TYPE, TRIGGER_ORDER, TRIGGER_ITEM_ID, TRIGGER_STATUS)
					values (".$maxTriggerRs.",'".'JS'."', '".'onchange'."', '".'FLC_VALIDATION_CALLER'."', '".'item'."', 1,".$_POST['hiddenItemID'].", 1)";
		$validationRs = $myQuery->query($validation,'RUN');

		$validateAgainst = array();

		for ($i=0 ; $i< count($_POST['ItemValidation']); $i++)
		{
			$validateAgainst[] = $_POST['ItemValidation'][$i];

		}

		$validation_parameter = "insert into FLC_TRIGGER_PARAMETER
										(TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
										values (".$maxTriggerRs.",1, '''".implode('|',$validateAgainst)."''')";

		$validation_parameterRs = $myQuery->query($validation_parameter,'RUN');

		$validation_parameter2 = "insert into FLC_TRIGGER_PARAMETER
									(TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
									values (".$maxTriggerRs.",2, '".'this.value'."')";
		$validation_parameterRs2 = $myQuery->query($validation_parameter2,'RUN');

		$validation_parameter3 = "insert into FLC_TRIGGER_PARAMETER
									(TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
								 values (".$maxTriggerRs.",3, '".'this'."')";
		$validation_parameterRs3 = $myQuery->query($validation_parameter3,'RUN');
	}

	//== permission =================================================================
	if($updateComponentItemRs)
	{
		//delete previous permission
		$deletePermRs = $mySQL->deletePermission('item', $_POST['hiddenItemID']);

		$selectedGroupCount=count($_POST['selectedGroupEdit']);

		//loop on count
		for($x=0; $x<$selectedGroupCount; $x++)
		{
			//insert permission data into FLC_PERMISSION
			$insertPerm = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM, PERM_VALUE)
							values ('item', ".$_POST['selectedGroupEdit'][$x].", ".$_POST['hiddenItemID'].", '1')";
			$insertPermRs = $myQuery->query($insertPerm,'RUN');
		}//eof for
	}//eof if
	//== eof permission =============================================================

	/*===insert component item at specified position===*/
	//get current orders for component by page
	$getOrder = "select b.ITEMORDER, b.ITEMID, a.COMPONENTID
					from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b
					where a.COMPONENTID = b.COMPONENTID
					and a.PAGEID = ".$_POST['code']."
					and a.COMPONENTID = ".$_POST['componentID']."
					and b.ITEMID != ".$_POST['hiddenItemID']."
					order by a.COMPONENTORDER, b.ITEMORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$getOrderRsCount = count($getOrderRs);

	//increment current component order
	$orderIncrement=false;
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		if($getOrderRs[$x][0]==$_POST['editItemOrder'])
		{
			$orderIncrement=true;
		}//eof if

		if($orderIncrement)
		{
			$orderUpdate = "update FLC_PAGE_COMPONENT_ITEMS
							set ITEMORDER=".(int)++$getOrderRs[$x][0]."
							where ITEMID=".$getOrderRs[$x][1]." and COMPONENTID=".$getOrderRs[$x][2];
			$orderUpdateRs = $myQuery->query($orderUpdate,'RUN');
		}//eof if
	}//eof for

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if duplicate component items
else if($_POST['duplicateReference'])
{
	//duplicate the item
	$duplicateItemRs = $mySQL->duplicateItem($_POST['hiddenItemID']);

	//if successfully duplicated, show the edit screen
	if($duplicateItemRs)
	{
		$_POST['editItem'] = true;
		$_POST['hiddenItemID'] = $duplicateItemRs;
	}//eof if
}//eof if

//if component item deleted
else if($_POST['deleteReference'])
{
	//delete item and everything related to it
	$deleteComponentItemRs = $mySQL->deleteItem($_POST['hiddenItemID']);

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if order - move up clicked
else if($_POST['moveUpItem'])
{
	//check current ORDER position
	$checkPos = "select ITEMORDER from FLC_PAGE_COMPONENT_ITEMS where ITEMID = ".$_POST['hiddenItemID'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//if current position is NOT 1, move up
	if($checkPosRs[0]['ITEMORDER'] != 1)
	{
		$updatePos = "update FLC_PAGE_COMPONENT_ITEMS
						set ITEMORDER = ".($checkPosRs[0]['ITEMORDER'] - 1)."
						where ITEMID = ".$_POST['hiddenItemID'];
		$updatePosFlag = $myQuery->query($updatePos,'RUN');
	}//eof if

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if order - move down clicked
else if($_POST['moveDownItem'])
{
	//check current ORDER position
	$checkPos = "select ITEMORDER from FLC_PAGE_COMPONENT_ITEMS where ITEMID = ".$_POST['hiddenItemID'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//update position
	$updatePos = "update FLC_PAGE_COMPONENT_ITEMS set ITEMORDER = ".($checkPosRs[0]['ITEMORDER'] + 1)."
					where ITEMID = ".$_POST['hiddenItemID'];
	$updatePosFlag = $myQuery->query($updatePos,'RUN');

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if reset ordering button clicked
else if($_POST['resetOrderingItem'])
{
	$getOrder = "select a.COMPONENTID, b.ITEMID, b.ITEMORDER
					from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b
					where a.COMPONENTID = b.COMPONENTID and a.PAGEID = ".$_POST['code']."
					order by a.COMPONENTORDER, b.ITEMORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$countComponentItem = count($getOrderRs);		//count result rows

	$compTemp=$getOrderRs[0][0];	//temporary component id
	for($x=0,$y=1; $x<$countComponentItem; $x++,$y++)
	{
		if($getOrderRs[$x][0]!=$compTemp)	//new component id
		{
			$y=1;	//initiate order
			$compTemp=$getOrderRs[$x][0];	//new temporary component id
		}//eof if

		//update item by component
		$updateOrder = "update FLC_PAGE_COMPONENT_ITEMS
								set ITEMORDER = ".($y)."
								where COMPONENTID = ".$getOrderRs[$x][0]." and ITEMID=".$getOrderRs[$x][1];
		$updateOrderItemFlag = $myQuery->query($updateOrder,'RUN');
	}//eof for

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if new component item or edit component item clicked
if($_POST['newItem'] || $_POST['editItem'])
{
	//get item type
	$itemType = "select REFERENCECODE,DESCRIPTION2 from REFSYSTEM
					where MASTERCODE = (select REFERENCECODE
									from REFSYSTEM
									where MASTERCODE =  'XXX'
									and DESCRIPTION1 = 'SYS_INPUT_TYPE')
					order by DESCRIPTION2,REFERENCECODE";
	$itemTypeRs = $myQuery->query($itemType,'SELECT','NAME');

	$pluginName = "select EXT_NAME, EXT_TITLE from FLC_EXTENSION where EXT_TYPE = 'item' order by EXT_NAME";
	$pluginNameRs = $myQuery->query($pluginName,'SELECT','NAME');

	//predefined lookup
	$predefined = "select DESCRIPTION1
						from REFGENERAL
						where MASTERCODE = 'XXX' and REFERENCESTATUSCODE = '00' and DESCRIPTION1 is not null
						order by DESCRIPTION1";
	$predefinedRs = $myQuery->query($predefined,'SELECT','NAME');
	$predefinedRsCount = count($predefinedRs);

	/*//get component item for current page
	$getItemName = "select b.ITEMORDER, b.ITEMNAME, a.COMPONENTID
						from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b
						where a.COMPONENTID = b.COMPONENTID
						and a.PAGEID = ".$_POST['code']."
						order by a.COMPONENTORDER, b.ITEMORDER";
	$getItemNameRs = $myQuery->query($getItemName,'SELECT');
	$getItemNameRsCount = count($getItemNameRs);*/
}//eof if

//if new component item clicked
if($_POST['newItem'])
{
	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupPermissionNonSelected();
	$groupListNonSelectedCount=count($groupListNonSelected);
}//eof if

//if edit component item clicked, show detail
else if($_POST['editItem'])
{
	//show reference detail
	$showEditComponentItem = "select a.COMPONENTTYPE, a.COMPONENTBINDINGTYPE, a.COMPONENTBINDINGSOURCE, b.*
								from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b
								where a.COMPONENTID = b.COMPONENTID and ITEMID = ".$_POST['hiddenItemID'];
	$showEditComponentItemRs = $myQuery->query($showEditComponentItem,'SELECT','NAME');


	$showValidation = "select a.TRIGGER_ITEM_ID, a.TRIGGER_BL, a.TRIGGER_EVENT, a.TRIGGER_ID, b.PARAMETER_VALUE FROM FLC_TRIGGER a, FLC_TRIGGER_PARAMETER b
						WHERE a.TRIGGER_ID = b.TRIGGER_ID and TRIGGER_ITEM_ID = ".$_POST['hiddenItemID'];
	$showValidateRs = $myQuery->query($showValidation,'SELECT', 'NAME');


	$parameter_value =explode('|',str_replace("'",'',trim($showValidateRs[0]['PARAMETER_VALUE'])));

	//--------------- upload component-----------------
	$uploadTemp=explode('|',$showEditComponentItemRs[0]['ITEMUPLOAD']);
	$uploadExtension=$uploadTemp[0];
	$uploadFolder=$uploadTemp[1];
	$uploadMaxSize=$uploadTemp[2];

	//---------------database mapping -----------------
	//if hav value when editting
	if($showEditComponentItemRs[0]['COMPONENTID'])
		$_POST['componentID']=$showEditComponentItemRs[0]['COMPONENTID'];

	//if component mapping is set
	if($_POST['componentID'])
	{
		//get table / view name to be mapped
		$getMappingTable = "select COMPONENTBINDINGTYPE, COMPONENTBINDINGSOURCE
							from FLC_PAGE_COMPONENT where COMPONENTID = ".$_POST['componentID'];
		$getMappingTableRs = $myQuery->query($getMappingTable,'SELECT','NAME');

		//if binding type table / view, get list of columns
		if($getMappingTableRs[0]['COMPONENTBINDINGTYPE'] == 'table' || $getMappingTableRs[0]['COMPONENTBINDINGTYPE'] == 'view')
		{
			//if table / view name is not null
			if($getMappingTableRs[0]['COMPONENTBINDINGSOURCE'] != '')
			{
				//get list of columns to be mapped (database mapping)
				$getColumnsRs = $mySQL->listTableColumn('','',$getMappingTableRs[0]['COMPONENTBINDINGSOURCE']);
				$getColumnsRsCount = count($getColumnsRs);
			}//end if
		}//end if get mapping table rs
	}//end if post component id
	//-------------------------------------------------

	//------------------chart--------------------------
	if($showEditComponentItemRs[0]['ITEMTYPE'] == 'chart')
	{
		$getChartItem = "select * from FLC_CHART where ITEM_ID = ".$_POST['hiddenItemID'];
		$getChartItemRs = $myQuery->query($getChartItem,'SELECT','NAME');
	}//eof if
	//-------------------------------------------------

	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupPermissionNonSelected('item',$_POST['hiddenItemID']);
	$groupListNonSelectedCount=count($groupListNonSelected);

	//list group user selected
	$groupListSelected=$mySQL->getUserGroupPermissionSelected('item',$_POST['hiddenItemID']);
	$groupListSelectedCount=count($groupListSelected);
}//eof if
//============================== // COMPONENT ITEMS ====================================

//close screen
if($_POST['closeScreen'])
{
	$_POST['showScreen'] = true;
}//eof elseif

//if showScreen and code not null, show component and component items
if($_POST['showScreen'] && $_POST['code'])
{
	//show component listing
	$reference = "select a.*,
						(select count(*) from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'component' and TRIGGER_ITEM_ID = a.COMPONENTID) TRIGGER_ID
					from FLC_PAGE_COMPONENT a
					where a.PAGEID = ".$_POST['code']. "
					order by a.COMPONENTORDER";
	$referenceRs = $myQuery->query($reference,'SELECT','NAME');
	$referenceRsCount = count($referenceRs);

	//show component items listing
	$reference_2 = "select a.COMPONENTNAME, b.*,
						(select count(*) from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'item' and TRIGGER_ITEM_ID = b.ITEMID) TRIGGER_ID
					from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b
					where a.COMPONENTID = b.COMPONENTID
					and a.PAGEID = ".$_POST['code']. "
					order by a.COMPONENTORDER, b.ITEMORDER";
	$reference_2Rs = $myQuery->query($reference_2,'SELECT','NAME');
	$reference_2RsCount = count($reference_2Rs);
}

//get list of page menu
$generalRs = $mySQL->menu($_POST['pageSearch']);
$generalRsCount = count($generalRs);

//get list of menus to link pages to
$menu = "select ".$mySQL->concat('b.MENUTITLE', '\' / \'','a.MENUTITLE')." as MENUTITLE, a.MENUID
			from FLC_MENU a, FLC_MENU b, FLC_PAGE c
			where a.MENUPARENT = b.MENUID
			and a.MENUID = c.MENUID
			and a.MENUPARENT != 0
			order by ".$mySQL->concat('b.MENUTITLE', '\' / \'','a.MENUTITLE');
$menuRs = $myQuery->query($menu,'SELECT','NAME');

//----------------------queries-----------------------
if($_POST['code'])
{
	//get component list
	$getComponent = "select * from FLC_PAGE_COMPONENT
						where COMPONENTTYPE not in ('custom','iframe') and PAGEID = ".$_POST['code']."
						order by COMPONENTNAME";
	$getComponentRs = $myQuery->query($getComponent,'SELECT','NAME');
	$getComponentRsCount = count($getComponentRs);

	//get parent component info and max order of items
	$getParentComponent = "select b.COMPONENTID, b.COMPONENTTYPE, b.COMPONENTBINDINGTYPE, b.COMPONENTBINDINGSOURCE,
								max(".$mySQL->isNullSQL('a.ITEMORDER',0).")+1 as MAXORDER
							from FLC_PAGE_COMPONENT_ITEMS a right join FLC_PAGE_COMPONENT b on a.COMPONENTID = b.COMPONENTID
							where b.PAGEID = ".$_POST['code']."
							group by b.COMPONENTID, b.COMPONENTTYPE, b.COMPONENTBINDINGTYPE, b.COMPONENTBINDINGSOURCE
							order by b.COMPONENTID, b.COMPONENTTYPE";
	$getParentComponentRs = $myQuery->query($getParentComponent,'SELECT','NAME');
	$getParentComponentRsCount = count($getParentComponentRs);
}//eof if

?>

<script language="javascript">
//enable/disable item base on value
function chartLayout(val) {
	if(val=="chart") {
		jQuery('.chart_no_need').hide();
	}
}

function codeDropDown(elem, items)
{
	//related item to enable/disable
	var relatedItems = items.split('|');
	var relatedItemsCount = relatedItems.length;

	//if have value
	if(elem.value != '')
	{
		//loop on count of related items
		for(x=0; x<relatedItemsCount; x++)
		{
			//enable
			document.getElementById(relatedItems[x]).disabled = false;
		}//eof for
	}//eof if
	else
	{
		//loop on count of related items
		for(x=0; x<relatedItemsCount; x++)
		{
			//disable
			document.getElementById(relatedItems[x]).disabled = true;
		}//eof for
	}//eof else
}//eof function

//hide/display block/field by item's parent component
function changeParentComponent(elem, componentId, componentIdExcept)
{
	//default component
	if(componentId == componentIdExcept)
		document.getElementById(elem).value = '<?php echo $showEditComponentItemRs[0]['ITEMORDER'];?>';
	else
	{
		<?php
		//loop on count of component
		for($x=0; $x<$getParentComponentRsCount; $x++)
		{
			if($x==0){?>if<?php }else{?>else if<?php }?>(componentId == <?php echo $getParentComponentRs[$x]['COMPONENTID'];?>)
				document.getElementById(elem).value = '<?php echo $getParentComponentRs[$x]['MAXORDER'];?>';
			<?php
		}//eof for
		?>
	}//eof else

	//=== hide/display listing block by component type
	if(!componentId) document.getElementById('listingTbody').style.display = 'none';
	<?php
	//loop on count of component
	for($x=0; $x<$getParentComponentRsCount; $x++)
	{
		//component type is tabular or report
		if($getParentComponentRs[$x]['COMPONENTTYPE'] == 'tabular' || $getParentComponentRs[$x]['COMPONENTTYPE'] == 'report')
		{
			?>else if(componentId == <?php echo $getParentComponentRs[$x]['COMPONENTID'];?>) document.getElementById('listingTbody').style.display = '';<?php
		}//eof if
		else
		{
			?>else if(componentId == <?php echo $getParentComponentRs[$x]['COMPONENTID'];?>) document.getElementById('listingTbody').style.display = 'none';<?php
		}//eof else
	}//eof for
	?>

	//=== hide/display database mapping block by component binding type and source existance
	if(!componentId || componentId == 0) document.getElementById('databaseMappingTr').style.display = 'none';
	<?php
	//loop on count of component
	for($x=0; $x<$getParentComponentRsCount; $x++)
	{
		//if binding type and source exist
		if($getParentComponentRs[$x]['COMPONENTBINDINGTYPE'] && $getParentComponentRs[$x]['COMPONENTBINDINGSOURCE'])
		{
			?>else if(componentId == <?php echo $getParentComponentRs[$x]['COMPONENTID'];?>) document.getElementById('databaseMappingTr').style.display = '';<?php
		}//eof if
		else
		{
			?>else if(componentId == <?php echo $getParentComponentRs[$x]['COMPONENTID'];?>) document.getElementById('databaseMappingTr').style.display = 'none';<?php
		}//eof else
	}//eof for
	?>
}//eof function
function checkUniqueName(type,elem,mode,pageID)
{
	//mode : new/edit
	//type: page/component/item

	var name = elem.value;

	if(type == 'page')
	{
		if(mode == 'new')
		{
			jQuery.get('ajax_editor.php?checkUnique=true&uniqueType='+type+'&mode='+mode+'&name='+name+'&caller='+elem.id,
				function(data)
				{
					jQuery(document).append(data);
				}
			);
		}
		else if(mode == 'edit')
		{
			var hiddenVal = jQuery('#hidden_'+elem.id).val();
			jQuery.get('ajax_editor.php?checkUnique=true&uniqueType='+type+'&mode='+mode+'&name='+name+'&caller='+elem.id+'&hiddencallerval='+hiddenVal,
				function(data)
				{
					jQuery(document).append(data);
				}
			);
		}
	}
	else if(type == 'component' || type == 'item')
	{
		if(mode == 'new')
		{
			jQuery.get('ajax_editor.php?checkUnique=true&uniqueType='+type+'&mode='+mode+'&name='+name+'&pageid='+pageID+'&caller='+elem.id,
				function(data)
				{
					jQuery(document).append(data);
				}
			);
		}
		else if(mode == 'edit')
		{
			var hiddenVal = jQuery('#hidden_'+elem.id).val();
			jQuery.get('ajax_editor.php?checkUnique=true&uniqueType='+type+'&mode='+mode+'&name='+name+'&pageid='+pageID+'&caller='+elem.id+'&hiddencallerval='+hiddenVal,
				function(data)
				{
					jQuery(document).append(data);
				}
			);
		}
	}
}
</script>
<script language="javascript" src="js/editor.js"></script>
<script language="javascript" src="tools/jscolor/jscolor.js"></script>

<div id="breadcrumbs">System Administrator / Configuration / Page Editor</div>
<h1>Page Editor </h1>
<?php
if($insertCatRs)
{
	//notification
	showNotificationInfo('New page has been added');
}
else if($duplicatePageRs)
{
	//notification
	showNotificationInfo('Page has been duplicated');
}
else if($updateCatRs)
{
	//notification
	showNotificationInfo('Page has been updated');
}
else if($deletePageRs)
{
	//notification
	showNotificationInfo('Page has been deleted');
}
else if($newComponentRs)
{
	//notification
	showNotificationInfo('New component has been added');
}
else if($duplicateComponentRs)
{
	//notification
	showNotificationInfo('Component has been duplicated');
}
else if($updateRefRs)
{
	//notification
	showNotificationInfo('Component has been updated');
}
else if($deleteComponentRs)
{
	//notification
	showNotificationInfo('Component has been deleted');
}
else if($updateOrderComponentFlag)
{
	//notification
	showNotificationInfo('Component ordering is now optimized');
}
else if($newItemRs)
{
	//notification
	showNotificationInfo('New component item has been added');
}
else if($duplicateItemRs)
{
	//notification
	showNotificationInfo('Component item has been duplicated');
}
else if($updateComponentItemRs)
{
	//notification
	showNotificationInfo('Component item has been updated');
}
else if($deleteComponentItemRs)
{
	//notification
	showNotificationInfo('Component item has been deleted');
}
else if($updateOrderItemFlag)
{
	//notification
	showNotificationInfo('Component item ordering is now optimized');
}
?>

<form method="post" name="form1">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Page List </th>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Page Search : </td>
      <td><input name="pageSearch" type="text" id="pageSearch" size="50" class="inputInput" value="<?php echo $_POST['pageSearch']?>" onkeyup="ajaxUpdatePageSelector('page','pageSelectorDropdown',this.value)" /></td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Page : </td>
      <td>
        <div id="pageSelectorDropdown">
          <select name="code" class="inputList" id="code" onchange="codeDropDown(this, 'showScreen|duplicatePage|modifyCategory|deleteCategory');">
            <option value="">&lt; Select Page &gt;</option>
            <?php for($x=0; $x<$generalRsCount; $x++){?>
            <option value="<?php echo $generalRs[$x]['PAGEID'];?>" <?php if($_POST['code'] == $generalRs[$x]['PAGEID']) echo "selected";?>>[<?php echo $generalRs[$x]['PAGEID'];?>] - <?php echo $generalRs[$x]['MENUTITLE'];?></option>
            <?php }?>
          </select>
          <input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Show List" <?php if(!$_POST['code']){?>disabled="disabled" <?php }?> />
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input name="duplicatePage"  id="duplicatePage"type="submit" class="inputButton" value="Duplicate Page" <?php if(!$_POST['code'] || isset($_POST['deleteCategory'])){ ?>disabled="disabled" <?php }?> />
        <input name="newCategory" type="submit" class="inputButton" value="New Page" />
        <input name="modifyCategory" id="modifyCategory" type="submit" class="inputButton" value="Update Page" <?php if(!$_POST['code'] || isset($_POST['deleteCategory'])){ ?>disabled="disabled" <?php }?> />
        <input name="deleteCategory" id="deleteCategory" type="submit" class="inputButton" value="Delete Page" <?php if(!$_POST['code'] || isset($_POST['deleteCategory'])){ ?>disabled="disabled" <?php }?> onclick="if(window.confirm('Are you sure you want to DELETE this page?\nThis will also delete ALL settings under this page')) {return true} else {return false}" />
      </td>
    </tr>
  </table>

  <?php if($_POST['newCategory']){?>
  <br>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">New Page </th>
    </tr>
	<tr>
      <td class="inputLabel">Menu Search : </td>
      <td><input name="menuSearch" type="text" id="menuSearch" size="50" class="inputInput" value="<?php echo $_POST['menuSearch']?>" onkeyup="ajaxUpdatePageSelector('menu','menuSelectorDropdown',this.value)" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Link to Menu : </td>
      <td>
	  <div id="menuSelectorDropdown">
	  <select name="newMenuID" class="inputList" id="newMenuID" onchange="document.getElementById('newPageBreadcrumbs').value=this.options[this.selectedIndex].innerHTML">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x<$getMenuRsCount; $x++){?>
          <option value="<?php echo $getMenuRs[$x]['MENUID'];?>"><?php echo $getMenuRs[$x]['MENUTITLE'];?></option>
          <?php } ?>
      </select>
	  </div></td>
    </tr>
	<tr>
      <td nowrap class="inputLabel">Name : </td>
      <td>
      	<input name="newPageName" type="text" class="inputInput" id="newPageName" size="50" onchange="trim(this); checkUniqueName('page',this,'new')" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td nowrap class="inputLabel">Title : </td>
      <td>
      	<input name="newPageTitle" type="text" class="inputInput" id="newPageTitle" size="100" onkeyup="this.onchange();"
        	onchange="if(this.value){form1.saveScreenNew.disabled = false;}else{form1.saveScreenNew.disabled = true;}">
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Breadcrumbs : </td>
      <td><input name="newPageBreadcrumbs" type="text" class="inputInput" id="newPageBreadcrumbs" size="100"></td>
    </tr>
    <tr>
      <td class="inputLabel">Notes : </td>
      <td><textarea name="newPageNotes" cols="50" rows="3" class="inputInput" id="newPageNotes" ></textarea></td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input name="saveScreenNew" type="submit" class="inputButton" value="Save" disabled="disabled" />
        <input name="closeScreen" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
  <?php } ?>

  <?php if($_POST['modifyCategory']){?>
  <br>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Modify <?php if($duplicatePageRs){?>Duplicated <?php }?>Page </th>
    </tr>
	<tr>
      <td class="inputLabel">Menu Search : </td>
      <td><input name="menuSearch" type="text" id="menuSearch" size="50" class="inputInput" value="<?php echo $_POST['menuSearch']?>" onkeyup="ajaxUpdatePageSelector('menu','menuSelectorDropdown',this.value,'<?php echo $_POST['code'];?>')" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Link to Menu : </td>
      <td>
	  <div id="menuSelectorDropdown">
	  <select name="editMenuID" class="inputList" id="editMenuID" onchange="document.getElementById('editPageBreadcrumbs').value=this.options[this.selectedIndex].innerHTML">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x<$getMenuRsCount; $x++) { ?>
          <option value="<?php echo $getMenuRs[$x]['MENUID'];?>" <?php if($getPageInfoRs[0]['MENUID'] == $getMenuRs[$x]['MENUID']) echo "selected";?>><?php echo $getMenuRs[$x]['MENUTITLE'];?></option>
          <?php } ?>
      </select>
	  </div></td>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Name : </td>
      <td>
      	<input name="editPageName" type="text" class="inputInput" id="editPageName" size="50" value="<?php echo $getPageInfoRs[0]['PAGENAME'];?>" onchange="trim(this); checkUniqueName('page',this,'edit',<?php echo $_POST['code']?>)" />
      	<input name="hidden_editPageName" type="hidden" id="hidden_editPageName" value="<?php echo $getPageInfoRs[0]['PAGENAME'];?>" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Title : </td>
      <td><input name="editPageTitle" type="text" class="inputInput" id="editPageTitle" size="100" value="<?php echo $getPageInfoRs[0]['PAGETITLE'];?>" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Breadcrumbs : </td>
      <td><input name="editPageBreadcrumbs" type="text" class="inputInput" id="editPageBreadcrumbs" value="<?php echo $getPageInfoRs[0]['PAGEBREADCRUMBS'];?>" size="100" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Notes : </td>
      <td><textarea name="editPageNotes" cols="50" rows="3" class="inputInput" id="editPageNotes" ><?php echo $getPageInfoRs[0]['PAGENOTES'];?></textarea></td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
      	<input name="hiddenPageID" type="hidden" id="hiddenPageID" value="<?php echo $getPageInfoRs[0]['PAGEID'];?>" />
        <input name="saveScreenEdit" type="submit" class="inputButton" id="saveScreenEdit" value="Save" />
        <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>
  <?php } ?>

  <?php if($_POST['duplicatePage']){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Duplicate Page</th>
    </tr>
	<tr>
      <td class="inputLabel">Menu Search : </td>
      <td><input name="menuSearch" type="text" id="menuSearch" size="50" class="inputInput" value="<?php echo $_POST['menuSearch']?>" onkeyup="ajaxUpdatePageSelector('menu','menuSelectorDropdown',this.value)" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Link to Menu : </td>
      <td>
        <div id="menuSelectorDropdown">
	      <select name="newMenuID" class="inputList" id="newMenuID">
            <option value="">&nbsp;</option>
            <?php for($x=0; $x<$getMenuRsCount; $x++){?>
            <option value="<?php echo $getMenuRs[$x]['MENUID'];?>"><?php echo $getMenuRs[$x]['MENUTITLE'];?></option>
            <?php }?>
          </select>
        </div>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Duplicate Page From :</td>
      <td>
        <select name="duplicatePageFrom" class="inputList" id="duplicatePageFrom">
          <?php for($x=0; $x<$generalRsCount; $x++){?>
          <option value="<?php echo $generalRs[$x]['PAGEID'];?>" <?php if($_POST['code'] == $generalRs[$x]['PAGEID']) echo "selected";?> ><?php echo $generalRs[$x]['MENUTITLE'];?></option>
          <?php }?>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input name="savePageDuplicate" id="savePageDuplicate" type="submit" class="inputButton" value="Save" />
        <input name="closeScreen" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
  <br />
  <?php }?>

  <?php if($_POST['newComponent']){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">New Component </th>
    </tr>
    <tr>
      <td class="inputLabel">Title : </td>
      <td>
      	<input name="newComponentTitle" type="text" class="inputInput" id="newComponentTitle" style="width:99%" />
        <script>document.getElementById('newComponentTitle').focus();</script>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name : </td>
      <td>
      	<input name="newComponentName" type="text" class="inputInput" id="newComponentName" size="50" onchange="trim(this); checkUniqueName('component',this,'new',<?php echo $_POST['code']?>)" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<select name="newComponentType" id="newComponentType" class="inputList" onchange="codeDropDown(this, 'saveScreenRefNew'); changeComponentType(this.value); if(this.value == 'query' || this.value == 'query_2_col' || this.value == 'report' || this.value == 'search_constraint'){document.getElementById('typeQueryRow').style.display = '';} else {document.getElementById('typeQueryRow').style.display = 'none';}">
          <option value="custom">Custom</option>
		  <option value="form_1_col" selected>Form 1 Column</option>
          <option value="form_2_col">Form 2 Column</option>
          <option value="iframe">Iframe</option>
          <option value="query">Query</option>
          <option value="query_2_col">Query 2 Column</option>
          <option value="report">Report</option>
          <option value="search_constraint">Search Constraint</option>
          <option value="tabular">Tabular</option>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Order : </td>
      <td><label>
        <input name="orderOption" type="radio" checked="checked" onclick="swapItemDisplay('newComponentOrderText', 'newComponentOrderCombo')" />
        At</label>
        <label>
        <input name="orderOption" type="radio" onclick="swapItemDisplay('newComponentOrderCombo', 'newComponentOrderText'); showComponent('<?php echo $_POST['code'];?>')" />
        Before</label>
        <label>
        <input name="orderOption" type="radio" value="++" onclick="swapItemDisplay('newComponentOrderCombo', 'newComponentOrderText'); showComponent('<?php echo $_POST['code'];?>')" />
        After</label>
        <br />
        <input name="newComponentOrder" type="text" class="inputInput" id="newComponentOrderText" size="5"  value="<?php echo $getOrderRs;?>" />
        <span id="hideEditorList"></span></td>
    </tr>
    <tr>
      <td class="inputLabel">Status : </td>
      <td><select name="newComponentStatus" id="newComponentStatus" class="inputList" >
          <option value="1" selected="selected">Active</option>
          <option value="0">Not Active</option>
        </select></td>
    </tr>
    <tr id="typeQueryRow" style="display:none;">
      <td class="inputLabel">Query : </td>
      <td><textarea name="newTypeQuery" style="width:99%" rows="10" class="inputInput" id="newTypeQuery"></textarea>
        <br />
		Database : <?php echo getListId('newQueryType'); ?>
        <br />
        <label><input name="newQueryLimit" type="checkbox" id="newQueryLimit" value="1" />Unlimited</label></td>
    </tr>
    <tbody id="dataBindingTbody">
    <tr>
      <td class="subHead">DATA BINDING </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
          <select name="newDataBindingType" id="newDataBindingType" class="inputList" onchange="
          switch(document.getElementById('newDataBindingType').value)
          {
              case 'table'	: swapItemDisplay('newDataSourceTable','newDataSourceNone|newDataSourceView')
                break;

              case 'view'	: swapItemDisplay('newDataSourceView','newDataSourceNone|newDataSourceTable')
                break;

              default		: swapItemDisplay('newDataSourceNone','newDataSourceView|newDataSourceTable')
                break;
          };">
              <option value="">&nbsp;</option>
              <option value="table">Table</option>
              <option value="view">View</option>
            </select>
        </td>
    </tr>
    <tr>
      <td class="inputLabel">Source : </td>
      <td><select name="newDataSource" id="newDataSourceNone" class="inputList" >
          <option value="">&nbsp;</option>
        </select>
        <select name="newDataSource" id="newDataSourceTable" class="inputList" style="display:none">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($getTableRs); $x++) {?>
          <option value="<?php echo $getTableRs[$x]['TABLE_NAME']?>"><?php echo $getTableRs[$x]['TABLE_NAME']?></option>
          <?php }?>
        </select>
        <select name="newDataSource" id="newDataSourceView" class="inputList"  style="display:none">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($getViewRs); $x++) { ?>
          <option value="<?php echo $getViewRs[$x]['VIEW_NAME']?>"><?php echo $getViewRs[$x]['VIEW_NAME']?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
    </tbody>
    <tbody id="prePostTbody">
    <tr>
      <td class="subHead">PRE/POST PROCESS </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Pre-process Type : </td>
      <td><select name="newComponentPreProcess" class="inputList" id="newComponentPreProcess" >
          <option value="">&nbsp;</option>
          <option value="select">SELECT</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Post-process Type : </td>
      <td><select name="newComponentPostProcess" class="inputList" id="newComponentPostProcess" onchange="if(this.selectedIndex > 0) document.getElementById('newComponentPostScript').disabled = false; else document.getElementById('newComponentPostScript').disabled = true;">
          <option value="">&nbsp;</option>
          <option value="insert">INSERT</option>
          <option value="update">UPDATE</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Post-Process Notification : </td>
      <td><textarea name="newComponentPostScript" style="width:99%" rows="3" class="inputInput" id="newComponentPostScript" ></textarea></td>
    </tr>
    </tbody>
	<tbody id="enableCollapse">
	<tr>
		<td class="inputLabel">Enable Collapse : </td>
		<td><label><input name="enableCollapse" id="enableCollapse" type="checkbox" value="1" />Yes</label></td>
	</tr>
	<tr>
		<td class="inputLabel">Collapse By Default : </td>
		<td>
			<label>
				<input name="collapseDefault" id="collapseDefault" type="checkbox" value="1" />Yes</label></td>
			</label>
		</td>
	</tr>
	</tbody>
    <tbody id="listingTbody" style="display:none;">
    <tr>
      <td class="subHead">REPORT / TABULAR </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">No. of Rows / Page :</td>
      <td><input name="newTabularDefaultRowNo" type="text" class="inputInput" id="newTabularDefaultRowNo" size="4" maxlength="3" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Max No. of Rows Fetched :</td>
      <td><input name="newTabularMaxFetch" type="text" class="inputInput" id="newTabularMaxFetch" size="4" maxlength="7" /> If empty, will revert to default limit.</td>
    </tr>
	<tr>
      <td class="inputLabel">Add Row  : </td>
      <td>
      	<label><input name="newAddRow" id="newAddRow" type="checkbox" value="1" />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Add Row Disabled  : </td>
	  <td>
      	<label><input name="newAddRowDisabled" id="newAddRowDisabled" type="checkbox" value="1" />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Add Row Javascript  : </td>
	  <td><textarea name="newAddJavascript" style="width:99%" rows="5" class="inputInput" id="newAddJavascript" ></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Delete Row  : </td>
      <td>
      	<label><input name="newDeleteRow" id="newDeleteRow" type="checkbox" value="1" />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Delete Row Disabled  : </td>
	  <td>
	  	<label><input name="newDeleteRowDisabled" id="newDeleteRowDisabled" type="checkbox" value="1" />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Delete Row Javascript  : </td>
	  <td><textarea name="newDeleteJavascript" style="width:99%" rows="5" class="inputInput" id="newDeleteJavascript" ></textarea></td>
    </tr>
    <!--
	<tr>
		<td class="inputLabel">Searchable Data: </td>
		<td><label><input name="searchableData" id="searchableData" type="checkbox" value="1" />Yes</label></td>
	</tr>
	-->
    </tbody>
	<tbody id="">
	<tr>
      <td class="inputLabel">Component Position : </td>
      <td><select name="newComponentPosition" class="inputList" id="newComponentPosition">
          <option value="">&nbsp;</option>
          <option value="center">Main Container</option>
          <option value="left">Left Side</option>
        </select></td>
    </tr>
	</tbody>
	<tbody id="">
	<tr>
      <td class="inputLabel">Component Search : </td>
      <td><select name="newComponentSearch" class="inputList" id="newComponentSearch">
          <option value="">&nbsp;</option>
          <option value="0">Disabled</option>
          <option value="1">Enabled</option>
        </select></td>
    </tr>
	</tbody>
    <tbody id="customTbody" style="display:none;">
    <tr>
      <td class="subHead">CUSTOM / IFRAME</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Path : </td>
      <td>
      	<input name="newComponentPath" type="text" class="inputInput" id="newComponentPath" style="width:99%" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    </tbody>
    <tbody id="masterDetailTbody" style="display:none;">
    <tr>
      <td class="subHead">MASTER DETAIL</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Master Component : </td>
      <td><select name="newMasterID" class="inputList" id="newMasterID" >
          <option value="0">&nbsp;</option>
          <?php for($x=0; $x<$getComponentRsCount; $x++) { ?>
          <option value="<?php echo $getComponentRs[$x]['COMPONENTID'];?>"><?php echo $getComponentRs[$x]['COMPONENTNAME'];?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
    </tbody>
  </table>
  <br />

  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Component Permission</th>
    </tr>
    <tr>
      <td class="inputLabel">Access List : </td>
      <td><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><div align="center"><strong>List of unselected user group</strong></div></td>
            <td>&nbsp;</td>
            <td><div align="center"><strong>List of selected user group</strong></div></td>
          </tr>
          <tr>
            <td>
              <select style="width:250px;" name="nonSelectedGroup" size="10" multiple class="inputList" id="nonSelectedGroup" >
                <?php for($x=0; $x<$groupListNonSelectedCount; $x++){?>
                <option value="<?php echo $groupListNonSelected[$x][0]?>"><?php echo $groupListNonSelected[$x][1];?></option>
                <?php }?>
              </select>
            </td>
            <td width="35">
              <div align="center">
                <input name="newMoveLTR" type="button" class="inputButton" id="newMoveLTR" value="&gt;" style="margin-bottom:2px;" onclick="moveoutid('nonSelectedGroup','selectedGroup');" />
                <br>
                <input name="newMoveRTL" type="button" class="inputButton" id="newMoveRTL" value="&lt;" style="margin-bottom:2px;" onclick="moveinid('nonSelectedGroup','selectedGroup');" />
                <br>
                <input name="newMoveAllLTR" type="button" class="inputButton" id="newMoveAllLTR" value="&gt;&gt;" style="margin-bottom:2px;" onclick="listBoxSelectall('nonSelectedGroup'); moveoutid('nonSelectedGroup','selectedGroup');" />
                <br>
                <input name="newMoveAllRTL" type="button" class="inputButton" id="newMoveAllRTL" value="&lt;&lt;" style="margin-bottom:2px;" onclick="listBoxSelectall('selectedGroup'); moveinid('nonSelectedGroup','selectedGroupEdit');" />
                <br>
                <input name="newSort" type="button" class="inputButton" id="newSort" value="a-z" style="margin-bottom:2px;" onclick="sortListBox('selectedGroup');sortListBox('nonSelectedGroup');" />
              </div>
            </td>
            <td>
              <select style="width:250px;" name="selectedGroup[]" size="10" multiple class="inputList" id="selectedGroup">
              </select>
            </td>
          </tr>
        </table>
        <?php if(!COMPONENT_PERMISSION_ENABLED){?>
		<label class="labelNote">Note: Usage of component permission is disabled. Access permission set here will be ignored.</label>
		<?php }?>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input name="saveScreenRefNew" type="submit" class="inputButton" id="saveScreenRefNew" value="Save" onclick="listBoxSelectall('selectedGroup');" />
        <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>
  <?php } ?>
  <?php if($_POST['editComponent']){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Modify <?php if($duplicateComponentRs){?>Duplicated <?php }?>Component </th>
    </tr>
    <tr>
      <td class="inputLabel">Title : </td>
      <td>
      	<input name="editComponentTitle" type="text" class="inputInput" id="editComponentTitle" value="<?php echo $getEditComponentRs[0]['COMPONENTTITLE'] ?>" style="width:99%" />
        <script>document.getElementById('editComponentTitle').focus();</script>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name : </td>
      <td>
      	<input name="editComponentName" type="text" class="inputInput" id="editComponentName" value="<?php echo $getEditComponentRs[0]['COMPONENTNAME'] ?>" size="50" onchange="trim(this); checkUniqueName('component',this,'edit',<?php echo $_POST['code']?>)" />
      	<input name="hidden_editComponentName" type="hidden" id="hidden_editComponentName" value="<?php echo $getEditComponentRs[0]['COMPONENTNAME'];?>" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<select name="editComponentType" id="editComponentType" class="inputList" onchange="codeDropDown(this, 'saveScreenRefEdit'); changeComponentType(this.value); if(this.value == 'query' || this.value == 'query_2_col' || this.value == 'report' || this.value == 'search_constraint'){document.getElementById('typeQueryRow').style.display = '';} else {document.getElementById('typeQueryRow').style.display = 'none';}">
          <option value="custom" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'custom') { ?>selected<?php }?>>Custom</option>
		  <option value="form_1_col" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'form_1_col') { ?>selected<?php }?>>Form 1 Column</option>
          <option value="form_2_col" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'form_2_col') { ?>selected<?php }?>>Form 2 Column</option>
          <option value="iframe" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'iframe') { ?>selected<?php }?>>Iframe</option>
          <option value="query" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'query') { ?>selected<?php }?>>Query</option>
          <option value="query_2_col" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'query_2_col') { ?>selected<?php }?>>Query 2 Column</option>
          <option value="report" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'report') { ?>selected<?php }?>>Report</option>
          <option value="search_constraint" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'search_constraint') { ?>selected<?php }?>>Search Constraint</option>
          <option value="tabular" <?php if($getEditComponentRs[0]['COMPONENTTYPE'] == 'tabular') { ?>selected<?php }?>>Tabular</option>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Order :</td>
      <td>
      	<label>
        <input name="orderOption" type="radio" checked="checked" onclick="swapItemDisplay('editComponentOrderText', 'editComponentOrderCombo')" />
        At</label>
        <label>
        <input name="orderOption" type="radio" onclick="swapItemDisplay('editComponentOrderCombo', 'editComponentOrderText'); showComponent('<?php echo $_POST['code'];?>', '<?php echo $_POST['hiddenComponentID'];?>')" />
        Before</label>
        <label>
        <input name="orderOption" type="radio" value="++" onclick="swapItemDisplay('editComponentOrderCombo', 'editComponentOrderText'); showComponent('<?php echo $_POST['code'];?>', '<?php echo $_POST['hiddenComponentID'];?>')" />
        After</label>
        <br />
        <input name="editComponentOrder" type="text" class="inputInput" id="editComponentOrderText" size="5" value="<?php echo $getEditComponentRs[0]['COMPONENTORDER'] ?>" />
        <span id="hideEditorList"></span>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Status : </td>
      <td><select name="editComponentStatus" id="editComponentStatus" class="inputList">
          <option value="1" <?php if($getEditComponentRs[0]['COMPONENTSTATUS'] == 1) { ?> selected<?php }?>>Active</option>
          <option value="0" <?php if($getEditComponentRs[0]['COMPONENTSTATUS'] == 0) { ?> selected<?php }?>>Not Active</option>
        </select></td>
    </tr>
    <tr id="typeQueryRow" <?php if($getEditComponentRs[0]['COMPONENTTYPE']!='search_constraint' && $getEditComponentRs[0]['COMPONENTTYPE']!='query' && $getEditComponentRs[0]['COMPONENTTYPE']!='query_2_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='report'){?> style="display:none;"<?php }?>>
      <td class="inputLabel">Query : </td>
      <td><textarea name="editTypeQuery" style="width:99%" rows="10" class="inputInput" id="editTypeQuery"><?php echo convertToDBQry(trim($getEditComponentRs[0]['COMPONENTTYPEQUERY']));?></textarea>
        <br />
		Database : <?php echo getListId('editQueryType',$getEditComponentRs[0]['COMPONENTQUERYDB']); ?>
        <br />
        <label><input name="editQueryLimit" type="checkbox" id="editQueryLimit" value="1" <?php if($getEditComponentRs[0]['COMPONENTQUERYUNLIMITED']){?> checked="checked"<?php }?> />Unlimited</label></td>
    </tr>
    <tbody id="dataBindingTbody"<?php if($getEditComponentRs[0]['COMPONENTTYPE']!='form_1_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='form_2_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='query' && $getEditComponentRs[0]['COMPONENTTYPE']!='query_2_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='report' && $getEditComponentRs[0]['COMPONENTTYPE']!='tabular'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="subHead">DATA BINDING </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td><select name="editDataBindingType" id="editDataBindingType" class="inputList" onchange="
	  switch(document.getElementById('editDataBindingType').value)
	  {
		  case 'table'		: swapItemDisplay('editDataSourceTable','editDataSourceNone|editDataSourceView')
			break;

		  case 'view'		: swapItemDisplay('editDataSourceView','editDataSourceNone|editDataSourceTable')
			break;

		  default			: swapItemDisplay('editDataSourceNone','editDataSourceView|editDataSourceTable')
		  	break;
	  };">
          <option value="">&nbsp;</option>
          <option <?php if($getEditComponentRs[0]['COMPONENTBINDINGTYPE'] == 'table') { ?>selected<?php }?> value="table">Table</option>
          <option <?php if($getEditComponentRs[0]['COMPONENTBINDINGTYPE'] == 'view') { ?>selected<?php }?> value="view">View</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Source : </td>
      <td><select name="editDataSource" id="editDataSourceNone" class="inputList" <?php if($getEditComponentRs[0]['COMPONENTBINDINGTYPE'] != '') { ?> style="display:none" disabled="disabled"<?php }?>>
          <option value="">&nbsp;</option>
        </select>
        <select name="editDataSource" id="editDataSourceTable" class="inputList" <?php if($getEditComponentRs[0]['COMPONENTBINDINGTYPE'] != 'table') { ?> style="display:none" disabled="disabled"<?php }?>>
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($getTableRs); $x++) { ?>
          <option value="<?php echo $getTableRs[$x]['TABLE_NAME']?>" <?php if($getEditComponentRs[0]['COMPONENTBINDINGSOURCE'] == $getTableRs[$x]['TABLE_NAME']){ ?>selected<?php }?>><?php echo $getTableRs[$x]['TABLE_NAME']?></option>
          <?php } ?>
        </select>
        <select name="editDataSource" id="editDataSourceView" class="inputList" <?php if($getEditComponentRs[0]['COMPONENTBINDINGTYPE'] != 'query') { ?> style="display:none" disabled="disabled"<?php }?>>
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($getViewRs); $x++) { ?>
          <option value="<?php echo $getViewRs[$x]['VIEW_NAME']?>" <?php if($getEditComponentRs[0]['COMPONENTBINDINGSOURCE'] == $getTableRs[$x]['VIEW_NAME']){ ?>selected<?php }?>><?php echo $getViewRs[$x]['VIEW_NAME']?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
    </tbody>
    <tbody id="prePostTbody"<?php if($getEditComponentRs[0]['COMPONENTTYPE']!='form_1_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='form_2_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='query' && $getEditComponentRs[0]['COMPONENTTYPE']!='query_2_col' && $getEditComponentRs[0]['COMPONENTTYPE']!='report' && $getEditComponentRs[0]['COMPONENTTYPE']!='tabular'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="subHead">PRE/POST PROCESS </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Pre-process Type : </td>
      <td><select name="editComponentPreProcess" class="inputList" id="editComponentPreProcess" >
          <option value="">&nbsp;</option>
          <option value="select" <?php if($getEditComponentRs[0]['COMPONENTPREPROCESS'] == 'select'){?> selected<?php }?>>SELECT</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Post-process Type : </td>
      <td><select name="editComponentPostProcess" class="inputList" id="editComponentPostProcess" onchange="if(this.selectedIndex &gt; 0) document.getElementById('editComponentPostScript').disabled = false; else document.getElementById('editComponentPostScript').disabled = true;">
          <option value="">&nbsp;</option>
          <option value="insert" <?php if($getEditComponentRs[0]['COMPONENTPOSTPROCESS'] == 'insert'){?> selected<?php }?>>INSERT</option>
          <option value="update" <?php if($getEditComponentRs[0]['COMPONENTPOSTPROCESS'] == 'update'){?> selected<?php }?>>UPDATE</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Post-Process Notification : </td>
      <td><textarea name="editComponentPostScript" style="width:99%" rows="3" class="inputInput" id="editComponentPostScript" ><?php echo $getEditComponentRs[0]['COMPONENTPOSTSCRIPT'];?></textarea></td>
    </tr>
	<tbody id="enableCollapse">
	<tr>
		<td class="inputLabel">Enable Collapse : </td>
		<td>
			<label>
				<input name="enableCollapse" id="enableCollapse" type="checkbox" value="1"<?php if($getEditComponentRs[0]['COMPONENTCOLLAPSE'] == '1'){ ?> checked="checked"<?php }?> />Yes</label>
			</label>
		</td>
	</tr>
	<tr>
		<td class="inputLabel">Collapse By Default : </td>
		<td>
			<label>
				<input name="collapseDefault" id="collapseDefault" type="checkbox" value="1"<?php if($getEditComponentRs[0]['COMPONENTCOLLAPSEDEFAULT'] == '1'){ ?> checked="checked"<?php }?> />Yes</label>
			</label>
		</td>
	</tr>
	</tbody>
    <tbody id="listingTbody" <?php if($getEditComponentRs[0]['COMPONENTTYPE']!='report'&&$getEditComponentRs[0]['COMPONENTTYPE']!='tabular'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="subHead">REPORT / TABULAR </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">No. of Rows / Page :</td>
      <td><input name="editTabularDefaultRowNo" type="text" class="inputInput" id="editTabularDefaultRowNo" value="<?php echo $getEditComponentRs[0]['COMPONENTABULARDEFAULTROWNO'] ?>" size="2" maxlength="3" /></td>
    </tr>
	<tr>
      <td class="inputLabel">Max No. of Rows Fetched :</td>
      <td><input name="editTabularMaxFetch" type="text" class="inputInput" id="editTabularMaxFetch" size="4" maxlength="7" value="<?php echo $getEditComponentRs[0]['COMPONENTQUERYMAXFETCH'] ?>" /> If empty, will revert to default limit.</td>
    </tr>
	<tr>
      <td class="inputLabel">Add Row  : </td>
      <td>
      	<label><input name="editAddRow" id="editAddRow" type="checkbox" value="1" <?php if($getEditComponentRs[0]['COMPONENTADDROW']){?> checked="checked" <?php }?> />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Add Row Disabled  : </td>
	  <td>
      	<label><input name="editAddRowDisabled" id="editAddRowDisabled" type="checkbox" value="1" <?php if($getEditComponentRs[0]['COMPONENTADDROWDISABLED']){?> checked="checked" <?php }?> />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Add Row Javascript  : </td>
	  <td><textarea name="editAddJavascript" style="width:99%" rows="5" class="inputInput" id="editAddJavascript" ><?php echo convertToDBQry($getEditComponentRs[0]['COMPONENTADDROWJAVASCRIPT']);?></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Delete Row  : </td>
      <td>
      	<label><input name="editDeleteRow" id="editDeleteRow" type="checkbox" value="1" <?php if($getEditComponentRs[0]['COMPONENTDELETEROW']){?> checked="checked" <?php }?> />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Delete Row Disabled  : </td>
	  <td>
      	<label><input name="editDeleteRowDisabled" id="editDeleteRowDisabled" type="checkbox" value="1" <?php if($getEditComponentRs[0]['COMPONENTDELETEROWDISABLED']){?> checked="checked" <?php }?> />Yes</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Delete Row Javascript  : </td>
	  <td><textarea name="editDeleteJavascript" style="width:99%" rows="5" class="inputInput" id="editDeleteJavascript" ><?php echo convertToDBQry($getEditComponentRs[0]['COMPONENTDELETEROWJAVASCRIPT']);?></textarea></td>
    </tr>
    <!--
    <tr>
		<td class="inputLabel">Searchable Data: </td>
		<td><label><input name="searchableData" id="searchableData" type="checkbox" value="1"<?php if($getEditComponentRs[0]['SEARCHABLEDATA'] == '1'){ ?> checked="checked"<?php }?> />Yes</label>
	</tr>
	-->
	</tbody>
	<tbody id="">
    <tr>
      <td class="inputLabel">Component Position : </td>
      <td><select name="editComponentPosition" class="inputList" id="editComponentPosition">
          <option value="">&nbsp;</option>
          <option value="center" <?php if($getEditComponentRs[0]['COMPONENTPOSITION'] == 'center') echo 'selected'; ?> >
		  Main Container</option>
          <option value="left" <?php if($getEditComponentRs[0]['COMPONENTPOSITION'] == 'left') echo 'selected'; ?> >
		  Left Side</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Component Search : </td>
      <td><select name="editComponentSearch" class="inputList" id="editComponentSearch">
          <option value="">&nbsp;</option>
          <option value="0" <?php if($getEditComponentRs[0]['COMPONENTSEARCH'] == '0') echo 'selected'; ?>>Disabled</option>
          <option value="1" <?php if($getEditComponentRs[0]['COMPONENTSEARCH'] == '1') echo 'selected'; ?>>Enabled</option>
        </select></td>
    </tr>
	</tbody>
    <tbody id="customTbody" <?php if($getEditComponentRs[0]['COMPONENTTYPE']!='custom' && $getEditComponentRs[0]['COMPONENTTYPE']!='iframe'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="subHead">CUSTOM / IFRAME </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Path : </td>
      <td>
      	<input name="editComponentPath" type="text" class="inputInput" id="editComponentPath" style="width:99%" value="<?php echo $getEditComponentRs[0]['COMPONENTPATH'];?>" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    </tbody>
    <tbody id="masterDetailTbody" <?php if($getEditComponentRs[0]['COMPONENTTYPE']!='search_constraint'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="subHead">MASTER DETAIL</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Master Component : </td>
      <td><select name="editMasterID" class="inputList" id="editMasterID" >
          <option value="0">&nbsp;</option>
          <?php for($x=0; $x<$getComponentRsCount; $x++) { ?>
          <option value="<?php echo $getComponentRs[$x]['COMPONENTID']?>" <?php if($getEditComponentRs[0]['COMPONENTMASTERID'] == $getComponentRs[$x]['COMPONENTID']) echo 'selected';?>><?php echo $getComponentRs[$x]['COMPONENTNAME'];?></option>
          <?php } ?>
        </select></td>
    </tr>
    </tbody>
  </table>
  <br />

  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Component Permission</th>
    </tr>
    <tr>
      <td class="inputLabel">Access List : </td>
      <td>
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><div align="center"><strong>List of unselected user group</strong></div></td>
            <td>&nbsp;</td>
            <td><div align="center"><strong>List of selected user group</strong></div></td>
          </tr>
          <tr>
            <td>
              <select style="width:250px;" name="nonSelectedGroup" size="10" multiple class="inputList" id="nonSelectedGroup" >
                <?php for($x=0; $x<$groupListNonSelectedCount; $x++){?>
                <option value="<?php echo $groupListNonSelected[$x][0]?>" ><?php echo $groupListNonSelected[$x][1];?></option>
                <?php } ?>
              </select>
            </td>
            <td width="35">
              <div align="center">
                <input name="newMoveLTR" type="button" class="inputButton" id="newMoveLTR" value="&gt;" style="margin-bottom:2px;" onclick="moveoutid('nonSelectedGroup','selectedGroupEdit'); " />
                <br>
                <input name="newMoveRTL" type="button" class="inputButton" id="newMoveRTL" value="&lt;" style="margin-bottom:2px;" onclick="moveinid('nonSelectedGroup','selectedGroupEdit'); " />
                <br>
                <input name="newMoveAllLTR" type="button" class="inputButton" id="newMoveAllLTR" value="&gt;&gt;" style="margin-bottom:2px;" onclick="listBoxSelectall('nonSelectedGroup'); moveoutid('nonSelectedGroup','selectedGroupEdit');" />
                <br>
                <input name="newMoveAllRTL" type="button" class="inputButton" id="newMoveAllRTL" value="&lt;&lt;" style="margin-bottom:2px;" onclick="listBoxSelectall('selectedGroupEdit'); moveinid('nonSelectedGroup','selectedGroupEdit');" />
                <br>
                <input name="newSort" type="button" class="inputButton" id="newSort" value="a-z" style="margin-bottom:2px;" onclick="sortListBox('selectedGroupEdit');sortListBox('nonSelectedGroup');" />
              </div>
            </td>
            <td>
              <select style="width:250px;height:150px;" name="selectedGroupEdit[]" size="10" multiple class="inputList" id="selectedGroupEdit" >
                <?php for($x=0; $x<$groupListSelectedCount; $x++){?>
                <option value="<?php echo $groupListSelected[$x][0]?>"><?php echo $groupListSelected[$x][1];?></option>
                <?php }?>
              </select>
            </td>
          </tr>
        </table>
        <?php if(!COMPONENT_PERMISSION_ENABLED){?>
		<label class="labelNote">Note: Usage of component permission is disabled. Access permission set here will be ignored.</label>
		<?php }?>
      </td>
   </tr>
   <tr>
      <td colspan="2" class="contentButtonFooter">
         <input name="hiddenComponentID" type="hidden" id="hiddenComponentID" value="<?php echo $_POST['hiddenComponentID'];?>" />
         <input name="saveScreenRefEdit" type="submit" class="inputButton" id="saveScreenRefEdit" value="Save" onclick="listBoxSelectall('selectedGroupEdit');" />
         <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>
  <?php }?>

  <?php if($_POST['newItem']){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">New Component Item </th>
    </tr>
    <tr>
      <td class="inputLabel">Component : </td>
      <td>
      	<select name="componentID" class="inputList" id="componentID" onchange="changeParentComponent('newItemOrderText', this.value); swapItemDisplay('newItemOrderText', 'newItemOrderCombo'); document.getElementById('orderOptionStart').checked=true; showDatabase(document.getElementById('componentID').value);">
          <option value="0">&nbsp;</option>
          <?php for($x=0; $x<$getComponentRsCount; $x++) { ?>
          <option value="<?php echo $getComponentRs[$x]['COMPONENTID'];?>">[<?php echo $getComponentRs[$x]['COMPONENTNAME'];?>] <?php echo $getComponentRs[$x]['COMPONENTTITLE'];?></option>
          <?php } ?>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Title : </td>
      <td>
      	<input name="newItemTitle" type="text" class="inputInput" id="newItemTitle" size="100" />
        <script>document.getElementById('newItemTitle').focus();</script>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name : </td>
      <td>
      	<input name="newItemName" type="text" class="inputInput" id="newItemName" size="50" onchange="trim(this); checkUniqueName('item',this,'new',<?php echo $_POST['code']?>)" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<select name="newItemType" class="inputList" id="newItemType" onchange="codeDropDown(this, 'saveScreenItemNew'); changeItemType(this.value); chartLayout(this.value);">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($itemTypeRs); $x++) { ?>
          <option value="<?php echo $itemTypeRs[$x]['REFERENCECODE'];?>" ><?php echo $itemTypeRs[$x]['DESCRIPTION2'];?></option>
          <?php } ?>
          <option value="plugin">Plugin</option>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr id="row_itemMapping" style="display:none">
      <td class="inputLabel">Plugin Name : </td>
      <td>
      	<select name="itemMapping" class="inputList" id="itemMapping">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($pluginNameRs); $x++){?>
          <option value="<?php echo $pluginNameRs[$x]['EXT_NAME'];?>"><?php echo $pluginNameRs[$x]['EXT_TITLE'];?></option>
          <?php }?>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Order : </td>
      <td><label>
        <input name="orderOption" id="orderOptionStart" type="radio" checked="checked" onclick="swapItemDisplay('newItemOrderText', 'newItemOrderCombo')" />
        At</label>
        <label>
        <input name="orderOption" type="radio" onclick="if(form1.componentID.value>0){swapItemDisplay('newItemOrderCombo', 'newItemOrderText');  showComponentItem('<?php echo $_POST['code'];?>', form1.componentID.value)}" />
        Before</label>
        <label>
        <input name="orderOption" type="radio" value="++" onclick="if(form1.componentID.value>0){swapItemDisplay('newItemOrderCombo', 'newItemOrderText'); showComponentItem('<?php echo $_POST['code'];?>', form1.componentID.value)}" />
        After</label>
        <br />
        <input name="newItemOrder" id="newItemOrderText" type="text" class="inputInput" size="5" />
        <span id="hideEditorList"></span></td>
    </tr>
    <tr id="row_itemDefault" class="chart_no_need">
      <td class="inputLabel">Default Value : </td>
      <td>
		<div id="newItemDefaultValueTextDiv">
	  	<input name="newItemDefaultValue" type="text" class="inputInput" id="newItemDefaultValueText" size="70"  />
		<br /><label class="labelNote">Note: For URL, please fill up Default Value field with the URL</label>
		</div>
		<div id="newItemDefaultValueQueryDiv" style="display:none">
		<textarea name="newItemDefaultValue" cols="100" rows="10" class="inputInput" id="newItemDefaultValueQuery" disabled="disabled"></textarea>
		<br /><label class="labelNote">Note: For URL, please fill up Default Value field with the URL</label>
		<br /><br />
		Database : <?php echo getListId('newDefaultDB') ?>
		</div>
        <span id="lovEditorSpan" style="display:none;">
        	<input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=page&type=url&lovValue=newItemDefaultValueText', 1, 1, 600, 500);" />
        </span>
        <br /><br /><label><input name="newItemDefaultValueQuery" type="checkbox" value="1" onchange="if(this.checked==true){swapItemDisplay('newItemDefaultValueQueryDiv','newItemDefaultValueTextDiv');swapItemDisabled('newItemDefaultValueQuery','newItemDefaultValueText')} else {swapItemDisplay('newItemDefaultValueTextDiv','newItemDefaultValueQueryDiv');swapItemDisabled('newItemDefaultValueText','newItemDefaultValueQuery');}" />
		Use SQL</label>
	  </td>
    </tr>
    <tbody id="lookupTbody">
    <tr id="row_itemLookup">
      <td class="inputLabel">Lookup : </td>
      <td><label>
        <input name="newLookupType" type="radio" value="no" checked="checked" onclick="if(this.checked == true) { form1.newPredefinedLookup.disabled = true; form1.newAdvQueryLimit.disabled = true; form1.newAdvancedLookup.disabled = true}" />
        No Lookup</label>
        <label>
        <input type="radio" name="newLookupType" value="predefined" onclick="if(this.checked == true) { form1.newPredefinedLookup.disabled = false; form1.newAdvQueryLimit.disabled = false; form1.newAdvancedLookup.disabled = true }" />
        Predefined</label>
        <label>
        <input type="radio" name="newLookupType" value="advanced" onclick="if(this.checked == true) { form1.newAdvancedLookup.disabled = false; form1.newAdvQueryLimit.disabled = false; form1.newPredefinedLookup.disabled = true }" />
        Advanced</label>      </td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Predefined Lookup : </td>
      <td><select name="newPredefinedLookup" class="inputList" id="newPredefinedLookup" disabled="disabled">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x<$predefinedRsCount; $x++) { ?>
          <option value="<?php echo $predefinedRs[$x]['DESCRIPTION1'];?>" ><?php echo $predefinedRs[$x]['DESCRIPTION1'];?></option>
          <?php } ?>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Advanced Lookup : </td>
      <td>
          <textarea name="newAdvancedLookup" cols="100" rows="10" class="inputInput" id="newAdvancedLookup" disabled="disabled" ></textarea>
          <br /><label class="labelNote">Note: For advanced lookup, rename column as &quot;flc_id&quot; for value and &quot;flc_name&quot; for displayed name</label>
		  <br /><br />  Database : <?php echo getListId('newAdvanceDB') ?><br />

          <br /><label><input name="newAdvQueryLimit" type="checkbox" id="newAdvQueryLimit" value="1" disabled="disabled" />Unlimited</label>
      </td>
    </tr>
    </tbody>
    <tr id="row_itemHints" class="chart_no_need">
      <td class="inputLabel">Hints : </td>
      <td><textarea name="newItemHints" cols="100" rows="3" class="inputInput" id="newItemHints"></textarea></td>
    </tr>
    <tr id="row_itemPlaceholder" class="chart_no_need">
      <td class="inputLabel">Placeholder : </td>
      <td><textarea name="newItemPlaceholder" cols="100" rows="3" class="inputInput" id="newItemPlaceholder"></textarea></td>
    </tr>
    <tr id="row_itemNotes">
      <td class="inputLabel">Notes : </td>
      <td><textarea name="newItemNotes" cols="100" rows="3" class="inputInput" id="newItemNotes"></textarea></td>
    </tr>
    <tr id="row_itemStatus">
      <td class="inputLabel">Status : </td>
      <td><select name="newItemStatus" id="newItemStatus" class="inputList" >
          <option value="1" selected="selected">Active</option>
          <option value="0">Not Active</option>
        </select></td>
    </tr>
    <tbody id="frequentTbody" class="chart_no_need">
    <tr>
      <td class="subHead">FREQUENTLY USED </td>
      <td>&nbsp;</td>
    </tr>
    <tr id="databaseMappingTr" style="display:none">
      <td class="inputLabel">Database Mapping : </td>
      <td><span id="hideDatabaseList"></span></td>
    </tr>
    <tr id="row_itemLenRows">
      <td class="inputLabel">Length / Rows : </td>
      <td>
      	<input name="newItemInputLength" type="text" class="inputInput" id="newItemInputLength" size="5" /> /
        <input name="newItemTextAreaRows" type="text" class="inputInput" id="newItemTextAreaRows" size="5" />
        <br /><label class="labelNote">Note: Width / Height</label>
      </td>
    </tr>
    <tr id="row_itemTab">
      <td class="inputLabel">Tab Index : </td>
      <td><input name="newTabIndex" type="text" class="inputInput" id="newTabIndex" size="2" maxlength="3" /></td>
    </tr>
    <tr id="row_itemTextAlign">
      <td class="inputLabel">Text Alignment  : </td>
      <td><select name="newItemTextAlign" class="inputList" id="newItemTextAlign">
          <option value="left">Left</option>
          <option value="right">Right</option>
          <option value="center">Center</option>
        </select></td>
    </tr>
    <tr id="row_itemAppend">
      <td class="inputLabel">Append To Item Before : </td>
      <td><label><input name="newItemAppend" id="newItemAppend" type="checkbox" value="1" />Yes</label></td>
    </tr>
    </tbody>
    <tbody id="additionalTbody" style="display:none;">
          <tr>
        <tr>
         <tr>
      <td class="subHead">VALIDATION </td>
      <td>&nbsp;</td>
    </tr>
         <td class="subHead">Validate For : </td>
      <td>&nbsp;
      <div style="height:200px;width:330px;overflow:auto;">
     <?php
	$desc = "select REFERENCECODE, DESCRIPTION1 from REFSYSTEM where MASTERCODE = '30012' order by DESCRIPTION1";
	$resultdesc = $myQuery->query($desc,'SELECT', 'NAME');
	$resultdescCnt = count($resultdesc);

		for ($i=0 ; $i< $resultdescCnt; $i++)
		{?>
          <input name="newItemValidation[]" id="<?php echo "newItemValidation".$i;?>" type="checkbox" value="<?php echo $resultdesc[$i]['REFERENCECODE'] ?>" onchange="if(!this.checked){document.getElementById('newItemPrimaryColumn').checked = false;}"
          Nama/> <?php echo $resultdesc[$i]['DESCRIPTION1'] ?> </a>
		  <br>
		  <?php } ?>
	</div>

      </td>
    </tr>
    <tr>
      <td class="subHead">ADDITIONAL </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Min / Max  Character : </td>
      <td>
      	<input name="newItemMinChar" type="text" class="inputInput" id="newItemMinChar" size="5" /> /
        <input name="newItemMaxChar" type="text" class="inputInput" id="newItemMaxChar" size="5" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Constraint : </td>
      <td>
      	<label><input name="newItemPrimaryColumn" id="newItemPrimaryColumn" type="checkbox" value="1" onchange="if(this.checked){document.getElementById('newItemUnique').checked = true;}" />Primary Key</label>
        <label><input name="newItemUnique" id="newItemUnique" type="checkbox" value="1" onchange="if(!this.checked){document.getElementById('newItemPrimaryColumn').checked = false;}" />Unique</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Style : </td>
      <td>
      	<label><input name="newItemUppercase" id="newItemUppercase" type="checkbox" value="1" />Uppercase</label>
        <label><input name="newItemReadonly" id="newItemReadonly" type="checkbox" value="1" />Readonly</label>
        <label><input name="newItemDisabled" id="newItemDisabled" type="checkbox" value="1" />Disabled</label>
      </td>
    </tr>
    </tbody>
    <tbody id="listingTbody" class="chart_no_need" style="display:none;">
    <tr>
      <td class="subHead">TABULAR / REPORT </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Aggregation Type : </td>
      <td>
          <select name="newAggregateColumn" class="inputList" id="newAggregateColumn" onchange="if(this.selectedIndex > 0) document.getElementById('newAggregateColumnLabel').disabled = false; else document.getElementById('newAggregateColumnLabel').disabled = true;">
              <option value="">&nbsp;</option>
              <option value="sum">Sum</option>
              <option value="max">Max</option>
              <option value="min">Min</option>
              <option value="count">Count</option>
            </select>
        </td>
    </tr>
    <tr>
      <td class="inputLabel">Aggregation Label : </td>
      <td><input name="newAggregateColumnLabel" type="text" class="inputInput" id="newAggregateColumnLabel" size="30" disabled="disabled" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Enable Check All :</td>
      <td>
      	<label><input name="newCheckAll" id="newCheckAll" type="checkbox" value="1" />Yes</label>
        <br /><label class="labelNote">Note: For checkbox only</label>
      </td>
    </tr>
    </tbody>
    <tbody id="fileUploadTbody" style="display:none;">
    <tr>
      <td class="subHead">FILE UPLOAD </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Filter Extension : </td>
      <td>
      	<input name="newFileExtension" type="text" class="inputInput" id="newFileExtension" size="30" onkeydown="trim(this)" onblur="onkeydown(this);" />
        <br /><label class="labelNote">Note: Separate extension with ';' sign. Leave blank for none</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Upload Folder : </td>
      <td><input name="newUploadFolder" type="text" class="inputInput" id="newUploadFolder" size="30" value="upload" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Maximum File Size: </td>
      <td><input name="newMaxSize" type="text" class="inputInput" id="newMaxSize" size="5" /> kb</td>
    </tr>
    </tbody>
    <tbody id="chartTbody" style="display:none;">
    <tr class="chart_no_need">
      <td class="subHead">CHART - <a target="_blank" href="manual/How_to_use_Chart_in_Falcon.pdf">Chart Manual</a></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Chart Type : </td>
      <td>
      	<select name="chartPtype" class="inputList" id="chartType" onchange="if(this.value=='combination_column_line'){document.getElementById('chartSyAxisLabelTr').style.display=''; document.getElementById('chartSsqlTr').style.display='';} else {document.getElementById('chartSyAxisLabelTr').style.display='none'; document.getElementById('chartSsqlTr').style.display='none';}">
          <option value="">&nbsp;</option>
          <option value="Bar">Bar</option>
          <option value="Card">Card</option>
          <option value="Doughnut">Doughnut</option>
          <option value="Line">Line</option>
          <option value="Pie">Pie</option>
          <option value="PolarArea">Polar Area</option>
          <option value="Radar">Radar</option>
          <option value="Tabular">Tabular</option>
          <!--
          <option value="multiple_area">Multiple Area</option>
          <option value="multiple_bar">Multiple Bar</option>
          <option value="multiple_column">Multiple Column</option>
          <option value="multiple_line">Multiple Line</option>
          <option value="stacked_area">Stacked Area</option>
          <option value="stacked_bar">Stacked Bar</option>
          <option value="stacked_column">Stacked Column</option>
          <option value="combination_column_line">Combination Column &amp; line</option>
          -->
        </select>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Background Color : </td>
      <td><input name="chartBgColor" type="text" class="color {hash:true} inputInput" id="chartBgColor" value="FFFFFF" size="8" /></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Decimal Precision : </td>
      <td><input name="chartDecimalPrecision" type="text" class="inputInput" id="chartDecimalPrecision" size="2" /></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Axis-X Label : </td>
      <td><input name="chartXAxisLabel" type="text" class="inputInput" id="chartXAxisLabel" size="50" /></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Axis-Y Label : </td>
      <td><input name="chartPyAxisLabel" type="text" class="inputInput" id="chartPyAxisLabel" size="50" /></td>
    </tr>
    <tr>
      <td class="inputLabel">SQL : </td>
      <td>
      	<textarea name="chartPsql" cols="100" rows="10" class="inputInput" id="chartPsql" ></textarea>
        <br /><label class="labelNote">Note: First column is the Category Name. Rename the next column to be Series Name (Except Single Series Chart)</label><br />
		<br/>Database : <?php echo getListId('chartPsqlDB'); ?><br/>
        <br/><label><input name="chartPshowValue" type="checkbox" value="1" checked />Show Value</label>
      </td>
    </tr>
    <tr id="chartSyAxisLabelTr" style="display:none;">
      <td class="inputLabel">Secondary Axis-Y Label : </td>
      <td><input name="chartSyAxisLabel" type="text" class="inputInput" id="chartSyAxisLabel" size="50" /></td>
    </tr>
    <tr id="chartSsqlTr" style="display:none;">
      <td class="inputLabel">Secondary Chart SQL : </td>
      <td>
      	<textarea name="chartSsql" cols="100" rows="10" class="inputInput" id="chartSsql" ></textarea>
        <br /><label class="labelNote">Note: Category Name will refer to Primary Chart. Rename the next column to be Series Name (Except Single Series Chart)</label><br />
		<br/>Database : <?php echo getListId('chartSsqlDB'); ?><br/>
        <br/><label><input name="chartSshowValue" type="checkbox" value="1" />Show Value</label>
      </td>
    </tr>
    </tbody>
  </table>
  <br />

  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Item Permission</th>
    </tr>
    <tr>
      <td class="inputLabel">Access List : </td>
      <td><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><div align="center"><strong>List of unselected user group</strong></div></td>
            <td>&nbsp;</td>
            <td><div align="center"><strong>List of selected user group</strong></div></td>
          </tr>
          <tr>
            <td>
              <select style="width:250px;" name="nonSelectedGroup" size="10" multiple class="inputList" id="nonSelectedGroup" >
                <?php for($x=0; $x<$groupListNonSelectedCount; $x++){?>
                <option value="<?php echo $groupListNonSelected[$x][0]?>"><?php echo $groupListNonSelected[$x][1];?></option>
                <?php }?>
              </select>
            </td>
            <td width="35">
              <div align="center">
                <input name="newMoveLTR" type="button" class="inputButton" id="newMoveLTR" value="&gt;" style="margin-bottom:2px;" onclick="moveoutid('nonSelectedGroup','selectedGroup');" />
                <br>
                <input name="newMoveRTL" type="button" class="inputButton" id="newMoveRTL" value="&lt;" style="margin-bottom:2px;" onclick="moveinid('nonSelectedGroup','selectedGroup');" />
                <br>
                <input name="newMoveAllLTR" type="button" class="inputButton" id="newMoveAllLTR" value="&gt;&gt;" style="margin-bottom:2px;" onclick="listBoxSelectall('nonSelectedGroup'); moveoutid('nonSelectedGroup','selectedGroup');" />
                <br>
                <input name="newMoveAllRTL" type="button" class="inputButton" id="newMoveAllRTL" value="&lt;&lt;" style="margin-bottom:2px;" onclick="listBoxSelectall('selectedGroup'); moveinid('nonSelectedGroup','selectedGroupEdit');" />
                <br>
                <input name="newSort" type="button" class="inputButton" id="newSort" value="a-z" style="margin-bottom:2px;" onclick="sortListBox('selectedGroup');sortListBox('nonSelectedGroup');" />
              </div>
            </td>
            <td>
              <select style="width:250px;" name="selectedGroup[]" size="10" multiple class="inputList" id="selectedGroup">
              </select>
            </td>
          </tr>
        </table>
        <?php if(!ITEM_PERMISSION_ENABLED){?>
		<label class="labelNote">Note: Usage of item permission is disabled. Access permission set here will be ignored.</label>
		<?php }?>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input name="saveScreenItemNew" type="submit" class="inputButton" id="saveScreenItemNew" value="Save" disabled="disabled" onclick="listBoxSelectall('selectedGroup');" />
        <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>
  <?php }?>

  <?php if($_POST['editItem']){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Modify <?php if($duplicateItemRs){?>Duplicated <?php }?>Component Item </th>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Component : </td>
      <td>
      	<select name="componentID" class="inputList" id="componentID" onchange="changeParentComponent('editItemOrderText', this.value, <?php echo $showEditComponentItemRs[0]["COMPONENTID"];?>); swapItemDisplay('editItemOrderText', 'editItemOrderCombo'); document.getElementById('orderOptionStart').checked=true; showDatabase(document.getElementById('componentID').value, '<?php echo $showEditComponentItemRs[0]["MAPPINGID"];?>')">
          <?php for($x=0; $x<$getComponentRsCount; $x++) { ?>
          <option value="<?php echo $getComponentRs[$x]['COMPONENTID'];?>" <?php if($showEditComponentItemRs[0]['COMPONENTID'] == $getComponentRs[$x]['COMPONENTID']) echo "selected";?> >[<?php echo $getComponentRs[$x]['COMPONENTNAME'];?>] <?php echo $getComponentRs[$x]['COMPONENTTITLE'];?></option>
          <?php } ?>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Title : </td>
      <td>
      	<input name="editItemTitle" type="text" class="inputInput" id="editItemTitle" size="100" value="<?php echo $showEditComponentItemRs[0]['ITEMTITLE'];?>" />
        <script>document.getElementById('editItemTitle').focus();</script>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name : </td>
      <td>
      	<input name="editItemName" type="text" class="inputInput" id="editItemName" size="50" value="<?php echo $showEditComponentItemRs[0]['ITEMNAME'];?>" onchange="trim(this); checkUniqueName('item',this,'edit',<?php echo $_POST['code']?>)" />
      	<input name="hidden_editItemName" type="hidden" id="hidden_editItemName" value="<?php echo $showEditComponentItemRs[0]['ITEMNAME'];?>" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<select name="editItemType" class="inputList" id="editItemType" onchange="codeDropDown(this, 'saveScreenItemEdit'); changeItemType(this.value); chartLayout(this.value);">
          <?php for($x=0; $x < count($itemTypeRs); $x++) { ?>
          <option value="<?php echo $itemTypeRs[$x]['REFERENCECODE'];?>" <?php if($showEditComponentItemRs[0]["ITEMTYPE"] == $itemTypeRs[$x]['REFERENCECODE']) echo "selected";?>><?php echo $itemTypeRs[$x]["DESCRIPTION2"];?></option>
          <?php } ?>
          <option value="plugin" <?php if($showEditComponentItemRs[0]['ITEMTYPE'] == 'plugin') echo "selected";?>>Plugin</option>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr id="row_itemMapping" <?php if($showEditComponentItemRs[0]['ITEMTYPE']!='plugin'){?>style="display:none"<?php }?>>
      <td class="inputLabel">Plugin Name : </td>
      <td>
      	<select name="itemMapping" class="inputList" id="itemMapping">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x < count($pluginNameRs); $x++){?>
          <option value="<?php echo $pluginNameRs[$x]['EXT_NAME'];?>" <?php if($showEditComponentItemRs[0]['ITEMTYPE'] == 'plugin' && $showEditComponentItemRs[0]['ITEMMAPPING'] == $pluginNameRs[$x]['EXT_NAME']){?> selected="selected"<?php }?>><?php echo $pluginNameRs[$x]['EXT_TITLE'];?></option>
          <?php }?>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Order : </td>
      <td><label>
        <input name="orderOption" id="orderOptionStart" type="radio" checked="checked" onclick="swapItemDisplay('editItemOrderText', 'editItemOrderCombo')" />
        At</label>
        <label>
        <input name="orderOption" type="radio" onclick="swapItemDisplay('editItemOrderCombo', 'editItemOrderText'); showComponentItem('<?php echo $_POST['code'];?>', form1.componentID.value, '<?php echo $_POST['hiddenItemID'];?>')" />
        Before</label>
        <label>
        <input name="orderOption" type="radio" value="++" onclick="swapItemDisplay('editItemOrderCombo', 'editItemOrderText'); showComponentItem('<?php echo $_POST['code'];?>', form1.componentID.value, '<?php echo $_POST['hiddenItemID'];?>')" />
        After</label>
        <br />
        <input name="editItemOrder" id="editItemOrderText" type="text" class="inputInput" size="5" value="<?php echo $showEditComponentItemRs[0]["ITEMORDER"];?>" />
        <span id="hideEditorList"> </span></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Default Value : </td>
      <td>
		<div id="editItemDefaultValueTextDiv" <?php if($showEditComponentItemRs[0]["ITEMDEFAULTVALUEQUERY"]){ $disable='disabled="disabled"'?> style="display:none"<?php }else{?> style="display:block"<?php }?> >
	  	<input name="editItemDefaultValue" type="text" class="inputInput" id="editItemDefaultValueText" size="70" value="<?php echo trim($showEditComponentItemRs[0]["ITEMDEFAULTVALUE"]);?>" <?php if($showEditComponentItemRs[0]["ITEMDEFAULTVALUEQUERY"]){ echo $disable;}?> />
		</div>
		<div id="editItemDefaultValueQueryDiv" <?php if(!$showEditComponentItemRs[0]["ITEMDEFAULTVALUEQUERY"]){ $disable='disabled="disabled"'?> style="display:none"<?php }else{?> style="display:block"<?php }?> >
		<textarea name="editItemDefaultValue" cols="100" rows="10" class="inputInput" id="editItemDefaultValueQuery" <?php if(!$showEditComponentItemRs[0]["ITEMDEFAULTVALUEQUERY"]){ echo $disable; }?> ><?php echo trim($showEditComponentItemRs[0]["ITEMDEFAULTVALUE"]);?></textarea>
        <span id="lovEditorSpan" <?php if($showEditComponentItemRs[0]['ITEMTYPE'] != 'url'){?>style="display:none;"<?php }?>>
        	<input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=page&type=url&lovValue=editItemDefaultValueText', 1, 1, 600, 500);" />
        </span>
		<br /><label class="labelNote">Note: For URL, please fill up Default Value field with the URL</label><br />
		<br />
	     Database : <?php echo getListId('editDefaultDB',$showEditComponentItemRs[0]['ITEMDEFAULTVALUEDB']) ?><br/>
		</div>
		<br/><label><input name="editItemDefaultValueQuery" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]["ITEMDEFAULTVALUEQUERY"]){?> checked="checked"<?php }?> 
		onchange="if(this.checked==true){swapItemDisplay('editItemDefaultValueQueryDiv','editItemDefaultValueTextDiv');swapItemDisabled('editItemDefaultValueQuery','editItemDefaultValueText');} else {swapItemDisplay('editItemDefaultValueTextDiv','editItemDefaultValueQueryDiv');swapItemDisabled('editItemDefaultValueText','editItemDefaultValueQuery');}" />
		Use SQL</label>
	  </td>
    </tr>
    <tbody id="lookupTbody" <?php if($showEditComponentItemRs[0]["ITEMTYPE"] == 'chart' || $showEditComponentItemRs[0]["ITEMTYPE"] == 'file'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="inputLabel">Lookup : </td>
      <td><label>
        <input name="editLookupType" type="radio" value="no" <?php if($showEditComponentItemRs[0]["ITEMLOOKUPTYPE"] == "no") { ?> checked="checked"<?php } ?> onclick="if(this.checked == true) { form1.editPredefinedLookup.disabled = true; form1.editAdvQueryLimit.disabled = true; form1.editAdvancedLookup.disabled = true}" />
        No Lookup</label>
        <label>
        <input type="radio" name="editLookupType" value="predefined" <?php if($showEditComponentItemRs[0]["ITEMLOOKUPTYPE"] == "predefined") { ?> checked="checked"<?php } ?> onclick="if(this.checked == true) { form1.editPredefinedLookup.disabled = false; form1.editAdvQueryLimit.disabled = false; form1.editAdvancedLookup.disabled = true }" />
        Predefined</label>
        <label>
        <input type="radio" name="editLookupType" value="advanced" <?php if($showEditComponentItemRs[0]["ITEMLOOKUPTYPE"] == "advanced") { ?> checked="checked"<?php } ?> onclick="if(this.checked == true) { form1.editAdvancedLookup.disabled = false; form1.editAdvQueryLimit.disabled = false; form1.editPredefinedLookup.disabled = true }" />
        Advanced</label>      </td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Predefined Lookup : </td>
      <td><select name="editPredefinedLookup" class="inputList" id="editPredefinedLookup" <?php if($showEditComponentItemRs[0]['ITEMLOOKUPTYPE'] != "predefined") { ?>disabled="disabled"<?php } ?> >
          <option value="">&nbsp;</option>
          <?php for($x=0; $x<$predefinedRsCount; $x++) { ?>
          <option value="<?php echo $predefinedRs[$x]['DESCRIPTION1'];?>" <?php if(strpos($showEditComponentItemRs[0]['ITEMLOOKUP'],"DESCRIPTION1 = '".$predefinedRs[$x]['DESCRIPTION1']."'") !== false){?> selected="selected"<?php }?>><?php echo $predefinedRs[$x]['DESCRIPTION1'];?></option>
          <?php } ?>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Advanced Lookup : </td>
      <?php $showEditComponentItemRs[0]['ITEMLOOKUP'] = str_replace('\\','',$showEditComponentItemRs[0]['ITEMLOOKUP']);
			?>
      <td><textarea name="editAdvancedLookup" cols="100" rows="10" class="inputInput" id="editAdvancedLookup" <?php if($showEditComponentItemRs[0]['ITEMLOOKUPTYPE'] != 'advanced') { ?>disabled="disabled"<?php } ?>><?php if($showEditComponentItemRs[0]['ITEMLOOKUPTYPE'] == 'advanced') echo trim($showEditComponentItemRs[0]['ITEMLOOKUP']);?></textarea><br>
<a href="javascript:void(0)" onclick="jQuery(this).next().show()">Show Sample Code</a>
<div style="display:none;background-color:#F7F7F7;padding:10px;">
	FLC_ID / FLC_NAME pair --> <code style="">select 'M' as flc_id, 'Malaysia' as flc_name</code><br>
	Multiple values --> <code>select col1, col2, col3, col4 (assumes first column to be the key)</code>
<br>
</div>
		<br/><br /> Database : <?php echo getListId('editAdvanceDB',$showEditComponentItemRs[0]['ITEMLOOKUPDB']) ?><br/>
		
	<br><label><input name="editAdvQueryLimit" type="checkbox" id="editAdvQueryLimit" value="1" <?php if($showEditComponentItemRs[0]['ITEMLOOKUPUNLIMITED']){?> checked="checked"<?php }?> <?php if($showEditComponentItemRs[0]['ITEMLOOKUPTYPE'] != 'advanced') { ?>disabled="disabled"<?php } ?> />Unlimited (fetch all rows)</label></td>
    </tr>
    </tbody>
    <tr id="row_itemHints" class="chart_no_need">
      <td class="inputLabel">Hints : </td>
      <td><textarea name="editItemHints" cols="100" rows="3" class="inputInput" id="editItemHints" ><?php echo $showEditComponentItemRs[0]['ITEMHINTS'];?></textarea></td>
    </tr>
    <tr id="row_itemPlaceholder" class="chart_no_need">
      <td class="inputLabel">Placeholder : </td>
      <td><textarea name="editItemPlaceholder" cols="100" rows="3" class="inputInput" id="editItemPlaceholder" ><?php echo $showEditComponentItemRs[0]['ITEMPLACEHOLDER'];?></textarea></td>
    </tr>
    <tr id="row_itemNotes">
      <td class="inputLabel">Notes : </td>
      <td><textarea name="editItemNotes" cols="100" rows="3" class="inputInput" id="editItemNotes" ><?php echo $showEditComponentItemRs[0]['ITEMNOTES'];?></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Status : </td>
      <td><select name="editItemStatus" id="editItemStatus" class="inputList">
          <option value="1" <?php if($showEditComponentItemRs[0]['ITEMSTATUS'] == 1) { ?> selected<?php }?> >Active</option>
          <option value="0" <?php if($showEditComponentItemRs[0]['ITEMSTATUS'] == 0) { ?> selected<?php }?> >Not Active</option>
        </select></td>
    </tr>
    <tbody id="frequentTbody" class="chart_no_need">
    <tr>
      <td class="subHead">FREQUENTLY USED</td>
      <td>&nbsp;</td>
    </tr>
    <tr id="databaseMappingTr" <?php if(!$showEditComponentItemRs[0]['COMPONENTBINDINGTYPE'] || !$showEditComponentItemRs[0]['COMPONENTBINDINGSOURCE']){?> style="display:none"<?php }?>>
      <td class="inputLabel">Database Mapping : </td>
      <td><span id="hideDatabaseList">
        <select name="editMappingID" class="inputList" id="editMappingID" >
          <option value="">&nbsp;</option>
          <?php
			for($x=0; $x < $getColumnsRsCount; $x++) { ?>
          <option value="<?php echo $getColumnsRs[$x]['COLUMN_NAME'];?>" <?php if($showEditComponentItemRs[0]['MAPPINGID'] == $getColumnsRs[$x]['COLUMN_NAME']) { ?> selected<?php }?>><?php echo $getColumnsRs[$x]['COLUMN_NAME'];?></option>
          <?php } ?>
        </select>
        </span></td>
    </tr>
    <tr>
      <td class="inputLabel">Length / Rows : </td>
      <td>
      	<input name="editItemInputLength" type="text" class="inputInput" id="editItemInputLength" size="5" value="<?php echo $showEditComponentItemRs[0]['ITEMINPUTLENGTH'];?>" /> /
        <input name="editTextareaRows" type="text" class="inputInput" id="editTextareaRows" size="5" value="<?php echo $showEditComponentItemRs[0]['ITEMTEXTAREAROWS'];?>" />
        <br /><label class="labelNote">Note: Width / Height</label>
      </td>
    </tr>
    <tr id="row_itemTab">
      <td class="inputLabel">Tab Index : </td>
      <td><input name="editTabIndex" type="text" class="inputInput" id="editTabIndex" value="<?php echo $showEditComponentItemRs[0]['ITEMTABINDEX'];?>" size="2" maxlength="3" /></td>
    </tr>
    <tr id="row_itemTextAlign">
      <td class="inputLabel">Text Alignment  : </td>
      <td><select name="editItemTextAlign" class="inputList" id="editItemTextAlign">
          <option value="left" <?php if($showEditComponentItemRs[0]['ITEMTEXTALIGN'] == 'left') { ?> selected<?php } ?>>Left</option>
          <option value="right"<?php if($showEditComponentItemRs[0]['ITEMTEXTALIGN'] == 'right') { ?> selected<?php } ?>>Right</option>
          <option value="center"<?php if($showEditComponentItemRs[0]['ITEMTEXTALIGN'] == 'center') { ?> selected<?php } ?>>Center</option>
        </select></td>
    </tr>
    <tr id="row_itemAppend">
      <td class="inputLabel">Append Item To Item Before : </td>
      <td>
      	<label><input name="editItemAppend" id="editItemAppend" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMAPPENDTOBEFORE']){ ?> checked="checked"<?php }?> />Yes</label>
      </td>
    </tr>
    </tbody>
    <tbody id="additionalTbody" <?php if($showEditComponentItemRs[0]['ITEMTYPE'] == 'chart' || $showEditComponentItemRs[0]['ITEMTYPE'] == 'file' || $showEditComponentItemRs[0]['ITEMTYPE'] == 'image' || $showEditComponentItemRs[0]['ITEMTYPE'] == 'lov' || $showEditComponentItemRs[0]['ITEMTYPE'] == 'url'){?> style="display:none;"<?php }?>>
    <tr>
          <td class="subHead">VALIDATION </td>
      <td>&nbsp;</td>
    </tr>
         <td class="subHead">Validate For : </td>
      <td>&nbsp;
      <div style="height:200px;width:330px;overflow:auto;">

    <?php
	$desc = "select REFERENCECODE, DESCRIPTION1 from REFSYSTEM where MASTERCODE = '30012' order by DESCRIPTION1";
	$resultdesc = $myQuery->query($desc,'SELECT', 'NAME');

	for ($i=0 ; $i<count($resultdesc) ; $i++)
		{
		if(in_array($resultdesc[$i]['REFERENCECODE'],$parameter_value))
			$check= 'checked';
		else
			$check= "";
		?>


          <input name="ItemValidation[]" id="<?php echo 'ItemValidation'.$i;?>" type="checkbox" value="<?php echo $resultdesc[$i]['REFERENCECODE'] ?>" <?php echo $check; ?> /> <?php echo $resultdesc[$i]['DESCRIPTION1'] ?> </a>
		  <br>
		  <?php } ?>
</div>

      </td>
    </tr>
         <td class="subHead">ADDITIONAL </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Min / Max  Character : </td>
      <td>
      	<input name="editItemMinChar" type="text" class="inputInput" id="editItemMinChar" value="<?php echo $showEditComponentItemRs[0]['ITEMMINCHAR'];?>" size="5" /> /
        <input name="editItemMaxChar" type="text" class="inputInput" id="editItemMaxChar" value="<?php echo $showEditComponentItemRs[0]['ITEMMAXCHAR'];?>" size="5" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Constraint : </td>
      <td>
        <label><input name="editItemPrimaryColumn" id="editItemPrimaryColumn" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMPRIMARYCOLUMN']){ ?> checked="checked"<?php }?> onchange="if(this.checked){document.getElementById('editItemUnique').checked = true;}" />Primary Key</label>
        <label><input name="editItemUnique" id="editItemUnique" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMUNIQUE']){ ?> checked="checked"<?php }?> onchange="if(!this.checked){document.getElementById('editItemPrimaryColumn').checked = false;}" />Unique</label>
      </td>
    </tr>
	<tr>
      <td class="inputLabel">Style : </td>
      <td>
      	<label><input name="editItemUppercase" id="editItemUppercase" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMUPPERCASE']){ ?> checked="checked"<?php }?> />Uppercase</label>
        <label><input name="editItemReadonly" id="editItemReadonly" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMREADONLY']){ ?> checked="checked"<?php }?> />Readonly</label>
        <label><input name="editItemDisabled" id="editItemDisabled" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMDISABLED']){ ?> checked="checked"<?php }?> />Disabled</label>
      </td>
    </tr>
    </tbody>
    <tbody id="listingTbody" class="chart_no_need">
    <tr>
      <td class="subHead">TABULAR / REPORT</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Aggregation Type : </td>
      <td><select name="editAggregateColumn" class="inputList" id="editAggregateColumn" onchange="if(this.selectedIndex &gt; 0) document.getElementById('editAggregateColumnLabel').disabled = false; else document.getElementById('editAggregateColumnLabel').disabled = true;">
          <option value="">&nbsp;</option>
          <option value="sum" <?php if($showEditComponentItemRs[0]['ITEMAGGREGATECOLUMN'] == 'sum') echo "selected"?>>Sum</option>
          <option value="max" <?php if($showEditComponentItemRs[0]['ITEMAGGREGATECOLUMN'] == 'max') echo "selected"?>>Max</option>
          <option value="min" <?php if($showEditComponentItemRs[0]['ITEMAGGREGATECOLUMN'] == 'min') echo "selected"?>>Min</option>
          <option value="count" <?php if($showEditComponentItemRs[0]['ITEMAGGREGATECOLUMN'] == 'count') echo "selected"?>>Count</option>
        </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Aggregation Label : </td>
      <td><input name="editAggregateColumnLabel" type="text" class="inputInput" id="editAggregateColumnLabel" size="30" value="<?php echo $showEditComponentItemRs[0]['ITEMAGGREGATECOLUMNLABEL'];?>" <?php if($showEditComponentItemRs[0]['ITEMAGGREGATECOLUMN'] == ''){?>disabled<?php } ?> /></td>
    </tr>
    <tr>
      <td class="inputLabel">Enable Check All :</td>
      <td>
      	<label><input name="editCheckAll" id="editCheckAll" type="checkbox" value="1" <?php if($showEditComponentItemRs[0]['ITEMCHECKALL']){ ?> checked="checked"<?php }?> />Yes</label>
        <br /><label class="labelNote">Note: For checkbox only</label>
      </td>
    </tr>
	</tbody>
    <tbody id="fileUploadTbody" <?php if($showEditComponentItemRs[0]['ITEMTYPE'] != 'file'){?> style="display:none;"<?php }?>>
    <tr>
      <td class="subHead">FILE UPLOAD </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Filter Extension : </td>
      <td>
      	<input name="editFileExtension" type="text" class="inputInput" id="editFileExtension" value="<?php echo $uploadExtension;?>" size="30" onkeydown="trim(this)" onblur="onkeydown(this);" />
        <br /><label class="labelNote">Note: Separate extension with ';' sign. Leave blank for none</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Upload Folder : </td>
      <td><input name="editUploadFolder" type="text" class="inputInput" id="editUploadFolder" size="30" value="<?php echo $uploadFolder;?>" /></td>
    </tr>
    <tr>
      <td class="inputLabel">Maximum File Size: </td>
      <td><input name="editMaxSize" type="text" class="inputInput" id="editMaxSize" value="<?php echo $uploadMaxSize;?>" size="5" /> kb</td>
    </tr>
    </tbody>
    <tbody id="chartTbody" <?php if($showEditComponentItemRs[0]['ITEMTYPE'] != 'chart'){?> style="display:none;"<?php }?>>
    <tr class="chart_no_need">
      <td class="subHead">CHART </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td class="inputLabel">Chart Type : </td>
      <td>
      	<select name="chartPtype" class="inputList" id="chartType"
        	onchange="if(this.value=='combination_column_line'){document.getElementById('chartSyAxisLabelTr').style.display=''; document.getElementById('chartSsqlTr').style.display='';} else {document.getElementById('chartSyAxisLabelTr').style.display='none'; document.getElementById('chartSsqlTr').style.display='none';}">
          <option value="Bar" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Bar'){?> selected="selected"<?php }?>>Bar</option>
          <option value="Card" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Card'){?> selected="selected"<?php }?>>Card</option>
          <option value="Doughnut" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Doughnut'){?> selected="selected"<?php }?>>Doughnut</option>
          <option value="Line" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Line'){?> selected="selected"<?php }?>>Line</option>
          <option value="Pie" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Pie'){?> selected="selected"<?php }?>>Pie</option>
          <option value="PolarArea" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'PolarArea'){?> selected="selected"<?php }?>>Polar Area</option>
          <option value="Radar" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Radar'){?> selected="selected"<?php }?>>Radar</option>
          <option value="Tabular" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'Tabular'){?> selected="selected"<?php }?>>Tabular</option>
          <!--
          <option value="multiple_area" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'multiple_area'){?> selected="selected"<?php }?>>Multiple Area</option>
          <option value="multiple_bar" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'multiple_bar'){?> selected="selected"<?php }?>>Multiple Bar</option>
          <option value="multiple_column" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'multiple_column'){?> selected="selected"<?php }?>>Multiple Column</option>
          <option value="multiple_line" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'multiple_line'){?> selected="selected"<?php }?>>Multiple Line</option>
          <option value="stacked_area" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'stacked_area'){?> selected="selected"<?php }?>>Stacked Area</option>
          <option value="stacked_bar" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'stacked_bar'){?> selected="selected"<?php }?>>Stacked Bar</option>
          <option value="stacked_column" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'stacked_column'){?> selected="selected"<?php }?>>Stacked Column</option>
          <option value="combination_column_line" <?php if($getChartItemRs[0]['CHART_PTYPE'] == 'combination_column_line'){?> selected="selected"<?php }?>>Combination Column &amp; line</option>
          -->
        </select>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Background Color : </td>
      <td><input name="chartBgColor" type="text" class="color {hash:true} inputInput" id="chartBgColor" value="<?php echo $getChartItemRs[0]['CHART_BG_COLOR'];?>" size="8" /></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Decimal Precision : </td>
      <td><input name="chartDecimalPrecision" type="text" class="inputInput" id="chartDecimalPrecision" size="2" value="<?php echo $getChartItemRs[0]['CHART_DECIMAL_PRECISION'];?>" /></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Axis-X Label : </td>
      <td><input name="chartXAxisLabel" type="text" class="inputInput" id="chartXAxisLabel" size="50" value="<?php echo $getChartItemRs[0]['CHART_X_AXIS_LABEL'];?>" /></td>
    </tr>
    <tr class="chart_no_need">
      <td class="inputLabel">Axis-Y Label : </td>
      <td><input name="chartPyAxisLabel" type="text" class="inputInput" id="chartPyAxisLabel" size="50" value="<?php echo $getChartItemRs[0]['CHART_PY_AXIS_LABEL'];?>" /></td>
    </tr>
    <tr>
      <td class="inputLabel">SQL : </td>
      <td>
      	<textarea name="chartPsql" cols="100" rows="10" class="inputInput" id="chartPsql" ><?php echo $getChartItemRs[0]['CHART_PSQL'];?></textarea>
        <br /><label class="labelNote">Note: First column is the Category Name. Rename the next column to be Series Name (Except Single Series Chart)</label><br />
		<br/>Database : <?php echo getListId('chartPsqlDB',$getChartItemRs[0]['CHART_PSQL_DB']) ?>
        <label><input name="chartPshowValue" type="checkbox" value="1" <?php if($getChartItemRs[0]['CHART_PSHOW_VALUE']){?> checked<?php }?> />Show Value</label>
      </td>
    </tr>
    <tr id="chartSyAxisLabelTr" <?php if($getChartItemRs[0]['CHART_PTYPE'] != 'combination_column_line'){?> style="display:none;"<?php }?>>
      <td class="inputLabel">Secondary Axis-Y Label : </td>
      <td><input name="chartSyAxisLabel" type="text" class="inputInput" id="chartSyAxisLabel" size="50" value="<?php echo $getChartItemRs[0]['CHART_SY_AXIS_LABEL'];?>" /></td>
    </tr>
    <tr id="chartSsqlTr" <?php if($getChartItemRs[0]['CHART_PTYPE'] != 'combination_column_line'){?> style="display:none;"<?php }?>>
      <td class="inputLabel">Secondary Chart SQL : </td>
      <td>
      	<textarea name="chartSsql" cols="100" rows="10" class="inputInput" id="chartSsql" ><?php echo $getChartItemRs[0]['CHART_SSQL'];?></textarea>
        <br /><label class="labelNote">Note: Category Name will refer to Primary Chart. Rename the next column to be Series Name (Except Single Series Chart)</label><br />
		<br/>Database : <?php echo getListId('chartSsqlDB',$getChartItemRs[0]['CHART_SSQL_DB']) ?>
        <br/><br/><label><input name="chartSshowValue" type="checkbox" value="1" <?php if($getChartItemRs[0]['CHART_SSHOW_VALUE']){?> checked<?php }?> />Show Value</label>
      </td>
    </tr>
    </tbody>
  </table>
  <br />

  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Item Permission</th>
    </tr>
    <tr>
      <td class="inputLabel">Access List : </td>
      <td>
        <table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><div align="center"><strong>List of unselected user group</strong></div></td>
            <td>&nbsp;</td>
            <td><div align="center"><strong>List of selected user group</strong></div></td>
          </tr>
          <tr>
            <td>
              <select style="width:250px;" name="nonSelectedGroup" size="10" multiple class="inputList" id="nonSelectedGroup" >
                <?php for($x=0; $x<$groupListNonSelectedCount; $x++){?>
                <option value="<?php echo $groupListNonSelected[$x][0]?>" ><?php echo $groupListNonSelected[$x][1];?></option>
                <?php } ?>
              </select>
            </td>
            <td width="35">
              <div align="center">
                <input name="newMoveLTR" type="button" class="inputButton" id="newMoveLTR" value="&gt;" style="margin-bottom:2px;" onclick="moveoutid('nonSelectedGroup','selectedGroupEdit'); " />
                <br>
                <input name="newMoveRTL" type="button" class="inputButton" id="newMoveRTL" value="&lt;" style="margin-bottom:2px;" onclick="moveinid('nonSelectedGroup','selectedGroupEdit'); " />
                <br>
                <input name="newMoveAllLTR" type="button" class="inputButton" id="newMoveAllLTR" value="&gt;&gt;" style="margin-bottom:2px;" onclick="listBoxSelectall('nonSelectedGroup'); moveoutid('nonSelectedGroup','selectedGroupEdit');" />
                <br>
                <input name="newMoveAllRTL" type="button" class="inputButton" id="newMoveAllRTL" value="&lt;&lt;" style="margin-bottom:2px;" onclick="listBoxSelectall('selectedGroupEdit'); moveinid('nonSelectedGroup','selectedGroupEdit');" />
                <br>
                <input name="newSort" type="button" class="inputButton" id="newSort" value="a-z" style="margin-bottom:2px;" onclick="sortListBox('selectedGroupEdit');sortListBox('nonSelectedGroup');" />
              </div>
            </td>
            <td>
              <select style="width:250px;height:150px;" name="selectedGroupEdit[]" size="10" multiple class="inputList" id="selectedGroupEdit" >
                <?php for($x=0; $x<$groupListSelectedCount; $x++){?>
                <option value="<?php echo $groupListSelected[$x][0]?>"><?php echo $groupListSelected[$x][1];?></option>
                <?php }?>
              </select>
            </td>
          </tr>
        </table>
        <?php if(!ITEM_PERMISSION_ENABLED){?>
		<label class="labelNote">Note: Usage of item permission is disabled. Access permission set here will be ignored.</label>
		<?php }?>
      </td>
   </tr>
   <tr>
      <td colspan="2" class="contentButtonFooter">
         <input name="hiddenItemID" type="hidden" id="hiddenItemID" value="<?php echo $_POST['hiddenItemID'];?>" />
         <input name="saveScreenItemEdit" type="submit" class="inputButton" id="saveScreenItemEdit" value="Save" onclick="listBoxSelectall('selectedGroupEdit');" />
         <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>
  <?php }?>
</form>
<?php if($_POST['code'] && $_POST['showScreen']){?>
<br />
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent flcEditorList">
  <tr>
    <th colspan="9">Component </th>
  </tr>
  <?php if($referenceRsCount) { ?>
  <tr>
    <th width="10" class="listingHead">#</th>
    <th width="15" class="listingHead">ID</th>
    <th width="100" class="listingHead">Name</th>
    <th class="listingHead">Title</th>
    <th width="100" class="listingHead">Type</th>
    <th width="30" class="listingHead">Order</th>
    <th width="30" class="listingHead">Trigger</th>
    <th width="50" class="listingHead">Status</th>
    <th width="80" class="listingHead">Action</th>
  </tr>
  <?php for($x=0; $x<$referenceRsCount; $x++){?>
 <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1){?> style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else {?> onmouseout="this.style.background = '#ffffff'"<?php }?> >
    <td class="listingContent"><?php echo ($x+1).".";?>&nbsp;</td>
    <td class="listingContent"><?php echo $referenceRs[$x]['COMPONENTID'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['COMPONENTNAME'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['COMPONENTTITLE'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['COMPONENTTYPE'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['COMPONENTORDER'];?></td>
    <td class="listingContent"><?php if($referenceRs[$x]['TRIGGER_ID']) echo 'Yes'; else echo 'No';?></td>
    <td class="listingContent"><?php if($referenceRs[$x]['COMPONENTSTATUS']) echo 'Active'; else echo 'Inactive';?></td>
    <td nowrap="nowrap" class="listingContentRight">
      <form id="formComponent" name="formComponent" method="post">
        <input name="moveUpComponent" <?php if($referenceRsCount == 1) echo ' disabled '; ?>type="submit" class="inputButton" id="moveUpComponent" value="&uarr;" />
        <input name="moveDownComponent" <?php if($referenceRsCount == 1) echo ' disabled '; ?>type="submit" class="inputButton" id="moveDownComponent" value="&darr;" />
        <input name="duplicateComponent" type="submit" class="inputButton" id="duplicateComponent" value="Duplicate" onClick="if(window.confirm('Duplicate this component?')) {return true} else {return false}" />
        <input name="editComponent" type="submit" class="inputButton" id="editComponent" value="Update" />
        <input name="deleteComponent" type="submit" class="inputButton" id="deleteComponent" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this component?\nThis will also delete ALL component items under this component')) {return true} else {return false}" />
        <input name="hiddenComponentID" type="hidden" id="hiddenComponentID" value="<?php echo $referenceRs[$x]['COMPONENTID'];?>" />
        <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
      </form>
    </td>
  </tr>
  <?php }?>
  <?php }else{?>
  <tr>
    <td colspan="9" class="myContentInput">&nbsp;&nbsp;No component(s) found.. </td>
  </tr>
  <?php }?>
  <tr>
    <td colspan="9" class="contentButtonFooter">
        <form id="form3" name="form3" method="post">
          <input name="resetOrderingComponent" type="submit" class="inputButton" id="resetOrderingComponent" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the component order?')) {return true} else {return false}" />
          <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
          <input name="hiddenComponentID" type="hidden" id="hiddenComponentID" value="<?php echo $_POST['hiddenComponentID'];?>" />
          <input name="newComponent" type="submit" class="inputButton" id="newComponent" value="New Component" />
        </form>
    </td>
  </tr>
</table>
<?php } ?>

<?php if($_POST['code'] && $_POST['showScreen']){?>
<br />
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent flcEditorList">
  <tr>
    <th colspan="10">Component Items </th>
  </tr>
  <?php if($reference_2RsCount){?>
  <tr>
    <th width="10" class="listingHead">#</th>
    <th width="15" class="listingHead">ID</th>
    <th width="100" class="listingHead">Component</th>
    <th width="100" class="listingHead">Name</th>
    <th class="listingHead">Title</th>
    <th width="100" class="listingHead">Type</th>
    <th width="30" class="listingHead">Order</th>
    <th width="30" class="listingHead">Trigger</th>
    <th width="50" class="listingHead">Status</th>
    <th width="80" class="listingHead">Action</th>
  </tr>
  <?php for($x=0; $x<$reference_2RsCount; $x++) { ?>
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;<?php if($reference_2Rs[$x]['ITEMPRIMARYCOLUMN'] == '1') { ?>font-weight:bold;<?php } ?>" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?><?php if($reference_2Rs[$x]['ITEMPRIMARYCOLUMN'] == '1') { ?>style="font-weight:bold;"<?php } ?> onmouseout="this.style.background = '#ffffff'"<?php } ?> >
    <td class="listingContent"><?php echo ($x+1).".";?></td>
    <td class="listingContent"><?php echo $reference_2Rs[$x]['ITEMID'];?></td>
    <td class="listingContent"><?php echo $reference_2Rs[$x]['COMPONENTNAME'];?></td>
    <td class="listingContent"><?php echo $reference_2Rs[$x]['ITEMNAME'];?></td>
    <td class="listingContent"><?php echo $reference_2Rs[$x]['ITEMTITLE'];?></td>
    <td class="listingContent"><?php echo $reference_2Rs[$x]['ITEMTYPE'];?></td>
    <td class="listingContent"><?php echo $reference_2Rs[$x]['ITEMORDER'];?></td>
    <td class="listingContent"><?php if($reference_2Rs[$x]['TRIGGER_ID']) echo 'Yes'; else echo 'No';?></td>
    <td class="listingContent"><?php if($reference_2Rs[$x]['ITEMSTATUS'] == 1) echo 'Active'; else echo 'Inactive';?></td>
    <td nowrap="nowrap" class="listingContentRight">
      <form id="formReference" name="formReference" method="post">
        <input name="moveUpItem" <?php if($reference_2RsCount == 1) echo ' disabled '; ?>type="submit" class="inputButton" id="moveUpItem" value="&uarr;" />
        <input name="moveDownItem" <?php if($reference_2RsCount == 1) echo ' disabled '; ?>type="submit" class="inputButton" id="moveDownItem" value="&darr;" />
        <input name="duplicateReference" type="submit" class="inputButton" id="duplicateReference" value="Duplicate" onClick="if(window.confirm('Duplicate this item?')) {return true} else {return false}" />
        <input name="editItem" type="submit" class="inputButton" id="editItem" value="Update" />
        <input name="deleteReference" type="submit" class="inputButton" id="deleteReference" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this component item?')) {return true} else {return false}" />
        <input name="hiddenItemID" type="hidden" id="hiddenItemID" value="<?php echo $reference_2Rs[$x]['ITEMID'];?>" />
        <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
      </form>
    </td>
  </tr>
  <?php }?>
  <?php }else{?>
  <tr>
    <td colspan="10" class="myContentInput">&nbsp;&nbsp;No component item(s) found.. </td>
  </tr>
  <?php }?>
  <tr>
    <td colspan="10" class="contentButtonFooter">
        <form id="form2" name="form2" method="post" action="">
          <input name="resetOrderingItem" type="submit" class="inputButton" id="resetOrderingItem" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the component item order?')) {return true} else {return false}" />
          <input name="hiddenComponentID" type="hidden" id="hiddenComponentID" value="<?php echo $_POST['hiddenComponentID'];?>" />
          <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
			<input name="newItem" type="submit" class="inputButton" id="newItem" value="New Component Item"  <?php if(!$referenceRsCount) { ?>disabled="disabled" style="color:#999999" <?php } ?>  />
        </form>
     </td>
  </tr>
</table>
<?php }
if($_POST['editItem'] == 'Update') { ?>
<script>jQuery('#editItemType').change();</script>
<?php } ?>
<script>
//from resizeOverflowedReport()
var pageWidthRef = jQuery('#form1').width();
var allReport = jQuery('#content').find('.flcEditorList');			//detect all reports in content page

for(var x=0; x < allReport.length; x++)
{
	jQuery('#content').append('<div id="overflowArea_'+x+'"></div>');
	jQuery(allReport[x]).after('').appendTo('#overflowArea_'+x);

	jQuery(allReport[x]).parent()
		.css('width',pageWidthRef+'px')
		.css('position','relative')
		.css('overflow','auto')
		.css('margin-left','0px')
		.css('margin-bottom','10px');
}
</script>
