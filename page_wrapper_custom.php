<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" <?php echo $js[$x];?>>
<?php
if($componentArr[$x]['COMPONENTPATH'])
	$componentArr[$x]['COMPONENTPATH'] = trim($componentArr[$x]['COMPONENTPATH']);

if(is_file($componentArr[$x]['COMPONENTPATH']))
	include($componentArr[$x]['COMPONENTPATH']);
else
	echo 'File: '.$componentArr[$x]['COMPONENTPATH'].' for '.convertDBSafeToQuery($componentArr[$x]['COMPONENTTITLE']).' (Custom Component) not exist';
?>
</div>
<?php } ?>
