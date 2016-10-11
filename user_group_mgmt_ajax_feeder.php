<?php
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');
$user_profile_schema = USER_PROFILE;
$user_profile_userid = USER_PROFILE_USERID;
$user_profile_username = USER_PROFILE_USERNAME;
$user_profile_name = USER_PROFILE_NAME;

if($_GET['id'] == 'ajaxFilterUser') { ?>
<script type="text/javascript">
	function ajaxUserMgmtAddUser(userid,groupid,elem)
	{
		jQuery.get('user_group_mgmt_ajax_feeder.php?id=ajaxAddUser&user=' + userid + '&group='+groupid,
			function(data)
			{
				showNotificationInfo('User added!',2,'top:160px;right:30px;');
				//

				if(jQuery(elem).parent().parent().parent().parent().find('tr').length == 2)
				{
					theDiv = jQuery(elem).parent().parent().parent().parent().parent();
					theDiv.empty();
					theDiv.html('<div style="font-family:Arial; padding:3px 3px 3px 6px;">All user(s) has been selected..</div>');

				}
				else
					jQuery(elem).parent().parent().remove();

				//window.alert('User added!');
				//jQuery('#showScreen').click();
			});
	}
	</script>
<?php } ?>
<?php
if($_GET['id'] == 'ajaxDeleteUser') {

	$deleteRef = "delete from FLC_USER_GROUP_MAPPING
					where GROUP_ID = ".$_GET['groupid']."
					and USER_ID = ".$_GET['userid'];
	$deleteRefRs = $myQuery->query($deleteRef,'RUN');

	//dummy
	//$_POST["showScreen"] = "some value";


	?>


<?php }

if($_GET['id'] == 'ajaxCheckKodKumpulan')
{
	//check kod kumpulan
	$checkKodKumpulan = "select GROUP_CODE from FLC_USER_GROUP where upper(GROUP_CODE) = upper('".trim($_GET['value'])."')";
	$checkKodKumpulanRsRows = $myQuery->query($checkKodKumpulan,'SELECT','NAME');

	if(count($checkKodKumpulanRsRows) > 0) { ?>
	<div id="newCodeDiv" style="color:#FF0000">
		<input name="newCode" type="text" class="inputInput" id="newCode" size="26" maxlength="20" onchange="this.value = this.value.toUpperCase(); ajaxCheckKodKumpulan(this.value);" />
		* Kod kumpulan telah wujud! Sila tukar kod kumpulan.
	</div>
<?php } else { ?>
	<div id="newCodeDiv">
		<input name="newCode" value="<?php echo $_GET['value']?>" type="text" class="inputInput" id="newCode" size="26" maxlength="20" onchange="this.value = this.value.toUpperCase(); ajaxCheckKodKumpulan(this.value);" />
	* </div>
<?php } ?>
<?php }//end if id = ajaxCheckKodKumpulan

else if($_GET['id'] == 'ajaxCheckKodKumpulanEdit')
{
	//check kod kumpulan
	$checkKodKumpulan = "select GROUP_CODE from FLC_USER_GROUP where upper(GROUP_CODE) = upper('".trim($_GET['value'])."')
							and upper(GROUP_CODE) <> '".$_GET['originalValue']."'";
	$checkKodKumpulanRsRows = $myQuery->query($checkKodKumpulan,'SELECT','NAME');

	if(count($checkKodKumpulanRsRows) > 0)
	{
	 ?>
<div id="editCodeDiv" style="color:#FF0000">
  <input name="editCode" type="text" class="inputInput" id="editCode" size="26" maxlength="20" onchange="this.value = this.value.toUpperCase(); ajaxCheckKodKumpulanEdit(this.value,$F('hiddenEditCode'));" />
  <input type="hidden" name="hiddenEditCode" id="hiddenEditCode" value="<?php echo $_GET['originalValue']?>" />
  * Kod kumpulan telah wujud! Sila tukar kod kumpulan.</div>
<?php }
  else { ?>
<div id="editCodeDiv">
  <input name="editCode" value="<?php echo $_GET['value']?>" type="text" class="inputInput" id="editCode" size="26" maxlength="20" onchange="this.value = this.value.toUpperCase(); ajaxCheckKodKumpulanEdit(this.value,$F('hiddenEditCode'));" />
  <input type="hidden" name="hiddenEditCode" id="hiddenEditCode" value="<?php echo $_GET['originalValue']?>" />
  * </div>
<?php } ?>
<?php }//end if id = ajaxCheckKodKumpulanEdit

else if($_GET['id'] == 'ajaxFilterUser')
{
	if($_GET['srcby'] == 'username')
		$col = 'USERNAME';
	else if($_GET['srcby'] == 'fullname')
		$col = 'NAME';

	$userList = $myQuery->query("select USERID,USERNAME,NAME, USERGROUPCODE from PRUSER
										where lower(".$col.") like '%".strtolower($_GET['val'])."%'
										and USERID not in (select USER_ID
															from FLC_USER_GROUP_MAPPING
															where GROUP_ID = ".$_GET['groupid'].")
										order by 2",'SELECT','NAME');
	$userListCnt = count($userList);
	?>
	<div style="background-color:white;border:1px solid #f0f0f0">
	<?php if($userListCnt == 0) { ?>
	<div style="font-family:Arial; padding:3px 3px 3px 6px;"><?php echo $userListCnt;?> row(s) returned..</div>
	<?php } ?>
	<?php if($userListCnt != 0) { ?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
		<tr>
			<th class="listingHead">Username</th>
			<th class="listingHead">Full Name</th>
			<th class="listingHead">Group Code</th>
		</tr>
		<?php for($x=0; $x < $userListCnt; $x++) {?>
		<tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
			<td class="listingContent"><a href="javascript:void(0)" onclick="ajaxUserMgmtAddUser(<?php echo $userList[$x]['USERID']?>,<?php echo $_GET['groupid']?>,this)"><?php echo $userList[$x]['USERNAME']?></a></td>
			<td class="listingContent"><?php echo $userList[$x]['NAME']?></td>
            <td class="listingContent"><?php echo $userList[$x]['USERGROUPCODE']?></td>
		</tr>
		<?php } ?>
	</table>
	<?php } ?>
	</div>
	<?php
	//<input value="Add" type="button" style="font-size:11px;"/>
}
else if($_GET['id'] == 'ajaxAddUser')
{
	//check if already added
	$check = $myQuery->query("select * from FLC_USER_GROUP_MAPPING where USER_ID =".$_GET['user']." and GROUP_ID = ".$_GET['group'],'SELECT','NAME');

	if($check){}
	else
	{
		$myQuery->query("insert into FLC_USER_GROUP_MAPPING (GROUP_ID,USER_ID,ADDED_BY,ADDED_DATE)
						values (".$_GET['group'].",".$_GET['user'].",".$_SESSION['userID'].",".$mySQL->currentDate().")",'RUN');
	}
}

?>
