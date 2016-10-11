<?php
require_once('system_prerequisite.php');

//validate that user have session
require_once('func_common.php');
validateUserSession();


//get theme list
$q = "select EXT_ID, EXT_TYPE, EXT_NAME, EXT_TITLE, EXT_DESC, EXT_VERSION, EXT_AUTHOR, EXT_AUTHOR_URL, EXT_ICON, EXT_PATH, EXT_STATUS
		from FLC_EXTENSION
		where EXT_TYPE = 'theme' and EXT_STATUS = 1";
$theme_list = $myQuery->query($q,'SELECT','NAME');

?>

<script type="text/javascript">
	function ChangeLayout(number)
	{
		jQuery.ajax({
			url:'themes/ajax.php',
			type:'post',
			data:{task:'ChangeLayout', type:number}
		}).done(function(){
			location.reload(true);
		});
	}

	function ChangeTheme(id)
	{
		jQuery.ajax({
			url:'themes/ajax.php',
			type:'post',
			data:{task:'ChangeTheme', themeId:id}
		}).done(function(){
			location.reload(true);
		});
	}
</script>


<div id="breadcrumbs">Profile / Themes</div>
<h1>Themes</h1>

<form name="form1" id="form1" method="post" action="">

	<table cellpadding="0" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="3">Layout</th>
		</tr>
		<tr>
			<td style="text-align:center; padding:15px;">
				<img src="img/layout_left.png"/>
				<p>Left Sidebar</p>
				<input type="button" name="btnLayoutLeft" class="inputButton" onclick="ChangeLayout(1)" value="Apply Layout"/>
			</td>
			<td style="text-align:center; padding:15px;">
				<img src="img/layout_top.png"/>
				<p>Top Menu</p>
				<input type="button" name="btnLayoutTop" class="inputButton" onclick="ChangeLayout(3)" value="Apply Layout"/>
			</td>
			<td style="text-align:center; padding:15px;">
				<img src="img/layout_right.png"/>
				<p>Right Sidebar</p>
				<input type="button" name="btnLayoutRight" class="inputButton" onclick="ChangeLayout(2)" value="Apply Layout"/>
			</td>
		</tr>
	</table>

	<br/>

	<table cellpadding="0" cellspacing="0" class="tableContent">
		<tr>
			<th colspan="2">Theme</th>
		</tr>
		<?php
		if($theme_list != '')
		{
			foreach($theme_list as $theme)
			{
				$folder = $theme['EXT_PATH'];

				//get status
				$status = ($_SESSION['THEME'] == $theme['EXT_ID'] ? '<span style="color:green">ACTIVATED</span>' : '<span style="color:red">NOT ACTIVE</span>');


				echo '<tr>';
				echo '<td style="padding:15px; width:250px;"><img style="width:200px; height:200px; padding:10px; border:1px solid #bbbbbb" src="'.$folder.'/'.$theme['EXT_ICON'].'"/></td>';
				echo '<td style="padding:15px;">
					  <table border="0">
					  	<tr>
					  		<td style="border:none; text-align:right;">Name</td>
					  		<td style="border:none;">'.$theme['EXT_TITLE'].'</td>
					  	</tr>

					  	<tr>
					  		<td style="border:none; text-align:right;">Author</td>
					  		<td style="border:none;">'.$theme['EXT_AUTHOR'].'</td>
					  	</tr>
					  	<tr>
					  		<td style="border:none; text-align:right;">Version</td>
					  		<td style="border:none;">'.$theme['EXT_VERSION'].'</td>
					  	</tr>
					  	<tr>
					  		<td style="border:none; text-align:right;">Folder Path</td>
					  		<td style="border:none;">: <i>'.$folder.'</i></td>
					  	</tr>
					  	<tr>
					  		<td style="border:none; text-align:right;">Status</td>
					  		<td style="border:none;">: '.$status.'</td>
					  	</tr>

					  </table>

					  <br/>

					  <input type="button" class="inputButton" value="Activate Theme" onclick="ChangeTheme('.$theme['EXT_ID'].')"/>

					  </td>';
				echo '</tr>';
			}
		}
		?>
	</table>

</form>
