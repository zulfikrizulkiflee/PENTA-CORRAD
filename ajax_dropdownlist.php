<?php
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');	

//if have element
if($_GET['ElementValue']&&$_GET['TargetElement'])
{
	//set xml header
	header('content-type: text/xml');
	
	$ElementValue = $_GET['ElementValue'];
	$CurrentElement = "{POST|".$_GET['CurrentElement']."}";
	
	//get item query
	$sql0 = "select a.*
			from FLC_PAGE_COMPONENT_ITEMS a, FLC_PAGE_COMPONENT b, FLC_PAGE c
			where a.COMPONENTID = b.COMPONENTID and b.PAGEID = c.PAGEID 
				and c.MENUID = ".$_SESSION['menuID']." and a.ITEMNAME = '".$_GET['TargetElement']."'";
	$sqlrs = $myQuery->query($sql0,'SELECT','NAME');
	
	//if dropdown
	if($sqlrs[0]['ITEMTYPE'] == 'dropdown')
	{
		//run the itemlookup query
		$run = str_replace($CurrentElement, $ElementValue, $sqlrs[0]['ITEMLOOKUP']);  
		$runRs = $myQuery->query($run,'SELECT','NAME');
		$runRsCount = count($runRs);
	}//eof if
	?>
	<DATA>
	<?php for($x=0; $x<$runRsCount; $x++ ){?>
	  <DROPDOWNLIST id="<?php echo $x;?>">
		<FLC_ID><?php echo $runRs[$x]['FLC_ID'];?></FLC_ID>
		<FLC_NAME><?php echo str_replace("&", "&amp;", $runRs[$x]['FLC_NAME']);?></FLC_NAME>
	  </DROPDOWNLIST>
	<?php }?>
	</DATA>	
<?php }?>
