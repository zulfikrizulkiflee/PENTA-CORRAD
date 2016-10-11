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
	$theQuery = convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTTYPEQUERY']));
	$id = $componentArr[$x]['COMPONENTTYPEDB'];

	//run the query from db
  	$createLabel = $myQueryArr['myQuery'.$id]->query($theQuery,'SELECT','NAME');

	//if have result
	if(is_array($createLabel))
		$createLabelKeys = array_keys($createLabel[0]);

	$createLabelCount = count($createLabel[0]);			//count number of rows
	$createLabelKeysCount = count($createLabelKeys);	//count number of rows

	//if ada label
	$gotLabels = "select * from FLC_PAGE_COMPONENT_ITEMS
					where COMPONENTID = ".$componentArr[$x]['COMPONENTID']."
					and ITEMSTATUS = 1
					".$itemPermissionSQL."
					and ITEMTYPE = 'label'
					order by ITEMORDER";
	$gotLabelsArr = $myQuery->query($gotLabels,'SELECT','NAME');

	//for all number of rows
	for($xx=0; $xx < $createLabelCount; $xx++)
	{
		$querylabel = $createLabelKeys[$xx];
		$queryinputarea =$createLabel[0][$createLabelKeys[$xx]];

		if (BUTTON_URL_SECURITY) {
			if ( $querylabel  != "" ) $querylabel = href_encoding( $querylabel  );
			if ( $queryinputarea  != "" ) $queryinputarea = href_encoding( $queryinputarea  );
		}
	?>
  <tr id="column_<?php echo $componentArr[$x]['COMPONENTID'].'_'.$itemsArr[$a]["ITEMID"] ; ?>" <?php echo $compCollapseHTML; ?>>
    <td width="150" class="inputLabel"><div id="input_label_<?php echo $componentArr[$x]['COMPONENTID'].'_'.$itemsArr[$a]["ITEMID"] ; ?>">&nbsp;
		<?php if(isset($gotLabelsArr[$xx]['ITEMTITLE']))
		{
			$tr8nStr_title = getElemTranslation($myQuery,$_SESSION['language'],5,$gotLabelsArr[$xx]['ITEMID'],'ITEMTITLE');
			($tr8nStr_title[0]['TRANS_SOURCE_COLUMN'] == 'ITEMTITLE') 				? $gotLabelsArr[$xx]['ITEMTITLE'] 	= $tr8nStr_title[0]['TRANS_TEXT'] : $itemnamelabel = $gotLabelsArr[$xx]['ITEMTITLE'];

			echo $gotLabelsArr[$xx]['ITEMTITLE'];
		}
		else echo $querylabel ;?></div></td>
    <td nowrap="nowrap" class="inputArea"><?php echo $queryinputarea;?>&nbsp;</td>
  </tr>
  <?php } ?>
  <?php if($createLabelCount == 0) {?>
  <tr><td><?php echo TR8N_TABULAR_NO_RECORD ?></td></tr>
  <?php } ?>
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
