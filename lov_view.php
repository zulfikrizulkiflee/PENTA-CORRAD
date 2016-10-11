<?php
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

//get translation for the lov texts
getTranslation($myQuery,$_SESSION['language']);

//process get data and convert to post
if($_GET)
{
	$getCount = count($_GET);
	$getKeys = array_keys($_GET);
	$getKeysCount = count($getKeys);

	//loop on count of get
	for($x=0;$x<$getCount;$x++)
	{
		//convert all get as post, except id
		if($getKeys[$x] != 'id')
			$_POST[$getKeys[$x]] = $_GET[$getKeys[$x]];
	}//eof for

	//todo - cater for encoded string!!!
	//to get GET data
	$qmarkPos = strpos($_SERVER['HTTP_REFERER'],'?');
	$getData = substr($_SERVER['HTTP_REFERER'], $qmarkPos+1, strlen($_SERVER['HTTP_REFERER'])-$qmarkPos);
	$getDataArr = explode('&',$getData);

	for($x=0; $x < count($getDataArr); $x++)
	{
		$data = explode('=',$getDataArr[$x]);
		$_GET[$data[0]] = $data[1];
	}

	//get item name
	if($_GET['id'])
	{
		$tempItem = explode('||',str_replace('lov_button_','',$_GET['id']));
		$itemId = $tempItem[0];
		$itemIdLength = strlen($itemId);

		//if item is array
		if(strpos($tempItem[1],'[]') !== false)
		{
			//explode '_'
			$tempTabularItem = explode('_',$itemId);
			$tempTabularItemCount = count($tempTabularItem);

			//get item index
			$itemIndex = $tempTabularItem[$tempTabularItemCount-1];
			$itemIndexLength = strlen($itemIndex);

			//item name without index
			$itemName = substr($itemId, 0, $itemIdLength-$itemIndexLength-1);
		}//eof if
		else
			$itemName = $tempItem[0];
	}//eof if
}//eof if

//have pageid
if($_GET['p'])
{
	//get item info
	$getItemInfo = "select a.ITEMID, a.ITEMLOOKUP,a.ITEMLOOKUPDB, a.COMPONENTID
						from FLC_PAGE_COMPONENT_ITEMS a, FLC_PAGE_COMPONENT b
						where a.ITEMTYPE = 'lov' and a.COMPONENTID = b.COMPONENTID and b.PAGEID = ".$_GET['p']."
							and a.ITEMNAME = '".$itemName."'";
	$getItemInfoRs = $myQuery->query($getItemInfo,'SELECT','NAME');
	$getItemInfoRsCount = count($getItemInfoRs);

	//if have value
	if($getItemInfoRsCount)
	{
		//convert POST,GET,SESSION variable
		$lookup = convertDBSafeToQuery($getItemInfoRs[0]['ITEMLOOKUP']);
		$lookupDB  = $getItemInfoRs[0]['ITEMLOOKUPDB'];

		//for filterby list
		$filterBy = ${'myQuery'.$lookupDB}->query(convertDBSafeToQuery("select * from (".$lookup.") a"),'SELECT','NAME');

		if(count($filterBy) != 0)
		{
			$lookupRsKeys = array_keys($filterBy[0]);
			$lookupRsKeysCount = count($lookupRsKeys);

			//if filtered
			if($_POST['filter'])
			{
				$lookup = "select * from (".$lookup.") a ";

				//open search
				if(trim($_POST['filterBy']) == '')
				{
					$lookup .= " where ";

					for($x=0; $x < $lookupRsKeysCount; $x++)
					{
						$lookup .= " upper(`".$lookupRsKeys[$x]."`) like upper('%".$_POST['value']."%') ";
							
						if($x+1 < $lookupRsKeysCount)
							$lookup .= ' or ';
					}
				}

				//detailed search (by selected filter)
				else
					$lookup .= " where upper(`".$_POST['filterBy']."`) like upper('%".$_POST['value']."%')";
			}
			
			//run the lookup query
			$lovArr = ${'myQuery'.$lookupDB}->query(convertDBSafeToQuery($lookup),'SELECT','NAME');
			$lovArrCount = count($lovArr);
		}

		//========= JAVASCRIPT ===================
		//get trigger (javascript)
		$getTrigger = "select b.TRIGGER_ID, b.TRIGGER_EVENT, a.BLNAME
							from FLC_BL a, FLC_TRIGGER b
							where b.TRIGGER_STATUS = 1 and b.TRIGGER_TYPE = 'JS' and a.BLNAME = b.TRIGGER_BL
								and b.TRIGGER_ITEM_TYPE = 'item' and b.TRIGGER_ITEM_ID = ".$getItemInfoRs[0]['ITEMID']."
								and a.BLPARENT is null
							order by b.TRIGGER_EVENT, b.TRIGGER_ORDER";
		$getTriggerRs = $myQuery->query($getTrigger,'SELECT','NAME');
		$getTriggerRsCount = count($getTriggerRs);

		//loop on count of trigger
		for($x=0; $x<$getTriggerRsCount; $x++)
		{
			//trigger parameter
			$getTriggerParameter = "select PARAMETER_VALUE from FLC_TRIGGER_PARAMETER
										where TRIGGER_ID = ".$getTriggerRs[$x]['TRIGGER_ID']."
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
					if($getTriggerRs[$x]['TRIGGER_PARAMETER'])
						$getTriggerRs[$x]['TRIGGER_PARAMETER'] .= ',';

					//re-construct the triggers parameter
					$getTriggerRs[$x]['TRIGGER_PARAMETER'] .= $getTriggerParameterRs[$y]['PARAMETER_VALUE'];
				}//eof for
			}//eof if

			//switch trigger type
			switch($getTriggerRs[$x]['TRIGGER_EVENT'])
			{
				case 'onblur': $onblur .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onchange': $onchange .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onclick': $onclick .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'ondblclick': $ondblclick .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onfocus': $onfocus .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onkeydown': $onkeydown .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onkeypress': $onkeypress .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onkeyup': $onkeyup .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmousedown': $onmousedown .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmousemove': $onmousemove .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmouseout': $onmouseout .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmouseover': $onmouseover .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmouseup': $onmouseup .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onselect': $onselect .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onload': $onload .= 'window.opener.'.$getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;
			}//eof switch
		}//eof for
		//========= EOF JAVASCRIPT ===============
	}//eof if
}//eof if
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>
<?php echo APP_FULL_NAME;?>
</title>
<link href="css/layout<?php echo $_SESSION['LAYOUT']; ?>.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $_SESSION['THEME_FOLDER']; ?>/main.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" type="image/x-icon" href="img/logo.ico" />
<script language="javascript" type="text/javascript" src="tools/jquery.js"></script>
<script type="text/javascript">jQuery = jQuery.noConflict();</script>
<script language="javascript" type="text/javascript" src="js/common.js"></script>
</head>
<body style="margin:0px; padding:0px;">
<div id="content" class="lov">
<form id="form1" name="form1" action="" method="post" style="padding:10px;">

	<table align="center" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="margin-left:0px;width:100%" >
		<tr>
			<th colspan="2"><?php echo TR8N_LOV_FILTER_TITLE; ?></th>
		</tr>
		<tr>
			<td class="inputLabel"><?php echo TR8N_LOV_FILTER_BY_LIST_TITLE; ?> : </td>
			<td>
				<select class="inputList" style="width:350px;" name="filterBy" id="filterBy" onchange="moveCursorToEnd(document.getElementById('value'));">
					<option selected="selected" value="">-- <?php echo TR8N_LOV_OPEN_SEARCH; ?> --</option>
					<?php
					for($y=1; $y < $lookupRsKeysCount; $y++ )
					{
						$keyName = $lookupRsKeys[$y];

						if($keyName == 'FLC_NAME')
						{	$keyName = 'Name';
							$keyNameValue = 'FLC_NAME';
						}
						else
							$keyNameValue = $keyName;
						?>
						<option value="<?php echo $keyNameValue; ?>" <?php if($_POST['filterBy'] == $keyNameValue) echo 'selected'; ?>><?php echo str_replace('_',' ',$keyName); ?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="inputLabel"><?php echo TR8N_LOV_VALUE_TITLE; ?> : </td>
			<td>
				<input name="value" type="text" class="inputInput" style="width:340px;" id="value" value="<?php echo $_POST['value'];?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2" class="contentButtonFooter">
				<input name="filter" type="submit" class="inputButton" id="filter" value="<?php echo TR8N_LOV_FILTER_BUTTON; ?>" />
				<input name="close" type="button" class="inputButton" value="<?php echo TR8N_LOV_CLOSE_BUTTON; ?>" onclick="window.close()" />
			</td>
		</tr>
	</table>
	<br />
	<div style="height:370px; overflow:auto">
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="margin-left:0px;" >
			<tr>
				<th colspan="<?php echo $lookupRsKeysCount; ?>"><?php echo TR8N_LOV_LIST_TITLE; ?></th>
			</tr>
			<?php if($lovArrCount) { ?>
			<tr>
				<th width="12" class="listingHead">#</th>
				<?php
				//generate head
				for($y=1; $y < $lookupRsKeysCount; $y++ )
				{
					if($lookupRsKeys[$y] == 'FLC_ID'){}
					else if($lookupRsKeys[$y] == 'FLC_NAME')
						$keyName = 'Name';
					else
						$keyName = $lookupRsKeys[$y];
					?>
					<th class="listingHead"><?php echo str_replace('_',' ',$keyName); ?></th><?php
				}
				?>
			</tr>
			<?php } ?>
			<?php
			if($lovArrCount)
			{
				for($x=0; $x<$lovArrCount; $x++){
			?>
			<tr>
				<td class="listingContent">
					<?php echo ($x+1).'.';?>&nbsp;
					<?php if(!in_array('FLC_NAME',$lookupRsKeys)){
						$lovArr[$x]['FLC_NAME'] = $lovArr[$x][$lookupRsKeys[1]];
					} ?>
					<input name="hiddenText_<?php echo $x;?>" type="hidden" id="hiddenText_<?php echo $x;?>" value="<?php echo $lovArr[$x]['FLC_NAME'];?>" />
					<input name="hiddenValue_<?php echo $x;?>" type="hidden" id="hiddenValue_<?php echo $x;?>" value="<?php echo $lovArr[$x]['FLC_ID'];?>" />
				</td>
				<?php
				for($y=0; $y < $lookupRsKeysCount; $y++ )
				{
					if($lookupRsKeys[$y] == 'FLC_ID'){}
					else
					{
						echo '<td>';
						if($lookupRsKeys[$y] == 'FLC_NAME' || $y == 1) { ?>
						<a 	href="javascript:void(0)"
							onclick="window.opener.document.getElementById('lov_text_<?php echo $itemId;?>').value = document.getElementById('hiddenText_<?php echo $x;?>').value;
									window.opener.document.getElementById('<?php echo $itemId;?>').value = document.getElementById('hiddenValue_<?php echo $x;?>').value;
						<?php echo $onclick;?>	window.close();">
						<?php } ?>
						<?php
						echo $lovArr[$x][$lookupRsKeys[$y]].'</a></td>';
					}
				}
				?>
			</tr>
			<?php }
			 } else {?>
			<tr>
				<td colspan="<?php echo $lookupRsKeysCount; ?>" class="myContentInput">&nbsp;&nbsp;No value(s) found.. </td>
			</tr>
			<?php }?>
		</table>
	</div>
	<input type="text" name="hiddenLookup" value="<?php echo $lookup; ?>" />
</form>
</div>
<script>
//move caret location to value field
moveCursorToEnd(document.getElementById('value'));

//if esc key pressed, close window
jQuery(document).keydown(function(e)
{
    if(e.keyCode === 27)
        window.close();
});
</script>
<?php if($lovArrCount == 0) { ?>
<script>jQuery('#value, #filter, #filterBy').attr('disabled','disabled');</script>
<?php } ?>
</body>
</html>
