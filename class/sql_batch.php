<?php
/*standard SQL function*/
class dbSQLBatch extends dbQueryFactory
{
	protected $dbQuery;
	protected $dbc;
	
	public function __construct($dbc,$dbms)
	{	
		$this->dbc = $dbc;
		$this->dbQuery = $this->getObj($dbc,$dbms);
	}
//=========================================== user ===============================================
	//get user info
	public function getUserInfo($username,$password)
	{
		$user_profile_schema 						= USER_PROFILE;
		$user_profile_userpassword 					= USER_PROFILE_USERPASSWORD;
		$user_profile_userid 						= USER_PROFILE_USERID;
		$user_profile_name 							= USER_PROFILE_NAME;
		$user_profile_username 						= USER_PROFILE_USERNAME;
		$user_profile_usergroupcode 				= USER_PROFILE_USERGROUPCODE;
		$user_profile_usertypecode  				= USER_PROFILE_USERTYPECODE;
		$user_profile_departmentcode				= USER_PROFILE_DEPARTMENTCODE;
		$user_profile_imagefile						= USER_PROFILE_IMAGEFILE;
		$user_profile_userchangepasswordtimestamp	= USER_PROFILE_USERCHANGEPASSWORDTIMESTAMP;

		//if have additional condition
		if(LOGIN_CONDITION)
			$extraSQL = " and ".LOGIN_CONDITION." ";

		$sql = "select 	$user_profile_userid, $user_profile_username, $user_profile_name, $user_profile_usergroupcode,
						$user_profile_usertypecode, $user_profile_departmentcode, $user_profile_imagefile,
						$user_profile_userchangepasswordtimestamp as USERCHANGEPASSWORDTIMESTAMP
				from $user_profile_schema
				where $user_profile_username = '".$username."'
				and $user_profile_userpassword = '".$password."' ".$extraSQL;

		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
//========================================= eof user =============================================

//============================================ menu =================================================
	//get list of menu by parent and permission (recursively)
	public function getMenuList($parentId = '', $permissionFlag = true, $uniqueRole=false, $publicFlag = false)
	{
		//root menu
		if(!$parentId) $parentId = 0;

		//permission
		if($permissionFlag && $_SESSION['userID'] != '1')
		{
			if($uniqueRole === false)
				$menuPermissionSQL = " and MENUID in (".$this->getPermissionSQL('menu',$_SESSION['userID']).") ";
			else
				$menuPermissionSQL = " and MENUID in (".$this->getPermissionSQL('menu',$_SESSION['userID'],$uniqueRole).") ";
		}
		else if ($publicFlag)
			$menuPermissionSQL = " and LINKTYPE = 'P' ";

		//get list of menu
		$getMenuList = "select MENUID, MENUNAME, MENUTITLE, MENUPARENT, MENULINK, MENUTARGET, MENUORDER, MENUSTATUS, 
							MENUHINTS, MENULEVEL, MENUSTATE, MENUICON, LINKTYPE
						from FLC_MENU 
						where MENUPARENT = ".$parentId." ".$menuPermissionSQL." 
						order by MENUORDER";

		$getMenuListRs = $this->dbQuery->query($getMenuList, 'SELECT', 'NAME');
		$getMenuListRsCount = count($getMenuListRs);

		//loop on count of menu
		for($x=0; $x<$getMenuListRsCount; $x++)
		{
			//get list of child menu
			$getMenuListRs[$x]['MENUCHILD'] = $this->getMenuList($getMenuListRs[$x]['MENUID'], $permissionFlag, $uniqueRole, $publicFlag);
		}//eof for

		return $getMenuListRs;
	}//eof function

	//get list of menuid by menuparent
	public function getMenuIdByMenuParent($menuId, $includeParent = true)
	{
		//if have menuId
		if($menuId)
		{
			if($includeParent)
				$extraSQL = " or MENUID = ".$menuId;

			//get related menu id
			$getMenuId = "select MENUID from FLC_MENU where MENUPARENT = ".$menuId.$extraSQL;
			$getMenuIdRs = $this->dbQuery->query($getMenuId);
			$getMenuIdRsCount = count($getMenuIdRs);

			//get list of menu
			for($x=0; $x<$getMenuIdRsCount; $x++)
			{
				$relatedMenuId[] = $getMenuIdRs[$x][0];

				//except the given menuid
				if($getMenuIdRs[$x][0] != $menuId)
				{
					//get sub menu (if any)
					$subMenu = $this->getMenuIdByMenuParent($getMenuIdRs[$x][0], false);

					//if have sub menu
					if(is_array($subMenu))
					{
						//merge parent and sub menu array
						$relatedMenuId = array_merge($relatedMenuId, $subMenu);
					}//eof if
				}//eof if
			}//eof for
		}//eof if

		if(count($relatedMenuId) == 1)
			return $relatedMenuId[0];
		else
			return $relatedMenuId;
	}//eof function

	//delete menu
	public function deleteMenu($menuId)
	{
		//if have menuId
		if($menuId)
		{
			//check array size
			if(is_array($menuId) && count($menuId)==1)
				$menuId = $menuId[0];

			//if array
			if(is_array($menuId))
				$constraint = "where MENUID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$menuId)." as col ".$this->fromDual().") a )";
				//$constraint = "where MENUID in (select ".implode($this->fromDual()." union select ",$menuId).$this->fromDual().")";
			else
				$constraint = "where MENUID = ".$menuId;

			//delete menu
			$deleteMenu = "delete from FLC_MENU ".$constraint;
			$deleteMenuRs = $this->dbQuery->query($deleteMenu,'RUN');

			//if menu deleted
			if($deleteMenuRs)
			{
				//get list of everything related to menu
				$relatedPageId 		= $this->getPageIdByMenuId($menuId);					//page

				//delete everything related to menu
				$deletePage 		= $this->deletePage($relatedPageId);					//page
				$deletePermission	= $this->deletePermission('menu', $menuId);				//permission
			}//eof if
		}//eof if

		return $deleteMenuRs;
	}//eof function
//========================================== eof menu ===============================================

//============================================ page =================================================
	//get list of FLC_PAGE columns
	public function getPageColumns()
	{
		//get list of FLC_PAGE columns
		$getPageColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_PAGE','PAGEID,MENUID');
		$getPageColumnRsCount = count($getPageColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getPageColumnRsCount; $x++)
			$pageColumns[] = $getPageColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $pageColumns;
	}//eof function

	//get list of page id by menu id
	public function getPageIdByMenuId($menuId)
	{
		//if have menuId
		if($menuId)
		{
			//if array
			if(is_array($menuId))
				$constraint = "where MENUID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$menuId)." as col ".$this->fromDual().") a )";
				//$constraint = "where MENUID in (select ".implode($this->fromDual()." union select ",$menuId).$this->fromDual().")";
			else
				$constraint = "where MENUID = ".$menuId;

			//get related page id
			$getPageId = "select PAGEID from FLC_PAGE ".$constraint;
			$getPageIdRs = $this->dbQuery->query($getPageId);
			$getPageIdRsCount = count($getPageIdRs);

			//get list of page
			for($x=0; $x<$getPageIdRsCount; $x++)
				$relatedPageId[] = $getPageIdRs[$x][0];
		}//eof if

		if(count($relatedPageId) == 1)
			return $relatedPageId[0];
		else
			return $relatedPageId;
	}//eof function

	//duplicate page
	public function duplicatePage($fromPage, $toMenu)
	{
		//if have fromPage
		if($fromPage && $toMenu)
		{
			//get max page id
			$maxPageIdRs = $this->maxValue('FLC_PAGE','PAGEID',0)+1;

			//get list of columns
			$pageColumns = $this->getPageColumns();

			//duplicate page
			$duplicatePage = "insert into FLC_PAGE (PAGEID,MENUID,".implode(',',$pageColumns)." )
								select ".$maxPageIdRs.", ".$toMenu.",".implode(',',$pageColumns)."
								from FLC_PAGE
								where PAGEID = ".$fromPage;
			$duplicatePageRs = $this->dbQuery->query($duplicatePage,'RUN');

			//if page duplicated
			if($duplicatePageRs)
			{
				//======= duplicate component =======
				//get component
				$relatedComponent = $this->getComponentIdByPageId($fromPage);
				$relatedComponentCount = count($relatedComponent);

				//duplicate all related component
				for($x=0; $x<$relatedComponentCount; $x++)
					$duplicateComponentRs = $this->duplicateComponent($relatedComponent[$x], $maxPageIdRs);
				//===== eof duplicate component =====

				//======= duplicate control =======
				//get control
				$relatedControl = $this->getControlIdByPageIdExcludeComponentBind($fromPage);
				$relatedControlCount = count($relatedControl);

				//duplicate all related control
				for($x=0; $x<$relatedControlCount; $x++)
					$duplicateControlRs = $this->duplicateControl($relatedControl[$x], $maxPageIdRs);
				//===== eof duplicate control =====

				//======= duplicate trigger =======
				//get trigger
				$relatedTrigger = $this->getTriggerId('page', $fromPage);
				$relatedTriggerCount = count($relatedTrigger);

				//duplicate all related trigger
				for($x=0; $x<$relatedTriggerCount; $x++)
					$duplicateTriggerRs = $this->duplicateTrigger($relatedTrigger[$x], $maxPageIdRs);
				//===== eof duplicate trigger =====

				//return the generated page id
				return $maxPageIdRs;
			}//eof if
		}//eof if
	}//eof function

	//delete page by page id
	public function deletePage($pageId)
	{
		//if have pageId
		if($pageId)
		{
			//check array size
			if(is_array($pageId) && count($pageId)==1)
				$pageId = $pageId[0];

			//if array
			if(is_array($pageId))
				$constraint = "where PAGEID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$pageId)." as col ".$this->fromDual().") a )";
				//$constraint = "where PAGEID in (select ".implode($this->fromDual()." union select ",$pageId).$this->fromDual().")";
			else
				$constraint = "where PAGEID = ".$pageId;

			//delete page
			$deletePage = "delete from FLC_PAGE ".$constraint;
			$deletePageRs = $this->dbQuery->query($deletePage,'RUN');

			//page deleted
			if($deletePageRs)
			{
				//get list of everything related to page
				$relatedComponentId = $this->getComponentIdByPageId($pageId);				//component
				$relatedControlId 	= $this->getControlIdByPageId($pageId);					//control
				$relatedTriggerId	= $this->getTriggerId('page', $pageId);					//trigger

				//delete everything related to page
				$deleteComponent 	= $this->deleteComponent($relatedComponentId);			//component
				$deleteControl 		= $this->deleteControl($relatedControlId);				//control
				$deleteTrigger 		= $this->deleteTrigger($relatedTriggerId);				//trigger
			}//eof if
		}//eof if

		return $deletePageRs;
	}//eof function
//========================================== eof page ===============================================

//========================================= component ===============================================
	//get list of FLC_PAGE_COMPONENT columns
	public function getComponentColumns()
	{
		//get list of FLC_PAGE_COMPONENT columns
		$getComponentColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_PAGE_COMPONENT','COMPONENTID,PAGEID');
		$getComponentColumnRsCount = count($getComponentColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getComponentColumnRsCount; $x++)
			$componentColumns[] = $getComponentColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $componentColumns;
	}//eof function

	//get list of component id by page id
	public function getComponentIdByPageId($pageId)
	{
		//if have pageId
		if($pageId)
		{
			//if array
			if(is_array($pageId))
				$constraint = "where PAGEID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$pageId)." as col ".$this->fromDual().") a )";
				//$constraint = "where PAGEID in (select ".implode($this->fromDual()." union select ",$pageId).$this->fromDual().")";
			else
				$constraint = "where PAGEID = ".$pageId;

			//get related component id
			$getComponentId = "select COMPONENTID from FLC_PAGE_COMPONENT ".$constraint;
			$getComponentIdRs = $this->dbQuery->query($getComponentId);
			$getComponentIdRsCount = count($getComponentIdRs);

			//get list of component
			for($x=0; $x<$getComponentIdRsCount; $x++)
				$relatedComponentId[] = $getComponentIdRs[$x][0];
		}//eof if

		return $relatedComponentId;
	}//eof function

	//duplicate component
	public function duplicateComponent($fromComponent, $toPage)
	{
		//if have fromComponent and toPage
		if($fromComponent && $toPage)
		{
			//get max component id
			$maxComponentIdRs = $this->maxValue('FLC_PAGE_COMPONENT','COMPONENTID',0)+1;

			//get list of columns
			$componentColumns = $this->getComponentColumns();

			//duplicate component
			$duplicateComponent = "insert into FLC_PAGE_COMPONENT (COMPONENTID,PAGEID,".implode(',',$componentColumns)." )
									select ".$maxComponentIdRs.", ".$toPage.",".implode(',',$componentColumns)."
									from FLC_PAGE_COMPONENT
									where COMPONENTID = ".$fromComponent;
			$duplicateComponentRs = $this->dbQuery->query($duplicateComponent,'RUN');

			//if component duplicated
			if($duplicateComponentRs)
			{
				//======= duplicate item =======
				//get items
				$relatedItems = $this->getItemIdByComponentId($fromComponent);
				$relatedItemsCount = count($relatedItems);

				//duplicate all related items
				for($x=0; $x<$relatedItemsCount; $x++)
					$duplicateItemsRs = $this->duplicateItem($relatedItems[$x], $maxComponentIdRs);
				//===== eof duplicate item =====

				//======= duplicate control =======
				//get control
				$relatedControl = $this->getControlIdByComponentId($fromComponent);
				$relatedControlCount = count($relatedControl);

				//duplicate all related control
				for($x=0; $x<$relatedControlCount; $x++)
					$duplicateControlRs = $this->duplicateControl($relatedControl[$x], $toPage, $maxComponentIdRs);
				//===== eof duplicate control =====

				//======= duplicate trigger =======
				//get trigger
				$relatedTrigger = $this->getTriggerId('component', $fromComponent);
				$relatedTriggerCount = count($relatedTrigger);

				//duplicate all related trigger
				for($x=0; $x<$relatedTriggerCount; $x++)
					$duplicateTriggerRs = $this->duplicateTrigger($relatedTrigger[$x], $maxComponentIdRs);
				//===== eof duplicate trigger =====

				//duplicate permission
				$duplicatePermissionRs = $this->duplicatePermission('component', $fromComponent, $maxComponentIdRs);

				//return the generated component id
				return $maxComponentIdRs;
			}//eof if
		}//eof if
	}//eof function

	//delete component by component id
	public function deleteComponent($componentId)
	{
		//if have componentId
		if($componentId)
		{
			//check array size
			if(is_array($componentId) && count($componentId)==1)
				$componentId = $componentId[0];

			//if array
			if(is_array($componentId))
				$constraint = "where COMPONENTID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$componentId)." as col ".$this->fromDual().") a )";
				//$constraint = "where COMPONENTID in (select ".implode($this->fromDual()." union select ",$componentId).$this->fromDual().")";
			else
				$constraint = "where COMPONENTID = ".$componentId;

			//delete component
			$deleteComponent = "delete from FLC_PAGE_COMPONENT ".$constraint;
			$deleteComponentRs = $this->dbQuery->query($deleteComponent,'RUN');

			//component deleted
			if($deleteComponentRs)
			{
				//get list of everything related to component
				$relatedItemId 		= $this->getItemIdByComponentId($componentId);			//item
				$relatedControlId 	= $this->getControlIdByComponentId($componentId);		//control
				$relatedTriggerId	= $this->getTriggerId('component', $componentId);		//trigger

				//delete everything related to component
				$deleteItem 		= $this->deleteItem($relatedItemId);					//item
				$deleteControl 		= $this->deleteControl($relatedControlId);				//control
				$deleteTrigger 		= $this->deleteTrigger($relatedTriggerId);				//trigger
				$deletePermission	= $this->deletePermission('component', $componentId);	//permission
			}//eof if
		}//eof if

		return $deleteComponentRs;
	}//eof function
//======================================= eof component =============================================

//============================================ item =================================================
	//get list of FLC_PAGE_COMPONENT_ITEMS columns
	public function getItemColumns()
	{
		//get list of FLC_PAGE_COMPONENT_ITEMS columns
		$getItemColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_PAGE_COMPONENT_ITEMS','ITEMID,COMPONENTID');
		$getItemColumnRsCount = count($getItemColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getItemColumnRsCount; $x++)
			$itemColumns[] = $getItemColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $itemColumns;
	}//eof function

	//get list of item id by component id
	public function getItemIdByComponentId($componentId)
	{
		//if have componentId
		if($componentId)
		{
			//if array
			if(is_array($componentId))
				$constraint = "where COMPONENTID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$componentId)." as col ".$this->fromDual().") a )";
				//$constraint = "where COMPONENTID in (select ".implode($this->fromDual()." union select ",$componentId).$this->fromDual().")";
			else
				$constraint = "where COMPONENTID = ".$componentId;

			//get related item id
			$getItemId = "select ITEMID from FLC_PAGE_COMPONENT_ITEMS ".$constraint;
			$getItemIdRs = $this->dbQuery->query($getItemId);
			$getItemIdRsCount = count($getItemIdRs);

			//get list of component
			for($x=0; $x<$getItemIdRsCount; $x++)
				$relatedItemId[] = $getItemIdRs[$x][0];
		}//eof if

		return $relatedItemId;
	}//eof function

	//duplicate item
	public function duplicateItem($fromItem, $toComponent='')
	{
		//if have fromItem
		if($fromItem)
		{
			//get max item id
			$maxItemIdRs = $this->maxValue('FLC_PAGE_COMPONENT_ITEMS','ITEMID',0)+1;

			//get list of columns
			$itemColumns = $this->getItemColumns();

			//if have toComponent
			if($toComponent)
				$componentID = $toComponent;
			else
				$componentID = 'COMPONENTID';

			//duplicate item
			$duplicateItem = "insert into FLC_PAGE_COMPONENT_ITEMS (ITEMID,COMPONENTID,".implode(',',$itemColumns)." )
								select ".$maxItemIdRs.", ".$componentID.", ".implode(',',$itemColumns)."
								from FLC_PAGE_COMPONENT_ITEMS
								where ITEMID = ".$fromItem;
			$duplicateItemRs = $this->dbQuery->query($duplicateItem,'RUN');

			//if item duplicated
			if($duplicateItemRs)
			{
				//======= duplicate trigger =======
				//get trigger
				$relatedTrigger = $this->getTriggerId('item', $fromItem);
				$relatedTriggerCount = count($relatedTrigger);

				//duplicate all related trigger
				for($x=0; $x<$relatedTriggerCount; $x++)
					$duplicateTriggerRs = $this->duplicateTrigger($relatedTrigger[$x], $maxItemIdRs);
				//===== eof duplicate trigger =====

				//duplicate chart
				$duplicateChartRs = $this->duplicateChart($fromItem, $maxItemIdRs);

				//duplicate permission
				$duplicatePermissionRs = $this->duplicatePermission('item', $fromItem, $maxItemIdRs);

				//return the generated item id
				return $maxItemIdRs;
			}//eof if
		}//eof if
	}//eof function

	//delete item by item id
	public function deleteItem($itemId)
	{
		//if have itemId
		if($itemId)
		{
			//check array size
			if(is_array($itemId) && count($itemId)==1)
				$itemId = $itemId[0];

			//if array
			if(is_array($itemId))
				$constraint = "where ITEMID in
					( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$itemId)." as col ".$this->fromDual().") a )";
				//$constraint = "where ITEMID in (select ".implode($this->fromDual()." union select ",$itemId).$this->fromDual().")";
			else
				$constraint = "where ITEMID = ".$itemId;

			//delete item
			$deleteItem = "delete from FLC_PAGE_COMPONENT_ITEMS ".$constraint;
			$deleteItemRs = $this->dbQuery->query($deleteItem,'RUN');

			//item deleted
			if($deleteItemRs)
			{
				//get list of everything related to item
				$relatedTriggerId	= $this->getTriggerId('item', $itemId);					//trigger

				//delete everything related to item
				$deleteTrigger 		= $this->deleteTrigger($relatedTriggerId);				//trigger
				$deleteChart 		= $this->deleteChart($itemId);							//chart
				$deletePermission	= $this->deletePermission('item', $itemId);				//permission
			}//eof if
		}//eof if

		return $deleteItemRs;
	}//eof function
//========================================== eof item ===============================================

//=========================================== chart =================================================
	//get list of FLC_CHART columns
	public function getChartColumns()
	{
		//get list of FLC_CHART columns
		$getChartColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_CHART','ITEM_ID');
		$getChartColumnRsCount = count($getChartColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getChartColumnRsCount; $x++)
			$chartColumns[] = $getChartColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $chartColumns;
	}//eof function

	//duplicate chart
	public function duplicateChart($fromItem, $toItem)
	{
		//if have fromItem and toItem
		if($fromItem && $toItem)
		{
			//get list of columns
			$chartColumns = $this->getChartColumns();

			//duplicate chart
			$duplicateChart = "insert into FLC_CHART (ITEM_ID,".implode(',',$chartColumns)." )
								select ".$toItem.", ".implode(',',$chartColumns)." from FLC_CHART where ITEM_ID = ".$fromItem;
			$duplicateChartRs = $this->dbQuery->query($duplicateChart,'RUN');
		}//eof if

		//return boolean result
		return $duplicateChartRs;
	}//eof function

	//delete chart by item id
	public function deleteChart($itemId)
	{
		//if have itemId
		if($itemId)
		{
			//check array size
			if(is_array($itemId) && count($itemId)==1)
				$itemId = $itemId[0];

			//if array
			if(is_array($itemId))
				$constraint = "where ITEM_ID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$itemId)." as col ".$this->fromDual().") a )";
				//$constraint = "where ITEM_ID in (select ".implode($this->fromDual()." union select ",$itemId).$this->fromDual().")";
			else
				$constraint = "where ITEM_ID = ".$itemId;

			//delete chart
			$deleteChart = "delete from FLC_CHART ".$constraint;
			$deleteChartRs = $this->dbQuery->query($deleteChart,'RUN');
		}//eof if

		return $deleteChartRs;
	}//eof function
//========================================= eof chart ===============================================

//========================================== control ================================================
	//get list of FLC_PAGE_CONTROL columns
	public function getControlColumns()
	{
		//get list of FLC_PAGE_CONTROL columns
		$getControlColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_PAGE_CONTROL','CONTROLID,PAGEID,COMPONENTID');
		$getControlColumnRsCount = count($getControlColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getControlColumnRsCount; $x++)
			$controlColumns[] = $getControlColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $controlColumns;
	}//eof function

	//get list of control id by page id
	public function getControlIdByPageId($pageId)
	{
		//if have pageId
		if($pageId)
		{
			//if array
			if(is_array($pageId))
				$constraint = "where PAGEID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$pageId)." as col ".$this->fromDual().") a )";
				//$constraint = "where PAGEID in (select ".implode($this->fromDual()." union select ",$pageId).$this->fromDual().")";
			else
				$constraint = "where PAGEID = ".$pageId;

			//get related control id
			$getControlId = "select CONTROLID from FLC_PAGE_CONTROL ".$constraint;
			$getControlIdRs = $this->dbQuery->query($getControlId);
			$getControlIdRsCount= count($getControlIdRs);

			//get list of control
			for($x=0; $x<$getControlIdRsCount; $x++)
				$relatedControlId[] = $getControlIdRs[$x][0];
		}//eof if

		return $relatedControlId;
	}//eof function

	//get list of control id by page id excluded the component binded
	public function getControlIdByPageIdExcludeComponentBind($pageId)
	{
		//if have pageId
		if($pageId)
		{
			//if array
			if(is_array($pageId))
				$constraint = "where PAGEID in
					( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$pageId)." as col ".$this->fromDual().") a )";
				//$constraint = "where PAGEID in (select ".implode($this->fromDual()." union select ",$pageId).$this->fromDual().")";
			else
				$constraint = "where PAGEID = ".$pageId;

			//get related control id
			$getControlId = "select CONTROLID from FLC_PAGE_CONTROL ".$constraint." and (COMPONENTID is null or COMPONENTID = 0)";
			$getControlIdRs = $this->dbQuery->query($getControlId);
			$getControlIdRsCount= count($getControlIdRs);

			//get list of control
			for($x=0; $x<$getControlIdRsCount; $x++)
				$relatedControlId[] = $getControlIdRs[$x][0];
		}//eof if

		return $relatedControlId;
	}//eof function

	//get list of control id by component id
	public function getControlIdByComponentId($componentId)
	{
		//if have componentId
		if($componentId)
		{
			//if array
			if(is_array($componentId))
				$constraint = "where COMPONENTID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$componentId)." as col ".$this->fromDual().") a )";
				//$constraint = "where COMPONENTID in (select ".implode($this->fromDual()." union select ",$componentId).$this->fromDual().")";
			else
				$constraint = "where COMPONENTID = ".$componentId;

			//get related control id
			$getControlId = "select CONTROLID from FLC_PAGE_CONTROL ".$constraint;
			$getControlIdRs = $this->dbQuery->query($getControlId);
			$getControlIdRsCount= count($getControlIdRs);

			//get list of control
			for($x=0; $x<$getControlIdRsCount; $x++)
				$relatedControlId[] = $getControlIdRs[$x][0];
		}//eof if

		return $relatedControlId;
	}//eof function

	//duplicate control
	public function duplicateControl($fromControl, $toPage, $toComponent='')
	{
		//if have fromControl and $toPage
		if($fromControl && $toPage)
		{
			//get max control id
			$maxControlIdRs = $this->maxValue('FLC_PAGE_CONTROL','CONTROLID',0)+1;

			//get list of columns
			$controlColumns = $this->getControlColumns();

			//if have toComponent
			if($toComponent)
				$componentId = $toComponent;
			else
				$componentId = 'COMPONENTID';

			//duplicate control
			$duplicateControl = "insert into FLC_PAGE_CONTROL (CONTROLID,PAGEID,COMPONENTID,".implode(',',$controlColumns)." )
								select ".$maxControlIdRs.",".$toPage.",".$componentId.",".implode(',',$controlColumns)."
								from FLC_PAGE_CONTROL
								where CONTROLID = ".$fromControl;
			$duplicateControlRs = $this->dbQuery->query($duplicateControl,'RUN');

			//if control duplicated
			if($duplicateControlRs)
			{
				//======= duplicate trigger =======
				//get trigger
				$relatedTrigger = $this->getTriggerId('control', $fromControl);
				$relatedTriggerCount = count($relatedTrigger);

				//duplicate all related trigger
				for($x=0; $x<$relatedTriggerCount; $x++)
					$duplicateTriggerRs = $this->duplicateTrigger($relatedTrigger[$x], $maxControlIdRs);
				//===== eof duplicate trigger =====

				//duplicate permission
				$duplicatePermissionRs = $this->duplicatePermission('control', $fromControl, $maxControlIdRs);

				//return the generated control id
				return $maxControlIdRs;
			}//eof if
		}//eof if
	}//eof function

	//delete control by control id
	public function deleteControl($controlId)
	{
		//if have controlId
		if($controlId)
		{
			//check array size
			if(is_array($controlId) && count($controlId)==1)
				$controlId = $controlId[0];

			//if array
			if(is_array($controlId))
				$constraint = "where CONTROLID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$controlId)." as col ".$this->fromDual().") a )";
				//$constraint = "where CONTROLID in (select ".implode($this->fromDual()." union select ",$controlId).$this->fromDual().")";
			else
				$constraint = "where CONTROLID = ".$controlId;

			//delete control
			$deleteControl = "delete from FLC_PAGE_CONTROL ".$constraint;
			$deleteControlRs = $this->dbQuery->query($deleteControl,'RUN');

			//control deleted
			if($deleteControlRs)
			{
				//get list of everything related to control
				$relatedTriggerId	= $this->getTriggerId('control', $controlId);			//trigger

				//delete everything related to control
				$deleteTrigger 		= $this->deleteTrigger($relatedTriggerId);				//trigger
				$deletePermission	= $this->deletePermission('control', $controlId);		//permission
			}//eof if
		}//eof if

		return $deleteControlRs;
	}//eof function
//======================================== eof control ==============================================

//========================================== trigger ================================================
	//get list of FLC_TRIGGER columns
	public function getTriggerColumns()
	{
		//get list of FLC_TRIGGER columns
		$getTriggerColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_TRIGGER','TRIGGER_ID,TRIGGER_ITEM_ID');
		$getTriggerColumnRsCount = count($getTriggerColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getTriggerColumnRsCount; $x++)
			$triggerColumns[] = $getTriggerColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $triggerColumns;
	}//eof function

	//get list of trigger id by item type and item id
	public function getTriggerId($itemType, $itemId)
	{
		//if have itemType and itemId
		if($itemType && $itemId)
		{
			//if array
			if(is_array($itemId))
				$constraint = "and TRIGGER_ITEM_ID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$itemId)." as col ".$this->fromDual().") a )";
				//$constraint = "and TRIGGER_ITEM_ID in (select ".implode($this->fromDual()." union select ",$itemId).$this->fromDual().")";
			else
				$constraint = "and TRIGGER_ITEM_ID = ".$itemId;

			//get related trigger id
			$getTriggerId = "select TRIGGER_ID from FLC_TRIGGER where TRIGGER_ITEM_TYPE = '".$itemType."' ".$constraint;
			$getTriggerIdRs = $this->dbQuery->query($getTriggerId);
			$getTriggerIdRsCount= count($getTriggerIdRs);

			//get list of trigger
			for($x=0; $x<$getTriggerIdRsCount; $x++)
				$relatedTriggerId[] = $getTriggerIdRs[$x][0];
		}//eof if

		return $relatedTriggerId;
	}//eof function

	//duplicate trigger
	public function duplicateTrigger($fromTrigger, $toItem)
	{
		//if have fromTrigger and toItem
		if($fromTrigger && $toItem)
		{
			//get max trigger id
			$maxTriggerIdRs = $this->maxValue('FLC_TRIGGER','TRIGGER_ID',0)+1;

			//get list of columns
			$triggerColumns = $this->getTriggerColumns();

			//duplicate trigger
			$duplicateTrigger = "insert into FLC_TRIGGER (TRIGGER_ID,TRIGGER_ITEM_ID,".implode(',',$triggerColumns)." )
								select ".$maxTriggerIdRs.",".$toItem.",".implode(',',$triggerColumns)."
								from FLC_TRIGGER
								where TRIGGER_ID = ".$fromTrigger;
			$duplicateTriggerRs = $this->dbQuery->query($duplicateTrigger,'RUN');

			//trigger duplicated
			if($duplicateTriggerRs)
			{
				//duplicate trigger parameter
				$duplicateTriggerParameter = "insert into FLC_TRIGGER_PARAMETER (TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
												select ".$maxTriggerIdRs.", PARAMETER_SEQ, PARAMETER_VALUE
												from FLC_TRIGGER_PARAMETER
												where TRIGGER_ID = ".$fromTrigger;
				$duplicateTriggerParameterRs = $this->dbQuery->query($duplicateTriggerParameter,'RUN');
			}//eof if
		}//eof if

		//return boolean result
		return $duplicateTriggerRs;
	}//eof function

	//delete trigger by trigger id
	public function deleteTrigger($triggerId)
	{
		//if have triggerId
		if($triggerId)
		{
			//check array size
			if(is_array($triggerId) && count($triggerId)==1)
				$triggerId = $triggerId[0];

			//if array
			if(is_array($triggerId))
				$constraint = "where TRIGGER_ID in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$triggerId)." as col ".$this->fromDual().") a )";
				//$constraint = "where TRIGGER_ID in (select ".implode($this->fromDual()." union select ",$triggerId).$this->fromDual().")";
			else
				$constraint = "where TRIGGER_ID = ".$triggerId;

			//delete trigger
			$deleteTrigger = "delete from FLC_TRIGGER ".$constraint;
			$deleteTriggerRs = $this->dbQuery->query($deleteTrigger,'RUN');

			//if trigger deleted
			if($deleteTriggerRs)
			{
				//delete trigger parameter
				$deleteTriggerParameter = "delete from FLC_TRIGGER_PARAMETER ".$constraint;
				$deleteTriggerParameterRs = $this->dbQuery->query($deleteTriggerParameter,'RUN');
			}//eof if
		}//eof if

		return $deleteTriggerRs;
	}//eof function
//======================================== eof trigger ==============================================

//======================================== permission ===============================================
	//get list of FLC_PERMISSION columns
	public function getPermissionColumns()
	{
		//get list of FLC_PERMISSION columns
		$getPermissionColumnRs = $this->listTableColumn(DB_DATABASE,DB_USERNAME,'FLC_PERMISSION','PERM_ID,PERM_ITEM');
		$getPermissionColumnRsCount = count($getPermissionColumnRs);

		//loop on count of table columns
		for($x=0; $x<$getPermissionColumnRsCount; $x++)
			$permissionColumns[] = $getPermissionColumnRs[$x]['COLUMN_NAME'];		//the column name

		return $permissionColumns;
	}//eof function

	//get the sql to find permission by item and userid
	public function getPermissionSQL($itemType, $userId, $role=false)
	{
		if($role === false)
		{
			$returnSQL = "select distinct PERM_ITEM from FLC_PERMISSION where PERM_TYPE = '".$itemType."' and GROUP_ID in
							(select GROUP_ID FROM FLC_USER_GROUP_MAPPING where USER_ID = ".$userId.")";
		}
		else
		{
			$returnSQL = "select distinct PERM_ITEM from FLC_PERMISSION where PERM_TYPE = '".$itemType."' and GROUP_ID in
							(select GROUP_ID FROM FLC_USER_GROUP_MAPPING where USER_ID = ".$userId." and GROUP_ID = ".$role.")";
		}
		return $returnSQL;
	}//eof function

	//get non-selected group for permission
	public function getUserGroupPermissionNonSelected($itemType='' ,$itemId='')
	{
		//if receive type and id
		if($itemType && $itemId)
			$extra .= "where GROUP_ID not in
							(select GROUP_ID from FLC_PERMISSION where PERM_TYPE = '".$itemType."' and PERM_ITEM = ".$itemId.")";

		//non-selected user group
		$sql = "select GROUP_ID, DESCRIPTION from FLC_USER_GROUP ".$extra." order by DESCRIPTION";
		return $this->dbQuery->query($sql);
	}//eof function

	//get selected group for permission
	public function getUserGroupPermissionSelected($itemType='' ,$itemId='')
	{
		//selected user group
		$sql = "select GROUP_ID, DESCRIPTION from FLC_USER_GROUP
				where GROUP_ID in
					(select GROUP_ID from FLC_PERMISSION where PERM_TYPE = '".$itemType."' and PERM_ITEM = ".$itemId.")
				order by DESCRIPTION";
		return $this->dbQuery->query($sql);
	}//eof function

	//duplicate permission
	public function duplicatePermission($itemType, $fromItem, $toItem)
	{
		//if have type, fromItem and toItem
		if($itemType && $fromItem && $toItem)
		{
			//get list of columns
			$permissionColumns = $this->getPermissionColumns();

			//duplicate permission
			$duplicatePermission = "insert into FLC_PERMISSION (PERM_ITEM,".implode(',',$permissionColumns)." )
								select ".$toItem.", ".implode(',',$permissionColumns)."
								from FLC_PERMISSION
								where PERM_TYPE = '".$itemType."' and PERM_ITEM = ".$fromItem;
			$duplicatePermissionRs = $this->dbQuery->query($duplicatePermission,'RUN');
		}//eof if

		//return boolean result
		return $duplicatePermissionRs;
	}//eof function

	//delete permission by type and id
	public function deletePermission($itemType, $itemId)
	{
		//if have itemId
		if($itemId)
		{
			//check array size
			if(is_array($itemId) && count($itemId)==1)
				$itemId = $itemId[0];

			//if array
			if(is_array($itemId))
				$constraint = "and PERM_ITEM in
								( select col from (select ".implode(" as col ".$this->fromDual()." union select ",$itemId)." as col ".$this->fromDual().") a )";
				//$constraint = "and PERM_ITEM in (select ".implode($this->fromDual()." union select ",$itemId).$this->fromDual().")";
			else
				$constraint = "and PERM_ITEM = ".$itemId;

			//delete permission
			$deletePermission = "delete from FLC_PERMISSION where PERM_TYPE = '".$itemType."' ".$constraint;
			$deletePermissionRs = $this->dbQuery->query($deletePermission,'RUN');
		}//eof if

		return $deletePermissionRs;
	}//eof function
//====================================== eof permission =============================================

//====================================== reference general ===========================================
	//get reference status
	public function status()
	{
		$sql="select REFERENCECODE, DESCRIPTION1
				from REFSYSTEM
				where MASTERCODE=
					(select REFERENCECODE from REFSYSTEM where DESCRIPTION1='REFERENCE_STATUS')";
		return $this->dbQuery->query($sql);
	}//eof function

	//get referenceid by referenceid or referencename filtered (permission) by userid
	public function getReferenceID($referenceid='', $referencename='', $userid='')
	{
		//check referenceid
		if($referenceid)
		{
			$constraintReferenceId = " and a.REFERENCEID = ".$referenceid;
		}//eof if

		//check referencename
		if($referencename)
		{
			$constraintReferenceName = " and upper(a.REFERENCENAME) = upper('".$referencename."')";
		}//eof if

		//if not admin (super-admin)
		if($userid && $userid!='1')
		{
			//select reference by permission
			$constraintUserId = " and a.REFERENCEID in (select REFERENCEID from SYSREFPERMISSION where GROUPID in
									(select GROUP_ID from FLC_USER_GROUP where GROUP_ID in
										(select GROUP_ID from FLC_USER_GROUP_MAPPING where USER_ID=".$userid.")))";
		}//eof if

		//select container
		$sql = "select a.REFERENCEID
					from SYSREFCONTAINER a
					where a.REFERENCESTATUSCODE='00' ".$constraintReferenceId.$constraintReferenceName.$constraintUserId;
		$tempResult=$this->dbQuery->query($sql);

		return $tempResult[0][0];
	}//eof function

	//get reference container
	public function reference($tablename,$userid='',$groupid='')
	{
		//if not admin (super-admin)
		if($userid&&$userid!='1')
		{
			//select reference by permission using userid
			$extra=" and a.REFERENCEID in (select REFERENCEID from SYSREFPERMISSION where GROUPID in
						(select GROUP_ID from FLC_USER_GROUP where GROUP_ID in
							(select GROUP_ID from FLC_USER_GROUP_MAPPING where USER_ID=".$userid.")))";
		}//eof if
		else if($groupid)
		{
			//select reference by permission using group id
			$extra=" and a.REFERENCEID in (select REFERENCEID from SYSREFPERMISSION where GROUPID =".$groupid.")";
		}//eof else if

		//get container / reference name
		$sql="select a.REFERENCEID, a.REFERENCETITLE, a.REFERENCENAME
				From SYSREFCONTAINER a, ".$tablename." b
				where a.REFERENCEID = b.REFERENCEID
                and a.REFERENCESTATUSCODE ='00' ".$extra."
				order by upper(a.REFERENCETITLE)";
		return $this->dbQuery->query($sql);
	}//eof function

	//get lookup table given field name and referenceid
	public function getLookupTableString($fieldname, $referenceid)
	{return "select ".$this->substring($fieldname,8)." as LOOKUP from SYSREFCONTAINER where REFERENCEID=".$referenceid;}//eof function

	//get group user non-selected
	public function getUserGroupNonSelected($referenceid='',$filter='')
	{
		//if receive referenceid
		if($referenceid)
			$extra.="where GROUP_ID not in (select GROUPID from SYSREFPERMISSION where REFERENCEID=".$referenceid.")";

		//non-selected user group
		$sql="select GROUP_ID, DESCRIPTION from FLC_USER_GROUP
				".$extra."
				order by DESCRIPTION";
		return $this->dbQuery->query($sql);
	}//eof function

	//get group user selected
	public function getUserGroupSelected($referenceid='',$filter='')
	{
		//selected user group
		$sql="select GROUP_ID, DESCRIPTION from FLC_USER_GROUP
				where GROUP_ID in (select GROUPID from SYSREFPERMISSION where REFERENCEID=".$referenceid.")
				order by DESCRIPTION";
		return $this->dbQuery->query($sql);
	}//eof function
//==================================== eof reference general =========================================

//============================================= get list ===================================================
	//get list of php event
	public function listPhpEvent()
	{
		//list of report
		$sql="select REFERENCECODE, DESCRIPTION1
				from REFSYSTEM
				where MASTERCODE =
					(select REFERENCECODE from REFSYSTEM where MASTERCODE = 'XXX' and DESCRIPTION1 = 'PHP_EVENT')
				order by DESCRIPTION1";
		return $this->dbQuery->query($sql);
	}//eof function

	//get list of javascript event
	public function listJsEvent()
	{
		//list of report
		$sql="select REFERENCECODE, DESCRIPTION1
				from REFSYSTEM
				where MASTERCODE =
					(select REFERENCECODE from REFSYSTEM where MASTERCODE = 'XXX' and DESCRIPTION1 = 'JS_EVENT')
				order by DESCRIPTION1";
		return $this->dbQuery->query($sql);
	}//eof function

	//get list of bl
	public function listPhpBL($filter='')
	{
		//list of bl
		$sql="select BLNAME
				from FLC_BL
				where upper(BLNAME) like upper('%".$filter."%') and BLTYPE='PHP'
				order by BLNAME";
		return $this->dbQuery->query($sql);
	}//eof function

	//get list of active bl
	public function listActivePhpBL($filter='')
	{
		//list of bl
		$sql="select BLNAME
				from FLC_BL
				where BLTYPE='PHP' and BLSTATUS='00' and upper(BLNAME) like upper('%".$filter."%') and BLPARENT is null
				order by BLNAME";
		return $this->dbQuery->query($sql);
	}//eof function

	//get list of javascript bl
	public function listJsBL($filter='')
	{
		//list of bl
		$sql="select BLNAME
				from FLC_BL
				where upper(BLNAME) like upper('%".$filter."%') and BLTYPE='JS'
				order by BLNAME";
		return $this->dbQuery->query($sql);
	}//eof function

	//get list of active javascript bl
	public function listActiveJsBL($filter='')
	{
		//list of bl
		$sql="select BLNAME
				from FLC_BL
				where upper(BLNAME) like upper('%".$filter."%') and BLTYPE='JS' and BLSTATUS='00' and BLPARENT is null
				order by BLNAME";
		return $this->dbQuery->query($sql);
	}//eof function

//******************************************** Dikemaskini oleh saleha pada 03-11-11 ******************************************************
	//get list of report static
	public function ADConn()
	{
		//list of report
		$sql="SELECT SYSTEM_NAME, LDAP_GROUP_NAME, LDAP_ENABLED, LDAP_TYPE, LDAP_CONNECTION, LDAP_PORT, LDAP_DN, LDAP_USERNAME, LDAP_PASSWORD, LDAP_FOLDER, LDAP_FILTER, LDAP_USER_ATT FROM FLC_CONFIG";
		return $this->dbQuery->query($sql);
	}//eof function

	// get data from table flc_config
	public function getListConfig()
	{
		// Select data from ldap_config
		$sql = "SELECT * FROM FLC_CONFIG ";
		return $this->dbQuery->query($sql);
	}

	//get field/column from selected table
	public function local_field($schema, $table)
	{
		$table=explode(".", trim($table));
		$table = $table[1];

		//$sql="SELECT COLUMN_NAME FROM USER_TAB_COLUMNS WHERE TABLE_NAME='$table'";
		$sql="SELECT COLUMN_NAME FROM ALL_TAB_COLUMNS WHERE OWNER = ANY ('".implode("','",explode(',',strtoupper($schema)))."') AND TABLE_NAME='$table' ";
		return $this->dbQuery->query($sql);
	}//eof function

	//get status from selected table
	public function flc_status()
	{
		$sql="SELECT STATUS_NAME FROM FLC_STATUS";
		return $this->dbQuery->query($sql);
	}//eof function


	public function getTable()
	{
		$sql="SELECT TABLE_NAME FROM TABS";
		return $this->dbQuery->query($sql);
	}

	//get attribute from lockup table
	public function getAttribute()
	{
		$sql="SELECT lower(ATTRIBUTE_NAME) FROM FLC_LDAP_ATTRIBUTE";
		return $this->dbQuery->query($sql);
	}

	public function getUserGroupAD($impexpType)
	{
		//selected user group
		$sql="SELECT GROUP_ID FROM FLC_CONFIG_LDAP_GROUP WHERE LDAP_IMPEXP_TYPE='$impexpType' ORDER BY GROUP_ID";
		return $this->dbQuery->query($sql);


	}//eof function getUserGroupAD

	public function getUserAD($impexpType)
	{
		//selected user group
		$sql="SELECT USER_ID, USER_PATH FROM FLC_CONFIG_LDAP_USER WHERE USER_IMPEXP_TYPE='$impexpType' ORDER BY USER_ID";
		return $this->dbQuery->query($sql);

	}//eof function getUserAD
	//******************************************* End of kemaskini Saleha **********************************************************

//=========================================== eof get list =================================================
}//eof class
?>
