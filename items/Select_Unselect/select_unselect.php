<?php
$type			= $itemArr['ITEMTYPE'];
$componentid	= $itemArr['COMPONENTID'];
$id				= $itemArr['ITEMID'];
$name			= $itemArr['ITEMNAME'];
$title			= $itemArr['ITEMTITLE'];
$default		= convertDBSafeToQuery($itemArr['ITEMDEFAULTVALUE']);
$defaultQuery	= $itemArr['ITEMDEFAULTVALUEQUERY'];
$lookup			= convertDBSafeToQuery($itemArr['ITEMLOOKUP']);
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

$css .= 'min-width:350px;max-width:450px;';

//========= JAVASCRIPT ===================
//js trigger
$getJsTrigger = getJsTrigger('item', $id);

//javascript, append js trigger with pre-set js
if($onblur || $getJsTrigger['onblur']) 				$js .= ' onblur="'.$onblur.$getJsTrigger['onblur'].'"';
if($onchange || $getJsTrigger['onchange']) 			$js .= ' onchange="'.$onchange.$getJsTrigger['onchange'].'"';
if($onclick || $getJsTrigger['onclick']) 			$js .= ' onclick="'.$onclick.$getJsTrigger['onclick'].'"';
if($ondblclick || $getJsTrigger['ondblclick']) 		$js .= ' ondblclick="'.$ondblclick.$getJsTrigger['ondblclick'].'"';
if($onfocus || $getJsTrigger['onfocus']) 			$js .= ' onfocus="'.$onfocus.$getJsTrigger['onfocus'].'"';
if($onkeydown || $getJsTrigger['onkeydown']) 		$js .= ' onkeydown="'.$onkeydown.$getJsTrigger['onkeydown'].'"';
if($onkeypress || $getJsTrigger['onkeypress']) 		$js .= ' onkeypress="'.$onkeypress.$getJsTrigger['onkeypress'].'"';
if($onkeyup || $getJsTrigger['onkeyup']) 			$js .= ' onkeyup="'.$onkeyup.$getJsTrigger['onkeyup'].'"';
if($onmousedown || $getJsTrigger['onmousedown']) 	$js .= ' onmousedown="'.$onmousedown.$getJsTrigger['onmousedown'].'"';
if($onmousemove || $getJsTrigger['onmousemove']) 	$js .= ' onmousemove="'.$onmousemove.$getJsTrigger['onmousemove'].'"';
if($onmouseout || $getJsTrigger['onmouseout']) 		$js .= ' onmouseout="'.$onmouseout.$getJsTrigger['onmouseout'].'"';
if($onmouseover || $getJsTrigger['onmouseover']) 	$js .= ' onmouseover="'.$onmouseover.$getJsTrigger['onmouseover'].'"';
if($onmouseup || $getJsTrigger['onmouseup']) 		$js .= ' onmouseup="'.$onmouseup.$getJsTrigger['onmouseup'].'"';
if($onselect || $getJsTrigger['onselect']) 			$js .= ' onselect="'.$onselect.$getJsTrigger['onselect'].'"';
//========= EOF JAVASCRIPT ===============

//if default in sql mode
if($defaultQuery && $default)
{
	$defaultRs = $myQuery->query($default,'SELECT','NAME');
	$defaultRsCount = count($defaultRs);

	//if default have value
	if($defaultRsCount)
	{
		$defaultArr = array();
		
		for($b=0; $b < $defaultRsCount; $b++)
		{
			$defaultArr[] = $defaultRs[$b]['FLC_ID'];
				
			//if not using FLC_ID
			if(!isset($defaultRs[$b]['FLC_ID']))
			{
				$defaultRsKeys = array_keys($defaultRs[$b]);
				$defaultArr[] = $defaultRs[$b][$defaultRsKeys[$b]];
			}//eof if
		}
		
		$default = implode(',',$defaultArr);
	}//eof if
	else
		$default = '';	
}//eof if


//lookup resultset
if($lookup)
{
	if($default == '')
		$defaultUnselectedStr = '';
	else 
		$defaultUnselectedStr = ' where flc_id not in ('.$default.')';
		
	//lookup values
	$lookupRs = $myQuery->query('select * from ('.$lookup.') a'.$defaultUnselectedStr,'SELECT','NAME');
	$lookupRsCount = count($lookupRs);
	
	if($default == '')
		$defaultSelectedStr = ' where flc_id = -999999999';
	else 
		$defaultSelectedStr = ' where flc_id in ('.$default.')';
	
	//lookup selected values
	$lookupSelectedRs = $myQuery->query('select * from ('.$lookup.') a'.$defaultSelectedStr,'SELECT','NAME');
	$lookupSelectedRsCount = count($lookupSelectedRs);

}//eof if

$returnValue = '<table><tr><td style="border:none;">';

if($lookupRsCount)
{
	$tempValue = '';
	
	//loop on count of record
	for($x=0; $x < $lookupRsCount; $x++)
	{
		$tempValue .= '<option value="'.$lookupRs[$x]['FLC_ID'].'"';

		//to select value
		if($lookupRs[$x]['FLC_ID'] == $default)
			$tempValue .= ' selected ';

		$tempValue .= ' >'.$lookupRs[$x]['FLC_NAME'].'</option>';
	}//eof fot

	//value to return
	$returnValue .= '<select name="'.$name.'[]" tabindex="'.$tabindex.'" size="'.$textarearows.'" id="'.$name.'_unselected" class="'.$cssInputClass.' flc_select_unselect" '.$js.' multiple="mutiple" '.$disabled.$readonly.' title="'.$hints.'" style="'.$css.'" >'.$tempValue.'</select>';
}//eof if

$returnValue .= '</td><td style="border:none;text-align:center;">';

//buttons
$returnValue .= '<input type="button" class="inputButton '.$name.'_flc_select_unselect_mtr" style="margin-bottom:2px;" value="&gt;" onclick="moveoutid(\''.$name.'_unselected'.'\',\''.$name.'_selected'.'\'); listBoxSelectall(\''.$name.'_selected'.'\');" /><br>';
$returnValue .= '<input type="button" class="inputButton '.$name.'_flc_select_unselect_mtl" style="margin-bottom:2px;" value="&lt;" onclick="moveinid(\''.$name.'_unselected'.'\',\''.$name.'_selected'.'\'); listBoxSelectall(\''.$name.'_selected'.'\');" /><br>';
$returnValue .= '<input type="button" class="inputButton '.$name.'_flc_select_unselect_matr" style="margin-bottom:2px;" value="&gt;&gt;" onclick="listBoxSelectall(\''.$name.'_unselected'.'\'); moveoutid(\''.$name.'_unselected'.'\',\''.$name.'_selected'.'\'); listBoxSelectall(\''.$name.'_selected'.'\');" /><br>';
$returnValue .= '<input type="button" class="inputButton '.$name.'_flc_select_unselect_matl" style="margin-bottom:2px;" value="&lt;&lt;" onclick="listBoxSelectall(\''.$name.'_selected'.'\'); moveinid(\''.$name.'_unselected'.'\',\''.$name.'_selected'.'\'); listBoxSelectall(\''.$name.'_selected'.'\');" /><br>';

$returnValue .= '</td><td style="border:none;">';

if($lookupSelectedRsCount)
{
	$tempValue = '';
	
	//loop on count of record
	for($x=0; $x < $lookupSelectedRsCount; $x++)
	{
		$tempValue .= '<option value="'.$lookupSelectedRs[$x]['FLC_ID'].'"';

		//to select value
		if($lookupSelectedRs[$x]['FLC_ID'] == $default)
			$tempValue .= ' selected ';

		$tempValue .= ' >'.$lookupSelectedRs[$x]['FLC_NAME'].'</option>';
	}//eof fot

	//value to return
	$returnValue .= '<select name="'.$name.'[]" tabindex="'.$tabindex.'" size="'.$textarearows.'" id="'.$name.'_selected" class="'.$cssInputClass.' flc_select_unselect" '.$js.' multiple="mutiple" '.$disabled.$readonly.' title="'.$hints.'" style="'.$css.'" >'.$tempValue.'</select>';
}
else
{
	$tempValue = '';
	$returnValue .= '<select name="'.$name.'[]" tabindex="'.$tabindex.'" size="'.$textarearows.'" id="'.$name.'_selected" class="'.$cssInputClass.' flc_select_unselect" '.$js.' multiple="mutiple" '.$disabled.$readonly.' title="'.$hints.'" style="'.$css.'" >'.$tempValue.'</select>';
}

$returnValue .= '</td></tr></table>';
$returnValue .= "<script>jQuery(document).ready(function(){
	jQuery('#".$name.'_selected'."').find('option').attr('selected','selected');
});</script>";
?>
