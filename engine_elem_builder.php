<script type="text/javascript" src="tools/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="tools/jscolor/jscolor.js"></script>
<script type="text/javascript" src="tools/timepicker/jquery.timePicker.js"></script>
<link href="tools/timepicker/timePicker.css" rel="stylesheet" type="text/css" />
<?php
//function to build input
function buildInput($myQueryArr, $itemArr, $runningNo='', $style='')
{
	$myQuery=$myQueryArr['myQuery'];
	//initialize variables
	$type			= isset($type) 			? $type : '';
	$componentid	= isset($componentid) 	? $componentid : '';
	$id				= isset($id) 			? $id : '';
	$name			= isset($name) 			? $name : '';
	$title			= isset($title) 		? $title : '';
	$default		= isset($default) 		? $default : '';
	$defaultQueryDB	= isset($defaultQueryDB)? $defaultQueryDB : '';
	$defaultQuery	= isset($defaultQuery) 	? $defaultQuery : '';
	$lookup			= isset($lookup) 		? $lookup : '';
	$lookupDB		= isset($lookupDB) 		? $lookupDB : '';
	$tabindex		= isset($tabindex) 		? $tabindex : '';
	$minchar		= isset($minchar) 		? $minchar : '';
	$maxchar		= isset($maxchar) 		? $maxchar : '';
	$length			= isset($length) 		? $length : '';
	$textarearows	= isset($textarearows) 	? $textarearows : '';
	$hints			= isset($hints) 		? $hints : '';
	$placeholder	= isset($placeholder) 	? $placeholder : '';
	$mapping		= isset($mapping) 		? $mapping : '';
	$textalign		= isset($textalign) 	? $textalign : '';
	$case			= isset($case) 			? $case : '';
	$disabled		= isset($disabled) 		? $disabled : '';
	$readonly		= isset($readonly) 		? $readonly : '';
	$addAttrib		= isset($addAttrib) 	? $addAttrib : '';

	//assign variable from item array
	$type			= $itemArr['ITEMTYPE'];
	$componentid	= $itemArr['COMPONENTID'];
	$id				= $itemArr['ITEMID'];
	$name			= $itemArr['ITEMNAME'];
	$title			= $itemArr['ITEMTITLE'];
	$default		= $itemArr['ITEMDEFAULTVALUE'];
	$defaultQueryDB	= $itemArr['ITEMDEFAULTVALUEDB'];
	$defaultQuery	= $itemArr['ITEMDEFAULTVALUEQUERY'];
	$lookup			= $itemArr['ITEMLOOKUP'];
	$lookupDB		= $itemArr['ITEMLOOKUPDB'];
	$tabindex		= $itemArr['ITEMTABINDEX'];
	$minchar		= $itemArr['ITEMMINCHAR'];
	$maxchar		= $itemArr['ITEMMAXCHAR'];
	$length			= $itemArr['ITEMINPUTLENGTH'];
	$textarearows	= $itemArr['ITEMTEXTAREAROWS'];
	$hints			= $itemArr['ITEMHINTS'];
	$placeholder	= $itemArr['ITEMPLACEHOLDER'];
	$mapping		= $itemArr['ITEMMAPPING'];
	$textalign		= $itemArr['ITEMTEXTALIGN'];
	$case			= $itemArr['ITEMUPPERCASE'];
	$disabled		= $itemArr['ITEMDISABLED'];
	$readonly		= $itemArr['ITEMREADONLY'];
	$addAttrib		= explode('|',$itemArr['ITEMADDITIONALATTR']);
	$lookuptype		= $itemArr['ITEMLOOKUPTYPE'];

	if(DBMS_NAME == 'informix')
	{
		if($lookuptype == 'no' || $lookuptype == '')
			$lookup = '';
	}

	//for tabular punye tabindex
	if(is_array($style) && $style['form_type'] == 'tabular')
		$tabindex = ($runningNo * 50) + (int) $tabindex + (int) $componentid;

	//convert POST,GET,SESSION variable
	$default= convertDBSafeToQuery($default);
	$lookup = convertDBSafeToQuery($lookup);

	//current date
	if(strpos($lookup,'{CONST|CURRENT_DATE}') !== false)
	{
		$lookup = str_replace("{CONST|CURRENT_DATE}",date('Y-m-d'),$lookup);
	}//eof if

	//get current running no
	if(strpos($lookup,'{CONST|RUNNING_NO}') !== false)
	{
		$lookup = str_replace("{CONST|RUNNING_NO}",$runningNo,$lookup);
	}//eof if

	//get current running no (javascript)
	if(strpos($javascript,'{CONST|RUNNING_NO}') !== false)
	{
		$javascript = str_replace("{CONST|RUNNING_NO}",$runningNo,$javascript);
	}//eof if

	//if default in sql mode
	if($defaultQuery && $default)
	{
		$defaultRs = $myQueryArr['myQuery'.$defaultQueryDB]->query($default,'SELECT','NAME');
		$defaultRsCount = count($defaultRs);

		//if default have value
		if($defaultRsCount)
		{
			//checkbox, handle multi default value (added by fais to check from multi value)
			if($defaultRsCount>1 && $type == 'checkbox')
			{
				//if not using FLC_ID
				if(!isset($defaultRs[0]['FLC_ID']))
				{
					//loop on count of defaultRs
					for($x = 0; $x < $defaultRsCount; $x++)
					{
						$defaultRsKeys = array_keys($defaultRs[0]);
						$default[$x]['FLC_ID'] = $defaultRs[$x][$defaultRsKeys[0]];
					}//eof for
				}//eof if
				else 
					$default = $defaultRs;
			}//eof if
			else
			{
				$default = $defaultRs[0]['FLC_ID'];
				$label = $defaultRs[0]['FLC_NAME'];

				//if not using FLC_ID
				if(!isset($defaultRs[0]['FLC_ID']))
				{
					$defaultRsKeys = array_keys($defaultRs[0]);
					$default = $defaultRs[0][$defaultRsKeys[0]];
				}//eof if
			}//eof else
		}//eof if
		else
			$default = '';
	}//eof if

	//lookup resultset
	if($lookup)
	{
		//lookup values
		$lookupRs = $myQueryArr['myQuery'.$lookupDB]->query($lookup,'SELECT','NAME');
		$lookupRsIdx = $myQueryArr['myQuery'.$lookupDB]->query($lookup,'SELECT','INDEX');
		$lookupRsCount = count($lookupRs);

		//get keys/column name
		if($lookupRsCount)
		{
			$lookupRsKeys = array_keys($lookupRs[0]);
			$lookupRsKeysCount = count($lookupRsKeys);
		}//eof if
	}//eof if

	$cssInputClass = 'inputInput';				//item class

	//unset length if 0
	if($length==0)
		unset($length);

	//text alignment
	if($textalign)
		$css .= 'text-align:'.$textalign.';';

	if($type == 'listbox')
		$css .= 'height:'.($textarearows*20).'px;';

	//case
	if($case=='1')
	{
		$css .= 'text-transform:uppercase;';
		$onchange .= 'this.value = this.value.toUpperCase();';
	}//eof if

	//disabled
	if($disabled)
		$disabled=' disabled ';
	else
		$disabled='';

	//readonly
	if($readonly)
		$readonly=' readonly ';
	else
		$readonly='';

	if($readonly&&!$disabled)
		$css .= 'background-color:#F2F2F2;';

	//for readonly
	$cssReadonlyStyle = 'style="background-color:#F2F2F2;'.$textalign.';'.$case.'"';

	//for min character
	if($minchar)
		$onchange = 'if(this.value.length < '.$minchar.') alert(\'Ralat! Sila isikan nilai lebih dari '.$minchar.' aksara\');';

	//for max character
	if($maxchar && $maxchar>$minchar)
	{
		//exclude ctrl+c v x, tab,capslock, shift, backspace, up, down, left,right,ctrl
		$onkeydown = 'if((event.ctrlKey && (event.keyCode == 67 || event.keyCode == 86 || event.keyCode == 88)) || event.keyCode == 9 || event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 8 || event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 17) {} else {';
		$onkeydown .= 'if(this.value.length >= '.$maxchar.') this.value = this.value.substring(0, '.$maxchar.');';
		$onkeydown .= '}';
	}
	//====== DATE =============
	/*if($type=='date')
	{
		//date format (for js)
		$jsDateFormat = str_replace('format-','',DEFAULT_DATE_FORMAT);
		$jsDateFormat = str_replace('d','dd',$jsDateFormat);
		$jsDateFormat = str_replace('m','mm',$jsDateFormat);
		$jsDateFormat = str_replace('Y','yyyy',$jsDateFormat);

		//day separator
		$jsDaySeparator = str_replace('d', '', substrByChar($jsDateFormat, 'd', 'm'));

		//temporarily for '-' only
		if($jsDaySeparator == '-' || $jsDaySeparator == '/')
		{
			//add onkeypress js
			$onkeypress .= "return addDateSeparator(event, this, '".$jsDateFormat."', '".$jsDaySeparator."');";
		}//eof if
	}//eof if*/
	//====== EOF DATE =========

	//====== FILE UPLOAD ======
	if($type=='file')
	{
		//get upload parameter from component item
		$getUploadParam = "select ITEMUPLOAD from FLC_PAGE_COMPONENT_ITEMS where ITEMID = ".$id;
		$getUploadParamRs = $myQuery->query($getUploadParam,'SELECT','NAME');

		//explode the data in ITEMUPLOAD FIELD
		$uploadParam = explode("|",$getUploadParamRs[0]['ITEMUPLOAD']);

		//upload extension allowed
		$tempUploadExt = explode(';',$uploadParam[0]);
		$tempUploadExtCount = count($tempUploadExt);

		//loop on count of extension
		for($i=0; $i < $tempUploadExtCount; $i++)
			if($tempUploadExt[$i])
				$uploadExt[] = trim($tempUploadExt[$i]);

		//upload parameter
		$uploadMaxSize = $uploadParam[2];

		//if have extension filtering
		if($uploadExt)
		{
			//js to filter the extension
			$onchange = "if(!filterFileExtension(this.value, new Array('".implode("','",$uploadExt)."'))){alert('Jenis fail yang ingin dimuatnaik adalah tidak dibenarkan!'); this.value=''; return false;}";
		}//eof if

		//if have max size filtering
		if($uploadMaxSize)
		{
			//js to filter the max size
			$onchange .= "if(!filterFileSize(this, ".$uploadMaxSize.")){alert('Saiz fail yang ingin dimuatnaik melebihi saiz yang dibenarkan!'); this.value=''; return false;}";
		}//eof if
	}//eof if
	//====== EOF FILE UPLOAD ======

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

	//switch item type
	switch($type)
	{
		//AUTHOR: AISHAH 2013/8/23
		case 'auto_sugg':
			//if have lookup
			if($lookupRsCount)
			{
				//loop on count of lookup
				for($x=0; $x < $lookupRsCount; $x++)
				{
					if($lookupRs[$x]['FLC_ID']==$default)
						$label=$lookupRs[$x]['FLC_NAME'];
				}//eof for
			}//eof if

			//if no label
			if(!$label)
				$label=$default;

			//hidden value
			$temp .= '<input name="'.$name.'" id="'.$name.'" type="hidden" value="'.$default.'" />';

			//text to show
			$temp .= '<input name="auto_text_'.$name.'" id="auto_text_'.$name.'" type="text" class="'.$cssInputClass.'"  size="'.$length.'"
						title="'.$hints.'" placeholder="'.$placeholder.'" value="'.$label.'" />';

			$temp .= '<div id="suggestions_'.$id.'" class="auto_sugg_list" style="position:absolute; box-shadow:#888 5px 10px 10px; -webkit-box-shadow : #888 5px 10px 10px;
															-moz-box-shadow : #888 5px 10px 10px; display:none; background-color:white;padding:0px;
															min-height:10px;max-height:200px;width:40%;overflow:auto; margin-left:10px"></div>';

			//value to return
			$returnValue = $temp;
			
			$_SESSION['delayedInitScript'] .= 'flc_auto_suggest_lookup(this.value,'.$id.',\''.$name.'\');';		
		break;

		case 'ajax_updater':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if
			?>

			<script language="javascript">
				setTimeout("exec_ajax_updater('<?php echo 'ajax_updater_'.$componentid.'_'.$id;?>','<?php echo $id;?>')", 100)
			</script>

			<?php
			//value to return
			$returnValue = '<div id="ajax_updater_'.$componentid.'_'.$id.'"><input name="'.$name.'" id="'.$name.'" type="text" tabindex="'.$tabindex.'" class="'.$cssInputClass.'" value="'.$default.'" size="'.$length.'" '.$js.' readonly="readonly" '.$cssReadonlyStyle.' title="'.$hints.'" /></div>';
		break;

		case 'ajax_updater_subsequent':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="text" tabindex="'.$tabindex.'" class="'.$cssInputClass.'" value="'.$default.'" size="'.$length.'" '.$js.' readonly="readonly" '.$cssReadonlyStyle.' title="'.$hints.'" />';
		break;

		case 'checkbox':
			//if have lookup
			if($lookupRsCount)
			{
				//loop on count of record
				for($x=0; $x < $lookupRsCount; $x++)
				{
					$tempValue .= '<label title="'.$hints.'"><input type="checkbox" name="'.$name.'[]" id="'.$name.'" tabindex="'.$tabindex.'" value="'.$lookupRs[$x]['FLC_ID'].'" ';

					if(is_array($default))
					{
						//loop on count of default
						$defaultCount = count($default);
						for($y = 0; $y < $defaultCount; $y++)
						{
							//to select value
							if($lookupRs[$x]['FLC_ID'] && $lookupRs[$x]['FLC_ID'] == $default[$y]['FLC_ID'])
								$tempValue .= ' checked="checked" ';
						}//eof for
					}//eof if
					else
					{
						//to select value
						if($lookupRs[$x]['FLC_ID'] && $lookupRs[$x]['FLC_ID'] == $default)
							$tempValue .= ' checked="checked" ';
					}//eof else

					$tempValue .= $js.' '.$disabled.$readonly.' />'.$lookupRs[$x]['FLC_NAME'].'</label>';

					if($x+1 != $lookupRsCount)
						$tempValue .= '<br>';
				}//eof for

				//value to return
				$returnValue = $tempValue;
			}
		break;

		case 'chart':
			$getChart = "select * from FLC_CHART where ITEM_ID = ".$id;
			$getChartRs = $myQuery->query($getChart,'SELECT','NAME');
			$getChartRsCount = count($getChartRs);

			//if chart have item
			if($getChartRsCount>0)
			{
				//if have chart SQL
				if($getChartRs[0]['CHART_PSQL'])
				{
					$chartData = $getChartRs[0]['CHART_PSQL'];
					$chartDataDB = $getChartRs[0]['CHART_PSQL_DB'];
					$chartData = convertDBSafeToQuery($getChartRs[0]['CHART_PSQL']);
					$chartDataRs =  $myQueryArr['myQuery'.$chartDataDB] ->query($chartData,'SELECT','NAME');
					$chartDataRsCount = count($chartDataRs);

					//if chart data have value
					if($chartDataRsCount>0)
					{
						//split chart category and type
						$chartCategoryType = explode('_',$getChartRs[0]['CHART_PTYPE']);

						//include chart class
						require_once('class/chart.php');

						//Chart library (currently using FusionCharts)
						require_once('tools/fusionChart/FusionCharts.php');

						$chart = new Chart($name, $chartCategoryType[0], $chartCategoryType[1], $length, $textarearows, $chartDataRs);

						//class chart variable
						$chart->setChartCaption($default);
						$chart->setChartDecimalPrecision($getChartRs[0]['CHART_DECIMAL_PRECISION']);
						$chart->setChartBgColor($getChartRs[0]['CHART_BG_COLOR']);
						$chart->setChartNoPrefix($getChartRs[0]['CHART_NO_PREFIX']);
						$chart->setChartXAxisLabel($getChartRs[0]['CHART_X_AXIS_LABEL']);
						$chart->setChartPrimaryYAxisLabel($getChartRs[0]['CHART_PY_AXIS_LABEL']);
						$chart->setChartPrimaryShowValue($getChartRs[0]['CHART_PSHOW_VALUE']);

						//if have secondary chart SQL
						if($getChartRs[0]['CHART_SSQL'])
						{
							$chartSecondaryData = $getChartRs[0]['CHART_SSQL'];
							$chartSecondaryDataDB = $getChartRs[0]['CHART_SSQL_DB'];
							$chartSecondaryDataRs = $myQueryArr['myQuery'.$chartSecondaryDataDB]->query($chartSecondaryData,'SELECT','NAME');
							$chartSecondaryDataRsCount = count($chartSecondaryDataRs);

							//if secondary chart data have value
							if($chartSecondaryDataRsCount>0)
							{
								$chart->setChartSecondaryData($chartSecondaryDataRs);
								$chart->setChartSecondaryYAxisLabel($getChartRs[0]['CHART_SY_AXIS_LABEL']);
								$chart->setChartSecondaryShowValue($getChartRs[0]['CHART_SSHOW_VALUE']);
							}//eof if
						}//eof if

						//value to return
						$returnValue = $chart->generateChartHTML();
					}//eof if
				}//eof if
			}//eof if
		break;

		case 'color_picker':
			//default length
			if(!$length)
				$length=8;

			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="'.$type.'" tabindex="'.$tabindex.'" maxlength="'.$maxchar.'" class="color {hash:true} '.$cssInputClass.'" value="'.trim($default).'" size="'.$length.'" style="'.$css.' " '.$js.' '.$disabled.$readonly.' title="'.$hints.'" />';
		break;

		case 'custom':
			include($default);
		break;

		case 'date':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//date format
			$dateFormatDefault = str_replace('format-','',DEFAULT_DATE_FORMAT);

			//if found dash (-)
			if(strpos($dateFormatDefault, '-') !== false)
				$dateFormat = str_replace('-','-ds-',$dateFormatDefault);
			//elseif found slash (/)
			else if(strpos($dateFormatDefault, '/') !== false)
				$dateFormat = str_replace('/','-sl-',$dateFormatDefault);

			//check whether caller is tabular or form --> form/tabular
			$compType = checkTabularOrForm($myQuery,$componentid);

			//generate item id for datepicker javascript usage
			if($compType!='tabular')
				$itemId = $name;
			else
				$itemId = $name.'_'.$runningNo;

			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="text" tabindex="'.$tabindex.'" class="'.$cssInputClass.'" value="'.trim($default).'" size="'.$length.'" '.$js.' '.$disabled.$readonly.' title="'.$hints.'" placeholder="'.$placeholder.'" style="'.$css.'" /><script>var opts = {formElements:{"'.$itemId.'":"'.$dateFormat.'"}, showWeeks:true, statusFormat:"l-cc-sp-d-sp-F-sp-Y"}; datePickerController.createDatePicker(opts);</script>';
		break;

		case 'dropdown':
			//if have lookup
			if($lookupRsCount)
			{
				$tempValue = '<option></option>';

				//if($lookupRsKeysCount == 2)
				//{
					//loop on count of record
					for($x=0; $x < $lookupRsCount; $x++)
					{
						$keyName = $lookupRsKeys[$y];
						$tempValue .= '<option value="'.$lookupRs[$x]['FLC_ID'].'"';

						//to select value
						if($lookupRs[$x]['FLC_ID'] == $default)
							$tempValue .= ' selected ';

						$tempValue .= ' >'.$lookupRs[$x]['FLC_NAME'].'</option>';
					}
				//}

				/*
				else
				{
					$dropdownExtendedFlag = true;
					$dropdownExtendedStr = '';

					//loop on count of record
					for($x=0; $x < $lookupRsCount; $x++)
					{
						$dropdownExtendedStr .= '<tr>';

						//loop on lookup column
						for($y=0; $y < $lookupRsKeysCount; $y++)
						{
							//get parameter
							if($y == 0)
							{
								$keyName = $lookupRsKeys[$y];

								$dropdownExtendedStr .= '<td><a href="javascript:void(0)">'.$lookupRsIdx[$x][$y].'</a></td>';

								//to select value
								//if($lookupRs[$x]['FLC_ID'] == $default)
								//	$tempValue .= ' selected ';

								//$tempValue .= ' >';
							}
							else
								$dropdownExtendedStr .= '<td>'.$lookupRsIdx[$x][$y].'</td>';
						}//eof for column

						$dropdownExtendedStr .= '</tr>';
						//$tempValue .= '</option>';
					}//eof for
				}
				*/
			}//eof if

			//value to return
			$returnValue = '<select name="'.$name.'" tabindex="'.$tabindex.'" id="'.$name.'" class="'.$cssInputClass.'" '.$js.' '.$disabled.$readonly.' title="'.$hints.'" >'.$tempValue.'</select>';

			/*
			if($dropdownExtendedFlag)
				$returnValue .= '<table name="'.$name.'" id="'.$name.'" style="width:500px;background-color:white;border:1px solid black;">
								'.$dropdownExtendedStr.'</table>';
			*/

		break;

		case 'file':
			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="file" tabindex="'.$tabindex.'" class="'.$cssInputClass.'" size="'.$length.'" '.$js.' title="'.$hints.'" />';
		break;

		case 'hidden':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="'.$type.'" value="'.trim($default).'" size="'.$length.'" />';
		break;

		case 'image':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//width
			if($length)
				$imageWidth = 'width="'.$length.'"';

			//heigth
			if($textarearows)
				$imageHeight = ' height="'.$textarearows.'"';

			//value to return
			$returnValue = '<img src="'.$default.'" alt="'.$hints.'" '.$imageWidth.' '.$imageHeight.' border="0" '.$js.' />';
		break;

		case 'label':
			//length of label
			if(!$length)
				$length='95%';
			else
				$length.='px';

			//if have lookup
			if($lookupRsCount)
			{
				//loop on count of record
				for($x=0; $x < $lookupRsCount; $x++)
				{
					if($lookupRs[$x]['FLC_ID'] == $default)
						$temp .= $lookupRs[$x]['FLC_NAME'];
				}
				$label = $temp;
			}
			//else just return the value
			else
				$label = $default;

			//href encoding
			if(BUTTON_URL_SECURITY)
				$label = href_encoding($label);

			//value to return
			$returnValue = '<label id="'.$name.'" style="display:block; width:'.$length.';'.$css.'" '.$js.' title="'.$hints.'">'.$label.'</label>';
		break;

		case 'label_with_hidden':
			//length of label
			if(!$length)
				$length='95%';
			else
				$length.='px';

			//if have lookup
			if($lookupRsCount)
			{
				//if have default value
				if($default)
				{
				//loop on count of record
				for($x=0; $x < $lookupRsCount; $x++)
				{
					if($lookupRs[$x]['FLC_ID'] == $default)
					{
						$default = $lookupRs[$x]['FLC_ID'];
						$label = $lookupRs[$x]['FLC_NAME'];
					}
					}//eof for
				}//eof if
				else
				{
					//if hav lookup
					if($lookupRs[0]['FLC_ID'])
						$default=$lookupRs[0]['FLC_ID'];

					if($lookupRs[0]['FLC_NAME'])
						$label=$lookupRs[0]['FLC_NAME'];
					else
						$label=$default;
				}//eof else
				//$label = $temp;
/*
				//if hav lookup
				if($lookupRs[0]['FLC_ID'])
					$default=$lookupRs[0]['FLC_ID'];

				if($lookupRs[0]['FLC_NAME'])
					$label=$lookupRs[0]['FLC_NAME'];
				else
					$label=$default;
*/
			}
			//else just return the value
			else
				$label = $default;

			//href encoding
			if(BUTTON_URL_SECURITY)
				$label = href_encoding($label);

			//value to return
			$returnValue = '<label style="display:block; width:'.$length.';'.$css.'" '.$js.' title="'.$hints.'">'.$label.'</label><input name="'.$name.'" id="'.$name.'" type="hidden" value="'.$default.'" />';
		break;

		case 'listbox':
			//if have lookup
			if($lookupRsCount)
			{
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
				$returnValue = '<select name="'.$name.'[]" tabindex="'.$tabindex.'" size="'.$textarearows.'" id="'.$name.'[]" class="'.$cssInputClass.'" '.$js.' multiple="mutiple" '.$disabled.$readonly.' title="'.$hints.'" style='.$css.' >'.$tempValue.'</select>';
			}//eof if
		break;

		case 'lov':
			//get pageid
			$getPage = "select PAGEID from FLC_PAGE_COMPONENT where COMPONENTID = ".$componentid;
			$getPageRs = $myQuery->query($getPage,'SELECT','NAME');
			$pageid = $getPageRs[0]['PAGEID'];

			//if have lookup
			if($lookupRsCount)
			{
				//loop on count of lookup
				for($x=0; $x < $lookupRsCount; $x++)
				{
					if($lookupRs[$x]['FLC_ID']==$default)
						$label=$lookupRs[$x]['FLC_NAME'];
				}//eof for
			}//eof if

			//if no label
			if(!$label)
				$label=$default;

			//hidden value
			$temp .= '<input name="'.$name.'" id="'.$name.'" type="hidden" value="'.trim($default).'" />';

			//text to show
			$temp .= '<input name="lov_text_'.$name.'" id="lov_text_'.$name.'" type="text" class="'.$cssInputClass.'" readonly="readonly" '.$cssReadonlyStyle.' value="'.trim($label).'" size="'.$length.'" title="'.$hints.'" placeholder="'.$placeholder.'" />';


			//lov button
			$temp .= ' <input name="lov_button_'.$name.'" id="lov_button_'.$name.'" type="button" class="inputButton" value=" ... " onclick="show_lov(\''.$pageid.'\', this.id+\'||\'+this.name);" '.$disabled.' title="'.$hints.'" />';

			//value to return
			$returnValue = $temp;
		break;

		case 'password':
			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="password" tabindex="'.$tabindex.'" maxlength="'.$maxchar.'" class="'.$cssInputClass.'" value="'.trim($default).'" size="'.$length.'" '.$js.' '.$disabled.$readonly.' title="'.$hints.'" placeholder="'.$placeholder.'" />';
		break;

		case 'password_md5':
			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="password" tabindex="'.$tabindex.'" maxlength="'.$maxchar.'" class="'.$cssInputClass.'" value="'.trim($default).'" size="'.$length.'" '.$js.' '.$disabled.$readonly.' title="'.$hints.'" placeholder="'.$placeholder.'" />';
		break;

		case 'plugin':
			//=============== INCLUDE EXTENSION ==============
			$returnValue = includeExtension('item', 'builder', $itemArr, $runningNo);
			//============= EOF INCLUDE EXTENSION ============
		break;

		case 'radio':
			//if have lookup
			if($lookupRsCount)
			{
				//loop on count of record
				for($x=0; $x < $lookupRsCount; $x++)
				{
					$tempValue .= '<label title="'.$hints.'"><input type="radio" name="'.$name.'" id="'.$name.'" tabindex="'.$tabindex.'" value="'.$lookupRs[$x]['FLC_ID'].'" ';

					//to select value
					if($lookupRs[$x]['FLC_ID'] == $default)
						$tempValue .= ' checked="checked" ';

					$tempValue .= $js.' '.$disabled.$readonly.' />'.$lookupRs[$x]['FLC_NAME'].'</label>';
				}//eof for

				//value to return
				$returnValue = $tempValue;
			}//eof if
		break;

		case 'text':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//value to return
			$returnValue = '<input name="'.$name.'" id="'.$name.'" type="'.$type.'" tabindex="'.$tabindex.'" maxlength="'.$maxchar.'" class="'.$cssInputClass.'" value="'.trim($default).'" size="'.$length.'" style="'.$css.'" '.$js.' '.$disabled.$readonly.' title="'.$hints.'" placeholder="'.$placeholder.'" />';
		break;

		case 'textarea':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//value to return
			$returnValue ='<textarea name="'.$name.'" id="'.$name.'" tabindex="'.$tabindex.'" class="'.$cssInputClass.'" cols="'.$length.'" rows="'.$textarearows.'" style="'.$css.'" '.$js.' '.$disabled.$readonly.' title="'.$hints.'" placeholder="'.$placeholder.'">'.trim($default).'</textarea>';
		break;

		case 'text_editor':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
			}//eof if

			//if have default value
			if($default)
			{
				$default = str_replace("\n",'',$default);		//" sign
				$default = str_replace("\r",'',$default);		//" sign
				$default = str_replace("\n\n",'',$default);		//" sign
			}//eof if

			//if readonly
			if($readonly)
			{
				$editorReadOnly = ', {readOnly:true}';
			}//eof if

			//check whether caller is tabular or form --> form/tabular
			$compType = checkTabularOrForm($myQuery,$componentid);

			//not listing component
			if($compType!='tabular')
				$itemId = 'CKEDITOR.replace(\''.$name.'\''.$editorReadOnly.');';
			else
				$itemId = 'CKEDITOR.replace(\''.$name.'\''.$editorReadOnly.');';

			//value to return
			$returnValue = '<textarea name="'.$name.'" id="'.$name.'" title="'.$hints.'">'.trim($default).'</textarea>';
			$_SESSION['delayedInitScript'] .= $itemId;
		break;

		case 'timepicker':
			//if lookup have value
			if($lookupRsCount)
			{
				$default = $lookupRs[0][$lookupRsKeys[0]];
				$defaultSplit = explode(':',$default);

			}//eof if

			//split to time components
			if($default)
				$defaultSplit = explode(':',$default);

			$returnValue .= '<select id="timepicker_h_'.$name.'" style="width:50px;" class="inputList" title="HH" onchange="collectTimepickerValues(this,\''.$name.'\')">';
			$returnValue .= '<option>&nbsp;</option>';

			//if 12 hours
			//if(in_array('12_hrs',$addAttrib))
			if(TIMEPICKER_FORMAT == 12)
			{
				for($x=1; $x <= 12; $x++)
				{
					$returnValue .= '<option ';

					if($defaultSplit[0] == $x)
						$returnValue .= ' selected ';

					$returnValue .= '>'.str_pad($x,2,'0',STR_PAD_LEFT).'</option>';
				}
			}

			//if 24 hours
			//if(in_array('24_hrs',$addAttrib))
			if(TIMEPICKER_FORMAT == 24)
			{
				for($x=0; $x < 24; $x++)
				{
					$returnValue .= '<option ';

					if($defaultSplit[0] == $x)
						$returnValue .= ' selected ';

					$returnValue .= '>'.str_pad($x,2,'0',STR_PAD_LEFT).'</option>';
				}
			}

			$returnValue .= '</select>';

			//minutes
			$returnValue .= ' :<select id="timepicker_m_'.$name.'" style="width:50px;margin-left:5px;" class="inputList"  title="MM" onchange="collectTimepickerValues(this,\''.$name.'\')"><option>&nbsp;</option>';

			for($x=0; $x < 60; $x++)
			{
				$returnValue .= '<option ';

				if($defaultSplit[1] == $x)
					$returnValue .= ' selected ';

				$returnValue .= '>'.str_pad($x,2,'0',STR_PAD_LEFT).'</option>';
			}

			$returnValue .= '</select>';

			//if(in_array('show_secs',$addAttrib))
			if(TIMEPICKER_SHOW_SECS)
			{
				//secs
				$returnValue .= ' :<select id="timepicker_s_'.$name.'" style="width:50px;margin-left:5px;" class="inputList"  title="SS" onchange="collectTimepickerValues(this,\''.$name.'\')"><option>&nbsp;</option>';

				for($x=0; $x < 60; $x++)
				{
					$returnValue .= '<option ';

					if($defaultSplit[2] == $x)
						$returnValue .= ' selected ';

					$returnValue .= '>'.str_pad($x,2,'0',STR_PAD_LEFT).'</option>';
				}
			}

			/*
			//if(in_array('12_hrs',$addAttrib))
			if(TIMEPICKER_SHOW_AMPM)
			{
				$returnValue .= '<select  id="timepicker_ampm_'.$name.'" style="width:55px;margin-left:5px;" class="inputList" title="AM/PM" onchange="collectTimepickerValues(this,\''.$name.'\')">';
				$returnValue .= '<option>&nbsp;</option>';
				$returnValue .= '<option>AM</option>';
				$returnValue .= '<option>PM</option>';
				$returnValue .= '</select>';
			}
			*/

			$returnValue .= '<input name="'.$name.'" id="'.$name.'" type="hidden" class="timepicker_value" value="'.$default.'" '.$js.' />';

		break;

		case 'url':
			//replace to comply html url <a href>
			$default = str_replace('\\','',$default);
			$default = str_replace('"',"'",$default);

			//if no title
			if(!$title)
				$title='Papar';

			//if lookup have value
			if($lookupRsCount)
			{
				//loop on number of record
				for($x=0; $x < $lookupRsCount; $x++)
				{
					$tempUrl = '';	//initial value

					//if lookup have FLC_NAME
					if($lookupRs[$x]['FLC_NAME']!='')
						$title = $lookupRs[$x]['FLC_NAME'];

					//if lookup hav FLC_ID
					if($lookupRs[$x]['FLC_ID']!='')
						$default = $lookupRs[$x]['FLC_ID'];

					//if menu security enabled
					if(MENU_URL_SECURITY)
					{
						//if using index.php
						if(strtolower(substr($default,0,10)) == 'index.php?')
							$default = 'index.php?a='.flc_url_encode(substr($default,10));
					}//eof if

					$tempUrl .= '<a href="'.$default.'" '.$js.' title="'.$hints.'">'.$title.'</a> ';

					//loop on lookup column
					for($y=0; $y < $lookupRsKeysCount; $y++)
					{
						if($lookupRsKeys[$y] != 'FLC_NAME' && $lookupRsKeys[$y] != 'FLC_ID')
						{
							//get parameter
							$keyName = $lookupRsKeys[$y];

							//append GET element
							$tempUrl = appendUrl($tempUrl,strtolower($keyName).'='.$lookupRs[$x][$lookupRsKeys[$y]]);
						}
					}//eof for column

					//if lookup return more than 1 result
					if($lookupRsCount>$x)
						$tempValue .= $tempUrl;

					if($lookupRsCount>1)
						$tempValue .= '<br>';
				}  //eof for row
			}//eof hav lookup
			else
			{
				//if menu security enabled
				if(MENU_URL_SECURITY)
				{
					//if using index.php
					if(strtolower(substr($default,0,10)) == 'index.php?')
						$default = 'index.php?a='.flc_url_encode(substr($default,10));
				}//eof if

				$tempValue = '<a href="'.$default.'" '.$js.' title="'.$hints.'" >'.$title.'</a>';
			}//eof else

			//value to return
			$returnValue = $tempValue;
		break;
	}//eof switch

	//if have pre-set or trigger onload js
	if($onload || $getJsTrigger['onload'])
		$returnValue .= '<script>'.$onload.$getJsTrigger['onload'].'</script>';

	return $returnValue;
}//eof function

//function to convert input into array items
function convertInputIntoArray($type,$input,$row)
{
	//switch the input type
	switch($type)
	{
		//collection of array able type
		case 'radio':
			$nameRow=$row;
		case 'ajax_dropdown':
		case 'auto_sugg':
		case 'js_cascade_dropdown':
		case 'checkbox':
		case 'color_picker':
		case 'date':
		case 'dropdown':
		case 'file':
		case 'hidden':
		//case 'label':
		case 'label_with_hidden':
		case 'listbox':
		case 'lov':
		case 'password':
		case 'plugin':
		case 'text':
		case 'textarea':
		case 'text_editor':
		case 'timepicker':
			$idRow=$row;

			//if input have name
			if(strpos($input, ' name="') !== false)
			{
				$charStart = ' name="';
				$charEnd = '"';
				$charStartLength = strlen($charStart);
				$offset = strpos($input, $charStart);

				if($type == 'radio')
				{
					//find number of radio button occurences in a radio group
					$occurCount = substr_count($input, 'type="radio"');

					$ending = strpos($input, $charEnd, $offset + $charStartLength);
					$htmlName = substr($input, $offset, $ending-$offset);

					for($x=0; $x < $occurCount; $x++)
						$input = str_replace_nth($htmlName, $htmlName.'['.$row.']', $input, $x);
				}
				else
				{
					//loop while have id
					do
					{
						//position of name opening and closing tag
						$position_1 = $offset;
						$position_2 = strpos($input, $charEnd, $offset + $charStartLength);

						//new id
						$inputName = substr($input,$position_1+$charStartLength,$position_2-$position_1-$charStartLength);

						//if not already in array format
						if(strpos($inputName,'[]') === false)
						{
							//replace with converted id (for array)
							$input = str_replace(' name="'.$inputName.'"',' name="'.$inputName.'['.$nameRow.']"',$input);

						}//eof if
					}
					while($offset = strpos($input, $charStart, $offset + $charStartLength));
				}
			}//eof if

			//if input have id
			if(strpos($input, ' id="') !== false)
			{
				$charStart = ' id="';
				$charEnd = '"';
				$charStartLength = strlen($charStart);
				$offset = strpos($input, $charStart);

				if($type == 'radio')
				{
					//find number of radio button occurences in a radio group
					$occurCount = substr_count($input, 'type="radio"');

					$ending = strpos($input, $charEnd, $offset + $charStartLength);
					$htmlName = substr($input, $offset, $ending-$offset);

					for($x=0; $x < $occurCount; $x++)
						$input = str_replace_nth($htmlName, $htmlName.'_'.$row.'_'.$x, $input, $x);
				}
				else
				{
					//loop while have id
					do
					{
						//position of id opening and closing tag
						$position_1 = $offset;
						$position_2 = strpos($input, $charEnd, $offset + $charStartLength);

						//new id
						$inputId = substr($input,$position_1+$charStartLength,$position_2-$position_1-$charStartLength);

						//replace with converted id (for array)
						$input = str_replace(' id="'.$inputId.'"',' id="'.$inputId.'_'.$idRow.'"',$input);
					}
					while($offset = strpos($input, $charStart, $offset + $charStartLength));
				}
			}//eof if
		break;
	}//eof switch

	return $input;
}//eof function

//to append parameter into href
function appendUrl($input,$parameter)
{
	//get the name of input
	$begin=explode('href="',$input);
	$end=explode('" ',$begin[1]);
	$tempInput=$end[0];		//value of href

	//append parameter into URL
	$result=str_replace($tempInput,$tempInput.'&'.$parameter,$input);

	return $result;
}//eof function
?>
<!-- todo - debug mode -->

