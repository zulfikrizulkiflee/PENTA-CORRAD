<?php

	include ('system_prerequisite.php');
    $sql = " INSERT INTO corrad214_pocspp.dashboard_positions (USERGROUPCODE, POSITIONS) VALUES ('SYSTEM', '".$_GET['data']."') ";
    mysql_query($sql); 
    
    if($_GET['type']=="load") {
        $sql = " SELECT * FROM corrad214_pocspp.dashboard_positions POS WHERE USERGROUPCODE = 'SYSTEM' ";
        $rs = mysql_query($sql); 
        $msg = "";
        if ($row = mysql_fetch_assoc($rs)) $msg = $row["POSITIONS"];
        echo $msg;
    }
    else {
        $sql = " INSERT INTO corrad214_pocspp.dashboard_positions (USERGROUPCODE, POSITIONS) VALUES ('SYSTEM', '".$_GET['data']."') ";
        mysql_query($sql); 
    }
    
?>
