<?php 
//if using cas
if(CAS_ENABLED&&is_object($cas))
{
	//check from cas server to get username
	if($cas->casIsAuthenticated())
	{
		$username = $cas->casOnlineUser();
		
		//check username
		$getPwd = "select USERPASSWORD from PRUSER where USERNAME = '".$username."'";
		$getPwdArr = $myQuery->query($getPwd,'SELECT');
		$password = $getPwdArr[0][0];
		
		checklogin($myQuery,$mySQL,$mySession,cleanData($username),$password);
		
		//strip ticket parameter in url
		$_SERVER['QUERY_STRING']=str_replace('ticket=','',$_SERVER['QUERY_STRING']);
		$_SERVER['QUERY_STRING']=str_replace($_GET['ticket'],'',$_SERVER['QUERY_STRING']);
		
		if(!$_SESSION['userID'])
		{
			$error = LOGIN_CAS_MSG;
			echo $error;
		}//eof if
		else
			redirect('index.php?'.$_SERVER['QUERY_STRING']);	//redirect to system
	}//eof if
	else
		$cas->casLogin();
}//eof if
else
{
	//include secondary login page
	include(CAS_SECONDARY_LOGIN_PAGE);
}//eof else
?>