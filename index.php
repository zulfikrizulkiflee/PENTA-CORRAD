<?php
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

if(PAGE_RESPONSE_ENABLED)
	$start = utime();

checkLogout($mySession,$cas,$_GET['logout']);
setDefaultLanguage();
checkIfLanguageChanged($myQuery);
checkDebugStatus();
urlSecurity();

if($_GET['menuID'])
{
	//check public page
	if(!$_SESSION['userID'])
	{
		//check public page (temp)
		$publicMenu = "select MENUID from FLC_MENU where MENUID = ".$_GET['menuID']." and LINKTYPE = 'P'";
		$publicMenuRs = $myQuery->query($publicMenu,'SELECT','NAME');
		$publicMenuRsCount = count($publicMenuRs);
		
		//get accessible menu
		if($publicMenuRsCount>0)
		{
			$_SESSION['MENU'] = $mySQL->getMenuList(0, false, false, true);	//get public menu
			$_SESSION['PUBLIC'] = true;
		}//eof if
	}//eof if
}//eof if

//if have post
if(is_array($_POST))
	$_POST = convertToDBSafe($_POST);

//get accessible menu
if((!$_SESSION['MENU'] || $_POST['menuForceRefresh']) && $_SESSION['userID'])
	$_SESSION['MENU'] = $mySQL->getMenuList();

//get theme,theme_folder & layout
$getLayoutRs = LoadUserTheme($myQuery);

//get settings for hide/show menu & header
$styles = BlockDisplayProperties();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo APP_FULL_NAME;?></title>
<link href="img/logo.ico" rel="shortcut icon" type="image/x-icon" />
<link href="css/layout<?php echo $getLayoutRs; ?>.css" rel="stylesheet" type="text/css"/>
<link href="tools/datepicker/css/datepicker.css" rel="stylesheet" type="text/css" />
<link href="tools/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
<script language="javascript" type="text/javascript" src="tools/prototype.js"></script>
<script language="javascript" type="text/javascript" src="tools/jquery.js"></script>
<script language="javascript" type="text/javascript" src="tools/jquery-ui.js"></script>
<script language="javascript" type="text/javascript" src="tools/json2.min.js"></script>
<script type="text/javascript">jQuery = jQuery.noConflict();</script>
<!--<script language="javascript" type="text/javascript" src="tools/scriptaculous/scriptaculous.js"></script>-->
<script language="javascript" type="text/javascript" src="js/common.js"></script>
<script language="javascript" type="text/javascript" src="js/maths_functions.js" defer="defer"></script>
<script language="javascript" type="text/javascript" src="js/string_functions.js"  defer="defer"></script>
<script language="javascript" type="text/javascript" src="js/func_lib.js"  defer="defer"></script>
<script language="javascript" type="text/javascript" src="tools/datepicker/js/datepicker.packed.js">{"describedby":"fd-dp-aria-describedby"}</script>
<script language="javascript" type="text/javascript" src="tools/perfect-scrollbar/jquery.mousewheel.js" defer="defer"></script>
<script language="javascript" type="text/javascript" src="tools/perfect-scrollbar/perfect-scrollbar.js" defer="defer"></script>
<!--
<link rel="stylesheet" href="tools/flexigrid/style.css" />
<link rel="stylesheet" type="text/css" href="tools/flexigrid/css/flexigrid.pack.css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="tools/flexigrid/js/flexigrid.pack.js"></script>
-->

<?php
//=============== INCLUDE EXTENSION ==============
includeExtension('theme', 'index');
includeExtension('library', 'index');
//============= EOF INCLUDE EXTENSION ============
?>

<?php
if($_SESSION['LAYOUT']=='3') { //manage overflow top menu ?>
<script type="text/javascript">
var displayList = [];

jQuery('ul.topMenuList.menuLevel1').ready(function(){
	var list = jQuery('ul.topMenuList.menuLevel1 > li');
	var maxWidth = <?php echo TOP_MENU_MAX_WIDTH; ?>;
	var totalWidth = 0;
	list.each(function(){ totalWidth += jQuery(this).width(); });

	var turn = 1;
	var w = 0;

	list.each(function(){
		w += jQuery(this).width();

		function Categorized(id){
			if(w < maxWidth*turn){
				if(displayList[turn-1]===undefined) displayList[turn-1] = [];
				if(displayList[turn-1]!==undefined) displayList[turn-1].push(id);
			} else {
				turn += 1;
				Categorized(id);
			}
		}
		Categorized(this.id);
	});

	DisplayMenuCat(0);
});

function DisplayMenuCat(cat)
{
	jQuery('ul.topMenuList.menuLevel1 > li').each(function(){ jQuery(this).hide(); });

	var displayListCatLen = displayList[cat].length;

	for(var i=0; i < displayListCatLen; i++){
		jQuery('#'+displayList[cat][i]).css('visibility','').show();
	}
	if(cat==0 || cat<0){
		if(displayList[cat+1]!=undefined && jQuery('#buttonMenuNext').length==0){ jQuery('ul.topMenuList.menuLevel1').append('<li><a id="buttonMenuNext" onclick="DisplayMenuCat('+(cat+1)+')" style="display:block;padding:0;width:16px;text-align:center;" href="javascript:void(0)">&#187;</a></li>'); }
		else{
			jQuery('#buttonMenuNext').attr('onclick','DisplayMenuCat('+(cat+1)+')');
			jQuery('#buttonMenuNext').parent().show();
		}
	}
	if(cat>0){
		if(jQuery('#buttonMenuPrevious').length==0){ jQuery('ul.topMenuList.menuLevel1').prepend('<li><a id="buttonMenuPrevious" onclick="DisplayMenuCat('+(cat-1)+')" style="display:block;padding:0;width:16px;text-align:center;" href="javascript:void(0)">&#171;</a></li>');	}
		else{jQuery('#buttonMenuPrevious').parent().show();}
		if(displayList[cat+1]!=undefined){
			jQuery('#buttonMenuNext').attr('onclick','DisplayMenuCat('+(cat+1)+')');
			jQuery('#buttonMenuNext').parent().show();
		}
		if(displayList[cat-1]!=undefined){
			jQuery('#buttonMenuPrevious').attr('onclick','DisplayMenuCat('+(cat-1)+')');
			jQuery('#buttonMenuPrevious').parent().show();
		}
	}
}
</script>
<?php } ?>
<?php GenerateMenuIconStyles($myQuery); ?>
</head>
<?php
if(!isset($_SESSION['zoomlevel']))
	$_SESSION['zoomlevel'] = 1;

if($_GET['zoomlevel'])
$_SESSION['zoomlevel'] = $_GET['zoomlevel'];
?>
<!--
style="
transform:scale(<?php echo $_SESSION['zoomlevel'] ?>);
-webkit-transform:scale(<?php echo $_SESSION['zoomlevel'] ?>);
-webkit-transform-origin:0 0;
-moz-transform:scale(<?php echo $_SESSION['zoomlevel'] ?>);
-moz-transform-origin:0 0;
-o-transform:scale(<?php echo $_SESSION['zoomlevel'] ?>);
-o-transform-origin:0 0;
-ms-transform:scale(<?php echo $_SESSION['zoomlevel'] ?>);
-ms-transform-origin:0 0;"
-->
<body>
<!--<div id="globalLoader" style="margin:0 auto;position:absolute; width:100%;height:100%;background-color: rgba(0,0,0,0.5);z-index:100;color:#ffffff;font-size:30px;text-align:center;display:table;font-weight:bold;"><div style="display:table-cell;vertical-align:middle;">Loading..</div></div>-->
<div id="debug" style="display:none; background-color:#FA7D7D; padding-left:5px; font-weight:bold; color:white; font-size:9px;">
	<a href="index.php?debug=<?php if($_SESSION['debug'] == 'on') echo 'off'; else echo 'on';?>" style="color:white">DEBUG <?php if($_SESSION['debug'] == 'on') echo 'ON'; else echo 'OFF'; ?></a>
</div>
<?php if($_SESSION['loginFlag'] || $publicMenuRsCount) {  //if session var login flag is set to true, user is logged in ?>
<!-- LOGGED IN USER SECTION ONLY -->
<!-- MAIN CONTENT SECTION -->
<?php if(HEADER_ENABLED && $_GET['HEADER_ENABLED'] ==""){?>
<div id="header" <?php echo $styles['headerStyle']; ?>>
	<?php if($_SESSION['LAYOUT']=='3') { ?>
		<div id="hide-topbar" onclick="ToggleTopbar()"></div>
	<?php } else { ?>
		<div id="hide-sidebar" onclick="ToggleSidebar()"></div>
	<?php } ?>
	<div id="logo"></div>

	<?php if(SUB_HEADER_ENABLED && $_GET['SUB_HEADER_ENABLED'] ==""){?>
		<div id="topMenu">
		  <div id="topTime" class="topMenuDiv">
			<div id="topDateIcon" class="topIcon"></div>
			<div id="topDateText" style="display:none"><?php echo dayToMalay(date(l)).', '.monthToMalay(date('F'),'long').' '.date('d, Y');?></div>
		</div>

		  <?php
		  	if(strtoupper($_SESSION['userName']) == "PUBLIC" && MAINMENU_PUBLIC_DISABLE ) {
		  		echo "";
		  	}
		  	else {
		  		if(HOME_PAGE&&HOME_PAGE_URL) {
		  			echo '<a href="'.HOME_PAGE_URL.'">
		  				  <div id="topHomeLink" class="topMenuDiv">
		  				  	<div id="topHomeLinkIcon" class="topIcon"><br/></div>
		  				  	<div style="display:none">'.HOME_PAGE.'</div>
		  				  </div>
		  				  </a>';
		  		}

		  		if((strtoupper($_SESSION['userName']) == "PUBLIC" && LOGOUT_PUBLIC_DISABLE) || $_SESSION['PUBLIC']) {
		  			echo "";
		  		}
		  		else {
		  			if(LOGOUT&&LOGOUT_URL) {
		  				echo '<a href="javascript:void(0)" onclick="logoutFader()">
		  					  <div id="topLogoutLink" class="topMenuDiv">
		  					  	<div id="topLogoutLinkIcon" class="topIcon"><br/></div>
		  					  	<div style="display:none">'.LOGOUT.'</div>
		  					  </div></a>';
		  			}
		  		}
		  	}
		  ?>
		</div>
	<?php }?>

</div>
<?php }
if($_SESSION['LAYOUT'] == '3')
{
	?>
	<div id="topMenuBar" <?php echo $styles['topMenuBarStyle'] ?>>
		<div id="topMenuBarBG"></div>
		<?php build_menu_list($_SESSION['MENU'],1,true,$myQuery);?>
		<?php if($_SESSION['userID'] && PROFILE_ENABLED){?>
			<div id="profileName"><?php echo $_SESSION['userName']; ?></div>
		<?php } ?>
		<div class="searchMenuBlock">
			<input type="text" class="inputInput" placeholder="Search menu here.." onkeyup="flc_auto_suggest_menu(this.value)" />
			<div id="menu_suggestions" style="display:none; position:absolute; z-index:100; background-color:#fff; overflow:auto">
			</div>
		</div>
	</div>
	<?php
}
?>
<div id="sidebar" <?php echo $styles['sidebarStyle']; ?>>
    <div id="sideMenuLeft" <?php if($_GET['LEFT_MENU_ENABLED']){?> style="display:none;"<?php }?>>
        <?php include('menu_wrapper.php');?>
    </div>
	<div id="sideLeftContent">
		<?php include('page_wrapper_side.php'); ?>
	</div>
</div>

<div id="pageProperties">
	<?php if(FLC_LANGUAGE) { ?>
	<div id="languageSelector"><?php echo DisplayLanguageSelector($myQuery) ?></div>
	<?php } ?>
	|
	<div id="zoomSelector">
		<a style="text-decoration:none;font-size:10px;<?php if($_SESSION['zoomlevel']== '1') echo 'color:#3938BD;';?>" href="index.php?page=<?php echo $_GET['page'].'&menuID='.$_GET['menuID'].'&zoomlevel=1';?>">aa</a>
		<a style="text-decoration:none;font-size:12px;<?php if($_SESSION['zoomlevel']== '1.1') echo 'color:#3938BD;';?>" href="index.php?page=<?php echo $_GET['page'].'&menuID='.$_GET['menuID'].'&zoomlevel=1.1';?>">aa</a>
		<a style="text-decoration:none;font-size:14px;<?php if($_SESSION['zoomlevel']== '1.2') echo 'color:#3938BD;';?>" href="index.php?page=<?php echo $_GET['page'].'&menuID='.$_GET['menuID'].'&zoomlevel=1.2';?>">aa</a>
	</div>
    <!--
	|
	<div id="hide-header" onclick="ToggleHeader(this)"><img src="<?php echo ($_SESSION['displayHeader']==0 ? 'img/toggleon.png' : 'img/toggleoff.png'); ?>"/></div>
    -->
</div>
<div id="content" class="<?php echo ($_SESSION['LAYOUT']!='3' ? ($_SESSION['LAYOUT']==1 ? 'withSideBarLeft' : 'withSideBarRight') : 'withTopMenu') ?> <?php if(PAGE_BORDER_ENABLED){ echo 'pageBorderEnabled'; }?>" <?php echo $styles['contentStyle']; ?>>
	<!-- CENTER CONTENT SECTION -->
	<?php displayContent($dbc,$myQueryArr,$mySQL);?>
	<!-- CENTER CONTENT SECTION -->
</div>

<div id="bottom">
	<!-- FOOTER SECTION -->
	<?php if(FOOTER_ENABLED && $_GET['FOOTER_ENABLED'] =="") {?>
	<div id="<?php echo FOOTER_ID;?>">
	  <?php echo FOOTER_TEXT;?>
	<!-- PAGE GENERATION TIME -->
	<?php if(PAGE_RESPONSE_ENABLED) { ?>
	<div id="<?php echo PAGE_RESPONSE_ID?>">Response time: <?php echo pageGenerationTime($start,' secs');?> </div>
	<?php } ?>
	<!-- //END PAGE GENERATION TIME -->
	</div>
	<?php } ?>
	<!-- //END FOOTER SECTION -->
	<!-- //END MAIN CONTENT SECTION -->
</div>

<!-- SESSION TIMEOUT SECTION -->
<?php if(SESSION_TIMEOUT_DURATION){?>
<div id="sessionTimeout" style="display:none">
	<div id="sessionTimeoutBg"></div>
	<div id="sessionTimeoutDialog">
		<img src="img/warning.png"/>
		<br/><br/>
		You have been on this page for too long.<br/>Your session will expire in <br/><br/><span id="sessionTimeoutLabel"></span>
		<br/><br/>
		<input id="sessionContinue" name="sessionContinue" type="button" value="Continue?" onclick="continueSession(<?php echo SESSION_TIMEOUT_DURATION;?>*60*1000);" />
		<script>setTimeout("checkSessionTimeout();",<?php echo SESSION_TIMEOUT_DURATION;?>*60*1000); //monitorSessionActivity(<?php echo SESSION_TIMEOUT_DURATION;?>*60*1000);</script>
	</div>
</div>
<?php }?>
<!-- SESSION TIMEOUT SECTION -->
<!-- //END LOGGED IN USER SECTION ONLY -->
<?php } //END if session var login flag is set to true, user is logged in
//else show login screen
else
{	

	//header('Location: ' . 'login.php?'.$_SERVER['QUERY_STRING']);
	echo "<script>window.location = 'login.php?".$_SERVER['QUERY_STRING']."';</script>";

}
?>
</body>
</html>
<?php $debugMode = 0; ?>
<script>
<?php if($_GET['page'] == 'page_wrapper') { ?>
if(<?php echo $debugMode; ?> == true)
{
	//find all input, add hooks
	var allInput = jQuery('#content').find('input, select, textarea').not('[type="radio"],[type="checkbox"],');

	var hook = jQuery('<div class="hook">')
					.css('width','10px')
					.css('height','10px')
					.css('position','absolute')
					.css('top','10px')
					.css('left','-15px')
					.css('color','blue')
					.css('cursor','pointer')
					.html('<form method="post" target="_blank" action="index.php?page=page_editor&menuID=7"><input type="hidden" name="pageSearch" value="" /><input type="hidden" name="code" value="23" /><input type="hidden" name="showScreen" value="Show List" /><a href="javascript:void(0)" onclick="jQuery(this).parent().submit();">Edit</a></form>')
					.hide();

	allInput.parent().css('position','relative');
	allInput.parent().append(hook);

	allInput.on('mouseover',function(){
		jQuery(this).parent().find('.hook').show();
	});

	allInput.on('mouseout',function(){
		var input = this;
		setTimeout(function(){
			jQuery(input).parent().find('.hook').hide();},1000);
	});

	jQuery('#content').find('.hook').on('mouseover',function(){
		jQuery(this).css('text-decoration','underline');
	});

	jQuery('#content').find('.hook').on('mouseout',function(){
		jQuery(this).css('text-decoration','none');
	});
}
jQuery(document).ready(function(){
	//setTimeout(function(){jQuery('#content').show()},1000);
	//jQuery('#globalLoader').hide();
});
<?php } ?>
</script>
<?php 

	//disconnect all connection

	$database = getNodeChild('connection.xml','database');
	
	//disconnect dbms name
	foreach ( $database as $db )  
	{
	$id = $db->getAttribute('id');
	$engine = $db->getAttribute('engine');

	if($engine == 'yes') $id='';		
		${'myDbConn'.$id}->disconnect();		 			
	}		


?>
