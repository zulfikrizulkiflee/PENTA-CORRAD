<?php
require_once('system_prerequisite.php');

//validate that user have session
validateUserSession();

//==================================== manipulation ==========================================
//insert/update => check if api_name exist
if($_POST['insert']||$_POST['update'])
{
	//if update
	if($_POST['update'])
		$constraintAPIId = " and API_ID != ".$_POST['api_id'];
	
	if($_POST['api_url'] != '' || $_POST['api_url'] != null)
		$constraintURL = "OR API_URL='".strtoupper($_POST['api_url'])."'";
		
	//select same api_name
	$chkExist = "select API_NAME from FLC_API where API_NAME = '".strtoupper($_POST['api_name'])."'".$constraintAPIId;
	$chkExistRs = $myQuery->query($chkExist,'SELECT','INDEX');
	$apiNameExist = $chkExistRs[0][0];
	
	//select same api_url
	$chkURLExist = "select API_URL from FLC_API where API_URL = '".strtoupper($_POST['api_url'])."'".$constraintAPIId;
	$chkURLExistRs = $myQuery->query($chkURLExist,'SELECT','INDEX');
	$URLexist =  $chkURLExistRs[0][0];
}//eof if

//if insert
if($_POST['insert'])
{
	//if api_name not exist
	if(!$apiNameExist)
	{
		//if url not exist
		if(!$URLexist)
		{		
			//max id
			$maxAPIIdRs = $mySQL->maxValue('FLC_API','API_ID',0) + 1;
			
			//insert api
			$insertAPI = "insert into FLC_API (API_ID, API_NAME, API_DESC, API_URL, API_RETURN_FORMAT, API_BL, API_STATUS)
						  values (".$maxAPIIdRs.", '".$_POST['api_name']."', '".$_POST['api_desc']."', '".$_POST['api_url']."', 
								'".$_POST['api_return_format']."', '".$_POST['api_bl']."', ".$_POST['api_status'].")";
			$insertAPIRs = $myQuery->query($insertAPI, 'RUN');

			//if inserted
			if($insertAPIRs)
			{
				//loop on count of key
				$keyCount = count($_POST['key']);
				for($x=0; $x < $keyCount; $x++)
				{
					//if have value in domain and key
					if($_POST['domain'][$x] || $_POST['key'][$x])
					{
						//max id
						$maxKeyIdRs = $mySQL->maxValue('FLC_API_PERMISSION','API_PERM_ID',0) + 1;

						//add permission
						$insertKey = "insert into FLC_API_PERMISSION (API_PERM_ID, API_PERM_DOMAIN, API_PERM_KEY, API_ID)
										values (".$maxKeyIdRs.",'".$_POST['domain'][$x]."', '".$_POST['key'][$x]."', ".$maxAPIIdRs.")";
						$insertKeyRs = $myQuery->query($insertKey, 'RUN');
					}//eof if
				}//eof for
		
				//notification
				showNotificationInfo('New API has been added.');
			}//eof if
			else
			{
				//notification
				showNotificationError('Fail to add new API!');
			}//eof else
		}//eof if url exist
		else{
			showNotificationError('API with same url already exist. Insert fail!');
		}
	}//eof if
	//if same name exist
	else
	{
		//notification
		showNotificationError('API with same name already exist. Insert fail!');
	}//eof else
}//eof if

//if update
else if($_POST['update'])
{	
	//if apiname not exist
	if(!$apiNameExist)
	{	
		//if url not exist
		if(!$URLexist)
		{
			//update api
			$updateAPI = "update FLC_API 
								set API_NAME = '".$_POST['api_name']."', 
									API_DESC = '".$_POST['api_desc']."', 
									API_URL = '".$_POST['api_url']."', 
									API_RETURN_FORMAT = '".$_POST['api_return_format']."', 
									API_BL = '".$_POST['api_bl']."', 
									API_STATUS = ".$_POST['api_status']."
								where API_ID = ".$_POST['api_id'];
			$updateAPIRs = $myQuery->query($updateAPI, 'RUN');
			
			//if updated
			if($updateAPIRs)
			{
				//delete previous key
				$deleteKey = "delete from FLC_API_PERMISSION where API_ID = ".$_POST['api_id'];
				$deleteKeyRs = $myQuery->query($deleteKey,'RUN');

				//loop on count of key
				$keyCount = count($_POST['key']);
				for($x=0; $x<$keyCount; $x++)
				{
					//if have value in domain and key
					if($_POST['domain'][$x] || $_POST['key'][$x])
					{
						//max id
						$maxKeyIdRs = $mySQL->maxValue('FLC_API_PERMISSION','API_PERM_ID',0) + 1;

						//add permission
						$insertKey = "insert into FLC_API_PERMISSION (API_PERM_ID, API_PERM_DOMAIN, API_PERM_KEY, API_ID)
										values (".$maxKeyIdRs.",'".$_POST['domain'][$x]."', '".$_POST['key'][$x]."', ".$_POST['api_id'].")";
						$insertKeyRs = $myQuery->query($insertKey, 'RUN');
					}//eof if
				}//eof for
				
				//notification
				showNotificationInfo('API has been updated.');
			}//eof if
			else
			{
				//notification
				showNotificationError('Fail to update API!');
			}//eof else
		}//eof if url exist
		else{
			//notification
			showNotificationError('API with same name already exist. Insert fail!');
		}
	}//eof if
	//if same name exist
	else
	{
		//notification
		showNotificationError('API with same name already exist. Update fail!');
	}//eof else
}//eof if

//if delete
else if($_POST['delete'])
{
	//delete api
	$deleteAPI = "delete from FLC_API where API_ID = ".$_POST['api_id'];
	$deleteAPIRs = $myQuery->query($deleteAPI, 'RUN');
	
	//if delete
	if($deleteAPIRs)
	{
		//notification
		showNotificationInfo('API has been deleted.');
	}//eof if
	else
	{
		//notification
		showNotificationError('Fail to delete API!');
	}//eof else
}//eof if
//================================== eof manipulation ========================================

//====================================== display =============================================
//api listing, if type selected and not in add/edit mode
if(!$_POST['new'] && !$_POST['edit'])
{
	//search/filter api
	$_GET['name'] = trim(str_replace('Search here..','',$_GET['name']));
	
	//filter title
	if($_GET['name']) $filterName = " where upper(a.API_NAME) like upper('%".$_GET['name']."%') ";
	
	//api list
	$apiList = "select a.API_ID, a.API_NAME, a.API_BL, a.API_RETURN_FORMAT, a.API_STATUS, 
					(select count(API_PERM_DOMAIN) from FLC_API_PERMISSION 
						where API_PERM_DOMAIN != '' and API_PERM_DOMAIN is not null and API_ID = a.API_ID) API_DOMAIN,
					(select count(API_PERM_KEY) from FLC_API_PERMISSION 
						where API_PERM_KEY != '' and API_PERM_KEY is not null and API_ID = a.API_ID) API_KEY
				from FLC_API a
				".$filterName."
				order by API_NAME";
	$apiListRs = $myQuery->query($apiList, 'SELECT', 'NAME');
	$apiListRsCount = count($apiListRs);

	//get url for bl editor
	$getBlUrl = "select ".$mySQL->concat("MENULINK","'&menuID='",$mySQL->convertToChar("MENUID"))." MENULINK from FLC_MENU where MENULINK like '%index.php?page=bl_editor%'";
	$getBlUrlRs = $myQuery->query($getBlUrl, 'SELECT', 'NAME');
}//eof if

else if($_POST['new'] || $_POST['edit'])
{
	//php bl trigger
	$getBLPhpRs = $mySQL->listActivePhpBL();
	$getBLPhpRsCount = count($getBLPhpRs);

	//include class Table
	require_once('class/Table.php');					//class Table
	
	//==================== DECLARATION =======================
	$tg = new TableGrid('100%',0,0,0);					//set object for table class (width,border,celspacing,cellpadding)
	
	//set attribute of table
	$tg->setAttribute('class','tableContent');				//set id
	$tg->setHeader('API Permission');						//set header
	$tg->setKeysStatus(true);							//use of keys (column header)
	$tg->setKeysAttribute('class','listingHead');		//set class
	$tg->setRunningStatus(true);						//set status of running number
	$tg->setRunningKeys('No');							//key / label for running number
	
	//set attribute of column in table
	$column = new Column();								//set object for column
	$column->setAttribute('class','listingContent');	//set attribute for table
	$tg->setColumn($column);							//insert/set class column into table
	
	//status add/delete row
	$tg->setAddRowStatus(true);
	$tg->setDelRowStatus(true);
	$tg->setAddRowType('ajax');
	
	//value add/delete row
	$tg->setAddRowValue('Add Row');
	$tg->setDelRowValue('Delete Row');
	
	//class
	$tg->setAddRowClass('inputButton');
	$tg->setDelRowClass('inputButton');
	//================== END DECLARATION =====================
}

//if edit
if($_POST['edit'])
{
	//get api info
	$api = "select API_ID, API_NAME, API_DESC, API_URL, API_RETURN_FORMAT, API_BL, API_STATUS 
			from FLC_API where API_ID = ".$_POST['api_id'];
	$apiRs = $myQuery->query($api,'SELECT','NAME');
	
	//variable assignment
	$api_id = $apiRs[0]['API_ID'];
	$api_name = $apiRs[0]['API_NAME'];
	$api_desc = $apiRs[0]['API_DESC'];
	$api_url = $apiRs[0]['API_URL'];
	$api_return_format = $apiRs[0]['API_RETURN_FORMAT'];
	$api_bl = $apiRs[0]['API_BL'];
	$api_status = $apiRs[0]['API_STATUS'];

	//get api permission
	$getPerm = "select API_PERM_DOMAIN, API_PERM_KEY from FLC_API_PERMISSION where API_ID = ".$_POST['api_id'];
	$getPermRs = $myQuery->query($getPerm,'SELECT','NAME');
	$getPermRsCount = count($getPermRs);
}//eof if
//==================================== eof display ===========================================
?>
<script language="javascript" type="text/javascript" src="js/editor.js"></script>
<script>
//to filter api editor's list 
function filterAPIEditorList(name)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="api_editor.php"
	url=url+"?filter_api=true&name="+name
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{ 
			document.getElementById("api_editor_list").innerHTML=xmlHttp.responseText 
		} 
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

function GetXmlHttpObject()
{
	var xmlHttp=null;
	try{
		// Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e){
		//Internet Explorer
		try{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e){
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}
</script>


<?php
//if ajax filter
if(!isset($_GET['filter_api'])){
?>
<div id="breadcrumbs">System Administrator / Configuration / API Editor</div>
<h1>API Editor</h1> 
<?php }?>

<?php if(!$_POST['new'] && !$_POST['edit']){?>

<div id="api_editor_list">
	<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="9">API List</th>
		</tr>
		<tr>
			<th width="15" class="listingHead">#</th>
			<th width="15" class="listingHead">ID</th>
			<th class="listingHead">Name<br><input type="text" class="inputInput" name="filter_name" id="filter_name" value="<?php if($_GET['name']) echo $_GET['name']; else echo ' Search here..';?>" onfocus="if(this.value == ' Search here..') this.value= ''; " onkeydown="if(event.keyCode == 13) this.onblur();" onblur="if(this.value == '') {this.value = ' Search here..';} filterAPIEditorList(this.value);" size="20" style="color:#7F7F7F;width:99%;" /></th>
			<th width="200" class="listingHead">BL</th>
			<th class="listingHead">Format</th>
			<th class="listingHead">Domain</th>
			<th class="listingHead">Key</th>
			<th class="listingHead">Status</th>
			<th width="85" class="listingHead">Action</th>
		</tr>
		<?php if($apiListRsCount>0) { ?>
		<?php for($x=0; $x<$apiListRsCount; $x++) { ?>
		<tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php }?> >
			<td class="listingContent"><?php echo $x+1;?>.</td>
			<td class="listingContent"><?php echo $apiListRs[$x]['API_ID'];?></td>
			<td class="listingContent"><?php echo $apiListRs[$x]['API_NAME'];?></td>
			<td class="listingContent">
				<a href="<?php echo $getBlUrlRs[0]['MENULINK'].'&blname='.$apiListRs[$x]['API_BL'];?>" target="_blank"><?php echo $apiListRs[$x]['API_BL'];?></a>
			</td>
			<td class="listingContent"><?php echo $apiListRs[$x]['API_RETURN_FORMAT'];?></td>
			<td class="listingContent"><?php if($apiListRs[$x]['API_DOMAIN']) echo 'Yes';else echo 'No';?></td>
			<td class="listingContent"><?php if($apiListRs[$x]['API_KEY']) echo 'Yes';else echo 'No';?></td>
			<td class="listingContent"><?php if($apiListRs[$x]['API_STATUS']) echo 'Active';else echo 'Inactive'?></td>
			<td nowrap="nowrap" class="listingContentRight">
				<form id="formDetail" name="formDetail" method="post">
				<input name="edit" type="submit" class="inputButton" id="edit" value="Update" />
				<input name="delete" type="submit" class="inputButton" id="delete" value="Delete" onClick="if(window.confirm('Are you sure you want to DELETE this API?')) {return true} else {return false}"/>
				<input name="api_id" type="hidden" id="api_id" value="<?php echo $apiListRs[$x]['API_ID'];?>" />
				</form>
			</td>
		</tr>
		<?php } //end for ?>
		<?php }//end if 
		else { ?>
		<tr>
			<td colspan="9" class="myContentInput">
			No API(s) found... 
			</td>
		</tr>
		<?php } //end else?>
		<tr>
			<td colspan="9" class="contentButtonFooter">
				<form id="form2" name="form2" method="post">
					<input type="hidden" name="api_type" id="api_type" value="<?php echo $_POST['api_type'];?>" />
					<input name="new" type="submit" class="inputButton" id="new" value="New" />
				</form>
			</td>
		</tr>
	</table>
</div>
<br />
<?php }?>
<?php if($_POST['new']||$_POST['edit']){?>
<form id="form1" name="form1" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<tr>
		<th colspan="2"><?php if($_POST['new']){?>New<?php }else if($_POST['edit']){?>Edit<?php }?> API</th>
	</tr>
	<tr>
		<td class="inputLabel" nowrap="nowrap">Name :<?php echo $URLexist; ?> </td>
		<td>
			<input name="api_name" id="api_name" type="text" class="inputInput" value="<?php echo $api_name;?>" size="50" onchange="trim(this);" />
			<input type="hidden" name="api_id" id="api_id" value="<?php echo $api_id;?>" />
			<label class="labelMandatory">*</label>
			<script>document.getElementById('api_name').focus();</script>
		</td>
	</tr>
	<tr>
		<td class="inputLabel" nowrap="nowrap">Description : </td>
		<td>
			<textarea name="api_desc" id="api_desc" cols="50" rows="3" class="inputInput"><?php echo $api_desc;?></textarea>
		</td>
	</tr>
	<tr>
	<td class="inputLabel" nowrap="nowrap">URL : </td>
		<td>
			<input name="api_url" id="api_url" type="text" class="inputInput" value="<?php echo $api_url;?>" size="50" onchange="trim(this);" /><!--label class="labelMandatory">*</label-->
		</td>
	</tr>
	<tr>
		<td class="inputLabel" nowrap="nowrap">Return Format: </td>
		<td>
			<select name="api_return_format" class="inputList" id="api_return_format">
				<option value=""></option>
				<option value="XML" <?php if($api_return_format == 'XML'){?> selected <?php }?>>XML</option>
				<option value="JSON" <?php if($api_return_format == 'JSON'){?> selected <?php }?>>JSON</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="inputLabel" nowrap="nowrap">BL: </td>
		<td>
			<select name="api_bl" class="inputList" id="api_bl">
				<option value=""></option>
				<?php for($x=0; $x<$getBLPhpRsCount; $x++){?>
				<option value="<?php echo $getBLPhpRs[$x][0];?>" <?php if($api_bl == $getBLPhpRs[$x][0]){?> selected <?php }?>><?php echo $getBLPhpRs[$x][0];?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="inputLabel" nowrap="nowrap">Status: </td>
		<td>
			<select name="api_status" class="inputList" id="api_status">
				<option value="1" <?php if($api_status == '1'){?> selected <?php }?>>Active</option>
				<option value="0" <?php if($api_status == '0'){?> selected <?php }?>>Inactive</option>
			</select>
		</td>
	</tr>
</table>
<br>

<?php
/* ======== TABLEGRID FOR PARAMETER ======== */

//set column
for($x=0; $x < $getPermRsCount+1; $x++)
{
	$param[$x]['Domain']='<input name="domain[]" type="text" class="inputInput" value="'.$getPermRs[$x]['API_PERM_DOMAIN'].'" size="50" />';
	$param[$x]['Key']='<input name="key[]" type="text" class="inputInput" value="'.$getPermRs[$x]['API_PERM_KEY'].'" size="50" onchange="trim(this);" />
						<input name="keygen[]" type="button" value="Generate"  onClick="jQuery(this).siblings().val(randomCharacter(7));" />';
	$param[$x]['Delete']='<input name="deletePerm[]" type="checkbox" value="1" onchange="var permCount=(document.getElementsByName(\'deletePerm[]\')).length;for(x=0;x<permCount;x++){if((document.getElementsByName(\'deletePerm[]\'))[x].checked){(document.getElementsByName(\'domain[]\'))[x].disabled=true;(document.getElementsByName(\'key[]\'))[x].disabled=true;(document.getElementsByName(\'keygen[]\'))[x].disabled=true;} else {(document.getElementsByName(\'domain[]\'))[x].disabled=false;(document.getElementsByName(\'key[]\'))[x].disabled=false;(document.getElementsByName(\'keygen[]\'))[x].disabled=false;}}" />';
}//eof for

//header
$headerCount=count($param[0]);
$tg->setHeaderAttribute('colspan',$headerCount);			//set colspan for header

//put data into tablegrid
$tg->setTableGridData($param);

//display table grid
$tg->showTableGrid();
/* ====== EOF TABLEGRID FOR PARAMETER ====== */
?>
<br />

<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
	<tr>
		<td class="contentButtonFooter" align="right">
			<?php if($_POST['new']){$btnName = 'insert';}else{$btnName = 'update';}?>
			<input name="<?php echo $btnName;?>" id="<?php echo $btnName;?>" type="submit" class="inputButton" value="Save" />
			<input name="close" type="submit" class="inputButton" value="Close" />
		</td>
	</tr>
</table>

</form>
<?php }?>
