<?php 
include('system_prerequisite.php');
require_once('engine_elem_builder.php');

if($_GET['itemid'])
{
	//items info
	$itemsArr = $myQuery->query("select * from FLC_PAGE_COMPONENT_ITEMS where ITEMID=".$_GET['itemid'],'SELECT','NAME');
	
	//re-set item type
	$itemsArr[0]['ITEMTYPE'] = 'ajax_updater_subsequent';
	
	//build the item
	echo buildInput($myQueryArr, $itemArr[0]);
}//eof if
?>
