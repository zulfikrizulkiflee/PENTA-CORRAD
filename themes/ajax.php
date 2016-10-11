<?php
//include stuff needed for session, database connection, and stuff
include('../system_prerequisite.php');

$task = $_POST['task'];

if($task == 'ChangeLayout')
{
	ResetLayoutThemeSession();
	$type = $_POST['type'];
	$q = "update PRUSER set LAYOUT = '$type' where USERID = ".$_SESSION['userID'];
	$r = $myQuery->query($q,'RUN');

	if($r)
		$_SESSION['LAYOUT'] = $type;
}

if($task == 'ChangeTheme')
{
	ResetLayoutThemeSession();
	$id = $_POST['themeId'];
	$q = "update PRUSER set THEME = '$id' where USERID = ".$_SESSION['userID'];
	$r = $myQuery->query($q,'RUN');

	if($r)
	{
		$_SESSION['THEME'] = $id;

		$getThemeFolder = "select EXT_PATH from FLC_EXTENSION where EXT_ID = ".$id;
		$getThemeFolderRs = $myQuery->query($getThemeFolder,'SELECT','INDEX');
		$getThemeFolderRs = $getThemeFolderRs[0][0];
		$_SESSION['THEME_FOLDER'] = $getThemeFolderRs;
	}
}

function ResetLayoutThemeSession()
{
	unset($_SESSION['contentTop']);
	$_SESSION['displaySidebar'] = 1;
	$_SESSION['displayTopBar'] = 1;
	$_SESSION['displayHeader'] = 1;
}

?>