<?php 
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

if(PUBLIC_LOGIN)
{
	$public_user_type_code = PUBLIC_USER_TYPE_CODE;
	$user_profile_schema = USER_PROFILE;
	$user_profile_userpassword = USER_PROFILE_USERPASSWORD;
	$user_profile_username = USER_PROFILE_USERNAME;	
	$user_profile_usertypecode  = USER_PROFILE_USERTYPECODE;
	
	$public_login_sql = "select $user_profile_username, $user_profile_userpassword 
							from $user_profile_schema 
							where $user_profile_usertypecode = '$public_user_type_code'";
	$public_login_rs = $myQuery->query($public_login_sql,'SELECT','NAME');
	
	//if checklogin
	$error = checklogin($myQuery,$mySQL,$mySession,$public_login_rs[0]['USERNAME'],$public_login_rs[0]['USERPASSWORD']);

	//url
	$url = 'page=page_wrapper&menuID='.$_REQUEST['menuID'];	

	$getParameter = $_GET;
	$getParameterCount = count($_GET);
	$getParameterKeys = array_keys($_GET);
	
	for($x=0; $x<$getParameterCount; $x++)
		$url.='&'.$getParameterKeys[$x].'='.$getParameter[$getParameterKeys[$x]];

	if (MENU_URL_SECURITY)
		$url = "a=" . flc_url_encode($url);

	//if session userID
	if($_SESSION['userID'])
		{redirect('index.php?' . $url);}	//redirect to system
	else
		{include(SYSTEM_LOGIN_PAGE);}			//include the login page	
}
else {
	include(SYSTEM_LOGIN_PAGE);
}
?>
