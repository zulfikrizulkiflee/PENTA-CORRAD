<?php
//set character set of db to utf8 - multilingual
function setCharSetUTF8($myQuery)
{
	if(DBMS_NAME == 'mysql')
	{
		mb_internal_encoding("UTF-8");
		$myQuery->query("set names 'utf8'",'RUN');
		$myQuery->query("SET CHARACTER SET utf8",'RUN');
		mysql_set_charset('utf8');
	}
	else if(DBMS_NAME == 'oracle')
	{
	}
	else if(DBMS_NAME == 'postgresql')
	{
	}
	else if(DBMS_NAME == 'sybase')
	{
	}
	else if(DBMS_NAME == 'sqlserver')
	{
	}
}

//ckm 20120308
//for index, pages, not for menu
function getTranslation($myQuery,$language)
{
	//if language is given
	if($language)
	{
		if(FLC_LANGUAGE)
		{
			//set charset to utf8
			setCharSetUTF8($myQuery);

			$qry = "select b.TRANS_SOURCE_CONSTANT_NAME, b.TRANS_TEXT,
						a.TRANS_SOURCE_CONSTANT_NAME TRANS_SOURCE_CONSTANT_NAME_BCK, a.TRANS_TEXT TRANS_TEXT_BCK
					from FLC_TRANSLATION a left join FLC_TRANSLATION b
						on a.TRANS_SOURCE_CONSTANT_NAME = b.TRANS_SOURCE_CONSTANT_NAME
						and b.TRANS_TYPE = 1 and b.TRANS_LANGUAGE = ".$language."
					where a.TRANS_TYPE = 1 and a.TRANS_LANGUAGE = 1";
			$qryRs = $myQuery->query($qry,'SELECT','NAME');

			for($x=0; $x < count($qryRs); $x++)
			{
				if($qryRs[$x]['TRANS_TEXT'] != '')
					define($qryRs[$x]['TRANS_SOURCE_CONSTANT_NAME'],$qryRs[$x]['TRANS_TEXT']);
				else
					define($qryRs[$x]['TRANS_SOURCE_CONSTANT_NAME_BCK'],$qryRs[$x]['TRANS_TEXT_BCK']);
			}
		}
		else
		{
			$qry = "select b.TRANS_SOURCE_CONSTANT_NAME, b.TRANS_TEXT,
						a.TRANS_SOURCE_CONSTANT_NAME TRANS_SOURCE_CONSTANT_NAME_BCK, a.TRANS_TEXT TRANS_TEXT_BCK
					from FLC_TRANSLATION a left join FLC_TRANSLATION b
						on a.TRANS_SOURCE_CONSTANT_NAME = b.TRANS_SOURCE_CONSTANT_NAME
						and b.TRANS_TYPE = 1 and b.TRANS_LANGUAGE = ".FLC_LANGUAGE_DEFAULT."
					where a.TRANS_TYPE = 1 and a.TRANS_LANGUAGE = 1";
			$qryRs = $myQuery->query($qry,'SELECT','NAME');

			for($x=0; $x < count($qryRs); $x++)
			{
				if($qryRs[$x]['TRANS_TEXT'] != '')
					define($qryRs[$x]['TRANS_SOURCE_CONSTANT_NAME'],$qryRs[$x]['TRANS_TEXT']);
				else
					define($qryRs[$x]['TRANS_SOURCE_CONSTANT_NAME_BCK'],$qryRs[$x]['TRANS_TEXT_BCK']);
			}
		}
	}//eof if
}//eof function

//type = 2-menu, 3-page, 4-component,5-component item, 6- page control
function getElemTranslation($myQuery,$language,$type,$id,$col)
{
	if(FLC_LANGUAGE)
	{
		//set db charset to utf8
		setCharSetUTF8($myQuery);

		$qry = 'select TRANS_TEXT, TRANS_SOURCE_COLUMN, TRANS_SOURCE_TABLE
					from FLC_TRANSLATION
					where TRANS_LANGUAGE = '.$language.'
					and TRANS_TYPE = '.$type.'
					and TRANS_SOURCE_ID = '.$id.'
					and TRANS_SOURCE_COLUMN = \''.$col.'\'';

		$qryRs = $myQuery->query($qry,'SELECT','NAME');
		
		return $qryRs;
	}
	else 
		return false;
}

//function to check whether component is tabular or form
function checkTabularOrForm($myQuery,$componentID)
{
	$qry = "select COMPONENTTYPE from FLC_PAGE_COMPONENT where COMPONENTID = ".$componentID;
	$qryRs = $myQuery->query($qry,'SELECT','NAME');

	if($qryRs[0]['COMPONENTTYPE'] == 'form_1_col' || $qryRs[0]['COMPONENTTYPE'] == 'form_2_col')
		return 'form';

	else if($qryRs[0]['COMPONENTTYPE'] == 'report' || $qryRs[0]['COMPONENTTYPE'] == 'tabular')
		return 'tabular';
}

//assemble recursive menu
function assambleRecursiveMenu($menuRecursive, $level=0)
{
	//count of menu
	$menuRecursiveCount = count($menuRecursive);

	//increment menu level
	$level++;

	//loop on count of sub menu
	for($x=0; $x<$menuRecursiveCount; $x++)
	{
		//get parent menu
		$menu[] = $menuRecursive[$x];
		$menuCount = count($menu);

		//set menu level
		$menu[$menuCount-1]['MENULEVEL'] = $level;

		//get sub menu (if any)
		if($menuRecursive[$x]['MENUCHILD'])
		{
			//set menu child status
			$menu[$menuCount-1]['MENUCHILD'] = true;

			//get sub menu
			$subMenu = assambleRecursiveMenu($menuRecursive[$x]['MENUCHILD'], $level);
			$subMenuCount = count($subMenu);

			//loop on count of sub menu
			for($y=0; $y<$subMenuCount; $y++)
			{
				$subMenu[$y]['MENUBREADCRUMBS'] = $menuRecursive[$x]['MENUTITLE'].' / '.$subMenu[$y]['MENUBREADCRUMBS'];
			}//eof for

			//merge parent and sub menu
			$menu = array_merge($menu, $subMenu);
		}//eof if
	}//eof for

	return $menu;
}//eof function

//to display right menu
function displayContent($dbc,$myQueryArr,$mySQL)
{
	$myQuery=$myQueryArr['myQuery'];

	//if home file exists, include home
	if(!$_GET['page'] && SYSTEM_HOME_PAGE && file_exists(SYSTEM_HOME_PAGE))
		include(SYSTEM_HOME_PAGE);

	//if GET page is set
	else if(trim($_GET['page']) != "")
	{
		//validate access
		if(validateAccessPermission())
		{
			//check file exists, if exist include
			if(file_exists($_GET['page'].".php"))
			{
				//execute audit, store navigation log
				executeAudit();

				include($_GET['page'].".php");
			}
			//else show error and file name to include
			else
				echo $_GET['page'].".php"." - ".FILE_NOT_EXIST_ERR;
		}//eof if
		else
			showNotificationError('You are not authorized to view this page!');		//show unauthorized message
	}//eof elseif

	else if($_SESSION['userID'] && strstr(SYSTEM_HOME_PAGE,'index.php?') != false)
		redirect(SYSTEM_HOME_PAGE);

	//else, show error
	else
	{
		if(SYSTEM_HOME_PAGE)
			echo SYSTEM_HOME_PAGE." - ".FILE_NOT_EXIST_ERR;
	}//eof else
}
//------------------------------------------------------------

//function to validate menu access permission for user
function validateAccessPermission()
{
	include('db.php');

	//permission
	if($_SESSION['userID'] && $_SESSION['userID'] != '1')
		$menuPermissionSQL = " and MENUID in (".$mySQL->getPermissionSQL('menu',$_SESSION['userID']).") ";
	else if($_SESSION['userID'] != '1')
		$menuPermissionSQL = " and LINKTYPE = 'P' ";

	//if have menuID
	if($_GET['menuID'])
		$extraSQL = " and MENUID = ".$_GET['menuID'];

	//get list of menu
	$allowedMenu = "select '1' from FLC_MENU
						where lower(MENULINK) like lower('%index.php?page=".$_GET['page']."%') ".$extraSQL.$menuPermissionSQL;
	$allowedMenuRs = $myQuery->query($allowedMenu);
	$allowedMenuRsCount = count($allowedMenuRs);

	//if current menu is allowed
	if($allowedMenuRsCount)
		return true;
	else
		return false;
}//eof function

//function to convert from db safe format query to display friendly
function convertToDBQry($str)
{
	$temp = trim(str_replace('[QD]','"',$str));
	$temp = trim(str_replace('[QS]',"'",$temp));
	return $temp;
}

//convert db safe query to normal query
function convertDBSafeToQuery($str)
{
	//clean up sql
	$toReplace = array('[QS]','[QS]','[QS]','[QD]','[QD]','[QD]');
	$theReplacement = array("'","'","'",'"','"','"');

	//cleaned up sql
	$str = trim(str_replace($toReplace,$theReplacement,$str));

	//if string is not null
	if($str != '')
	{
		//check query for {POST
		if(strpos($str,'{POST') !== false)
		{
			$offset = strpos($str, '{POST');

			do{
				$position_1[] = $offset;
				$position_2[] = strpos($str, '}', $offset + 1);
			}while($offset = strpos($str, '{POST', $offset + 1));
		}//eof if

		//check query for {GET
		if(strpos($str,'{GET') !== false)
		{
			$offset = strpos($str, '{GET');

			do{
				$position_1[] = $offset;
				$position_2[] = strpos($str, '}', $offset + 1);
			}while($offset = strpos($str, '{GET', $offset + 1));
		}//eof if

		//check query for {SESSION
		if(strpos($str,'{SESSION') !== false)
		{
			$offset = strpos($str, '{SESSION');

			do{
				$position_1[] = $offset;
				$position_2[] = strpos($str, '}', $offset + 1);
			}while($offset = strpos($str, '{SESSION', $offset + 1));
		}//eof if

		//check query for {CONST
		if(strpos($str,'{CONST') !== false)
		{
			$offset = strpos($str, '{CONST');

			do{
				$position_1[] = $offset;
				$position_2[] = strpos($str, '}', $offset + 1);
			}while($offset = strpos($str, '{CONST', $offset + 1));
		}//eof if

		//check query for {FILE
		if(strpos($str,'{FILE') !== false)
		{
			$offset = strpos($str, '{FILE');

			do{
				$position_1[] = $offset;
				$position_2[] = strpos($str, '}', $offset + 1);
			}while($offset = strpos($str, '{FILE', $offset + 1));
		}//eof if

		//check query for {EVAL
		if(strpos($str,'{EVAL') !== false)
		{
			$offset = strpos($str, '{EVAL');

			do{
				$position_1[] = $offset;
				$position_2[] = strpos($str, '}', $offset + 1);
			}while($offset = strpos($str, '{EVAL', $offset + 1));
		}//eof if

		//for all number of { and }
		for($x=0; $x < count($position_1); $x++)
		{
			//get original sub string
			$original[$x] = substr($str,$position_1[$x],$position_2[$x]-$position_1[$x]+1);

			//define things to be replaced
			$str_1 = array('{','}');
			$str_2 = array('','');

			//start processing the string
			$replaced = str_replace($str_1,$str_2,$original[$x]);

			//split to chunks
			$replacedSplit = explode('|',$replaced);

			//if post
			if($replacedSplit[0] == 'POST')
				$newValue[] = $_POST[$replacedSplit[1]];

			//else if get
			else if($replacedSplit[0] == 'GET')
				$newValue[] = $_GET[$replacedSplit[1]];

			//else if session
			else if($replacedSplit[0] == 'SESSION')
				$newValue[] = $_SESSION[$replacedSplit[1]];

			//else if file
			else if($replacedSplit[0] == 'CONST')
				$newValue[] = constant($replacedSplit[1]);

			//else if file
			else if($replacedSplit[0] == 'FILE')
				$newValue[] = $replacedSplit[1];

			//else if eval
			else if($replacedSplit[0] == 'EVAL')
				$newValue[] = eval($replacedSplit[1]);
		}

		//replace original string with converted string
		for($x=0; $x < count($original); $x++)
			$str = str_replace($original[$x],$newValue[$x],$str);

		return $str;
	}
}

//to convert array into db's safe expression
function convertToDBSafe($array)
{
	$array=str_replace('\'','\'\'',$array);

	//if array
	if(is_array($array))
	{
		$tempkeys=array_keys($array);
		$tempkeysCount=count($tempkeys);
	}//eof if
	else if (is_string($array))
		$tempkeysCount=1;

	//loop on count of array
	for($x=0;$x<$tempkeysCount;$x++)
	{
		//if multi dimentional array
		if(is_array($array[$tempkeys[$x]]))
			$array[$tempkeys[$x]] = convertToDBSafe($array[$tempkeys[$x]]);
		else
			$array[$tempkeys[$x]] = stripslashes($array[$tempkeys[$x]]);
	}//eof if

	return $array;
}//eof function

//to convert special characters to HTML entities
function convertHTMLSpecialChars($array)
{
	//if array
	if(is_array($array))
	{
		$arrayKeys=array_keys($array);
		$arrayKeysCount=count($arrayKeys);
	}//eof if
	else
		$arrayKeysCount=1;

	//loop on count of array
	for($x=0; $x<$arrayKeysCount; $x++)
	{
		//if multi dimentional array
		if(is_array($array[$arrayKeys[$x]]))
			$array[$arrayKeys[$x]] = convertHTMLSpecialChars($array[$arrayKeys[$x]]);
		else
			$array[$arrayKeys[$x]] = htmlspecialchars($array[$arrayKeys[$x]]);
	}//eof if

	return $array;
}//eof function

//to strip HTML and PHP tags from a string
function stripTags($array)
{
	//print_r($array);

	//htmlpurifier library
	//require_once 'tools/htmlpurifier/library/HTMLPurifier.auto.php';
   // $config = HTMLPurifier_Config::createDefault();
  //  $purifier = new HTMLPurifier($config);

	//if array
	if(is_array($array))
	{
		$arrayKeys=array_keys($array);

		//print_r($arrayKeys);

		$arrayKeysCount=count($arrayKeys);
	}//eof if
	else
		$arrayKeysCount=1;

	//loop on count of array
	for($x=0; $x<$arrayKeysCount; $x++)
	{
		//if multi dimentional array
		if(is_array($array[$arrayKeys[$x]]))
			$array[$arrayKeys[$x]] = stripTags($array[$arrayKeys[$x]]);
		else
		{
			//if have allowable tags
			if(STRIP_TAGS_EXCLUDE)
				//$allowableTags = '"'.str_replace(',','","',STRIP_TAGS_EXCLUDE).'"';
				$allowableTags = str_replace(',','',STRIP_TAGS_EXCLUDE);

			//echo htmlentities($allowableTags);

			//$clean_html = $purifier->purify($dirty_html);
			//echo 'xxx:'.($array[$arrayKeys[$x]]);
			//echo '<br>';
			//echo "aa";
			//strip tags in input
			$array[$arrayKeys[$x]] = strip_tags($array[$arrayKeys[$x]], $allowableTags);
		//	$array[$arrayKeys[$x]] = strip_tags($purifier->purify($array[$arrayKeys[$x]]));

			//echo 'yyyy:'.$array[$arrayKeys[$x]];
			//echo '<br>';

		}//eof else
	}//eof if

	return $array;
}//eof function

//function to convert post data to hidden input
//filter: if filter: nofilter, do not filter
function convertPostToHidden($post,$filter,$menuID,$myQuery)
{
	//if session is not set, set session data
	if(!isset($_SESSION['postStore'.$menuID]))
	{
		//for all post data, convert to hidden input
		foreach($post as $key => $value)
		{
			//if no filter
			if($filter == 'nofilter')
				$_SESSION['postStore'.$menuID][$key] = $value;

			//if filter is input map
			else if($filter == 'input_map')
			{
				//if filter is found in key, store as concatenated string
				if(strpos($key,$filter) !== false)
					$_SESSION['postStore'.$menuID][$key] = $value;
			}
		}
	}

	//if session is set
	if(isset($_SESSION['postStore'.$menuID]))
	{
		//to get the component id for current menu
		$getComponent="select COMPONENTID
							from FLC_PAGE_COMPONENT
							where PAGEID=
								(select PAGEID from FLC_PAGE where MENUID=".$menuID.")";
		$getComponentRs = $myQuery->query($getComponent,'SELECT');
		$getComponentRsCount = count($getComponentRs);		//count of component

		//for all post data, convert to hidden input
		foreach($post as $key => $value)
		{
			//if no filter
			if($filter == 'nofilter')
				$str .= '<input name="'.$key.'" id="'.$key.'" type="hidden" value="'.$value.'" />';

			//if filter is input map
			else if($filter == 'input_map')
			{
				//flag to display hidden
				$strFlag = true;

				//get componentid of item
				$temp = explode('_',str_replace($filter.'_','',$key));

				//search items for current menuid
				for($x=0;$x<$getComponentRsCount;$x++)
					if($temp[0] == $getComponentRs[$x][0])
						$strFlag = false;	//if found items for current page, set flag false

				//if filter is found in key, store as concatenated string
				if(strpos($key,$filter) !== false && $strFlag)
					$str .= '<input name="'.$key.'" id="'.$key.'" type="hidden" value="'.$value.'" />';
			}
		}
	}

	//return concatenated string
	return $str;
}

//function to check if user can login
function checklogin($myQuery,$mySQL,$mySession,$username='',$password='')
{
	$ip = $_SERVER["REMOTE_ADDR"];
	
	/* Checks if this IP address is currently blocked*/	
	$result = confirmIPAddress($myQuery,$ip);
	
	if($result == 1)
	{
	 $error  = LOGIN_ATTEMP_ERR;
	 
	}
	else
	{ 	
	//check if both username and password is entered
	if($username != '' && $password != '')
	{
		//check username
		$usernameArr = $mySQL->getUserInfo($username,$password);

		//if there exists user with username and password given, login
		if($usernameArr)
		{
					//set attempt to 0
					clearLoginAttempts($myQuery,$ip);
			//if single user login is enabled and session active, logout other sessions
			if(LOGIN_SINGLE_USER_ENABLED)
			{
				//check login status
				$loginAudit = "select a.USER_ID, a.AUDIT_TIMESTAMP, a.AUDIT_CLIENT_IP, a.AUDIT_CLIENT_PC_NAME, a.AUDIT_SESSION_ID
							from
								(select USER_ID, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME, AUDIT_SESSION_ID,
										max(AUDIT_TIMESTAMP) AUDIT_TIMESTAMP
									from FLC_AUDIT where AUDIT_ACTION = 'login'
									group by USER_ID, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME, AUDIT_SESSION_ID) a left join
								(select USER_ID, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME, AUDIT_SESSION_ID,
										max(AUDIT_TIMESTAMP) AUDIT_TIMESTAMP
									from FLC_AUDIT where AUDIT_ACTION like 'logout%'
									group by USER_ID, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME, AUDIT_SESSION_ID) b
								on a.USER_ID = b.USER_ID and a.AUDIT_SESSION_ID = b.AUDIT_SESSION_ID
							where (a.AUDIT_TIMESTAMP > b.AUDIT_TIMESTAMP or b.AUDIT_TIMESTAMP is null)
								and a.USER_ID = ".$usernameArr[0]['USERID'];
				$loginAuditRs = $myQuery->query($loginAudit,'SELECT','NAME');
				$loginAuditRsCount = count($loginAuditRs);

				//if already logged in, update audit to logout the session
				if($loginAuditRsCount)
				{
					//loop of count of user login
					for($x=0; $x<$loginAuditRsCount; $x++)
					{
						//insert into audit table, logout other sessions
						$insertAudit = "insert into FLC_AUDIT
										(AUDIT_TIMESTAMP, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME, AUDIT_SESSION_ID,
										AUDIT_REQUEST_URI, AUDIT_REQUEST_MENU_ID, AUDIT_ACTION, USER_ID)
										values (".$mySQL->currentDate().",
										'".$_SERVER['REMOTE_ADDR']."', '',
										'".$loginAuditRs[$x]['AUDIT_SESSION_ID']."', NULL, NULL,
										'logout (auto)', ".$loginAuditRs[$x]['USER_ID'].")";
						$insertAuditRs = $myQuery->query($insertAudit,'RUN');
					}//eof for
				}//eof if
			}//eof if

			//check expiry
			if(PASSWORD_EXPIRY && $usernameArr[0]['USERCHANGEPASSWORDTIMESTAMP'])
			{
				$currentDate = date('Y-m-d');
				$expiryDate = $usernameArr[0]['USERCHANGEPASSWORDTIMESTAMP'];

				$tempCurrentDate = explode('-',$currentDate);
				$tempExpiryDate = explode('-',$expiryDate);

				$currentTimestamp = mktime(0, 0, 0, $tempCurrentDate[1], $tempCurrentDate[2], $tempCurrentDate[0]);
				$expiryTimestamp = mktime(0, 0, 0, $tempExpiryDate[1], $tempExpiryDate[2], $tempExpiryDate[0]);

				//number of expiry days
				$chkExpired = floor(($currentTimestamp-$expiryTimestamp)/(60*60*24));
			}//eof if

			//if expired
			if($chkExpired > 0)
			{
				$error = LOGIN_PASSWORD_EXPIRED;
			}
			else
			{
				//store important user data in session
				$_SESSION['userID'] = $usernameArr[0]['USERID'];
				$_SESSION['userName'] = $usernameArr[0]['USERNAME'];
				$_SESSION['userGroupCode'] = $usernameArr[0]['USERGROUPCODE'];
				$_SESSION['userTypeCode'] = $usernameArr[0]['USERTYPECODE'];
				$_SESSION['departmentCode'] = $usernameArr[0]['DEPARTMENTCODE'];
				$_SESSION['userImage'] = $usernameArr[0]['IMAGEFILE'];
				$_SESSION['Name'] = $usernameArr[0]['NAME'];

				//set login flag to true
				$_SESSION['loginFlag'] = true;

				//execute audit, store login log
				executeAudit('login');

				//if almost expired
				if(PASSWORD_EXPIRY && abs($chkExpired) <= PASSWORD_EXPIRY_REMINDER_DAYS)
				{?>
					<script>
					if(window.confirm('<?php echo LOGIN_PASSWORD_ALMOST_EXPIRED;?>'))
						window.location="<?php echo CHANGE_PASSWORD_URL;?>";
					</script>
				<?php }//eof if
			}//eof else
		}//eof if
		//show error
			else
				{
					addLoginAttempt($myQuery,$ip);
					if(getStatusCodeUsr($myQuery,$username,$password)==='0')
						$error = LOGIN_NONACTIVE_USR_MSG;
		else
			$error = LOGIN_INVALID_MSG;
	}
		}

	//if either username or password not entered, load error msg
	else
		$error = LOGIN_ERROR_MSG;

	}
	return $error;
}//eof function

function getSideComponent($myQuery,$perm)
{
	if(!isset($_SESSION['sideComponentStatus']) && $_SESSION['sideComponentStatus'] !== false)
	{
		$component = "select * from FLC_PAGE_COMPONENT
						where COMPONENTSTATUS = 1
						and COMPONENTPOSITION = 'left'
						".$perm."
						order by COMPONENTORDER";
		$componentArr = $myQuery->query($component,'SELECT','NAME');
				
		if(count($componentArr) == 0)
			$_SESSION['sideComponentStatus'] = false;

		else	
			$_SESSION['sideComponentStatus'] = $componentArr;	
	}
	return $_SESSION['sideComponentStatus'];
}

//function to check if user has logged out
function checkLogout($mySession,$cas,$flag)
{
	//if single user login is enabled and session active
	if(LOGIN_SINGLE_USER_ENABLED && $_SESSION['userID'])
	{
		include('db.php');

		//check logout status
		$logoutAudit = "select AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME
							from FLC_AUDIT
							where AUDIT_SESSION_ID = '".session_id()."' and AUDIT_ACTION = 'logout (auto)'
								and USER_ID = ".$_SESSION['userID'];
		$logoutAuditRs = $myQuery->query($logoutAudit,'SELECT','NAME');
		$logoutAuditRsCount = count($logoutAuditRs);

		//if session has been logged out
		if($logoutAuditRsCount)
		{
			//log out and clear session
			$mySession->logout();

			?>
			<script>
			alert("Your session has been logged out by: \n" +
					"IP Address: <?php echo $logoutAuditRs[0]['AUDIT_CLIENT_IP'];?>\n" +
					"PC Name: <?php echo $logoutAuditRs[0]['AUDIT_CLIENT_PC_NAME'];?>");
            </script>
			<?php

			//redirect to main page after logout
			redirect(LOGOUT_URL);
			exit();
		}//eof if
	}//eof if

	//if user logout, call function logout
	if($flag == 'true')
	{
		//update logout audit
		logoutLog();

		//log out and clear session
		$mySession->logout();

		//if cas enabled
		if(CAS_ENABLED&&is_object($cas))
			$cas->casLogout();

		//redirect to index
		redirect(LOGOUT_URL);
	}//eof if
}//eof function

//function to save log of logout
function logoutLog()
{
	//execute audit, store logout log
	executeAudit('logout');
}//eof function

//function to store audit trail
function executeAudit($action='navigation')
{
	//if audit enable
	if(AUDIT_ENABLED)
	{
		include('db.php');

		//request URI, decode if encoder is enabled
		if(URL_SECURITY)
		{
			$requestURI = $_SERVER['PHP_SELF'];
			$decodedParameter = flc_url_decode($_GET['a']);

			//if have parameter
			if($decodedParameter)
				$requestURI .= '?'.$decodedParameter;
		}//eof if
		else
			$requestURI = $_SERVER['REQUEST_URI'];

		//default for menuID, int value
		if($_GET['menuID'])
			$menuID = $_GET['menuID'];
		else
			$menuID = 'NULL';

		//default for userID, int value
		if($_SESSION['userID'])
			$userID = $_SESSION['userID'];
		else
			$userID = 'NULL';

		//check if have $_POST, change action to submit
		if($action == 'navigation' && $_POST)
		{
			$action = 'submit';

			//get pageid
			$getPage = "select b.PAGEID
						from FLC_MENU a, FLC_PAGE b
						where a.MENUID = b.MENUID and a.MENUID = ".$menuID;
			$getPageRs = $myQuery->query($getPage,'SELECT','NAME');

			//if have pageid
			if($getPageRs)
			{
				$postKeys = array_keys($_POST);
				$postKeysCount = count($postKeys);

				//loop on count of post
				for($x=0; $x<$postKeysCount; $x++)
				{
					//get control
					$getControl = "select CONTROLTITLE from FLC_PAGE_CONTROL
									where PAGEID = ".$getPageRs[0]['PAGEID']." and CONTROLNAME = '".$postKeys[$x]."'";
					$getControlRs = $myQuery->query($getControl,'SELECT','NAME');

					//if found control
					if($getControlRs)
					{
						$auditControl = $getControlRs[0]['CONTROLTITLE'];
						break;
					}//eof if
				}//eof for
			}//eof if
		}//eof if

		//insert into audit table
		$insertAudit = "insert into FLC_AUDIT
						(AUDIT_TIMESTAMP, AUDIT_CLIENT_IP, AUDIT_CLIENT_PC_NAME, AUDIT_SESSION_ID,
						AUDIT_REQUEST_URI, AUDIT_REQUEST_MENU_ID, AUDIT_ACTION, AUDIT_CONTROL, USER_ID)
						values (".$mySQL->currentDate().",
						'".$_SERVER['REMOTE_ADDR']."', '-', '".session_id()."',
						'".$requestURI."', ".$menuID.", '".$action."', '".$auditControl."', ".$userID.")";
		$insertAuditRs = $myQuery->query($insertAudit,'RUN');
	}//eof if
}//eof function

//function to store error log
function sqlErrorLog($errorText, $errorSQL='')
{
	//if $errorText is array
	if(is_array($errorText))
	{
		//get array keys
		$errorTextKeys = array_keys($errorText);
		$errorTextKeysCount = count($errorTextKeys);

		//loop on count of keys
		for($x=0; $x<$errorTextKeysCount; $x++)
			$errorTextArr .= '['.$errorTextKeys[$x].'] '. $errorText[$errorTextKeys[$x]].' ';

		//copy the text from array
		$errorText = $errorTextArr;
	}//eof if
	//if log enable
	if(ERROR_LOG_ENABLED)
	{
		//request URI, decode if encoder is enabled
		if(URL_SECURITY)
		{
			$requestURI = $_SERVER['PHP_SELF'];
			$decodedParameter = flc_url_decode($_GET['a']);

			//if have parameter
			if($decodedParameter)
				$requestURI .= '?'.$decodedParameter;
		}//eof if
		else
			$requestURI = $_SERVER['REQUEST_URI'];

		//log info
		$logFile = 'error_sql.log';
		$logInitText = '['.date('d/m/Y H:i:s').'] ['.$_SESSION['userName'].': '.$requestURI.']';
		$logEndText = '------------------------------------------------------';

		//write log
		file_put_contents($logFile, $logInitText.PHP_EOL.$errorText.PHP_EOL.$errorSQL.PHP_EOL.$logEndText.PHP_EOL, FILE_APPEND);

		//show nofitication
		showNotificationError(ERROR_LOG_MSG);
	}//eof if
	else
		echo $errorText.'<br>'.$errorSQL;
}//eof function

//todo here for extension in tabular or report
//include extension
function includeExtension($extType, $fileMode, $itemArr = NULL, $runningNo = NULL)
{
	//db connection
	include('db.php');

	//if theme, add additional sql
	if($extType == 'theme')
		$extraSQL = " and a.EXT_ID = ".$_SESSION['THEME'];
	//add filter name
	else if($extType == 'item' && $itemArr)
		$extraSQL = " and a.EXT_NAME = '".$itemArr['ITEMMAPPING']."'";

	//get extension file
	$includeFile = "select a.EXT_PATH, b.EXT_FILE_TYPE, b.EXT_FILE_NAME
					from FLC_EXTENSION a, FLC_EXTENSION_FILE b
					where a.EXT_ID = b.EXT_ID and a.EXT_STATUS = 1
						and a.EXT_TYPE = '".$extType."' and b.EXT_FILE_MODE = '".$fileMode."'".$extraSQL;
	$includeFileRs = $myQuery->query($includeFile,'SELECT','NAME');
	$includeFileRsCount = count($includeFileRs);

	if($includeFileRsCount)
	{
		//loop on count of include file
		for($x=0; $x<$includeFileRsCount; $x++)
		{
			//file path (either online or local)
			if(substr($includeFileRs[$x]['EXT_FILE_NAME'],0,4) == 'http')
				$filePath = $includeFileRs[$x]['EXT_FILE_NAME'];
			else
				$filePath = $includeFileRs[$x]['EXT_PATH'].'/'.$includeFileRs[$x]['EXT_FILE_NAME'];

			//switch file type
			switch($includeFileRs[$x]['EXT_FILE_TYPE'])
			{
				case 'php':	
					include($filePath);
				break;

				case 'js':	echo '<script language="javascript" type="text/javascript" src="'.$filePath.'"></script>'."\n";
				break;

				case 'css':	echo '<link href="'.$filePath.'" rel="stylesheet" type="text/css" />'."\n";
				break;
			}//eof switch
		}//eof for
	}//eof if
	else
	{
		//include default theme
		if($extType == 'theme')
		{
			echo '<script language="javascript" type="text/javascript" src="themes/default/main.js"></script>';
			echo '<link href="themes/default/main.css" rel="stylesheet" type="text/css" />';
		}//eof if
	}//eof else
	
	return $returnValue;
}//eof function

//execute bl
function executeBL($blName)
{
	//db connection
	include('db.php');

	//separate bl name and bl parameters
	$mapParameterString = substrByChar($blName, '(', ')');
	$blName = str_replace('('.$mapParameterString.')','',$blName);

	//get bl info
	$getBL = "select BLID, BLNAME, BLDETAIL
				from FLC_BL
				where BLTYPE = 'PHP' and BLSTATUS = '00' and BLNAME = '".$blName."'
				and BLPARENT is null";
	$getBLRs = $myQuery->query($getBL,'SELECT','NAME');
	$getBLRsCount = count($getBLRs);

	//if bl exist
	if($getBLRsCount)
	{
		//bl parameter
		$getBlParameter = "select PARAMETER_VALUE from FLC_BL_PARAMETER
							where BL_ID = ".$getBLRs[0]['BLID']."
							order by PARAMETER_SEQ";
		$getBlParameterRs = $myQuery->query($getBlParameter, 'SELECT', 'NAME');
		$getBlParameterRsCount = count($getBlParameterRs);

		//if have parameter
		if($getBlParameterRsCount)
		{
			//loop on count of parameter
			for($x=0; $x<$getBlParameterRsCount; $x++)
			{
				//append, if have value
				if($blParameterString)
					$blParameterString .= ',';

				//re-construct the triggers parameter
				$blParameterString .= $getBlParameterRs[$x]['PARAMETER_VALUE'];
			}//eof for
		}//eof if

		//create bl function
		$$blName = create_function($blParameterString,convertDBSafeToQuery($getBLRs[0]['BLDETAIL'])."\n");

		//run bl with return value (if any)
		eval('$returnValue=$$blName('.$mapParameterString.');');
	}//eof if
	else
		showNotificationError($blName.' does not exist!');

	return $returnValue;
}//eof function

//execute php bl by event
function executePhpEvent($myQuery, $event, $itemType, $itemId)
{
	//if have event, itemType and itemId
	if($event && $itemType && $itemId)
	{
		//php trigger
		$phpTrigger = "select b.TRIGGER_ID, a.BLNAME
						from FLC_BL a, FLC_TRIGGER b
						where b.TRIGGER_STATUS = 1 and a.BLNAME = b.TRIGGER_BL and b.TRIGGER_EVENT = '".$event."' and b.TRIGGER_TYPE = 'PHP'
							and b.TRIGGER_ITEM_TYPE = '".$itemType."' and b.TRIGGER_ITEM_ID = ".$itemId."
							and a.BLPARENT is null
						order by TRIGGER_ORDER";
		$phpTriggerRs = $myQuery->query($phpTrigger,'SELECT','NAME');
		$phpTriggerRsCount = count($phpTriggerRs);

		//if have php trigger
		if($phpTriggerRsCount)
		{
			//loop on count of php trigger
			for($x=0; $x<$phpTriggerRsCount; $x++)
			{
				//trigger parameter
				$getTriggerParameter = "select PARAMETER_VALUE from FLC_TRIGGER_PARAMETER
											where TRIGGER_ID = ".$phpTriggerRs[$x]['TRIGGER_ID']."
											order by PARAMETER_SEQ";
				$getTriggerParameterRs = $myQuery->query($getTriggerParameter, 'SELECT', 'NAME');
				$getTriggerParameterRsCount = count($getTriggerParameterRs);

				//if have trigger parameter
				if($getTriggerParameterRsCount)
				{
					//loop on count of trigger parameter
					for($y=0; $y<$getTriggerParameterRsCount; $y++)
					{
						//append, if have value
						if($phpTriggerRs[$x]['TRIGGER_PARAMETER'])
							$phpTriggerRs[$x]['TRIGGER_PARAMETER'] .= ',';

						//re-construct the triggers parameter
						$phpTriggerRs[$x]['TRIGGER_PARAMETER'] .= $getTriggerParameterRs[$y]['PARAMETER_VALUE'];
					}//eof for
				}//eof if

				//execute the php bl
				executeBL($phpTriggerRs[$x]['BLNAME'].'('.$phpTriggerRs[$x]['TRIGGER_PARAMETER'].')');
			}//eof for
		}//eof if
	}//eof if
}//eof function

//function to create php bl
function createPhpBl($pageId)
{
	//db connection
	include('db.php');

	//get list of bl
	$getPhpBl = "select BLID, BLNAME, BLDETAIL from FLC_BL where BLTYPE = 'PHP' and BLGLOBAL = 1 and BLSTATUS = '00' and BLPARENT is null";
	$getPhpBlRs = $myQuery->query($getPhpBl,'SELECT','NAME');
	$getPhpBlRsCount = count($getPhpBlRs);

	//if have php bl
	if($getPhpBlRsCount)
	{
		//loop on count of php bl
		for($x=0; $x<$getPhpBlRsCount; $x++)
		{
			//bl parameter
			$getBlParameter = "select PARAMETER_VALUE from FLC_BL_PARAMETER
								where BL_ID = ".$getPhpBlRs[$x]['BLID']."
								order by PARAMETER_SEQ";
			$getBlParameterRs = $myQuery->query($getBlParameter, 'SELECT', 'NAME');
			$getBlParameterRsCount = count($getBlParameterRs);

			//unset parameter
			unset($parameterStr);

			//if have parameter
			if($getBlParameterRsCount)
			{
				//loop on count of parameter
				for($y=0; $y<$getBlParameterRsCount; $y++)
				{
					//append, if have value
					if($parameterStr)
						$parameterStr .= ',';

					//re-construct the triggers parameter
					$parameterStr .= $getBlParameterRs[$y]['PARAMETER_VALUE'];
				}//eof for
			}//eof if
			else
				$parameterStr='';

			//craete the function with parameter (if any)
			$strPhpBl = "function ".$getPhpBlRs[$x]['BLNAME']."(".$parameterStr."){".$getPhpBlRs[$x]['BLDETAIL']."\n}";

			//create php bl on the fly
			$evalResult = eval($strPhpBl);

			//if bl have syntax error
			if($evalResult === FALSE)
				echo 'Parse Error: <b>'.$getPhpBlRs[$x]['BLNAME'].'</b><br>';
		}//eof for
	}//eof if
}//eof function

//function to create javascript bl
function createJsBl($pageId)
{
	//db connection
	include('db.php');

	//get list of bl
	$getJsBl = "select BLID, BLNAME, BLDETAIL from FLC_BL where BLNAME in
					( select BLNAME from
						(
						select BLNAME from FLC_BL where BLTYPE = 'JS' and BLGLOBAL = 1
						union
						select TRIGGER_BL from FLC_TRIGGER
							where TRIGGER_STATUS = 1 and TRIGGER_TYPE = 'JS' and TRIGGER_ITEM_TYPE = 'page' and TRIGGER_ITEM_ID = ".$pageId."
						union
						select TRIGGER_BL from FLC_TRIGGER
							where TRIGGER_STATUS = 1 and TRIGGER_TYPE = 'JS' and TRIGGER_ITEM_TYPE = 'component' and TRIGGER_ITEM_ID in
									(select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$pageId.")
						union
						select TRIGGER_BL from FLC_TRIGGER
							where TRIGGER_STATUS = 1 and TRIGGER_TYPE = 'JS' and TRIGGER_ITEM_TYPE = 'item' and TRIGGER_ITEM_ID in
								(select ITEMID from FLC_PAGE_COMPONENT_ITEMS where COMPONENTID in
									(select COMPONENTID from FLC_PAGE_COMPONENT where PAGEID = ".$pageId."))
						union
						select TRIGGER_BL from FLC_TRIGGER
							where TRIGGER_STATUS = 1 and TRIGGER_TYPE = 'JS' and TRIGGER_ITEM_TYPE = 'control' and TRIGGER_ITEM_ID in
								(select CONTROLID from FLC_PAGE_CONTROL where PAGEID = ".$pageId.")
					) a )
				and BLPARENT is null";
	$getJsBlRs = $myQuery->query($getJsBl,'SELECT','NAME');
	$getJsBlRsCount = count($getJsBlRs);

	//if have js bl
	if($getJsBlRsCount)
	{
		//loop on count of js bl
		for($x=0; $x<$getJsBlRsCount; $x++)
		{
			//bl parameter
			$getBlParameter = "select PARAMETER_VALUE from FLC_BL_PARAMETER
								where BL_ID = ".$getJsBlRs[$x]['BLID']."
								order by PARAMETER_SEQ";
			$getBlParameterRs = $myQuery->query($getBlParameter, 'SELECT', 'NAME');
			$getBlParameterRsCount = count($getBlParameterRs);

			//if have parameter
			if($getBlParameterRsCount)
			{
				//loop on count of parameter
				for($y=0; $y<$getBlParameterRsCount; $y++)
				{
					//append, if have value
					if($parameterStr[$x])
						$parameterStr[$x] .= ',';

					//re-construct the triggers parameter
					$parameterStr[$x] .= $getBlParameterRs[$y]['PARAMETER_VALUE'];
				}//eof for
			}//eof if
			else
				$parameterStr[$x] = '';

			//convert POST,GET,SESSION variable
			$getJsBlRs[$x]['BLDETAIL'] = convertDBSafeToQuery($getJsBlRs[$x]['BLDETAIL']);

			//encode javascript if have url
			if(BUTTON_URL_SECURITY)
				$getJsBlRs[$x]['BLDETAIL'] = url_encoding($getJsBlRs[$x]['BLDETAIL'], 'window.location','=');

			//craete the function with parameter (if any)
			$strJsBl .= "function ".$getJsBlRs[$x]['BLNAME']."(".$parameterStr[$x]."){\n".$getJsBlRs[$x]['BLDETAIL']."\n}\n";
		}//eof for

		//create js bl on the fly
		echo "<script>\n".$strJsBl."</script>\n";
	}//eof if
}//eof function

//get js trigger
function getJsTrigger($itemType, $itemId)
{
	//db connection
	include('db.php');

	//get trigger (javascript)
	$getTrigger = "select b.TRIGGER_ID, b.TRIGGER_EVENT, a.BLNAME
						from FLC_BL a, FLC_TRIGGER b
						where b.TRIGGER_STATUS = 1 and b.TRIGGER_TYPE = 'JS' and a.BLNAME = b.TRIGGER_BL
							and b.TRIGGER_ITEM_TYPE = '".$itemType."' and b.TRIGGER_ITEM_ID = ".$itemId."
							and a.BLPARENT is null
						order by b.TRIGGER_EVENT, b.TRIGGER_ORDER";
	$getTriggerRs = $myQuery->query($getTrigger,'SELECT','NAME');
	$getTriggerRsCount = count($getTriggerRs);

	//if have trigger
	if($getTriggerRsCount)
	{
		//loop on count of trigger
		for($x=0; $x<$getTriggerRsCount; $x++)
		{
			//trigger parameter
			$getTriggerParameter = "select PARAMETER_VALUE from FLC_TRIGGER_PARAMETER
										where TRIGGER_ID = ".$getTriggerRs[$x]['TRIGGER_ID']."
										order by PARAMETER_SEQ";
			$getTriggerParameterRs = $myQuery->query($getTriggerParameter, 'SELECT', 'NAME');
			$getTriggerParameterRsCount = count($getTriggerParameterRs);

			//if have trigger parameter
			if($getTriggerParameterRsCount)
			{
				//loop on count of trigger parameter
				for($y=0; $y<$getTriggerParameterRsCount; $y++)
				{
					//append, if have value
					if($getTriggerRs[$x]['TRIGGER_PARAMETER'])
						$getTriggerRs[$x]['TRIGGER_PARAMETER'] .= ',';

					//re-construct the triggers parameter
					$getTriggerRs[$x]['TRIGGER_PARAMETER'] .= $getTriggerParameterRs[$y]['PARAMETER_VALUE'];
				}//eof for
			}//eof if

			//switch trigger type
			switch($getTriggerRs[$x]['TRIGGER_EVENT'])
			{
				case 'onblur': $onblur .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onchange': $onchange .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onclick': $onclick .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'ondblclick': $ondblclick .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onfocus': $onfocus .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onkeydown': $onkeydown .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onkeypress': $onkeypress .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onkeyup': $onkeyup .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmousedown': $onmousedown .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmousemove': $onmousemove .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmouseout': $onmouseout .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmouseover': $onmouseover .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onmouseup': $onmouseup .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onselect': $onselect .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;

				case 'onload': $onload .= $getTriggerRs[$x]['BLNAME'].'('.$getTriggerRs[$x]['TRIGGER_PARAMETER'].');';
					break;
			}//eof switch
		}//eof for

		//return value in array
		return array('onblur'=>$onblur, 'onchange'=>$onchange, 'onclick'=>$onclick, 'ondblclick'=>$ondblclick, 'onfocus'=>$onfocus,
					'onkeydown'=>$onkeydown, 'onkeypress'=>$onkeypress, 'onkeyup'=>$onkeyup, 'onmousedown'=>$onmousedown,
					'onmousemove'=>$onmousemove, 'onmouseout'=>$onmouseout, 'onmouseover'=>$onmouseover, 'onmouseup'=>$onmouseup,
					'onselect'=>$onselect, 'onload'=>$onload);
	}//eof if
}//eof function

//FOR SENDING MAIL WITH ATTACHMENT
//author	: ikhmal enc
//date		: 22/8/2013
//example	: flc_mail('ali@ali.com','abu@abu.com','Test email','this is a test email','upload1,upload2');
//http://www.jeremytunnell.com/posts/really-hairy-problem-with-seemingly-random-crlf-and-spaces-inserted-in-emails
//http://stackoverflow.com/questions/9999908/php-mail-function-randomly-adds-a-space-to-message-text

//http://www.sitepoint.com/forums/showthread.php?602443-Extra-Spaces-Added-by-Mail-Server-Client-in-HTML-Email
//So it turns out if the URL/Path to the image or link is really long, the mail client sometimes puts a space (%20) into the path which causes the loading of the image to fail.
//Shortening the path to the image fixed the issue.
function flc_mail($from,$to,$subject,$msg,$filename,$filearray=null)
{
	//---------------
	//php.ini example
	//---------------
	/*
		[mail function]
		SMTP = smtp.gmail.com
		smtp_port = 587
		sendmail_from = ikhmalhisyam@gmail.com
	*/
	//--------------------
	//sendmail.ini example
	//--------------------
	/*
		smtp_server=smtp.gmail.com
		smtp_port=587
		smtp_ssl=auto
		;smtp_ssl=tls
		error_logfile=error.log
		pop3_server=
		pop3_username=
		pop3_password=
		force_recipient=
		hostname=

		auth_username=ikhmalhisyam@gmail.com
		auth_password=xxx
		force_sender=ikhmalhisyam@gmail.com
	*/

	ini_set('max_execution_time','180');	//secs
	ini_set('max_input_time','180');		//secs
	ini_set('upload_max_filesize','20M');	//max allowed size for uploaded files
	ini_set('max_file_uploads','20');		//max number of files allowed

	//$cc   = "cc@labnol.org";
	//$bcc  = "bcc@labnol.org";
	//http://ctrlq.org/code/19840-base64-encoded-email
	 
	$boundary = str_replace(" ", "", date('l jS \of F Y h i s A'));
	$newline  = "\r\n";
	
	$headers = "From: $from$newline".
			   "Cc: $cc$newline".
			   "Bcc: $bcc$newline".
			   "MIME-Version: 1.0$newline".
			   "Content-Type: multipart/mixed;".
			   "boundary = \"$boundary\"$newline$newline".
			   "--$boundary$newline".
			   "Content-Type: text/html; charset=ISO-8859-1$newline".
			   "Content-Transfer-Encoding: base64$newline$newline";
	 
	
	 
	

	
	
	//include the from email in the headers
	$headers = "From: ".$from;

	//boundary
	$time = md5(time());
	$boundary = "==Multipart_Boundary_x{".$time."}x";

	//headers used for send attachment with email
	$headers .= "\nMIME-Version: 1.0\n"."Content-Type: multipart/mixed;\n"." boundary=\"{".$boundary."}\"";
	
//	$headers = rtrim(chunk_split(base64_encode($html)));

	//multipart boundary
	//$msg = 	"--{".$boundary."}\n" ."Content-Type: text/html; charset=\"iso-8859-1\"\n"."Content-Transfer-Encoding: 7bit\n\n".$msg."\n\n";		//ikhmal
	$msg = 	"--{".$boundary."}\n" ."Content-Type: text/html; charset=\"iso-8859-1\"\n"."Content-Transfer-Encoding: base64\n\n".$msg."\n\n";			//cikkim 20150621
	$msg = html_entity_decode($msg);
	//$msg = rtrim(chunk_split(base64_encode($msg)));			//cikkim 20150621
	//$msg .= "--{".$boundary."}\n";

	//echo $msg;
	//echo '<br><br>';
	
	$uploadName = array();
	$uploadTmp = array();

	/*
	$array = is_array($filearray[$filename]['name']) ? "yes" : "no";
	if ($array=='yes'){
		$uploadName = $filearray[$filename]['name'];
		$uploadTmp  = $filearray[$filename]['tmp_name'];

	}else{
		$uploadName[] = $filearray[$filename]['name'];
		$uploadTmp[] = $filearray[$filename]['tmp_name'];
	}
	*/
	
	$uploadName[] = $_FILES['test']['name'];
	$uploadTmp[] = $_FILES['test']['tmp_name'];
	
	$uploadedFileArrName = array_filter($uploadName);
	$uploadedFileArrTmp  = array_filter($uploadTmp);

	echo $countupload = count($uploadedFileArrName);
	
	print_r($uploadedFileArrName);
	print_r($uploadedFileArrTmp);
	

	if ($countupload == 0){
		$sendmail = mail($to, $subject, $msg, $headers);
	}else{
	//attach the attachments to the message
		for($x=0; $x < $countupload; $x++)
		{
			$file = fopen($uploadedFileArrTmp[$x],"r");
			echo $content = fread($file,filesize($uploadedFileArrTmp[$x]));
			fclose($file);
			$content = chunk_split(base64_encode($content));
			$msg 	.= 	"Content-Type: {\"application/octet-stream\"};\n"." name=\"".$uploadedFileArrTmp[$x]."\"\n".
						"Content-Disposition: attachment;\n"." filename=\"".$uploadedFileArrName[$x]."\"\n".
						"Content-Transfer-Encoding: base64\n\n".$content."\n\n";
			$msg 	.= "--{".$boundary."}\n";
		}
	$sendmail = mail($to,$subject,$msg,$headers);
	}
	
	//echo $headers;
	//echo '<br>';
	//echo $msg;
	
	//new
	//$headers .= rtrim(chunk_split(base64_encode($msg)));
	//mail($to,$subject,"",$headers);			//baru
	
	//verify if mail is sent or not
	if ($sendmail)
		echo "Email successfully sent!";
	else
		echo "Email not sent. Error occurred. Try again!";
}
/*
Generate Menu Icon Styles (Luqman,11/9/2013)
Use theme's css file to properly arrange icons with padding
*/
function GenerateMenuIconStyles($myQuery)
{
    //universal (child & parent)
    $default_style = "a.hasChild, a.noChild {background-repeat:no-repeat; background-size:".MENU_ICON_WIDTH."px ".MENU_ICON_HEIGHT."px;}\n";

    //each menu icon (visible only and have menuicon)
    $menus = $myQuery->query("select MENUID, MENUICON from FLC_MENU where MENUSTATUS = '1' and MENUICON is not null",'SELECT','NAME');

	$styles = '';

    foreach((array)$menus as $menu)
    	$styles .= "#menu_id_".$menu['MENUID']."{background-image:url('".$menu['MENUICON']."') !important;}";

    //default icons
    $styles .= "a.hasChild {background-image:url('img/icon_default_folder.png');}\n";
    $styles .= "a.noChild {background-image:url('img/icon_default_file.png');}\n";

    echo '<style type="text/css">' . $default_style . $styles . '</style>';
}//eof function

/*
Display Flag Language Selector
*/
function DisplayLanguageSelector($myQuery)
{
	if(FLC_LANGUAGE)
	{
		//active language
		$q = "select LANG_NAME,LANG_FLAG_URL from FLC_TRANSLATION_LANGUAGE where LANG_ID = ".$_SESSION['language'];
		$r = $myQuery->query($q,'SELECT','NAME');
		$name = $r[0]['LANG_NAME'];
		$flag = $r[0]['LANG_FLAG_URL'];

		//all language
		$q = "select LANG_ID,LANG_NAME, LANG_FLAG_URL from FLC_TRANSLATION_LANGUAGE where LANG_STATUS = 1";
		$allLangs = $myQuery->query($q,'SELECT','NAME');

		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$html .= '<span onclick="LanguageSelectorPopup()" id="languageName">'.$name.'</span><img id="languageFlag" src="'.$flag.'">';
		$html .= '<div id="languageSelectorPopup" style="display:none; position: absolute; padding:7 10px 10px 10px; background-color: #ffffff; border: 5px solid #EBEBEB; top: 20px; left: 50%; width: 250px; max-height:150px;height: 130px; margin-left: -125px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; -webkit-box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.46); -moz-box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.46); box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.46); overflow-y:auto">';
		$html .= '<h3 style="text-align:center">BAHASA / LANGUAGE</h3>';
		$html .= '<table cellpadding="3" style="font-size:12px;text-align:left;" align="center">';
		foreach((array)$allLangs as $language)
		{
			$html .= '<tr><td><img src="'.$language['LANG_FLAG_URL'].'"/></td><td><a style="'.($language['LANG_ID']==$_SESSION['language'] ? 'color:#85B82C; text-decoration:none; font-weight:bold;' : 'color:inherit; text-decoration:none;').'" href="'.$url.'&lang='.$language['LANG_ID'].'">'.$language['LANG_NAME'].'</a></td></tr>';
		}
		$html .= '</table>';
		$html .= '</div>';
		return $html;
	}
	else return '';
}

/*
Load user theme
*/
function LoadUserTheme($myQuery)
{
	if($_SESSION['userID'])
	{
		$_SESSION['THEME_AND_FOLDER_SET'] = false;
		$_SESSION['LAYOUT_SET'] = false;

		//proses theme & folder
		if(!$_SESSION['THEME_AND_FOLDER_SET'])
		{
			$getTheme = "select THEME from PRUSER where USERID = ".$_SESSION['userID'];
			$getThemeRs = $myQuery->query($getTheme,'SELECT','INDEX');
			$getThemeRs = $getThemeRs[0][0];

			//if theme not set, get theme from group is there's any
			if($getThemeRs == '')
			{
				$getGroupTheme = "select GROUP_THEME from FLC_USER_GROUP where GROUP_ID = (select max(GROUP_ID) GROUP_ID from FLC_USER_GROUP_MAPPING
										where USER_ID = ".$_SESSION['userID'].")";
				$getGroupThemeRs = $myQuery->query($getGroupTheme,'SELECT','INDEX');
				$getThemeRs = $getGroupThemeRs[0][0];
			}

			if($getThemeRs == '') $getThemeRs = DEFAULT_THEME;
			$_SESSION['THEME'] = $getThemeRs;

			$getThemeFolder = "select EXT_PATH from FLC_EXTENSION where EXT_ID = ".$getThemeRs;
			$getThemeFolderRs = $myQuery->query($getThemeFolder,'SELECT','INDEX');
			$getThemeFolderRs = $getThemeFolderRs[0][0];
			$_SESSION['THEME_FOLDER'] = $getThemeFolderRs;

			$_SESSION['THEME_AND_FOLDER_SET'] = true;
		}
		else
		{
			$getThemeRs = $_SESSION['THEME'];
			$getThemeFolderRs = $_SESSION['THEME_FOLDER'];
		}

		//process layout
		if(!$_SESSION['LAYOUT_SET'])
		{
			$getLayoutRs = $myQuery->query("select LAYOUT from PRUSER where USERID = ".$_SESSION['userID'],'SELECT','INDEX');
			$getLayoutRs = $getLayoutRs[0][0];

			//if layout not set, get layout from group is there's any
			if($getLayoutRs == '')
			{
				$getGroupLayout = "select GROUP_LAYOUT from FLC_USER_GROUP where GROUP_ID = (select max(GROUP_ID) GROUP_ID from FLC_USER_GROUP_MAPPING
										where USER_ID = ".$_SESSION['userID'].")";
				$getGroupLayoutRs = $myQuery->query($getGroupLayout,'SELECT','INDEX');
				$getLayoutRs = $getGroupLayoutRs[0][0];
			}

			if($getLayoutRs == '') $getLayoutRs = DEFAULT_LAYOUT;
			$_SESSION['LAYOUT'] = $getLayoutRs;

			$_SESSION['LAYOUT_SET'] = true;
		}
		else
			$getLayoutRs = $_SESSION['LAYOUT'];
	}
	else //public theme/layout atau first landing pada login page
	{
		$getThemeRs = DEFAULT_THEME;
		$_SESSION['THEME'] = $getThemeRs;

		$getThemeFolder = "select EXT_PATH from FLC_EXTENSION where EXT_ID = ".$getThemeRs;
		$getThemeFolderRs = $myQuery->query($getThemeFolder,'SELECT','INDEX');
		$getThemeFolderRs = $getThemeFolderRs[0][0];
		$_SESSION['THEME_FOLDER'] = $getThemeFolderRs;

		$getLayoutRs = DEFAULT_LAYOUT;
		$_SESSION['LAYOUT'] = $getLayoutRs;
	}

	return $getLayoutRs;
}

//settings for hide/show menu & header
function BlockDisplayProperties()
{
	if(isset($_SESSION['displayHeader']) && $_SESSION['displayHeader']==0)
	{
		$styles['headerStyle'] = 'style="display:none"';
		$styles['contentStyle'] = 'style="top:'.$_SESSION['contentTop'].'px"';
		$styles['topMenuBarStyle'] = 'style="top:0;"';
		$styles['sidebarStyle'] = 'style="margin-top:0px;"';
	}
	if(isset($_SESSION['displayTopBar']) && $_SESSION['displayTopBar']==0)
	{
		$styles['topMenuBarStyle'] = 'style="display:none;"';
		$styles['contentStyle'] = 'style="top:'.$_SESSION['contentTop'].'px"';
	}
	if(isset($_SESSION['displaySidebar']) && $_SESSION['displaySidebar']==0)
	{
		$styles['sidebarStyle'] = 'style="display:none;"';
		$styles['contentStyle'] = (PAGE_BORDER_ENABLED ? 'style="margin:10px;"' : 'style="margin-left:0px; margin-right:0px;"');
	}

	return $styles;
}

function setDefaultLanguage()
{
	//set language
	if(!isset($_SESSION['language']))
	{
		if(FLC_LANGUAGE && FLC_LANGUAGE_DEFAULT)
			$_SESSION['language'] = FLC_LANGUAGE_DEFAULT;
		else
			$_SESSION['language'] = 2;
	}//eof if
}

function checkIfLanguageChanged($myQuery)
{
	if($_GET['lang'] || $_GET['menuForceRefresh'])
	{
		$_SESSION['language'] = $_GET['lang'];
		$_POST['menuForceRefresh'] = 1;

		getTranslation($myQuery,$_SESSION['language'],$dbc);
	}
}

function checkDebugStatus()
{
	if($_GET['debug'] == 'on')
		$_SESSION['debug'] = true;
	else if(!isset($_SESSION['debug']) || $_GET['debug'] == 'off')
		$_SESSION['debug'] = false;
}

function urlSecurity()
{
	//if url security is enabled
	if(URL_SECURITY)
	{
		$decodeURL = flc_url_decode($_GET['a']);		//decode the url
		stringTo_GET($decodeURL);
	}
}

////todo - cips officecentral
//ckm 20131106
function createXMLFromQry($myQuery,$qry,$parentName,$downloadFlag=false)
{
	$xml = new SimpleXMLElement('<xml/>');
	$data = $myQuery->query($qry,'SELECT','NAME');
	$dataColName = array_keys($data[0]);
	$dataCnt = count($data);

	for ($x=0; $x < $dataCnt; $x++)
	{
		$childNode = $xml->addChild($parentName);
		//$childNode->addAttribute('order',$x+1);

		for ($y=0; $y < count($dataColName); $y++)
			$childNode->addChild($dataColName[$y],$data[$x][$dataColName[$y]]);
	}

	header('Content-type: text/xml');

	if($downloadFlag)
		header('Content-Disposition: attachment; filename="'.$parentName.'.xml"');

	print($xml->asXML());
}

function createJSONFromQry($myQuery,$qry)
{
	$data = $myQuery->query($qry,'SELECT','NAME');
	return json_encode($data);
}

//reformat index array to associative array
function reformatStructureArr($array)
{
	$arrayCount = count($array);
	for($i=0; $i<$arrayCount; $i++)
	{
		//check if array has another array inside
		if(is_array($array[$i]))
		{ 
			foreach ($array[$i] as $key => $value)
			{	
				//create associative array
				$structInner[$key] = $value;				
			}

			$newStruct[data][] = $structInner;
		
		}
		else
		{
			$newStruct[data] = $array[$i];		
		}//eof if
	}//eof for

	return $newStruct;
}

function reassignRandomNo($var)
{	//for uap
	foreach($var as $key => $val)
	{
		$key = str_replace('_','',$key);
		similar_text($key,'randomNo', $percentSimilar);
		
		if($key == 'randomNo' || $percentSimilar > 90)
			$_GET['randomNo'] = $val;
	}
}

function getStatusCodeUsr($myQuery,$username,$password){
		$getStat = "select STATUSCODE from PRUSER where USERNAME = '".$username."' and USERPASSWORD= '$password'";
		$getStatRs = $myQuery->query($getStat,'SELECT');
		return $getStatRs[0][0];
}

// Add by nima 08102015
// http://webcheatsheet.com/php/blocking_system_access.php
//start
function confirmIPAddress($myQuery,$ip) {
	/*
	$sql = "SELECT ATTEMPTS, (CASE when LASTLOGIN is not NULL and DATE_ADD(LASTLOGIN, INTERVAL ".TIME_PERIOD." MINUTE)>NOW() then 1 else 0 end) as Denied ".
   " FROM ".TBL_ATTEMPTS." WHERE ip = '$ip'";	
	$sqlRs = $myQuery->query($sql,'SELECT','NAME');

   //Verify that at least one login attempt is in database

   if (!$sqlRs) {
     return 0;
   } 
   if ($sqlRs[0]["ATTEMPTS"] >= ATTEMPTS_NUMBER)
   {
      if($sqlRs[0]["Denied"] == 1)
      {
         return 1;
      }
     else
     {
        clearLoginAttempts($myQuery,$ip);
        return 0;
     }
   }
   return 0;  
   */
  }
   
   function addLoginAttempt($myQuery,$ip) {
	   /*
   // increase number of attempts
   // set last login attempt time if required    
	 $sql = "SELECT * FROM ".TBL_ATTEMPTS." WHERE IP = '$ip'"; 
	  $sqlRs = $myQuery->query($sql,'SELECT','NAME');
	  
	  if($sqlRs)
      {
        $attempts = $sqlRs[0]["ATTEMPTS"]+1;

        if($attempts==3) {
		 $sql = "UPDATE ".TBL_ATTEMPTS." SET ATTEMPTS=".$attempts.", LASTLOGIN=NOW() WHERE IP = '$ip'";
		 $result = $myQuery->query($sql,'RUN');
		}
        else {
		 $sql = "UPDATE ".TBL_ATTEMPTS." SET ATTEMPTS=".$attempts." WHERE IP = '$ip'";
		  $result = $myQuery->query($sql,'RUN');
		}
       }
      else {
	   $sql = "INSERT INTO ".TBL_ATTEMPTS." (ATTEMPTS,IP,LASTLOGIN) values (1, '$ip', NOW())";
	   $result = $myQuery->query($sql,'RUN');
	  }
	  */
    }
	
	function clearLoginAttempts($myQuery,$ip) {
		/*
		$sql = "UPDATE ".TBL_ATTEMPTS." SET ATTEMPTS = 0 WHERE IP = '$ip'"; 
		$result = $myQuery->query($sql,'RUN');
		*/
   }
 //end function for blocking access
?>
