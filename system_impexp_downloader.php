<?php 
if($_GET['type'] == 'exp') {
header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=exp_".$_GET['file']); 
echo file_get_contents('export_import/exp_'.$_GET['file']);
}
else if($_GET['type'] == 'dbsync') {
header("Content-type: application/octet-stream");
header("Content-disposition: attachment; filename=dbsync_".$_GET['file']); 
echo file_get_contents('export_import/dbsync/dbsync_'.$_GET['file']);	
}
?>
