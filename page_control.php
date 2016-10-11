<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

//===========================================reference==========================================
//if new reference added
if($_POST['saveScreenRefNew'])
{
	//get max pageid
	$getMax = "select max(CONTROLID) from FLC_PAGE_CONTROL";
	$getMaxRs = $myQuery->query($getMax,'COUNT') + 1;

	//set default name, if blank
	if(!$_POST['newControlName'])
		$_POST['newControlName'] = 'control_'.$getMaxRs;

	//to prevent error
	if($_POST['newControlOrder'] == '')
		$_POST['newControlOrder'] = 1;

	if($_POST['newComponentID'] == '')
		$_POST['newComponentID'] = "null";

	//insert into page control table
	$insertRef = "insert into FLC_PAGE_CONTROL
					(CONTROLID, CONTROLNAME, CONTROLTITLE, CONTROLTYPE, CONTROLHINTS, CONTROLNOTES, CONTROLORDER, CONTROLREDIRECTURL,
					CONTROLIMAGEURL, CONTROLPOSITION,COMPONENTID, PAGEID)
					values (".$getMaxRs.", '".$_POST['newControlName']."', '".$_POST['newControlTitle']."', ".$_POST['newControlType'].",
						'".$_POST['newControlHints']."', '".$_POST['newControlNotes']."', ".$_POST['newControlOrder'].",
						'".$_POST['newRedirectURL']."', '".$_POST['newImageURL']."', '".$_POST['newControlPosition']."',
						".$_POST['newComponentID'].", ".$_POST['code'].")";
	$insertRefRs = $myQuery->query($insertRef,'RUN');

	/*===insert at specified position===*/
	//get current orders for component by page
	$getOrder = "select CONTROLORDER, CONTROLID from FLC_PAGE_CONTROL
					where PAGEID=".$_POST['code']." and CONTROLID != ".$getMaxRs."
					order by CONTROLORDER,CONTROLID";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$getOrderRsCount = count($getOrderRs);

	//increment current component order
	$orderIncrement=false;
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		if($getOrderRs[$x][0]==$_POST['newControlOrder'])
		{
			$orderIncrement=true;
		}

		if($orderIncrement)
		{
			$orderUpdate = "update FLC_PAGE_CONTROL
							set CONTROLORDER=".(int)++$getOrderRs[$x][0]."
							where CONTROLID=".$getOrderRs[$x][1]."";
			$orderUpdateRs = $myQuery->query($orderUpdate,'RUN');
		}
	}

	//== permission =================================================================
	if($insertRefRs)
	{
		$selectedGroupCount=count($_POST['selectedGroup']);

		//loop on count
		for($x=0; $x<$selectedGroupCount; $x++)
		{
			//insert permission data into FLC_PERMISSION
			$insertPerm = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM, PERM_VALUE)
							values ('control', ".$_POST['selectedGroup'][$x].", ".$getMaxRs.", '1')";
			$insertPermRs = $myQuery->query($insertPerm,'RUN');
		}//eof for
	}//eof if
	//== eof permission =============================================================

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if save screen edit reference
else if($_POST['saveScreenRefEdit'])
{
	//set default name, if blank
	if(!$_POST['editControlName'])
		$_POST['editControlName'] = 'control_'.$_POST['hiddenCode'];

	//to prevent error
	if($_POST['editControlOrder'] == '')
		$_POST['editControlOrder'] = 1;

	if($_POST['editComponentID'] == '')
		$_POST['editComponentID'] = 'null';

	//update reference
	$updateRef = "update FLC_PAGE_CONTROL
					set CONTROLNAME = '".$_POST['editControlName']."',
					CONTROLTITLE = '".$_POST['editControlTitle']."',
					CONTROLTYPE = ".$_POST['editControlType'].",
					CONTROLORDER = ".$_POST['editControlOrder'].",
					CONTROLHINTS = '".$_POST['editControlHints']."',
					CONTROLNOTES = '".$_POST['editControlNotes']."',
					CONTROLREDIRECTURL = '".$_POST['editRedirectURL']."',
					CONTROLIMAGEURL = '".$_POST['editImageURL']."',
					CONTROLPOSITION = '".$_POST['editControlPosition']."',
					COMPONENTID = ".$_POST['editComponentID']."
					where CONTROLID = ".$_POST['hiddenCode'];
	$updateRefRs = $myQuery->query($updateRef,'RUN');

	/*===insert at specified position===*/
	//get current orders for component by page
	$getOrder = "select CONTROLORDER, CONTROLID from FLC_PAGE_CONTROL
					where PAGEID=".$_POST['code']." and CONTROLID != ".$_POST['hiddenCode']."
					order by CONTROLORDER,CONTROLID";
	$getOrderRs = $myQuery->query($getOrder,'SELECT');
	$getOrderRsCount = count($getOrderRs);

	//increment current component order
	$orderIncrement=false;
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		if($getOrderRs[$x][0]==$_POST['editControlOrder'])
		{
			$orderIncrement=true;
		}//eof if

		if($orderIncrement)
		{
			$orderUpdate = "update FLC_PAGE_CONTROL
							set CONTROLORDER=".(int)++$getOrderRs[$x][0]."
							where CONTROLID=".$getOrderRs[$x][1];
			$orderUpdateRs = $myQuery->query($orderUpdate,'RUN');
		}//eof if
	}//eof for

	//== permission =================================================================
	if($updateRefRs)
	{
		//delete previous permission
		$deletePermRs = $mySQL->deletePermission('control', $_POST['hiddenCode']);

		$selectedGroupCount=count($_POST['selectedGroupEdit']);

		//loop on count
		for($x=0; $x<$selectedGroupCount; $x++)
		{
			//insert permission data into FLC_PERMISSION
			$insertPerm = "insert into FLC_PERMISSION (PERM_TYPE, GROUP_ID, PERM_ITEM, PERM_VALUE)
							values ('control', ".$_POST['selectedGroupEdit'][$x].", ".$_POST['hiddenCode'].", '1')";
			$insertPermRs = $myQuery->query($insertPerm,'RUN');
		}//eof for
	}//eof if
	//== eof permission =============================================================

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if reference deleted
else if($_POST['deleteReference'])
{
	//delete control and everything related to it
	$deleteControlRs = $mySQL->deleteControl($_POST['hiddenCode']);

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//if reset ordering button clicked
else if($_POST['resetControlOrder'])
{
	//get menu ordering level 2
	$getOrder = "select CONTROLID from FLC_PAGE_CONTROL where PAGEID = ".$_POST['code']." order by CONTROLORDER";
	$getOrderRs = $myQuery->query($getOrder,'SELECT','NAME');
	$getOrderRsCount = count($getOrderRs);

	//update control
	for($x=0; $x<$getOrderRsCount; $x++)
	{
		$updateOrder = "update FLC_PAGE_CONTROL set CONTROLORDER = ".($x+1)." where CONTROLID = ".$getOrderRs[$x]['CONTROLID'];
		$updateOrderControlFlag = $myQuery->query($updateOrder,'RUN');
	}//eof for

	//display showScreen
	$_POST['showScreen'] = true;
}//eof if

//close screen
else if($_POST['closeScreen'])
{
	$_POST['showScreen'] = true;
}//eof elseif

//if have code
if($_POST['newReference'] || $_POST['editReference'])
{
	//have pageid
	if($_POST['code'])
	{
		//list of component by pageid
		$componentList = "select COMPONENTID, COMPONENTTITLE from FLC_PAGE_COMPONENT where PAGEID=".$_POST['code'];
		$componentListRs = $myQuery->query($componentList,'SELECT','NAME');
		$componentListRsCount = count($componentListRs);

		//get all control for the page
		$getAllControl = "select CONTROLNAME from FLC_PAGE_CONTROL where PAGEID=".$_POST['code'];
		$getAllControlRs = $myQuery->query($getAllControl,'SELECT','NAME');

		for($x=0; $x < count($getAllControlRs); $x++)
			$allControlName[] = $getAllControlRs[$x]['CONTROLNAME'];
	}//eof if

	//get list of control types
	$controlType = "select REFERENCECODE, DESCRIPTION1
					from REFSYSTEM
					where MASTERCODE =
						(select REFERENCECODE from REFSYSTEM where MASTERCODE = 'XXX' and DESCRIPTION1 = 'PAGE_CONTROL_TYPE')
					order by DESCRIPTION1";
	$controlTypeRs = $myQuery->query($controlType,'SELECT','NAME');
	$countControl = count($controlTypeRs);
}//eof if

//if new reference clicked
if($_POST['newReference'])
{
	//get max pageid
	$getMaxOrder = "select max(CONTROLORDER) from FLC_PAGE_CONTROL where PAGEID=".$_POST['code'];
	$getMaxOrderRs = $myQuery->query($getMaxOrder,'COUNT');



	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupPermissionNonSelected();
	$groupListNonSelectedCount=count($groupListNonSelected);
}//eof elseif

//if edit reference clicked, show detail
else if($_POST['editReference'])
{
	//show reference detail
	$showRef = "select b.CONTROLID, b.CONTROLNAME, b.CONTROLTITLE, b.CONTROLTYPE as CONTROLTYPEID, b.CONTROLORDER, b.CONTROLREDIRECTURL,
				b.CONTROLHINTS, b.CONTROLNOTES, b.PAGEID, c.DESCRIPTION1 as CONTROLTYPE,
				b.CONTROLIMAGEURL, b.CONTROLPOSITION, b.COMPONENTID
				from FLC_PAGE a, FLC_PAGE_CONTROL b, REFSYSTEM c
					where
					a.PAGEID = b.PAGEID
					and ".$mySQL->convertToChar('b.CONTROLTYPE')." = c.REFERENCECODE
					and c.MASTERCODE = (select REFERENCECODE from REFSYSTEM where MASTERCODE = 'XXX' and DESCRIPTION1 = 'PAGE_CONTROL_TYPE')
					and b.PAGEID = ".$_POST['code']."
					and b.CONTROLID = ".$_POST['hiddenCode']."
					order by CONTROLID";
	$showRefRs = $myQuery->query($showRef,'SELECT','NAME');

	//list group user non selected
	$groupListNonSelected=$mySQL->getUserGroupPermissionNonSelected('control',$showRefRs[0]['CONTROLID']);
	$groupListNonSelectedCount=count($groupListNonSelected);

	//list group user selected
	$groupListSelected=$mySQL->getUserGroupPermissionSelected('control',$showRefRs[0]['CONTROLID']);
	$groupListSelectedCount=count($groupListSelected);
}//eof elseif
//===========================================//reference==========================================

//if showScreen and code not null
if($_POST['showScreen'] && $_POST['code'])
{
	//get reference
	$reference = "select a.CONTROLID, a.CONTROLNAME, a.CONTROLTITLE, a.CONTROLORDER, a.CONTROLIMAGEURL, a.CONTROLNOTES, b.DESCRIPTION1 as CONTROLTYPE,
						(select COMPONENTNAME from FLC_PAGE_COMPONENT where COMPONENTID = a.COMPONENTID) COMPONENTNAME,
						(select count(*) from FLC_TRIGGER where TRIGGER_ITEM_TYPE = 'control' and TRIGGER_ITEM_ID = a.CONTROLID) TRIGGER_ID
					from FLC_PAGE_CONTROL a, REFSYSTEM b
					where ".$mySQL->convertToChar('a.CONTROLTYPE')." = b.REFERENCECODE
					and b.MASTERCODE = (select REFERENCECODE from REFSYSTEM where MASTERCODE = 'XXX' and DESCRIPTION1 = 'PAGE_CONTROL_TYPE')
					and a.PAGEID = ".$_POST['code']."
					order by a.CONTROLORDER";
	$referenceRs = $myQuery->query($reference,'SELECT','NAME');
	$countReference = count($referenceRs);
}//eof if

//get list of page menu
$generalRs = $mySQL->menu($_POST['pageSearch']);
$countGeneral = count($generalRs);
?>

<script language="javascript">
//enable/disable item base on value
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
			if(document.getElementById(relatedItems[x]))
				document.getElementById(relatedItems[x]).disabled = false;
		}//eof for
	}//eof if
	else
	{
		//loop on count of related items
		for(x=0; x<relatedItemsCount; x++)
		{
			//disable
			if(document.getElementById(relatedItems[x]))
				document.getElementById(relatedItems[x]).disabled = true;
		}//eof for
	}//eof else

	//display note for pdf button
	if( jQuery(elem).val()=='30' || jQuery(elem).val()=='31' )
	{
		//kalau takde lagi, append
		if( jQuery('#noteForPDFButton').length==0 ) jQuery('.labelMandatory').after('<span id="noteForPDFButton" style="font-style:italic;"> For Report Component Only</span>');
		//kalau dah ada, show
		if( jQuery('#noteForPDFButton').length==1 ) jQuery('#noteForPDFButton').show();
	}
	else
	{
		if( jQuery('#noteForPDFButton').length==1 ) jQuery('#noteForPDFButton').hide();
	}
}//eof function

function checkExistingControlName(name,type)
{
	var allName = jQuery('#control_allname').val();
	var allNameSplit = allName.split('|');
	var allNameSplitCnt = allNameSplit.length;

	if(type == 'edit')
	{
		for(var x=0; x < allNameSplitCnt; x++)
		{
			if(allNameSplit[x] == jQuery('#editOriginalControlName').val())
				allNameSplit.splice(x,1);
		}
	}

	for(var x=0; x < allNameSplitCnt; x++)
	{
		if(name == allNameSplit[x])
		{
			if(type == 'new')
			{
				jQuery('#'+type+'ControlName').val('').focus();
				window.alert('Control name already exists!');
			}
			else
			{
				jQuery('#'+type+'ControlName').val(jQuery('#editOriginalControlName').val()).focus();
				window.alert('Control name already exists! Reverting to previous name.');
			}
		}
	}
}
</script>
<script language="javascript" src="js/editor.js"></script>
<div id="breadcrumbs">System Administrator / Configuration / Control Editor</div>
<h1>Control Editor</h1>
<?php
if($insertRefRs)
{
	//notification
	showNotificationInfo('New control has been added');
}
else if($updateRefRs)
{
	//notification
	showNotificationInfo('Control has been updated');
}
else if($deleteControlRs)
{
	//notification
	showNotificationInfo('Control has been deleted');
}
else if($updateOrderControlFlag)
{
	//notification
	showNotificationInfo('Control ordering is now optimized');
}
?>

<form method="post" enctype="multipart/form-data" name="form1">
  <?php if(!isset($_POST['editScreen']))  { ?>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Page List </th>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Page Search : </td>
      <td valign="top"><input name="pageSearch" type="text" id="pageSearch" size="50" class="inputInput" value="<?php echo $_POST['pageSearch']?>" onkeyup="ajaxUpdatePageSelector('page','updateSelectorDropdown',this.value)" />
      </td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Page : </td>
      <td>
	  <div id="updateSelectorDropdown">
	  <select name="code" class="inputList" id="code" onChange="codeDropDown(this, 'showScreen');">
          <option value="">&lt; Select Page &gt;</option>
          <?php for($x=0; $x < $countGeneral; $x++) { ?>
          <option value="<?php echo $generalRs[$x]['PAGEID']?>" <?php if($_POST['code'] == $generalRs[$x]['PAGEID']) echo "selected";?>><?php echo $generalRs[$x]['MENUTITLE']?></option>
          <?php } ?>
      </select>
        <input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Show List" <?php if(!$_POST['code']){?>disabled="disabled"<?php }?> />
	  </div>
	  </td>
    </tr>
  </table>
  <?php }?>
  <?php if($_POST['newReference'] && !isset($_POST['saveScreenRefNew']) && !isset($_POST['showScreen']) && !isset($_POST['cancelScreen'])){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">New Control </th>
    </tr>
    <tr>
      <td class="inputLabel">Title : </td>
      <td>
      	<input name="newControlTitle" type="text" class="inputInput" id="newControlTitle" size="100" />
        <script>document.getElementById('newControlTitle').focus();</script>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Name : </td>
      <td>
      	<input name="newControlName" type="text" class="inputInput" id="newControlName" size="50" onchange="trim(this); checkExistingControlName(this.value,'new');" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
        <input type="hidden" name="control_allname" id="control_allname" value="<?php if(count($allControlName)) echo implode('|',$allControlName) ?>" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<select name="newControlType" class="inputList" id="newControlType" onchange="codeDropDown(this, 'saveScreenRefNew');" />
          <option value="">&nbsp;</option>
		  <?php for($x=0; $x<$countControl; $x++){?>
          <option value="<?php echo $controlTypeRs[$x]['REFERENCECODE']?>" <?php if($_POST['newControlType'] == $controlTypeRs[$x]['REFERENCECODE']) { ?>selected<?php }?> ><?php echo $controlTypeRs[$x]['DESCRIPTION1']?></option>
          <?php }?>
        </select>
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
       <td class="inputLabel">Order : </td>
       <td><label>
         <input name="orderOption" type="radio" checked="checked" onclick="swapItemDisplay('newControlOrderText', 'newControlOrderCombo')" />
         At</label>
           <label>
           <input name="orderOption" type="radio" onclick="swapItemDisplay('newControlOrderCombo', 'newControlOrderText'); showControl('<?php echo $_POST['code'];?>')" />
             Before</label>
           <label>
           <input name="orderOption" type="radio" value="++" onclick="swapItemDisplay('newControlOrderCombo', 'newControlOrderText'); showControl('<?php echo $_POST['code'];?>')" />
             After</label>
           <br />
           <input name="newControlOrder" type="text" class="inputInput" id="newControlOrderText" value="<?php echo $getMaxOrderRs+1;?>" size="5" />
           <span id="hideEditorList"> </span> </td>
     </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Redirect URL : </td>
	  <td>
      	<input name="newRedirectURL" type="text" class="inputInput" id="newRedirectURL" size="70" />
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=page&type=url&lovValue=newRedirectURL', 1, 1, 600, 500);" />
      </td>
    </tr>
	<tr>
	  <td nowrap="nowrap" class="inputLabel">Image Path : </td>
	  <td>
      	<input name="newImageURL" type="text" class="inputInput" id="newImageURL" size="70" />
	    <br /><label class="labelNote">Note: Leave blank to use default button</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Hints : </td>
      <td><textarea name="newControlHints" cols="50" rows="3" class="inputInput" id="newControlHints"></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Notes : </td>
      <td><textarea name="newControlNotes" cols="50" rows="3" class="inputInput" id="newControlNotes"></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Component Binding : </td>
      <td><select name="newComponentID" class="inputList" id="newComponentID">
        <option value="">&nbsp;</option>
        <?php for($x=0; $x<$componentListRsCount; $x++){?>
        <option value="<?php echo $componentListRs[$x]['COMPONENTID']?>" ><?php echo $componentListRs[$x]['COMPONENTTITLE']?></option>
        <?php } ?>
      </select></td>
    </tr>
    <tr>
      <td class="inputLabel">Control Position : </td>
      <td><select name="newControlPosition" class="inputList" id="newControlPosition">
		<option value="right" >Right (Default)</option>
		<option value="center" >Center</option>
		<option value="left" >Left</option>

      </select></td>
    </tr>
  </table>
  <br />

  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Control Permission</th>
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
              <select style="width:250px;height:150px;" name="selectedGroup[]" size="10" multiple class="inputList" id="selectedGroup"></select>
            </td>
          </tr>
        </table>
        <?php if(!CONTROL_PERMISSION_ENABLED){?>
		<label class="labelNote">Note: Usage of control permission is disabled. Access permission set here will be ignored.</label>
		<?php }?>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
          <input name="newReference" type="hidden" id="newReference" value="<?php echo $_POST['newReference'];?>" />
          <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
          <input name="saveScreenRefNew" onclick="listBoxSelectall('selectedGroup');" type="submit" disabled="disabled" class="inputButton" id="saveScreenRefNew" value="Save" />
          <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>
  <?php }?>

  <?php if($_POST['editReference'] && !isset($_POST['cancelScreen']) && !isset($_POST['showScreen'])){?>
  <br />
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Modify Control </th>
    </tr>
    <tr>
        <td class="inputLabel">Title : </td>
	    <td>
        	<input name="editControlTitle" type="text" class="inputInput" id="editControlTitle" value="<?php echo $showRefRs[0]['CONTROLTITLE'];?>" size="100" />
            <script>document.getElementById('editControlTitle').focus();</script>
        </td>
    </tr>
	<tr>
        <td class="inputLabel">Name : </td>
	    <td>
        	<input name="editControlName" type="text" class="inputInput" id="editControlName" onchange="trim(this); checkExistingControlName(this.value,'edit');" onkeyup="form1.saveScreenRefEdit.disabled = false" value="<?php echo $showRefRs[0]['CONTROLNAME']?>" size="50" />
        	<input name="editOriginalControlName" type="hidden" id="editOriginalControlName" value="<?php echo $showRefRs[0]['CONTROLNAME']?>" />
            <br /><label class="labelNote">Note: Leave blank for system generated.</label>
              <input type="hidden" name="control_allname" id="control_allname" value="<?php echo implode('|',$allControlName) ?>" />
        </td>
    </tr>
	<tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<select name="editControlType" class="inputList" id="editControlType" onchange="codeDropDown(this, 'saveScreenRefEdit');" />
          <option value="">&nbsp;</option>
		  <?php for($x=0; $x<$countControl; $x++){?>
          <option value="<?php echo $controlTypeRs[$x]['REFERENCECODE']?>" <?php
		  		if(!isset($_POST['editControlType']))
				{
					if($showRefRs[0]['CONTROLTYPEID'] == $controlTypeRs[$x]['REFERENCECODE'])
						echo "selected";
				}
					else if($_POST['editControlType'] == $controlTypeRs[$x]['REFERENCECODE']) echo "selected";?> ><?php echo $controlTypeRs[$x]['DESCRIPTION1']?></option>
          <?php } ?>
        </select>
        <label class="labelMandatory">*</label>
        <?php
        if( $showRefRs[0]['CONTROLTYPEID']=='30' || $showRefRs[0]['CONTROLTYPEID']=='31')
        {
        	echo '<span id="noteForPDFButton" style="font-style:italic;"> For Report Component Only</span>';
        }
        ?>
      </td>
    </tr>
	<tr>
        <td class="inputLabel">Order : </td>
	    <td><label>
          <input name="orderOption" type="radio" checked="checked" onclick="swapItemDisplay('editControlOrderText', 'editControlOrderCombo')" />
	      At</label>
            <label>
            <input name="orderOption" type="radio" onclick="swapItemDisplay('editControlOrderCombo', 'editControlOrderText'); showControl('<?php echo $_POST['code'];?>', '<?php echo $_POST['hiddenCode'];?>')" />
              Before</label>
            <label>
            <input name="orderOption" type="radio" value="++" onclick="swapItemDisplay('editControlOrderCombo', 'editControlOrderText'); showControl('<?php echo $_POST['code'];?>', '<?php echo $_POST['hiddenCode'];?>')" />
              After</label>
            <br />
            <input name="editControlOrder" id="editControlOrderText" type="text" class="inputInput" value="<?php echo $showRefRs[0]['CONTROLORDER']?>" size="5" />
            <span id="hideEditorList"> </span> </td>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Redirect URL : </td>
	  <td>
      	<input name="editRedirectURL" type="text" class="inputInput" id="editRedirectURL" value="<?php echo $showRefRs[0]['CONTROLREDIRECTURL']?>" size="70" />
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=page&type=url&lovValue=editRedirectURL', 1, 1, 600, 500);" />
      </td>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Image Path : </td>
	  <td>
      	<input name="editImageURL" type="text" class="inputInput" id="editImageURL" value="<?php echo $showRefRs[0]['CONTROLIMAGEURL']?>" size="70" />
	    <br /><label class="labelNote">Note: Leave blank to use default button</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Hints : </td>
      <td><textarea name="editControlHints" cols="50" rows="3" class="inputInput" id="editControlHints"><?php echo $showRefRs[0]['CONTROLHINTS']?></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Notes : </td>
      <td><textarea name="editControlNotes" cols="50" rows="3" class="inputInput" id="editControlNotes"><?php echo $showRefRs[0]['CONTROLNOTES']?></textarea></td>
    </tr>
    <tr>
      <td class="inputLabel">Component Binding : </td>
      <td><select name="editComponentID" class="inputList" id="editComponentID">
			  <option value="" selected="selected">&nbsp;</option>
			  <?php for($x=0; $x < $componentListRsCount; $x++) { ?>
			  <option value="<?php echo $componentListRs[$x]['COMPONENTID']?>" <?php if($componentListRs[$x]['COMPONENTID']==$showRefRs[0]['COMPONENTID']){?> selected="selected"<?php }?> ><?php echo $componentListRs[$x]['COMPONENTTITLE']?></option>
			  <?php } ?>
          </select></td>
    </tr>
     <tr>
      <td class="inputLabel">Control Position : </td>
      <td><select name="editControlPosition" class="inputList" id="editControlPosition">
		<option value="right" <?php if($showRefRs[0]['CONTROLPOSITION'] == 'right'){?> selected="selected"<?php }?>>Right (Default)</option>
		<option value="center"  <?php if($showRefRs[0]['CONTROLPOSITION'] == 'center'){?> selected="selected"<?php }?>>Center</option>
		<option value="left"  <?php if($showRefRs[0]['CONTROLPOSITION'] == 'left'){?> selected="selected"<?php }?>>Left</option>

      </select></td>
    </tr>
  </table>
  <br />

  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Control Permission</th>
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
              <select style="width:250px;height:150px;" name="selectedGroupEdit[]" size="10" multiple class="inputList" id="selectedGroupEdit" ><?php for($x=0; $x<$groupListSelectedCount; $x++){?>
                <option value="<?php echo $groupListSelected[$x][0]?>"><?php echo $groupListSelected[$x][1];?></option>
                <?php }?></select>
            </td>
          </tr>
        </table>
        <?php if(!CONTROL_PERMISSION_ENABLED){?>
		<label class="labelNote">Note: Usage of control permission is disabled. Access permission set here will be ignored.</label>
		<?php }?>
      </td>
   </tr>
   <tr>
      <td colspan="2" class="contentButtonFooter">
          <input name="editReference" type="hidden" id="editReference" value="<?php echo $_POST['editReference'];?>" />
          <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
          <input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $_POST['hiddenCode'];?>" />
          <input name="saveScreenRefEdit" onclick="listBoxSelectall('selectedGroupEdit');" type="submit" class="inputButton" id="saveScreenRefEdit" value="Save" />
          <input name="closeScreen" type="submit" class="inputButton" id="closeScreen" value="Close" />
      </td>
    </tr>
  </table>

  <?php } ?>
</form>
<?php if($_POST['showScreen'] && $_POST['code']){?>
<br />
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="9">Control List </th>
  </tr>
  <?php if($countReference){?>
  <tr>
    <th width="10" class="listingHead">#</th>
    <th width="15" class="listingHead">ID</th>
    <th width="100" class="listingHead">Component</th>
    <th width="100" class="listingHead">Name</th>
    <th class="listingHead">Title</th>
    <th width="100" class="listingHead">Type</th>
    <th width="30" class="listingHead">Order</th>
    <th width="30" class="listingHead">Trigger</th>
    <th width="100" class="listingHead">Action</th>
  </tr>
  <?php for($x=0; $x<$countReference; $x++){?>
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
    <td class="listingContent"><?php echo $x+1;?>.</td>
    <td class="listingContent"><?php echo $referenceRs[$x]['CONTROLID'];?></td>
    <td class="listingContent"><?php if($referenceRs[$x]['COMPONENTNAME']){echo $referenceRs[$x]['COMPONENTNAME'];}else {echo '-';}?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['CONTROLNAME'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['CONTROLTITLE'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['CONTROLTYPE'];?></td>
    <td class="listingContent"><?php echo $referenceRs[$x]['CONTROLORDER'];?></td>
    <td class="listingContent"><?php if($referenceRs[$x]['TRIGGER_ID']) echo 'Yes'; else echo 'No';?></td>
    <td nowrap="nowrap" class="listingContentRight"><form id="formReference<?php echo $referenceRs[$x]['code'];?>" name="formReference<?php echo $referenceRs[$x]['code'];?>" method="post" action="">
        <input name="editReference" type="submit" class="inputButton" id="editReference" value="Update" />
        <input name="deleteReference" type="submit" class="inputButton" id="deleteReference" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this control?')) {return true} else {return false}"/>
        <input name="hiddenCode" type="hidden" id="hiddenCode" value="<?php echo $referenceRs[$x]['CONTROLID'];?>" />
        <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
      </form></td>
  </tr>
  <?php 		} //end for ?>
  <?php 	}//end if
	else 	{ ?>
  <tr>
    <td colspan="9" class="myContentInput">&nbsp;&nbsp;No control(s) found.. </td>
  </tr>
  <?php 	} //end else?>
  <tr>
    <td colspan="9" class="contentButtonFooter">
        <form id="form2" name="form2" method="post" action="">
          <input name="code" type="hidden" id="code" value="<?php echo $_POST['code'];?>" />
		  <input name="resetControlOrder" type="submit" class="inputButton" id="resetControlOrder" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the control order?')) {return true} else {return false}" />
          <input name="newReference" type="submit" class="inputButton" id="newReference" value="New Control" />
        </form>
    </td>
  </tr>
</table>
<br />
<?php } ?>