<?php
include_once('system_prerequisite.php');

//functions needed
require_once('engine_elem_builder.php');
require_once('engine_control_builder.php');
require_once('class/Table.php');				//class Table

//============== PERMISSION ==================
//if enable permission for component
if(COMPONENT_PERMISSION_ENABLED)
	$componentPermissionSQL = " and COMPONENTID in (".$mySQL->getPermissionSQL('component',$_SESSION['userID']).") ";

//if enable permission for item
if(ITEM_PERMISSION_ENABLED)
	$itemPermissionSQL = " and ITEMID in (".$mySQL->getPermissionSQL('item',$_SESSION['userID']).") ";

//if enable permission for control
if(CONTROL_PERMISSION_ENABLED)
	$controlPermissionSQL = " and CONTROLID in (".$mySQL->getPermissionSQL('control',$_SESSION['userID']).") ";
//============ EOF PERMISSION ================

$componentArr = getSideComponent($myQuery,$componentPermissionSQL);
$componentArrCount = count($componentArr);

if($componentArrCount > 0 && $componentArr !== false) { ?>
	<form id="form2" name="form2" method="post" action="" enctype="multipart/form-data" accept-charset="utf-8" >
	<div>
	<?php
	//for all component in this page, show the component!
	for($x=0; $x<$componentArrCount; $x++)
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
									where COMPONENTID = ".$componentArr[$x]['COMPONENTID']." and ITEMSTATUS = 1 and ITEMTYPE = 'hidden'
									".$itemPermissionSQL."
									order by ITEMORDER";
				$itemsArrHidden = $myQuery->query($itemsHidden,'SELECT','NAME');
				$countItemHidden = count($itemsArrHidden);

				//get other than hidden item
				$items = "select * from FLC_PAGE_COMPONENT_ITEMS
							where COMPONENTID = ".$componentArr[$x]['COMPONENTID']." and ITEMSTATUS = 1 and ITEMTYPE != 'hidden'
							".$itemPermissionSQL."
							order by ITEMORDER";
				$itemsArr = $myQuery->query($items,'SELECT','NAME');
				$countItem = count($itemsArr);

				//loop on count of hidden item
				for($y=0; $y<$countItemHidden; $y++)
				{
					//=============== ITEM PRE-PROCESS ===============
					executePhpEvent($myQuery, 'preprocess', 'item', $itemsArrHidden[$y]['ITEMID']);
					//============= EOF ITEM PRE-PROCESS =============
				}//eof for

				//loop on count of item
				for($y=0; $y<$countItem; $y++)
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
							where COMPONENTID = ".$componentArr[$x]['COMPONENTID']." and ITEMSTATUS = 1
							".$itemPermissionSQL."
							order by ITEMORDER";
				$itemsArr = $myQuery->query($items,'SELECT','NAME');
				$countItem = count($itemsArr);

				//loop on count of item
				for($y=0; $y<$countItem; $y++)
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
						where COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
						".$controlPermissionSQL."
						order by CONTROLORDER";
		$controlArr = $myQuery->query($control);
		$controlArrCount = count($controlArr);

		//get control that is not binded to any component - LEFT ALIGN
		$compControlLeft = "select CONTROLID from FLC_PAGE_CONTROL
							where COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
							and CONTROLPOSITION = 'left'
							".$controlPermissionSQL."
							order by CONTROLORDER";
		$compControlLeftRs = $myQuery->query($compControlLeft);
		$compControlLeftRsCount = count($compControlLeftRs);

		//get control that is not binded to any component - CENTER ALIGN
		$compControlCenter = "select CONTROLID from FLC_PAGE_CONTROL
							where COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
							and CONTROLPOSITION = 'center'
							".$controlPermissionSQL."
							order by CONTROLORDER";
		$compControlCenterRs = $myQuery->query($compControlCenter);
		$compControlCenterRsCount = count($compControlCenterRs);

		//get control that is not binded to any component - RIGHT ALIGN
		$compControlRight = "select CONTROLID from FLC_PAGE_CONTROL
							where COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
							and CONTROLPOSITION = 'right'
							".$controlPermissionSQL."
							order by CONTROLORDER";
		$compControlRightRs = $myQuery->query($compControlRight);
		$compControlRightRsCount = count($compControlRightRs);

		unset($compControlidLeft);
		unset($compControlidCenter);
		unset($compControlidRight);

		for($y=0;$y<$compControlLeftRsCount;$y++)
			$compControlidLeft[] = $compControlLeftRs[$y][0];

		for($y=0;$y<$compControlCenterRsCount;$y++)
			$compControlidCenter[] = $compControlCenterRs[$y][0];

		for($y=0;$y<$compControlRightRsCount;$y++)
			$compControlidRight[] = $compControlRightRs[$y][0];
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

		if( $componentArr[$x]['COMPONENTPOSITION'] == 'left' ) {

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
	</div>
	<?php
	if(is_array($pageArr))
	{
		//generate page onload javascript
		$pageTriggerJs = "select b.TRIGGER_ID, a.BLNAME
						from FLC_BL a, FLC_TRIGGER b
						where a.BLNAME = b.TRIGGER_BL and b.TRIGGER_STATUS = 1 and b.TRIGGER_EVENT = 'onload' and b.TRIGGER_TYPE = 'JS'
							and b.TRIGGER_ITEM_TYPE = 'page'
							and b.TRIGGER_ITEM_ID = ".$pageArr[0]['PAGEID']."
							and a.BLPARENT is null";
		$pageTriggerJsRs = $myQuery->query($pageTriggerJs,'SELECT','NAME');
		$pageTriggerJsRsCount = count($pageTriggerJsRs);

		//if have onload js
		if($pageTriggerJsRsCount)
		{
			//loop on count of onload js
			for($x=0; $x<$pageTriggerJsRsCount; $x++)
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
					for($y=0; $y<$getTriggerParameterRsCount; $y++)
					{
						//append, if have value
						if($pageTriggerJsRs[$x]['TRIGGER_PARAMETER'])
							$pageTriggerJsRs[$x]['TRIGGER_PARAMETER'] .= ',';

						//re-construct the triggers parameter
						$pageTriggerJsRs[$x]['TRIGGER_PARAMETER'] = $getTriggerParameterRs[$y]['PARAMETER_VALUE'];
					}//eof for
				}//eof if

				$pageOnload .= $pageTriggerJsRs[$x]['BLNAME'].'('.$pageTriggerJsRs[$x]['TRIGGER_PARAMETER'].');';
			}//eof for

			//if have onload
			if($pageOnload)
				echo '<script>'.$pageOnload.'</script>';
		}//eof if
	}//eof if
}
?>
</form>
