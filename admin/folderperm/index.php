<?php
$files = array('conf.php');
$folders = array('conf_bak','export_import','export_import/dbsync','export_import/rollback','items','libraries','themes','upload');
$all = array($files,$folders);
?>
<div id="files_list">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent flcEditorList">
	<tr>
		<th colspan="9">Files and Folders List</th>
	</tr>
	<tr>
	  <th width="15" class="listingHead">#</th>
	  <th width="100" class="listingHead">Type</th>
      <th width="" class="listingHead">Name</th>
      <th width="40" class="listingHead">Exists</th>
	  <th width="20" class="listingHead">Permission</th>
	  <th width="30" class="listingHead">Level</th>
	</tr>
	<?php for($a=0; $a < count($all); $a++)  { ?>
	<?php for($x=0; $x < count($all[$a]); $x++){?>
	<tr>
	  <td class="listingContent"><?php echo $x+1;?>.</td>
	  <td class="listingContent"><?php if($a == 0) echo 'File'; else echo 'Folder'; ?></td>
	  <td class="listingContent"><?php echo $all[$a][$x];?></td>
      <td class="listingContent"><?php if(file_exists($all[$a][$x]) === true) echo 'Yes'; else echo 'No'; ?></td>
      <td class="listingContent"><?php if(is_writable($all[$a][$x]) === true && file_exists($all[$a][$x]) === true) echo 'Writable'; else if(is_writable($all[$a][$x]) === false && file_exists($all[$a][$x]) === false) echo '-'; else echo 'Readonly'; ?></td>
      <td class="listingContent"><?php  if(is_writable($all[$a][$x]) === true && file_exists($all[$a][$x]) === true) echo substr(sprintf('%o', fileperms($all[$a][$x])), -4); else echo '-'; ?></td>
	</tr>
	<?php } ?>
	<?php } ?>
  </table>
</div>