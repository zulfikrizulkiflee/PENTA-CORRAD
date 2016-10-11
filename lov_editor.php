<?php
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');
validateUserSession();

//switch page
switch($_GET['editor'])
{
	//menu editor
	case 'menu':
		//defaultValue
		if($_GET['type']=='menu')
		{
			//get all menu (recursively)
			$recursiveMenuList = $mySQL->getMenuList('',false);

			//list of available menu
			$menuList = assambleRecursiveMenu($recursiveMenuList);
			//$menuListCount = count($menuList);

			//add for no parent
			$menuListTemp[0]['MENUID'] = 0;
			$menuListTemp[0]['MENUTITLE'] = 'None';

			//merge the array
			$menuList = array_merge($menuListTemp, $menuList);
			$menuListCount = count($menuList);

			//loop on count of menu
			for($x=0, $y=0; $x<$menuListCount; $x++)
			{
				//filter name
				if(!$_POST['value'] || stripos($menuList[$x]['MENUTITLE'],$_POST['value']) !== false || stripos($menuList[$x]['MENUBREADCRUMBS'],$_POST['value']) !== false)
				{
					//assign menu into lov list
					$lovListRs[$y]['FLC_ID'] = $menuList[$x]['MENUID'];
					$lovListRs[$y]['FLC_NAME'] = $menuList[$x]['MENUBREADCRUMBS'].$menuList[$x]['MENUTITLE'];

					//increment lov index
					$y++;
				}//eof if
			}//eof for

			$lovListRsCount = count($lovListRs);
		}//eof if
	break;

	//page editor
	case 'page':
		//defaultValue
		if($_GET['type']=='url')
		{
			//get all menu (recursively)
			$recursiveMenuList = $mySQL->getMenuList('',false);

			//list of available menu
			$menuList = assambleRecursiveMenu($recursiveMenuList);
			$menuListCount = count($menuList);

			//add login into menu
			$menuListTemp[0]['MENULINK'] = 'login.php';
			$menuListTemp[0]['MENUTITLE'] = 'Login';

			//add logout into menu
			$menuListTemp[1]['MENULINK'] = 'index.php?logout=true';
			$menuListTemp[1]['MENUTITLE'] = 'Logout';

			//merge the array
			$menuList = array_merge($menuListTemp, $menuList);

			//loop on count of menu
			for($x=0, $y=0; $x<$menuListCount; $x++)
			{
				//not parent menu
				if(!$menuList[$x]['MENUCHILD'])
				{
					//filter name
					if(!$_POST['value'] || stripos($menuList[$x]['MENUTITLE'],$_POST['value']) !== false || stripos($menuList[$x]['MENUBREADCRUMBS'],$_POST['value']) !== false)
					{
						//assign menu into lov list
						$lovListRs[$y]['FLC_ID'] = $menuList[$x]['MENULINK'];
						$lovListRs[$y]['FLC_NAME'] = $menuList[$x]['MENUBREADCRUMBS'].$menuList[$x]['MENUTITLE'];

						//append menuid into link
						if($menuList[$x]['MENUID'])
							$lovListRs[$y]['FLC_ID'] .= '&menuID='.$menuList[$x]['MENUID'];

						//increment lov index
						$y++;
					}//eof if
				}//eof if
			}//eof for

			$lovListRsCount = count($lovListRs);
		}//eof if
	break;

	//trigger editor
	case 'trigger':
		//list of active bl
		$lovList = "select BLNAME as FLC_ID, BLNAME as FLC_NAME
						from FLC_BL
						where BLTYPE = '".$_GET['type']."' and BLSTATUS = '00' and BLPARENT is null
							and upper(BLNAME) like upper('%".$_POST['value']."%')
							and BLPARENT is null
						order by BLNAME";
		$lovListRs = $myQuery->query($lovList, 'SELECT', 'NAME');
		$lovListRsCount = count($lovListRs);
	break;
}//eof switch

//get layout
if(!isset($_SESSION['LAYOUT']) || $_SESSION['LAYOUT'] == '')
{
	$getLayout = "select LAYOUT from PRUSER where USERID = ".$_SESSION['userID'];
	$getLayoutRs = $myQuery->query($getLayout,'SELECT','INDEX');
	$getLayoutRs = $getLayoutRs[0][0];
	$_SESSION['LAYOUT'] = $getLayoutRs;
}
else
{
	$getLayoutRs = $_SESSION['LAYOUT'];
}


//get theme
if( !isset($_SESSION['THEME']) && !isset($_SESSION['THEME_FOLDER']))
{
	$getTheme = "select THEME from PRUSER where USERID = ".$_SESSION['userID'];
	$getThemeRs = $myQuery->query($getTheme,'SELECT','INDEX');
	$getThemeRs = $getThemeRs[0][0];
	$_SESSION['THEME'] = $getThemeRs;

	$getThemeFolder = "select EXT_PATH from FLC_EXTENSION where EXT_ID = ".$getThemeRs;
	$getThemeFolderRs = $myQuery->query($getThemeFolder,'SELECT','INDEX');
	$getThemeFolderRs = $getThemeFolderRs[0][0];
	$_SESSION['THEME_FOLDER'] = $getThemeFolderRs;
}
else
{
	$getThemeRs = $_SESSION['THEME'];
	$getThemeFolderRs = $_SESSION['THEME_FOLDER'];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>
<?php echo APP_FULL_NAME;?>
</title>
<link href="css/layout<?php echo $_SESSION['LAYOUT']; ?>.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $_SESSION['THEME_FOLDER']; ?>/main.css" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" type="image/x-icon" href="img/logo.ico">
<script language="javascript" type="text/javascript" src="tools/jquery.js"></script>
<script>jQuery = jQuery.noConflict();</script>
<script language="javascript" type="text/javascript" src="js/common.js"></script>

<script>
//return the selected lov value
function returnLovValue(index)
{
	<?php if($_GET['lovValue']){?>
	window.opener.document.getElementById('<?php echo $_GET['lovValue'];?>').value = document.getElementById('hiddenValue_'+index).value;

	//if have onchange
	if(window.opener.document.getElementById('<?php echo $_GET['lovValue'];?>').onchange)
		window.opener.document.getElementById('<?php echo $_GET['lovValue'];?>').onchange();
	<?php }?>

	<?php if($_GET['lovText']){?>
	window.opener.document.getElementById('<?php echo $_GET['lovText'];?>').value = document.getElementById('hiddenText_'+index).value;

	//if have onchange
	if(window.opener.document.getElementById('<?php echo $_GET['lovText'];?>').onchange)
		window.opener.document.getElementById('<?php echo $_GET['lovText'];?>').onchange();
	<?php }?>

	window.close();
}//eof function
</script>
</head>

<body style="margin:0px; padding:0px;">
<div id="content" class="lov">
<form id="form1" name="form1" method="post">
  <table width="100%" border="0" cellpadding="3" cellspacing="0" class="tableContent" style="margin-left:0px; width:100%;" >
    <tr>
      <th colspan="2">Filter</th>
    </tr>
    <tr>
      <td class="inputLabel">Value : </td>
      <td><input name="value" type="text" id="value" size="30" value="<?php echo $_POST['value'];?>" /></td>
    </tr>
    <tr>
      <td colspan="2" class="contentButtonFooter">
          <input name="filter" type="submit" class="inputButton" id="filter" value="Filter" />
      </td>
    </tr>
  </table>
  <br />

    <table border="0" cellpadding="3" cellspacing="0" class="tableContent" style="margin-left:0px; width:100%;" >
      <tr>
        <th colspan="2">List of Values </th>
      </tr>
      <?php if($lovListRsCount){?>
      <tr>
        <td width="12" class="listingHead">#</td>
        <td class="listingHeadRight">Value</td>
      </tr>
      <?php for($x=0; $x<$lovListRsCount; $x++){?>
      <tr>
        <td class="listingContent"><?php echo ($x+1).".";?>&nbsp;</td>
        <td class="listingContentRight">
          <a href="javascript:void(0)" onclick="returnLovValue(<?php echo $x;?>)"><?php echo $lovListRs[$x]['FLC_NAME'];?></a>
          <input name="hiddenValue_<?php echo $x;?>" type="hidden" id="hiddenValue_<?php echo $x;?>" value="<?php echo $lovListRs[$x]['FLC_ID'];?>" />
          <input name="hiddenText_<?php echo $x;?>" type="hidden" id="hiddenText_<?php echo $x;?>" value="<?php echo $lovListRs[$x]['FLC_NAME'];?>" />
        </td>
      </tr>
      <?php }?>
      <?php }else{?>
      <tr>
        <td colspan="2" class="myContentInput">&nbsp;&nbsp;No value(s) found.. </td>
      </tr>
      <?php }?>
      <tr>
        <td colspan="2" class="contentButtonFooter">
            <input name="close" type="button" class="inputButton" value="Tutup" onclick="window.close()" />
        </td>
      </tr>
    </table>
</form>
</div>
</body>
</html>
