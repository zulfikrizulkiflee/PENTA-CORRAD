<?php
/**
*  DESCRIPTION: To generate captcha code validation (currently using cool-php-captcha library)
*  COPYRIGHT: ESRA TECHNOLOGY SDN BHD
*  AUTHOR: MOHDFAISALIMRAN
*/

//prerequisite
require_once('system_prerequisite.php');

//captcha library (currently using cool-php-captcha)
require_once('tools/captcha/captcha.php');

//decode if encoder is enabled
if(URL_SECURITY)
{
	//decode and assign the GET parameter
	stringTo_GET(flc_url_decode($_GET['a']));
}//eof if

//declare captcha object
$captcha = new SimpleCaptcha();

//======= PARAMETERS =======
//parameter to alter captcha variables (if no parameter, use default)
if($_GET['width'])$captcha->setCaptchaWidth($_GET['width']);
if($_GET['height'])$captcha->setCaptchaHeight($_GET['height']);
if($_GET['minWordLength'])$captcha->setCaptchaMinWordLength($_GET['minWordLength']);
if($_GET['maxWordLength'])$captcha->setCaptchaMaxWordLength($_GET['maxWordLength']);
if($_GET['id'])$captcha->setCaptchaId($_GET['id']);
//===== EOF PARAMETERS =====

//generate captcha image
$captcha->CreateImage();
?>