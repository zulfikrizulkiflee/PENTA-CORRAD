<?php
//validate that user have session
require_once('func_common.php');
validateUserSession();

/* ===== FUNCTION ===== */
function loadXML($path, $xmlFile)
{
	//parse xml file
	$xmlParse = simplexml_load_file($path.'/'.$xmlFile);
	
	//xml attributes and values
	$extension_attr = $xmlParse->attributes();
	//$extension['type'] = $extension_attr['type'];
	//$extension['version'] = $extension_attr['version'];
	$extension['type'] = $_POST['ext_type'];
	$extension['size'] = round(getPathSize($path)/1024,2);
	$extension['upload_path'] = str_replace('\\','/',$path);
	
	//basic info
	$extension['name'] = fileEscapeCharacter($xmlParse->name);
	$extension['title'] = $xmlParse->title;
	$extension['description'] = $xmlParse->description;
	$extension['version'] = $xmlParse->version;
	$extension['icon'] = $xmlParse->icon;
	$extension['author'] = $xmlParse->author;
	$extension['author_url'] = $xmlParse->authorUrl;
	
	//convert string to db safe string
	if($extension['title'] != '') 			$extension['title'] = convertToDBSafe($extension['title']);
	if($extension['description'] != '') 	$extension['description'] = convertToDBSafe($extension['description']);
	if($extension['author'] != '') 			$extension['author'] = convertToDBSafe($extension['author']);
	
	//set path based on type
	switch($extension['type'])
	{
		case 'item';		$extension['install_path'] = 'items/'.$extension['name'];
		break;
		
		case 'library';		$extension['install_path'] = 'libraries/'.$extension['name'];
		break;
		
		case 'theme';		$extension['install_path'] = 'themes/'.$extension['name'];
		break;
	}//eof switch
	
	/*=== additional attributes ===*/
	//include file (index)
	if($xmlParse->index)
	{
		foreach($xmlParse->index->file as $file)
			$extension['include']['index'][] = $file;
	}//eof if
	
	//include file (builder)
	if($xmlParse->builder)
	{
		foreach($xmlParse->builder->file as $file)
			$extension['include']['builder'][] = $file;
	}//eof if
	
	//install file
	if($xmlParse->install)
	{
		foreach($xmlParse->install->file as $file)
			$extension['installFile'][] = $file;
	}//eof if
	
	//uninstall file
	if($xmlParse->uninstall)
	{
		foreach($xmlParse->uninstall->file as $file)
			$extension['uninstallFile'][] = $file;
	}//eof if
	/*=== additional attributes ===*/
	
	return $extension;
}//eof function
/* ===== FUNCTION ===== */

//upload
if($_POST['upload'])
{
	//have file to upload
	if($_FILES['extFile'])
	{
		//upload file
		$uploadedZip = $_FILES['extFile']['tmp_name'];
		
		//if file uploaded
		if($uploadedZip)
		{
			//get name of file without extension
			$extractedZip = str_replace('.'.$fileExt,'',$uploadedZip).'_tmp';

			//create new directory if not exist
			if(!is_dir($extractedZip)) 
				mkdir($extractedZip, 0777);

			//unzip file into the created folder and remove the uploaded zip file
			unzip($uploadedZip, $extractedZip);
			
			//check install.xml file exist
			if(file_exists($extractedZip.'/install.xml'))
			{
				//extract xml
				$extension = loadXML($extractedZip, 'install.xml');
				
				//if extension exist
				if(is_array($extension))
				{
					//check existing extension
					$checkExt = "select EXT_ID from FLC_EXTENSION where EXT_NAME = '".$extension['name']."'";
					$checkExtRs = $myQuery->query($checkExt,'SELECT','NAME');
					
					//if already exist
					if($checkExtRs)
					{
						$installFlag = false;	
					
						//notification
						showNotificationError('Extension already exist. Please rename your extension or remove the current extension!');
					}//eof if
					else
						$installFlag = true;
				}//eof if
				else
				{
					$installFlag = false;
					
					//notification
					showNotificationError('Package file not complete. File install.xml cannot be read!');
				}//eof else
			}//eof if
			else
			{
				$installFlag = false;
				
				//notification
				showNotificationError('Package file not complete. File install.xml not exist!');
			}//eof else
				
			//if install flag true
			if($installFlag)
			{
				//max id
				$maxExtIdRs = $mySQL->maxValue('FLC_EXTENSION','EXT_ID',0) + 1;
				
				//insert extension info into db
				$insertExt = "insert into FLC_EXTENSION
								(EXT_ID, EXT_TYPE, EXT_NAME, EXT_TITLE, EXT_DESC, EXT_VERSION, EXT_ICON,
								EXT_AUTHOR, EXT_AUTHOR_URL, EXT_PATH, EXT_CREATE_BY, EXT_CREATE_DATE) 
								values (".$maxExtIdRs.", '".$extension['type']."', '".$extension['name']."', '".$extension['title']."',
								'".$extension['description']."', '".$extension['version']."', '".$extension['icon']."',
								'".$extension['author']."', '".$extension['author_url']."', 
								'".$extension['install_path']."', ".$_SESSION['userID'].", ".$mySQL->currentDate().")";
				$insertExtRs = $myQuery->query($insertExt,'RUN');
				
				//if inserted
				if($insertExtRs)
				{
					//insert include file (if any)
					if(is_array($extension['include']))
					{
						//file mode
						$includeMode = array_keys($extension['include']);
						$includeModeCount = count($includeMode);
						
						//loop on count of mode
						for($x=0; $x<$includeModeCount; $x++)
						{
							//loop on count of file by mode
							for($y=0; $y<count($extension['include'][$includeMode[$x]]); $y++)
							{
								//max file id
								$maxExtFileIdRs = $mySQL->maxValue('FLC_EXTENSION_FILE','EXT_FILE_ID',0) + 1;
								
								//insert file
								$insertFile = "insert into FLC_EXTENSION_FILE 
												(EXT_FILE_ID, EXT_FILE_MODE, EXT_FILE_TYPE, EXT_FILE_NAME, EXT_ID)
												values (".$maxExtFileIdRs.", '".$includeMode[$x]."', 
													'".$extension['include'][$includeMode[$x]][$y]['type']."', 
													'".$extension['include'][$includeMode[$x]][$y]['path']."', ".$maxExtIdRs.")";
								$insertFileRs = $myQuery->query($insertFile,'RUN');
							}//eof for
						}//eof for
					}//eof if
					
					//to automatic open listing by extension type
					$_POST['ext_type'] = $extension['type'];
					
					//notification
					showNotificationInfo('Extension successfully installed.');
				}//eof if
				else
				{
					//notification
					showNotificationError('Installation fail!');
				}//eof else
				
				//if inserted
				if($insertExtRs)		
				{
					//copy the uploaded extension into extension folder
					copyFile($extension['upload_path'], $extension['install_path']);
					
					//move the zip file
					move_uploaded_file($uploadedZip, $extension['install_path'].'/'.$extension['name'].'.zip');
				}//eof if
			}//eof if
			
			//remove the uploaded extension
			removeFolder($extractedZip);
		}//eof if
	}//eof if
}//eof if

//upgrade
else if($_POST['upgrade'])
{
	//have file to upload
	if($_FILES['extFile'])
	{
		//upload file
		$uploadedZip = $_FILES['extFile']['tmp_name'];
		
		//if file uploaded
		if($uploadedZip)
		{
			//get name of file without extension
			$extractedZip = str_replace('.'.$fileExt,'',$uploadedZip).'_tmp';

			//create new directory if not exist
			if(!is_dir($extractedZip)) 
				mkdir($extractedZip, 0777);

			//unzip file into the created folder and remove the uploaded zip file
			unzip($uploadedZip, $extractedZip);
			
			//check install.xml file exist
			if(file_exists($extractedZip.'/install.xml'))
			{
				//extract xml
				$extension = loadXML($extractedZip, 'install.xml');
				
				//if extension exist
				if(is_array($extension))
				{
					//check existing extension
					$checkExt = "select EXT_ID from FLC_EXTENSION where EXT_NAME = '".$extension['name']."'";
					$checkExtRs = $myQuery->query($checkExt,'SELECT','NAME');
					
					//if already exist
					if(!$checkExtRs)
					{
						$installFlag = false;	
					
						//notification
						showNotificationError('Extension name does not match. Please choose already installed extension!');
					}//eof if
					else
						$installFlag = true;
				}//eof if
				else
				{
					$installFlag = false;
					
					//notification
					showNotificationError('Package file not complete. File install.xml cannot be read!');
				}//eof else
			}//eof if
			else
			{
				$installFlag = false;
				
				//notification
				showNotificationError('Package file not complete. File install.xml not exist!');
			}//eof else
				
			//if install flag true
			if($installFlag)
			{
				//extension id
				$currentExtIdRs = $checkExtRs[0]['EXT_ID'];
				
				//update extension info into db
				$updateExt = "update FLC_EXTENSION
								set EXT_TITLE = '".$extension['title']."',
									EXT_DESC = '".$extension['description']."',
									EXT_VERSION = '".$extension['version']."',
									EXT_ICON = '".$extension['icon']."',
									EXT_AUTHOR = '".$extension['author']."',
									EXT_AUTHOR_URL = '".$extension['author_url']."',
									EXT_PATH = '".$extension['install_path']."',
									EXT_UPDATE_BY = ".$_SESSION['userID'].",
									EXT_UPDATE_DATE = ".$mySQL->currentDate()."
								where EXT_ID = ".$currentExtIdRs."";
				$updateExtRs = $myQuery->query($updateExt,'RUN');
				
				//if updated
				if($updateExtRs)
				{
					//remove prev include file
					$deleteFile = "delete from FLC_EXTENSION_FILE where EXT_ID = ".$currentExtIdRs;
					$deleteFileRs = $myQuery->query($deleteFile,'RUN');
					
					//insert include file (if any)
					if(is_array($extension['include']))
					{
						//file mode
						$includeMode = array_keys($extension['include']);
						$includeModeCount = count($includeMode);
						
						//loop on count of mode
						for($x=0; $x<$includeModeCount; $x++)
						{
							//loop on count of file by mode
							for($y=0; $y<count($extension['include'][$includeMode[$x]]); $y++)
							{
								//max file id
								$maxExtFileIdRs = $mySQL->maxValue('FLC_EXTENSION_FILE','EXT_FILE_ID',0) + 1;
								
								//insert file
								$insertFile = "insert into FLC_EXTENSION_FILE 
												(EXT_FILE_ID, EXT_FILE_MODE, EXT_FILE_TYPE, EXT_FILE_NAME, EXT_ID)
												values (".$maxExtFileIdRs.", '".$includeMode[$x]."', 
													'".$extension['include'][$includeMode[$x]][$y]['type']."', 
													'".$extension['include'][$includeMode[$x]][$y]['path']."', ".$currentExtIdRs.")";
								$insertFileRs = $myQuery->query($insertFile,'RUN');
							}//eof for
						}//eof for
					}//eof if
					
					//to automatic open listing by extension type
					$_POST['ext_type'] = $extension['type'];
					
					//notification
					showNotificationInfo('Extension successfully installed.');
				}//eof if
				else
				{
					//notification
					showNotificationError('Installation fail!');
				}//eof else
				
				//if inserted
				if($updateExtRs)		
				{
					//copy the uploaded extension into extension folder
					copyFile($extension['upload_path'], $extension['install_path']);
					
					//move the zip file
					move_uploaded_file($uploadedZip, $extension['install_path'].'/'.$extension['name'].'.zip');
				}//eof if
			}//eof if
			
			//remove the uploaded extension
			removeFolder($extractedZip);
		}//eof if
	}//eof if
}//eof elseif

//change status
else if($_POST['changeStatus'])
{
	if(!isset($_POST['newStatus']))
		$_POST['newStatus'] = 0;
	
	//update status
	$updateStatus = "update FLC_EXTENSION set EXT_STATUS = ".$_POST['newStatus']." where EXT_ID = ".$_POST['changeStatus'];
	$updateStatusRs = $myQuery->query($updateStatus,'RUN');
}//eof elseif

//delete extension
else if($_POST['delete'])
{
	//get extension path
	$extPath = "select EXT_PATH from FLC_EXTENSION where EXT_ID =".$_POST['hiddenCode'];
	$extPathRs = $myQuery->query($extPath,'SELECT','INDEX');
	
	//delete extension
	$deleteExt = "delete from FLC_EXTENSION where EXT_ID = ".$_POST['hiddenCode'];
	$deleteExtRs = $myQuery->query($deleteExt,'RUN');
	
	//if deleted
	if($deleteExtRs)
	{
		//remove extension file
		$deleteExt = "delete from FLC_EXTENSION_FILE where EXT_ID = ".$_POST['hiddenCode'];
		$deleteExtRs = $myQuery->query($deleteExt,'RUN');
		
		//if directory exist
		if(is_dir($extPathRs[0][0]))
		{
			//remove extension folder
			removeFolder($extPathRs[0][0]);
		}//eof if
		
		//notification
		showNotificationInfo('Extension successfully deleted.');
	}//eof if
}//else if
else if($_POST['view'])
{
	//view extension
	$extView = "select EXT_ID, EXT_TYPE, EXT_NAME, EXT_TITLE, EXT_DESC, EXT_VERSION, EXT_AUTHOR, EXT_AUTHOR_URL, EXT_ICON, EXT_PATH, EXT_STATUS,
						(select USERNAME from PRUSER where USERID = a.EXT_CREATE_BY) EXT_CREATE_BY, 
						".$mySQL->convertFromDatetime("EXT_CREATE_DATE")." EXT_CREATE_DATE, 
						(select USERNAME from PRUSER where USERID = a.EXT_UPDATE_BY) EXT_UPDATE_BY,  
						".$mySQL->convertFromDatetime("EXT_UPDATE_DATE")." EXT_UPDATE_DATE
					from FLC_EXTENSION a
					where EXT_ID = ".$_POST['view'];
	$extViewRs = $myQuery->query($extView,'SELECT','NAME');
	$extViewRsCount = count($extViewRs);
}//eof elseif


if(!$_POST['new'] && $_POST['ext_type'])
{
	//get extension
	$extList = "select EXT_ID, EXT_TYPE, EXT_NAME, EXT_TITLE, EXT_DESC, EXT_VERSION, EXT_AUTHOR, EXT_AUTHOR_URL, EXT_ICON, EXT_PATH, EXT_STATUS
					from FLC_EXTENSION 
					where EXT_TYPE = '".$_POST['ext_type']."'
					order by EXT_TYPE, EXT_NAME";
	$extListRs = $myQuery->query($extList,'SELECT','NAME');
	$extListRsCount = count($extListRs);
}//eof if
?>

<?php if(!class_exists('ZipArchive')){showNotificationInfo('Please enable Zip support for PHP!');}?>

<div id="breadcrumbs">System Administrator / Configuration / Extension Editor</div>
<h1>Extension Editor</h1>

<form name="form1" enctype="multipart/form-data" method="post">
<?php if($_POST['new']){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">New <?php echo ucfirst($_POST['ext_type']);?></th>
  </tr>
  <tr>
    <td nowrap="nowrap" class="inputLabel">Package File : </td>
    <td>
      <input id="extFile" name="extFile" type="file" class="inputInput" size="50" />
      <br />
      <label class="labelNote">Note: Please upload the package file (.zip)</label>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="contentButtonFooter">
      <input id="upload" name="upload" type="submit" class="inputButton" value="Upload & Install" onclick="if(!document.getElementById('extFile').value){alert('Please select a Package File to install!'); return false;}" />
      <input id="close" name="close" type="submit" class="inputButton" value="Close" />
      <input id="ext_type" name="ext_type" type="hidden" value="<?php echo $_POST['ext_type'];?>" />
    </td>
  </tr>
</table>
<?php }else if($_POST['view']){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">View Extension</th>
  </tr>
  <tr>
    <td style="padding:15px; width:250px;"><img style="width:200px; height:200px; padding:10px; border:1px solid #bbbbbb" src="<?php echo $extViewRs[0]['EXT_PATH'].'/'.$extViewRs[0]['EXT_ICON'];?>"/></td>
    <td style="padding:15px;">
      <table border="0">
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Type :</td>
            <td style="border:none;"><b><?php echo ucfirst($extViewRs[0]['EXT_TYPE']);?></b></td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Name :</td>
            <td style="border:none;"><?php echo $extViewRs[0]['EXT_NAME'];?></td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Title :</td>
            <td style="border:none;"><?php echo $extViewRs[0]['EXT_TITLE'];?></td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Version :</td>
            <td style="border:none;"><?php echo $extViewRs[0]['EXT_VERSION'];?></td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Description :</td>
            <td style="border:none;"><?php echo $extViewRs[0]['EXT_DESC'];?></td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Folder Path :</td>
            <td style="border:none;"><i><?php echo $extViewRs[0]['EXT_PATH'];?></i></td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Author :</td>
            <td style="border:none;">
              <?php if($extViewRs[0]['EXT_AUTHOR_URL']){?>
				<a href="<?php echo $extViewRs[0]['EXT_AUTHOR_URL'];?>"><?php echo $extViewRs[0]['EXT_AUTHOR'];?></a>
              <?php }else{?>
                <?php echo $extViewRs[0]['EXT_AUTHOR'];?>
              <?php }?>
            </td>
        </tr>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Installed :</td>
            <td style="border:none;"><?php echo $extViewRs[0]['EXT_CREATE_BY'];?> [<?php echo $extViewRs[0]['EXT_CREATE_DATE'];?>]</td>
        </tr>
        <?php if($extViewRs[0]['EXT_UPDATE_BY']){?>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Upgraded :</td>
            <td style="border:none;"><?php echo $extViewRs[0]['EXT_UPDATE_BY'];?> [<?php echo $extViewRs[0]['EXT_UPDATE_DATE'];?>]</td>
        </tr>
        <?php }?>
        <tr>
            <td nowrap="nowrap" style="border:none; text-align:right;">Status :</td>
            <td style="border:none;">
				<?php if($extViewRs[0]['EXT_STATUS']){?><span style="color:green">ENABLED</span><?php }else{?><span style="color:red">DISABLED</span><?php }?>
            </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br />
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">Upgrade Extension</th>
  </tr>
  <tr>
    <td nowrap="nowrap" class="inputLabel">Package File : </td>
    <td>
      <input id="extFile" name="extFile" type="file" class="inputInput" size="50" />
      <br />
      <label class="labelNote">Note: Please upload the package file (.zip)</label>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="contentButtonFooter">
      <input id="upgrade" name="upgrade" type="submit" class="inputButton" value="Upload & Install" onclick="if(!document.getElementById('extFile').value){alert('Please select a Package File to install!'); return false;}" />
      <input id="close" name="close" type="submit" class="inputButton" value="Close" />
      <input id="ext_type" name="ext_type" type="hidden" value="<?php echo $extViewRs[0]['EXT_TYPE'];?>" />
    </td>
  </tr>
</table>
<?php }?>
</form>

<?php if(!$_POST['new'] && !$_POST['view']){?>
<form id="form1" name="form1" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="2">Extension Type </th>
  </tr>
  <tr>
      <td nowrap="nowrap" class="inputLabel">Extension Type : </td>
      <td>
      	<label>
        	<input name="ext_type" type="radio" value="theme" <?php if($_POST['ext_type'] == 'theme'){?> checked="checked"<?php }?> onchange="this.form.submit();" />Theme
        </label>
        <label>
        	<input name="ext_type" type="radio" value="library" <?php if($_POST['ext_type'] == 'library'){?> checked="checked"<?php }?> onchange="this.form.submit();" />Library / Module
        </label>
        <label>
        	<input name="ext_type" type="radio" value="item" <?php if($_POST['ext_type'] == 'item'){?> checked="checked"<?php }?> onchange="this.form.submit();" />Extended Item
        </label>
      </td>
   </tr>
   <tr>
      <td class="contentButtonFooter" colspan="2">
    	<input name="new" type="submit" class="inputButton" id="new" value="New" <?php if(!$_POST['ext_type']){?>disabled="disabled"<?php }?> />
      </td>
   </tr>
</table>
</form>
<br />

<?php if($_POST['ext_type']){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="5"><?php echo ucfirst($_POST['ext_type']);?> List</th>
  </tr>
  <?php if($extListRsCount){?>
  <tr>
    <th width="10" class="listingHead">#</th>
    <th width="30" class="listingHead"></th>
    <th class="listingHead">Details</th>
    <th width="70" class="listingHead">Status</th>
    <th width="70" class="listingHead">Action</th>
  </tr>
  <?php for($x=0; $x<$extListRsCount; $x++){?>
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php } ?> >
    <td class="listingContent"><?php echo $x+1;?>.</td>
    <td class="listingContent">
      <?php 
	  	//icon file
	  	$iconFile = $extListRs[$x]['EXT_PATH'].'/'.$extListRs[$x]['EXT_ICON'];
	  	$iconSize = resizeImageResolution($iconFile,70,70);
	  ?>
      <img src="<?php echo $iconFile;?>" width="<?php echo $iconSize[0];?>" height="<?php echo $iconSize[1];?>" />
    </td>
    <td class="listingContent">
      <form id="formList" name="formList" method="post">
      <b>
		<?php echo $extListRs[$x]['EXT_TITLE'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo $extListRs[$x]['EXT_VERSION'];?>
      </b>
      <br />
      
      <?php echo limitText($extListRs[$x]['EXT_DESC'],250);?><br />
      
      <a href="javascript:void(0)" onclick="jQuery(this).closest('form').submit();">View Detail</a>&nbsp;&nbsp;&nbsp;
      <a href="<?php echo $extListRs[$x]['EXT_PATH'].'/'.$extListRs[$x]['EXT_NAME'].'.zip';?>">Download</a>
      
      <input id="view" name="view" type="hidden" value="<?php echo $extListRs[$x]['EXT_ID'];?>" />
      </form>
    </td>
    <td class="listingContent">
      <form id="formList" name="formList" method="post">
    	<label>
        	<input id="newStatus" name="newStatus" type="checkbox" value="1" <?php if($extListRs[$x]['EXT_STATUS']){?> checked="checked"<?php }?> onchange="this.form.submit();" /><?php if($extListRs[$x]['EXT_STATUS']){echo '<b>Enabled</b>';}else{echo 'Enable';}?>
        </label>
        <input id="changeStatus" name="changeStatus" type="hidden" value="<?php echo $extListRs[$x]['EXT_ID'];?>" />
        <input id="ext_type" name="ext_type" type="hidden" value="<?php echo $_POST['ext_type'];?>" />
      </form>
    </td>
    <td nowrap="nowrap" class="listingContentRight">
      <form id="formList" name="formList" method="post">
        <input id="changeStatus" name="changeStatus" type="hidden" class="inputButton" value="" />
        <input id="delete" name="delete" type="submit" class="inputButton" value="Remove" onClick="if(window.confirm('Are you sure you want to REMOVE this extension?\nThis will UNINSTALL the extension entirely.')) {return true} else {return false}"/>
        <input id="hiddenCode" name="hiddenCode" type="hidden" value="<?php echo $extListRs[$x]['EXT_ID'];?>" />
        <input id="ext_type" name="ext_type" type="hidden" value="<?php echo $_POST['ext_type'];?>" />
      </form>
    </td>
  </tr>
  <?php }?>
  <?php }else{?>
  <tr>
    <td colspan="5" class="myContentInput">&nbsp;&nbsp;No extension(s) found.. </td>
  </tr>
  <?php }?>
  <tr>
    <td class="contentButtonFooter" colspan="5">
      <form id="form2" name="form2" method="post">
    	<input name="new" type="submit" class="inputButton" id="new" value="New" />
        <input id="ext_type" name="ext_type" type="hidden" value="<?php echo $_POST['ext_type'];?>" />
      </form>
    </td>
  </tr>
</table>
<?php }?>
<?php }?>
