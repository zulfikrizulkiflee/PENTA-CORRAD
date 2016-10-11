<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();
?>
<script>
function hoverTable()
{
	var tr = jQuery('#files_list table').find('tr');

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
</script>
<div id="breadcrumbs">System Administrator / Configuration / System Monitoring</div>
<h1>System Monitoring</h1>

<form method="post" enctype="multipart/form-data" name="form1">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">What Do You Want To Do?</th>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Choose Action : </td>
      <td valign="top">
		  <select name="action" class="inputList" id="action" onChange="codeDropDown(this, 'showScreen');">
			  <option value="">&lt; Select Action &gt;</option>
			  <option value="phpinfo" <?php if($_POST['action'] == 'phpinfo') echo 'selected'; ?>>Get PHP Info</option>
			  <option value="sysinfo" <?php if($_POST['action'] == 'sysinfo') echo 'selected'; ?>>Get Server Info</option>
			  <option value="xdebug" <?php if($_POST['action'] == 'xdebug') echo 'selected'; ?>>Webgrind for Xdebug</option>
			  <option value="opcache" <?php if($_POST['action'] == 'opcache') echo 'selected'; ?>>Zend OPcache</option>
			  <option value="opcache_gui" <?php if($_POST['action'] == 'opcache_gui') echo 'selected'; ?>>OPcache status - Advanced</option>
			  <option value="opcache_gui2" <?php if($_POST['action'] == 'opcache_gui2') echo 'selected'; ?>>OpCache GUI</option>
			  <option value="ocp" <?php if($_POST['action'] == 'ocp') echo 'selected'; ?>>Opcache Control Panel</option>
			  <option value="folderperm" <?php if($_POST['action'] == 'folderperm') echo 'selected'; ?>>File &amp; Folder Permissions</option>
			  <option value="dbclean" <?php if($_POST['action'] == 'dbclean') echo 'selected'; ?>>Database Cleanup</option>
		</select>
		 <input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Get Info" />
      </td>
    </tr>
  </table>
<br />
<?php if($_POST['action'] == 'phpinfo') { ?>
<iframe class="sysadmin" src="admin/phpinfo/index.php" style="width:100%;height:1000px;"></iframe>
<?php } ?>
<br />
<?php if($_POST['action'] == 'xdebug') { ?>
<iframe class="sysadmin" src="admin/webgrind/index.php" style="width:100%;height:1000px;"></iframe>
<?php } ?>
<?php if($_POST['action'] == 'sysinfo') { ?>
<iframe class="sysadmin" src="admin/phpsysinfo/index.php" style="width:100%;height:1000px;"></iframe>
<?php } ?>
<?php if($_POST['action'] == 'opcache') { ?>
<iframe class="sysadmin" src="admin/opcache/opcache.php" style="width:100%;height:700px;"></iframe>
<?php } ?>
<?php if($_POST['action'] == 'opcache_gui') { ?>
<iframe class="sysadmin" src="admin/opcache-gui/index.php" style="width:100%;height:1000px;"></iframe>
<?php } ?>
<?php if($_POST['action'] == 'opcache_gui2') { ?>
<iframe class="sysadmin" src="admin/opcache-gui2/public/index.php" style="width:100%;height:1000px;"></iframe>
<?php } ?>
<?php if($_POST['action'] == 'ocp') { ?>
<iframe class="sysadmin" src="admin/ocp/ocp.php" style="width:100%;height:1000px;"></iframe>
<?php } ?>
<?php if($_POST['action'] == 'folderperm') include('admin/folderperm/index.php'); ?>
<?php if($_POST['action'] == 'dbclean') {

$menu = "select * from FLC_MENU where MENUPARENT not in (select MENUID from FLC_MENU) and MENUPARENT <> 0";
$menuRs = $myQuery->query($menu,'SELECT','NAME');

$page = "select * from FLC_PAGE where MENUID not in (select MENUID from FLC_MENU)";
$pageRs = $myQuery->query($page,'SELECT','NAME');

$component = "select * from FLC_PAGE_COMPONENT where PAGEID not in (select PAGEID from FLC_PAGE)";
$componentRs = $myQuery->query($component,'SELECT','NAME');

$item = "select * from FLC_PAGE_COMPONENT_ITEMS where COMPONENTID not in (select COMPONENTID from FLC_PAGE_COMPONENT)";
$itemRs = $myQuery->query($item,'SELECT','NAME');

$control = "select * from FLC_PAGE_CONTROL where PAGEID not in (select PAGEID from FLC_PAGE) or COMPONENTID not in (select COMPONENTID from FLC_PAGE_COMPONENT)";
$controlRs = $myQuery->query($control,'SELECT','NAME');

$permission = "	select * from FLC_PERMISSION where PERM_TYPE = 'menu' and PERM_ITEM not in (select MENUID from FLC_MENU)
				UNION ALL
				select * from FLC_PERMISSION where PERM_TYPE = 'page' and PERM_ITEM not in (select PAGEID from FLC_PAGE)
				UNION ALL
				select * from FLC_PERMISSION where PERM_TYPE = 'component' and PERM_ITEM not in (select COMPONENTID from FLC_PAGE_COMPONENT)
				UNION ALL
				select * from FLC_PERMISSION where PERM_TYPE = 'item' and PERM_ITEM not in (select ITEMID from FLC_PAGE_COMPONENT_ITEMS)
				UNION ALL
				select * from FLC_PERMISSION where PERM_TYPE = 'control' and PERM_ITEM not in (select CONTROLID from FLC_PAGE_CONTROL)";
$permissionRs = $myQuery->query($permission,'SELECT','NAME');

$group = "select * from FLC_USER_GROUP_MAPPING where GROUP_ID not in (select GROUP_ID from FLC_USER_GROUP) or USER_ID not in (select USERID from PRUSER)";
$groupRs = $myQuery->query($group,'SELECT','NAME');


//trigger
//trigger param
//flc_user_group



echo '<pre>';
print_r($menuRs);
print_r($pageRs);
print_r($componentRs);
print_r($itemRs);
print_r($controlRs);
print_r($permissionRs);
print_r($groupRs);
echo '</pre>';
?>
<div id="files_list">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent flcEditorList">
	<tr>
		<th colspan="9">Files and Folders List</th>
	</tr>
	<tr>
	  <th width="15" class="listingHead">#</th>
	  <th width="100" class="listingHead">Type</th>
      <th width="" class="listingHead">Name</th>
      <th width="40" class="listingHead">Exists</th>
	  <th width="20" class="listingHead">Permission</th>
	  <th width="30" class="listingHead">Level</th>
	</tr>
	<?php for($a=0; $a < count($all); $a++)  { ?>
	<?php for($x=0; $x < count($all[$a]); $x++){?>
	<tr>
	  <td class="listingContent"><?php echo $x+1;?>.</td>
	  <td class="listingContent"><?php if($a == 0) echo 'File'; else echo 'Folder'; ?></td>
	  <td class="listingContent"><?php echo $all[$a][$x];?></td>
      <td class="listingContent"><?php if(file_exists($all[$a][$x]) === true) echo 'Yes'; else echo 'No'; ?></td>
      <td class="listingContent"><?php if(is_writable($all[$a][$x]) === true && file_exists($all[$a][$x]) === true) echo 'Writable'; else if(is_writable($all[$a][$x]) === false && file_exists($all[$a][$x]) === false) echo '-'; else echo 'Readonly'; ?></td>
      <td class="listingContent"><?php  if(is_writable($all[$a][$x]) === true && file_exists($all[$a][$x]) === true) echo substr(sprintf('%o', fileperms($all[$a][$x])), -4); else echo '-'; ?></td>
	</tr>
	<?php } ?>
	<?php } ?>
  </table>
</div>
<?php } ?>
<br>
<script>
hoverTable();
</script>