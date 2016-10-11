<?php
$type			= $itemArr['ITEMTYPE'];
$componentid	= $itemArr['COMPONENTID'];
$id				= $itemArr['ITEMID'];
$name			= $itemArr['ITEMNAME'];
$title			= $itemArr['ITEMTITLE'];
$default		= convertDBSafeToQuery($itemArr['ITEMDEFAULTVALUE']);
$defaultQuery	= $itemArr['ITEMDEFAULTVALUEQUERY'];
$lookup			= $itemArr['ITEMLOOKUP'];
$tabindex		= $itemArr['ITEMTABINDEX'];
$minchar		= $itemArr['ITEMMINCHAR'];
$maxchar		= $itemArr['ITEMMAXCHAR'];
$length			= $itemArr['ITEMINPUTLENGTH'];
$textarearows	= $itemArr['ITEMTEXTAREAROWS'];
$hints			= $itemArr['ITEMHINTS'];
$mapping		= $itemArr['ITEMMAPPING'];
$textalign		= $itemArr['ITEMTEXTALIGN'];
$case			= $itemArr['ITEMUPPERCASE'];
$disabled		= $itemArr['ITEMDISABLED'];
$readonly		= $itemArr['ITEMREADONLY'];
$addAttrib		= explode('|',$itemArr['ITEMADDITIONALATTR']);

//if default in sql mode
if($defaultQuery && $default)
{
	$defaultRs = $myQuery->query($default,'SELECT','NAME');
	$defaultRsCount = count($defaultRs);

	//if default have value
	if($defaultRsCount)
	{
		$default = $defaultRs[0]['FLC_ID'];
		$label = $defaultRs[0]['FLC_NAME'];
	
		//if not using FLC_ID
		if(!isset($defaultRs[0]['FLC_ID']))
		{
			$defaultRsKeys = array_keys($defaultRs[0]);
			$default = $defaultRs[0][$defaultRsKeys[0]];
		}//eof if
	}//eof if
	else
		$default = '';
}//eof if

//if lookup have value
if($lookupRsCount)
{
	$default = $lookupRs[0][$lookupRsKeys[0]];
}//eof if
?>
<input  type="text" style="width: 70px;" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $default; ?>" />
<div class="<?php echo $name.'_timeTrigger'; ?>" style="vertical-align:top; width: 16px; height:16px; background: url(items/timepicker/img/time.png); background-repeat:no-repeat;
															display: inline-block; padding:0px 1px 1px 1px;
															 cursor:pointer">
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#<?php echo $id; ?>').timepicker({
		showLeadingZero: false,
		showMinutesLeadingZero: false,
		showOn: 'both',
		rows: 4,
		showNowButton: true,
		showDeselectButton: true, 
		showPeriod: true,
		 minutes: {
			interval: 1
		},
		button: '.<?php echo $name ?>_timeTrigger'
	});
	
	jQuery('#<?php echo $id; ?>, .<?php echo $name ?>_timeTrigger').on('click',function(){
		/*
		var button = jQuery('#ui-timepicker-div').parent().find('.<?php echo $name ?>_timeTrigger');
		var top = jQuery(button).position().top;
		var left = jQuery(button).position().left;
		
		jQuery('#ui-timepicker-div')
			.css('top',top+23+'px')
			.css('left',left-2+'px')		
		*/
	});
});
</script>
