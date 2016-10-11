<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="component_<?php echo $componentArr[$x]['COMPONENTID'];?>" <?php echo $js[$x];?>>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent" style="border:none;">
		<tr>
			<td>
				<iframe id="iframe_<?php echo $componentArr[$x]['COMPONENTID'];?>" src="<?php echo convertDBSafeToQuery($componentArr[$x]['COMPONENTPATH']);?>" style="width:100%; height:500px;" frameborder="0" /></iframe>
			</td> 
		</tr>
	</table>
</div>
<?php }?>
