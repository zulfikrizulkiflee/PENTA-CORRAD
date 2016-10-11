<?php
/*
sample lookup query :

select 'Parent 1' as flc_name, 'google.com' as flc_url, '1' as level
union all
select 'Child 1' as flc_name, 'google1.com' as flc_url, '2' as level
union all
select 'Child 2' as flc_name, 'google2.com' as flc_url, '3' as level
union all
select 'Parent 2' as flc_name, 'google3.com' as flc_url, '1' as level
union all
select 'Child 1' as flc_name, 'google4.com' as flc_url, '2' as level
union all
select 'Parent 3' as flc_name, 'google5.com' as flc_url, '1' as level          
*/

$name			= $itemArr['ITEMNAME'];
$lookup			= convertDBSafeToQuery($itemArr['ITEMLOOKUP']);
$length			= $itemArr['ITEMINPUTLENGTH'];
$textarearows	= $itemArr['ITEMTEXTAREAROWS'];
$addAttrib		= explode('|',$itemArr['ITEMADDITIONALATTR']);

$lookupRs = $myQuery->query($lookup,'SELECT','NAME');
$lookupRsIdx = $myQuery->query($lookup,'SELECT','INDEX');
$lookupRsCount = count($lookupRs);

if($lookupRsCount)
{
	//loop on count of record
	for($x=0; $x<$lookupRsCount; $x++) 
	{ 	
		$keyName = $lookupRsKeys[$y];
		$tempValue .= '<option value="'.$lookupRs[$x]['FLC_ID'].'"';
		
		//to select value
		if($lookupRs[$x]['FLC_ID'] == $default)
			$tempValue .= ' selected ';
		
		$tempValue .= ' >'.$lookupRs[$x]['FLC_NAME'].'</option>';
	}	
}
?>
<div class="flcTreeMenu">
	<ul id="<?php echo $name; ?>">
		<?php
		$currentLevel = 0;
		
		for($x=0; $x < count($lookupRs); $x++)
		{ 				
			echo '<li>';
			echo '<div class="flcTreeMenuLinkDiv"><a href="'.$lookupRs[$x]['FLC_URL'].'">'.$lookupRs[$x]['FLC_NAME'].'</a></div>';
		
			$currentLevel = $lookupRs[$x]['LEVEL'];
		
			//if next item same level, echo </li>, else 
			if($lookupRs[$x+1]['LEVEL'] == $lookupRs[$x]['LEVEL'])		
				echo '</li>';
			else if($lookupRs[$x+1]['LEVEL'] > $lookupRs[$x]['LEVEL'])
				echo '<ul>';
			else if($lookupRs[$x+1]['LEVEL'] < $lookupRs[$x]['LEVEL'])
			{	
				$diff = $lookupRs[$x]['LEVEL'] - $lookupRs[$x+1]['LEVEL'];
				
				for($y=0; $y < $diff; $y++)
					echo '</ul>';
			} 
		 } ?>
	</ul>
</div>
<script type="text/javascript">make_tree_menu('<?php echo $name; ?>');</script>
