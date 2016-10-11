<?php 
//AUTHOR: AISHAH
//DATE: 2013/8/23	
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');
?>
<?php
if($_GET['type'] == 'menu')
{	
	$menu = "select MENUTITLE,MENULINK,MENUID,MENUPARENT,b.TRANS_TEXT from FLC_MENU a
				left join 
				(select TRANS_TEXT,TRANS_SOURCE_ID from FLC_TRANSLATION
				where TRANS_TYPE = 2
				and TRANS_SOURCE_COLUMN = 'MENUTITLE') b on b.TRANS_SOURCE_ID = a.MENUID
				where 
				(lower(a.MENUTITLE) like lower('%".$_POST['queryString']."%')
				or lower(b.TRANS_TEXT) like lower('%".$_POST['queryString']."%'))";
						
	if($_SESSION['userID'] != 1 && !$_SESSION['PUBLIC'])
		$menu .= " and MENUID in (".$mySQL->getPermissionSQL('menu',$_SESSION['userID']).")";
	else if($_SESSION['PUBLIC'])
		$menu .= " and LINKTYPE = 'P' ";
		
	$menu .= "order by 1";
		
	$menuRs = $myQuery->query($menu,'SELECT','NAME');
	$menuRsCnt = count($menuRs);
	
	//if have lookup
	if($menuRs)
	{	
		echo '<table border="0" cellspacing="1" cellpadding="0" style="border:none; margin:0px;width:100%" class="tableContent">';
		
		//loop on count of record
		for($x=0; $x < $menuRsCnt; $x++) 
		{ 	
			if($menuRs[$x]['MENULINK'] == 'index.php?page=page_wrapper')
				$menuRs[$x]['MENULINK'] .= '&menuID='.$menuRs[$x]['MENUID'];
			
			//if($x%2 == 1)
			//	$addStyle = 'style="background-color:#FAFAFA;"';
		//	else
			//	$addStyle = 'style="background-color:#ffffff;"';
			
			if($menuRs[$x]['TRANS_TEXT'] != '')
				$menuRs[$x]['TRANS_TEXT'] = ' ('.$menuRs[$x]['TRANS_TEXT'].')';
			
			if($menuRs[$x]['MENULINK'] == '')
				echo '<tr><td style="color:black;" class="listingContent" '.$addStyle.'>'.$menuRs[$x]['MENUTITLE'].'</td><tr>';
			else
				echo '<tr><td class="listingContent" '.$addStyle.'><a href="javascript:void(0)" onclick="flc_auto_suggest_menu_goto(\''.$menuRs[$x]['MENULINK'].'\')">'.$menuRs[$x]['MENUTITLE'].$menuRs[$x]['TRANS_TEXT'].'</a></td><tr>';
		}					
		echo '</table>';
	}//eof if			
	
}
else
{
	// Is there a posted query string?
	if(isset($_POST['queryString'])) {
		//$queryString = mysql_real_escape_string($_POST['queryString']);
		$queryString = $_POST['queryString'];
		
		// Is the string length greater than 0?
		if(strlen($queryString) >0)
		{			
			//lookup values
			$selectlookupRs = $myQuery->query("SELECT ITEMLOOKUP,ITEMLOOKUPDB 
							   FROM FLC_PAGE_COMPONENT_ITEMS 
							   WHERE ITEMID=".$_GET['itemid'],'SELECT','NAME');
			
			//get lookup type dbms
			$lookupDB = $selectlookupRs[0]['ITEMLOOKUPDB'];
			
			$lookupRs = ${'myQuery'.$lookupDB}->query("select * from (".convertDBSafeToQuery($selectlookupRs[0]['ITEMLOOKUP']).") a 
											where lower(FLC_NAME) like lower('%".$queryString."%')",'SELECT','NAME');

			$lookupRsCount = count($lookupRs);
			
			//get keys/column name
			if($lookupRsCount)
			{
				$lookupRsKeys = array_keys($lookupRs[0]);
				$lookupRsKeysCount = count($lookupRsKeys);
			}//eof if
		
			//if have lookup
			if($lookupRsCount)
			{	
				echo '<div style="position:absolute;right:10px;top:9px;color:#ffffff;cursor:pointer" onclick="jQuery(this).closest(\'.auto_sugg_list\').fadeOut();">[Close]</div>';
				echo '<table border="0" cellspacing="1" cellpadding="0" style="border:none; margin:0px;width:1000px;" class="tableContent"><tr>';
				for($x=1; $x < $lookupRsKeysCount; $x++ )
				{
					if($lookupRsKeys[$x] == 'FLC_ID'){}
					else if($lookupRsKeys[$x] == 'FLC_NAME')
						$keyName = 'NAME';
					else
						$keyName = $lookupRsKeys[$x];
					?>
					<th class="listingHead"><?php echo $keyName; ?></th><?php
				}
				echo '</tr>';
				
				//loop on count of record
				for($x=0; $x < $lookupRsCount; $x++) 
				{ 	
					if($x%2 == 1)
						$addStyle = 'style="background-color:#FAFAFA;"';
					else
						$addStyle = 'style="background-color:#ffffff;"';
					
					if($lookupRsKeysCount == '2')
					{
						echo '<tr><td class="listingContent" '.$addStyle.'><a href="javascript:void(0)" onclick="flc_auto_suggest_setvalue('.$lookupRs[$x]['FLC_ID'].',\''.$lookupRs[$x]['FLC_NAME'].'\','.$_GET['itemid'].',\''.$_GET['name'].'\')">'.$lookupRs[$x]['FLC_NAME'].'</a></td>';
					}			
					else
					{
						echo '<tr><td class="listingContent" '.$addStyle.'><a href="javascript:void(0)" onclick="flc_auto_suggest_setvalue('.$lookupRs[$x]['FLC_ID'].',\''.$lookupRs[$x]['FLC_NAME'].'\','.$_GET['itemid'].',\''.$_GET['name'].'\')">'.$lookupRs[$x]['FLC_NAME'].'</a></td>';
						
						for($y=2; $y < $lookupRsKeysCount; $y++ )
						{		
							$keyName = $lookupRsKeys[$y];
							echo '<td class="listingContent" '.$addStyle.'>'.$lookupRs[$x][$keyName].'</td>';
						}
					}
					echo '</tr>';
				}				
				echo '</table>';
			}//eof if	
			else
				echo '<div style="margin:5px;">No record(s) found [<a href="javascript:void(0)" onclick="jQuery(this).closest(\'.auto_sugg_list\').fadeOut();"> Close </a>]</div>';		
		}//eof isset
	}//eof strlen
}//end if else
?>
