<?php
require_once('system_prerequisite.php');

//validate that user have session
validateUserSession();

//if ajax filter
if(isset($_GET['filter_message']))
{
	$_POST['message_type'] = $_GET['type'];
}//eof if

//==================================== manipulation ==========================================
//insert/update => check if message_name exist
if($_POST['insert']||$_POST['update'])
{
	//if update
	if($_POST['update'])
		$constraintMessageId = " and MESSAGE_ID != ".$_POST['message_id'];

	//select same message_name
	$chkExist = "select MESSAGE_NAME from FLC_MESSAGE where MESSAGE_NAME = '".strtoupper($_POST['message_name'])."' ".$constraintMessageId;
	$chkExistRs = $myQuery->query($chkExist,'SELECT','INDEX');
	$messageNameExist = $chkExistRs[0][0];
}//eof if

//if insert
if($_POST['insert'])
{
	//if message_name not exist
	if(!$messageNameExist)
	{
		//max id
		$maxMessageIdRs = $mySQL->maxValue('FLC_MESSAGE','MESSAGE_ID',0) + 1;

		//insert message
		$insertMessage = "insert into FLC_MESSAGE (MESSAGE_ID, MESSAGE_NAME, MESSAGE_TYPE, MESSAGE_TEXT)
						values (".$maxMessageIdRs.", '".$_POST['message_name']."', '".$_POST['message_type']."', '".$_POST['message_text']."')";
		$insertMessageRs = $myQuery->query($insertMessage, 'RUN');

		//if inserted
		if($insertMessageRs)
		{
			//notification
			showNotificationInfo('New Message has been added.');
		}//eof if
		else
		{
			//notification
			showNotificationError('Fail to add new Message!');
		}//eof else
	}//eof if
	//if same name exist
	else
	{
		//notification
		showNotificationError('Message with same name already exist. Insert fail!');
	}//eof else
}//eof if

//if update
else if($_POST['update'])
{
	//if messagename not exist
	if(!$messageNameExist)
	{
		//update message
		$updateMessage = "update FLC_MESSAGE
							set MESSAGE_NAME = '".$_POST['message_name']."',
								MESSAGE_TYPE = '".$_POST['message_type']."',
								MESSAGE_TEXT = '".$_POST['message_text']."'
							where MESSAGE_ID = ".$_POST['message_id'];
		$updateMessageRs = $myQuery->query($updateMessage, 'RUN');

		//if updated
		if($updateMessageRs)
		{
			//notification
			showNotificationInfo('Message has been updated.');
		}//eof if
		else
		{
			//notification
			showNotificationError('Fail to update Message!');
		}//eof else
	}//eof if
	//if same name exist
	else
	{
		//notification
		showNotificationError('Message with same name already exist. Update fail!');
	}//eof else
}//eof if

//if delete
else if($_POST['delete'])
{
	//delete message
	$deleteMessage = "delete from FLC_MESSAGE where MESSAGE_ID = ".$_POST['message_id'];
	$deleteMessageRs = $myQuery->query($deleteMessage, 'RUN');

	//if delete
	if($deleteMessageRs)
	{
		//notification
		showNotificationInfo('Message has been deleted.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to delete Message!');
	}//eof else
}//eof if
//================================== eof manipulation ========================================

//====================================== display =============================================
//message listing, if type selected and not in add/edit mode
if($_POST['message_type'] && !$_POST['new'] && !$_POST['edit'])
{
	//search/filter message
	$_GET['name'] = trim(str_replace('Search here..','',$_GET['name']));

	//filter title
	if($_GET['name']) $filterName = " and upper(MESSAGE_NAME) like upper('%".$_GET['name']."%') ";

	//message list
	$messageList = "select MESSAGE_ID, MESSAGE_NAME, MESSAGE_TEXT
					from FLC_MESSAGE
					where MESSAGE_TYPE = '".$_POST['message_type']."' ".$filterName."
					order by MESSAGE_NAME";
	$messageListRs = $myQuery->query($messageList, 'SELECT', 'NAME');
	$messageListRsCount = count($messageListRs);
}//eof if

//if edit
if($_POST['edit'])
{
	//get message info
	$message = $myQuery->query("select * from FLC_MESSAGE where MESSAGE_ID = ".$_POST['message_id'],'SELECT','NAME');

	//variable assignment
	$message_id = $message[0]['MESSAGE_ID'];
	$message_name = $message[0]['MESSAGE_NAME'];
	$message_type = $message[0]['MESSAGE_TYPE'];
	$message_text = $message[0]['MESSAGE_TEXT'];
}//eof if
//==================================== eof display ===========================================
?>
<script language="javascript" type="text/javascript" src="js/editor.js"></script>

<?php
//if ajax filter
if(!isset($_GET['filter_message'])){
?>
<div id="breadcrumbs">System Administrator / Configuration / Message Editor</div>
<h1>Message Editor</h1>
<?php }?>

<?php if(!$_POST['new'] && !$_POST['edit']){?>
<?php
//if ajax filter
if(!isset($_GET['filter_message'])){
?>
<form id="form1" name="form1" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">Message Type </th>
  </tr>
  <tr>
      <td nowrap="nowrap" class="inputLabel">Message Type : </td>
      <td>
      	<label>
        	<input name="message_type" type="radio" value="i" <?php if($_POST['message_type'] == 'i'){?> checked="checked"<?php }?> onchange="this.form.submit();" />Info
        </label>
        <label>
        	<input name="message_type" type="radio" value="e" <?php if($_POST['message_type'] == 'e'){?> checked="checked"<?php }?> onchange="this.form.submit();" />Error
        </label>
      </td>
   </tr>
   <tr>
      <td class="contentButtonFooter" colspan="2">
    	<input name="new" type="submit" class="inputButton" id="new" value="New" <?php if(!$_POST['message_type']){?> disabled="disabled"<?php }?> />
      </td>
   </tr>
</table>
</form>
<br />
<?php }?>

<?php if($_POST['message_type'] || $_GET['filter_message']){?>
<div id="message_editor_list">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	  <tr>
		<th colspan="5">
        	<?php if($_POST['message_type'] == 'i'){?>Info<?php }else if($_POST['message_type'] == 'e'){?>Error<?php }?> Message List
        </th>
	  </tr>
	  <tr>
		<th width="15" class="listingHead">#</th>
		<th width="15" class="listingHead">ID</th>
        <th class="listingHead">Name<br><input type="text" class="inputInput" name="filter_name" id="filter_name" value="<?php if($_GET['name']) echo $_GET['name']; else echo ' Search here..';?>" onfocus="if(this.value == ' Search here..') this.value= ''; " onkeydown="if(event.keyCode == 13) this.onblur();" onblur="if(this.value == '') {this.value = ' Search here..';} filterMessageEditorList(document.getElementById('message_type').value,this.value);" size="20" style="color:#7F7F7F;width:99%;" /></th>
        <th class="listingHead">Text</th>
		<th width="85" class="listingHead">Action</th>
	  </tr>
	   <?php if($messageListRsCount>0) { ?>
	  <?php for($x=0; $x<$messageListRsCount; $x++) { ?>
	  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php }?> >
		<td class="listingContent"><?php echo $x+1;?>.</td>
        <td class="listingContent"><?php echo $messageListRs[$x]['MESSAGE_ID'];?></td>
		<td class="listingContent"><?php echo $messageListRs[$x]['MESSAGE_NAME'];?></td>
		<td class="listingContent"><?php echo $messageListRs[$x]['MESSAGE_TEXT'];?></td>
		<td nowrap="nowrap" class="listingContentRight">
          <form id="formDetail" name="formDetail" method="post">
			<input name="edit" type="submit" class="inputButton" id="edit" value="Update" />
			<input name="delete" type="submit" class="inputButton" id="delete" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this Message?')) {return true} else {return false}"/>
			<input name="message_id" type="hidden" id="message_id" value="<?php echo $messageListRs[$x]['MESSAGE_ID'];?>" />
            <input type="hidden" name="message_type" id="message_type" value="<?php echo $_POST['message_type'];?>" />
		  </form>
        </td>
	  </tr>
	  <?php } //end for ?>
	  <?php }//end if
		else { ?>
	  <tr>
		<td colspan="5" class="myContentInput">
        	No Message(s) found...
        </td>
	  </tr>
	  <?php } //end else?>
	  <tr>
		<td colspan="5" class="contentButtonFooter">
		  <form id="form2" name="form2" method="post">
          	<input type="hidden" name="message_type" id="message_type" value="<?php echo $_POST['message_type'];?>" />
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
	  <th colspan="2"><?php if($_POST['new']){?>New<?php }else if($_POST['edit']){?>Edit<?php }?> Message</th>
	</tr>
	<tr>
	  <td class="inputLabel" nowrap="nowrap">Type : </td>
	  <td><b><?php if($_POST['message_type'] == 'i'){?>Info<?php }else if($_POST['message_type'] == 'e'){?>Error<?php }?></b>
        <input type="hidden" name="message_type" id="message_type" value="<?php echo $_POST['message_type'];?>" />
      </td>
	</tr>
    <tr>
	  <td class="inputLabel" nowrap="nowrap">Name : </td>
	  <td>
      	<input name="message_name" id="message_name" type="text" class="inputInput" value="<?php echo $message_name;?>" size="50" onchange="trim(this);" />
        <input type="hidden" name="message_name_old" id="message_name_old" value="<?php echo $message_name;?>" />
        <input type="hidden" name="message_id" id="message_id" value="<?php echo $message_id;?>" />
        <label class="labelMandatory">*</label>
        <script>document.getElementById('message_name').focus();</script>
      </td>
	</tr>
	<tr>
	  <td class="inputLabel" nowrap="nowrap">Text : </td>
	  <td>
      	<textarea name="message_text" id="message_text" cols="50" rows="3" class="inputInput"><?php echo $message_text;?></textarea>
      </td>
	</tr>
    <tr>
      <td class="contentButtonFooter" colspan="2" align="right">
      	<?php if($_POST['new']){$btnName = 'insert';}else{$btnName = 'update';}?>
        <input name="<?php echo $btnName;?>" id="<?php echo $btnName;?>" type="submit" class="inputButton" value="Save" />
        <input name="close" type="submit" class="inputButton" value="Close" />
	  </td>
  	</tr>
</table>
</form>
<?php }?>
