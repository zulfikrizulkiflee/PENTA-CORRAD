<?php
//include file for db conn and so on
require_once('system_prerequisite.php');

//=========================== ajax fareeda suggest updater ===============================
//if updater type is filter
if($_GET['updater'] == 'filter')
{
	//switch type
	switch($_GET['type'])
	{
		//type page
		case 'page':
			$generalRs = $mySQL->menu($_GET['value']);
		
			?>
			<select name="code" class="inputList" id="code" onChange="codeDropDown(this, 'showScreen|duplicatePage|modifyCategory|deleteCategory');">
			  <option value="">&lt; Pilih Page &gt;</option>
			  <?php for($x=0; $x < count($generalRs); $x++) { ?>
			  <option value="<?php echo $generalRs[$x]['PAGEID']?>" <?php if($_POST['code'] == $generalRs[$x]['PAGEID']) echo "selected";?>><?php echo $generalRs[$x]['MENUTITLE']?></option>
			  <?php } ?>
			</select>
			<input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Show List" <?php if(!$_POST['code']) { ?>disabled="disabled" <?php } ?> />
			[<?php echo count($generalRs);?> records]
			<?php 
		break;
		
		//type menu
		case 'menu':
			//check for undefine variable and unset if true
			if($_GET['select'] && $_GET['select']=='undefined')	unset($_GET['select']);
			
			//if modify page
			if($_GET['select'])
				$selectedPageId=$_GET['select'];
			
			//get list of page menu
			$getMenuRs = $mySQL->menuExcludePage($selectedPageId,$_GET['value']);
			
			?>
			<select name="<?php if($_GET['select']){?>editMenuID<?php }else{?>newMenuID<?php }?>" class="inputList" id="<?php if($_GET['select']){?>editMenuID<?php }else{?>newMenuID<?php }?>" onchange="document.getElementById('<?php if($_GET['select']){?>editPageBreadcrumbs<?php }else{?>newPageBreadcrumbs<?php }?>').value=this.options[this.selectedIndex].innerHTML">
			  <option value="">&nbsp;</option>
			  <?php for($x=0; $x < count($getMenuRs); $x++) { ?>
			  <option value="<?php echo $getMenuRs[$x]["MENUID"]?>" <?php if($getPageInfoRs[0]["MENUID"] == $getMenuRs[$x]["MENUID"]) echo "selected";?>><?php echo $getMenuRs[$x]["MENUTITLE"]?></option>
			  <?php } ?>
			</select>
			[<?php echo count($getMenuRs);?> records]
			<?php
		break;
	}//eof switch
}//eof if updater==filter
//========================= eof ajax fareeda suggest updater =============================

else if($_GET['checkUnique'])
{
	if($_GET['uniqueType'] == 'page')
	{
		if($_GET['mode'] == 'new')
		{
			$qryRs = $myQuery->query("select PAGENAME from FLC_PAGE where lower(PAGENAME) = '".strtolower($_GET['name'])."'",'SELECT','NAME');
			$qryRsCnt = count($qryRs);
			
			if($qryRsCnt > 0)
				echo "<script>window.alert('Page name already exists!');jQuery('#".$_GET['caller']."').val('').focus();</script>";
		}
		else if($_GET['mode'] == 'edit')
		{
			$qryRs = $myQuery->query("select PAGENAME from FLC_PAGE where lower(PAGENAME) = '".strtolower($_GET['name'])."'
										and lower(PAGENAME) <> lower('".$_GET['hiddencallerval']."')",'SELECT','NAME');
			$qryRsCnt = count($qryRs);
			
			if($qryRsCnt > 0)
				echo "<script>window.alert('Page name already exists!');jQuery('#".$_GET['caller']."').val('').focus();</script>";
		}
	}
	else if($_GET['uniqueType'] == 'component')
	{
		if($_GET['mode'] == 'new')
		{
			$qryRs = $myQuery->query("select COMPONENTNAME from FLC_PAGE_COMPONENT 
										where lower(COMPONENTNAME) = '".strtolower($_GET['name'])."'
										and PAGEID = ".$_GET['pageid'],'SELECT','NAME');
			$qryRsCnt = count($qryRs);
			
			if($qryRsCnt > 0)
				echo "<script>window.alert('Component name already exists in this page!');jQuery('#".$_GET['caller']."').val('').focus();</script>";
		}
		else if($_GET['mode'] == 'edit')
		{
			$qryRs = $myQuery->query("select COMPONENTNAME from FLC_PAGE_COMPONENT 
										where lower(COMPONENTNAME) = '".strtolower($_GET['name'])."'
										and lower(COMPONENTNAME) <> lower('".$_GET['hiddencallerval']."') 
										and PAGEID = ".$_GET['pageid'],'SELECT','NAME');
			$qryRsCnt = count($qryRs);
			
			if($qryRsCnt > 0)
				echo "<script>window.alert('Component name already exists!');jQuery('#".$_GET['caller']."').val('').focus();</script>";
		}
	}
	else if($_GET['uniqueType'] == 'item')
	{
		if($_GET['mode'] == 'new')
		{
			$qryRs = $myQuery->query("select ITEMNAME from FLC_PAGE_COMPONENT_ITEMS
										where lower(ITEMNAME) = '".strtolower($_GET['name'])."'
										and COMPONENTID in (select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$_GET['pageid'].")",'SELECT','NAME');
			$qryRsCnt = count($qryRs);
			
			if($qryRsCnt > 0)
				echo "<script>window.alert('Item name already exists in this page!');jQuery('#".$_GET['caller']."').val('').focus();</script>";
		}
		else if($_GET['mode'] == 'edit')
		{
			
			echo "select ITEMNAME from FLC_PAGE_COMPONENT_ITEMS
										where lower(ITEMNAME) = '".strtolower($_GET['name'])."' 
										and lower(ITEMNAME) <> lower('".$_GET['hiddencallerval']."') 
										and COMPONENTID in (select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$_GET['pageid'].")";
			$qryRs = $myQuery->query("select ITEMNAME from FLC_PAGE_COMPONENT_ITEMS
										where lower(ITEMNAME) = '".strtolower($_GET['name'])."' 
										and lower(ITEMNAME) <> lower('".$_GET['hiddencallerval']."') 
										and COMPONENTID in (select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$_GET['pageid'].")",'SELECT','NAME');
			$qryRsCnt = count($qryRs);
			
			if($qryRsCnt > 0)
				echo "<script>window.alert('Item name already exists in this page!');jQuery('#".$_GET['caller']."').val('').focus();</script>";
		}	
	}
}

else
{
	//================================== ajax cascade dropdown ====================================
	//switch between editor's mode
	switch($_GET['editor'])
	{
		//page editor
		case 'component':
			//show dropdown of component item
			if($_GET['page']&&$_GET['component'])
			{
				if($_GET['itemExcepted']>0)
					$extra="and b.ITEMID != ".$_GET['itemExcepted'];
				
				//get component item for current page
				$getComponentItem = "select b.ITEMORDER, b.ITEMNAME
								from FLC_PAGE_COMPONENT a, FLC_PAGE_COMPONENT_ITEMS b 
								where a.COMPONENTID = b.COMPONENTID 
								and a.PAGEID = ".$_GET['page']." and a.COMPONENTID = ".$_GET['component']." ".$extra."
								order by b.ITEMORDER";
				$getComponentItemRs = $myQuery->query($getComponentItem,'SELECT');
				$getComponentItemRsCount = count($getComponentItemRs);
				
				?>
				<select name="<?php if($_GET['itemExcepted']>0){?>editItemOrder<?php }else {?>newItemOrder<?php }?>" id="<?php if($_GET['itemExcepted']>0){?>editItemOrderCombo<?php }else {?>newItemOrderCombo<?php }?>" class="inputList">
					<option value="0">None</option>
					<?php for($x=0; $x < $getComponentItemRsCount; $x++) { ?>
					<option value="<?php echo $getComponentItemRs[$x][0];?>" ><?php echo $getComponentItemRs[$x][1];?></option>
					<?php } ?>
				</select>
				<?php
			}
			
			//show dropdown of component
			else if($_GET['page'])
			{
				if($_GET['componentExcepted']>0)
					$extra="and COMPONENTID != '".$_GET['componentExcepted']."'";
				
				//get component for current page
				$getComponent = "select COMPONENTORDER,COMPONENTNAME from FLC_PAGE_COMPONENT
									where PAGEID = ".$_GET['page']." ".$extra."
									order by COMPONENTORDER";
				$getComponentRs = $myQuery->query($getComponent,'SELECT');
				$getComponentRsCount = count($getComponentRs);
				
				?>
				<select name="<?php if($_GET['componentExcepted']>0){?>editComponentOrder<?php }else {?>newComponentOrder<?php }?>" id="<?php if($_GET['componentExcepted']>0){?>editComponentOrderCombo<?php }else {?>newComponentOrderCombo<?php }?>" class="inputList">
					<option value="0">None</option>
					<?php for($x=0; $x < $getComponentRsCount; $x++) { ?>
					<option value="<?php echo $getComponentRs[$x][0];?>" ><?php echo $getComponentRs[$x][1];?></option>
					<?php } ?>
				</select>
				<?php
			}
		break;
		
		//control editor
		case 'control':
			//show dropdown of control
			if($_GET['page'])
			{
				if($_GET['controlExcepted']>0)
					$extra="and b.CONTROLID != '".$_GET['controlExcepted']."'";
				
				//get component for current page
				$getControl = "select b.CONTROLORDER, b.CONTROLNAME 
									from FLC_PAGE a, FLC_PAGE_CONTROL b, REFSYSTEM c 
									where a.PAGEID = b.PAGEID and b.CONTROLTYPE = c.REFERENCECODE 
									and c.MASTERCODE = 
										(select REFERENCECODE from REFSYSTEM 
											where MASTERCODE = 'XXX' and DESCRIPTION1 = 'PAGE_CONTROL_TYPE') 
									and b.PAGEID = ".$_GET['page']." ".$extra."
									order by CONTROLORDER";
				$getControlRs = $myQuery->query($getControl,'SELECT');
				$getControlRsCount = count($getControlRs);
				
				?>
				<select name="<?php if($_GET['controlExcepted']>0){?>editControlOrder<?php }else {?>newControlOrder<?php }?>" id="<?php if($_GET['controlExcepted']>0){?>editControlOrderCombo<?php }else {?>newControlOrderCombo<?php }?>" class="inputList">
					<option value="0">None</option>
					<?php for($x=0; $x < $getControlRsCount; $x++) { ?>
					<option value="<?php echo $getControlRs[$x][0];?>" ><?php echo $getControlRs[$x][1];?></option>
					<?php } ?>
				</select>
				<?php
			}
		break;
		
		//menu editor
		case 'menu':
			if($_GET['menuExcepted']>0)
				$extra.=" and MENUID != ".$_GET['menuExcepted']."";
			
			//get component for current page
			$getMenu = "select MENUORDER,MENUTITLE 
							from FLC_MENU 
							where MENUPARENT = ".$_GET['menuParent']." ".$extra." 
							order by MENUORDER";
			$getMenuRs = $myQuery->query($getMenu,'SELECT');
			$getMenuRsCount = count($getMenuRs);
			?>
			<select name="menuOrder" id="menuOrderCombo" class="inputList">
			  <option value="0">None</option>
			  <?php for($x=0; $x<$getMenuRsCount; $x++){?>
			  <option value="<?php echo $getMenuRs[$x][0];?>"><?php echo $getMenuRs[$x][1];?></option>
			  <?php }?>
			</select>
			<?php
		break;
		
		//database mapping
		case 'database' :
			//show dropdown of databse mapping
			if($_GET['component'])
			{	
				//get table / view name to be mapped
				$getMappingTable = "select COMPONENTBINDINGTYPE, COMPONENTBINDINGSOURCE 
									from FLC_PAGE_COMPONENT where COMPONENTID = ".$_GET['component'];
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
				
				?>
				<select name="<?php if($_GET['mapping']>0){?>editMappingID<?php }else {?>newMappingID<?php }?>" class="inputList" id="<?php if($_GET['mapping']>0){?>editMappingID<?php }else {?>newMappingID<?php }?>" >
				  <option value="">&nbsp;</option>
				  <?php 
					for($x=0; $x < $getColumnsRsCount; $x++) { ?>
				  <option value="<?php echo $getColumnsRs[$x]['COLUMN_NAME']?>" <?php if($_GET['mapping'] == $getColumnsRs[$x]['COLUMN_NAME']) { ?> selected<?php }?> ><?php echo $getColumnsRs[$x]['COLUMN_NAME']?></option>
				  <?php } ?>
	
				</select>
				<?php
			}
		break;
	}//eof switch
	//================================ eof cascade dropdown ==================================
}//eof else
?>
