<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

//if edit screen submitted
if($_POST['saveChanges'])
{
	//if new password is equal
	if($_POST['userNewPassword_1'] == $_POST['userNewPassword_2'])
	{
		//check user password, correct or not

		$user_profile_schema = USER_PROFILE;
		$check = "select USERID,USERNAME,USERPASSWORD
					from $user_profile_schema where USERID = ".$_SESSION['userID']."
					and USERPASSWORD = '".md5($_POST["userPassword"])."'";
		$checkRs = $myQuery->query($check,'SELECT','NAME');

		//if password is correct
		if(count($checkRs) > 0)
		{
			//check length of password, if less than min length:ref
			if(strlen($_POST['userNewPassword_1']) < CPWD_MIN_LENGTH)
			{
				$error_3 = true;				//show error
			}

			//if length > than min, update user password
			else
			{
				//update password
				$user_profile_schema = USER_PROFILE;
				$user_profile_userpassword = USER_PROFILE_USERPASSWORD;
				$user_profile_userid = USER_PROFILE_USERID;
				$update = "update $user_profile_schema
							set $user_profile_userpassword = '".md5($_POST['userNewPassword_1'])."'
							where $user_profile_userid = ".$_SESSION['userID'];
				$updateSuccess = $myQuery->query($update,'RUN');
			}
		}
		else
			$error_1 = true;		//show error
	}
	else
		$error_2 = true;			//show error
}
else if($_POST['cancelScreenNew'])
	redirect('index.php');
?>

<div id="breadcrumbs">Profile / Change Password</div>
<h1>Change Your Password</h1>

<?php
if($update)
{
	//notification
	showNotificationInfo(CPWD_MSG_SUCCESS);
}

else if($error_1)
{
	//notification
	showNotificationError(CPWD_MSG_ERR_1);
}

else if($error_2)
{
	//notification
	showNotificationError(CPWD_MSG_ERR_2);
}

else if($error_3)
{
	//notification
	showNotificationError(CPWD_MSG_ERR_3);
}
?>

<script>
//show password strength
function showPasswordStrength(password)
{
	var pwdStrength = checkPasswordStrength(password);

	document.getElementById('passwordStrength').innerHTML = pwdStrength;
	document.getElementById('passwordStrength').className = 'passwordStrength'+pwdStrength;
}//eof function
</script>

<form method="post" name="form1">
  <table width="750" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Password Information</th>
    </tr>
    <tr>
      <td nowrap class="inputLabel">Current Password : </td>
      <td width="662"><input name="userPassword" type="password" class="inputInput" id="userPassword" size="40" onkeyup="form1.saveChanges.disabled = false" value="" /></td>
    </tr>
    <tr>
      <td nowrap class="inputLabel">New Password : </td>
      <td>
      	<input name="userNewPassword_1" type="password" class="inputInput" id="userNewPassword_1" size="40" onkeyup="form1.saveChanges.disabled = false; showPasswordStrength(this.value);" value="" />
        <label id="passwordStrength"></label>
      </td>
    </tr>
    <tr>
      <td nowrap class="inputLabel">New Password (Again) : </td>
      <td><input name="userNewPassword_2" type="password" class="inputInput" id="userNewPassword_2" size="40" onkeyup="form1.saveChanges.disabled = false; if(this.value == document.getElementById('userNewPassword_1').value) document.getElementById('passwordCheck').innerHTML = '*  <?php echo CPWD_MSG_ERR_5;?>!'; else document.getElementById('passwordCheck').innerHTML = '* <?php echo CPWD_MSG_ERR_4;?>'" value="" />
        <label id="passwordCheck" style=""></label></td>
    </tr>
    <tr>
      <td class="contentButtonFooter" colspan="2">
        <input name="saveChanges" type="submit" disabled="disabled" class="inputButton" id="saveChanges" value="Save" />
        <input name="cancelScreenNew" type="submit" class="inputButton" value="Cancel" />
      </td>
    </tr>
  </table>
</form>