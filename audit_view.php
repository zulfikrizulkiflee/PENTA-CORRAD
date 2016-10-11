<?php
require_once('system_prerequisite.php');

//validate that user have session
validateUserSession();

//====================================== display =============================================\
if($_POST['filterAudit'])
{
	if($_POST['filterDate'])
		$filterDate = " and ".$mySQL->convertFromDate("AUDIT_TIMESTAMP")." = '".$_POST['filterDate']."'";

	if($_POST['filterAction'])
		$filterAction = " and AUDIT_ACTION = '".$_POST['filterAction']."'";

	if($_POST['filterUser'])
		$filterUser = " and USERNAME like '%".$_POST['filterUser']."%'";

	if($_POST['filterControl'])
		$filterControl = " and AUDIT_CONTROL like '%".$_POST['filterControl']."%'";

	//audit list
	$auditList = "select ".$mySQL->convertFromDatetime("AUDIT_TIMESTAMP")." AUDIT_TIMESTAMP, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME,
					".$mySQL->concat("'http://".$_SERVER['HTTP_HOST']."'","AUDIT_REQUEST_URI")." AUDIT_REQUEST_URI, AUDIT_REQUEST_MENU_ID,
					AUDIT_ACTION, AUDIT_CONTROL, USERNAME USER_ID
					from FLC_AUDIT a, PRUSER b
					where a.USER_ID = b.USERID ".$filterDate.$filterAction.$filterUser.$filterControl."
					order by AUDIT_ID desc";
	$auditListRs = $myQuery->query($auditList, 'SELECT', 'NAME');
	$auditListRsCount = count($auditListRs);
}//eof if
//==================================== eof display ===========================================
?>
<script language="javascript" type="text/javascript" src="js/editor.js"></script>

<div id="breadcrumbs">System Administrator / Configuration / Audit Trail</div>
<h1>Audit Trail</h1>

<form id="form1" name="form1" method="post">
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="4">Filter Audit</th>
  </tr>
  <tr>
      <td nowrap="nowrap" class="inputLabel">Date :</td>
      <td>
      	<input id="filterDate" name="filterDate" type="text" class="inputInput" size="10" value="<?php echo $_POST['filterDate'];?>" />
        <?php
		//date format
		$dateFormatDefault = str_replace('format-','',DEFAULT_DATE_FORMAT);

		//if found dash (-)
		if(strpos($dateFormatDefault, '-') !== false)
			$dateFormat = str_replace('-','-ds-',$dateFormatDefault);
		//elseif found slash (/)
		else if(strpos($dateFormatDefault, '/') !== false)
			$dateFormat = str_replace('/','-sl-',$dateFormatDefault);
		?>
        <script>var opts = {formElements:{'filterDate':'<?php echo $dateFormat;?>'}, showWeeks:true, statusFormat:"l-cc-sp-d-sp-F-sp-Y"}; datePickerController.createDatePicker(opts);</script>
      </td>
      <td nowrap="nowrap" class="inputLabel">Action :</td>
      <td>
      	<select name="filterAction" class="inputList" id="filterAction">
           <option value=""></option>
           <option value="login" <?php if($_POST['filterAction'] == 'login'){?> selected="selected"<?php }?>>Login</option>
           <option value="logout" <?php if($_POST['filterAction'] == 'logout'){?> selected="selected"<?php }?>>Logout</option>
           <option value="navigation" <?php if($_POST['filterAction'] == 'navigation'){?> selected="selected"<?php }?>>Navigation</option>
           <option value="submit" <?php if($_POST['filterAction'] == 'submit'){?> selected="selected"<?php }?>>Submit</option>
      	</select>
      </td>
   </tr>
   <tr>
      <td nowrap="nowrap" class="inputLabel">User :</td>
      <td><input id="filterUser" name="filterUser" type="text" class="inputInput" size="30" value="<?php echo $_POST['filterUser'];?>" /></td>
      <td nowrap="nowrap" class="inputLabel">Control Name :</td>
      <td><input id="filterControl" name="filterControl" type="text" class="inputInput" size="30" value="<?php echo $_POST['filterControl'];?>" /></td>
   </tr>
   <tr>
      <td class="contentButtonFooter" colspan="4">
    	<input name="filterAudit" type="submit" class="inputButton" id="filterAudit" value="Search" />
      </td>
   </tr>
</table>
</form>
<br />

<?php if($_POST['filterAudit']){?>
<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent">
  <tr>
    <th colspan="10">Audit List</th>
  </tr>
  <tr>
    <th width="15" class="listingHead">#</th>
    <th class="listingHead">Timestamp</th>
    <th class="listingHead">IP Address</th>
    <th class="listingHead">PC Name</th>
    <th class="listingHead">URL</th>
    <th class="listingHead">Menu ID</th>
    <th class="listingHead">Action</th>
    <th class="listingHead">Control Name</th>
    <th class="listingHead">User</th>
  </tr>
   <?php if($auditListRsCount>0) { ?>
  <?php for($x=0; $x<$auditListRsCount; $x++) { ?>
  <tr onmouseover="this.style.background = '#FFFFCC'" <?php if($x%2 == 1) { ?>style="background-color:#F7F7F7;" onmouseout="this.style.background = '#F7F7F7'"<?php } else { ?>onmouseout="this.style.background = '#ffffff'"<?php }?> >
    <td class="listingContent"><?php echo $x+1;?>.</td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_TIMESTAMP'];?></td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_CLIENT_IP'];?></td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_CLIENT_PC_NAME'];?></td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_REQUEST_URI'];?></td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_REQUEST_MENU_ID'];?></td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_ACTION'];?></td>
    <td class="listingContent"><?php echo $auditListRs[$x]['AUDIT_CONTROL'];?></td>
    <td class="listingContentRight"><?php echo $auditListRs[$x]['USER_ID'];?></td>
  </tr>
  <?php } //end for ?>
  <?php }//end if
    else { ?>
  <tr>
    <td colspan="10" class="myContentInput">
        No audit(s) found...
    </td>
  </tr>
  <?php } //end else?>
</table>
<?php }?>
