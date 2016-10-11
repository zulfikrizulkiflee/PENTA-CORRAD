<?php
//function to display control button
function buildControl($myQuery,$controlID)
{
	//if have controlid
	if($controlID)
	{
		//get control attributes
		$getControl = "select * from FLC_PAGE_CONTROL where CONTROLID in (".implode(',',$controlID).") order by CONTROLORDER";
		$getControlRs = $myQuery->query($getControl,'SELECT','NAME');
		$getControlRsCount = count($getControlRs);
	}//eof if

	//loop on count of page control
	for($x=0; $x < $getControlRsCount; $x++)
	{
		//=============== CONTROL PRE-PROCESS ===============
		executePhpEvent($myQuery, 'preprocess', 'control', $getControlRs[$x]['CONTROLID']);
		//============= EOF CONTROL PRE-PROCESS =============

		//redirect url
		if($getControlRs[$x]['CONTROLREDIRECTURL'])
		{
			$getControlRs[$x]['CONTROLREDIRECTURL'] = convertDBSafeToQuery($getControlRs[$x]['CONTROLREDIRECTURL']);
			$redirectURL = ' document.getElementById(\'form1\').action = \''.button_url_encoding($getControlRs[$x]['CONTROLREDIRECTURL'].'&prevID='.$_GET['menuID'].'&prevFormat='.$_GET['page']).'\';';
		}//eof if
		else
			$redirectURL = '';

		//switch item type
		switch($getControlRs[$x]['CONTROLTYPE'])
		{
			//save
			case '1':
				$controlAttr = ' type="submit"';

				//default onclick
				$onclick[$x] = $redirectURL;
			break;

			//reset
			case '2':
				$controlAttr = ' type="reset"';
			break;

			//back
			case '3':
				$controlAttr = ' type="submit"';

				//default onclick
				$onclick[$x] = 'document.getElementById(\'form1\').action = \''. button_url_encoding("index.php?page=".$_GET['prevFormat'].'&menuID='.$_GET['prevID']).'\'; document.getElementById(\'form1\').submit();';
			break;

			//print
			case '5':
				$controlAttr = ' type="button"';

				//default onclick
				$onclick[$x] = 'flcPrint(this)';
			break;

			//submit
			case '6':
				$controlAttr = ' type="submit"';

				//default onclick
				$onclick[$x] = $redirectURL;
			break;

			//delete
			case '8':
				$controlAttr = ' type="submit"';

				//default onclick
				$onclick[$x] = $redirectURL;
			break;

			//new popup - GET
			case '20':
				$controlAttr = ' type="button"';

				//default onclick
				$onclick[$x] = 'my_new_window(getInputList(\'form1\',\''.button_url_encoding($getControlRs[$x]['CONTROLREDIRECTURL']).'\'),\'\',800,500);';
			break;

			//new popup - POST
			case '21':
				$controlAttr = ' type="button"';

				//default onclick
				$onclick[$x] = 'form1.target=\'_blank\'; document.getElementById(\'form1\').action=\''.button_url_encoding($getControlRs[$x]['CONTROLREDIRECTURL']).'\'; document.getElementById(\'form1\').submit();"';
			break;

			//unsubmit button, normal button
			case '25':
				$controlAttr = ' type="button"';
			break;

			//pdf button untuk component report - Luqman
			case '30':
				$controlAttr = 'type="button"';

				//default onclick
				$onclick[$x] = 'PromptPDFExport(this)';
			break;

			//csv button - Luqman
			case '31':
				$controlAttr = 'type="button"';

				//default onclick
				$onclick[$x] = 'ExportReport(\'csv\',this)';
			break;
		}//eof switch

		//if image
		if($getControlRs[$x]['CONTROLIMAGEURL'])
		{
			//if file exist
			if(is_file($getControlRs[$x]['CONTROLIMAGEURL']))
			{
				//size of original image
				$imgSize = getimagesize($getControlRs[$x]['CONTROLIMAGEURL']);
				$imgWidth = $imgSize[0];		//array [0] - width
				$imgHeight = $imgSize[1];		//array [1] - height
			}//eof if

			$controlAttr .= ' class="inputButtonImage" style="border:none; background-color:none;background-image:url('.$getControlRs[$x]['CONTROLIMAGEURL'].'); width:'.$imgWidth.'px; height:'.$imgHeight.'px;" value=""';
		}//eof if
		else
		{
			$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],6,$getControlRs[$x]['CONTROLID'],'CONTROLTITLE');
			($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'CONTROLTITLE') ? $controlTitle = $tr8nStr[0]['TRANS_TEXT'] : $controlTitle = $getControlRs[$x]['CONTROLTITLE'];

			$controlAttr .= ' class="inputButton" value="'.$controlTitle.'"';
		}//eof else

		//========= JAVASCRIPT ===================
		//js trigger
		$getJsTrigger[$x] = getJsTrigger('control', $getControlRs[$x]['CONTROLID']);

		//javascript, append js trigger with pre-set js
		if($onblur[$x]||$getJsTrigger[$x]['onblur']) 			$js[$x] .= ' onblur="'.$onblur[$x].$getJsTrigger[$x]['onblur'].'"';
		if($onchange[$x]||$getJsTrigger[$x]['onchange']) 		$js[$x] .= ' onchange="'.$onchange[$x].$getJsTrigger[$x]['onchange'].'"';
		if($onclick[$x]||$getJsTrigger[$x]['onclick']) 			$js[$x] .= ' onclick="'.$onclick[$x].$getJsTrigger[$x]['onclick'].'"';
		if($ondblclick[$x]||$getJsTrigger[$x]['ondblclick'])	$js[$x] .= ' ondblclick="'.$ondblclick[$x].$getJsTrigger[$x]['ondblclick'].'"';
		if($onfocus[$x]||$getJsTrigger[$x]['onfocus']) 			$js[$x] .= ' onfocus="'.$onfocus[$x].$getJsTrigger[$x]['onfocus'].'"';
		if($onkeydown[$x]||$getJsTrigger[$x]['onkeydown']) 		$js[$x] .= ' onkeydown="'.$onkeydown[$x].$getJsTrigger[$x]['onkeydown'].'"';
		if($onkeypress[$x]||$getJsTrigger[$x]['onkeypress']) 	$js[$x] .= ' onkeypress="'.$onkeypress[$x].$getJsTrigger[$x]['onkeypress'].'"';
		if($onkeyup[$x]||$getJsTrigger[$x]['onkeyup']) 			$js[$x] .= ' onkeyup="'.$onkeyup[$x].$getJsTrigger[$x]['onkeyup'].'"';
		if($onmousedown[$x]||$getJsTrigger[$x]['onmousedown'])	$js[$x] .= ' onmousedown="'.$onmousedown[$x].$getJsTrigger[$x]['onmousedown'].'"';
		if($onmousemove[$x]||$getJsTrigger[$x]['onmousemove'])	$js[$x] .= ' onmousemove="'.$onmousemove[$x].$getJsTrigger[$x]['onmousemove'].'"';
		if($onmouseout[$x]||$getJsTrigger[$x]['onmouseout']) 	$js[$x] .= ' onmouseout="'.$onmouseout[$x].$getJsTrigger[$x]['onmouseout'].'"';
		if($onmouseover[$x]||$getJsTrigger[$x]['onmouseover'])	$js[$x] .= ' onmouseover="'.$onmouseover[$x].$getJsTrigger[$x]['onmouseover'].'"';
		if($onmouseup[$x]||$getJsTrigger[$x]['onmouseup']) 		$js[$x] .= ' onmouseup="'.$onmouseup[$x].$getJsTrigger[$x]['onmouseup'].'"';
		if($onselect[$x]||$getJsTrigger[$x]['onselect']) 		$js[$x] .= ' onselect="'.$onselect[$x].$getJsTrigger[$x]['onselect'].'"';
		//========= EOF JAVASCRIPT ===============

		//button tag
		$returnValue = '<input name="'.$getControlRs[$x]['CONTROLNAME'].'" id="'.$getControlRs[$x]['CONTROLNAME'].'"'.$controlAttr.$js[$x].' title="'.$getControlRs[$x]['CONTROLHINTS'].'" /> ';

		//if have pre-set or trigger onload js
		if($onload[$x] || $getJsTrigger[$x]['onload'])
			$returnValue .= '<script>'.$onload[$x].$getJsTrigger[$x]['onload'].'</script>';

		//create button
		echo $returnValue;
	}//eof for
}//eof function

function button_url_encoding($url)
{
	if(BUTTON_URL_SECURITY)
	{
		$url_file = substr($url,0,strpos($url,"?"));
		$url_item = substr($url,strpos($url,"?")+1);
		$url = $url_file."?a=".flc_url_encode($url_item);
	}//eof if

	return $url;
}//eof function
?>
