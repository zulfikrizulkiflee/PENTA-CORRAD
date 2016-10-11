<?php
if($_POST['task'] == 'ajax_toggleMenu')
{
	include_once('system_prerequisite.php');
	$toggle_value = $_SESSION['toggleState'][$_POST['menuId']];
	$_SESSION['toggleState'][$_POST['menuId']] = ($toggle_value == 1 ? 0 : 1);
	exit;
}

//fetch translations
getTranslation($myQuery,$_SESSION['language']);
?>

<?php if($_SESSION['userID'] && PROFILE_ENABLED){?>
  <div id="profileBlock">
    <div class="profileImageBlock">
      <img src="<?php if($_SESSION['userImage']) echo $_SESSION['userImage']; else echo 'img/default.gif'; ?>" class="profileImage" />
    </div>

    <div class="profileTextBlock">
      <span class="profileLabel"><?php echo TR8N_SIDE_MENU_LEFT_USERNAME; ?></span> : <span class="profileValue"><?php echo ucwords($_SESSION['userName']);?></span><br />
      <span class="profileLabel"><?php echo TR8N_SIDE_MENU_LEFT_GROUP; ?></span> : <span class="profileValue"><?php echo ucwords(strtolower($_SESSION['userGroupCode']));?></span><br />
    </div>
  </div>
<?php }?>
	<div class="searchMenuBlock">
		<input type="text" class="inputInput" placeholder="Search menu here.." onkeyup="flc_auto_suggest_menu(this.value)" />
		<div id="menu_suggestions" style="position:absolute; box-shadow:#888 2px 3px 3px; -webkit-box-shadow : #888 2px 3px 3px;
															-moz-box-shadow : #888 5px 10px 10px; display:none; background-color:white;padding:0px;
															min-height:0px;max-height:200px;width:400px;overflow:auto; margin-left:10px">
		</div>
	</div>
<?php
//side menu
if($_SESSION['LAYOUT'] != '3'){
	build_menu_list($_SESSION['MENU'],1,true,$myQuery);
	?>
	<script>
	jQuery('.sideMenuList > li > a').click(function(){
		var theMenu = jQuery(this).parent().children('ul');
		var isHidden = theMenu.is(':hidden');

		if(isHidden){theMenu.slideDown('fast');}
		else{theMenu.slideUp('fast');}
	});
	</script>
	<?php
}
?>
