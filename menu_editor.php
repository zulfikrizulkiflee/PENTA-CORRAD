<?php
require_once('system_prerequisite.php');

//validate that user have session
validateUserSession();

function resetOrdering($myQuery,$currLevel,$parent)
{
	if($currLevel == 0)
	{
		$topMenuRs = $myQuery->query("select MENUID,MENUPARENT from FLC_MENU where MENULEVEL = 1
										and MENUPARENT = 0
										order by MENUORDER",'SELECT','NAME');

		resetOrdering($myQuery,1,$topMenuRs);

	}
	else
	{
		$parentCnt = count($parent);

		for($x=0; $x < $parentCnt; $x++)
		{
			if($currLevel == 1)
				$parent[$x]['MENUID'] = 0;

			$newParentRs = $myQuery->query("select MENUID,MENUPARENT from FLC_MENU where MENULEVEL = ".$currLevel."
												and MENUPARENT = ".$parent[$x]['MENUID']."
												order by MENUORDER",'SELECT','NAME');
			$newParentRsCnt = count($newParentRs);

			//for all menu by that level, update the order one by one
			for($y=1; $y <= $newParentRsCnt; $y++)
				$myQuery->query("update FLC_MENU set MENUORDER = ".$y." where MENUID = ".$newParentRs[$y-1]['MENUID'],'RUN');

			resetOrdering($myQuery,$currLevel+1,$newParentRs);
		}
	}
}

//insert / update
if($_POST['insert'] || $_POST['update'])
{
	//increment order if option is after
	if($_POST['orderOption'] == '++')
		$_POST['menuOrder']++;

	//default value
	if(!$_POST['menuParent'])	$_POST['menuParent'] = 0;
	if(!$_POST['menuOrder'])	$_POST['menuOrder'] = 1;
	if(!$_POST['menuStatus'])	$_POST['menuStatus'] = 0;
}//eof if

//insert
if($_POST['insert'])
{
	//get max menuid
	$maxIdRs = $mySQL->maxValue('FLC_MENU','MENUID',0)+1;

	//set default name, if blank
	if(!$_POST['menuName'])
		$_POST['menuName'] = 'menu_'.$maxIdRs;

	//get parent's level
	$getParentLevel = "select MENULEVEL from FLC_MENU where MENUID = ".$_POST['menuParent'];
	$getParentLevelRs = $myQuery->query($getParentLevel,"SELECT","INDEX");
	$parentLevel = (int)$getParentLevelRs[0][0];

	//insert menu
	$insertMenu = "insert into FLC_MENU (MENUID, MENUNAME, MENUTITLE, MENUPARENT, MENULEVEL, MENUORDER, MENUTARGET, MENULINK, MENUSTATUS, 
						MENUNOTES, MENUHINTS, MENUSTATE, LINKTYPE)
					values (".$maxIdRs.", '".$_POST['menuName']."', '".$_POST['menuTitle']."', ".$_POST['menuParent'].", ".($parentLevel+1).",
						".$_POST['menuOrder'].", '".$_POST['menuTarget']."', '".$_POST['menuLink']."', '".$_POST['menuStatus']."',
						'".$_POST['menuNotes']."', '".$_POST['menuHints']."',".$_POST['menuState'].", '".$_POST['linkType']."')";
	$insertMenuRs = $myQuery->query($insertMenu,'RUN');

	//if menu inserted
	if($insertMenuRs)
	{
		//increment current menu order (within same level) if same
		$updateMenuOrder = "update FLC_MENU set MENUORDER = MENUORDER+1
							where MENUPARENT = ".$_POST['menuParent']." and MENUORDER = ".$_POST['menuOrder']."
							and MENUID != ".$maxIdRs;
		$updateMenuOrderRs = $myQuery->query($updateMenuOrder,'RUN');

		//notification
		showNotificationInfo('New menu has been added.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to add new menu!');
	}//eof else
}//eof if

//update
else if($_POST['update'])
{
	//set default name, if blank
	if(!$_POST['menuName'])
		$_POST['menuName'] = 'menu_'.$_POST['menuId'];

	//get parent's level
	$getParentLevel = "select MENULEVEL from FLC_MENU where MENUID = ".$_POST['menuParent'];
	$getParentLevelRs = $myQuery->query($getParentLevel,"SELECT","INDEX");
	$parentLevel = (int)$getParentLevelRs[0][0];

	//reset menu icon
	if($_POST['iconSetting']=='icon_default') $_POST['iconURL']='null';

	//update menu
	$updateMenu = "update FLC_MENU
					set MENUNAME = '".$_POST['menuName']."',
						MENUTITLE = '".$_POST['menuTitle']."',
						MENUPARENT = ".$_POST['menuParent'].",
						MENULEVEL = ".($parentLevel+1).",
						MENUORDER = ".$_POST['menuOrder'].",
						MENUTARGET = '".$_POST['menuTarget']."',
						MENULINK = '".$_POST['menuLink']."',
						MENUSTATUS = '".$_POST['menuStatus']."',
						MENUNOTES = '".$_POST['menuNotes']."',
						MENUHINTS = '".$_POST['menuHints']."',
						MENUSTATE = ".$_POST['menuState'].",
						LINKTYPE = '".$_POST['linkType']."',
						MENUICON = ".($_POST['iconURL']=='null' ? $_POST['iconURL'] : "'".$_POST['iconURL']."'")."
					where MENUID = ".$_POST['menuId']."";
	$updateMenuRs = $myQuery->query($updateMenu,'RUN');

	//if menu updated
	if($updateMenuRs)
	{
		//increment current menu order (within same level) if same
		$updateMenuOrder = "update FLC_MENU set MENUORDER = MENUORDER+1
							where MENUPARENT = ".$_POST['menuParent']." and MENUORDER = ".$_POST['menuOrder']."
								and MENUID != ".$_POST['menuId'];
		$updateMenuOrderRs = $myQuery->query($updateMenuOrder,'RUN');

		//notification
		showNotificationInfo('Menu has been updated.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to update menu!');
	}//eof else
}//eof if

//delete
else if($_POST['delete'])
{
	//delete menu and everything related to it
	$relatedMenuId 		= $mySQL->getMenuIdByMenuParent($_POST['menuId']);
	$deleteMenuRs 		= $mySQL->deleteMenu($relatedMenuId);

	//if menu deleted
	if($deleteMenuRs)
	{
		//notification
		showNotificationInfo('Menu has been deleted.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to delete menu!');
	}//eof else
}//eof if

//decrease order
else if($_POST['moveUp'])
{
	//check current ORDER position
	$checkPos = "select MENUORDER, MENUPARENT from FLC_MENU where MENUID = ".$_POST['menuId'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//if current position is NOT 1, move up
	if($checkPosRs[0]['MENUORDER'] != 1)
	{
		//check if theres another menu with same order, increase the overlapping menu order by 1
		$selectOverlapRs = $myQuery->query("select MENUID from FLC_MENU
												where MENUPARENT = ".$checkPosRs[0]['MENUPARENT']."
												and MENUORDER = ".($checkPosRs[0]['MENUORDER'] - 1),'SELECT','NAME');
		$selectOverlapRsCnt = count($selectOverlapRs);

		//update the overlapped menu
		for($x=0; $x < count($selectOverlapRs); $x++)
			$updatePos2Flag = $myQuery->query("update FLC_MENU
												set MENUORDER = MENUORDER + 1
												where MENUID = ".$selectOverlapRs[$x]['MENUID'],'RUN');

		$updatePosFlag = $myQuery->query("update FLC_MENU
											set MENUORDER = ".($checkPosRs[0]['MENUORDER'] - 1)."
											where MENUID = ".$_POST['menuId'],'RUN');
	}//eof if

	showNotificationInfo('The order for the selected menu has been moved up by one step.',2);

}//eof elseif

//increase order
else if($_POST['moveDown'])
{
	//check current ORDER position
	$checkPos = "select MENUORDER, MENUPARENT from FLC_MENU where MENUID = ".$_POST['menuId'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//check if theres another menu with same order, decrease the overlapping menu order by 1
	$selectOverlapRs = $myQuery->query("select MENUID from FLC_MENU
											where MENUPARENT = ".$checkPosRs[0]['MENUPARENT']."
											and MENUORDER = ".($checkPosRs[0]['MENUORDER'] + 1),'SELECT','NAME');
	$selectOverlapRsCnt = count($selectOverlapRs);

	//update the overlapped menu
	for($x=0; $x < $selectOverlapRsCnt; $x++)
		$updatePos2Flag = $myQuery->query("update FLC_MENU
											set MENUORDER = MENUORDER - 1
											where MENUID = ".$selectOverlapRs[$x]['MENUID'],'RUN');

	$updatePosFlag = $myQuery->query("update FLC_MENU
										set MENUORDER = ".($checkPosRs[0]['MENUORDER'] + 1)."
										where MENUID = ".$_POST['menuId'],'RUN');

	showNotificationInfo('The order for the selected menu has been moved down by one step.',2);
}

//if reset ordering button clicked
else if($_POST["resetOrdering"])
{
	resetOrdering($myQuery,0,array(0=>0));
	showNotificationInfo('Menu order has been resetted and reordered.',2);

}
//if reset ordering button clicked
else if($_POST["menuForceRefresh"])
{
	showNotificationInfo('Menu has been successfully refreshed!',2);
}

//new menu
else if($_POST['new'])
{
	//default values
	$menuLink = 'index.php?page=page_wrapper';
	$menuStatus = 1;

	//get max order
	$menuOrder = $mySQL->maxValue('FLC_MENU','MENUORDER',0,'MENUPARENT=0')+1;
}//eof if

//edit menu
else if($_POST['edit'])
{
	$menuInfo = "select * from FLC_MENU where MENUID = ".$_POST['menuId'];
	$menuInfoRs = $myQuery->query($menuInfo,'SELECT','NAME');
	$menuInfoRsCount = count($menuInfoRs);

	//assign variable
	$menuId = $menuInfoRs[0]['MENUID'];
	$menuName = $menuInfoRs[0]['MENUNAME'];
	$menuTitle = $menuInfoRs[0]['MENUTITLE'];
	$menuParent = $menuInfoRs[0]['MENUPARENT'];
	$menuOrder = $menuInfoRs[0]['MENUORDER'];
	$menuTarget = $menuInfoRs[0]['MENUTARGET'];
	$menuLink = $menuInfoRs[0]['MENULINK'];
	$menuStatus = $menuInfoRs[0]['MENUSTATUS'];
	$menuNotes = $menuInfoRs[0]['MENUNOTES'];
	$menuHints = $menuInfoRs[0]['MENUHINTS'];
	$menuState = $menuInfoRs[0]['MENUSTATE'];
	$linkType = $menuInfoRs[0]['LINKTYPE'];
}//eof if

//new / edit
if($_POST['new'] || $_POST['edit'])
{
	//menu target
	$target = "select REFERENCECODE, DESCRIPTION1 from REFSYSTEM
				where MASTERCODE = (select REFERENCECODE
									from REFSYSTEM
									where MASTERCODE = 'XXX'
									and DESCRIPTION1 = 'MENU_TARGET')";
	$targetRs = $myQuery->query($target,'SELECT','NAME');
	$targetRsCount = count($targetRs);

	//get all menu (recursively)
	$recursiveMenuList = $mySQL->getMenuList('',false);

	//list of available menu
	$menuList = assambleRecursiveMenu($recursiveMenuList);
	$menuListCount = count($menuList);

	//get max order
	for($x=0; $x<$menuListCount; $x++)
	{
		//get max order
		$maxOrderRs[$x]['MENUID'] = $menuList[$x]['MENUID'];
		$maxOrderRs[$x]['MAXORDER'] = $mySQL->maxValue('FLC_MENU','MENUORDER',0,'MENUPARENT='.$menuList[$x]['MENUID'])+1;
	}//eof for

	$maxOrderRsCount = count($maxOrderRs);
}//eof if

//show list
else if((!$_POST['new'] && !$_POST['edit']) || $_GET['filter_menu'])
{
	//filter
	$_GET['parentmenu'] = trim(str_replace('Search here..','',$_GET['parentmenu']));
	$_GET['title'] = trim(str_replace('Search here..','',$_GET['title']));

	//get all menu (recursively)
	$recursiveMenuList = $mySQL->getMenuList('',false);

	//list of available menu
	$menuList = assambleRecursiveMenu($recursiveMenuList);
	$menuListCount = count($menuList);

	//loop on count of menu
	for($x=0, $y=0; $x<$menuListCount; $x++)
	{
		//filter parent and title
		if(($_GET['parentmenu'] == '' || stripos($menuList[$x]['MENUBREADCRUMBS'],$_GET['parentmenu']) !== false) && ($_GET['title'] == '' ||stripos($menuList[$x]['MENUTITLE'],$_GET['title']) !== false))
		{
			//assign filtered menu
			$menuListRs[] = $menuList[$x];
		}//eof if
	}//eof for

	$menuListRsCount = count($menuListRs);
}//eof if

//TODO - LUQMAN
if($_POST['menuId'])
{
	//check menu icon
	if(TOP_MENU_ICON || SIDE_MENU_ICON) //sidebar
	{
		$queryGetIcon = "select MENUICON from FLC_MENU where MENUID = ".$_POST['menuId'];
		$resultGetIcon = $myQuery->query($queryGetIcon,'SELECT','INDEX');
		$resultGetIcon = $resultGetIcon[0][0];

		//child or parent (role)
		$queryGetRole = "select MENUID from FLC_MENU where MENUPARENT = ".$_POST['menuId'];
		$resultGetRole = $myQuery->query($queryGetRole,'SELECT','NAME');
		$menuRole = ($resultGetRole ? 'parent' : 'child');

		//if null, get default icon
		if($resultGetIcon=='' || $resultGetIcon==null)
		{
			$isIconDefault=true;
			$iconURL = ($menuRole=='parent' ? 'img/icon_default_folder.png' : 'img/icon_default_file.png');
		}
		else
		{
			$isIconDefault=false;
			$iconURL = $resultGetIcon;

			//if user choose to use custom icon but same url as default and same role, change it to default
			if($iconURL=='img/icon_default_folder.png' && $menuRole=='parent') $isIconDefault=true;
			if($iconURL=='img/icon_default_file.png' && $menuRole=='child') $isIconDefault=true;
		}
	}//eof if
}//eof if
?>

<script language="javascript" src="js/editor.js"></script>

<script>
function swapOrderType(type)
{
	if(type == 'text')
	{
		//enable text
		if(document.getElementById('menuOrderText'))
		{
			document.getElementById('menuOrderText').style.display = '';
			document.getElementById('menuOrderText').disabled = false;
		}

		if(document.getElementById('menuOrderCombo'))
		{
			//disable combo
			document.getElementById('menuOrderCombo').style.display = 'none';
			document.getElementById('menuOrderCombo').disabled = true;
		}
	}
	else
	{
		if(document.getElementById('menuOrderCombo'))
		{
			//enable combo
			document.getElementById('menuOrderCombo').style.display = '';
			document.getElementById('menuOrderCombo').disabled = false;
		}

		if(document.getElementById('menuOrderText'))
		{
			//disable text
			document.getElementById('menuOrderText').style.display = 'none';
			document.getElementById('menuOrderText').disabled = true;
		}
	}
}//eof function

//show max order by parent menu
function showMaxOrder(parentMenu)
{
	//switch parent menu
	switch(parentMenu)
	{
		<?php for($x=0; $x<$maxOrderRsCount; $x++){?>
		case '<?php echo $maxOrderRs[$x]['MENUID'];?>':
			document.getElementById('menuOrderText').value = '<?php echo $maxOrderRs[$x]['MAXORDER'];?>';
		break;
		<?php }?>
	}//eof switch
}//eof function

function toggleDefaultCustomIcon(elem)
{
	var id = jQuery(elem).attr('id');
	var role = jQuery('#menuRole').val();

	if(id == 'icon_default'){
		jQuery('#iconURL').prop('readonly',true);
		jQuery('#iconURL').val((role=='parent' ? 'img/icon_default_folder.png' : 'img/icon_default_file.png'));
	}
	else if(id == 'icon_custom'){
		jQuery('#iconURL').prop('readonly',false);
		jQuery('#iconURL').val('');
	}
}//eof function

function refreshIcon()
{
	var url = jQuery('#iconURL').val();
	jQuery('#previewIcon').attr('src',url);
}

function hoverTable()
{
	var tr = jQuery('#menu_editor_list table').find('tr');

	jQuery(tr).each(function(x){

		if(x > 0)
		{
			jQuery(this).on('mouseover',function(){
					jQuery(this).css('background-color','#FFFFCC')
			});

			if(x % 2 == 1)
			{
				jQuery(this).css('background-color','#F7F7F7');

				jQuery(this).on('mouseover',function(){
					jQuery(this).css('background-color','#F7F7F7')
				});
			}
			else
			{
				jQuery(this).on('mouseout',function(){
					jQuery(this).css('background-color','#FFFFFF');
				});
			}
		}
	});
}

function deleteFunction()
{
	jQuery('[name="delete"]').on('click',function(){
		if(window.confirm('Are you sure you want to DELETE this menu?\nThis will also delete ALL sub-menu (if any) under this menu.'))
			return true;
		else
			return false;
	});
}

</script>
<?php
//if ajax filter
if(!$_GET['filter_menu']){?>
<div id="breadcrumbs">System Administrator / Configuration / Menu Editor</div>
<h1>Menu Editor </h1>
<?php }?>

<?php if((!$_POST['new'] && !$_POST['edit']) || $_GET['filter_menu']){?>
<div id="menu_editor_list">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent flcEditorList">
	<tr>
	  <th colspan="10" style="padding:0px 2px 0px 10px;margin:0px;line-height:35px;">
		  <div style="float:left;">Menu List</div><div style="float:right;">
	  <form id="form2" name="form2" method="post" style="padding:0px; margin:0px;">
			<input id="gotoBottom" name="gotoBottom" type="button" class="inputButton" value="Go To Bottom" onclick="jQuery('html, body').animate({ scrollTop: jQuery(document).height() }, 1000);" />
			<input id="menuForceRefresh" name="menuForceRefresh" type="submit" class="inputButton" value="RELOAD Side Menu" />
            <input id="resetOrdering" name="resetOrdering" type="submit" class="inputButton" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the menu order?')) {return true;} else {return false;}" />
			<input id="new" name="new" type="submit" class="inputButton" value="New" />
		  </form>
	  </div></th>
	</tr>

	<tr>
	  <th width="15" class="listingHead">#</th>
	  <th width="20" class="listingHead">ID</th>
      <th width="100" class="listingHead">Name</th>
	  <th class="listingHead">Parent Menu <br><input type="text" class="inputInput" name="filter_parent" id="filter_parent" value="<?php if($_GET['parentmenu']) echo $_GET['parentmenu']; else echo ' Search here..';?>" onfocus="if(this.value == ' Search here..') this.value= ''; " onkeydown="if(event.keyCode == 13) this.onblur();" onblur="if(this.value == '') {this.value = ' Search here..';} filterMenuEditorList(this.value,document.getElementById('filter_title').value);" size="20" style="color:#7F7F7F;width:95%;" /></th>
	  <th class="listingHead">Title<br><input type="text" class="inputInput" name="filter_title" id="filter_title" value="<?php if($_GET['title']) echo $_GET['title']; else echo ' Search here..';?>" onfocus="if(this.value == ' Search here..') this.value= ''; " onkeydown="if(event.keyCode == 13) this.onblur();" onblur="if(this.value == '') {this.value = ' Search here..';} filterMenuEditorList(document.getElementById('filter_parent').value,this.value);" size="20" style="color:#7F7F7F;width:95%;" /></th>
      <th width="40" class="listingHead">Visible</th>
      <th width="40" class="listingHead">Public</th>
	  <th width="20" class="listingHead">Lvl</th>
	  <th width="20" class="listingHead">Ord</th>
	  <th width="85" class="listingHead">Action</th>
	</tr>
	<?php if($menuListRsCount){?>
	<?php for($x=0; $x < $menuListRsCount; $x++){?>
	<tr id="<?php echo $menuListRs[$x]['MENUID'];?>">
	  <td class="listingContent"><?php echo $x+1;?>.</td>
	  <td class="listingContent"><?php echo $menuListRs[$x]['MENUID'];?></td>
      <td class="listingContent"><?php echo $menuListRs[$x]['MENUNAME'];?></td>
	  <td class="listingContent"><?php if($menuListRs[$x]['MENUBREADCRUMBS']){echo $menuListRs[$x]['MENUBREADCRUMBS'];}else{echo '-';}?></td>
	  <td class="listingContent"><?php echo $menuListRs[$x]['MENUTITLE'];?></td>
	  <td class="listingContent"><?php if($menuListRs[$x]['MENUSTATUS']){echo 'Yes';}else{echo '-';}?></td>
	  <td class="listingContent"><?php if($menuListRs[$x]['LINKTYPE'] == 'P'){echo 'Yes';}else{echo '-';}?></td>
	  <td class="listingContent"><?php echo $menuListRs[$x]['MENULEVEL'];?></td>
	  <td class="listingContent"><?php echo $menuListRs[$x]['MENUORDER'];?></td>
	  <td nowrap="nowrap" class="listingContentRight">
          <form name="formDetail" method="POST">
			<input name="moveUp" type="submit" class="inputButton" value="&uarr;" />
			<input name="moveDown" type="submit" class="inputButton" value="&darr;" />
			<input name="edit" type="submit" class="inputButton" value="Update" />
			<input name="delete" type="submit" class="inputButton" value="Delete" />
			<input Name="menuId" type="hidden" value="<?php echo $menuListRs[$x]['MENUID'];?>" />
		  </form>
	  </td>
	</tr>
	<?php } //end for ?>
	<?php }//end if
	else { ?>
	<tr>
	  <td colspan="10" class="myContentInput">No Menu(s) found... </td>
	</tr>
	<?php } //end else?>
	<tr>
	  <td colspan="10" class="contentButtonFooter">
		  <form id="form2" name="form2" method="post">
			<input id="gotoBottom" name="gotoBottom" type="button" class="inputButton" value="Go To Top" onclick="jQuery('html, body').animate({ scrollTop: 0 }, 1000);" />
			<input id="menuForceRefresh" name="menuForceRefresh" type="submit" class="inputButton" value="RELOAD Side Menu" />
            <input id="resetOrdering" name="resetOrdering" type="submit" class="inputButton" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the menu order?')) {return true;} else {return false;}" />
			<input id="new" name="new" type="submit" class="inputButton" value="New" />
		  </form>
	  </td>
	</tr>
  </table>
</div>
<?php }?>

<?php if($_POST['new'] || $_POST['edit']){?>
<form id="form1" name="form1" method="post">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2"><?php if($_POST['edit']){?>Edit<?php }else{?>New<?php }?> Menu</th>
    </tr>
    <tr>
      <td class="inputLabel">Name : </td>
      <td>
      	<input id="menuName" name="menuName" type="text" class="inputInput" size="50" value="<?php echo $menuName;?>" onchange="trim(this);" />
        <input id="menuId" name="menuId" type="hidden" value="<?php echo trim($menuId);?>" />
        <script>document.getElementById('menuName').focus();</script>
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Title : </td>
      <td>
      	<input id="menuTitle" name="menuTitle" type="text" class="inputInput" size="100" value="<?php echo trim($menuTitle);?>" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Parent Menu : </td>
      <td>
        <select id="menuParent" name="menuParent" class="inputList" style="max-width:500px;" onchange="showMaxOrder(this.value); if(document.getElementById('menuOrderCombo') && document.getElementById('menuOrderCombo').style.display == ''){showMenu(this.value,'<?php echo $menuId;?>');}">
          <option value="0">None</option>
          <?php for($x=0; $x<$menuListCount; $x++){?>
          <option value="<?php echo $menuList[$x]['MENUID'];?>" <?php if($menuList[$x]['MENUID'] == $menuParent){?> selected="selected"<?php }?>>
		    <?php echo $menuList[$x]['MENUBREADCRUMBS'].$menuList[$x]['MENUTITLE'];?>
          </option>
          <?php } ?>
        </select>
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=menu&type=menu&lovValue=menuParent', 1, 1, 500, 500);" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Order : </td>
      <td>
        <label><input name="orderOption" id="orderOptionStart" type="radio" checked="checked" onclick="swapOrderType('text');" /> At</label>
        <label><input name="orderOption" type="radio" onclick="swapOrderType('combo'); showMenu(document.getElementById('menuParent').value,'<?php echo $menuId;?>');" /> Before</label>
        <label><input name="orderOption" type="radio" value="++" onclick="swapOrderType('combo'); showMenu(document.getElementById('menuParent').value,'<?php echo $menuId;?>');" />  After</label>
        <br />
        <input name="menuOrder" id="menuOrderText" type="text" class="inputInput" size="5" value="<?php echo $menuOrder;?>" />
        <span id="hideEditorList"></span>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Target : </td>
      <td>
        <select id="menuTarget" name="menuTarget" class="inputList">
          <option value="">&nbsp;</option>
          <?php for($x=0; $x<$targetRsCount; $x++){?>
          <option value="<?php echo $targetRs[$x]['REFERENCECODE'];?>" <?php if($targetRs[$x]['REFERENCECODE'] == $menuTarget){?> selected="selected"<?php }?>>
		    <?php echo $targetRs[$x]['REFERENCECODE'];?>
          </option>
          <?php }?>
        </select>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Link : </td>
      <td>
      	<input id="menuLink" name="menuLink" type="text" class="inputInput" size="100" value="<?php echo $menuLink;?>" />
        <br /><label class="labelNote">Note: For others, replace 'page_wrapper' with the file name </label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Visible : </td>
      <td>
        <label>
          <input name="menuStatus" id="menuStatus" type="checkbox" value="1" <?php if($menuStatus){?> checked="checked"<?php }?> />Yes
        </label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Public Access: </td>
      <td>
        <label>
          <input name="linkType" id="linkType" type="checkbox" value="P" <?php if($linkType == 'P'){?> checked="checked"<?php }?> />Allow
        </label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Default State : </td>
      <td>
        <label><input name="menuState" type="radio" value="0" <?php if((isset($menuState) && $menuState == '0') || !isset($menuState)){?> checked="checked"<?php }?> /> Hidden on startup</label>
        <label><input name="menuState" type="radio" value="1" <?php if(isset($menuState) && $menuState == '1'){?> checked="checked"<?php }?> /> Show</label>

      </td>
    </tr>
    <tr>
      <td class="inputLabel">Hints : </td>
      <td><textarea name="menuHints" cols="50" rows="3" class="inputInput" id="menuHints"><?php echo $menuHints;?></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Notes : </td>
      <td><textarea name="menuNotes" cols="50" rows="3" class="inputInput" id="menuNotes"><?php echo $menuNotes;?></textarea></td>
    </tr>
    <?php if(SIDE_MENU_ICON || TOP_MENU_ICON) { ?>
    <tr>
      <td class="inputLabel">Icon :</td>
      <td>
        <input onchange="toggleDefaultCustomIcon(this)" type="radio" <?php if($isIconDefault) echo ' checked '; ?> name="iconSetting" id="icon_default" value="icon_default"/><label for="icon_default">Default</label>
        <input onchange="toggleDefaultCustomIcon(this)" type="radio" <?php if(!$isIconDefault) echo ' checked '; ?> name="iconSetting" id="icon_custom" value="icon_custom"/><label for="icon_custom">Custom</label>
        &nbsp;&nbsp;[<span style="font-style:italic; font-size:7pt;">Size : <?php echo MENU_ICON_WIDTH ?>x<?php echo MENU_ICON_HEIGHT ?> px</span>]
        <br/>
        URL : <input type="text" <?php if($isIconDefault) echo ' readonly '; ?> name="iconURL" id="iconURL" class="inputInput" value="<?php echo $iconURL ?>" style="width:350px;"/>
        <br/>
        <hr style="border:none; border-top:1px dotted #A2A2A2;">
        Preview [<span style="font-size:7pt;"><a href="javascript:void(0)" onclick="refreshIcon()">refresh</a></span>]: <br/><img id="previewIcon" style="padding:10px;"src="<?php echo $iconURL ?>"/>
        <input type="hidden" id="menuRole" name="menuRole" value="<?php echo $menuRole; ?>"/>
      </td>
    </tr>
    <?php } ?>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <?php if($_POST['edit']){$btnName='update';}else{$btnName='insert';}?>
        <input id="<?php echo $btnName;?>" name="<?php echo $btnName;?>" type="submit" class="inputButton" value="Save" />
        <input id="close" name="close" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
</form>
<?php }?>
<script>
//highlight recently tocuhed menu, and scroll to the menu
jQuery('#<?php echo $_POST['menuId']?>').css('background-color','#E6E6E6');
//setTimeout(function(){jQuery('#<?php echo $_POST['menuId']?>').css('background-color','')},3000);
//jQuery('html, body').animate({scrollTop: jQuery("#<?php echo $_POST['menuId']?>").offset().top-100 }, 200);
</script>
<?php if((!$_POST['new'] && !$_POST['edit']) || $_GET['filter_menu']){?>
<script>
var allMenuTr = jQuery('#menu_editor_list table tr');
var startTD = 0;
var colToMerge = 3;
var allMenuTRRows = allMenuTr.length-1;

for(var x=2; x < allMenuTRRows; x++)
{
	if(x == 2)
		startTD = x;

	var parentMenuTD = jQuery(allMenuTr[startTD]).find('td').eq(colToMerge).html().replace(/\//g,'&#8260;');
	var nextParentMenuTD = jQuery(allMenuTr[x+1]).find('td').eq(colToMerge).html();

	if(nextParentMenuTD != null)
		nextParentMenuTD = nextParentMenuTD.replace(/\//g,'&#8260;');

	if(parentMenuTD == nextParentMenuTD)
	{
		jQuery(allMenuTr[x+1]).find('td').eq(colToMerge).hide();			//next parent menu td

		var originalRowspan = jQuery(allMenuTr[startTD]).find('td').eq(colToMerge).attr('rowspan');

		if(originalRowspan === undefined)
			originalRowspan = 1;
		else
			originalRowspan = parseInt(originalRowspan);

		jQuery(allMenuTr[startTD]).find('td')
			.eq(colToMerge)
			.attr('rowspan',originalRowspan+1)
			.css('vertical-align','top')
			.css('padding-top','10px');					//add colspan
	}
	else
		startTD = x+1;
}
</script>
<?php } ?>
<script>
//from resizeOverflowedReport()
var pageWidthRef = jQuery('#form1').width();
var allReport = jQuery('#content').find('.flcEditorList');			//detect all reports in content page
var allReportLen = allReport.length;

for(var x=0; x < allReportLen; x++)
{
	jQuery(allReport[x]).after('').appendTo('#menu_editor_list');

	jQuery(allReport[x]).parent()
		.css('width',pageWidthRef+'px')
		.css('position','relative')
		.css('overflow','auto')
		.css('margin-left','0px')
		.css('margin-bottom','10px');
}

hoverTable();
deleteFunction();
</script>
