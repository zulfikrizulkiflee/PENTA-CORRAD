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

//value to return
$returnValue = '<div name="gmap_wrapper_'.$name.'" id="gmap_wrapper_'.$name.'"></div>';
$returnValue .= '<input onchange="if(this.value == \'geo\') var latlong = new Array(\'geo\',\'geo\'); else var latlong = this.value.split(\',\'); flc_googleMap(latlong[0],latlong[1],'.$textarearows.','.$length.',\'gmap_wrapper_'.$name.'\');" name="'.$name.'" id="'.$name.'" type="text" tabindex="'.$tabindex.'" placeholder="'.$hints.'" class="'.$cssInputClass.'" style="width:'.($length-10).'px" '.$js.' title="'.$hints.'" value="'.$default.'" />';

//if tabular/report
//var_dump($runningNo);
//if($runningNo != null)
	$runningNo = '_'.$runningNo;
			
//geolocation default
if($default == 'geo')
	$returnValue .= '<script>flc_googleMap(\'geo\',\'geo\','.$textarearows.','.$length.',\'gmap_wrapper_'.$name.'\');</script>';
else if($default != '')
	$returnValue .= '<script>flc_googleMap('.$default.','.$textarearows.','.$length.',\'gmap_wrapper_'.$name.'\');</script>';

return $returnValue;
?>
