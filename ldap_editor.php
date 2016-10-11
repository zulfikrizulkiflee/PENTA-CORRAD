<?php
require_once('system_prerequisite.php');
error_reporting(0); 	//Hide All Error

//POST [START]
$conn 		= $_POST['conn'];
$port 		= $_POST['port'];
$version	= $_POST['version'];
$referral	= $_POST['referral'];
$dn 		= $_POST['dn'];
$sLimit 	= $_POST['sLimit'];
$fEx 		= $_POST['fEx'];
$attr 		= $_POST['attr'];
$user		= $_POST['user'];
$password	= $_POST['password'];
$pImp 		= ($_POST['import'] == '') ? 'false' : $_POST['import'] ;
//POST [END]

function encrypt_decrypt($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'AD secret key';
    $secret_iv = 'AD secret iv';

    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' ){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

//DB Query Section [START]
$adInfo 		= "select * from FLC_LDAP";
$adInfoRs 		= $myQuery->query($adInfo,'SELECT','NAME');

$adInfoRsCount 	= count($adInfoRs);
$ldap_host 		= $adInfoRs[0]['LDAP_CONN'];
$ldap_port 		= $adInfoRs[0]['LDAP_PORT'];
$ldap_version 	= $adInfoRs[0]['LDAP_VERSION'];
$ldap_referral 	= $adInfoRs[0]['LDAP_REFERRAL'];
$ldap_dn 		= $adInfoRs[0]['LDAP_DN'];
$ldap_limit 	= $adInfoRs[0]['LDAP_LIMIT'];
$ldap_exclude 	= $adInfoRs[0]['LDAP_EXCLUDE'];
$ldap_attr 		= $adInfoRs[0]['LDAP_ATTR'];
$ldap_user  	= $adInfoRs[0]['LDAP_USER'];
$ldap_pass 		= $adInfoRs[0]['LDAP_PASSWORD'];

//$ldap_pass2		= md5($adInfoRs[0]['LDAP_PASSWORD']);
//DB Query Section [END]

//Logical Section [START]
if($password == $ldap_pass)
{	$ldap_password 	= encrypt_decrypt('decrypt', $ldap_pass);}
else
{	$ldap_password 	= $password;}

$ldap_password1 = encrypt_decrypt('encrypt', $ldap_password);

//If button 'CONNECT' or 'TEST CONNECTION' Clicked
if($_POST['bconn']||$_POST['btconn']||$_POST['import'])
{
	$connect = 	ldap_connect($conn, $port);
				ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, $version);
				ldap_set_option($connect, LDAP_OPT_REFERRALS, $referral);

	//Test Connection START
	$bind = ldap_bind($connect, $user, $ldap_password);
	if ($bind)
		{
		if($_POST['btconn'])
			{	echo "<script>alert('Connected successfully to LDAP!')</script>";}
		else{	$genImp = true;} //Hardcoded for Import Validation purpose
		}
	else
		{	echo "<script>alert('Connection Failed. Please check your configuration settings')</script>";	$genImp = false; }
	//Test Connection END

	//If button Connect clicked
	if($_POST['bconn']||$_POST['import'])
	{
		if(($genImp))
		{

			//Get USERNAME from DB [START]
			$adPruser 			= "SELECT USERNAME FROM PRUSER";
			$adPruserRs 		= $myQuery->query($adPruser,'SELECT','NAME');
			$adPruserRsCount 	= count($adPruserRs);

			if(!empty($fEx))
				$xExc = explode(';',$fEx);
				for($xA=0;$xA<count($xExc);$xA++){ $xB[] = '(!('.$xExc[$xA].'))'; }

			//print_r($xB);
			$iEx = implode('',$xB);

			for ($b=0; $b<$adPruserRsCount; $b++)
			{	$userDb[] = "(!($attr=".$adPruserRs[$b]["USERNAME"]."))";}
			//Get USERNAME from DB [END]

			$xAttr = implode('',$userDb);
			$filter = "(&(".$attr."=*)".$xAttr.$iEx.")";
			$read = ldap_search($connect, $dn, $filter);
			$info = ldap_get_entries($connect, $read);

			//Get ATTR from LDAP [START]
			for ($a=0; $a<$info["count"]; $a++)
			{	$userLdap[] = $info[$a][$attr][0];}
			//Get ATTR from LDAP [END]

			ldap_close($connect);

			//Find Different between user LDAP and DB [START]
			$listUser = array_values(array_filter($userLdap)); //Import ke PRUSER
			$CntListUser = count($listUser);
			//Find Different between user LDAP and DB [END]
		}
	}
}

//If button 'SAVE' Clicked
if($_POST['bsave'])
{
		if($adInfoRsCount == "1")
		{
			$updateLdap =	"UPDATE FLC_LDAP SET
								LDAP_CONN 	= '$conn',
								LDAP_PORT 	= '$port',
								LDAP_DN 		= '$dn',
								LDAP_REFERRAL = '$referral',
								LDAP_VERSION 	= '$version',
								LDAP_LIMIT 	= '$sLimit',
								LDAP_EXCLUDE 	= '$fEx',
								LDAP_USER 	= '$user',
								LDAP_PASSWORD = '$ldap_password1',
								LDAP_ATTR 	= '$attr'";
			$updateLdaprs 	 = $myQuery->query($updateLdap, 'RUN');
		}
		else
		{
			$insertLdap =	"INSERT INTO FLC_LDAP
								(LDAP_CONN, LDAP_PORT, LDAP_DN, LDAP_REFERRAL, LDAP_VERSION, LDAP_USER, LDAP_PASSWORD, LDAP_ATTR, LDAP_LIMIT, LDAP_EXCLUDE)
							 VALUES
								('$conn', '$port', '$dn', '$referral', '$version', '$user', 'ldap_password1','$attr', '$sLimit', '$fEx')";
			$insertLdaprs 	 = $myQuery->query($insertLdap, 'RUN');
		}
		echo
		"<script>alert('Configuration updated successfully')</script>";
}
//If button 'Import' Clicked
if($_POST['import'])
{
	if( isset($_POST['chkImp']) && is_array($_POST['chkImp']) )
	{
		foreach($_POST['chkImp'] as $chkImp)
		{
			$ldapImpCnt 	= "SELECT COUNT(USERID) AS CSERID FROM PRUSER WHERE USERNAME = '$chkImp'";
			$ldapImpCntRs 	= $myQuery->query($ldapImpCnt,'SELECT','NAME');
			$ldapCntUsr		= $ldapImpCntRs[0]['CSERID'];

				if($ldapCntUsr == 0)
				{
					$ldapImpSel 	= "SELECT MAX(USERID) AS MUSERID FROM PRUSER";
					$ldapImpSelRs 	= $myQuery->query($ldapImpSel,'SELECT','NAME');
					$ldapImpMax		= $ldapImpSelRs[0]['MUSERID'];

					$ldapImpIns 	= "INSERT INTO PRUSER(USERID, USERNAME, USERPASSWORD, STATUSCODE) VALUES ($ldapImpMax+1, '$chkImp', '202cb962ac59075b964b07152d234b70', '')";
					$ldapImpInsRs 	= $myQuery->query($ldapImpIns,'RUN');
				}
		}
		echo "<script>alert('User successfully Import')</script>";
	}
	else
		{echo "<script>alert('Nothing to be Import')</script>";}
}
?>

<script language="javascript" type="text/javascript" src="tools/jquery.js"></script>
<script type="text/javascript">jQuery = jQuery.noConflict();</script>
<script type="text/javascript">
//Check UnCheck Button
jQuery(document).ready(function(){
   jQuery('input[name="Check_All"]').click(function(){
      jQuery('input[name^="chkImp"]').attr('checked', true);
   });
   jQuery('input[name="Un_CheckAll"]').click(function(){
      jQuery('input[name^="chkImp"]').attr('checked', false);
   });
});
</script>

<div id="breadcrumbs">System Administrator / Configuration / LDAP Editor</div>
<h1>LDAP Editor </h1>
	<form method="post">
	<!-- Connection Form START -->
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="<?php if(!($_POST['bconn'] || $_POST['import']) || ($genImp != 1)){echo '';}else{echo 'display:none';}?>">
		<tr>
			<th colspan="2">LDAP Configuration </th>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Connection :</td>
			<td valign="top"><input id="conn" name="conn" type="text" size="50" value=<?php if(empty($conn)){echo $ldap_host;}else{echo $conn;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Port :</td>
			<td valign="top"><input id="port" name="port" type="text" size="10" value=<?php if(empty($port)){echo $ldap_port;}else{echo $port;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Protocol Version :</td>
			<td valign="top"><input id="version" name="version" type="text" size="10" value=<?php if(empty($version)){echo $ldap_version;}else{echo $version;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Referrals :</td>
			<td valign="top"><input id="referral" name="referral" type="text" size="10" value=<?php if(empty($referral)){echo $ldap_referral;}else{echo $referral;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Base DN :</td>
			<td valign="top"><input id="dn" name="dn" type="text" size="50" value=<?php if(empty($dn)){echo $ldap_dn;}else{echo $dn;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">AD Server Limit :</td>
			<td valign="top"><input id="sLimit" name="sLimit" type="text" size="10" value=<?php if(empty($sLimit)){echo $ldap_limit;}else{echo $sLimit;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Filter Exclude :</td>
			<td valign="top"><input id="fEx" name="fEx" type="text" size="50" value=<?php if(empty($fEx)){echo $ldap_exclude;}else{echo $fEx;} ?>>* separate by semicolon if more ( ; )</td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Attribute :</td>
			<td valign="top"><input id="attr" name="attr" type="text" size="50" value=<?php if(empty($attr)){echo $ldap_attr;}else{echo $attr;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Username : </td>
			<td valign="top"><input id="user" name="user" type="text" size="50" value=<?php if(empty($user)){echo $ldap_user;}else{echo $user;} ?>></td>
		</tr>
		<tr>
			<td nowrap="nowrap" class="inputLabel">Password :</td>
			<td valign="top"><input id="password" name="password" type="password" size="50" autocomplete="off" value=<?php 	if(!empty($password)){echo $password;} elseif(!empty($ldap_pass)){echo $ldap_pass;} else{echo '';}?>></td>
		</tr>
		<tr>
			<td colspan="9" class="contentButtonFooter">
				<input id="btconn" name="btconn" type="submit" class="inputButton" value="Test Connection">
				<input id="bsave" name="bsave" type="submit" class="inputButton" value="Save Configuration">
				<input id="bconn" name="bconn" type="submit" class="inputButton" value="Import User to Corrad">
			</td>
		</tr>
		</table>
	<!-- Connection Form END -->
	<!-- Import Form START-->
		<table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="<?php if((($genImp==1)&&($_POST['bconn']))||(isset($_POST["import"]) && !empty($_POST["import"]))){echo '';}else{echo 'display:none';}?>">
		<tr>
			<th colspan="9" style="padding:0px 2px 0px 10px;margin:0px;line-height:35px;">
				<div style="float:left">Import User</div>
				<div style="float:right">
					<input id="back" name="back" type="button" class="inputButton" onclick="history.back()" value="Back">
					<input id="gotoBottom" name="gotoBottom" type="button" class="inputButton" value="Go To Bottom" onclick="jQuery('html, body').animate({ scrollTop: jQuery(document).height() }, 1000);">
					<input id="Check_All" name="Check_All" type="button" class="inputButton" value="Check All" >
					<input id="Un_CheckAll" name="Un_CheckAll" type="button" class="inputButton" value="Uncheck All" >
					<input id="import" name="import" type="submit" class="inputButton" value="Import">
				</div>
			</th>
		</tr>
		<tr>
			<th nowrap="nowrap" class="listingHead">#</th>
			<th nowrap="nowrap" class="listingHead">New User (From LDAP)</th>
			<th nowrap="nowrap" class="listingHead">Import</th>
		</tr>
		<?php
		//Loop User into Table Import [START]
		for ($c=0; $c<$CntListUser;$c++)
		{
			$d = $c+1;
			echo "
			<tr>
				<td>$d.</td>
				<td nowrap=nowrap class=inputLabel>$listUser[$c]</td>
				<td nowrap=nowrap class=inputLabel><input id=$listUser[$c] value=$listUser[$c]  name='chkImp[]' type='checkbox'></td>
			</tr>";
		}
		//Loop User into Table Import [END]
		?>
		<tr>
			<td colspan="9" class="contentButtonFooter">
				<input id="back" name="back" type="button" class="inputButton" onclick="history.back();" value="Back">
				<input id="gotoBottom" name="gotoBottom" type="button" class="inputButton" value="Go To Top" onclick="jQuery('html, body').animate({ scrollTop: 0 }, 1000);">
				<input id="Check_All" name="Check_All" type="button" class="inputButton" value="Check All">
				<input id="Un_CheckAll" name="Un_CheckAll" type="button" class="inputButton" value="Uncheck All">
				<input id="import" name="import" type="submit" class="inputButton" value="Import">
			</td>
		</tr>
		</table>
	<!-- Import Form END -->
	</form>
