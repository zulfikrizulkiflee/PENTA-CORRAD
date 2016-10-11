<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" <?php echo $js[$x];?>>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
	<tr>
		<th colspan="2">
		<?php
			//translation for component
			$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],4,$componentArr[$x]['COMPONENTID'],'COMPONENTTITLE');
			($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'COMPONENTTITLE') ? $compTitle = $tr8nStr[0]['TRANS_TEXT'] : $compTitle = $componentArr[$x]['COMPONENTTITLE'];
			echo convertDBSafeToQuery($compTitle);

			include('show_hidden_inputs.php');

			$compCollapseHTML = '';
			if($componentArr[$x]['COMPONENTCOLLAPSE'] == 1) {
				if($componentArr[$x]['COMPONENTCOLLAPSEDEFAULT'] == 0)
					$compCollapseHTML = 'style="display:none;"';
				?>
				<div style="text-align:right; float:right">
					<label id="collapseComponentLabel_<?php echo $componentArr[$x]['COMPONENTID']; ?>" title="Collapse Component" onclick="collapseComponent(this)" style="cursor:pointer">
					<?php
					if($componentArr[$x]['COMPONENTCOLLAPSEDEFAULT'] == 1)
						echo '<img src="img/arrow_up.gif" />';
					else
						echo '<img src="img/arrow_down.gif" />';
					?>
					</label>
				</div>
			<?php }//end if collapsible ?>
		</th>
	</tr>
	<?php
  	//set append true flag to false, init.
  	$appendTrueFlag = false;

	//append to item number index, set default to zero
	$appendToItemNo = 0;

  	//to find appended item
  	for($b=0; $b<$countItem; $b++)
	{
		//--START TRANSLATION
		//get the translation for component item
		$tr8nStr_title = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$b]['ITEMID'],'ITEMTITLE');
		$tr8nStr_notes = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$b]['ITEMID'],'ITEMNOTES');

		($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMTITLE') ? $itemnamelabel 	= $tr8nStr_title[0]['TRANS_TEXT'] : $itemnamelabel = $itemsArr[$b]['ITEMTITLE'];
		($tr8nStr_notes[0]['TRANS_SOURCE_COLUMN'] == 'ITEMNOTES') ? $itemnoteurl = $tr8nStr_notes[0]['TRANS_TEXT'] : $itemnoteurl = $itemsArr[$b]['ITEMNOTES'];
		//--END TRANSLATION

		if (BUTTON_URL_SECURITY)
			if ($itemsArr[$b]['ITEMNOTES'] != "" ) $itemnoteurl = href_encoding($itemsArr[$b]['ITEMNOTES']);

		//if first iteration
		if($b == 0)
		{
			//if item append to before = 1, reset to zero
			if($itemsArr[$b]['ITEMAPPENDTOBEFORE'] == 1)
			{
				$itemsArr[$b]['ITEMAPPENDTOBEFORE'] = 0;
				$appendTrueFlag = false;
			}

			$newItemsArr[$b] = $itemsArr[$b];
		}
		//if not in first iteration
		else
		{
			//if item append to before is true, and this is the first appendment
			if($itemsArr[$b]['ITEMAPPENDTOBEFORE'] == 1 && $appendTrueFlag == false)
			{
				$appendTrueFlag = true;			//set append true flag to true
				$appendToItemNo = count($newItemsArr)-1;			//store index to append item to (previous item)

				$tempStr = buildInput($myQueryArr, $itemsArr[$b], $b+1);

				$newItemsArr[$appendToItemNo]['ITEMNOTES'] .= $itemnamelabel.'<span class="theAppendedItem">'.$tempStr.'</span>'.$itemnoteurl;
			}

			//if item append to before is true, and this is NOT the first appendment
			else if($itemsArr[$b]['ITEMAPPENDTOBEFORE'] == 1 && $appendTrueFlag == true)
			{
				//append item to appendtoitemno
				$tempStr = buildInput($myQueryArr, $itemsArr[$b], $b+1);

				$newItemsArr[$appendToItemNo]['ITEMNOTES'] .= $itemnamelabel.'<span class="theAppendedItem">'.$tempStr.'</span>'.$itemnoteurl;
			}

			//if item append to before is FALSE
			else
			{
				$appendTrueFlag = false;			//set append true flag to false
				$appendToItemNo = 0;

				//copy array content to new array
				$newItemsArr[] = $itemsArr[$b];
			}
		}
	}

	//copy appended array to existing array
	$itemsArr = $newItemsArr;
  	$newItemsArr = array();
   	$countItem = count($itemsArr);

  	//END APPEND TO ITEM BEFORE BLOCK
	//--------------------------------

	//for all component items, show the items
	for($a=0; $a < $countItem; $a++) {

		//--START TRANSLATION
		//ckm 20120314 - func_sys
		//get the translation for component item
		$tr8nStr_title = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$a]['ITEMID'],'ITEMTITLE');
		$tr8nStr_notes = getElemTranslation($myQuery,$_SESSION['language'],5,$itemsArr[$a]['ITEMID'],'ITEMNOTES');
		($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMTITLE') 				? $itemnamelabel 	= $tr8nStr_title[0]['TRANS_TEXT'] : $itemnamelabel = $itemsArr[$a]['ITEMTITLE'];
		($tr8nStr_notes[0]['TRANS_SOURCE_COLUMN'] == 'ITEMNOTES') 				? $itemnoteurl 		= $tr8nStr_notes[0]['TRANS_TEXT'] : $itemnoteurl = $itemsArr[$a]['ITEMNOTES'];
		//--END TRANSLATION

		if (BUTTON_URL_SECURITY)
			if ($itemnamelabel != "" ) $itemnamelabel = href_encoding($itemnamelabel); ?>

	<tr id="column_<?php echo $componentArr[$x]['COMPONENTID'].'_'.$itemsArr[$a]['ITEMID'] ; ?>" <?php echo $compCollapseHTML; ?> >
		<td width="150" class="inputLabel"><div id="input_label_<?php echo $componentArr[$x]['COMPONENTID'].'_'.$itemsArr[$a]['ITEMID'] ; ?>"> <?php echo $itemnamelabel?> </div></td>
		<td class="inputArea"><?php
		//if item default value is not set, check if bind to database columns and pre process is SELECT
		if($itemsArr[$a]['ITEMDEFAULTVALUE'] == '' && $componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
		{
			//for all items
			for($g=0; $g < $countGetMappedItem; $g++)
			{
				//if input name is in getMappedItem array, get mapping id
				if($getMappedItem[$g]['COMPONENTIDNAME'] == $itemsArr[$a]['ITEMNAME'])
				{
					//for column to find, find value in getDataRs
					$columnValueToFind = strtoupper($getMappedItem[$g]['MAPPINGID']);

					//set the value to default variable
					$itemsArr[$a]['ITEMDEFAULTVALUE'] = $getDataRs[0][$columnValueToFind];
				}//end if
			}//end for g
		}//end if

		//to build input
		$tempStr = buildInput($myQueryArr, $itemsArr[$a], $a+1);

		if(strlen($tempStr) > 0)
		{
			if (BUTTON_URL_SECURITY)
				if ( $itemnoteurl != "" )
					$itemnoteurl = href_encoding($itemnoteurl);
					echo $tempStr.' '. $itemnoteurl;
		}
			//else
			//	echo '&nbsp;';
		?></td>
	</tr>
	<?php }//end for x ?>
	<?php
	//if theres page control associated with the component
	if($controlArrCount > 0) { ?>
	<tr id="footer_<?php echo $componentArr[$x]['COMPONENTNAME']; ?>" <?php echo $compCollapseHTML; ?>>
		<td colspan="2" class="contentButtonFooter" style="margin:0px;padding:0px;">
			<table style="width:100%;">
				<tr>
					<td class="footer" style="text-align:left;border-right:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidLeft);?></td>
					<td class="footer" style="text-align:center;border-left:none;border-right:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidCenter);?></td>
					<td class="footer" style="text-align:right;border-left:none;width:33%;white-space:nowrap;"><?php buildControl($myQuery,$compControlidRight);?></td>
				</tr>
			</table>
		</td>
	</tr>
  <?php }?>
</table>
<br />
</div>
<?php }?>
