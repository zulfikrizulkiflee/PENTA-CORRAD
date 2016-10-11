<?php
require_once('system_prerequisite.php');

//Checking session
validateUserSession();

//PART (INSERTING)

if($_POST['insert'])
{
//STEP 1 (Inserting Cloud Information and Creating User Schema)
	$insertNewInstance = "insert into FLC_INSTANCE_DETAIL (CLOUD_NO, CLOUD_ID,CLOUD_NAME,CLOUD_DATE_CREATED,CLOUD_CREATED_BY,CLOUD_STATUS,CLOUD_REMARKS)
							values (SEQ_FLC_INSTANCE_DETAIL.NEXTVAL, '".$_POST['instanceId']."','".$_POST['instanceName']."','".$_POST['dateCreated']."','".$_POST['createdBy']."','".$_POST['instanceStatus']."','".$_POST['instanceRemark']."')";
	$insertNewInstances = $myQuery->query($insertNewInstance,'RUN');
	
	if($insertNewInstances)
	{
		$insertNewDataInstance = "insert into FLC_DATABASE_CLOUD (DATABASE_OWNER,DATABASE_CDB_NAME,DATABASE_CUDB_NAME,DATABASE_USERNAME,DATABASE_PASSWORD) values ('".$_POST['instanceId']."','".$_POST['corradDBname']."','".$_POST['corradUDBname']."','".$_POST['corradDBname']."','".$_POST['databasePassword']."')";
		$insertNewInstances1 = $myQuery->query($insertNewDataInstance,'RUN');
		
		if($insertNewInstances1)
		{
			$createQ 	= "CREATE USER ".$_POST['corradDBname']." IDENTIFIED BY ".$_POST['databasePassword']."";
			$createQRes = $myQuery->query($createQ,'RUN');
			
			if($createQRes)
			{
				$grantQ 	= "GRANT ALL PRIVILEGE TO ".$_POST['corradDBname']."";//connection grant all
				$grantQRes 	= $myQuery->query($grantQ,'RUN');
				
				if($grantQRes)
				{
				//STEP 2 (Creating user Folder : Copying from existing Corrad Folder)
					function recurse_copy($src,$dst) 
					{
						$dir = opendir($src); 
						@mkdir($dst); 
							while(false !== ( $file = readdir($dir)) ) 
							{
								if (( $file != '.' ) && ( $file != '..' )) 
								{ 
									if(is_dir($src . '/' . $file)) 
									{	recurse_copy($src . '/' . $file,$dst . '/' . $file);	} 
									else 
									{	copy($src . '/' . $file,$dst . '/' . $file);	} 
								} 
							}
						closedir($dir); 
					}

					// Source directory (can be an FTP address)
					$src = "../upsi_clone";
					// Full path to the destination directory
					$dst = "../".$_POST['instanceName']."";
					//COPY FOLDER DEFAULT_CORRAD INTO THEIR INSTANCE NAME
					recurse_copy($src,$dst);
					
					$fname = "../".$_POST['instanceName']."/conf.php";
					$fhandle = fopen($fname,"r");
					$content = fread($fhandle,filesize($fname));
					
					$content = str_replace("dbusername", "".$_POST['corradDBname']."", $content);
					$content = str_replace("dbpass", "".$_POST['databasePassword']."", $content);
					$content = str_replace("dbother", "".$_POST['corradUDBname']."", $content);

					$fhandle = fopen($fname,"w");
					fwrite($fhandle,$content);
					fclose($fhandle);

					//STEP 3 (Connect to user Database and Create Table, Trigger, Inserting Corrad Apps Framework Information)
					$cloud_un 	= $_POST['corradDBname'];
					$cloud_pass	= $_POST['databasePassword'];

					//Attempt to connect for each cloud user
					$myDbConnTemp = new dbConnection;
					$myDbConnTemp->init($cloud_un,$cloud_pass,DB_DATABASE,DB_CONNECTION);
					
					if($myDbConnTemp)
					{
						$dbcTemp = $myDbConnTemp->getConnString();
						$myQueryTemp 	= new dbQuery($dbcTemp);
						$mySQLTemp 		= new dbSQL($dbcTemp);
						$dalTemp 		= new DAL($dbcTemp);

						//Get, Read, Create, Commit table from SQL File to each cloud user
						$filename = 'corrad_oracle_2.13.sql';
						$templine = '';
						$lines = file($filename);
						$homepage = file_get_contents($filename);

						foreach ($lines as $line)
						{
							//Skip comment line
							if (substr($line, 0, 2) == '--' || $line == '')
							continue;
							
							$templine .= $line;
							
							if (substr(trim($line), -1, 1) == ';'){
								$line_res[] = preg_replace('/;*/','', $templine);
								$templine = '';
							}
						}
						$lineRes_cnt = count($line_res);

						for($b=0; $b<$lineRes_cnt; $b++)
						{
							$lineRes_exec = $line_res[$b];
							$sa = $myQueryTemp->query($lineRes_exec,'RUN');
							if(!$sa){
							echo $cloud_un;
							}
						}
						unset($line_res);
						$line_res = array();

						//STEP 4 (Register User to Admin Privilege)
						$hash = md5($cloud_pass);

						$upd = "UPDATE PRUSER SET USERNAME='$cloud_un',USERPASSWORD='$hash' WHERE USERID = 1";
						$myQueryTemp->query($upd,'RUN');
						$myDbConnTemp->disconnect();
						
						$force = "SELECT s.sid||','|| s.serial# as SERIAL FROM ".'v$session s'.", ".'v$process'." p WHERE s.username = upper('$cloud_un') AND p.addr(+) = s.paddr";
						$force_rs = $myQuery->query($force,'SELECT','NAME');
						$force_cnt = count($force_rs);
						
						for($x=0; $x<$force_cnt; $x++)
						{
							$force_s = $force_rs[$x]['SERIAL'];
							$force_run = "ALTER SYSTEM KILL SESSION '$force_s'";
							$force_exec = $myQuery->query($force_run,'RUN');
						}

						//LIST (Part Insert)
						$recursiveInstanceList = $mySQL->getInstanceDetail('',false);
						$InstanceList = assambleRecursiveMenu($recursiveInstanceList);
						$InstanceListCount = count($InstanceList);

						for($x=0, $y=0; $x<$InstanceListCount; $x++)
						{
							$InstanceListRs[] = $InstanceList[$x];
						}
						$InstanceListRsCount = count($InstanceListRs);
					}
					else
					{
						showNotificationError('Cannot Connect to Cloud Database Schema!'); echo 'Cannot Connect to Cloud Database Schema!';
					}
				}
				else
				{
					showNotificationError('Grant Permission Failed!'); echo 'Grant Permission Failed! <br/>';	
				}
			}
			else
			{	echo 'Creating User Failed! <br/>';	}

			showNotificationInfo('New Instance Detail has been added.');
		}
		else
		{	showNotificationError('Fail to add new instances!'); echo 'Fail to add new instances!';	}
	}
}

//PART (UPDATING)
else if($_POST['update'])
{
	$instanceInfo = "select * from FLC_INSTANCE_DETAIL where CLOUD_ID = ".$_POST['Id'];
	$instanceInfoRs = $myQuery->query($instanceInfo,'SELECT','NAME');
	$instanceInfoRsCount = count($instanceInfoRs);

	$instanceName1 = $instanceInfoRs[0]['CLOUD_NAME'];
	
	$instanceDataInfo = "select * from FLC_DATABASE_CLOUD where DATABASE_OWNER= ".$_POST['Id'];
	$instanceDataInfoRs = $myQuery->query($instanceDataInfo,'SELECT','NAME');
	$instanceDataInfoRsCount = count($instanceDataInfoRs);
	$databaseUsername1 = $instanceDataInfoRs[0]['DATABASE_USERNAME'];
	$databasePassword1 = $instanceDataInfoRs[0]['DATABASE_PASSWORD'];
	$databaseCDBName = $instanceDataInfoRs[0]['DATABASE_CDB_NAME'];

	$fname = "C:/xampp/htdocs/".$instanceName1."/conf.php";
	$fhandle = fopen($fname,"r");
	$content = fread($fhandle,filesize($fname));
	$content = str_replace("".$databasePassword1."", "".$_POST['databasePassword']."", $content);

	$fhandle = fopen($fname,"w");
	fwrite($fhandle,$content);
	fclose($fhandle);

	$updateInstance = "update FLC_INSTANCE_DETAIL 
					set CLOUD_DATE_CREATED = '".$_POST['dateCreated']."',
						CLOUD_CREATED_BY = '".$_POST['createdBy']."',
						CLOUD_STATUS = '".$_POST['instanceStatus']."',
						CLOUD_REMARKS = '".$_POST['instanceRemark']."'
						
						where CLOUD_ID = ".$_POST['Id']."";
	
	$updateInstanceS = "update FLC_DATABASE_CLOUD
					set DATABASE_PASSWORD = '".$_POST['databasePassword']."'
						
						where DATABASE_OWNER = ".$_POST['Id']."";
	$updateInstanceRs = $myQuery->query($updateInstance,'RUN');
	$updateInstanceSRs = $myQuery->query($updateInstanceS,'RUN');

	if($updateInstanceRs)
	{	showNotificationInfo('Menu has been updated.');	}
	else
	{	showNotificationError('Fail to update menu!');	}
	
	$recursiveInstanceList = $mySQL->getInstanceDetail('',false);
	
	//list of available instance
	$InstanceList = assambleRecursiveMenu($recursiveInstanceList);
	$InstanceListCount = count($InstanceList);
	
	for($x=0, $y=0; $x<$InstanceListCount; $x++)
	{	$InstanceListRs[] = $InstanceList[$x];}
	
	$InstanceListRsCount = count($InstanceListRs);

	$instanceDataInfo = "select * from FLC_DATABASE_CLOUD where DATABASE_OWNER= ".$_POST['Id'];
	$instanceDataInfoRs = $myQuery->query($instanceDataInfo,'SELECT','NAME');
	$instanceDataInfoRsCount = count($instanceDataInfoRs);
	$databaseUsername1 = $instanceDataInfoRs[0]['DATABASE_USERNAME'];
	$databasePassword1 = $instanceDataInfoRs[0]['DATABASE_PASSWORD'];
	$databaseCDBName = $instanceDataInfoRs[0]['DATABASE_CDB_NAME'];

	require_once('class/db_'.DBMS_NAME.'.php');
	require_once('class/sql_'.DBMS_NAME.'.php');
	require_once('class/dal.php');

	$myDbConn_2 = new dbConnection;
	$myDbConn_2->init($_POST['corradDBname'],$_POST['databasePassword'],DB_DATABASE,DB_CONNECTION);
	$dbc_2 = $myDbConn_2->getConnString();
	$myQuery_2 	= new dbQuery($dbc_2);
	$mySQL_2 	= new dbSQL($dbc_2);
	$dal_2		= new DAL($dbc_2);
			
	$userid = "1";
	$password1 = "".$_POST['databasePassword']."";
	$hash = md5($password1);
	
	$databaseName = $databaseCDBName;

	$sqlb = "UPDATE PRUSER ".
			"SET USERPASSWORD = '$hash' ".
			"WHERE USERID = $userid" ;
	$sqlbRes 	= $myQuery_2->query($sqlb,'RUN');	

	$user2			= $_POST['databaseUser'];
	$password2		= $_POST['databasePassword'];
	$hash 			= md5($password2);
	$sqlc_Upd 		= "UPDATE user SET Password = '$hash' WHERE User='$user2'";
	$sqlc_UpdRes 	= $myQuery->query($sqlc_Upd,'RUN');	
}

//PART (EDITTING)
else if($_POST['edit'])
{
	$instanceInfo = "select * from FLC_INSTANCE_DETAIL where CLOUD_ID = ".$_POST['Id'];
	$instanceInfoRs = $myQuery->query($instanceInfo,'SELECT','NAME');
	$instanceInfoRsCount = count($instanceInfoRs);

	//assign variable
	$instanceId = $instanceInfoRs[0]['CLOUD_ID'];
	$instanceName = $instanceInfoRs[0]['CLOUD_NAME'];
	$dateCreated = $instanceInfoRs[0]['CLOUD_DATE_CREATED'];
	$createdBy = $instanceInfoRs[0]['CLOUD_CREATED_BY'];
	$instanceStatus = $instanceInfoRs[0]['CLOUD_STATUS'];
	$instanceRemarks = $instanceInfoRs[0]['CLOUD_REMARKS'];
	
	$instanceDataInfo = "select * from FLC_DATABASE_CLOUD where DATABASE_OWNER= ".$_POST['Id'];
	$instanceDataInfoRs = $myQuery->query($instanceDataInfo,'SELECT','NAME');
	$instanceDataInfoRsCount = count($instanceDataInfoRs);

	//assign variable
	$databaseOwner = $instanceDataInfoRs[0]['DATABASE_OWNER'];
	$databaseCDBName = $instanceDataInfoRs[0]['DATABASE_CDB_NAME'];
	$databaseCUDBName = $instanceDataInfoRs[0]['DATABASE_CUDB_NAME'];
	$databaseUsername = $instanceDataInfoRs[0]['DATABASE_USERNAME'];
	$databasePassword = $instanceDataInfoRs[0]['DATABASE_PASSWORD'];

}

//PART (VIEWING)
else if($_POST['view'])
{
	$instanceInfo = "select * from FLC_INSTANCE_DETAIL where CLOUD_ID = '".$_POST['Id']."'";
	$instanceInfoRs = $myQuery->query($instanceInfo,'SELECT','NAME');
	$instanceInfoRsCount = count($instanceInfoRs);

	//assign variable
	$instanceId = $instanceInfoRs[0]['CLOUD_ID'];
	$instanceName = $instanceInfoRs[0]['CLOUD_NAME'];
	$dateCreated = $instanceInfoRs[0]['CLOUD_DATE_CREATED'];
	$createdBy = $instanceInfoRs[0]['CLOUD_CREATED_BY'];
	$instanceStatus = $instanceInfoRs[0]['CLOUD_STATUS'];
	$instanceRemarks = $instanceInfoRs[0]['CLOUD_REMARKS'];
	
	$instanceDataInfo = "select * from FLC_DATABASE_CLOUD where DATABASE_OWNER= '".$_POST['Id']."'";
	$instanceDataInfoRs = $myQuery->query($instanceDataInfo,'SELECT','NAME');
	$instanceDataInfoRsCount = count($instanceDataInfoRs);

	//assign variable
	$databaseOwner = $instanceDataInfoRs[0]['DATABASE_OWNER'];
	$databaseCDBName = $instanceDataInfoRs[0]['DATABASE_CDB_NAME'];
	$databaseCUDBName = $instanceDataInfoRs[0]['DATABASE_CUDB_NAME'];
	$databaseUsername = $instanceDataInfoRs[0]['DATABASE_USERNAME'];
	$databasePassword = $instanceDataInfoRs[0]['DATABASE_PASSWORD'];

}

//PART (DELETING)
else if($_POST['delete'])
{
	$deleteInstanceRs	= $mySQL->deleteInstance("'".$_POST['Id']."'");

	if($deleteInstanceRs)
	{	showNotificationInfo('Menu has been deleted.');	}
	else
	{	showNotificationError('Fail to delete menu!');	}
	
	$recursiveInstanceList = $mySQL->getInstanceDetail('',false);
	
	//list of available menu
	$InstanceList = assambleRecursiveMenu($recursiveInstanceList);
	$InstanceListCount = count($InstanceList);
	
	//loop on count of menu
	for($x=0, $y=0; $x<$InstanceListCount; $x++)
	{
		//assign filtered menu
		$InstanceListRs[] = $InstanceList[$x];
	}//eof for
	
	$InstanceListRsCount = count($InstanceListRs);
}//eof if


//show list
else
{
	//get all menu (recursively)
	$recursiveInstanceList = $mySQL->getInstanceDetail('',false);
	
	//list of available menu
	$InstanceList = assambleRecursiveMenu($recursiveInstanceList);
	$InstanceListCount = count($InstanceList);
	
		//loop on count of menu
		for($x=0, $y=0; $x<$InstanceListCount; $x++)
		{	$InstanceListRs[] = $InstanceList[$x];	}
	
	$InstanceListRsCount = count($InstanceListRs);
}
?>

<script language="javascript" src="js/editor.js"></script>

<script>


function refreshIcon()
{
	var url = jQuery('#iconURL').val();
	jQuery('#previewIcon').attr('src',url);
}
function hoverTable()
{
	var tr = jQuery('#menu_editor_list table').find('tr');

	jQuery(tr).each(function(x){

		if(x > 0)
		{
			jQuery(this).on('mouseover',function(){
					jQuery(this).css('background-color','#FFFFCC')
			});

			if(x % 2 == 1)
			{
				jQuery(this).css('background-color','#F7F7F7');

				jQuery(this).on('mouseover',function(){
					jQuery(this).css('background-color','#F7F7F7')
				});
			}
			else
			{
				jQuery(this).on('mouseout',function(){
					jQuery(this).css('background-color','#FFFFFF');
				});
			}
		}
	});
}
function deleteFunction()
{
	jQuery('[name="delete"]').on('click',function(){
		if(window.confirm('Are you sure you want to DELETE this menu?\nThis will also delete ALL sub-menu (if any) under this menu.'))
			return true;
		else
			return false;
	});
}
</script>

<?php 
//if ajax filter
if(!$_GET['filter_menu'])
{?>
<div id="breadcrumbs">System Administrator / Configuration / Cloud Admin</div>
<h1>Cloud Admin</h1>
<?php }?>

<?php if((!$_POST['new']&& !$_POST['view'] && !$_POST['edit']))
{?>
<div id="menu_editor_list">
 <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="7">List of Instance</th>
    </tr>
	<tr>
      <th nowrap="nowrap" class="listingHead" width="3%">#</th>
      <th nowrap="nowrap" class="listingHead" width="17%">Instance Name</th>
      <th nowrap="nowrap" class="listingHead" width="17%">Created By</th>
      <th nowrap="nowrap" class="listingHead" width="10%">Date Created</th>
      <th nowrap="nowrap" class="listingHead" width="10%">Status</th>
      <th nowrap="nowrap" class="listingHead" width="38%">Remarks</th>
      <th nowrap="nowrap" class="listingHead" width="10%">Action</th>
    </tr>
   <?php if($InstanceListRsCount)
   {?>
	<?php for($x=0; $x<$InstanceListRsCount; $x++)
					{?>
	<tr id="<?php echo $InstanceListRs[$x]['CLOUD_NO'];?>" onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
      <td class="listingContent"><?php echo $x+1;?></td>
	  <td class="listingContent"><?php echo $InstanceListRs[$x]['CLOUD_NAME'];?></td>
      <td class="listingContent"><?php echo $InstanceListRs[$x]['CLOUD_CREATED_BY'];?></td>
	  <td class="listingContent"><?php echo $InstanceListRs[$x]['CLOUD_DATE_CREATED'];?></td>
	  <td class="listingContent"><?php echo $InstanceListRs[$x]['CLOUD_STATUS'];?></td>
	  <td class="listingContent"><?php echo $InstanceListRs[$x]['CLOUD_REMARKS'];?></td>
      <td nowrap="nowrap" class="listingContentRight" align="center">
          <form id="formDetail" name="formDetail" method="post">
			<input name="view" type="submit" class="inputButton" id="view" value="View" />
           <!-- <input id="edit" name="edit" type="submit" class="inputButton" value="Edit" /> -->
			<input id="delete" name="delete" type="submit" class="inputButton" value="Delete" />
            <input name="Id" id="Id" type="hidden" value="<?php echo $InstanceListRs[$x]['CLOUD_ID'];?>" />
		
		  </form>
	  </td>
    </tr>
    				<?php } //end for ?>
	<?php }//end if 
	else { ?>
	<tr>
	  <td colspan="7" class="myContentInput">No Instances(s) Register Found... </td>
	</tr>
	<?php } //end else?>
    <tr>
	  <td colspan="9" class="contentButtonFooter">
		  <form id="form2" name="form2" method="post">
			<input id="gotoBottom" name="gotoBottom" type="button" class="inputButton" value="Go To Top" onclick="jQuery('html, body').animate({ scrollTop: 0 }, 1000);" />
			<input id="new" name="new" type="submit" class="inputButton" value="New" />
		  </form>
	  </td>
	</tr>
  </table>
</div>
<?php }?>

<?php if($_POST['new']  )
/////////////////////Form for inserting the instance ////////////////////////////////////////////
{?>


<form id="form1" name="form1" method="post">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2"><?php if($_POST['edit']){?>View&nbsp;Instance&nbsp;Detail<?php }else{?>Register&nbsp;New&nbsp;Instance<?php }?> </th>
    </tr>
    <tr>
      <td class="inputLabel">Instance Name :<br /><font size="-6"><b>Student ID</b></font> </td>
      <td>
      	<input id="instanceName" name="instanceName" type="text" placeholder="A001" class="inputInput" size="50" value="" onChange="this.form.corradDBname.value=this.value" />
        <br /><label class="labelNote">Note: Leave blank for system generated.</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Cloud ID : </td>
      <td>
      	<input id="instanceId" name="instanceId" type="text" placeholder="001" class="inputInput" size="50" value="" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    
        <input id="dateCreated" name="dateCreated" type="hidden" class="inputInput" size="50" value="<?php echo date('d-m-Y')?>" />
		<input id="createdBy" name="createdBy" type="hidden" class="inputInput" size="50" value="<?php  print $_SESSION['userName'] ?>" />
        <input id="instanceStatus" name="instanceStatus" type="hidden" class="inputInput" size="50" value="<?php echo "Active"?>" />
    <tr>
      <td class="inputLabel">Remarks : </td>
      <td>
      	<input id="instanceRemark" name="instanceRemark" type="text" class="inputInput" size="50" value="" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Corrad Database Name : </td>
      <td>
      	<input id="corradDBname" name="corradDBname" type="text" placeholder="corrad_A001" class="inputInput" size="50" value="" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Corrad User Database Name : </td>
      <td>
      	<input id="corradUDBname" name="corradUDBname" type="text" placeholder="corrad_user_A001" class="inputInput" size="50" value="" />
        <label class="labelMandatory">*Kosongkan jika tiada</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Database User : </td>
      <td>
      	<input id="databaseUser" name="databaseUser" type="text" placeholder="A001"class="inputInput" size="50" value="" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Database Password : </td>
      <td>
      	<input id="databasePassword" name="databasePassword" placeholder="password123" type="password" class="inputInput" size="50" value="" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <?php if($_POST['edit']){ ?> <?php }else{ ?> <input id="insert" name="insert"   type="submit" class="inputButton" value="Save" /> <?php }?>
        
       
        <input id="close" name="close" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
</form>
<?php 

/////////////////////////// end of form instance

}?>


<?php if($_POST['view'] ){?>
<form id="form1" name="form1" method="post">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2"><?php if($_POST['view']){?>View&nbsp;Instance&nbsp;Detail<?php }else{?>Register&nbsp;New&nbsp;Instance<?php }?> </th>
    </tr>
    <tr>
      <td class="inputLabel">Instance Name : </td>
      <td><?php echo $instanceName;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Cloud ID : </td>
      <td><?php echo $instanceId;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Date Created : </td>
      <td><?php echo $dateCreated;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Created By : </td>
      <td><?php echo $createdBy;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Status : </td>
      <td><?php echo $instanceStatus;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Remarks : </td>
      <td><?php echo $instanceRemarks;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Corrad Database Name : </td>
      <td><?php echo $databaseCDBName;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Corrad User Database Name : </td>
      <td><?php echo $databaseCDBName;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Database User : </td>
      <td><?php echo $databaseUsername;?></td>
    </tr>
    <tr>
      <td class="inputLabel">Database Password : </td>
      <td><?php echo $databasePassword;?></td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
        <input id="close" name="close" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
</form>
<?php }?>



<?php if($_POST['edit'] ){?>
<form id="form1" name="form1" method="post">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2"><?php if($_POST['edit']){?>Edit&nbsp;Instance&nbsp;Detail<?php }else{?>Register&nbsp;New&nbsp;Instance<?php }?> </th>
    </tr>
    <tr>
      <td class="inputLabel">Instance Name : </td>
      <td>
      	<input id="instanceName" name="instanceName" type="text" placeholder="A001" class="inputInput" size="50" value="<?php echo $instanceName;?>" readonly="readonly"  />
        <input id="Id" name="Id" type="hidden" value="<?php echo $instanceId;?>" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Cloud ID : </td>
      <td>
      	<input id="instanceId" name="instanceId" type="text" placeholder="001" class="inputInput" size="50" value="<?php echo $instanceId;?>" readonly="readonly" />
	  </td>
    </tr>
    
       
       <input id="dateCreated" name="dateCreated" type="hidden" class="inputInput" size="50" value="<?php echo date('d-m-Y')?>" />
		<input id="createdBy" name="createdBy" type="hidden" class="inputInput" size="50" value="<?php  print $_SESSION['userName'] ?>" />
     
    <tr>
      <td class="inputLabel">Status :<font size="-6"><br /><b>* Can Be Update</b></font> </td>
      <td>
       <select id="instanceStatus" name="instanceStatus" class="inputList">
     <option value='Active' <?php if($instanceStatus== "Active") 
                        {
                        echo 'selected=selected';}?>>Active</option>
						<option value='Inactive' <?php if($instanceStatus == "Inactive") 
                        {
                        echo 'selected=selected';}?>>Inactive</option>
                    	<option value='Temporary'<?php if($instanceStatus == "Temporary") 
                        {
                        echo 'selected=selected';}?>>Temporary</option>
                    	</select>
         
        </select>
       
      
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Remarks :<font size="-6"><br /><b>* Can Be Update</b></font> </td>
      <td>
      	<input id="instanceRemark" name="instanceRemark" type="text" class="inputInput" size="50" value="<?php echo $instanceRemarks;?>" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Corrad Database Name : </td>
      <td>
      	<input id="corradDBname" name="corradDBname" type="text"  class="inputInput" size="50" value="<?php echo $databaseCDBName;?>" readonly="readonly" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Corrad User Database Name : </td>
      <td>
      	<input id="corradUDBname" name="corradUDBname" type="text"  class="inputInput" size="50" value="<?php echo $databaseCUDBName;?>" readonly="readonly" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Database User : </td>
      <td>
      	<input id="databaseUser" name="databaseUser" type="text" placeholder="A001"class="inputInput" size="50" value="<?php echo $databaseUsername;?>" readonly="readonly" />
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Database Password :<font size="-6"><br /><b>* Can Be Update</b></font>  </td>
      <td>
      	<input id="databasePassword" name="databasePassword"  type="text"class="inputInput" size="50" value="<?php echo $databasePassword;?>" />
      </td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
       <input id="update" name="update" type="submit" class="inputButton" value="Save" />
        <input id="close" name="close" type="submit" class="inputButton" value="Close" />
      </td>
    </tr>
  </table>
</form>
<?php }?>



<script>
//highlight recently tocuhed menu, and scroll to the menu	
jQuery('#<?php echo $_POST['CLOUD_NO']?>').css('background-color','#E6E6E6');
//setTimeout(function(){jQuery('#<?php echo $_POST['CLOUD_NO']?>').css('background-color','')},3000);
//jQuery('html, body').animate({scrollTop: jQuery("#<?php echo $_POST['CLOUD_NO']?>").offset().top-100 }, 200);
</script>
<?php if((!$_POST['new'] && !$_POST['view']) || $_GET['filter_menu']){?>
<script>
var allMenuTr = jQuery('#menu_editor_list table tr');
var startTD = 0;
var colToMerge = 3;

for(var x=2; x < allMenuTr.length-1; x++)
{
	if(x == 2)
		startTD = x;
	
	var parentMenuTD = jQuery(allMenuTr[startTD]).find('td').eq(colToMerge).html().replace(/\//g,'&#8260;');
	var nextParentMenuTD = jQuery(allMenuTr[x+1]).find('td').eq(colToMerge).html();
	
	if(nextParentMenuTD != null)
		nextParentMenuTD = nextParentMenuTD.replace(/\//g,'&#8260;');
		
	if(parentMenuTD == nextParentMenuTD)
	{	
		jQuery(allMenuTr[x+1]).find('td').eq(colToMerge).hide();			//next parent menu td
		
		var originalRowspan = jQuery(allMenuTr[startTD]).find('td').eq(colToMerge).attr('rowspan');
		
		if(originalRowspan === undefined)
			originalRowspan = 1;
		else
			originalRowspan = parseInt(originalRowspan);
		
		jQuery(allMenuTr[startTD]).find('td')
			.eq(colToMerge)
			.attr('rowspan',originalRowspan+1)
			.css('vertical-align','top')
			.css('padding-top','10px');					//add colspan
	}
	else
		startTD = x+1;
}
</script>
<?php } ?>
<script>
//from resizeOverflowedReport() 
var pageWidthRef = jQuery('#breadcrumbs').width();
var allReport = jQuery('#content .flcEditorList');			//detect all reports in content page

for(var x=0; x < allReport.length; x++)
{  
	jQuery(allReport[x]).after('').appendTo('#menu_editor_list');
	
	jQuery(allReport[x]).parent()
		.css('width',pageWidthRef+13+'px')
		.css('position','relative')
		.css('overflow','auto')
		.css('margin-left','0px')
		.css('margin-bottom','10px');
}
</script>