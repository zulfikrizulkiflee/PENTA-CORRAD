<?php 	
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

//create BL (global)
createPhpBl('');

if($_REQUEST['blname'])
	executeBL($_REQUEST['blname']);
?>
