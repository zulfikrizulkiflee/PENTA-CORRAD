<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title><?php echo APP_FULL_NAME;?></title>
		<link href="css/screen.css" rel="stylesheet" type="text/css">
        <link rel="shortcut icon" type="image/x-icon" href="img/logo.ico">
	</head>
	<body onLoad="document.form1.userID.focus();" id="loginBody">
		<form id="form1" name="form1" method="post">
			<div id="loginError"><?php if($error) echo $error; ?></div>
			<div id="loginHeader"><img src="images/logo.svg" width="200px" height="200px"><br><?php echo APP_FULL_NAME;?></div>
			<div id="loginShadow">
				<div id="loginScreen">
				  <?php if($_SESSION["SYSTEM_INFO"]["LOGIN_IMAGE_PATH"]) {?>
				  <div style="background-image:url(<?php echo $_SESSION["SYSTEM_INFO"]["LOGIN_IMAGE_PATH"]?>); background-repeat:no-repeat; background-color:#00CCCC"> </div>
				  <?php } ?>
				  <div class="sideLabel">
					<label>Nama Pengguna</label>
					<input name="userID" type="text" class="inputInput" id="userID" autocomplete="off" />
				  </div>
				  <div class="sideLabel">
					<label>Kata Laluan</label>
					<input name="userPassword" type="password" class="inputInput" id="userPassword" />
				  </div>
                  <?php if(LOGIN_CAPTCHA_ENABLED){?>
                  <div class="sideLabel">
                    <div style="background-color:#FFF; border:1px solid #CCCCCC;">
                    <label>Masukkan Kod di Bawah</label><br />
                    <center>
                        <img src="captcha.php" id="captcha" /><br/>
                        <!-- CHANGE TEXT LINK -->
                        <a href="#" onclick="
                            document.getElementById('captcha').src='captcha.php?'+Math.random();
                            document.getElementById('userCaptcha').focus();"
                            id="change-image">Kurang jelas? Tukar perkataan</a><br/><br/>
                        <input name="userCaptcha" type="text" class="inputInput" id="userCaptcha" style="width:90%;" />
                    </center><br />
                    </div>
				  </div>
                  <?php }?>
				  <div>
					<center><input name="login" type="submit" class="inputButton" id="login" value="Masuk" style="margin-top:8px;"
                                   onclick="if(form1.userID.value != '' && form1.userPassword.value != '') {return true;} else {alert('<?php echo LOGIN_ERROR_MSG; ?>'); form1.userID.focus(); return false; }" /></center>
				  </div>
				</div>
			</div>
			<div id="loginFalconCredit"><?php echo LOGIN_CREDIT; ?></div>
			<div id="loginFalconCredit">
				<noscript style="color:#FF0000">
				<?php echo JAVASCRIPT_NOT_ENABLED_ERR; ?>
				</noscript>
			</div>
		</form>
	</body>
</html>
