<?php 	

//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

//get task
$task = $_POST['task'];

//*header dan topmenu adalah 2 benda yang berbeza*//

$_SESSION['contentTop'] = $_POST['contentTop'];

if($task=='hideHeader') $_SESSION['displayHeader'] = 0;
if($task=='showHeader') $_SESSION['displayHeader'] = 1;

if($task=='hideTopbar') $_SESSION['displayTopBar'] = 0;
if($task=='showTopbar') $_SESSION['displayTopBar'] = 1;

if($task=='hideSidebar') $_SESSION['displaySidebar'] = 0;
if($task=='showSidebar') $_SESSION['displaySidebar'] = 1;


?>
