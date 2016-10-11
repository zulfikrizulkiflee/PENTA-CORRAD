<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

include_once('system_prerequisite.php');

//for label purposes
if($_POST['trans_type'] == 2)
	$translationType = 'Menu';
else if($_POST['trans_type'] == 3)
	$translationType = 'Page';
else if($_POST['trans_type'] == 4)
	$translationType = 'Component';
else if($_POST['trans_type'] == 5)
	$translationType = 'Item';
else if($_POST['trans_type'] == 6)
	$translationType = 'Control';
else if($_POST['trans_type'] == 7)
	$translationType = 'Message';

//if ajax filter
if(isset($_GET['filter_sub']))
{
	$_POST['code'] = $_GET['parentid'];
	$_POST['showScreen'] = true;
}

//if show disabled parent menu not set
if(!isset($_POST['showDisabled']))
	$_POST['showDisabled'] = 0;

//============================================ parent menu ============================================
//if new screen submitted
if($_POST["newCategory"])
{
	//get parent menu order
	$parentOrder = "select max(MENUORDER) + 1 as MAXPARENTORDER
					from FLC_MENU
					where MENULEVEL = 1";
	$parentOrderRsRows = $myQuery->query($parentOrder,'SELECT','NAME');
}

//if main category edit button clicked
if($_POST["code"] && $_POST["editCategory"])
{
	//show info of selected category
	$showCatInfo = "select LANG_ID, LANG_NAME, LANG_FLAG_URL, LANG_STATUS
					from FLC_TRANSLATION_LANGUAGE where LANG_ID = ".$_POST["code"];
	$showCatInfoRsRows = $myQuery->query($showCatInfo,'SELECT','NAME');
}

//if new language added
if($_POST["saveScreenNew"])
{
	//get max menuid
	$getMaxRs = $mySQL->maxValue('FLC_TRANSLATION_LANGUAGE','LANG_ID',0)+1;

	$insertCat = "insert into FLC_TRANSLATION_LANGUAGE (LANG_ID,LANG_NAME,LANG_FLAG_URL,LANG_STATUS,ADDED_BY,ADDED_DATETIME)
					values (".$getMaxRs.", '".$_POST['newName']."','".$_POST['flag']."',".$_POST['status'].",".$_SESSION['userID'].",".$mySQL->currentDate().")";
	$insertCatRs = $myQuery->query($insertCat,'RUN');
}

//if edit screen submitted
if($_POST["saveScreenEdit"])
{
	$updateCat = "update FLC_TRANSLATION_LANGUAGE set
					LANG_NAME = '".$_POST['editName']."',
					LANG_FLAG_URL = '".$_POST['flag']."',
					LANG_STATUS = ".$_POST['status']."
					where LANG_ID = ".$_POST['code'];
	$myQuery->query($updateCat,'RUN');
}//eof id

else if($_POST["deleteCategory"])
{
	//delete the language
	$deleteMenu = "delete from FLC_TRANSLATION_LANGUAGE
					where LANG_ID = ".$_POST["code"];
	$deleteMenuRs = $myQuery->query($deleteMenu,'RUN');

	//delete translation
	$deletePerm = "delete from FLC_TRANSLATION
				where TRANS_LANGUAGE = ".$_POST['code'];
	$deletePermRs = $myQuery->query($deletePerm,'RUN');
}
//=====================================================================================================

//===========================================reference=================================================
//if new reference added
if($_POST['saveScreenRefNew'])
{
	$sourceSplit = explode('|',$_POST['newItemTranslate']);
	$type = $_POST['trans_type'];
	$col = $sourceSplit[1];

	//if index
	if($_POST['trans_type'] == 1)
	{
		//get max menuid
		$getMaxRs = $mySQL->maxValue('FLC_TRANSLATION','TRANS_ID',0)+1;
		$insertRefRs = $myQuery->query("insert into FLC_TRANSLATION
										(TRANS_ID, TRANS_TYPE, TRANS_LANGUAGE, TRANS_TEXT, TRANS_SOURCE_ID, TRANS_SOURCE_CONSTANT_NAME)
										values (".$getMaxRs.", ".$type.",".$_POST['code'].",'".$_POST['newTransText']."',
										".$_POST['newSourceID'].",'".$sourceSplit[1]."')",'RUN');
	}
	else
	{
		//translate similar items
		if($_POST['newTranslateSimilar'] == 1)
		{
			if($type == 2)
			{
				$srcTable 	= 'FLC_MENU';
				$primaryKey = 'MENUID';
			}
			else if($type == 3)
			{
				$srcTable 	= 'FLC_PAGE';
				$primaryKey = 'PAGEID';
			}
			else if($type == 4)
			{
				$srcTable = 'FLC_PAGE_COMPONENT';
				$primaryKey = 'COMPONENTID';
			}
			else if($type == 5)
			{
				$srcTable = 'FLC_PAGE_COMPONENT_ITEMS';
				$primaryKey = 'ITEMID';
			}
			else if($type == 6)
			{
				$srcTable = 'FLC_PAGE_CONTROL';
				$primaryKey = 'CONTROLID';
			}
			else if($type == 7)
			{
				$srcTable = 'FLC_MESSAGE';
				$primaryKey = 'MESSAGE_ID';
			}

			//find ID of similar text
			$qryRs = $myQuery->query("select ".$primaryKey." from ".$srcTable."
										where ".$primaryKey." in (select ".$primaryKey." from ".$srcTable."
																	where ".$col." = (select ".$col." from ".$srcTable."
																						where ".$primaryKey." = ".$_POST['newSourceID']."))
										and ".$primaryKey." <> ".$_POST['newSourceID'],'SELECT','NAME');

			for($x=0; $x < count($qryRs); $x++)
			{
				$getMaxRs = $mySQL->maxValue('FLC_TRANSLATION','TRANS_ID',0)+1;
				$myQuery->query( "insert into FLC_TRANSLATION
									(TRANS_ID, TRANS_TYPE, TRANS_LANGUAGE, TRANS_TEXT, TRANS_SOURCE_ID, TRANS_SOURCE_TABLE, TRANS_SOURCE_COLUMN)
									values (".$getMaxRs.", ".$type.",".$_POST['code'].",'".$_POST['newTransText']."',
									".$qryRs[$x][$primaryKey].",'".$srcTable."','".$col."')",'RUN');
			}
		}

		//get max menuid
		$getMaxRs = $mySQL->maxValue('FLC_TRANSLATION','TRANS_ID',0)+1;
		$insertRef = "insert into FLC_TRANSLATION
						(TRANS_ID, TRANS_TYPE, TRANS_LANGUAGE, TRANS_TEXT, TRANS_SOURCE_ID, TRANS_SOURCE_TABLE, TRANS_SOURCE_COLUMN)
						values
						(".$getMaxRs.", ".$type.",".$_POST['code'].",'".$_POST['newTransText']."',
						".$_POST['newSourceID'].",'".$sourceSplit[0]."','".$sourceSplit[1]."')";
		$insertRefRs = $myQuery->query($insertRef,'RUN');
	}

	//dummy to trigger
	$_POST['showScreen'] = true;
}

//if save screen edit reference
else if($_POST['saveScreenRefEdit'])
{
	$sourceSplit = explode('|',$_POST['editItemTranslate']);


//if index
	if($_POST['trans_type'] == 1)
	{
		//update statement
		$updateRef = "update FLC_TRANSLATION
						set TRANS_TEXT = '".$_POST['editTransText']."',
						TRANS_SOURCE_CONSTANT_NAME = '".$sourceSplit[1]."'
						where TRANS_ID = ".$_POST['hiddenCode'];
		$updateRefRs =  $myQuery->query($updateRef,'RUN');

	}
	else
	{
		//update statement
		$updateRef = "update FLC_TRANSLATION
						set TRANS_TEXT = '".$_POST['editTransText']."',
						TRANS_SOURCE_ID = ".$_POST['editSourceID'].",
						TRANS_SOURCE_TABLE = '".$sourceSplit[0]."',
						TRANS_SOURCE_COLUMN = '".$sourceSplit[1]."'
						where TRANS_ID = ".$_POST['hiddenCode'];
		$updateRefRs =  $myQuery->query($updateRef,'RUN');
	}
	//dummy
	$_POST['showScreen'] = true;
}

//if reference deleted
if($_POST['deleteTrans'])
{
	$deleteTranslation = "delete from FLC_TRANSLATION where TRANS_ID = ".$_POST['hiddenCode'];
	$deleteTranslationRs = $myQuery->query($deleteTranslation,'RUN');

	//dummy to trigger
	$_POST['showScreen'] = true;
}

//if edit reference clicked, show detail
if($_POST['editReference'])
{
	//show reference detail
	$showRef = "select *
				from FLC_TRANSLATION
				where TRANS_ID = ".$_POST['hiddenCode'];
	$showRefRsRows = $myQuery->query($showRef,'SELECT','NAME');
}

if($_POST['newReference'] || $_POST['editReference'])
{
	//get all menu (recursively)
	$recursiveMenuList = $mySQL->getMenuList('',false);

	//list of available menu
	$menuList = assambleRecursiveMenu($recursiveMenuList);
	$menuListCount = count($menuList);
}
//===========================================//reference===============================================

function languageEditor_getTranslation($myQuery,$type,$lang,$id,$col,$const)
{
	//index page
	if($type == 1) {}

	//menu
	else if($type == 2)
	{
		$srcTable = 'FLC_MENU';
		$primaryKey = 'MENUID';
		$col = 'MENUTITLE';
	}

	//page
	else if($type == 3)
	{
		$srcTable = 'FLC_PAGE';
		$primaryKey = 'PAGEID';
	}

	//component
	else if($type == 4)
	{
		$srcTable = 'FLC_PAGE_COMPONENT';
		$primaryKey = 'COMPONENTID';
	}

	//item
	else if($type == 5)
	{
		$srcTable = 'FLC_PAGE_COMPONENT_ITEMS';
		$primaryKey = 'ITEMID';
	}

	//control
	else if($type == 6)
	{
		$srcTable = 'FLC_PAGE_CONTROL';
		$primaryKey = 'CONTROLID';
		$col = 'CONTROLTITLE';
	}

	//control
	else if($type == 7)
	{
		$srcTable = 'FLC_MESSAGE';
		$primaryKey = 'MESSAGE_ID';
		$col = 'MESSAGE_TEXT';
	}

	if($type > 1)
	{
		$qry = "select ".$col." as TRANS_TEXT from ".$srcTable." where ".$primaryKey."=".$id;
		$qryRs = $myQuery->query($qry,'SELECT','NAME');

		if(count($qryRs) > 0)
			return $qryRs[0]['TRANS_TEXT'];
		else
			return '<span style="color:red;">--- Item Deleted ---</span>';
	}
	else if($type == 1)
		return $const;
}

//=========================================== page ordering ===========================================
//if showScreen and code not null
if($_POST['showScreen'] && $_POST['code'])
{
	$_GET['original'] = trim(str_replace('search here..','',$_GET['original']));
	$_GET['translated'] = trim(str_replace('search here..','',$_GET['translated']));

	$filterArr = array();

	if(!empty($_GET['original']))
		$filterArr['original'] = $_GET['original'];

	if(!empty($_GET['translated']))
		$filterArr['translated'] = $_GET['translated'];

	//if have filter
	if(count($filter) > 0)
	{
		if(array_key_exists('original',$filter))
			$extraMenu=" and upper(b.MENUTITLE) like upper('%".$filter['parentmenu']."%')";

		if(array_key_exists('translated',$filter))
			$extraParent=" and upper(a.MENUTITLE) like upper('%".$filter['title']."%')";
	}

	$reference = "select * from FLC_TRANSLATION
	 				where TRANS_LANGUAGE = ".$_POST['code']." and TRANS_TYPE = ".$_POST['trans_type']."
					order by TRANS_TEXT";
	$referenceRsArr = $myQuery->query($reference,'SELECT','NAME');
}

//notifications
if($insertCatRs)
	showNotificationInfo('New language added.');
if($deleteCatRs && $deleteCatChildRs)
	showNotificationInfo('Selected language has been deleted!');
if($updateCatRs)
	showNotificationInfo('Language has been updated');
if($insertRefRs)
	showNotificationInfo('New translation has been added');
if($deleteTranslationRs)
	showNotificationInfo('Translation has been deleted!');
if($updateRefRs)
	showNotificationInfo('Translation has been updated');

$general = "select LANG_ID, LANG_NAME, LANG_FLAG_URL, LANG_STATUS from FLC_TRANSLATION_LANGUAGE order by LANG_ID";
$generalRsArr = $myQuery->query($general,'SELECT','NAME');

//get flag;
$flagPath = 'img/flag/gif/';
$flag = scandir($flagPath);
array_shift($flag);
array_shift($flag);
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
</script>
<script language="javascript" src="js/editor.js"></script>
<div id="breadcrumbs">System Administrator / Configuration / Translation Editor</div>
<h1>Translation Editor </h1>
<form action="" method="post" name="form1">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Language List</th>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Available Language(s) : </td>
      <td>
        <select name="code" class="inputList" id="code" onchange="" style="width:300px;">
          <?php for($x=0; $x < count($generalRsArr); $x++) { ?>
          <option value="<?php echo $generalRsArr[$x]['LANG_ID']?>" label="<?php echo $generalRsArr[$x]['LANG_NAME']?><?php if($generalRsArr[$x]['LANG_STATUS'] != 1) echo ' - Inactive';?>" <?php if($_POST['code'] == $generalRsArr[$x]['LANG_ID']) echo "selected";?>><?php echo $generalRsArr[$x]['LANG_NAME']?></option>
          <?php } ?>
        </select>
        <input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Get Translation" />
      </td>
    </tr>
      <tr>
      <td  nowrap="nowrap" class="inputLabel">Translation Type : </td>
      <td >
		<label <?php if($_POST['trans_type'] == 1) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 1) echo 'checked'; if(!isset($_POST['trans_type'])) echo 'checked'; ?> name="trans_type" value="1" />Index</label>
        <label <?php if($_POST['trans_type'] == 2) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 2) echo 'checked';?> name="trans_type" value="2" />Menu</label>
		<label <?php if($_POST['trans_type'] == 3) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 3) echo 'checked';?> name="trans_type" value="3" />Page</label>
		<label <?php if($_POST['trans_type'] == 4) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 4) echo 'checked';?> name="trans_type" value="4" />Component</label>
		<label <?php if($_POST['trans_type'] == 5) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 5) echo 'checked';?> name="trans_type" value="5" />Item</label>
		<label <?php if($_POST['trans_type'] == 6) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 6) echo 'checked'; ?> name="trans_type" value="6" />Control</label>
        <label <?php if($_POST['trans_type'] == 7) {?>style="font-weight:normal;text-decoration:underline"<?php } ?>><input type="radio" onchange="jQuery('#showScreen').click()" <?php if($_POST['trans_type'] == 7) echo 'checked'; ?> name="trans_type" value="7" />Message</label>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter"><div align="right">
          <input name="newCategory" type="submit" class="inputButton" value="Add New Language" />
		  <input name="editCategory" type="submit" class="inputButton" value="Update" <?php if($_POST["code"] == "" || isset($_POST["deleteCategory"])) { ?>disabled="disabled" <?php } ?> />
          <input name="deleteCategory" type="submit" class="inputButton" value="Delete" <?php if($_POST["code"] == "" || isset($_POST["deleteCategory"])) { ?>disabled="disabled" <?php } ?> onClick="if(window.confirm('Are you sure you want to DELETE this language?\nThis will also delete ALL translations under for this language.')) {return true} else {return false}" />
        </div></td>
    </tr>
  </table>
  <?php if($_POST["newCategory"]) { ?>
  <br>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">New Language Information</th>
    </tr>
    <tr>
      <td class="inputLabel" nowrap="nowrap">Language Name :</td>
      <td ><input name="newName" type="text" class="inputInput" id="newName" size="50" onKeyUp="form1.saveScreenNew.disabled = false"></td>
    </tr>
    <tr>
      <td class="inputLabel">Flag Icon : </td>
      <td>
		<?php for($x=0;$x < count($flag); $x++) { ?>
          <div style="width:40px; padding:3px; float:left;cursor:pointer;" onclick="this.down().checked = true;">
          <input type="radio" name="flag" id="flag" <?php if($x == 0) echo 'checked';  ?> value="<?php echo $flagPath.$flag[$x]; ?>" /><img title="<?php echo $flag[$x]; ?>" src="<?php echo $flagPath.$flag[$x]; ?>" />
          </div>
          <?php } ?>
        </td>
    </tr>
    <tr>
		<td class="inputLabel">Status : </td>
		<td>
			<label><input type="radio" name="status" value="1" checked /> Active</label>
			<label><input type="radio" name="status" value="0" /> Inactive</label>
		</td>
    </tr>
    <tr>
      <td colspan="2" bgcolor="#F7F3F7"><div align="right">
          <input name="saveScreenNew" type="submit" class="inputButton" value="Save" disabled="disabled" />
          <input name="cancelScreenNew" type="submit" class="inputButton" value="Cancel" />
        </div></td>
    </tr>
  </table>
  <?php } ?>
  <?php if($_POST["editCategory"]) { ?>
  <br>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Update Language Information</th>
    </tr>
    <tr>
      <td class="inputLabel" nowrap="nowrap">Language Name :</td>
      <td ><input name="editName" type="text" class="inputInput" id="editName" size="50" onKeyUp="form1.saveScreenEdit.disabled = false" value="<?php echo $showCatInfoRsRows[0]["LANG_NAME"]?>"></td>
    </tr>

    <tr>
     <td class="inputLabel">Flag Icon : </td>
      <td>
		<?php for($x=0;$x < count($flag); $x++) { ?>
          <div style="width:40px; padding:3px; float:left;cursor:pointer;" onclick="this.down().checked = true;">
          <input type="radio" name="flag" id="flag" <?php if($showCatInfoRsRows[0]["LANG_FLAG_URL"] == $flagPath.$flag[$x]) echo 'checked';  ?> value="<?php echo $flagPath.$flag[$x]; ?>" /><img title="<?php echo $flag[$x]; ?>" src="<?php echo $flagPath.$flag[$x]; ?>" />
          </div>
          <?php } ?>
        </td>
    </tr>
     <tr>
		<td class="inputLabel">Status : </td>
		<td>
<label><input type="radio" name="status" value="1" <?php if($showCatInfoRsRows[0]["LANG_STATUS"] == '1') echo 'checked'; ?> /> Active</label>
<label><input type="radio" name="status" value="0" <?php if($showCatInfoRsRows[0]["LANG_STATUS"] == '0' || $showCatInfoRsRows[0]["LANG_STATUS"] == '') echo 'checked'; ?> /> Inactive</label>
		</td>
    </tr>
    <tr>
      <td colspan="2" bgcolor="#F7F3F7"><div align="right">
          <input name="saveScreenEdit" type="submit" class="inputButton" id="saveScreenEdit" value="Save" />
          <input name="cancelScreenEdit" type="submit" class="inputButton" id="cancelScreenEdit" value="Cancel" />
        </div></td>
    </tr>
  </table>
  <?php } ?>
  <?php if($_POST["newReference"]) { ?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Add Translation</th>
    </tr>
    <tr>
      <td class="inputLabel">Translation Text : </td>
      <td ><input name="newTransText" type="text" class="inputInput" id="newTransText" size="70" onkeyup="form1.saveScreenRefNew.disabled = false" /></td>
    </tr>
    <tr <?php if($_POST['trans_type'] == 1) echo 'style="display:none;"';?>>
      <td class="inputLabel"><?php echo $translationType; ?> ID : </td>
      <td>
      	<?php if($_POST['trans_type'] == 2){?>
        <select id="newSourceID" name="newSourceID" class="inputList" style="max-width:500px;" >
          <option value="0">None</option>
          <?php for($x=0; $x<$menuListCount; $x++){?>
          <option value="<?php echo $menuList[$x]['MENUID'];?>">
		    <?php echo $menuList[$x]['MENUBREADCRUMBS'].$menuList[$x]['MENUTITLE'];?>
          </option>
          <?php } ?>
        </select>
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=menu&type=menu&lovValue=newSourceID', 1, 1, 500, 500);" />
        <?php } else if ($_POST['trans_type'] == '3') {
			//get list of page menu
			$generalRs = $mySQL->menu();
			$generalRsCount = count($generalRs);
			?>
         <select name="newSourceID" class="inputList" id="newSourceID">
            <option value="">&lt; Select Page &gt;</option>
            <?php for($x=0; $x<$generalRsCount; $x++){?>
            <option value="<?php echo $generalRs[$x]['PAGEID'];?>">[<?php echo $generalRs[$x]['PAGEID'];?>] - <?php echo $generalRs[$x]['MENUTITLE'];?></option>
            <?php }?>
          </select>
        <?php }  else if($_POST['trans_type'] == 1) { ?>
         <input name="newSourceID" type="hidden" class="inputInput" id="newSourceID" value="0" size="5" />
        <?php } else{?>
        <input name="newSourceID" type="text" class="inputInput" id="newSourceID" size="5" />
        <?php }?>
         <label><input name="newTranslateSimilar" type="checkbox" value="1" /> Translate similar text</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Item To Translate : </td>
      <td><select name="newItemTranslate" class="inputList" id="newItemTranslate">
			<?php if($_POST['trans_type'] == 1) { ?>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_USERNAME">Side Menu - Username</option>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_NAME">Side Menu - Name</option>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_GROUP">Side Menu - Group</option>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_DEPARTMENT">Side Menu - Department</option>
			<option value="INDEX|TR8N_TABULAR_ADD_ROW">Report/Tabular Component - Add Row Button</option>
			<option value="INDEX|TR8N_TABULAR_DEL_ROW">Report/Tabular Component - Delete Row Button</option>
			<option value="INDEX|TR8N_TABULAR_NO_RECORD">Report/Tabular Component - No records(s) found</option>
			<option value="INDEX|TR8N_LOV_FILTER_TITLE">List of Values - Filter Title</option>
			<option value="INDEX|TR8N_LOV_FILTER_BY_LIST_TITLE">List of Values - Filter By Title</option>
			<option value="INDEX|TR8N_LOV_VALUE_TITLE">List of Values - Value Title</option>
			<option value="INDEX|TR8N_LOV_LIST_TITLE">List of Values - List Title</option>
			<option value="INDEX|TR8N_LOV_FILTER_BUTTON">List of Values - Filter Button</option>
			<option value="INDEX|TR8N_LOV_CLOSE_BUTTON">List of Values - Close Button</option>
			<option value="INDEX|TR8N_LOV_OPEN_SEARCH">List of Values - Open Search</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 2) { ?>
			<option value="FLC_MENU|MENUTITLE">Menu Title</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 3) { ?>
			<option value="FLC_PAGE|PAGETITLE">Page Title</option>
			<option value="FLC_PAGE|PAGEBREADCRUMBS">Page Breadcrumbs</option>
			<option value="FLC_PAGE|PAGEPRESCRIPT">Page Prescript</option>
			<option value="FLC_PAGE|PAGEPOSTSCRIPT">Page Postscript</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 4) { ?>
			<option value="FLC_PAGE_COMPONENT|COMPONENTTITLE">Component Title</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 5) { ?>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMTITLE">Item Title</option>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMNOTES">Item Notes</option>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMHINTS">Item Hints</option>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMPLACEHOLDER">Item Placeholder</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 6) { ?>
			<option value="FLC_PAGE_CONTROL|CONTROLTITLE">Control Title</option>
			<?php } ?>
            <?php if($_POST['trans_type'] == 7) { ?>
			<option value="FLC_MESSAGE|MESSAGE_TEXT">Message Text</option>
			<?php } ?>
			</select></td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter"><div align="right">
          <input name="saveScreenRefNew" type="submit" disabled="disabled" class="inputButton" id="saveScreenRefNew" value="Save" />
          <input name="cancelScreen" type="submit" class="inputButton" id="cancelScreen" value="Cancel" />
        </div></td>
    </tr>
  </table>
  <?php } ?>
  <?php if($_POST["editReference"]) { ?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Update Translation</th>
    </tr>
     <tr>
      <td  class="inputLabel">Translation Text : </td>
      <td ><input name="editTransText" type="text" value="<?php echo $showRefRsRows[0]['TRANS_TEXT'];?>" class="inputInput" id="editTransText" size="70" onkeyup="form1.saveScreenRefEdit.disabled = false" /></td>
    </tr>
    <tr <?php if($_POST['trans_type'] == 1) echo 'style="display:none;"';?>>
      <td class="inputLabel"><?php echo $translationType; ?> ID : </td>
      <td>
      	<?php if($_POST['trans_type'] == 2){?>
        <select id="editSourceID" name="editSourceID" class="inputList" style="max-width:500px;" >
          <option value="0">None</option>
          <?php for($x=0; $x<$menuListCount; $x++){?>
          <option value="<?php echo $menuList[$x]['MENUID'];?>" <?php if($menuList[$x]['MENUID'] == $showRefRsRows[0]['TRANS_SOURCE_ID']){?> selected="selected"<?php }?>>
		    <?php echo $menuList[$x]['MENUBREADCRUMBS'].$menuList[$x]['MENUTITLE'];?>
          </option>
          <?php } ?>
        </select>
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=menu&type=menu&lovValue=editSourceID', 1, 1, 500, 500);" />
         <?php } else if ($_POST['trans_type'] == '3') {
			//get list of page menu
			$generalRs = $mySQL->menu();
			$generalRsCount = count($generalRs);
			?>
         <select name="editSourceID" class="inputList" id="editSourceID">
            <option value="">&lt; Select Page &gt;</option>
            <?php for($x=0; $x<$generalRsCount; $x++){?>
            <option value="<?php echo $generalRs[$x]['PAGEID'];?>" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == $generalRs[$x]['PAGEID']) echo "selected";?>>[<?php echo $generalRs[$x]['PAGEID'];?>] - <?php echo $generalRs[$x]['MENUTITLE'];?></option>
            <?php }?>
          </select>
        <?php } else if($_POST['trans_type'] == 1) { ?>
         <input name="editSourceID" type="hidden" class="inputInput" id="editSourceID" value="0" />
        <?php } else{?>
        <input name="editSourceID" type="text"  value="<?php echo $showRefRsRows[0]['TRANS_SOURCE_ID'];?>" class="inputInput" id="editSourceID" size="5" />
        <?php }?>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Item To Translate : </td>
      <td>
		  <select name="editItemTranslate" class="inputList" id="editItemTranslate">
			<?php if($_POST['trans_type'] == 1) { ?>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_USERNAME" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_SIDE_MENU_LEFT_USERNAME') echo 'selected'; ?>>Side Menu - Username</option>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_NAME" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_SIDE_MENU_LEFT_NAME') echo 'selected'; ?>>Side Menu - Name</option>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_GROUP" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_SIDE_MENU_LEFT_GROUP') echo 'selected'; ?>>Side Menu - Group</option>
			<option value="INDEX|TR8N_SIDE_MENU_LEFT_DEPARTMENT" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_SIDE_MENU_LEFT_DEPARTMENT') echo 'selected'; ?>>Side Menu - Department</option>
			<option value="INDEX|TR8N_TABULAR_ADD_ROW" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_TABULAR_ADD_ROW') echo 'selected'; ?>>Report/Tabular Component - Add Row Button</option>
			<option value="INDEX|TR8N_TABULAR_DEL_ROW" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_TABULAR_DEL_ROW') echo 'selected'; ?>>Report/Tabular Component - Delete Row Button</option>
			<option value="INDEX|TR8N_TABULAR_NO_RECORD" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_TABULAR_NO_RECORD') echo 'selected'; ?>>Report/Tabular Component - No records(s) found</option>
			
			<option value="INDEX|TR8N_LOV_FILTER_TITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_FILTER_TITLE') echo 'selected'; ?>>List of Values - Filter Title</option>
			<option value="INDEX|TR8N_LOV_FILTER_BY_LIST_TITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_FILTER_BY_LIST_TITLE') echo 'selected'; ?>>List of Values - Filter By Title</option>
			<option value="INDEX|TR8N_LOV_VALUE_TITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_VALUE_TITLE') echo 'selected'; ?>>List of Values - Value Title</option>
			<option value="INDEX|TR8N_LOV_LIST_TITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_LIST_TITLE') echo 'selected'; ?>>List of Values - List Title</option>
			<option value="INDEX|TR8N_LOV_FILTER_BUTTON" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_FILTER_BUTTON') echo 'selected'; ?>>List of Values - Filter Button</option>
			<option value="INDEX|TR8N_LOV_CLOSE_BUTTON" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_CLOSE_BUTTON') echo 'selected'; ?>>List of Values - Close Button</option>
			<option value="INDEX|TR8N_LOV_OPEN_SEARCH" <?php if($showRefRsRows[0]['TRANS_SOURCE_ID'] == '0' && $showRefRsRows[0]['TRANS_SOURCE_CONSTANT_NAME'] == 'TR8N_LOV_OPEN_SEARCH') echo 'selected'; ?>>List of Values - Open Search</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 2) { ?>
			<option value="FLC_MENU|MENUTITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_MENU|MENUTITLE') echo 'selected'; ?>>Menu Title</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 3) { ?>
			<option value="FLC_PAGE|PAGETITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE|PAGETITLE') echo 'selected'; ?>>Page Title</option>
			<option value="FLC_PAGE|PAGEBREADCRUMBS" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE|PAGEBREADCRUMBS') echo 'selected'; ?>>Page Breadcrumbs</option>
			<option value="FLC_PAGE|PAGEPRESCRIPT" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE|PAGEPRESCRIPT') echo 'selected'; ?>>Page Prescript</option>
			<option value="FLC_PAGE|PAGEPOSTSCRIPT" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE|PAGEPOSTSCRIPT') echo 'selected'; ?>>Page Postscript</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 4) { ?>
			<option value="FLC_PAGE_COMPONENT|COMPONENTTITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE_COMPONENT|COMPONENTTITLE') echo 'selected'; ?>>Component Title</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 5) { ?>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMTITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE_COMPONENT_ITEMS|ITEMTITLE') echo 'selected'; ?>>Item Title</option>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMNOTES" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE_COMPONENT_ITEMS|ITEMNOTES') echo 'selected'; ?>>Item Notes</option>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMHINTS" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE_COMPONENT_ITEMS|ITEMHINTS') echo 'selected'; ?>>Item Hints</option>
			<option value="FLC_PAGE_COMPONENT_ITEMS|ITEMPLACEHOLDER" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE_COMPONENT_ITEMS|ITEMPLACEHOLDER') echo 'selected'; ?>>Item Placeholder</option>
			<?php } ?>
			<?php if($_POST['trans_type'] == 6) { ?>
			<option value="FLC_PAGE_CONTROL|CONTROLTITLE" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_PAGE_CONTROL|CONTROLTITLE') echo 'selected'; ?>>Control Title</option>
			<?php } ?>
            <?php if($_POST['trans_type'] == 7) { ?>
			<option value="FLC_MESSAGE|MESSAGE_TEXT" <?php if($showRefRsRows[0]['TRANS_SOURCE_TABLE'].'|'.$showRefRsRows[0]['TRANS_SOURCE_COLUMN'] == 'FLC_MESSAGE|MESSAGE_TEXT') echo 'selected'; ?>>Message Text</option>
			<?php } ?>
			</select></td>
    </tr>
    <tr>
		<td class="contentButtonFooter" colspan="2">
			<input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $_POST['hiddenCode'];?>" />
			<input name="saveScreenRefEdit" type="submit" class="inputButton" id="saveScreenRefEdit" value="Save" />
			<input name="cancelScreen" type="submit" class="inputButton" id="cancelScreen" value="Cancel" />
		</td>
    </tr>
  </table>
  <?php } ?>
</form>
<?php if($_POST["showScreen"] && $_POST["code"] != "") { ?>
<div id="language_editor_translation_list">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	  <tr>
		<th colspan="9">Translation List</th>
	  </tr>
	 <?php if(count($referenceRsArr) > 0) { ?>
	  <tr>
		<th width="15" class="listingHead">#</th>

		<th class="listingHead">Original Text</th>
        <th class="listingHead">Translated Text</th>
       <?php if($_POST['trans_type'] > 1) {?>
        <th class="listingHead">Type</th>
        <?php } ?>
		<th width="85" class="listingHead">Action</th>
	  </tr>
	  <?php for($x=0; $x < count($referenceRsArr); $x++) {

		if($_POST['trans_type'] > 1)
		{
			$sourceLink = $referenceRsArr[$x]['TRANS_SOURCE_COLUMN'];

			if($sourceLink == 'MENUTITLE')
				$sourceLink = 'Menu Title';
			else if($sourceLink == 'PAGETITLE')
				$sourceLink = 'Page Title';
			else if($sourceLink == 'PAGEBREADCRUMBS')
				$sourceLink = 'Breadcrumbs';
			//else if($sourceLink == 'PAGEPRESCRIPT')
			//	$sourceLink = 'Menu Title';
			//else if($sourceLink == 'PAGEPOSTSCRIPT')
			//	$sourceLink = 'Menu Title';
			else if($sourceLink == 'COMPONENTTITLE')
				$sourceLink = 'Component Title';
			else if($sourceLink == 'ITEMTITLE')
				$sourceLink = 'Item Title';
			else if($sourceLink == 'ITEMNOTES')
				$sourceLink = 'Item Notes';
			else if($sourceLink == 'ITEMHINTS')
				$sourceLink = 'Hints';
			else if($sourceLink == 'ITEMPLACEHOLDER')
				$sourceLink = 'Placeholder';
			else if($sourceLink == 'CONTROLTITLE')
				$sourceLink = 'Control Title';
			else if($sourceLink == 'MESSAGE_TEXT')
				$sourceLink = 'Message Text';
		} ?>
	  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
		<td class="listingContent"><?php echo $x+1;?>.</td>
		<td class="listingContent"><?php echo languageEditor_getTranslation($myQuery,$_POST['trans_type'],$_POST['code'],$referenceRsArr[$x]['TRANS_SOURCE_ID'],$referenceRsArr[$x]['TRANS_SOURCE_COLUMN'],$referenceRsArr[$x]['TRANS_SOURCE_CONSTANT_NAME']);?></td>
        <td class="listingContent"><?php echo $referenceRsArr[$x]['TRANS_TEXT'];?></td>
        <?php if($_POST['trans_type'] > 1) {?>
        <td class="listingContent"><?php echo $sourceLink;?></td>
        <?php } ?>
		<td nowrap="nowrap" class="listingContentRight">
		  <form id="formReference<?php echo $referenceRsArr[$x]['TRANS_ID'];?>" name="formReference<?php echo $referenceRsArr[$x]['MENUID'];?>" method="post">
			 <input name="editReference" type="submit" class="inputButton" id="editReference" value="Update" />
			<input name="deleteTrans" type="submit" class="inputButton" id="deleteReference" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this translation?')) {return true} else {return false}"/>
			<input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $referenceRsArr[$x]['TRANS_ID'];?>" />
			<input name="trans_type" type="hidden" id="trans_type" value="<?php echo $_POST['trans_type'];?>" />
			<input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
		  </form>
        </td>
	  </tr>
	  <?php 		} //end for ?>
	  <?php 	}//end if
		else 	{ ?>
	  <tr>
		<td colspan="9" class="myContentInput">No translation(s) found... </td>
	  </tr>
	  <?php 	} //end else?>
	  <tr>
		<td colspan="9" class="contentButtonFooter">
			<form id="form2" name="form2" method="post" action="">
			  <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
			  <input name="trans_type" type="hidden" id="trans_type_code" value="<?php echo $_POST['trans_type'];?>" />
			  <!-- TODO<input name="retranslateAll" type="submit" class="inputButton" id="retranslateAll" value="Retranslate All Similar Texts" />-->
			  <input name="newReference" type="submit" class="inputButton" id="newReference" value="Add Translation" />
			  <input name="saveScreen2" type="submit" class="inputButton" value="Close" />
			</form>
        </td>
	  </tr>
	</table>
</div>
<?php } ?>
