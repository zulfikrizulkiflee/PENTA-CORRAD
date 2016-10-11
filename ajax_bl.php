<?php 	
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

if($_POST['blName'])
	executeBL($_POST['blName']);
?>
