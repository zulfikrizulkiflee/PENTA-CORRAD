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

//return as string
$returnValue = "<input type=\"text\" style=\"text-align:right\" class=\"autonumeric\" name=\"".$name."\" id=\"".$id."\" value=\"".$default."\" />";
$returnValue .= "<script type=\"text/javascript\">";
$returnValue .= "jQuery(function($) {";
$returnValue .= "    $('.autonumeric').autoNumeric('init');";
$returnValue .= "});";
$returnValue .= "</script>";
