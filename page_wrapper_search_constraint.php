<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" <?php echo $js[$x];?>>
<?php
//get list of constraints
$getConstraint = convertDBSafeToQuery(convertToDBQry($componentArr[$x]['COMPONENTTYPEQUERY']));
$getConstraintRs = $myQuery->query($getConstraint,'SELECT','NAME');
$getConstraintRsCount = count($getConstraintRs);

//if filtering button is clicked
if($_POST['filterButton'])
{
	//check if column contains space, wrap with quotes " "
	if(trim(strpos($_POST['constraintColumn'],' ')))
		$_POST['constraintColumn'] = '[QD]'.$_POST['constraintColumn'].'[QD]';

	//create sql to append to master component
	$sqlToAppend = " where upper(".$_POST['constraintColumn'].") like upper('%".$_POST['constraintValue']."%')";
}
?>
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">
	  	<?php
			//translation for component
			$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],4,$componentArr[$x]['COMPONENTID'],'COMPONENTTITLE');
			($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'COMPONENTTITLE') ? $compTitle = $tr8nStr[0]['TRANS_TEXT'] : $compTitle = $componentArr[$x]['COMPONENTTITLE'];
			echo convertDBSafeToQuery($compTitle);

			include('show_hidden_inputs.php');

			if($componentArr[$x]['COMPONENTCOLLAPSE'] == 1) {
				if($componentArr[$x]['COMPONENTCOLLAPSEDEFAULT'] == 0)
					$compCollapseHTML = 'style="display:none;"';
				else
					$compCollapseHTML = '';
				?>
				<div style="text-align:right; float:right">
					<label id="collapseComponentLabel_<?php echo $componentArr[$x]['COMPONENTID']; ?>" title="Collapse Component" onclick="collapseComponentSearch(this)" style="cursor:pointer">
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
    <tr <?php echo $compCollapseHTML; ?>>
      <td width="150" class="inputLabel">Jenis Carian : </td>
      <td class="inputArea"><select name="constraintColumn" class="inputList" id="constraintColumn">
          <?php for($y=0; $y<$getConstraintRsCount; $y++) { ?>
          <option value="<?php echo $getConstraintRs[$y]['FLC_ID'];?>" <?php if(str_replace('[QS]','',str_replace('[QD]','',$_POST['constraintColumn'])) == $getConstraintRs[$y]['FLC_ID']) echo 'selected';?>><?php echo $getConstraintRs[$y]['FLC_NAME'];?></option>
          <?php } ?>
        </select></td>
    </tr>
    <tr <?php echo $compCollapseHTML; ?>>
      <td width="150" class="inputLabel">Carian : </td>
      <td class="inputArea"><input name="constraintValue" type="text" class="inputInput" id="constraintValue" size="30"  value="<?php echo $_POST['constraintValue'];?>" /></td>
    </tr>
    <tr <?php echo $compCollapseHTML; ?>>
      <td colspan="2" class="contentButtonFooter">
        <input name="filterButton" type="submit" class="inputButton" id="filterButton" value="Carian" />
        <input name="cancelFilterButton" type="reset" class="inputButton" value="Batal" /></td>
    </tr>
  </table>
<br />
</div>
<?php }?>
