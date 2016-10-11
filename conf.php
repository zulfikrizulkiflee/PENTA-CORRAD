<?php
//db configurations
define('DB_OTHERS','');				//other db, separate by comma (,)

//constants definition
define('SESSION_NAME','CORRAD_AKU');
define('SYSTEM_VERSION','2.14');
define('SYSTEM_BUILD','170815');
define('DEFAULT_DATE_FORMAT','format-d-m-Y');							//format-y-m-d, format-d-m-y		YYYY-MM-DD, DD-MM-YYYY
define('DEFAULT_QUERY_LIMIT',1000);                                     //max row of query result
define('DEFAULT_REFERENCE_PAGING',15);                                  //max row of reference
define('TIMEPICKER_FORMAT',24);											//12/24
define('TIMEPICKER_SHOW_SECS',true);

//--------APP SETTINGS----------------------------------------------------
define('APP_NAME','CORRAD');
define('APP_FULL_NAME','Sistem Pengurusan Taman Permainan');
define('APP_BACKUP_FILE_PREFIX',APP_NAME);
define('APP_VERSION','1');
define('APP_BUILD','01');
//--------//APP SETTINGS--------------------------------------------------

//--------LOGIN OPTIONS---------------------------------------------------
define('SYSTEM_LOGIN_DEFAULT','login_default.php');
define('SYSTEM_LOGIN_CUSTOM','login_custom.php');
define('SYSTEM_LOGIN_CAS','login_cas.php');
define('SYSTEM_LOGIN_WEB_AUTHENTICATION','login_web_authentication.php');
define('SYSTEM_LOGIN_PAGE',SYSTEM_LOGIN_DEFAULT);
//--------//LOGIN OPTIONS-------------------------------------------------

//define('SYSTEM_HOME_PAGE','map.html');
define('SYSTEM_HOME_PAGE','index.php?page=dashboard');

//--------SUB HEADER------------------------------------------------------
define('HOME_PAGE','Muka Utama');
define('HOME_PAGE_URL','index.php?page=dashboard');
//define('HOME_PAGE_URL','map.html');
define('LOGOUT','Log Keluar');
define('LOGOUT_URL','login.php');
//--------SUB HEADER------------------------------------------------------

//--------LOGIN SCREEN---------------------------------------------------
define('LOGIN_ERROR_MSG','Sila isikan Nama Pengguna dan Kata Laluan anda.');					//login error msg
define('LOGIN_INVALID_MSG','Ralat! Nama Pengguna atau Kata Laluan yang diberi tidak tepat.');	//login error msg
define('LOGIN_ACCOUNT_USED_MSG','Ralat! Akaun sedang digunakan. Sila cuba sebentar lagi.');
define('LOGIN_PASSWORD_EXPIRED','Ralat! Kata laluan telah melepasi tarikh luput.');
define('LOGIN_PASSWORD_ALMOST_EXPIRED','Ralat! Kata laluan hampir melepasi tarikh luput. Tukar kata laluan?');
define('LOGIN_SYS_NAME_ENABLED',true);
define('LOGIN_CREDIT','Powered by CORRAD v'.SYSTEM_VERSION.' Build '.SYSTEM_BUILD.'<br>Copyright '.date('Y').'. eNCoral Digital Solutions Sdn Bhd');
//--------//LOGIN SCREEN-------------------------------------------------

//--------CAPTCHA SETTING------------------------------------------------
define('LOGIN_CAPTCHA_ENABLED',false);										//captcha flag (true/false)
define('LOGIN_CAPTCHA_ERROR_MSG','Ralat! Pengesahan Kod Tidak Tepat!');		//captcha error message
//--------//CAPTCHA SETTING----------------------------------------------

//--------LOGIN EXTRA OPTION---------------------------------------------
define('LOGIN_SINGLE_USER_ENABLED',false);									//single user login flag (true/false), audit is required
define('SESSION_TIMEOUT_DURATION',0);										//max duration,in minutes, of session per page (0 to disable)
//--------//LOGIN EXTRA OPTION-------------------------------------------

//--------CAS SETTING----------------------------------------------------
define('CAS_ENABLED',false);					//cas enable flag
define('CAS_VERSION',2);						//cas version
define('CAS_HOSTNAME','localhost');				//cas host name (domain)
define('CAS_PORT',8443);						//cas port
define('CAS_URI','cas-server');					//cas uri (folder name for cas)
define('LOGIN_CAS_MSG','Anda adalah pengguna yang tidak berdaftar atau tidak mempunyai akses kepada sistem ini!');	//cas login error msg
//--------//CAS SETTING--------------------------------------------------

//--------//CAS SECONDARY LOGIN SETTING----------------------------------
define('CAS_SECONDARY_LOGIN_ENABLED',false);					//secondary login if cas server unavailable
define('CAS_SECONDARY_LOGIN_PAGE',SYSTEM_LOGIN_DEFAULT);	//secondary login page
//--------//CAS SECONDARY LOGIN SETTING----------------------------------

//--------PASSWORD EXPIRY OPTIONS---------------------------------------------------
define('PASSWORD_EXPIRY',false);
define('PASSWORD_EXPIRY_DAYS',1);
define('PASSWORD_EXPIRY_REMINDER_DAYS',1);
define('CHANGE_PASSWORD_URL','index.php?page=change_password&menuID=12');
//--------//PASSWORD EXPIRY OPTIONS-------------------------------------------------

//--------ERROR MESSAGES-------------------------------------------------
define('FILE_NOT_EXIST_ERR','Error. The file requested does not exist.');
define('JAVASCRIPT_NOT_ENABLED_ERR','Please enable JavaScript.');
define('COOKIE_NOT_ENABLED_ARR','Please enable cookie.');
//--------//ERROR MESSAGES-----------------------------------------------

//-------LAYOUT----------------------------------------------------------
//menu
define('TOP_MENU_MAX_WIDTH',800);       	//px
define('SIDE_MENU_ICON',true);             	//true/false
define('TOP_MENU_ICON',true);              	//true/false
define('MENU_ICON_WIDTH',16);              	//px
define('MENU_ICON_HEIGHT',16);             	//px

//theme
define('DEFAULT_THEME',4);
define('DEFAULT_LAYOUT',1);

//footer
define('FOOTER_ENABLED',true);					//true/false
define('FOOTER_TEXT','Copyright '.date('Y').'.<br>Powered by CORRAD v'.SYSTEM_VERSION.' Build '.SYSTEM_BUILD);
define('FOOTER_ID','footer');

//page response time
define('PAGE_RESPONSE_ENABLED',true);
define('PAGE_RESPONSE_ID','pageGeneration');

//block
define('HEADER_ENABLED',true);						//true/false
define('SUB_HEADER_ENABLED',true);					//true/false
define('PROFILE_ENABLED',true);						//true/false
define('PAGE_BORDER_ENABLED',true);               	//true/false
//-------//LAYOUT-------------------------------------------------------

//------CHANGE PASSWORD SCREEN------------------------------------------
define('CPWD_MIN_LENGTH',3);
define('CPWD_MSG_ERR_1','Ralat! Kata laluan semasa tidak tepat. Sila cuba sekali lagi. ');
define('CPWD_MSG_ERR_2','Ralat! Kata laluan baru tidak sama. Sila cuba sekali lagi. ');
define('CPWD_MSG_ERR_3','Ralat! Pastikan kata laluan lebih dari '.CPWD_MIN_LENGTH.' aksara.');
define('CPWD_MSG_SUCCESS','Kata laluan telah berjaya dikemaskini.');
define('CPWD_MSG_ERR_4','Ralat! Kata laluan tidak sama.');
define('CPWD_MSG_ERR_5','Kata laluan sama.');
//------//END CHANGE PASSWORD SCREEN------------------------------------

//------NOTIFICATION----------------------------------------------------
define('NOTIFICATION_DURATION','1');
//------//END NOTIFICATION----------------------------------------------

//------STANDARD DATABASE NOTIFICATION MESSAGE--------------------------
define('DB_INSERT_SUCCESS','Maklumat telah berjaya dimasukkan.');
define('DB_UPDATE_SUCCESS','Maklumat telah berjaya dikemaskini.');
define('DB_DELETE_SUCCESS','Maklumat telah berjaya dihapuskan.');
//------END STANDARD DATABASE NOTIFICATION MESSAGE----------------------

//------STANDARD DATABASE MESSAGE ERROR---------------------------------
define('DB_INSERT_ERR','Maklumat tidak berjaya dimasukkan ke dalam pengkalan data.');
define('DB_UPDATE_ERR','Maklumat tidak berjaya dikemaskini ke dalam pengkalan data.');
define('DB_DELETE_ERR','Maklumat tidak berjaya dihapuskan dari pengkalan data.');
//------END STANDARD DATABASE MESSAGE ERROR-----------------------------

//------AUDIT----------------------------------------
define('AUDIT_ENABLED',true);			//true/false
//------//END AUDIT----------------------------------

//------LOG----------------------------------------
define('ERROR_LOG_ENABLED',false);			//true/false
define('ERROR_LOG_MSG','Terdapat masalah pada Pengkalan Data. Sila hubungi Pentadbir Sistem!');
//------//END LOG----------------------------------

//------PERMISSION-----------------------------------
define('COMPONENT_PERMISSION_ENABLED',false);		//usage of component permisison
define('ITEM_PERMISSION_ENABLED',true);			//usage of item permisison
define('CONTROL_PERMISSION_ENABLED',false);			//usage of control permisison
//------//PERMISSION---------------------------------

//------ENCODED SECURITY----------------------------------------
define('URL_SECURITY',false);						//true/false for ENCODE url for security purpose, this must be true if one of below define item set to true
define('MENU_URL_SECURITY',false);					//true/false for ENCODE menu link url for security purpose
define('BUTTON_URL_SECURITY',false);				//true/false for ENCODE button link url for security purpose
define('OTHERS_URL_SECURITY',false);				//true/false for ENCODE others link url for security purpose
//------//ENCODED SECURITY--------------------------------------

//------$_POST SECURITY----------------------------------------
define('HTML_SPECIAL_CHARS_ENABLED',true);			//true/false for escape html special chars
define('STRIP_TAGS_ENABLED',true);					//true/false for strip html tags
define('STRIP_TAGS_EXCLUDE','<br>,<p>,</p>');		//tags that are not stripped, separate by comma (,)
//------//URL SECURITY----------------------------------------

//------PUBLIC LOGIN----------------------------------------
define('PUBLIC_LOGIN',true);						//true/false for public login, enable or disable login
define('PUBLIC_USER_TYPE_CODE','PUBLIC');			//for public user type code, mostly is PUBLIC
define('PUBLIC_ACCESS_FILE','public_access.php');	//for public access file, mostly the name is public_access.php or you can custom to other file
//------//PUBLIC LOGIN----------------------------------------

//------USER PROFILE ----------------------------------------
// to setup user profile, default is using the pruser.
define('USER_PROFILE','PRUSER');		//format : schema.tablename
define('USER_PROFILE_NAME','NAME');
define('USER_PROFILE_USERID','USERID');
define('USER_PROFILE_USERNAME','USERNAME');
define('USER_PROFILE_USERPASSWORD','USERPASSWORD');
define('USER_PROFILE_USERGROUPCODE','USERGROUPCODE');
define('USER_PROFILE_USERTYPECODE','USERTYPECODE');
define('USER_PROFILE_DEPARTMENTCODE','DEPARTMENTCODE');
define('USER_PROFILE_IMAGEFILE','IMAGEFILE');
define('USER_PROFILE_USERCHANGEPASSWORDTIMESTAMP',"USERCHANGEPASSWORDTIMESTAMP");
//------USER PROFILE ----------------------------------------

//login where condition
define('LOGIN_CONDITION','');

//------OTHERS------------------------------------------
define('LOGOUT_PUBLIC_DISABLE',false);						//true/false for public login, enable or disable logout link
define('MAINMENU_PUBLIC_DISABLE',false);					//true/false for public login, enable or disable mainmenu link
//------//OTHERS----------------------------------------

//------LANGUAGE----------------------------------------
define('FLC_LANGUAGE',true);								//toogle true/false to active the language usage
define('FLC_LANGUAGE_DEFAULT','2');							//select language DEFAULT='' for standard or type_code (refer flc_language)
//------//LANGUAGE--------------------------------------

//------EXTENSION---------------------------------------
define('TEMP_FOLDER','temp');
define('EXTENSION_FOLDER','ext');
//------//EXTENSION-------------------------------------

//------USER GROUP MANAGEMENT---------------------------
define('USER_GROUP_MGMT_MAX_PER_PAGE',200);
//------//USER GROUP MANAGEMENT-------------------------

define('ROOTPATH', __DIR__);
define('CON_FILE',ROOTPATH.'/connection.xml');
?>