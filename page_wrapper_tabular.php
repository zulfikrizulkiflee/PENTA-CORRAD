<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" <?php echo $js[$x];?>>
<?php
//translation
$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],4,$componentArr[$x]['COMPONENTID'],'COMPONENTTITLE');
if($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'COMPONENTTITLE')
	$componentArr[$x]['COMPONENTTITLE'] = $tr8nStr[0]['TRANS_TEXT'];
//end translation

//==================== DECLARATION =======================
$tg = new TableGrid('99%',0,0,0);						//set object for table class (width,border,celspacing,cellpadding)

//set attribute of table
$tg->setAttribute('class','tableContent flcTabular');					//set id

//if component search or collapsible is enabled
if($componentArr[$x]['COMPONENTSEARCH'] == 1 || $componentArr[$x]['COMPONENTCOLLAPSE'] == 1)
{
	$tg->setHeader('<table id="comp_header_additional_'.$componentArr[$x]['COMPONENTID'].'" border="0" cellspacing="0" cellpadding="0" style="width:100%; padding:0px;margin:0px;"><tr><td style="border:none;padding:0px;">'.convertDBSafeToQuery($componentArr[$x]['COMPONENTTITLE']).'</td>');
	$tg->appendToHeader('<td style="text-align:right; border:none;padding:0px;">');

	//if($componentArr[$x]['COMPONENTSEARCH'] == 1)
	//	$tg->appendToHeader('<input placeholder="search here" onkeyup="flc_comp_search(this)" class="inputInput" style="font-size:11px;padding:2px;" type="text" />');			//set component search

	if($componentArr[$x]['COMPONENTCOLLAPSE'] == 1)
	{
		$tg->appendToHeader('<label style="cursor:pointer; padding:0 0 0 5px; margin:0px;" id="collapseComponentLabel_'.$componentArr[$x]['COMPONENTID'].'" title="Collapse Component" onclick="collapseComponentReport(this)">');
		$tg->appendToHeader('<img style="vertical-align:middle; padding:0px; margin:0px;" src="img/arrow_up.gif" />');
		$tg->appendToHeader('</label>');
	}

	$tg->appendToHeader('</td></tr></table>');
}
else
	$tg->setHeader(convertDBSafeToQuery($componentArr[$x]['COMPONENTTITLE']));	//set header

$tg->setKeysStatus(true);								//use of keys (column header)
$tg->setKeysAttribute('class','listingHead');			//set class
$tg->setRunningStatus(true);							//set status of running number
$tg->setRunningKeys('No');								//key / label for running number

//set attribute of column in table
$col = new Column();									//set object for column
$col->setAttribute('class','listingContent');			//set attribute for table
$tg->setColumn($col);									//insert/set class column into table

//if component add row is true or component preprocess is not select
if($componentArr[$x]['COMPONENTADDROW']/* && $componentArr[$x]['COMPONENTPREPROCESS'] != 'select'*/)
{
	//add row button flag
	if($componentArr[$x]['COMPONENTADDROW'])
	{
		$tg->setAddRowStatus(true);
		$tg->setAddRowType('ajax');
		$tg->setAddRowClass('inputButton');
		$tg->setAddRowId('addrow_'.$componentArr[$x]['COMPONENTID']);
		$tg->setAddRowValue(trim(TR8N_TABULAR_ADD_ROW));
		$tg->setAddRowDisabled($componentArr[$x]['COMPONENTADDROWDISABLED']);

		//javascript
		if($componentArr[$x]['COMPONENTADDROWJAVASCRIPT'])
			$tg->addAddRowJs('onclick', convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTADDROWJAVASCRIPT'])));
	}//eof if

	//delete row button flag
	if($componentArr[$x]['COMPONENTDELETEROW'])
	{
		$tg->setDelRowStatus(true);
		$tg->setDelRowClass('inputButton');
		$tg->setDelRowId('deleterow_'.$componentArr[$x]['COMPONENTID']);
		$tg->setDelRowValue(trim(TR8N_TABULAR_DEL_ROW));
		$tg->setDelRowDisabled($componentArr[$x]['COMPONENTDELETEROWDISABLED']);
		$tg->addDelRowJs('onclick', $btnDelRowJs);

		//javascript
		if($componentArr[$x]['COMPONENTDELETEROWJAVASCRIPT'])
			$tg->addDelRowJs('onclick', convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTDELETEROWJAVASCRIPT'])));
	}//eof if
}//eof if

//get default row number
if($componentArr[$x]['COMPONENTABULARDEFAULTROWNO']=='')
	$componentArr[$x]['COMPONENTABULARDEFAULTROWNO']=5;		//set  default as 5

//if preprocess is select
if($componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
{
	//count row of data
	$getDataRsCount = count($getDataRs);

	//if no data
	if($getDataRsCount == 0)
		$componentArr[$x]['COMPONENTABULARDEFAULTROWNO']=0;		//set row as0 if no data

	//if data rows is less than default row number
	else if($getDataRsCount>$componentArr[$x]['COMPONENTABULARDEFAULTROWNO'])
		$componentArr[$x]['COMPONENTABULARDEFAULTROWNO'] = $getDataRsCount;		//set row number based of query rows
}//eof if preprocess is select

//number of data
$dataCount = $componentArr[$x]['COMPONENTABULARDEFAULTROWNO'];

//if select && addrow
if($componentArr[$x]['COMPONENTADDROW'] && $componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
	$dataCount++;

//clear previous data in array
if(isset($data))unset($data);							//unset all data
if(isset($keysLabel))unset($keysLabel);					//unset aggregation
if(isset($aggArr))unset($aggArr);						//unset aggregation
if(isset($hiddenData))unset($hiddenData);				//unset hidden data
//================== END DECLARATION =====================

//=====this part for footer
//loop on count of item
for($a=0; $a < $countItem; $a++)
{
	//if item type hidden, skip
	if($itemsArr[$a]['ITEMTYPE'] == 'hidden')
		continue;

	//if item have aggregation
	if($itemsArr[$a]['ITEMAGGREGATECOLUMN'] || $itemsArr[$a]['ITEMAGGREGATECOLUMNLABEL'])
	{
		//name of aggregation field (in array form)
		$inputFieldName[$a] = $itemsArr[$a]['ITEMNAME'].'[]';
		$aggFieldName[$a] = $itemsArr[$a]['ITEMNAME'].'_'.$itemsArr[$a]['ITEMAGGREGATECOLUMN'];

		//if aggregation have label
		if($itemsArr[$a]['ITEMAGGREGATECOLUMNLABEL'])
			$aggLabel[$a]='<b>'.$itemsArr[$a]['ITEMAGGREGATECOLUMNLABEL'].'</b><br>';

		$aggArr[$a]=$aggLabel[$a].'<input class="inputInput" name="'.$aggFieldName[$a].'" id="'.$aggFieldName[$a].'" type="text" value="'.$aggArr[$a].'" style="background-color:#F2F2F2; width:93%; text-align:'.$itemsArr[$a]['ITEMTEXTALIGN'].';" />';

		//javascript for item (onblur)
		$itemsArr[$a]['ITEMJAVASCRIPTEVENT']='1';		//event code for 'onblur'
		$itemsArr[$a]['ITEMJAVASCRIPT']='aggregateColumn(\''.$itemsArr[$a]['ITEMAGGREGATECOLUMN'].'\',this,\''.$aggFieldName[$a].'\');'.$itemsArr[$a]['ITEMJAVASCRIPT'];

		$btnDelRowJs.='aggregateColumn(\''.$itemsArr[$a]['ITEMAGGREGATECOLUMN'].'\',\''.$inputFieldName[$a].'\',\''.$aggFieldName[$a].'\');';

		$showFooter=true;	//set footer is true
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
			if(document.getElementsByName(itemname+'[]').length>0)
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
				for(x=0; x < itemArrLen; x++)
					itemArr[x].checked = checkFlag
			}
		}//eof function
		</script>

		<?php
		$showFooter=true;	//set footer is true
	}//eof if have check all

	else
		$aggArr[$a]='';
}//eof for
//=====eof part for footer

//loop on number of default row
for($ff=0; $ff < $dataCount; $ff++)
{
	//loop on number of items
	for($a=0; $a < $countItem; $a++)
	{
		//get translation
		$tr8nStr_title = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$a]['ITEMID'],'ITEMTITLE');
		$tr8nStr_notes = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$a]['ITEMID'],'ITEMNOTES');

		if($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMTITLE')
			$itemsArr[$a]['ITEMTITLE'] = $tr8nStr_title[0]['TRANS_TEXT'];
		if($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMNOTES')
			$itemsArr[$a]['ITEMNOTES'] = $tr8nStr_title[0]['TRANS_TEXT'];
		//end translation

		//if item default value is not set, check if bind to database columns and pre process is select
		if($itemsArr[$a]['ITEMDEFAULTVALUE'] == '' && $componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
		{
			//for all items
			for($g=0; $g < $countGetMappedItem; $g++)
			{
				//if input name is in getMappedItem array, get mapping id
				if($getMappedItem[$g]['COMPONENTIDNAME'] == $itemsArr[$a]['ITEMNAME'])
				{
					//for column to find, find value in getDataRs
					$columnValueToFind = $getMappedItem[$g]['MAPPINGID'];

					//set the value to default variable
					$itemsArr[$a]['ITEMDEFAULTVALUE'] = strtoupper($getDataRs[$ff][$columnValueToFind]);
				}//end if
			}//end for g
		}//end if

		//build the input, convert input into array type, then put into array as data for tabular
		$temp = convertInputIntoArray($itemsArr[$a]['ITEMTYPE'], buildInput($myQueryArr, $itemsArr[$a], $ff), $ff);

		//if item type not hidden
		if($itemsArr[$a]['ITEMTYPE'] != 'hidden')
		{
			//check append to prev item
			if(!$itemsArr[$a]['ITEMAPPENDTOBEFORE'])
			{
				//set current data header/title
				$dataCurrentHeader = $itemsArr[$a]['ITEMTITLE'];

				//insert temp data into data array
				$data[$ff][$dataCurrentHeader] .= $temp;

				//append notes?
				$data[$ff][$dataCurrentHeader] .= ' '.$itemsArr[$a]['ITEMNOTES'];
			}//eof if
			else
			{
				//append temp data into prev data array
				$data[$ff][$dataCurrentHeader] .=  $itemsArr[$a]['ITEMTITLE'].' '.'<span class="theAppendedItem">'.$temp.'</span>';

				//append notes?
				$data[$ff][$dataCurrentHeader] .= ' '.$itemsArr[$a]['ITEMNOTES'].' ';
			}//eof else
		}//eof if
		else
		{
			//if array for hidden not created yet
			if(!is_array($hiddenData))
				$hiddenData[$ff] = $temp;	//append temp data into previous data array
			else
				$hiddenData[$ff] .= $temp;	//insert temp data into next data array
		}//eof else
	}//eof for loop item
}//eof for loop data row

//loop on count of data
for($ax=0; $ax < $dataCount; $ax++)
{
	//if have hidden data
	if(is_array($hiddenData))
	{
		$tempKeys = array_keys($data[$ax]);
		$data[$ax][$tempKeys[0]] .= $hiddenData[$ax];
	}//eof if
}//eof for

//count data
$colspan=count($data[0]);	//count data column
$tg->setHeaderAttribute('colspan',$colspan);	//set colspan for header

//if hav data
if($dataCount > 0)
{
	//if footer true
	if($showFooter)
	{
		$tg->setFooterAttribute('class','listingContent');		//set footer attribute
		$tg->setFooter($aggArr);								//set the data of footer
	}//eof if

	$tg->setTableGridData($data);		//show tabular with the input
}//eof if
else
{
	$tg->setTableGridData(TR8N_TABULAR_NO_RECORD);
}//eof else

$tg->showTableGrid();
?>

<?php
//if theres page control associated with the component
if($controlArrCount > 0)
{
	//for($y=0;$y<$controlArrCount;$y++)
	//{$controlid[] = $controlArr[$y][0];}
?>

<table class="tableContent flcReportButton" style="border-top:none;" width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
	<td class="contentButtonFooter" style="text-align:left;border-right:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidLeft);?></td>
	<td class="contentButtonFooter" style="text-align:center;border-left:none;border-right:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidCenter);?></td>
	<td class="contentButtonFooter" style="text-align:right;border-left:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidRight);?></td>
  </tr>
</table>
<?php }?>
<br>
</div>

<?php //loop on count of item
for($a=0; $a < $countItem; $a++){
if($inputFieldName[$a] && $aggFieldName[$a]){?>
<script language="javascript">
aggregateColumn('<?php echo $itemsArr[$a]['ITEMAGGREGATECOLUMN'];?>','<?php echo $inputFieldName[$a];?>','<?php echo $aggFieldName[$a];?>')
</script>
<?php }}?>
<?php }?>
<?php
if($componentArr[$x]['COMPONENTCOLLAPSE'] == 1 && $componentArr[$x]['COMPONENTCOLLAPSEDEFAULT'] == 0) { ?>
<script>collapseComponentReport(jQuery('#collapseComponentLabel_<?php echo $componentArr[$x]['COMPONENTID'] ?>'),'onload_default');</script>
<?php } ?>
