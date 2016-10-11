<?php
//if ajax is called
if($_GET['type'] == 'ajax')
	include_once('system_prerequisite.php');
else
	include('system_prerequisite.php');

//functions needed
require_once('engine_elem_builder.php');
require_once('engine_control_builder.php');
require_once('class/Table.php');				//class Table

//if tabular scrollbar in page - todo
$showTabularScrollbar = array();

//used for checking pdf or csv button
$isPdfOrCsvButtonAppear = false;               //default false

//if menuID is set
if($_GET['menuID'])
{
	//set current menuID in $_SESSION
	$_SESSION['menuID'] = $_GET['menuID'];

	//query to get page info
	$page = "select a.* from FLC_PAGE a, FLC_MENU b where a.MENUID = b.MENUID and a.MENUID = ".$_GET['menuID'];
	$pageArr = $myQuery->query($page,'SELECT','NAME');
	$pageArrStyle = unserialize($pageArr[0]['PAGEADDITIONALATTR']);

	//if no page border
	if($pageArrStyle['pageborder'] == '0')
		echo "<script>document.getElementById('content').id = 'content-no-border';</script>";

	if($pageArrStyle['pagesize'])
		echo "<script>document.getElementById('content').style.width = '".($pageArrStyle['pagesize'])."px';</script>";

	//if menu have page
	if(is_array($pageArr))
	{
		//=============== INCLUDE EXTENSION ==============
		includeExtension('item', 'index');
		//============= EOF INCLUDE EXTENSION ============

		//========== CREATE VIRTUAL BL FUNCTION ==========
		createPhpBl($pageArr[0]['PAGEID']);
		createJsBl($pageArr[0]['PAGEID']);
		//======== EOF CREATE VIRTUAL BL FUNCTION ========

		//============== PERMISSION ==================
		//if enable permission for component
		if(COMPONENT_PERMISSION_ENABLED && !$_SESSION['PUBLIC'])
			$componentPermissionSQL = " and COMPONENTID in (".$mySQL->getPermissionSQL('component',$_SESSION['userID']).") ";

		//if enable permission for item
		if(ITEM_PERMISSION_ENABLED && !$_SESSION['PUBLIC'])
			$itemPermissionSQL = " and ITEMID in (".$mySQL->getPermissionSQL('item',$_SESSION['userID']).") ";

		//if enable permission for control
		if(CONTROL_PERMISSION_ENABLED && !$_SESSION['PUBLIC'])
			$controlPermissionSQL = " and CONTROLID in (".$mySQL->getPermissionSQL('control',$_SESSION['userID']).") ";
		//============ EOF PERMISSION ================

		//=========== RUN POST SCRIPT================
		include('page_wrapper_post.php');
		//========= EOF RUN POST SCRIPT ==============
		
		//=============== PAGE PRE-PROCESS ===============
		executePhpEvent($myQuery, 'preprocess', 'page', $pageArr[0]['PAGEID']);
		//============= EOF PAGE PRE-PROCESS =============

		//get component
		$component = "select * from FLC_PAGE_COMPONENT
						where PAGEID = ".$pageArr[0]['PAGEID']." 
						and COMPONENTSTATUS = 1
						".$componentPermissionSQL."
						order by COMPONENTORDER";
		$componentArr = $myQuery->query($component,'SELECT','NAME');
		$componentArrCount = count($componentArr);
	}//eof if
	else
	{
		//notification
		showNotificationError('Please create page!!');
	}

	//page header
	if($pageArrStyle['header'])
		echo '<div style="margin-left:-10px;margin-top:5px;">'.$pageArrStyle['header'].'</div>';
}
?>
<!-- PAGE HEADER SECTION -->
<div id="PAGEHEADER">
<?php
	$pageheader_pos = strpos( strtoupper($pageArr[0]['PAGENOTES']),'<FLC TYPE=PAGEHEADER>' ) ;
	if( strlen($pageheader_pos) > 0 )
	{
		$pageheader_end_pos =strpos(substr(strtoupper($pageArr[0]['PAGENOTES']), $pageheader_pos+21), '</FLC>');
		echo substr(strtoupper($pageArr[0]['PAGENOTES']), $pageheader_pos+21, $pageheader_end_pos) ;
	}
?>
</div>
<!-- //END PAGE HEADER SECTION -->

<!-- BREADCRUMBS SECTION -->
<?php if($_GET['BREADCRUMBS_ENABLED'] == '' && $pageArrStyle['pagebreadcrumbs'] != '0'){
		if($pageArr[0]['PAGEBREADCRUMBS'] != ''){?>
<div id="breadcrumbs">

<?php
//translation for page
$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],3,$pageArr[0]['PAGEID'],'PAGEBREADCRUMBS');
($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'PAGEBREADCRUMBS') ? $breadcrumbs = $tr8nStr[0]['TRANS_TEXT'] : $breadcrumbs = $pageArr[0]['PAGEBREADCRUMBS'];
echo convertDBSafeToQuery($breadcrumbs);
?></div>
<!-- //END BREADCRUMBS SECTION -->
<?php 	}
	  }?>
<!-- PAGE TITLE SECTION -->
<?php
//global - conf.php
if ( $_GET['PAGETITLE_ENABLED'] == "" && $pageArrStyle['pagetitle'] != '0') { ?>
<div id="pagetitle"><h1><?php

if(is_array($pageArr))
{
	//translation for page
	$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],3,$pageArr[0]['PAGEID'],'PAGETITLE');
	($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'PAGETITLE') ? $pageName = $tr8nStr[0]['TRANS_TEXT'] : $pageName = $pageArr[0]['PAGETITLE'];
	echo convertDBSafeToQuery($pageName);
}
?></h1></div>
<?php } ?>
<!-- //END PAGE TITLE SECTION -->
<form id="form1" name="form1" method="post" enctype="multipart/form-data" accept-charset="utf-8" >
<div>
<?php
//for all component in this page, show the component!
for($x=0; $x < $componentArrCount; $x++)
{
	//=============== COMPONENT PRE-PROCESS ===============
	executePhpEvent($myQuery, 'preprocess', 'component', $componentArr[$x]['COMPONENTID']);
	//============= EOF COMPONENT PRE-PROCESS =============

	//============================== COMPONENT PRE PROCESS ==============================
  	//if type of pre process is SELECT
	if($componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
	{
		//include file for pre
		include('page_wrapper_pre.php');
	}//end if pre process SELECT

	//============== SHARED ITEMS ================
	unset($itemsArr);
	if($componentArr[$x]['COMPONENTTYPE'] == 'form_1_col' ||
		$componentArr[$x]['COMPONENTTYPE'] == 'form_2_col' ||
		$componentArr[$x]['COMPONENTTYPE'] == 'tabular' ||
		$componentArr[$x]['COMPONENTTYPE'] == 'report')
	{
		//if page wrapper 1 column / page wrapper 2 column
		if($componentArr[$x]['COMPONENTTYPE'] == 'form_1_col' || $componentArr[$x]['COMPONENTTYPE'] == 'form_2_col')
		{
			//get hidden item
			$itemsHidden = "select * from FLC_PAGE_COMPONENT_ITEMS
								where COMPONENTID = ".$componentArr[$x]['COMPONENTID']." 
								and ITEMSTATUS = 1 
								and ITEMTYPE = 'hidden'
								".$itemPermissionSQL."
								order by ITEMORDER";
			$itemsArrHidden = $myQuery->query($itemsHidden,'SELECT','NAME');
			$countItemHidden = count($itemsArrHidden);

			//get other than hidden item
			$items = "select * from FLC_PAGE_COMPONENT_ITEMS
						where COMPONENTID = ".$componentArr[$x]['COMPONENTID']." 
						and ITEMSTATUS = 1 
						and ITEMTYPE != 'hidden'
						".$itemPermissionSQL."
						order by ITEMORDER";
			$itemsArr = $myQuery->query($items,'SELECT','NAME');
			$countItem = count($itemsArr);

			//loop on count of hidden item
			for($y=0; $y < $countItemHidden; $y++)
			{
				//=============== ITEM PRE-PROCESS ===============
				executePhpEvent($myQuery, 'preprocess', 'item', $itemsArrHidden[$y]['ITEMID']);
				//============= EOF ITEM PRE-PROCESS =============
			}//eof for

			//loop on count of item
			for($y=0; $y < $countItem; $y++)
			{
				//=============== ITEM PRE-PROCESS ===============
				executePhpEvent($myQuery, 'preprocess', 'item', $itemsArr[$y]['ITEMID']);
				//============= EOF ITEM PRE-PROCESS =============
			}//eof for
		}//eof if
		else
		{
			//get component items
			$items = "select * from FLC_PAGE_COMPONENT_ITEMS
						where COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
						and ITEMSTATUS = 1
						".$itemPermissionSQL."
						order by ITEMORDER";
			$itemsArr = $myQuery->query($items,'SELECT','NAME');
			$countItem = count($itemsArr);

			//loop on count of item
			for($y=0; $y < $countItem; $y++)
			{
				//=============== ITEM PRE-PROCESS ===============
				executePhpEvent($myQuery, 'preprocess', 'item', $itemsArr[$y]['ITEMID']);
				//============= EOF ITEM PRE-PROCESS =============
			}//eof for
		}//eof else
	}//========= END SHARED ITEMS ================

	//============ CONTROL FIELD =================
	//get control that is binded to component
	$control = "select CONTROLID from FLC_PAGE_CONTROL
					where PAGEID = ".$pageArr[0]['PAGEID']." 
					and COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
					".$controlPermissionSQL."
					order by CONTROLORDER";
	$controlArr = $myQuery->query($control);
	$controlArrCount = count($controlArr);

	//get control that is binded to component - LEFT ALIGN
	$compControlLeft = "select CONTROLID from FLC_PAGE_CONTROL
						where PAGEID = ".$pageArr[0]['PAGEID']." 
						and COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
						and CONTROLPOSITION = 'left'
						".$controlPermissionSQL."
						order by CONTROLORDER";
	$compControlLeftRs = $myQuery->query($compControlLeft);
	$compControlLeftRsCount = count($compControlLeftRs);

	//get control that is binded to component - CENTER ALIGN
	$compControlCenter = "select CONTROLID from FLC_PAGE_CONTROL
						where PAGEID = ".$pageArr[0]['PAGEID']." 
						and COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
						and CONTROLPOSITION = 'center'
						".$controlPermissionSQL."
						order by CONTROLORDER";
	$compControlCenterRs = $myQuery->query($compControlCenter);
	$compControlCenterRsCount = count($compControlCenterRs);

	//get control that is binded to component - RIGHT ALIGN
	$compControlRight = "select CONTROLID from FLC_PAGE_CONTROL
						where PAGEID = ".$pageArr[0]['PAGEID']." 
						and COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
						and CONTROLPOSITION = 'right'
						".$controlPermissionSQL."
						order by CONTROLORDER";
	$compControlRightRs = $myQuery->query($compControlRight);
	$compControlRightRsCount = count($compControlRightRs);

	unset($compControlidLeft);
	unset($compControlidCenter);
	unset($compControlidRight);

	for($y=0; $y < $compControlLeftRsCount; $y++)
		$compControlidLeft[] = $compControlLeftRs[$y][0];

	for($y=0; $y < $compControlCenterRsCount; $y++)
		$compControlidCenter[] = $compControlCenterRs[$y][0];

	for($y=0; $y < $compControlRightRsCount; $y++)
		$compControlidRight[] = $compControlRightRs[$y][0];

	//reset controlid for footer
	unset($controlid);

	//============ CONTROL FIELD =================

	//========= JAVASCRIPT ===================
	//js trigger
	$getJsTrigger[$x] = getJsTrigger('component', $componentArr[$x]['COMPONENTID']);

	//if have js trigger
	if(is_array($getJsTrigger[$x]))
	{
		//javascript, append js trigger with pre-set js
		if($getJsTrigger[$x]['onblur']) 		$js[$x].= ' onblur="'.$getJsTrigger[$x]['onblur'].'"';
		if($getJsTrigger[$x]['onchange']) 		$js[$x] .= ' onchange="'.$getJsTrigger[$x]['onchange'].'"';
		if($getJsTrigger[$x]['onclick']) 		$js[$x] .= ' onclick="'.$getJsTrigger[$x]['onclick'].'"';
		if($getJsTrigger[$x]['ondblclick']) 	$js[$x] .= ' ondblclick="'.$getJsTrigger[$x]['ondblclick'].'"';
		if($getJsTrigger[$x]['onfocus']) 		$js[$x] .= ' onfocus="'.$getJsTrigger[$x]['onfocus'].'"';
		if($getJsTrigger[$x]['onkeydown']) 		$js[$x] .= ' onkeydown="'.$getJsTrigger[$x]['onkeydown'].'"';
		if($getJsTrigger[$x]['onkeypress']) 	$js[$x] .= ' onkeypress="'.$getJsTrigger[$x]['onkeypress'].'"';
		if($getJsTrigger[$x]['onkeyup']) 		$js[$x] .= ' onkeyup="'.$getJsTrigger[$x]['onkeyup'].'"';
		if($getJsTrigger[$x]['onmousedown']) 	$js[$x] .= ' onmousedown="'.$getJsTrigger[$x]['onmousedown'].'"';
		if($getJsTrigger[$x]['onmousemove']) 	$js[$x] .= ' onmousemove="'.$getJsTrigger[$x]['onmousemove'].'"';
		if($getJsTrigger[$x]['onmouseout']) 	$js[$x] .= ' onmouseout="'.$getJsTrigger[$x]['onmouseout'].'"';
		if($getJsTrigger[$x]['onmouseover']) 	$js[$x] .= ' onmouseover="'.$getJsTrigger[$x]['onmouseover'].'"';
		if($getJsTrigger[$x]['onmouseup']) 		$js[$x] .= ' onmouseup="'.$getJsTrigger[$x]['onmouseup'].'"';
		if($getJsTrigger[$x]['onselect']) 		$js[$x] .= ' onselect="'.$getJsTrigger[$x]['onselect'].'"';
	}//eof if
	//========= EOF JAVASCRIPT ===============

	if( $componentArr[$x]['COMPONENTPOSITION']=='' || $componentArr[$x]['COMPONENTPOSITION']!='left' )
	{
		//switch display based on component type
		switch($componentArr[$x]['COMPONENTTYPE'])
		{
			case 'custom': 				include('page_wrapper_custom.php'); 			break;	//custom - php file
			case 'form_1_col': 			include('page_wrapper_1_col.php'); 				break;	//form 1 column
			case 'form_2_col': 			include('page_wrapper_2_col.php'); 				break;	//form 2 column
			case 'iframe': 				include('page_wrapper_iframe.php'); 			break;	//iframe
			case 'query': 				include('page_wrapper_query.php'); 				break;	//query 1 column
			case 'query_2_col': 		include('page_wrapper_query_2_col.php'); 		break;	//query 2 column
			case 'report': 				include('page_wrapper_report.php'); 			break;	//report
			case 'search_constraint': 	include('page_wrapper_search_constraint.php'); 	break;	//search constraint
			case 'tabular': 			include('page_wrapper_tabular.php'); 			break;	//tabular
		}//eof switch
	}

	//if have trigger onload js
	if($getJsTrigger[$x]['onload'])
		echo '<script>'.$getJsTrigger[$x]['onload'].'</script>';
}//end for x
?>
  <!-- =============================================================== PAGE CONTROL =============================================================== -->
<?php
//if have page
if($pageArr)
{
	//get control that is not binded to any component - LEFT ALIGN
	$controlCheck = "select CONTROLID from FLC_PAGE_CONTROL
						where PAGEID = ".$pageArr[0]['PAGEID']." 
						and (COMPONENTID is null or COMPONENTID = 0)
						".$controlPermissionSQL."
						order by CONTROLORDER";
	$controlCheckRs = $myQuery->query($controlCheck);
	$controlCheckRsCount = count($controlCheckRs);

	unset($controlid);

	//if theres page control associated with the page
	if($controlCheckRsCount > 0)
	{
		//get page level control - LEFT ALIGN
		$controlLeft = "select CONTROLID from FLC_PAGE_CONTROL
							where PAGEID = ".$pageArr[0]['PAGEID']." 
							and (COMPONENTID is null or COMPONENTID = 0)
							and CONTROLPOSITION = 'left'
							".$controlPermissionSQL."
							order by CONTROLORDER";
		$controlLeftRs = $myQuery->query($controlLeft);
		$controlLeftRsCount = count($controlLeftRs);

		//get page level control - CENTER ALIGN
		$controlCenter = "select CONTROLID from FLC_PAGE_CONTROL
							where PAGEID = ".$pageArr[0]['PAGEID']." 
							and (COMPONENTID is null or COMPONENTID = 0)
							and CONTROLPOSITION = 'center'
							".$controlPermissionSQL."
							order by CONTROLORDER";
		$controlCenterRs = $myQuery->query($controlCenter);
		$controlCenterRsCount = count($controlCenterRs);

		//get page level control - RIGHT ALIGN
		$controlRight = "select CONTROLID from FLC_PAGE_CONTROL
							where PAGEID = ".$pageArr[0]['PAGEID']." 
							and (COMPONENTID is null or COMPONENTID = 0)
							and CONTROLPOSITION = 'right'
							".$controlPermissionSQL."
							order by CONTROLORDER";
		$controlRightRs = $myQuery->query($controlRight);
		$controlRightRsCount = count($controlRightRs);

		for($y=0;$y < $controlLeftRsCount; $y++)
			$controlidLeft[] = $controlLeftRs[$y][0];

		for($y=0;$y < $controlCenterRsCount; $y++)
			$controlidCenter[] = $controlCenterRs[$y][0];

		for($y=0;$y < $controlRightRsCount; $y++)
			$controlidRight[] = $controlRightRs[$y][0];
	?>

  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent pageLevelControls">
    <tr>
      <td class="footer" style="text-align:left;border-right:none;width:33%;"><?php buildControl($myQuery,$controlidLeft);?></td>
      <td class="footer" style="text-align:center;border-left:none;border-right:none;width:33%;"><?php buildControl($myQuery,$controlidCenter);?></td>
      <td class="footer" style="text-align:right;border-left:none;width:33%;"><?php buildControl($myQuery,$controlidRight);?></td>
    </tr>
  </table>
  <?php
  }//eof if
}//eof if?>
  <!-- =============================================================== END PAGE CONTROL =============================================================== -->

<!-- PAGE FOOTER SECTION -->
<div id="PAGEFOOTER">
<?php
	$pageheader_pos = strpos(strtoupper($pageArr[0]['PAGENOTES']),'<FLC TYPE=PAGEFOOTER>' ) ;
	if(strlen($pageheader_pos) > 0 ) {
		$pageheader_end_pos =strpos(substr(strtoupper($pageArr[0]['PAGENOTES']), $pageheader_pos+21), '</FLC>');
		echo substr(strtoupper($pageArr[0]['PAGENOTES']), $pageheader_pos+21, $pageheader_end_pos) ;
	}
?>
</div>
<!-- //END PAGE FOOTER SECTION -->
<?php
if($pageArrStyle['footer'])
	echo '<div style="margin-left:-10px;margin-bottom:-5px;">'.$pageArrStyle['footer'].'</div>';
?>
</div>
<?php
//=============== PAGE ONLOAD ===============
if(is_array($pageArr))
{
	//generate page onload javascript
	$pageTriggerJs = "select b.TRIGGER_ID, a.BLNAME
					from FLC_BL a, FLC_TRIGGER b
					where a.BLNAME = b.TRIGGER_BL 
					and b.TRIGGER_STATUS = 1 
					and b.TRIGGER_EVENT = 'onload' 
					and b.TRIGGER_TYPE = 'JS'
					and b.TRIGGER_ITEM_TYPE = 'page'
					and b.TRIGGER_ITEM_ID = ".$pageArr[0]['PAGEID']."
					and a.BLPARENT is null
					order by b.TRIGGER_ORDER";
	$pageTriggerJsRs = $myQuery->query($pageTriggerJs,'SELECT','NAME');
	$pageTriggerJsRsCount = count($pageTriggerJsRs);

	//if have onload js
	if($pageTriggerJsRsCount)
	{
		//loop on count of onload js
		for($x=0; $x < $pageTriggerJsRsCount; $x++)
		{
			//trigger parameter
			$getTriggerParameter = "select PARAMETER_VALUE from FLC_TRIGGER_PARAMETER
										where TRIGGER_ID = ".$pageTriggerJsRs[$x]['TRIGGER_ID']."
										order by PARAMETER_SEQ";
			$getTriggerParameterRs = $myQuery->query($getTriggerParameter, 'SELECT', 'NAME');
			$getTriggerParameterRsCount = count($getTriggerParameterRs);

			//if have trigger parameter
			if($getTriggerParameterRsCount)
			{
				//loop on count of trigger parameter
				for($y=0; $y < $getTriggerParameterRsCount; $y++)
				{
					//append, if have value
					if($pageTriggerJsRs[$x]['TRIGGER_PARAMETER'])
						$pageTriggerJsRs[$x]['TRIGGER_PARAMETER'] .= ',';

					//re-construct the triggers parameter
					$pageTriggerJsRs[$x]['TRIGGER_PARAMETER'] .= convertDBSafeToQuery($getTriggerParameterRs[$y]['PARAMETER_VALUE']);
				}//eof for
			}//eof if

			$pageOnload .= $pageTriggerJsRs[$x]['BLNAME'].'('.$pageTriggerJsRs[$x]['TRIGGER_PARAMETER'].');';
		}//eof for

		//if have onload
		if($pageOnload)
			echo '<script>'.$pageOnload.'</script>';
	}//eof if
	
	//delayed init script for ckeditor
	if($_SESSION['delayedInitScript'])
	{
		echo '<script>jQuery(document).ready(function(){';
		echo $_SESSION['delayedInitScript'];
		echo '});';
		echo '</script>';
		
		unset($_SESSION['delayedInitScript']);
	}
	
}//eof if
//============= EOF PAGE ONLOAD =============
?>

<?php
if($isPdfOrCsvButtonAppear && count($_POST) > 0)
{
	//store POST values into input hidden fields
	foreach($_POST as $name => $value)
	{
		echo '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.htmlentities($value).'"/>';
	}
}
?>
</form>
