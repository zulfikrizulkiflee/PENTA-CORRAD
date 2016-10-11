<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

//if group is edited or added
if($_POST['newCategory'] || $_POST['editCategory'])
{
	//get theme list
	$groupTheme = "select EXT_ID, EXT_TITLE from FLC_EXTENSION where EXT_TYPE = 'theme' and EXT_STATUS = 1 order by 2";
	$groupThemeRs = $myQuery->query($groupTheme,'SELECT','NAME');
	$groupThemeRsCnt = count($groupThemeRs);

	//get layout list
	$groupLayout = "select '1' as LAYOUT_ID, 'Left Menu' as LAYOUT_DESC ".$mySQL->fromDual()."
					union all
					select '2' as LAYOUT_ID, 'Right Menu' as LAYOUT_DESC ".$mySQL->fromDual()."
					union all
					select '3' as LAYOUT_ID, 'Top Menu' as LAYOUT_DESC ".$mySQL->fromDual()." ";
	$groupLayoutRs = $myQuery->query($groupLayout,'SELECT','NAME');
	$groupLayoutRsCnt = count($groupLayoutRs);
}

//if main category edit button clicked
if($_POST["code"] && $_POST["editCategory"])
{
	//show info of selected category
	$showCatInfo = "select * from FLC_USER_GROUP where GROUP_ID = ".$_POST["code"]." ";
	$showCatInfoRsRows = $myQuery->query($showCatInfo,'SELECT','NAME');
}

//if edit screen submitted
if($_POST["saveScreenEdit"])
{
	if($_POST['groupTheme'] == '')
		$_POST['groupTheme'] = '4';

	if($_POST['groupLayout'] == '')
		$_POST['groupLayout'] = '1';

	//update GROUP
	$updateCat = "update FLC_USER_GROUP set
					GROUP_CODE = '".$_POST['editCode']."',
					DESCRIPTION = '".$_POST["editName"]."',
					GROUP_LAYOUT = '".$_POST["groupLayout"]."',
					GROUP_THEME = '".$_POST["groupTheme"]."'
					where GROUP_ID = ".$_POST["code"];
	$updateCatRs = $myQuery->query($updateCat,'RUN');
}

//if new category added
else if($_POST["saveScreenNew"])
{
	if(trim($_POST['groupTheme']) == '')
		$_POST['groupTheme'] = '4';

	if(trim($_POST['groupLayout']) == '')
		$_POST['groupLayout'] = '1';

	//insert category
	$insertCat = "insert into FLC_USER_GROUP (GROUP_ID,GROUP_CODE,DESCRIPTION,GROUP_LAYOUT,GROUP_THEME,ADDED_BY,ADDED_DATE)
						values (".($mySQL->maxValue('FLC_USER_GROUP','GROUP_ID')+1).",'".$_POST['newCode']."', '".$_POST["newName"]."','".$_POST['groupLayout']."','".$_POST['groupTheme']."',
						".$_SESSION['userID'].",".$mySQL->currentDate().")";
	$insertCatRs = $myQuery->query($insertCat,'RUN');
}

//if category deleted
else if($_POST["deleteCategory"])
{
	$deleteCatRs 		= 	$myQuery->query("delete from FLC_USER_GROUP where GROUP_ID = ".$_POST["code"],'RUN');				//delete kumpulan
	$deleteCatChildRs 	= 	$myQuery->query("delete from FLC_USER_GROUP_MAPPING where GROUP_ID = ".$_POST["code"],'RUN');		//delete users under the group
	$deletePermissionRs = 	$myQuery->query("delete from FLC_PERMISSION where GROUP_ID = ".$_POST['code'],'RUN');				//delete permission under this group
}
//=====================================================================================================
//=========================================== USER - GROUP MAPPING ======================================

//=======================================================================================================
//=========================================== GROUP PERMISSION ==========================================
//if edit ke menu akses
else if($_POST['editPermission'])
{
	//get all menu (recursively)
	$recursiveMenuList = $mySQL->getMenuList($_POST['hiddenCode'],false);

	//root menu
	$menuRoot[0]['MENUID'] = $_POST['hiddenCode'];
	$menuRoot[0]['MENUTITLE'] = $_POST['hiddenMenuTitle'];
	$menuRoot[0]['MENUPARENT'] = 0;
	$menuRoot[0]['MENULEVEL'] = 0;

	//list of available menu
	$menuList = assambleRecursiveMenu($recursiveMenuList);

	//merge the array
	if($menuList)
		$menuList = array_merge($menuRoot, $menuList);
	else
		$menuList = $menuRoot;

	$menuListCount = count($menuList);

	//get previously configured permission
	$currentPermission = "select PERM_ITEM from FLC_PERMISSION where PERM_TYPE = 'menu' and GROUP_ID = ".$_POST['code'];
	$currentPermissionRs = $myQuery->query($currentPermission,'SELECT','INDEX');
	$currentPermissionRsCount = count($currentPermissionRs);
}//eof elseif

//if update menu permission screen button is clicked
else if($_POST['saveScreenRefEdit'])
{
	//prev permission
	$prevPermission = $_POST['menuPermissionHidden'];
	$prevPermissionCount = count($prevPermission);

	//loop on count of previous permission
	for($x=0; $x < $prevPermissionCount; $x++)
	{
		//delete previous permission
		$deletePermission = "delete from FLC_PERMISSION
								where PERM_TYPE = 'menu' and GROUP_ID = ".$_POST['code']." and PERM_ITEM = ".$prevPermission[$x];
		$deletePermissionRs = $myQuery->query($deletePermission,'RUN');
	}//eof for

	//new permission
	$newPermission = $_POST['menuPermission'];
	$newPermissionCount = count($newPermission);

	//loop on count of new permisison
	for($x=0; $x < $newPermissionCount; $x++)
	{
		//insert new permission
		$insertPermission = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM)
								values ('menu', ".$_POST['code'].", ".$newPermission[$x].")";
		$insertPermissionRs = $myQuery->query($insertPermission,'RUN');
	}//eof for

	//dummy
	$_POST['showScreen'] = true;
}//eof elseif

//if showScreen and code not null
if($_POST['showScreen'] && $_POST['code'])
{
	//for paging
	if(defined('USER_GROUP_MGMT_MAX_PER_PAGE'))
		$recPerPage = USER_GROUP_MGMT_MAX_PER_PAGE;
	else
		$recPerPage = 50;

	if(!isset($_POST['pageSelectHidden']) || $_POST['pageSelectHidden'] == '')
		$_POST['pageSelectHidden'] = 1;

	//get reference
	$reference = "select a.USERID, a.USERNAME, a.NAME, a.USERGROUPCODE, a.ADDED_DATE, b.USERNAME as ADDED_BY
					from
					(
						select a.USERID, a.USERNAME, a.NAME, a.USERGROUPCODE, c.ADDED_DATE, c.ADDED_BY, b.GROUP_ID, b.DESCRIPTION
						from PRUSER a, FLC_USER_GROUP b, FLC_USER_GROUP_MAPPING c
						where b.GROUP_ID = ".$_POST['code']."
						and a.USERID = c.USER_ID
						and b.GROUP_ID = c.GROUP_ID
					) a
					left join (select USERID,USERNAME from PRUSER) b on b.USERID = a.ADDED_BY
					where a.GROUP_ID = ".$_POST['code']."
					";
	$referenceRsArr = $myQuery->query($reference,'SELECT','NAME');
	$referenceRsArrCntOri = count($referenceRsArr);
	$refPageSize = ceil($referenceRsArrCntOri/$recPerPage);


	if(DBMS_NAME == 'oracle')
	{
		//added for paging
		$reference = "SELECT * FROM
					(
						SELECT a.*, rownum r__
						FROM
						(
							".$reference."
						) a
						WHERE rownum < ((".$_POST['pageSelectHidden']." * ".$recPerPage.") + 1 )
					)
					WHERE r__ >= (((".$_POST['pageSelectHidden']."-1) * ".$recPerPage.") + 1)
					order by USERNAME";
	}
	else if(DBMS_NAME == 'mysql')
	{
		$reference = "SELECT * FROM
				(
					".$reference."

				) a
				order by USERNAME
				limit ".($recPerPage*($_POST['pageSelectHidden']-1)).", ".$recPerPage."
				";
	}
	//TODO
	else if(DBMS_NAME == 'sybase_ASE')
	{
	}

	$referenceRsArr = $myQuery->query($reference,'SELECT','NAME');
	$referenceRsArrCnt = count($referenceRsArr);

	//get root menu
	$rootMenu = "select MENUID, MENUTITLE from FLC_MENU where MENUPARENT = 0 order by MENUORDER";
	$rootMenuRs = $myQuery->query($rootMenu,'SELECT','NAME');
	$rootMenuRsCount = count($rootMenuRs);

	//get list of total allowed and total denied
	for($x=0; $x < $rootMenuRsCount; $x++)
	{
		//default values
		$rootMenuRs[$x]['MENUALLOWED'] = 0;
		$rootMenuRs[$x]['MENUDENIED'] = 0;

		//get all sub menu (recursively)
		$recursiveMenuList = $mySQL->getMenuList($rootMenuRs[$x]['MENUID'],false);

		//root menu
		$menuRoot[0]['MENUID'] = $rootMenuRs[$x]['MENUID'];

		//list of available sub menu
		$menuList = assambleRecursiveMenu($recursiveMenuList);

		//merge the array
		if($menuList)
			$menuList = array_merge($menuRoot, $menuList);
		else
			$menuList = $menuRoot;

		$menuListCount = count($menuList);

		//loop on count of  menu
		for($y=0; $y < $menuListCount; $y++)
		{
			//check permission
			$checkPermission = "select '1' from FLC_PERMISSION
								where PERM_TYPE = 'menu'
								and GROUP_ID = ".$_POST['code']."
								and PERM_ITEM = ".$menuList[$y]['MENUID'];
			$checkPermissionRs = $myQuery->query($checkPermission,'SELECT');
			$checkPermissionRsCount = count($checkPermissionRs);

			if($checkPermissionRsCount)	$rootMenuRs[$x]['MENUALLOWED']++;
			else						$rootMenuRs[$x]['MENUDENIED']++;
		}//eof for
	}//eof for
}//eof if

//get list of groups
$general = "select GROUP_ID, DESCRIPTION, GROUP_CODE
			from FLC_USER_GROUP
			order by GROUP_CODE";
$generalRsArr = $myQuery->query($general,'SELECT','NAME');
$generalRsArrCnt = count($generalRsArr);

//notification
if($insertCatRs)
	showNotificationInfo('New user group successfully inserted.');
else if($updateCatRs)
	showNotificationInfo('User group successfully ppdated.');
else if($deleteRefRs)
	showNotificationInfo('User removed!');
else if($insertRefRs)
	showNotificationInfo('User group list updated.');
else if($deleteCatRs && $deleteCatChildRs && $deletePermissionRs)
	showNotificationInfo('Group Information Successfully Deleted.');
else if($insertPermissionRs)
	showNotificationInfo('Access Grant Successfully Update.');
?>
<script language="javascript">
function codeDropDown(elem)
{
	if(elem.selectedIndex != 0)
	{
		document.form1.showScreen.disabled = false;
		document.form1.editCategory.disabled = false;
		document.form1.deleteCategory.disabled = false;
	}
	else
	{
		document.form1.showScreen.disabled = true;
		document.form1.editCategory.disabled = true;
		document.form1.deleteCategory.disabled = true;
	}
}

//auto check / uncheck parent and child
function autoCheckBox(elem)
{
	autoCheckChildBox(elem);
	autoCheckParentBox(elem);
}//eof function

//auto check parent checkbox
function autoCheckParentBox(child)
{
	//if child checked
	if(child.checked)
	{
		//child id
		var childSplit = child.id.split('_');
		var parentId = childSplit[1];

		//all menu
		var menuPermission = document.getElementsByName('menuPermission[]');
		var menuPermissionCount = menuPermission.length;

		//loop on count of menu permission checkbox
		for(var x=menuPermissionCount-1; x>=0; x--)
		{
			var parentSplit = menuPermission[x].id.split('_');

			//if child belong to parent, copy parent checked condition
			if(parentId == parentSplit[2])
			{
				//set parent menu as checked
				menuPermission[x].checked = true;

				//re-execute (if have parent)
				autoCheckParentBox(menuPermission[x]);
			}//eof if
		}//eof for
	}//eof if
}//eof function

//auto check child checkbox
function autoCheckChildBox(parent)
{
	//parent id
	var parentSplit = parent.id.split('_');
	var parentId = parentSplit[2];

	//all menu
	var menuPermission = document.getElementsByName('menuPermission[]');
	var menuPermissionCount = menuPermission.length;

	//loop on count of menu permission checkbox
	for(var x=0; x < menuPermissionCount; x++)
	{
		//not parent menu
		if(parent.id != menuPermission[x].id)
		{
			var childSplit = menuPermission[x].id.split('_');

			//if child belong to parent
			if(parentId == childSplit[1])
			{
				//copy parent checked condition
				menuPermission[x].checked = parent.checked;

				//re-execute (if have child)
				autoCheckChildBox(menuPermission[x]);
			}//eof if
		}//eof if
	}//eof for
}//eof function

//ajax function to check whether keyed in kod kumpulan is already exist - NEW KUMPULAN
function ajaxCheckKodKumpulan(value)
{
	var url = 'user_group_mgmt_ajax_feeder.php';
	var params = 'id=ajaxCheckKodKumpulan&value=' + value;
	var ajax = new Ajax.Updater({success: 'newCodeDiv'},url,{method: 'get', parameters: params, onFailure: reportError});
}

//ajax function to check whether keyed in kod kumpulan is already exist - EDIT KUMPULAN
function ajaxCheckKodKumpulanEdit(value,originalValue)
{
	var url = 'user_group_mgmt_ajax_feeder.php';
	var params = 'id=ajaxCheckKodKumpulanEdit&value=' + value + '&originalValue=' + originalValue;
	var ajax = new Ajax.Updater({success: 'editCodeDiv'},url,{method: 'get', parameters: params, onFailure: reportError});
}

function confirmDelete(elem,username,userid)
{
	if(window.confirm('Anda pasti untuk mengeluarkan pengguna '+username+' ini dari kumpulan ini?'))
	{
		jQuery.get('user_group_mgmt_ajax_feeder.php?id=ajaxDeleteUser&userid=' + userid + '&groupid='+jQuery('#code').val(),
			function(data)
			{
				jQuery(elem).parent().parent().parent().remove();

				//reindex table
				var allTR = jQuery('#userListTable tr');

				for(var x=1; x < allTR.length; x++)
					jQuery(allTR[x]).find('td').eq(0).html(x+'.');
			});
	}
	else
		return false;
}

function ajaxFilter(elem)
{
	if(elem.value.length == 0 && event.which != 40)
		jQuery('#ajaxFilterResult').hide();
	else
	{
		//if(elem.value.length > 2 || event.which == 40)
		if(elem.value.length > 0 || event.which == 40)
		{
			jQuery.get('user_group_mgmt_ajax_feeder.php?id=ajaxFilterUser&val=' + elem.value + '&groupid='+jQuery('#code').val() + '&srcby='+jQuery("#ajaxSearchBy:checked").val(),
			function(data)
			{
				jQuery('#ajaxFilterResult').show();
				jQuery('#ajaxFilterResult').html(data);
			});
		}
	}
}

</script>
<script language="javascript" src="js/editor.js"></script>
<div id="breadcrumbs">User Administrator / User Group Access /</div>
<h1>User Group Access </h1>
<form action="" method="post" name="form1">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Group List</th>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Group : </td>
      <td><select name="code" class="inputList" id="code" onchange="codeDropDown(this);">
          <option value="">&lt; Select Group &gt;</option>
          <?php for($x=0; $x < $generalRsArrCnt; $x++) { ?>
          <option value="<?php echo $generalRsArr[$x]["GROUP_ID"]?>" <?php if($_POST["code"] == $generalRsArr[$x]["GROUP_ID"]) echo "selected";?>><?php echo $generalRsArr[$x]["GROUP_CODE"].' - '.$generalRsArr[$x]["DESCRIPTION"]?></option>
          <?php } ?>
        </select>
        <input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Show List" <?php if(!$_POST["code"]) { ?>disabled="disabled" <?php } ?> />
        <input type="hidden" name="pageSelectHidden" id="pageSelectHidden" />
        </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input id="menuForceRefresh" name="menuForceRefresh" type="submit" class="inputButton" value="RELOAD Side Menu" />
        <input name="newCategory" type="submit" class="inputButton" value="New" />
		<input name="editCategory" type="submit" class="inputButton" value="Edit" <?php if(!$_POST['code'] || $_POST['deleteCategory']){?>disabled="disabled" <?php }?> />
        <input name="deleteCategory" type="submit" class="inputButton" value="Delete" <?php if(!$_POST['code'] || $_POST['deleteCategory']){?>disabled="disabled" <?php }?> onClick="if(window.confirm('Delete this group?')) {return true} else {return false}" />
      </td>
    </tr>
  </table>
  <br>

  <?php if($_POST["newCategory"]){?>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">New group </th>
    </tr>
    <tr>
      <td class="inputLabel">Group Code : </td>
      <td>
        <div id="newCodeDiv">
          <input name="newCode" type="text" class="inputInput" id="newCode" size="26" maxlength="20" onchange="this.value = this.value.toUpperCase(); ajaxCheckKodKumpulan(this.value);" />
          <label class="labelMandatory">*</label>
        </div>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name :</td>
      <td>
        <input name="newName" type="text" class="inputInput" id="newName" size="53" maxlength="100">
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Theme :</td>
      <td>
       	<select class="inputList" style="padding:0px;margin:0px;" name="groupTheme" id="groupTheme" >
			<option value="">&nbsp;</option>
			<?php for($y=0; $y < $groupThemeRsCnt; $y++) { ?>
			<option value="<?php echo $groupThemeRs[$y]['EXT_ID'];?>"><?php echo $groupThemeRs[$y]['EXT_TITLE'];?></option>
			<?php } ?>
		</select>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Layout :</td>
      <td>
       	<select class="inputList" style="padding:0px;margin:0px;" name="groupLayout" id="groupLayout" >
			<option value="">&nbsp;</option>
			<?php for($y=0; $y < $groupLayoutRsCnt; $y++) { ?>
			<option value="<?php echo $groupLayoutRs[$y]['LAYOUT_ID'];?>"><?php echo $groupLayoutRs[$y]['LAYOUT_DESC'];?></option>
			<?php } ?>
		</select>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
          <input name="saveScreenNew" type="submit" class="inputButton" value="Save" onclick="if(jQuery('#newCode').val() == '' || jQuery('#newName').val() == '') { window.alert('Sila isikan maklumat yang wajib diisi!'); return false;} else return true;"  />
          <input name="cancelScreenNew" type="submit" class="inputButton" value="Cancel" />
      </td>
    </tr>
  </table>
  <?php }?>

  <?php if($_POST["editCategory"]){?>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Update group </th>
    </tr>
    <tr>
      <td class="inputLabel">Group Code : </td>
      <td>
        <div id="editCodeDiv">
          <input name="editCode" type="text" class="inputInput" id="editCode" value="<?php echo $showCatInfoRsRows[0]["GROUP_CODE"]?>" size="26" maxlength="20" onchange="this.value = this.value.toUpperCase(); ajaxCheckKodKumpulanEdit(this.value,jQuery('#hiddenEditCode').val());"  />
          <input type="hidden" name="hiddenEditCode" id="hiddenEditCode" value="<?php echo $showCatInfoRsRows[0]["GROUP_CODE"]?>" />
          <label class="labelMandatory">*</label>
        </div>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name :</td>
      <td>
        <input name="editName" type="text" class="inputInput" id="editName" value="<?php echo $showCatInfoRsRows[0]["DESCRIPTION"]?>" size="53" maxlength="100">
        <label class="labelMandatory">*</label>
      </td>
    </tr>
       <tr>
      <td class="inputLabel">Theme :</td>
      <td>
       	<select class="inputList" style="padding:0px;margin:0px;" name="groupTheme" id="groupTheme" >
			<option value="">&nbsp;</option>
			<?php for($y=0; $y < $groupThemeRsCnt; $y++) { ?>
			<option value="<?php echo $groupThemeRs[$y]['EXT_ID'];?>" <?php if($showCatInfoRsRows[0]["GROUP_THEME"] == $groupThemeRs[$y]['EXT_ID']) echo 'selected'; ?>><?php echo $groupThemeRs[$y]['EXT_TITLE'];?></option>
			<?php } ?>
		</select>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Layout :</td>
      <td>
       	<select class="inputList" style="padding:0px;margin:0px;" name="groupLayout" id="groupLayout" >
			<option value="">&nbsp;</option>
			<?php for($y=0; $y < $groupLayoutRsCnt; $y++) { ?>
			<option value="<?php echo $groupLayoutRs[$y]['LAYOUT_ID'];?>" <?php if($showCatInfoRsRows[0]["GROUP_LAYOUT"] == $groupLayoutRs[$y]['LAYOUT_ID']) echo 'selected'; ?>><?php echo $groupLayoutRs[$y]['LAYOUT_DESC'];?></option>
			<?php } ?>
		</select>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
          <input name="saveScreenEdit" type="submit" class="inputButton" id="saveScreenEdit" value="Save" onclick="if(jQuery('#editCode').val() == '' || jQuery('#editName').val() == '') { window.alert('Sila isikan maklumat yang wajib diisi!'); return false;} else return true;" />
          <input name="cancelScreenEdit" type="submit" class="inputButton" id="cancelScreenEdit" value="Cancel" />
      </td>
    </tr>
  </table>
  <?php }?>
  <?php if($_POST['editPermission']){?>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th>Menu Assignment Access : <?php echo strtoupper($_POST['hiddenMenuTitle']); ?></th>
    </tr>
    <?php for($x=0; $x < $menuListCount; $x++){?>
    <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1){?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php }else{?>onmouseout="this.style.background = '#ffffff'"<?php }?>>
	  <td class="listingContent">
	    <label style="display:block; cursor:pointer; padding-left:<?php echo $menuList[$x]['MENULEVEL']*20;?>px">
          <input type="checkbox" name="menuPermission[]" id="menuPermission_<?php echo $menuList[$x]['MENUPARENT'];?>_<?php echo $menuList[$x]['MENUID'];?>" value="<?php echo $menuList[$x]['MENUID'];?>" <?php for($y=0; $y<$currentPermissionRsCount; $y++){if($currentPermissionRs[$y][0]==$menuList[$x]['MENUID']){?> checked="checked"<?php }}?> onclick="autoCheckBox(this);" />
          <?php echo $menuList[$x]['MENUTITLE'];?>
          <input name="menuPermissionHidden[]" type="hidden" value="<?php echo $menuList[$x]['MENUID'];?>" />
        </label>
      </td>
	</tr>
    <?php }?>
    <tr>
      <td class="contentButtonFooter">
        <input name="selectAll" type="button" class="inputButton" id="selectAll" value="Select All" onclick="prototype_selectAllCheckbox()" />
        <input name="unselectAll" type="button" class="inputButton" id="unselectAll" value="Unselect All" onclick="prototype_unselectAllCheckbox()" />
        |
        <input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $_POST['hiddenCode'];?>" />
        <input name="saveScreenRefEdit" type="submit" class="inputButton" id="saveScreenRefEdit" value="Save" />
        <input name="cancelScreen" type="submit" class="inputButton" id="cancelScreen" value="Cancel" />
      </td>
    </tr>
  </table>
  <?php }?>
</form>

<!--user list-->
<?php if($_POST["showScreen"] && $_POST["code"] != ""){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="7">
		<div style="float:left">
			User List - <?php echo '('.number_format($referenceRsArrCntOri,0,'',',').')'; ?>
		</div>
		<div style="float:right">
			<em>Showing <?php echo $recPerPage ?> record(s) per page : </em>
			<select class="inputList" style="padding:0px;margin:0px;" name="pageSelect" id="pageSelect" onchange="jQuery('#pageSelectHidden').val(this.options[this.selectedIndex].value); jQuery('#showScreen').click();">
				<?php for($y=0; $y < $refPageSize; $y++) { ?>
				<option style="padding:0px;margin:0px;" value="<?php echo $y+1?>" <?php if($_POST['pageSelectHidden'] == ($y+1)) echo 'selected';?>>Page <?php echo $y+1?></option>
				<?php } ?>
			</select>
		</div>
    </th>
  </tr>
</table>
<?php if(count($referenceRsArr) > 10) { ?>
<div style="overflow:auto; height:300px;width:100%;">
<?php } ?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" id="userListTable">
  <?php if(count($referenceRsArr) > 0) { ?>
  <tr>
    <th width="15" class="listingHead">#</th>
    <th width="100" class="listingHead">User Name </th>
    <th class="listingHead">Full Name</th>
    <th width="100" class="listingHead">Group </th>
    <th width="80" class="listingHead">Date Update </th>
    <th width="80" class="listingHead">By</th>
    <th width="40" class="listingHead">Action</th>
    <!--<th width="10" class="listingHead"><input type="checkbox" /></th>-->
  </tr>
  <?php for($x=0; $x < $referenceRsArrCnt; $x++) { ?>
	  <!-- todo delete selected -->
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >

    <td class="listingContent"><?php echo (($_POST['pageSelectHidden']-1)*$recPerPage)+($x+1);?>.</td>
    <td class="listingContent"><?php echo $referenceRsArr[$x]["USERNAME"];?></td>
    <td class="listingContent"><?php echo $referenceRsArr[$x]["NAME"];?></td>
    <td class="listingContent"><?php echo $referenceRsArr[$x]["USERGROUPCODE"];?></td>
    <td class="listingContent"><?php echo $referenceRsArr[$x]["ADDED_DATE"];?></td>
    <td class="listingContent"><?php echo $referenceRsArr[$x]["ADDED_BY"];?></td>
    <td nowrap="nowrap" class="listingContentRight">
		<form id="formReference<?php echo $referenceRsArr[$x]["MENUID"];?>" name="formReference<?php echo $referenceRsArr[$x]["MENUID"];?>" method="post" action="">
			<input name="deleteUser" type="button" class="inputButton" id="deleteUser" value="Delete" onClick="if(confirmDelete(this,'<?php echo $referenceRsArr[$x]["USERNAME"];?>','<?php echo $referenceRsArr[$x]["USERID"]?>')) return true; else return false;"/>
			<input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $referenceRsArr[$x]["USERID"];?>" />
			<input name="code" type="hidden" id="code" value="<?php echo $_POST["code"];?>" />
		  </form>
	</td>
	<!--
	<td class="listingContent" style="text-align:center" onclick="var checkState = jQuery(this).children(0).attr('checked'); if(checkState == 'checked') jQuery(this).children(0).attr('checked',false); else jQuery(this).children(0).attr('checked','checked'); ">
		<input type="checkbox" onclick="

	var checkState = jQuery(this).attr('checked'); if(checkState == 'checked') jQuery(this).attr('checked',false); else jQuery(this).attr('checked','checked');

	" />
	</td>
	-->
  </tr>
  <?php 		} //end for ?>
  <?php 	}//end if
	else 	{ ?>
  <tr>
    <td colspan="7" class="myContentInput">&nbsp;User not found.. </td>
  </tr>
  <?php 	} //end else?>

</table>

<?php } ?>
<!--eof user list-->
<?php if(count($referenceRsArr) > 10){?>
</div>
<?php }?>
<?php if($_POST['showScreen'] && $_POST['code']){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="">
<tr>
    <td colspan="7" class="contentButtonFooter" style="">
        <form id="form2" name="form2" method="post" action="" >
          <div style="position:relative;">
			   <div id="userAssignDiv" style="position:fixed;top:50%;left:50%; margin:-100px 0 0 -100px;   -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.46); -moz-box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.46); box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.46); padding:7 10px 10px 10px; background-color: #ffffff; border: 5px solid #EBEBEB; display:none;text-align:left;background-color:white;padding:3px;">
					<div>Find user by <label><input type="radio" name="ajaxSearchBy" id="ajaxSearchBy" value="username" checked />Username</label><label><input type="radio" name="ajaxSearchBy" id="ajaxSearchBy" value="fullname" />Full Name</label></div>
					<input type="text" name="newUser" id="newUser" class="inputInput" style="width:360px;" onkeyup="ajaxFilter(this)" />
					<input type="button" name="newUserCancel" value="Close" class="inputButton" onclick="jQuery('#userAssignDiv').hide();jQuery('#ajaxFilterResult').html('')" />
					<input type="button" name="close_n_refresh" value="Close & Refresh" class="inputButton" onclick="jQuery('#showScreen').click();" />
					<div id="ajaxFilterResult" style="display:none; position:absolute;top:58px;left:7px;z-index:1000;width:521px;overflow:auto;min-height:10px;max-height:300px;"></div>
				</div>
			  <input name="code" type="hidden" id="code" value="<?php echo $_POST["code"];?>" />
			 <!-- <input name="deleteSelected" type="button" class="inputButton" id="deleteSelected" value="Delete Selected" style="color:yellow;" onclick="jQuery('#userAssignDiv').show(); jQuery('#newUser').focus();" />-->
			  <input name="userAssignment" type="button" class="inputButton" id="userAssignment" value="User Assignment" onclick="jQuery('#userAssignDiv').show(); jQuery('#newUser').focus();" />
          </div>
        </form>
    </td>
  </tr>
</table>
<br />
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="7">
      Summary List for Access to Menu - <span style="font-style:italic"><?php echo '('.$rootMenuRsCount; ?> parent menu(s))</span>
      </th>
  </tr>
</table>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <?php if($rootMenuRsCount){?>
  <tr>
    <th width="15" class="listingHead">#</th>
    <th class="listingHead">Main Menu</th>
    <th width="50" class="listingHead">Allowed</th>
    <th width="45" class="listingHead">Not Allowed</th>
    <th width="60" class="listingHead">Action</th>
  </tr>
  <?php for($x=0; $x<$rootMenuRsCount; $x++){?>
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
    <td class="listingContent"><?php echo $x+1;?>.</td>
    <td class="listingContent"><?php echo ucwords(strtolower($rootMenuRs[$x]['MENUTITLE']));?></td>
    <td class="listingContent"><div align="right" <?php if(!$rootMenuRs[$x]['MENUALLOWED']){?>style="color:#FF0000"<?php }?> ><?php echo $rootMenuRs[$x]['MENUALLOWED'];?>&nbsp;</div></td>
    <td class="listingContent"><div align="right"><?php echo $rootMenuRs[$x]['MENUDENIED'];?>&nbsp;</div></td>
    <td nowrap="nowrap" class="listingContentRight">
      <form id="formDetail" name="formDetail" method="post">
        <input name="editPermission" type="submit" class="inputButton" id="editPermission" value="Update" />
        <input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $rootMenuRs[$x]['MENUID'];?>" />
        <input name="hiddenMenuTitle" type="hidden" id="hiddenMenuTitle" value="<?php echo $rootMenuRs[$x]['MENUTITLE'];?>" />
        <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
      </form>
    </td>
  </tr>
  <?php 		} //end for ?>
  <?php 	}//end if
	else 	{ ?>
  <tr>
    <td colspan="5" class="myContentInput">&nbsp;Grant not found.. </td>
  </tr>
  <?php 	} //end else?>
</table>
<?php }?>