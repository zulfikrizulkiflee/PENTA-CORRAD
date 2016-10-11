<?php
require_once('system_prerequisite.php');

//validate that user have session
validateUserSession();

if($_GET['verajax'] == 'true')
{
	if($_GET['type'] == 'delete')
	{
		$myQuery->query("delete from FLC_BL where BLID =".$_GET['blid'],'RUN');
	}
	else if($_GET['type'] == 'restore')
	{
		//get current FLC_BL id
		$currentBLId = $myQuery->query("select BLPARENT,BLTYPE from FLC_BL where BLID = ".$_GET['blid'],'SELECT','NAME');

		//delete the current BL
		$myQuery->query("delete from FLC_BL
							where BLID = ".$currentBLId[0]['BLPARENT'],'RUN');

		//set the selected BL to be the parent
		$myQuery->query("update FLC_BL set BLPARENT = NULL where BLID = ".$_GET['blid'],'RUN');

		//update the other bl history to this new parent
		$myQuery->query("update FLC_BL set BLPARENT = ".$_GET['blid']."
							where BLPARENT = ".$currentBLId[0]['BLPARENT'],'RUN');



		echo $_GET['bltype'];
	}
	else if($_GET['type'] == 'preview')
	{
		$blDetail = $myQuery->query("select BLDETAIL from FLC_BL where BLID =".$_GET['blid'],'SELECT','NAME'); ?>
		<div id="blPreview" style="width:70%;position:fixed;top:10px;left:100px; z-index:100;background-color:#F7F7F7;">
			<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="position:relative;z:index:200; margin:0px;padding:0px;">
				<tr>
					<th colspan="2">BL History Preview</th>
				</tr>
				<tr>
					<td colspan="2">
					<div style="width:100%; height:500px; overflow:scroll;">
					<?php highlight_string($blDetail[0]['BLDETAIL']);?>
					</div>
					</td>
				</tr>
				<tr>
					<td class="contentButtonFooter" colspan="2" align="right">
						<input name="close" type="button" class="inputButton" value="Close" onclick="jQuery(this).parent().parent().parent().parent().remove()" />
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	return false;
}

//if ajax filter
if(isset($_GET['filter_bl']))
{
	$_POST['bltype'] = $_GET['type'];
}//eof if

//==================================== manipulation ==========================================
//insert/update => check if blname exist
if($_POST['insert']||$_POST['update'])
{
	//if update
	if($_POST['update'])
		$constraintBlId = " and BLID != ".$_POST['blid'];

	//select same blname where status active
	$chkExist = "select BLNAME from FLC_BL where BLNAME = '".strtoupper($_POST['blname'])."' ".$constraintBlId." and BLSTATUS = '00'
				and BLPARENT is null";
	$chkExistRs = $myQuery->query($chkExist,'SELECT','INDEX');
	$blNameExist = $chkExistRs[0][0];

	//set default value for int
	if(!$_POST['blglobal'])
		$_POST['blglobal'] = 'NULL';
	//convert backslash
	if(strpos($_POST['bldetail'],'&bcksl;') !== false)
		$_POST['bldetail'] = str_replace('&bcksl;', '\\\\', $_POST['bldetail']);
}//eof if

//if insert
if($_POST['insert'])
{
	//if blname not exist
	if(!$blNameExist)
	{
		//max id
		$maxBLIdRs = $mySQL->maxValue('FLC_BL','BLID',0) + 1;

		//insert bl
		$insertBl = "insert into FLC_BL (BLID, BLTYPE, BLNAME, BLDESCRIPTION, BLGLOBAL, BLSTATUS, CREATEBY, CREATEDATE)
						values (".$maxBLIdRs.", '".strtoupper($_POST['bltype'])."', '".$_POST['blname']."',
							'".$_POST['bldescription']."', ".$_POST['blglobal'].", '".$_POST['blstatus']."',
							".$_SESSION['userID'].", ".$mySQL->currentDate().")";
		$insertBlRs = $myQuery->query($insertBl, 'RUN');

		//if character too long, execute update to append
		$mySQL->storeUnlimitedChar('FLC_BL', 'BLDETAIL', $_POST['bldetail'], " where BLID = ".$maxBLIdRs);

		//if inserted
		if($insertBlRs)
		{
			//insert bl parameter (if any)
			$parameterCount = count($_POST['parameter']);

			//loop on count of parameter
			for($x=0; $x<$parameterCount; $x++)
			{
				//if have value
				if($_POST['parameter'][$x])
				{
					//insert parameter
					$insertParameter = "insert into FLC_BL_PARAMETER (BL_ID, PARAMETER_SEQ, PARAMETER_VALUE)
										values
										(".$maxBLIdRs.", ".++$paramSeq.",'".$_POST['parameter'][$x]."')";
					$insertParameterRs = $myQuery->query($insertParameter,'RUN');
				}//eof if
			}//eof for

			//notification
			showNotificationInfo('New BL has been added.');
		}//eof if
		else
		{
			//notification
			showNotificationError('Fail to add new BL!');
		}//eof else
	}//eof if
	//if same name exist
	else
	{
		//notification
		showNotificationError('BL with same name already exist. Insert fail!');
	}//eof else
}//eof if

//if update
else if($_POST['update'])
{
	//----------
	//VERSIONING
	//----------
	//max id
	$maxBLIdRs = $mySQL->maxValue('FLC_BL','BLID',0) + 1;
	$maxBLParamIdRs = $mySQL->maxValue('FLC_BL_PARAMETER','BL_ID',0) + 1;

	//insert bl history
	$addToHistory = "insert into FLC_BL (BLID, BLTYPE, BLNAME, BLTITLE, BLDESCRIPTION, BLGLOBAL, BLSTATUS, CREATEBY, CREATEDATE, MODIFYBY, MODIFYDATE, BLPARENT)
						select ".$maxBLIdRs.", BLTYPE, BLNAME, BLTITLE, BLDESCRIPTION, BLGLOBAL, BLSTATUS, CREATEBY, CREATEDATE, ".$_SESSION['userID'].",
						".$mySQL->currentDate().", ".$_POST['blid']."
						from FLC_BL where BLID = ".$_POST['blid'];
	$addToHistoryRs = $myQuery->query($addToHistory, 'RUN');
	//-----------------
	//END OF VERSIONING
	//-----------------

	//if character too long, execute update to append
	$mySQL->storeUnlimitedChar('FLC_BL', 'BLDETAIL', $_POST['bldetail'], " where BLID = ".$maxBLIdRs);

	//if blname not exist
	if(!$blNameExist)
	{
		//update bl
		$updateBl = "update FLC_BL set
						BLTYPE = '".strtoupper($_POST['bltype'])."',
						BLNAME = '".$_POST['blname']."',
						BLDESCRIPTION = '".$_POST['bldescription']."',
						BLGLOBAL = ".$_POST['blglobal'].",
						BLSTATUS = '".$_POST['blstatus']."',
						MODIFYBY = ".$_SESSION['userID'].",
						MODIFYDATE = ".$mySQL->currentDate()."
						where BLID = ".$_POST['blid'];
		$updateBlRs = $myQuery->query($updateBl, 'RUN');

		//if character too long, execute update to append
		$mySQL->storeUnlimitedChar('FLC_BL', 'BLDETAIL', $_POST['bldetail'], " where BLID = ".$_POST['blid']);

		//if updated
		if($updateBlRs)
		{
			if($_POST['blname'] != $_POST['blname_old'])
			{
				//rename blname in related triggers
				$updateTriggerBL = "update FLC_TRIGGER set TRIGGER_BL = '".$_POST['blname']."' where TRIGGER_BL = '".$_POST['blname_old']."'";
				$updateTriggerBLRs = $myQuery->query($updateTriggerBL,'RUN');
			}//eof if

			//===========================
			//VERSIONING FOR BL PARAMETER
			//===========================
			//if parameter exists
			/*$paramCheck = $myQuery->query("select * from FLC_BL_PARAMETER where BL_ID = ".$_POST['blid'],'SELECT','NAME');

			//if parameter exists, add parameter history
			if($paramCheck)
			{
				echo 'gggg'.$xxx = "insert into FLC_BL_PARAMETER
						(BL_ID, PARAMETER_SEQ, PARAMETER_VALUE, BLPARENT)
						values (".$maxBLParamIdRs.",".$_POST['blid'].",".$paramCheck[0]['PARAMETER_SEQ'].",'".$paramCheck[0]['PARAMETER_SEQ']."',
						".$paramCheck[0]['BL_PARAM_ID'].")";

					//bl history
				$myQuery->query($xxx,'RUN');
			}
			*/
			//===============================
			//END VERSIONING FOR BL PARAMETER
			//===============================

			//insert bl parameter (if any)
			$parameterCount = count($_POST['parameter']);

			//delete previous parameter
			$deleteParameter = "delete from FLC_BL_PARAMETER where BL_ID = ".$_POST['blid'];
			$deleteParameterRs = $myQuery->query($deleteParameter,'RUN');

			//loop on count of parameter
			for($x=0; $x<$parameterCount; $x++)
			{
				//if have value
				if($_POST['parameter'][$x])
				{
					//insert parameter
					$insertParameter = "insert into FLC_BL_PARAMETER (BL_ID, PARAMETER_SEQ, PARAMETER_VALUE)
										values
										(".$_POST['blid'].", ".++$paramSeq.",'".$_POST['parameter'][$x]."')";
					$insertParameterRs = $myQuery->query($insertParameter,'RUN');
				}//eof if
			}//eof for

			//notification
			showNotificationInfo('BL has been updated.');
		}//eof if
		else
		{
			//notification
			showNotificationError('Fail to update BL!');
		}//eof else
	}//eof if
	//if same name exist
	else
	{
		//notification
		showNotificationError('BL with same name already exist. Update fail!');
	}//eof else
}//eof if

//if delete
else if($_POST['delete'])
{
	//delete bl
	$deleteBl = "delete from FLC_BL where BLID = ".$_POST['blid'];
	$deleteBlRs = $myQuery->query($deleteBl, 'RUN');

	//if delete
	if($deleteBlRs)
	{
		//delete bl parameter
		$deleteBl = "delete from FLC_BL_PARAMETER where BL_ID = ".$_POST['blid'];
		$deleteBlRs = $myQuery->query($deleteBl, 'RUN');

		//notification
		showNotificationInfo('BL has been deleted.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to delete BL!');
	}//eof else
}//eof if
//================================== eof manipulation ========================================

//====================================== display =============================================
//open bl from other page
if($_GET['blname'])
{
	//check bl existance
	$checkExist = "select BLID from FLC_BL where BLNAME = '".$_GET['blname']."' and BLPARENT is null";
	$checkExistRs = $myQuery->query($checkExist, 'SELECT', 'NAME');
	$checkExistRsCount = count($checkExistRs);

	//if exist
	if($checkExistRsCount)
	{
		//set the selected id and open editor
		$_POST['blid'] = $checkExistRs[0]['BLID'];
		$_POST['edit'] = true;
	}//
}//eof if

//bl listing, if type selected and not in add/edit mode
if($_POST['bltype'] && !$_POST['new'] && !$_POST['edit'])
{
	//search/filter bl
	$_GET['name'] = trim(str_replace('Search here..','',$_GET['name']));

	//filter title
	if($_GET['name']) $filterName = " and upper(BLNAME) like upper('%".$_GET['name']."%') ";

	//bl list
	$blList = "select BLID, BLNAME, BLGLOBAL, BLSTATUS,
					(select count(*) from FLC_BL_PARAMETER a where a.BL_ID = b.BLID) BLPARAMETER
				from FLC_BL b
				where BLTYPE = '".$_POST['bltype']."' ".$filterName."
				and BLPARENT is null
				order by BLNAME";
	$blListRs = $myQuery->query($blList, 'SELECT', 'NAME');
	$blListRsCount = count($blListRs);
}//eof if

//if new/edit
if($_POST['new'] || $_POST['edit'])
{
	//list of status
	$statusList=$mySQL->status();
	$statusListCount=count($statusList);

	//include class Table
	require_once('class/Table.php');					//class Table

	//==================== DECLARATION =======================
	$tg = new TableGrid('100%',0,0,0);					//set object for table class (width,border,celspacing,cellpadding)

	//set attribute of table
	$tg->setAttribute('class','tableContent');				//set id
	$tg->setHeader('Parameter');						//set header
	$tg->setKeysStatus(true);							//use of keys (column header)
	$tg->setKeysAttribute('class','listingHead');		//set class
	$tg->setRunningStatus(true);						//set status of running number
	$tg->setRunningKeys('No');							//key / label for running number

	//set attribute of column in table
	$column = new Column();								//set object for column
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
}//eof if

//if edit
if($_POST['edit'])
{
	//get bl info
	$bl = "select BLID, BLTYPE, BLNAME, BLDESCRIPTION, BLDETAIL, BLGLOBAL, BLSTATUS
			from FLC_BL where BLID = ".$_POST['blid'];
	$blRs = $myQuery->query($bl,'SELECT','NAME');

	//variable assignment
	$blid = $blRs[0]['BLID'];
	$bltype = $blRs[0]['BLTYPE'];
	$blname = $blRs[0]['BLNAME'];
	$bldescription = $blRs[0]['BLDESCRIPTION'];
	$bldetail = $blRs[0]['BLDETAIL'];
	$blglobal = $blRs[0]['BLGLOBAL'];
	$blstatus = $blRs[0]['BLSTATUS'];
	/*
	$createby = $blRs[0]['CREATEBY'];
	$createdate = $blRs[0]['CREATEDATE'];
	$modifyby = $blRs[0]['MODIFYBY'];
	$modifydate = $blRs[0]['MODIFYDATE'];
	*/

	//set post for bl type
	$_POST['bltype'] = $blRs[0]['BLTYPE'];

	//get bl parameter
	$getBlParameter = "select * from FLC_BL_PARAMETER where BL_ID = ".$_POST['blid']." order by PARAMETER_SEQ";
	$getBlParameterRs = $myQuery->query($getBlParameter,'SELECT','NAME');
	$getBlParameterRsCount = count($getBlParameterRs);
}//eof if
//==================================== eof display ===========================================
?>
<script language="javascript" type="text/javascript" src="js/editor.js"></script>
<script>
function verDelete(elem,blid)
{
	var type = 'delete';

	if(window.confirm('Are you sure you want to DELETE this BL history?'))
	{
		jQuery(elem).parent().parent().remove();

		jQuery.post('bl_editor.php?verajax=true&type='+type+'&blid='+blid, function(data)
		{
			showNotificationInfo('BL history has been deleted.',2);

			var tr = jQuery('#versionHistory tr');
			var trLength = tr.length;

			for(var x=2; x < trLength-1; x++)
				jQuery(tr[x]).find('td').eq(0).html((x-1)+'.');

			if(trLength == 3)
			{
				jQuery(tr[2]).hide();
				jQuery(tr[1]).children().eq(4).remove();
				jQuery(tr[1]).children().eq(3).remove();
				jQuery(tr[1]).children().eq(2).remove();
				jQuery(tr[1]).children().eq(1).remove();
				jQuery(tr[1]).children().eq(0).html('No record(s) found..').attr('colspan','4');
			}
		});
	}
	else
		return false;
}

function verRestore(elem,blid)
{
	var type = 'restore';

	if(window.confirm('Are you sure you want to RESTORE this BL history?'))
	{
		jQuery(elem).parent().parent().remove();

		jQuery.post('bl_editor.php?verajax=true&type='+type+'&blid='+blid, function(data)
		{
			showNotificationInfo('This BL history has been successfully restored.',2);

			var tr = jQuery('#versionHistory tr');
			var trLength = tr.length;

			for(var x=2; x < trLength-1; x++)
				jQuery(tr[x]).find('td').eq(0).html((x-1)+'.');

			if(trLength == 3)
			{
				jQuery(tr[2]).hide();
				jQuery(tr[1]).children().eq(4).remove();
				jQuery(tr[1]).children().eq(3).remove();
				jQuery(tr[1]).children().eq(2).remove();
				jQuery(tr[1]).children().eq(1).remove();
				jQuery(tr[1]).children().eq(0).html('No record(s) found..').attr('colspan','4');
			}

			setTimeout(function(){
				jQuery('[name="close"]').click();
			},1500);


		});
	}
	else
		return false;
}

function verDeleteAll(elem,allID)
{
	var type = 'delete';
	var id = allID.split(',');

	for(var x=0; x < id.length; x++)
	{
		jQuery.post('bl_editor.php?verajax=true&type='+type+'&blid='+id[x], function(data) {
		});
	}

	showNotificationInfo('All BL history has been deleted.',2);

	var tr = jQuery('#versionHistory tr');
	var trLength = tr.length;

	for(var x=2; x < trLength; x++)
	{
		jQuery(tr[1]).children().eq(4).remove();
		jQuery(tr[1]).children().eq(3).remove();
		jQuery(tr[1]).children().eq(2).remove();
		jQuery(tr[1]).children().eq(1).remove();
		jQuery(tr[1]).children().eq(0).html('No record(s) found..').attr('colspan','4');
		jQuery(tr[x]).remove();
	}
}

function verPreview(elem,blid)
{
	var type = 'preview';

	jQuery.post('bl_editor.php?verajax=true&type='+type+'&blid='+blid, function(data)
	{
		jQuery('#blPreview').remove();
		jQuery('#content').prepend(data);
	});
}
</script>

<?php
//if ajax filter
if(!isset($_GET['filter_bl'])){
?>
<div id="breadcrumbs">System Administrator / Configuration / BL Editor</div>
<h1>BL Editor</h1>
<?php }?>

<?php if(!$_POST['new'] && !$_POST['edit']){?>
<?php
//if ajax filter
if(!isset($_GET['filter_bl'])){
?>
<form id="form1" name="form1" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">BL Type </th>
  </tr>
  <tr>
      <td nowrap="nowrap" class="inputLabel">BL Type : </td>
      <td>
      	<label>
        	<input name="bltype" type="radio" value="JS" <?php if($_POST['bltype'] == 'JS'){?> checked="checked"<?php }?> onchange="this.form.submit();" />JavaScript
        </label>
        <label>
        	<input name="bltype" type="radio" value="PHP" <?php if($_POST['bltype'] == 'PHP'){?> checked="checked"<?php }?> onchange="this.form.submit();" />PHP
        </label>
      </td>
   </tr>
   <tr>
      <td class="contentButtonFooter" colspan="2">
    	<input name="new" type="submit" class="inputButton" id="new" value="New" <?php if(!$_POST['bltype']){?> disabled="disabled"<?php }?> />
      </td>
   </tr>
</table>
</form>
<br />
<?php }?>
<?php if($_POST['bltype'] || $_GET['filter_bl']){?>
<div id="bl_editor_list">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	  <tr>
		<th colspan="6">
        	<?php if($_POST['bltype'] == 'JS'){?>JavaScript<?php }else if($_POST['bltype'] == 'PHP'){?>PHP<?php }?> BL List
        </th>
	  </tr>
	  <tr>
		<th width="15" class="listingHead">#</th>
		<th class="listingHead">Name<br><input type="text" class="inputInput" name="filter_name" id="filter_name" value="<?php if($_GET['name']) echo $_GET['name']; else echo ' Search here..';?>" onfocus="if(this.value == ' Search here..') this.value= ''; " onkeydown="if(event.keyCode == 13) this.onblur();" onblur="if(this.value == '') {this.value = ' Search here..';} filterBLEditorList(document.getElementById('bltype').value,this.value);" size="20" style="color:#7F7F7F;width:99%;" /></th>
        <th width="20" class="listingHead">Global</th>
		<th width="20" class="listingHead">Parameter</th>
        <th width="40" class="listingHead">Status</th>
		<th width="85" class="listingHead">Action</th>
	  </tr>
	   <?php if($blListRsCount>0) { ?>
	  <?php for($x=0; $x<$blListRsCount; $x++) { ?>
	  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php }?> >
		<td class="listingContent"><?php echo $x+1;?>.</td>
		<td class="listingContent"><?php echo $blListRs[$x]['BLNAME'];?></td>
		<td class="listingContent"><?php if($blListRs[$x]['BLGLOBAL']) echo "Yes"; else echo "-";?></td>
		<td class="listingContent"><?php if($blListRs[$x]['BLPARAMETER']) echo "Yes"; else echo "-";?></td>
        <td class="listingContent"><?php if($blListRs[$x]['BLSTATUS'] == '00') echo "Active"; else echo "Inactive";?></td>
		<td nowrap="nowrap" class="listingContent">
          <form id="formDetail" name="formDetail" method="post">
			<input name="edit" type="submit" class="inputButton" id="edit" value="Update" />
			<input name="delete" type="submit" class="inputButton" id="delete" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this BL?')) {return true} else {return false}"/>
			<input name="blid" type="hidden" id="blid" value="<?php echo $blListRs[$x]['BLID'];?>" />
            <input type="hidden" name="bltype" id="bltype" value="<?php echo $_POST['bltype'];?>" />
		  </form>
        </td>
	  </tr>
	  <?php } //end for ?>
	  <?php }//end if
		else { ?>
	  <tr>
		<td colspan="6" class="myContentInput">
        	No BL(s) found...
        </td>
	  </tr>
	  <?php } //end else?>
	  <tr>
		<td colspan="6" class="contentButtonFooter">
		  <form id="form2" name="form2" method="post">
			<input type="hidden" name="bltype" id="bltype" value="<?php echo $_POST['bltype'];?>" />
			<input name="new" type="submit" class="inputButton" id="new" value="New" />
		  </form>
		</td>
	  </tr>
	</table>
</div>
<br />
<?php }?>
<?php }?>

<?php if($_POST['new']||$_POST['edit']){?>
<form id="form1" name="form1" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<tr>
	  <th colspan="2"><?php if($_POST['new']){?>New<?php }else if($_POST['edit']){?>Edit<?php }?> BL</th>
	</tr>
	<tr>
	  <td class="inputLabel" nowrap="nowrap">Type : </td>
	  <td><b><?php if($_POST['bltype'] == 'JS'){?>JavaScript<?php }else if($_POST['bltype'] == 'PHP'){?>PHP<?php }?></b>
        <input type="hidden" name="bltype" id="bltype" value="<?php echo $_POST['bltype'];?>" />
      </td>
	</tr>
	<tr>
	  <td class="inputLabel" nowrap="nowrap">Name : </td>
	  <td>
      	<input name="blname" id="blname" type="text" class="inputInput" value="<?php echo $blname;?>" size="50"  onchange="trim(this);" />
        <input type="hidden" name="blname_old" id="blname_old" value="<?php echo $blname;?>" />
        <input type="hidden" name="blid" id="blid" value="<?php echo $blid;?>" />
        <label class="labelMandatory">*</label>
        <script>document.getElementById('blname').focus();</script>
      </td>
	</tr>
	<tr>
	  <td class="inputLabel">Status :</td>
	  <td>
          <select name="blstatus" class="inputList" id="blstatus">
            <?php
			//if status not been set
			if(!isset($blstatus))
				$blstatus='00';		//set default

			echo createDropDown($statusList, $blstatus)?>
		  </select>
	  </td>
	</tr>
	<tr>
	  <td class="inputLabel" nowrap="nowrap">Description : </td>
	  <td>
      	<textarea name="bldescription" id="bldescription" cols="50" rows="3" class="inputInput"><?php echo $bldescription;?></textarea>
      </td>
	</tr>
    <tr>
	  <td class="inputLabel" nowrap="nowrap">Make Global : </td>
	  <td>
      	<label><input name="blglobal" id="blglobal" type="checkbox" <?php if($blglobal){?> checked="checked"<?php }?> value="1" />Yes</label>
        <br />
        <label class="labelNote">Note: Enable this BL to be executed from another BL</label>
      </td>
	</tr>
</table>
<br />

<?php
/* ======== TABLEGRID FOR PARAMETER ======== */
//set column
for($x=0; $x<$getBlParameterRsCount+1; $x++)
{
	$param[$x]['Name']='<input name="parameter[]" type="text" class="inputInput" value="'.$getBlParameterRs[$x]['PARAMETER_VALUE'].'" size="50" />';
	$param[$x]['Delete']='<input name="deleteparameter[]" type="checkbox" value="1" onchange="var parameterCount=(document.getElementsByName(\'deleteparameter[]\')).length;for(x=0;x<parameterCount;x++){if((document.getElementsByName(\'deleteparameter[]\'))[x].checked){(document.getElementsByName(\'parameter[]\'))[x].disabled=true;} else {(document.getElementsByName(\'parameter[]\'))[x].disabled=false;}}" />';
}//eof for

//header
$headerCount=count($param[0]);
$tg->setHeaderAttribute('colspan',$headerCount);			//set colspan for header

//put data into tablegrid
$tg->setTableGridData($param);

//display table grid
$tg->showTableGrid();
/* ====== EOF TABLEGRID FOR PARAMETER ====== */
?>
<br />
<!--
<input type="hidden" name="blhash" value="<?php echo md5($bldetail); ?>" />
<textarea name="blolddd" rows="10" cols="100"><?php echo $bldetail; ?></textarea>
-->
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<tr>
	  <th colspan="2">Detail</th>
	</tr>
	<tr>
	  <td colspan="2">
      	<a name="detail_section"></a>
      	<textarea name="bldetail" id="bldetail" class="inputInput"><?php echo $bldetail;?></textarea>
        <label class="labelNote">Note: Press 'F11' for Full Screen view. Press 'Esc' to exit Full Screen view.</label>
      </td>
	</tr>
	<tr>
      <td class="contentButtonFooter" colspan="2" align="right">
      	<?php if($_POST['new']){$btnName = 'insert';}else{$btnName = 'update';}?>
        <input name="<?php echo $btnName;?>" id="<?php echo $btnName;?>" type="submit" class="inputButton" value="Save" onclick="editor.setValue(addSlashes(editor.getValue()));" />
        <input name="close" type="submit" class="inputButton" value="Close" />
	  </td>
  	</tr>
</table>

<br>
<?php if($_POST['edit']) {
	$blHistory = "select BLID, BLNAME, BLGLOBAL, BLSTATUS,
					(select count(*) from FLC_BL_PARAMETER a where a.BL_ID = b.BLID) BLPARAMETER,
					".$mySQL->convertFromDatetime("MODIFYDATE")." MODIFYDATE,
					(select USERNAME from PRUSER where USERID = b.MODIFYBY) MODIFYBY
					from FLC_BL b
					where BLTYPE = '".$_POST['bltype']."' ".$filterName."
					and BLPARENT = ".$_POST['blid']."
					and BLPARENT is not null
					order by BLID";
	$blHistoryRs = $myQuery->query($blHistory, 'SELECT', 'NAME');
	$blHistoryRsCnt = count($blHistoryRs);
?>
<table border="0" cellpadding="0" cellspacing="0" class="tableContent" id="versionHistory">
	<tr>
	  <th colspan="9">Versioning History</th>
	</tr>
	<?php if($blHistoryRsCnt) { ?>
	<tr>
	  <th width="15" class="listingHead">#</th>
	  <th class="listingHead">Modified Date</th>
      <th class="listingHead">Modified By</th>
	  <th width="230" class="listingHead">Action</th>
	</tr>
	<?php
	 for($x=0; $x<$blHistoryRsCnt; $x++){
		 $allVerId[] = $blHistoryRs[$x]['BLID'];
		 ?>
	 <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
	  <td class="listingContent"><?php echo $x+1;?>.</td>
	  <td class="listingContent"><?php echo $blHistoryRs[$x]['MODIFYDATE'];?></td>
      <td class="listingContent"><?php echo $blHistoryRs[$x]['MODIFYBY'];?></td>
	  <td class="listingContentRight">
		<input name="ver_preview" type="button" class="inputButton" value="Preview" onclick="verPreview(this,<?php echo $blHistoryRs[$x]['BLID']?>)" />
		<input name="ver_delete" type="button" class="inputButton" value="Delete" onclick="verDelete(this,<?php echo $blHistoryRs[$x]['BLID']?>)" />
		<input name="ver_restore" type="button" class="inputButton" value="Restore" onclick="verRestore(this,<?php echo $blHistoryRs[$x]['BLID']?>)" />
	  </td>
	</tr>
	<?php } } else { ?>
	<tr>
		<td class="listingContent">No record(s) found..</td>
	</tr>
	<?php } ?>
	<?php if($blHistoryRsCnt) { ?>
	<tr>
      <td class="contentButtonFooter" colspan="4" align="right">
        <input name="deleteAll" type="button" class="inputButton" value="Delete All" onclick="verDeleteAll(this,'<?php echo implode(',',$allVerId);?>')" />
	  </td>
  	</tr>
  	<?php } ?>
</table>
<?php } ?>
<!-- include codemirror library -->
<script src="tools/codemirror/lib/codemirror.js"></script>
<script src="tools/codemirror/lib/util/searchcursor.js"></script>
<script src="tools/codemirror/lib/util/match-highlighter.js"></script>
<script src="tools/codemirror/lib/util/foldcode.js"></script>
<?php if($_POST['bltype'] == 'JS'){?>
<script src="tools/codemirror/mode/javascript/javascript.js"></script>
<?php }else if($_POST['bltype'] == 'PHP'){?>
<script src="tools/codemirror/mode/php/php.js"></script>
<script src="tools/codemirror/mode/clike/clike.js"></script>
<?php }?>

<link rel="stylesheet" href="tools/codemirror/lib/codemirror.css" />
<style type="text/css">
.CodeMirror {border: 1px solid #F0F0F0;}
.CodeMirror-scroll {height: 400px;}
.CodeMirror-focused span.CodeMirror-matchhighlight {background: #e7e4ff; !important}
.CodeMirror-fullscreen {display: block; position: absolute; top: 0; left: 0; width: 100%; z-index: 9999; background-color:white;}
.activeline {background: #e8f2ff !important;}
</style>
<script>
function isFullScreen(cm) {
  return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
}
function winHeight() {
  return window.innerHeight || (document.documentElement || document.body).clientHeight;
}
function setFullScreen(cm, full) {
  var wrap = cm.getWrapperElement(), scroll = cm.getScrollerElement();
  if (full) {
	wrap.className += " CodeMirror-fullscreen";
	scroll.style.height = winHeight() + "px";
	document.documentElement.style.overflow = "hidden";
  } else {
	wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
	scroll.style.height = "";
	document.documentElement.style.overflow = "";

	//to make window jump to detail section after close 'Full Screen'
	window.location.hash="detail_section";
	cm.focus();
  }
  cm.refresh();
}
CodeMirror.connect(window, "resize", function() {
  var showing = document.body.getElementsByClassName("CodeMirror-fullscreen")[0];
  if (!showing) return;
  showing.CodeMirror.getScrollerElement().style.height = winHeight() + "px";
});

var editor = CodeMirror.fromTextArea(document.getElementById("bldetail"), {
	lineNumbers: true,
	lineWrapping: true,
	matchBrackets: true,
	mode: "<?php if($_POST['bltype'] == 'JS'){?>text/javascript<?php }else if($_POST['bltype'] == 'PHP'){?>text/x-php<?php }?>",
	indentUnit: 4,
	indentWithTabs: true,
	enterMode: "keep",
	tabMode: "shift",
	autoClearEmptyLines: true,
	onCursorActivity: function() {
    editor.setLineClass(hlLine, null, null);
    hlLine = editor.setLineClass(editor.getCursor().line, null, "activeline");
	editor.matchHighlight("CodeMirror-matchhighlight");
	},
	extraKeys: {
        "F11": function(cm) {setFullScreen(cm, !isFullScreen(cm));},
        "Esc": function(cm) {if (isFullScreen(cm)) setFullScreen(cm, false);}
	}
});

var hlLine = editor.setLineClass(0, "activeline");
</script>
<!-- eof include codemirror library -->
</form>
<?php }?>
