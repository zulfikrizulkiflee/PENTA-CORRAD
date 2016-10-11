<?php
require_once('system_prerequisite.php');

//validate that user have session
require_once('func_common.php');
validateUserSession();

//==================================== manipulation ==========================================
//insert/update (set selected / default value)
if($_POST['insert']||$_POST['update'])
{
	//switch trigger type
	switch($_POST['trigger_type'])
	{
		//javascript
		case 'JS':
			$_POST['trigger_event'] = $_POST['trigger_event_js'];
			$_POST['trigger_bl'] = $_POST['trigger_bl_js'];
		break;

		//php
		case 'PHP':
			$_POST['trigger_event'] = $_POST['trigger_event_php'];
			$_POST['trigger_bl'] = $_POST['trigger_bl_php'];
		break;
	}//eof switch

	//switch element
	if($_POST['trigger_element'] == 'page')
		$_POST['trigger_element_id'] = $_POST['pageID'];
	else if($_POST['trigger_element'] == 'component')
		$_POST['trigger_element_id'] = $_POST['componentID'];
	else if($_POST['trigger_element'] == 'item')
		$_POST['trigger_element_id'] = $_POST['itemID'];
	else if($_POST['trigger_element'] == 'control')
		$_POST['trigger_element_id'] = $_POST['controlID'];

	//default value
	if(!$_POST['trigger_order'])
		$_POST['trigger_order'] = 1;
}//eof if

//insert
if($_POST['insert'])
{
	//get max trigger id
	$maxTriggerIdRs = $mySQL->maxValue('FLC_TRIGGER','TRIGGER_ID',0)+1;

	//insert trigger
	$insertTrigger = "insert into FLC_TRIGGER
						(TRIGGER_ID, TRIGGER_TYPE, TRIGGER_EVENT, TRIGGER_BL, TRIGGER_ITEM_TYPE, TRIGGER_ITEM_ID, TRIGGER_ORDER, TRIGGER_STATUS)
						values
						(".$maxTriggerIdRs.", '".$_POST['trigger_type']."', '".$_POST['trigger_event']."', '".$_POST['trigger_bl']."',
							'".$_POST['trigger_element']."', ".$_POST['trigger_element_id'].", ".$_POST['trigger_order'].",
							".$_POST['trigger_status'].")";
	$insertTriggerRs = $myQuery->query($insertTrigger,'RUN');

	//if trigger inserted
	if($insertTriggerRs)
	{
		//insert trigger parameter (if any)
		$parameterCount = count($_POST['parameter']);

		//loop on count of parameter
		for($x=0; $x<$parameterCount; $x++)
		{
			//if have value
			if($_POST['parameter'][$x])
			{
				//insert parameter
				$insertParameter = "insert into FLC_TRIGGER_PARAMETER (TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
									values
									(".$maxTriggerIdRs.", ".++$paramSeq.",'".$_POST['parameter'][$x]."')";
				$insertParameterRs = $myQuery->query($insertParameter,'RUN');
			}//eof if
		}//eof for

		//notification
		showNotificationInfo('New trigger has been added.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to add new trigger!');
	}//eof else

	//show listing
	$_POST['showScreen'] = true;
}//eof if

//update
else if($_POST['update'])
{
	//update trigger
	$updateTrigger = "update FLC_TRIGGER set
						TRIGGER_TYPE = '".$_POST['trigger_type']."',
						TRIGGER_EVENT = '".$_POST['trigger_event']."',
						TRIGGER_BL = '".$_POST['trigger_bl']."',
						TRIGGER_ITEM_TYPE = '".$_POST['trigger_element']."',
						TRIGGER_ITEM_ID = ".$_POST['trigger_element_id'].",
						TRIGGER_ORDER = ".$_POST['trigger_order'].",
						TRIGGER_STATUS = ".$_POST['trigger_status']."
						where TRIGGER_ID = ".$_POST['trigger_id'];
	$updateTriggerRs = $myQuery->query($updateTrigger,'RUN');

	//if trigger updated
	if($updateTriggerRs)
	{
		//insert trigger parameter (if any)
		$parameterCount = count($_POST['parameter']);

		//delete previous parameter
		$deleteParameter = "delete from FLC_TRIGGER_PARAMETER where TRIGGER_ID = ".$_POST['trigger_id'];
		$deleteParameterRs = $myQuery->query($deleteParameter,'RUN');

		//loop on count of parameter
		for($x=0; $x<$parameterCount; $x++)
		{
			//if have value
			if($_POST['parameter'][$x])
			{
				//insert parameter
				$insertParameter = "insert into FLC_TRIGGER_PARAMETER (TRIGGER_ID, PARAMETER_SEQ, PARAMETER_VALUE)
									values
									(".$_POST['trigger_id'].", ".++$paramSeq.",'".$_POST['parameter'][$x]."')";
				$insertParameterRs = $myQuery->query($insertParameter,'RUN');
			}//eof if
		}//eof for

		//notification
		showNotificationInfo('Trigger has been updated.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to update trigger!');
	}//eof else

	//show listing
	$_POST['showScreen'] = true;
}//eof elseif

//delete
else if($_POST['delete'])
{
	//delete trigger and everything related to it
	$deleteTriggerRs = $mySQL->deleteTrigger($_POST['trigger_id']);

	//if trigger deleted
	if($deleteTriggerRs)
	{
		//notification
		showNotificationInfo('Trigger has been deleted.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to delete trigger!');
	}//eof else

	//show listing
	$_POST['showScreen'] = true;
}//eof elseif

//moveup
else if($_POST['moveUp'])
{
	//check current ORDER position
	$checkPos = "select TRIGGER_ORDER from FLC_TRIGGER where TRIGGER_ID = ".$_POST['trigger_id'];
	$checkPosRs = $myQuery->query($checkPos,'SELECT','NAME');

	//if current position is NOT 1, move up
	if($checkPosRs[0]['TRIGGER_ORDER'] != 1)
	{
		//decrement order
		$decrOrder = "update FLC_TRIGGER set TRIGGER_ORDER = ".($checkPosRs[0]['TRIGGER_ORDER'] - 1)."
						where TRIGGER_ID = ".$_POST['trigger_id'];
		$decrOrderRs = $myQuery->query($decrOrder,'RUN');
	}//eof if

	//show listing
	$_POST['showScreen'] = true;
}//eof elseif

//moveDown
else if($_POST['moveDown'])
{
	//increment order
	$incrOrder = "update FLC_TRIGGER set TRIGGER_ORDER = TRIGGER_ORDER+1 where TRIGGER_ID = ".$_POST['trigger_id'];
	$incrOrderRs = $myQuery->query($incrOrder,'RUN');

	//show listing
	$_POST['showScreen'] = true;
}//eof elseif

//resetOrder
else if($_POST['resetOrder'])
{
	//reset order

	//show listing
	$_POST['showScreen'] = true;
}//eof elseif

//close
else if($_POST['close'])
{
	//show listing
	$_POST['showScreen'] = true;
}//eof elseif
//================================== eof manipulation ========================================

//====================================== display =============================================
//new/edit trigger
if($_POST['new']||$_POST['edit'])
{
	if($_POST['code'] == 'system')
	{
	}
	else
	{
		//get page
		$getPage = "select PAGEID, PAGENAME, PAGETITLE from FLC_PAGE where PAGEID = ".$_POST['code'];
		$getPageRs = $myQuery->query($getPage,'SELECT','NAME');

		//get component list
		$getComponent = "select COMPONENTID, ".$mySQL->concat("'['",COMPONENTNAME,"'] '",COMPONENTTITLE)."
							from FLC_PAGE_COMPONENT
							where PAGEID = ".$_POST['code']."
							order by 2";
		$getComponentRs = $myQuery->query($getComponent,'SELECT','NAME');
		$getComponentRsCount = count($getComponentRs);

		//get item list
		$getItem = "select a.ITEMID, ".$mySQL->concat("'['",ITEMNAME,"'] '",ITEMTITLE)."
						from FLC_PAGE_COMPONENT_ITEMS a, FLC_PAGE_COMPONENT b
						where a.COMPONENTID = b.COMPONENTID and b.PAGEID = ".$_POST['code']."
						order by ITEMNAME";
		$getItemRs = $myQuery->query($getItem,'SELECT','NAME');
		$getItemRsCount = count($getItemRs);

		//get control list
		$getControl = "select CONTROLID, ".$mySQL->concat("'['",CONTROLNAME,"'] '",CONTROLTITLE)."
						from FLC_PAGE_CONTROL
						where PAGEID = ".$_POST['code']."
						order by CONTROLNAME";
		$getControlRs = $myQuery->query($getControl,'SELECT','NAME');
		$getControlRsCount = count($getControlRs);
	}

	//js bl trigger
	$triggerEventJsRs = $mySQL->listJsEvent();
	$triggerBLJsRs = $mySQL->listActiveJsBL();

	//php bl trigger
	$triggerEventPhpRs = $mySQL->listPhpEvent();
	$triggerBLPhpRs = $mySQL->listActivePhpBL();
}//eof if

//edit trigger
if($_POST['edit'])
{
	//trigger info
	$getTriggerInfo = "select * from FLC_TRIGGER where TRIGGER_ID = ".$_POST['trigger_id'];
	$getTriggerInfoRs = $myQuery->query($getTriggerInfo, 'SELECT', 'NAME');
	$getTriggerInfoRsCount = count($getTriggerInfoRs);

	//trigger parameter
	$getTriggerParameter = "select * from FLC_TRIGGER_PARAMETER where TRIGGER_ID = ".$_POST['trigger_id']." order by PARAMETER_SEQ";
	$getTriggerParameterRs = $myQuery->query($getTriggerParameter, 'SELECT', 'NAME');
	$getTriggerParameterRsCount = count($getTriggerParameterRs);
}//eof if

//trigger listing, if not in add/edit mode
if($_POST['showScreen'] && $_POST['code'])
{
	//if page selected
	if($_POST['code'] && $_POST['code'] != 'system')
	{
		//get all triggers
		$triggerList = "select * from
						(
							select 'page' ELEMENT, a.PAGENAME NAME, a.PAGETITLE TITLE,
								d.TRIGGER_ID, d.TRIGGER_TYPE, d.TRIGGER_EVENT, d.TRIGGER_ORDER, d.TRIGGER_STATUS, d.TRIGGER_BL
							from FLC_PAGE a, FLC_TRIGGER d
							where a.PAGEID = ".$_POST['code']."
								and d.TRIGGER_ITEM_TYPE = 'page' and d.TRIGGER_ITEM_ID = a.PAGEID
							union
							select 'component' ELEMENT, b.COMPONENTNAME NAME, b.COMPONENTTITLE TITLE,
								d.TRIGGER_ID, d.TRIGGER_TYPE, d.TRIGGER_EVENT, d.TRIGGER_ORDER, d.TRIGGER_STATUS, d.TRIGGER_BL
							from FLC_PAGE a, FLC_PAGE_COMPONENT b, FLC_TRIGGER d
							where a.PAGEID = ".$_POST['code']." and a.PAGEID = b.PAGEID
								and d.TRIGGER_ITEM_TYPE = 'component' and d.TRIGGER_ITEM_ID = b.COMPONENTID
							union
							select 'item' ELEMENT, c.ITEMNAME NAME, c.ITEMTITLE TITLE,
								d.TRIGGER_ID, d.TRIGGER_TYPE, d.TRIGGER_EVENT, d.TRIGGER_ORDER, d.TRIGGER_STATUS, d.TRIGGER_BL
							from FLC_PAGE a, FLC_PAGE_COMPONENT b, FLC_PAGE_COMPONENT_ITEMS c, FLC_TRIGGER d
							where a.PAGEID = ".$_POST['code']." and a.PAGEID = b.PAGEID and b.COMPONENTID = c.COMPONENTID
								and d.TRIGGER_ITEM_TYPE = 'item' and d.TRIGGER_ITEM_ID = c.ITEMID
							union
							select 'control' ELEMENT, c.CONTROLNAME NAME, c.CONTROLTITLE TITLE,
								d.TRIGGER_ID, d.TRIGGER_TYPE, d.TRIGGER_EVENT, d.TRIGGER_ORDER, d.TRIGGER_STATUS, d.TRIGGER_BL
							from FLC_PAGE a, FLC_TRIGGER d, FLC_PAGE_CONTROL c
							where a.PAGEID = ".$_POST['code']." and a.PAGEID = c.PAGEID
								and d.TRIGGER_ITEM_TYPE = 'control' and d.TRIGGER_ITEM_ID = c.CONTROLID
						) a
						order by ".$mySQL->customOrder('ELEMENT', array('page', 'component', 'item', 'control')).",
							NAME, TITLE, TRIGGER_EVENT, TRIGGER_ORDER";
	}
	else
	{
		$triggerList = "select 	'system' ELEMENT, TRIGGER_ID, TRIGGER_TYPE, TRIGGER_EVENT, TRIGGER_ORDER, TRIGGER_STATUS, TRIGGER_BL
							from FLC_TRIGGER
							where TRIGGER_ITEM_TYPE = 'system'
							order by TRIGGER_EVENT, TRIGGER_ORDER";

	}

	$triggerListRs = $myQuery->query($triggerList, 'SELECT', 'NAME');
	$triggerListRsCount = count($triggerListRs);

	//get url for bl editor
	$getBlUrl = "select ".$mySQL->concat("MENULINK","'&menuID='",$mySQL->convertToChar("MENUID"))." MENULINK from FLC_MENU where MENULINK like '%index.php?page=bl_editor%'";
	$getBlUrlRs = $myQuery->query($getBlUrl, 'SELECT', 'NAME');
	//}//eof if
}//eof if

//get list of page menu
$generalRs = $mySQL->menu($_POST['pageSearch']);
$generalRsCount = count($generalRs);
//==================================== eof display ===========================================
?>

<script language="javascript" type="text/javascript" src="js/editor.js"></script>
<script>
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

//swap display of element by element type
function swapElementType(elementType)
{
	//switch element type
	switch(elementType)
	{
		case 'page': swapItemDisplay('', 'componentDivId|itemDivId|controlDivId');
			break;
		case 'component': swapItemDisplay('componentDivId', 'itemDivId|controlDivId');
			break;
		case 'item': swapItemDisplay('itemDivId', 'componentDivId|controlDivId');
			break;
		case 'control': swapItemDisplay('controlDivId', 'componentDivId|itemDivId');
			break;
	}//efo switch
}//eof function

//swap display of event and bl by trigger type
function swapTriggerType(triggerType)
{
	//switch trigger type
	switch(triggerType)
	{
		case 'JS': swapItemDisplay('eventJsDivId|blJsDivId', 'eventPhpDivId|blPhpDivId');
			break;
		case 'PHP': swapItemDisplay('eventPhpDivId|blPhpDivId', 'eventJsDivId|blJsDivId');
			break;
	}//efo switch
}//eof function
</script>

<div id="breadcrumbs">System Administrator / Configuration / Trigger Editor</div>
<h1>Trigger Editor</h1>

<form method="post" name="form1">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Page List </th>
    </tr>
	<tr>
      <td nowrap="nowrap" class="inputLabel">Page Search : </td>
      <td>
      	<input name="pageSearch" type="text" id="pageSearch" size="50" class="inputInput" value="<?php echo $_POST['pageSearch']?>"
        	onkeyup="ajaxUpdatePageSelector('page','pageSelectorDropdown',this.value)" />
      </td>
    </tr>
    <tr>
      <td nowrap="nowrap" class="inputLabel">Page : </td>
      <td>
        <div id="pageSelectorDropdown">
          <select name="code" class="inputList" id="code" onchange="codeDropDown(this, 'showScreen');">
            <option value="">&lt; Select Page &gt;</option>
            <option value="system" <?php if($_POST['code'] == 'system') echo "selected";?>>System level trigger</option>
            <optgroup label="Select Page">
            <?php for($x=0; $x<$generalRsCount; $x++) { ?>
            <option value="<?php echo $generalRs[$x]['PAGEID']?>" <?php if($_POST['code'] == $generalRs[$x]['PAGEID']) echo "selected";?>>[<?php echo $generalRs[$x]['PAGEID']?>] - <?php echo $generalRs[$x]['MENUTITLE']?></option>
            <?php } ?>
            </optgroup>
          </select>
          <input name="showScreen" type="submit" class="inputButton" id="showScreen" value="Show List" />
        </div>
      </td>
    </tr>
  </table>
  <br />

  <?php if($_POST['new']||$_POST['edit']){?>
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2"><?php if($_POST['new']){?>New<?php }else if($_POST['edit']){?>Edit<?php }?> Trigger </th>
    </tr>
    <tr>
      <td class="inputLabel">Element : </td>
      <td>
      	<input type="hidden" name="trigger_id" id="trigger_id" value="<?php echo $getTriggerInfoRs[0]['TRIGGER_ID'];?>" />
        <?php if($_POST['code'] == 'system') { ?>
        <label>SYSTEM</label>
        <?php } else { ?>
        <label><input id="trigger_element" name="trigger_element" type="radio" value="page" <?php if($_POST['new']||$getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']=='page'){?>checked="checked"<?php }?> onchange="swapElementType(this.value);" />Page</label>
        <label><input id="trigger_element" name="trigger_element" type="radio" value="component" <?php if($getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']=='component'){?>checked="checked"<?php }?> onchange="swapElementType(this.value);" />Component</label>
        <label><input id="trigger_element" name="trigger_element" type="radio" value="item" <?php if($getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']=='item'){?>checked="checked"<?php }?> onchange="swapElementType(this.value);" />Item</label>
        <label><input id="trigger_element" name="trigger_element" type="radio" value="control" <?php if($getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']=='control'){?>checked="checked"<?php }?> onchange="swapElementType(this.value);" />Control </label>
        <?php } ?>
        <input type="hidden" name="pageID" id="pageID" value="<?php echo $_POST['code'];?>" />
        <div id="componentDivId" <?php if($getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']!='component'){?>style="display:none;"<?php }?>>
            <select id="componentID" name="componentID" class="inputList">
              <?php echo createDropDown($getComponentRs, $getTriggerInfoRs[0]['TRIGGER_ITEM_ID']);?>
            </select>
            <label class="labelMandatory">*</label>
        </div>
      	<div id="itemDivId" <?php if($getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']!='item'){?>style="display:none;"<?php }?>>
            <select id="itemID" name="itemID" class="inputList">
              <?php echo createDropDown($getItemRs, $getTriggerInfoRs[0]['TRIGGER_ITEM_ID']);?>
            </select>
            <label class="labelMandatory">*</label>
        </div>
      	<div id="controlDivId" <?php if($getTriggerInfoRs[0]['TRIGGER_ITEM_TYPE']!='control'){?>style="display:none;"<?php }?>>
            <select id="controlID" name="controlID" class="inputList">
              <?php echo createDropDown($getControlRs, $getTriggerInfoRs[0]['TRIGGER_ITEM_ID']);?>
            </select>
            <label class="labelMandatory">*</label>
        </div>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Type : </td>
      <td>
      	<label><input id="trigger_type" name="trigger_type" type="radio" value="JS" <?php if($_POST['new']||$getTriggerInfoRs[0]['TRIGGER_TYPE']=='JS'){?>checked="checked"<?php }?> onchange="swapTriggerType('JS');" />JS</label>
        <label><input id="trigger_type" name="trigger_type" type="radio" value="PHP" <?php if($getTriggerInfoRs[0]['TRIGGER_TYPE']=='PHP'){?>checked="checked"<?php }?> onchange="swapTriggerType('PHP');" />PHP</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Event : </td>
      <td>
      	<div id="eventJsDivId" <?php if($getTriggerInfoRs[0]['TRIGGER_TYPE']=='PHP'){?>style="display:none;"<?php }?>>
        <select id="trigger_event_js" name="trigger_event_js" class="inputInput">
          <?php echo createDropDown($triggerEventJsRs, $getTriggerInfoRs[0]['TRIGGER_EVENT']);?>
        </select>
        <label class="labelMandatory">*</label>
        </div>
        <div id="eventPhpDivId" <?php if($_POST['new']||$getTriggerInfoRs[0]['TRIGGER_TYPE']=='JS'){?>style="display:none;"<?php }?>>
        <select id="trigger_event_php" name="trigger_event_php" class="inputInput">
          <?php echo createDropDown($triggerEventPhpRs, $getTriggerInfoRs[0]['TRIGGER_EVENT']);?>
        </select>
        <label class="labelMandatory">*</label>
        </div>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">BL : </td>
      <td>
      	<div id="blJsDivId" <?php if($getTriggerInfoRs[0]['TRIGGER_TYPE']=='PHP'){?>style="display:none;"<?php }?>>
        <select id="trigger_bl_js" name="trigger_bl_js" class="inputInput">
          <?php echo createDropDown($triggerBLJsRs, $getTriggerInfoRs[0]['TRIGGER_BL']);?>
        </select>
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=trigger&type=JS&lovValue=trigger_bl_js', 1, 1, 500, 500);" />
        <label class="labelMandatory">*</label>
        </div>
        <div id="blPhpDivId" <?php if($_POST['new']||$getTriggerInfoRs[0]['TRIGGER_TYPE']=='JS'){?>style="display:none;"<?php }?>>
        <select id="trigger_bl_php" name="trigger_bl_php" class="inputInput">
          <?php echo createDropDown($triggerBLPhpRs, $getTriggerInfoRs[0]['TRIGGER_BL']);?>
        </select>
        <input id="lovEditorButton" name="lovEditorButton" type="button" class="inputButton" value="..." onclick="my_popup('lov_editor', 'editor=trigger&type=PHP&lovValue=trigger_bl_php', 1, 1, 500, 500);" />
        <label class="labelMandatory">*</label>
        </div>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Order : </td>
      <td>
        <input id="trigger_order" name="trigger_order" type="text" class="inputInput" size="5" value="<?php echo $getTriggerInfoRs[0]['TRIGGER_ORDER'];?>" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Status : </td>
      <td>
      	<select name="trigger_status" id="trigger_status" class="inputList" >
          <option value="1" <?php if($_POST['new']||$getTriggerInfoRs[0]['TRIGGER_STATUS']){?>selected="selected"<?php }?>>Active</option>
          <option value="0" <?php if($_POST['edit']&&!$getTriggerInfoRs[0]['TRIGGER_STATUS']){?>selected="selected"<?php }?>>Not Active</option>
        </select>
      </td>
    </tr>
  </table>

  <br />
  <?php
  //include class Table
  require_once('class/Table.php');				//class Table

  //==================== DECLARATION =======================
  $tg = new TableGrid('100%',0,0,0);					//set object for table class (width,border,celspacing,cellpadding)

  //set attribute of table
  $tg->setAttribute('class','tableContent');			//set id
  $tg->setHeader('Parameter');						//set header
  $tg->setKeysStatus(true);							//use of keys (column header)
  $tg->setKeysAttribute('class','listingHead');		//set class
  $tg->setRunningStatus(true);						//set status of running number
  $tg->setRunningKeys('No');						//key / label for running number

  //set attribute of column in table
  $column = new Column();							//set object for column
  $column->setAttribute('class','listingContent');	//set attribute for table
  $tg->setColumn($column);							//insert/set class column into table

  //status add/delete row
  $tg->setAddRowStatus(true);
  $tg->setDelRowStatus(true);
  $tg->setAddRowType('ajax');

  //value add/delete row
  $tg->setAddRowValue('Add Row');
  $tg->setDelRowValue('Delete Row');

  //class
  $tg->setAddRowClass('inputButton');
  $tg->setDelRowClass('inputButton');
  //================== END DECLARATION =====================

  //set column
  for($x=0; $x<$getTriggerParameterRsCount+1; $x++)
  {
	  $param[$x]['Parameter']='<input name="parameter[]" type="text" class="inputInput" value="'.htmlentities($getTriggerParameterRs[$x]['PARAMETER_VALUE']).'" size="50" />';
	  $param[$x]['Delete']='<input name="deleteParameter[]" type="checkbox" value="1" onchange="var parameterCount=(document.getElementsByName(\'deleteParameter[]\')).length;for(x=0;x<parameterCount;x++){if((document.getElementsByName(\'deleteParameter[]\'))[x].checked){(document.getElementsByName(\'parameter[]\'))[x].disabled=true;} else {(document.getElementsByName(\'parameter[]\'))[x].disabled=false;}}" />';
  }//eof for

  //header
  $headerCount=count($param[0]);
  $tg->setHeaderAttribute('colspan',$headerCount);			//set colspan for header

  //put data into tablegrid
  $tg->setTableGridData($param);

  //display table grid
  $tg->showTableGrid();
  ?>
  <br />

  <table class="tableContent" width="100%" border="0" cellpadding="2" cellspacing="2" style="border:none;">
    <tr >
      <td class="footer">
        <?php if($_POST['new']){$btnName = 'insert';}else{$btnName = 'update';}?>
        <input name="<?php echo $btnName;?>" id="<?php echo $btnName;?>" type="submit" class="inputButton" value="Save" />
        <input name="close" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
  <?php }?>
</form>

<?php if($_POST['showScreen'] && $_POST['code']){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="10">Trigger List</th>
  </tr>
  <tr>
    <th width="15" class="listingHead">#</th>
    <th width="50" class="listingHead">Element</th>
    <th width="100" class="listingHead">Name</th>
    <th class="listingHead">Title</th>
    <th width="200" class="listingHead">BL</th>
    <th width="100" class="listingHead">Event</th>
    <th width="30" class="listingHead">Type</th>
    <th width="30" class="listingHead">Order</th>
    <th width="50" class="listingHead">Status</th>
    <th width="100" class="listingHead">Action</th>
  </tr>
  <?php if($triggerListRsCount>0){?>
  <?php for($x=0; $x<$triggerListRsCount; $x++) { ?>
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php }?> >
    <td class="listingContent"><?php echo $x+1;?>.</td>
    <td class="listingContent"><?php echo ucwords($triggerListRs[$x]['ELEMENT']);?></td>
    <td class="listingContent"><?php echo $triggerListRs[$x]['NAME'];?></td>
    <td class="listingContent"><?php echo $triggerListRs[$x]['TITLE'];?></td>
    <td class="listingContent">
      <a href="<?php echo $getBlUrlRs[0]['MENULINK'].'&blname='.$triggerListRs[$x]['TRIGGER_BL'];?>" target="_blank"><?php echo $triggerListRs[$x]['TRIGGER_BL'];?></a>
      <?php
		$getTriggerParamListRs = $myQuery->query("select * from FLC_TRIGGER_PARAMETER where TRIGGER_ID = ".$triggerListRs[$x]['TRIGGER_ID']." order by PARAMETER_SEQ",'SELECT', 'NAME');

		if(count($getTriggerParamListRs) > 0) { ?>
      <br>
      <span style="font-style:italic;color:#717171">parameter:<br>
		<?php
		for($a=0; $a < count($getTriggerParamListRs); $a++)
			echo '&nbsp;&nbsp;'.htmlentities($getTriggerParamListRs[$a]['PARAMETER_VALUE']).'<br>';
		 ?></span>
		 <?php }//end if ?>
    </td>
    <td class="listingContent"><?php echo $triggerListRs[$x]['TRIGGER_EVENT'];?></td>
    <td class="listingContent"><?php echo $triggerListRs[$x]['TRIGGER_TYPE'];?></td>
    <td class="listingContent"><?php echo $triggerListRs[$x]['TRIGGER_ORDER'];?></td>
    <td class="listingContent"><?php if($triggerListRs[$x]['TRIGGER_STATUS']){echo 'Active';}else{echo 'Inactive';}?></td>
    <td nowrap="nowrap" class="listingContentRight">
      <form id="formDetail" name="formDetail" method="post">
        <input name="moveUp" type="submit" class="inputButton" id="moveUp" value="&uarr;" />
        <input name="moveDown" type="submit" class="inputButton" id="moveDown" value="&darr;" />
  		<input name="edit" type="submit" class="inputButton" id="edit" value="Update" />
        <input name="delete" type="submit" class="inputButton" id="delete" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this trigger?')) {return true} else {return false}"/>
        <input name="trigger_id" type="hidden" id="trigger_id" value="<?php echo $triggerListRs[$x]['TRIGGER_ID'];?>" />
        <input type="hidden" name="code" id="code" value="<?php echo $_POST['code'];?>" />
      </form>
    </td>
  </tr>
  <?php }?>
  <?php }else { ?>
  <tr>
    <td colspan="10" class="myContentInput">No trigger(s) found...</td>
  </tr>
  <?php }?>
  <tr>
    <td colspan="10" class="contentButtonFooter">
      <form id="form2" name="form2" method="post">
        <input name="resetOrder" type="submit" class="inputButton" id="resetOrder" value="Optimize Order" onclick="if(window.confirm('Are you sure you want to reset the trigger order?')) {return true} else {return false}" />
        <input type="hidden" name="code" id="code" value="<?php echo $_POST['code'];?>" />
        <input name="new" type="submit" class="inputButton" id="new" value="New" />
      </form>
    </td>
  </tr>
</table>
<br />
<?php }?>