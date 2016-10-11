<?php 
include('system_prerequisite.php');	

//captcha enabled
if(LOGIN_CAPTCHA_ENABLED)
{
	if($_POST['login'] && $_POST['userCaptcha'] != $_SESSION['captcha'])
		$error = LOGIN_CAPTCHA_ERROR_MSG;
}//eof if

//submit for login
if($_POST['login'] && !$error)
{
	$error = checklogin($myQuery,$mySQL,$mySession,cleanData($_POST['userID']),md5($_POST['userPassword']));
}//eof if

if($_SESSION['userID'])
	header('Location: index.php?'.$_SERVER['QUERY_STRING']);	
else
	include(SYSTEM_LOGIN_PAGE);			
?>
