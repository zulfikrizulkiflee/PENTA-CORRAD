<?php
require_once "Mail.php";

//if resetPwd
if($_POST['resetPwd'])
{
	//if have additional condition
	if(LOGIN_CONDITION)
		$extraSQL = " and ".LOGIN_CONDITION." ";

	//validate user email
	$validateEmail = "select 1 from PRUSER where USERNAME = '".$_POST['userID']."' and EMAIL = '".$_POST['userEmail']."'".$extraSQL;
	$validateEmailRs = $myQuery->query($validateEmail,'SELECT');
	$validateEmailRsCount = count($validateEmailRs);

	//user and email exist
	if($validateEmailRsCount)
	{
		//random password
		$newPwd = rand(100000,999999);

		//reset password with md5
		$resetPwd = "update PRUSER set USERPASSWORD = '".md5($newPwd)."'
						where USERNAME = '".$_POST['userID']."' and EMAIL = '".$_POST['userEmail']."'".$extraSQL;
		$resetPwdRs = $myQuery->query($resetPwd,'RUN');

		//if successfully reset
		if($resetPwdRs)
		{
			//send email
			$from = "espbiasiswa@mohe.gov.my";
			$to = $_POST['userEmail'];
			$subject = "Set Semula Katalaluan";
			$body = "Tuan/Puan,\nKata laluan anda telah dikemaskini. Maklumat kata laluan baru anda adalah seperti berikut:\n
			Kata laluan baru: ".$newPwd."\n
			URL: http://esp.mohe.gov.my\n\nSekian, terima kasih.";

			$host = "ssl://email.mohe.gov.my";
			$username = "";
			$password = "";

			$headers = array ('From' => $from, 'To' => $to, 'Subject' => $subject);
			$smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => false, 'port'=>'465'));

			$mail = $smtp->send($to, $headers, $body);

			if (PEAR::isError($mail)) {
			//echo("<p>Message unsuccessfully sent!</p>");
			} else {
			//echo("<p>Message successfully sent!</p>");
			}

			//notification
			showNotificationInfo('Kata laluan baru telah dihantar ke email anda. Sila periksa email anda dan <a href="index.php">log masuk</a> untuk menggunakan sistem.',3);
			echo '<script>setTimeout("window.location=\'index.php\';",3000);</script>';
		}//eof if
	}//eof if
	else
		showNotificationError('Ralat! Id Pengguna atau Email yang diberi tidak tepat.',3);
}//eof if
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo APP_FULL_NAME;?></title>
<link href="css/screen.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" type="image/x-icon" href="img/logo.ico">

<script language="javascript" type="text/javascript" src="js/common.js"></script>
</head>
<body onLoad="document.form1.userID.focus();">

<div id="breadcrumbs">Set Semula Kata Laluan</div>
<h1>Set Semula Kata Laluan</h1>

<form method="post" name="form1">
  <table border="0" cellpadding="3" cellspacing="0" class="tableContent">
    <tr>
      <th colspan="2">Maklumat Kata Laluan</th>
    </tr>
    <tr>
      <td class="inputLabel">Id Pengguna : </td>
      <td>
      	<input name="userID" type="text" class="inputInput" id="userID" size="30" value="" autocomplete="off" />
        <label class="labelMandatory">*</label>
      </td>
    </tr>
    <tr>
      <td class="inputLabel">Email : </td>
      <td>
      	<input name="userEmail" type="text" class="inputInput" id="userEmail" size="50" value="" autocomplete="off" />
        <label class="labelMandatory">*</label><br />
        <label class="labelNote">Nota: Kata laluan yang diset semula akan dihantar ke email yang telah didaftarkan ke dalam sistem.</label>
      </td>
    </tr>
    <tr>
      <td class="contentButtonFooter" colspan="2">
        <input name="resetPwd" type="submit" class="inputButton" id="resetPwd" value="Hantar" />
        <input name="cancel" type="button" class="inputButton" value="Batal" onclick="window.location='index.php'" />
      </td>
    </tr>
  </table>
</form>
</body>
</html>