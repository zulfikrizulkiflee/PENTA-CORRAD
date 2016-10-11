<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" class="tableContentReport" <?php echo $js[$x];?>>
<?php
//translation
$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],4,$componentArr[$x]['COMPONENTID'],'COMPONENTTITLE');
if($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'COMPONENTTITLE')
	$componentArr[$x]['COMPONENTTITLE'] = $tr8nStr[0]['TRANS_TEXT'];
//end translation

//==================== DECLARATION =======================
$tg = new TableGrid('99%',0,0,0);						//set object for table class (width,border,celspacing,cellpadding)

//set attribute of table
$tg->setAttribute('class','tableContent flcReport');					//set id

//if component search or collapsible is enabled
if($componentArr[$x]['COMPONENTSEARCH'] == 1 || $componentArr[$x]['COMPONENTCOLLAPSE'] == 1)
{
	$tg->setHeader('<table id="comp_header_additional_'.$componentArr[$x]['COMPONENTID'].'" border="0" cellspacing="0" cellpadding="0" style="width:100%; padding:0px;margin:0px;"><tr><td style="border:none;padding:0px;">'.convertDBSafeToQuery($componentArr[$x]['COMPONENTTITLE']).'</td>');
	$tg->appendToHeader('<td style="text-align:right; border:none;padding:0px;">');

	if($componentArr[$x]['COMPONENTSEARCH'] == 1)
		$tg->appendToHeader('<input placeholder="search here" onkeyup="flc_comp_search(this)" class="inputInput" style="font-size:11px;padding:2px;" type="text" />');			//set component search

	if($componentArr[$x]['COMPONENTCOLLAPSE'] == 1)
	{
		$tg->appendToHeader('<label style="cursor:pointer; padding:0 0 0 5px; margin:0px;" id="collapseComponentLabel_'.$componentArr[$x]['COMPONENTID'].'" title="Collapse Component" onclick="collapseComponentReport(this)">');
		$tg->appendToHeader('<img style="vertical-align:middle; padding:0px; margin:0px;" src="img/arrow_up.gif" />');
		$tg->appendToHeader('</label>');
	}

	//$tg->appendToHeader('<select><option></option></select>');
	$tg->appendToHeader('</td></tr></table>');
}
else
	$tg->setHeader(convertDBSafeToQuery($componentArr[$x]['COMPONENTTITLE']));	//set header

$tg->setKeysStatus(true);									//use of keys (column header)
$tg->setKeysAttribute('class','listingHead');				//set class
$tg->setRunningStatus(true);								//set status of running number
$tg->setRunningKeys('No');									//key / label for running number

//set attribute of column in table
$col = new Column();										//set object for column
$col->setAttribute('class','listingContent');				//set attribute for table
$tg->setColumn($col);										//insert/set class column into table

//clear previous data in array
if(isset($data))unset($data);								//unset all data
if(isset($keysLabel))unset($keysLabel);						//unset aggregation
if(isset($aggArr))unset($aggArr);							//unset aggregation
if(isset($hiddenData))unset($hiddenData);					//unset hidden data

//initialize variable
$dataCount=$componentArr[$x]['COMPONENTABULARDEFAULTROWNO'];	//set count of data as 0
//================== END DECLARATION =====================

/* TEMPORARILY DISABLED BY FAIS BECAUSE NOT SUPPORTED ON ALL DB / ACROSS ALL DB VERSION (EVEN MYSQL)
//temp for sybase limitation (constraint with derived query)
if(DBMS_NAME!='sybase')
{
	//append sql for filtering
	if($sqlToAppend)
	{
		$componentArr[$x]['COMPONENTTYPEQUERY'] = 'select * from ('.$componentArr[$x]['COMPONENTTYPEQUERY'].') a '.$sqlToAppend;

		if(!$componentArr[$x]['COMPONENTQUERYUNLIMITED'])
		{
			if($componentArr[$x]['COMPONENTQUERYMAXFETCH'] > 0)
				$componentArr[$x]['COMPONENTTYPEQUERY'] .= $mySQL->limit($componentArr[$x]['COMPONENTQUERYMAXFETCH'],' and ');
			else
				$componentArr[$x]['COMPONENTTYPEQUERY'] .= $mySQL->limit(DEFAULT_QUERY_LIMIT,' and ');
		}
	}//eof if
	else
	{
		if(!$componentArr[$x]['COMPONENTQUERYUNLIMITED'])
		{
			if($componentArr[$x]['COMPONENTQUERYMAXFETCH'] > 0)
				$componentArr[$x]['COMPONENTTYPEQUERY'] = "select * from (".$componentArr[$x]['COMPONENTTYPEQUERY'].") a  ".$mySQL->limit($componentArr[$x]['COMPONENTQUERYMAXFETCH'],' where ');
			else
				$componentArr[$x]['COMPONENTTYPEQUERY'] = "select * from (".$componentArr[$x]['COMPONENTTYPEQUERY'].") a  ".$mySQL->limit(DEFAULT_QUERY_LIMIT,' where ');			//limit query result
		}
	}//eof else
}//eof else
*/

//if have query
if($componentArr[$x]['COMPONENTTYPEQUERY'])
{
	//convert query into dbsafe
	$theQuery = convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTTYPEQUERY']));
	$id = $componentArr[$x]['COMPONENTQUERYDB'];

	if($theQuery)
	{
		//fetch the query
		$data=$myQueryArr['myQuery'.$id]->query($theQuery,'SELECT','NAME');
		$dataCount=count($data);	//count row of data
	}
	else
		$dataCount = 0;
}//eof if have query
else
	$dataCount=0;

if($data[0] != '')
{
	//set keys of array
	$dataKeys = array_keys($data[0]);
	$dataKeysCount = count($dataKeys);		//count the keys

	//set keys as label for tablegrid
	$keysLabel=$dataKeys;
}
else
{
	unset($dataKeys);
	unset($keysLabel);
	$dataKeysCount = 0;
}//eof else

$useLabel=false;	//temporary

//=====this part for footer
//clear data
unset($tempArr);

//loop on count of item
for($a=0; $a < $countItem; $a++)
{
	//if item type hidden, skip
	if($itemsArr[$a]['ITEMTYPE'] == 'hidden')
		continue;

	//if item have aggregation
	if($itemsArr[$a]['ITEMAGGREGATECOLUMN'] || $itemsArr[$a]['ITEMAGGREGATECOLUMNLABEL'])
	{
		//loop on data count
		for($ax=0;$ax<$dataCount;$ax++)
		{
			$tempArr[$a][$ax]=$data[$ax][$dataKeys[$a]];					//get value into temp array

			//if item have comma
			if(strpos($tempArr[$a][$ax],',') !== false)
			{
				$tempArr[$a][$ax]=str_replace(',','',$tempArr[$a][$ax]);		//trim comma ','
				$useComma=true;	//set comma value true for thousand
			}//eof if
		}//eof for

		//if array
		if(is_array($tempArr))
		{
			//switch by aggregate type
			switch(strtoupper($itemsArr[$a]['ITEMAGGREGATECOLUMN']))
			{
				case 'SUM':	$aggArr[$a]=array_sum($tempArr[$a]);
					break;
				case 'COUNT': //$aggArr[$a]=count($tempArr[$a]);
							$aggArr[$a]=0;	//initial value
							$tempArrCount=count($tempArr[$a]);	//count row

							//loop on row
							for($y=0;$y<$tempArrCount;$y++)
								if($tempArr[$a][$y]!='')	//if have value
									$aggArr[$a]++;

					break;
				case 'AVG': $aggArr[$a]=array_sum($tempArr[$a])/count($tempArr[$a]);
					break;
				case 'MAX': $aggArr[$a]=max($tempArr[$a]);
					break;
				case 'MIN': $aggArr[$a]=min($tempArr[$a]);
					break;
			}//eof switch

			//============================start number format converter
			//if float number and use comma for thousand
			if(is_float($aggArr[$a]) && $useComma)
				$aggArr[$a] = number_format($aggArr[$a], 2, '.', ',');

			//if float number
			else if(is_float($aggArr[$a]))
				$aggArr[$a] = sprintf("%.2f",$aggArr[$a]);

			//if use comma for thousand
			else if($useComma)
				$aggArr[$a] = number_format($aggArr[$a], '', '', ',');
			//===============================end number format converter

			//if aggregation have label
			if($itemsArr[$a]['ITEMAGGREGATECOLUMNLABEL'])
				$aggLabel[$a]='<b>'.$itemsArr[$a]['ITEMAGGREGATECOLUMNLABEL'].'</b><br>';

			//aggregation
			$aggFieldName[$a] = $itemsArr[$a]['ITEMNAME'].'_'.$itemsArr[$a]['ITEMAGGREGATECOLUMN'];
			$aggArr[$a] = $aggLabel[$a].'<input class="inputInput" name="'.$aggFieldName[$a].'" id="'.$aggFieldName[$a].'" type="text" value="'.$aggArr[$a].'" style="background-color:#F2F2F2; width:93%; text-align:'.$itemsArr[$a]['ITEMTEXTALIGN'].';" />';

			//javascript for item (onblur)
			$itemsArr[$a]['ITEMJAVASCRIPTEVENT']='1';		//event code for 'onblur'
			$itemsArr[$a]['ITEMJAVASCRIPT']='aggregateColumn(\''.$itemsArr[$a]['ITEMAGGREGATECOLUMN'].'\',this,\''.$aggFieldName[$a].'\');'.$itemsArr[$a]["ITEMJAVASCRIPT"];

			$btnDelRowJs.='aggregateColumn(\''.$itemsArr[$a]['ITEMAGGREGATECOLUMN'].'\',\''.$inputFieldName[$a].'\',\''.$aggFieldName[$a].'\');';

			$showFooter=true;	//set footer is true
		}//eof if array
	}//eof if have aggregation

	//if item have check all
	else if($itemsArr[$a]['ITEMCHECKALL'])
	{
		//name of checkbox (in array form)
		$chkBoxName = $itemsArr[$a]['ITEMNAME'];

		//checkbox to trigger check all (same name but have 'all_' in front)
		$aggArr[$a]='<input name="all_'.$chkBoxName.'" id="all_'.$chkBoxName.'" type="checkbox" value="" onclick="itemCheckAll(\''.$chkBoxName.'\')" />';

		?>
		<script language="javascript">
		//to set all checkbox as check or not
		function itemCheckAll(itemname)
		{
			//if have array of items by name
			if(document.getElementsByName(itemname).length>0)
			{
				//get array of items by name
				var itemArr=document.getElementsByName(itemname+'[]');

				//if triggered as check
				if(document.getElementById('all_'+itemname).checked==true)
					checkFlag=true		//set as check
				else
					checkFlag=false		//set as uncheck

				var itemArrLen = itemArr.length;

				//loop on array of checkbox with same name
				for(x=0; x<itemArrLen; x++)
					itemArr[x].checked=checkFlag
			}
		}//eof function
		</script>

		<?php
		$showFooter = true;	//set footer is true
	}//eof if have check all

	else
		$aggArr[$a] = '';
}//eof for
//=====eof part for footer

//if select && addrow
if($componentArr[$x]['COMPONENTADDROW']&&$countItem>0)
	$dataCount++;

//if have data
if($dataCount>0)
{
	//loop on row of items
	for($a=0; $a<$countItem; $a++)
	{
		//get translation
		$tr8nStr_title = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$a]['ITEMID'],'ITEMTITLE');
		$tr8nStr_notes = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$a]['ITEMID'],'ITEMNOTES');

		if($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMTITLE')
			$itemsArr[$a]['ITEMTITLE'] = $tr8nStr_title[0]['TRANS_TEXT'];
		if($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMNOTES')
			$itemsArr[$a]['ITEMNOTES'] = $tr8nStr_title[0]['TRANS_TEXT'];
		//end translation

		//if item type is not label
		switch($itemsArr[$a]['ITEMTYPE'])
		{
			//for those items
			case 'radio':
			case 'color_picker':
			case 'date':
			case 'dropdown':
			case 'file':
			case 'hidden':
			case 'label':
			case 'label_with_hidden':
			case 'listbox':
			case 'lov':
			case 'password':
			case 'text':
			case 'textarea':
			case 'text_editor':
			case 'timepicker':

				//if data keys not exist
				if(!isset($dataKeys[$a]))
					$dataKeys[$a]=$itemsArr[$a]['ITEMTITLE'];	//set the data keys

				//loop on data row
				for($ax=0;$ax<$dataCount;$ax++)
				{
					//set result data as default value
					$itemsArr[$a]['ITEMDEFAULTVALUE'] = $data[$ax][$dataKeys[$a]];

					//build input, convert into array and set as temp variable
					$temp = convertInputIntoArray($itemsArr[$a]['ITEMTYPE'], buildInput($myQueryArr, $itemsArr[$a], $ax), $ax);

					//if item type not hidden
					if($itemsArr[$a]['ITEMTYPE'] != 'hidden')
					{
						if(!$itemsArr[$a]['ITEMAPPENDTOBEFORE'])
						{
							//insert temp data into data array
							$data[$ax][$dataKeys[$a]] = $temp.' '.$itemsArr[$a]['ITEMNOTES'];
						}//eof if
						else
						{
							//append into data array before
							$data[$ax][$dataKeys[$a-1]] .=  ' '.$dataKeys[$a].' '.'<span class="theAppendedItem">'.$temp.'</span> '.$itemsArr[$a]['ITEMNOTES'];
						}//eof else
					}//eof if
					else
					{
						//if 1st hidden input
						if(!$hiddenData[$ax])
							$hiddenData[$ax] = $temp;	//append temp data into previous data array
						else
							$hiddenData[$ax] .= $temp;	//insert temp data into next data array

						//unset involved array
						unset($data[$ax][$dataKeys[$a]]);		//delete array for current row (hidden)
						unset($keysLabel[$a]);					//delete keys for current item
					}//eof else
				}//eof for

				//if item type not hidden
				if($itemsArr[$a]['ITEMTYPE'] != 'hidden')
				{
					//enable label
					$useLabel = true;		//temporary -> set to use given label
					$keysLabel[$a]=$itemsArr[$a]['ITEMTITLE'];	//use item name as key
				}//eof if
			break;

			//others item
			default:
				//temp for sybase limitation (constraint with derived query)
				if(DBMS_NAME!='sybase')
				{
					//itemlookup not null
					if($itemsArr[$a]['ITEMLOOKUP'] != '')
					{
						if(!$sqlToAppend)
						{
							if(!$itemsArr[$a]['ITEMLOOKUPUNLIMITED'])
								$itemsArr[$a]["ITEMLOOKUP"] = 'select * from ('.$itemsArr[$a]['ITEMLOOKUP'].') a '.$mySQL->limit(DEFAULT_QUERY_LIMIT,' where ');
						}//eof if
						else
						{
							if(!$itemsArr[$a]['ITEMLOOKUPUNLIMITED'])
								$itemsArr[$a]['ITEMLOOKUP'] = 'select * from ('.$itemsArr[$a]['ITEMLOOKUP'].') a '.$sqlToAppend.$mySQL->limit(DEFAULT_QUERY_LIMIT,' and ');
							else
								$itemsArr[$a]['ITEMLOOKUP'] = 'select * from ('.$itemsArr[$a]['ITEMLOOKUP'].') a '.$sqlToAppend;
						}//eof if
					}//eof if
				}//eof if

				//build input
				$temp = buildInput($myQueryArr, $itemsArr[$a], $a+1);
				//$temp = convertInputIntoArray($itemsArr[$a]['ITEMTYPE'], buildInput($myQueryArr, $itemsArr[$a], $a+1), $ax);

				//explode
				$tempInput=explode('<br>',$temp);

				//count
				$tempInputCount=count($tempInput);

				//loop on data row
				for($ax=0; $ax<$dataCount; $ax++)
				{
					if($tempInputCount>1)
						$data[$ax][$itemsArr[$a]['ITEMTITLE']] = convertInputIntoArray($itemsArr[$a]['ITEMTYPE'], $tempInput[$ax], $ax);
					else
						$data[$ax][$itemsArr[$a]['ITEMTITLE']] = convertInputIntoArray($itemsArr[$a]['ITEMTYPE'], $tempInput[0], $ax);
				}//eof for datacount

				//set label
				if($a<$dataKeysCount)
					$keysLabel[++$dataKeysCount]=$itemsArr[$a]['ITEMTITLE'];		//add item name as key
				else
					$keysLabel[$a]=$itemsArr[$a]['ITEMTITLE'];		//add item name as key
			break;
		}//eof switch
	}//eof loop on row of items

	//loop on count of data
	for($ax=0; $ax<$dataCount; $ax++)
	{
		//if have hidden data
		if(is_array($hiddenData) && is_array($data))
		{
			$tempKeys = array_keys($data[$ax]);
			$data[$ax][$tempKeys[0]] .= $hiddenData[$ax];
		}//eof if
	}//eof for

}//eof if have data

//check if keys / label is create
if($useLabel)
{
	$keysLabelCount=count($keysLabel);	//count keys/header label
	$tempCountLabel=0;			//set initial as 0 (count index of label)

	//do while not reach end of array
	do
	{
		//put label that will submit current form with index to be ordered when submitted
		$keysLabel[key($keysLabel)]='<label style="cursor:pointer; text-decoration:underline" onclick="document.getElementById(\''.$componentArr[$x]['COMPONENTNAME'].'_order\').value=\''.($tempCountLabel++).'\';this.form.submit();">'.current($keysLabel).'</label>';
	}while(next($keysLabel));

	$tg->setKeys($keysLabel);		//set keys in tablegrid
}//eof if label

//count data
$headerCount=count($data[0]);	//header

if($headerCount>0)
	$tg->setHeaderAttribute('colspan',$headerCount);	//set colspan for header

//if have data
if($dataCount>0)
{
	//if component add row is true or component preprocess is not select
	if($componentArr[$x]['COMPONENTADDROW']/* && $componentArr[$x]['COMPONENTPREPROCESS'] != 'select'*/)
	{
		$tg->setAddRowValue(TR8N_TABULAR_ADD_ROW);
		$tg->setDelRowValue(TR8N_TABULAR_DEL_ROW);

		//status and type
		if($componentArr[$x]['COMPONENTADDROW'])
			{$tg->setAddRowStatus(true);$tg->setAddRowType('ajax');}
		if($componentArr[$x]['COMPONENTDELETEROW'])
			{$tg->setDelRowStatus(true);}

		//id
		$tg->setAddRowId('addrow_'.$componentArr[$x]['COMPONENTID']);
		$tg->setDelRowId('deleterow_'.$componentArr[$x]['COMPONENTID']);

		//enable/disable
		$tg->setAddRowDisabled($componentArr[$x]['COMPONENTADDROWDISABLED']);
		$tg->setDelRowDisabled($componentArr[$x]['COMPONENTDELETEROWDISABLED']);

		//add javascript for add row
		if($componentArr[$x]['COMPONENTADDROWJAVASCRIPT'])
			$tg->addAddRowJs('onclick', convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTADDROWJAVASCRIPT'])));

		//add javascipt for delete row
		if($componentArr[$x]['COMPONENTDELETEROWJAVASCRIPT'])
			$tg->addDelRowJs('onclick', convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTDELETEROWJAVASCRIPT'])));
	}

	if(!$componentArr[$x]['COMPONENTADDROW'])
		$tg->setLimit($componentArr[$x]['COMPONENTABULARDEFAULTROWNO']);			//set limit

	//if footer true
	if($showFooter)
	{
		//countfooter
		$footerCount = count($aggArr);

		//check if number of footer not same as number of header
		if($footerCount<$headerCount)
			for($y=$footerCount; $y<$headerCount; $y++)
				$aggArr[$y]='';	//put empty spaces for addition

		$tg->setFooterAttribute('class','listingContent');		//set footer attribute
		$tg->setFooter($aggArr);								//set the data of footer

		$showFooter=false;		//re-set as false
	}//eof if

	//sorting
	if($_POST[$componentArr[$x]['COMPONENTNAME'].'_order']!='')
	{
		$tg->sortIndex(true,$_POST[$componentArr[$x]['COMPONENTNAME'].'_order']);
	}//eof if

	//show report/ tablegrid
	$tg->setAddRowClass('inputButton');
	$tg->setDelRowClass('inputButton');
	$tg->setTableGridData($data);
}//eof if
else
{
	$tg->setTableGridData(TR8N_TABULAR_NO_RECORD);
}//eof else

$tg->showTableGrid();
?>

<?php
//if theres page control associated with the component
if($controlArrCount>0)
{
	for($y=0;$y<$controlArrCount;$y++)
	{
		$controlid[] = $controlArr[$y][0];

		if(!$isPdfOrCsvButtonAppear)
		{
			$ctrltype = $myQuery->query("select CONTROLTYPE from FLC_PAGE_CONTROL where CONTROLID=".$controlArr[$y][0],'SELECT','INDEX');
			$ctrltype = $ctrltype[0][0];

			if($ctrltype=='30' || $ctrltype=='31') $isPdfOrCsvButtonAppear=true;
		}
	}

?>
<table class="tableContent flcReportButton" style="border-top:none;" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td class="contentButtonFooter" style="text-align:left;border-right:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidLeft);?></td>
	<td class="contentButtonFooter" style="text-align:center;border-left:none;border-right:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidCenter);?></td>
	<td class="contentButtonFooter" style="text-align:right;border-left:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidRight);?></td>
  </tr>
</table>
<?php }?>
<br>
</div>
<?php
if($componentArr[$x]['COMPONENTCOLLAPSE'] == '1' && $componentArr[$x]['COMPONENTCOLLAPSEDEFAULT'] == '0') { ?>
<script>collapseComponentReport(jQuery('#collapseComponentLabel_<?php echo $componentArr[$x]['COMPONENTID'] ?>'),'onload_default');</script>
<?php } ?>
<!--used for sorting-->
<input id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>_order" name="<?php echo $componentArr[$x]['COMPONENTNAME'];?>_order" type="hidden" />
<?php }?>
