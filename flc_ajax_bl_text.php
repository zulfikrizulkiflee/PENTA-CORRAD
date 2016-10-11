<?php
// Developed by Rosli Amir @ 26th February 2011
// Usage : This code is used to call bl and return text to the AJAX
include('system_prerequisite.php');						//include stuff needed for session, database connection, and stuff
include('db.php');

 $bl_name = $_GET['bl_name'];
 $sql_1 = "select blid from FLC_BL where blname = '$bl_name' and BLPARENT is null";
 $sql_1_rs = $myQuery->query($sql_1,"SELECT","NAME");
 if ( $sql_1_rs[0]['BLID'] != "" ) {
	executeBL($bl_name);
 }
?>
