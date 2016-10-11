<?php if($componentArr[$x]['COMPONENTID']){
	$scrollHeight = ($componentArr[$x]['COMPONENTABULARDEFAULTROWNO']*14)+17;
?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" <?php echo $js[$x];?> <?php if(!isset($_GET['type']))
	{ ?>style="height:<?php echo $scrollHeight; ?>px; overflow:auto;position:absolute;" <?php } ?>>
<?php
	if($_GET['type'] == 'freeform_editor')
		$componentArr[$x]['COMPONENTABULARDEFAULTROWNO'] = 1;

	$itemForScrollbar = array();

	//if live
	if(!isset($_GET['type']))
	{
		$firstItemTop = array();
		$firstItemLeft = 0;
		$lastItemRightEdge = 0;
		$calendarCount = 0;
	}
	
	//initialize arrays
	$itemXpos = array();
	$itemYpos = array();
	$itemWidth = array();
	$itemHeight = array();
	$itemBackColor = array();
	
	//collection arrays
	$itemXposColl = array();
	$itemYposColl = array();
	$itemWidthColl = array();
	$itemHeightColl = array();
	$itemBackColorColl = array();
	$itemFontColorColl = array();
	$itemZIndexColl = array();
	$itemWindowColl = array();
	
	$tempStrColl = array();
	$tempStr = '';
	
	//$componentArr[$x]['COMPONENTABULARDEFAULTROWNO'] = 10;
	
	for($b=0; $b < $componentArr[$x]['COMPONENTABULARDEFAULTROWNO']; $b++)
	{	
		for($a=0; $a < $countItem; $a++) 
		{
			// ------ temporarily used to display date picker (only unique html name/id will show)
			if($itemsArr[$a]["MAPPINGID"] == "")
				$itemsArr[$a]["MAPPINGID"] = rand();
				
			//temporary sb gile
			unset($default);

			//if item default value is not set, check if bind to database columns and pre process is SELECT
			if($itemsArr[$a]["ITEMDEFAULTVALUE"] == '' && $componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
			{
				//for all items
				for($g=0; $g < $countGetMappedItem; $g++)
				{
					//if input name is in getMappedItem array, get mapping id
					if($getMappedItem[$g]['COMPONENTIDNAME'] == $itemsArr[$a]['ITEMNAME'])
					{
						//for column to find, find value in getDataRs
						$columnValueToFind = $getMappedItem[$g]['MAPPINGID'];
						
						//set the value to default variable
						$default = $getDataRs[0][$columnValueToFind];
					}//end if
				}//end for g
			}//end if
			
			//else, use the default value
			else
				$default = $itemsArr[$a]["ITEMDEFAULTVALUE"];

			if($b==0 )
			{
				if($itemsArr[$a]['ITEMTYPE'] != 'hidden')
					$itemForScrollbar[] = $itemsArr[$a]['ITEMNAME'];
			}
			
			//get the canvas for the item
			$getItemCanvas = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
												where ATTR_ID = 13 
												and ATTR_PARENT_ID = ".$itemsArr[$a]["ITEMID"],'SELECT','NAME');		
			
			//first row and first col			
			if($a == 0 && $b == 0)
			{	
				//get canvas x and y pos AND TYPE
				$getItemCanvasXPos = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
														where ATTR_ID = 42 
														and ATTR_PARENT_ID = ".$pageArr[0]['PAGEID']."
														and ATTR_VALUE like '".$getItemCanvas[0]['ATTR_VALUE']."|%'",'SELECT','NAME');
				
				$getItemCanvasYPos = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
														where ATTR_ID = 43 
														and ATTR_PARENT_ID = ".$pageArr[0]['PAGEID']."
														and ATTR_VALUE like '".$getItemCanvas[0]['ATTR_VALUE']."|%'",'SELECT','NAME');
					
				$getItemCanvasType = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
														where ATTR_ID = 47
														and ATTR_PARENT_ID = ".$pageArr[0]['PAGEID']."
														and ATTR_VALUE like '".$getItemCanvas[0]['ATTR_VALUE']."|%'",'SELECT','NAME');
				
				$getItemCanvasXPosArr = explode('|',$getItemCanvasXPos[0]['ATTR_VALUE']);
				$getItemCanvasYPosArr = explode('|',$getItemCanvasYPos[0]['ATTR_VALUE']);
				$getItemCanvasTypeArr = explode('|',$getItemCanvasType[0]['ATTR_VALUE']);
				
				$getItemCanvasXPos = $getItemCanvasXPosArr[1];
				$getItemCanvasYPos = $getItemCanvasYPosArr[1];
				$getItemCanvasType = $getItemCanvasTypeArr[1];
				
				if($getItemCanvasXPos == 'null')
					$getItemCanvasXPos = 0;
				if($getItemCanvasYPos == 'null')
					$getItemCanvasYPos = 0;
				
				if($getItemCanvasType != 'Stacked')
				{
					$getItemCanvasXPos = 0;
					$getItemCanvasYPos = 0;
				}
			}
			
			//first row loop
			if($b==0)
			{
				//get xpos
				$itemXpos = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
												where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
												and ATTR_ID = 9",'SELECT','NAME');
				
				//get ypos
				$itemYpos = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
												where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
												and ATTR_ID = 10",'SELECT','NAME');
															
				//get width
				$itemWidth = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
												where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
												and ATTR_ID = 11",'SELECT','NAME');
																
				//get height
				$itemHeight = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
												where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
												and ATTR_ID = 12",'SELECT','NAME');
				
				//get back color
				$itemBackColor = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
												where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
												and ATTR_ID = 34",'SELECT','NAME');
							
				//get foreground color (font color)
				$itemFontColor = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
											where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
											and ATTR_ID = 33",'SELECT','NAME');
				
				//get zindex
				$itemZIndex = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
											where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
											and ATTR_ID = 78",'SELECT','NAME');
										
				//get window
				$itemWindow = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
											where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
											and ATTR_ID = 14",'SELECT','NAME');			
												
				$itemXposColl[] = $itemXpos;
				$itemYposColl[] = $itemYpos;
				$itemWidthColl[] = $itemWidth;
				$itemHeightColl[] = $itemHeight;
				$itemBackColorColl[] = $itemBackColor;
				$itemFontColorColl[] = $itemFontColor;
				$itemZIndexColl[] = $itemZIndex;
				$itemWindowColl[] = $itemWindow;
				
				//echo 'huhu';
			}
			else
			{
				$itemXpos = $itemXposColl[$a];
				$itemYpos = $itemYposColl[$a];
				$itemWidth = $itemWidthColl[$a];
				$itemHeight = $itemHeightColl[$a];
				$itemBackColor = $itemBackColorColl[$a];
				$itemFontColor = $itemFontColorColl[$a];
				$itemZIndex = $itemZIndexColl[$a];
				$itemWindow = $itemWindowColl[$a];
			}
			
			//if live
			if(!isset($_GET['type']) && $itemsArr[$a]['ITEMTYPE'] != 'hidden' && strpos($itemsArr[$a]['ITEMNAME'],'FLC_ROWID') === FALSE && strpos($itemsArr[$a]['ITEMNAME'],'FLC_ROWSTATUS') === FALSE)
			//if(!isset($_GET['type']) && $itemsArr[$a]['ITEMTYPE'] != 'hidden')
			{
				//for scrollbars
				if($b == 0)
				{	
					$firstItemTop[$a] = $itemYpos[0]['ATTR_VALUE'];
					
					if($a == 0)
						$firstItemLeft = $itemXpos[0]['ATTR_VALUE'];
					
					if($a+1 == $countItem)
						$lastItemRightEdge = $itemXpos[0]['ATTR_VALUE']+$itemWidth[0]['ATTR_VALUE']+0+($calendarCount*100);			//110
				}
				
				$liveXpos = $itemXpos[0]['ATTR_VALUE'] - $firstItemLeft;
				$liveYpos = $itemYpos[0]['ATTR_VALUE'] + (($itemHeight[0]['ATTR_VALUE']+1)*$b) - $firstItemTop[$a];
			}
			else
			{
				$liveXpos = $itemXpos[0]['ATTR_VALUE'];
				$liveYpos = ($itemYpos[0]['ATTR_VALUE'] + (($itemHeight[0]['ATTR_VALUE']+1)*$b));
			}
			
			//alternating color
			if($b%2)
			{
				if($itemBackColor[0]['ATTR_VALUE'] == '' || $itemBackColor[0]['ATTR_VALUE'] == '#ffffff') 
					$itemBackColor[0]['ATTR_VALUE'] = '#F8F8F8';
			}
			
			//if first row
			//if($b==0)
		//	{
				//to build input
				$tempStr = convertInputIntoArray($itemsArr[$a]['ITEMTYPE'], buildInput($myQueryArr,$itemsArr[$a], $b,
								array(	'xpos'=>$liveXpos,
										'ypos'=>$liveYpos,
										'width'=>$itemWidth[0]['ATTR_VALUE'],
										'height'=>$itemHeight[0]['ATTR_VALUE'],
										'color'=>$itemFontColor[0]['ATTR_VALUE'],
										'backgroundColor'=>$itemBackColor[0]['ATTR_VALUE'],
										'zindex'=>$itemZIndex[0]['ATTR_VALUE'],
										'canvas'=>$getItemCanvas[0]['ATTR_VALUE'],
										'window'=>$itemWindow[0]['ATTR_VALUE'],
										'form_type'=>'tabular'
									)
								), $b);
				
				if(strlen($tempStr) > 0) 
				{	
					$tempStrColl[] = $tempStr.' '. $itemnoteurl;
					echo $tempStr.' '. $itemnoteurl;
				}
			//}
			/*
			else
			{//echo 'aaaaa';
				//echo '<pre>';
				//print_r($tempStrColl);
				//echo '<code style="margin-top:100px;">xxx'.htmlentities($tempStrColl[$a]).'</code>';
			//	echo "hehehe";
				//echo '<br>';
				//echo '</pre>';
				echo $tempStrColl[$a];
			}
			*/
			//if theres page control associated with the component
			if($controlArrCount > 0)
			{ 
				for($y=0;$y<$controlArrCount;$y++)
				{$controlid[] = $controlArr[$y][0];}
				 buildControl($myQuery,$controlid);
		   }//build control
		
			//else
			switch($itemsArr[$a]['ITEMTYPE'])
			{
				case 'text' :
					$labelStr = 'div_text_'; 		break;
				case 'lov' :
					$labelStr = 'div_lov_'; 		break;
				case 'date' :
					$labelStr = 'div_date_'; 		$calendarCount++; break;
				case 'checkbox' :
					$labelStr = 'div_checkbox_'; 	break;
				case 'radio' :
					$labelStr = 'div_radio_'; 		break;
				case 'textarea' :
					$labelStr = 'div_textarea_'; 	break;
				case 'dropdown' :
					$labelStr = 'div_dropdown_'; 	break;
			}
			
			if($getItemCanvas[0]['ATTR_VALUE'] == '' || strtolower($getItemCanvas[0]['ATTR_VALUE']) == 'null')
			{	
				if(strpos($itemsArr[$a]['ITEMNAME'],'FLC_ROWID') || strpos($itemsArr[$a]['ITEMNAME'],'FLC_ROWSTATUS')) {}
				else
				{	
					$getItemCanvas[0]['ATTR_VALUE'] = 'OrphanCanvas';
					$itemCanvasArr[$getItemCanvas[0]['ATTR_VALUE']][] = $labelStr.$componentArr[$x]['COMPONENTID'].'_'.$itemsArr[$a]["ITEMID"];
				}
			}
			else
				$itemCanvasArr[$getItemCanvas[0]['ATTR_VALUE']][] = $labelStr.$componentArr[$x]['COMPONENTID'].'_'.$itemsArr[$a]["ITEMID"];
		}//for 
	}//end for b
	
	if($firstItemTop[0]== '')
		$firstItemTop[0] = 0;
	if($firstItemLeft == '')
		$firstItemLeft = 0;
	?>
<br />
</div>
<?php 
	//if live
	if(!isset($_GET['type']))
		$showTabularScrollbar[] = "flc_show_tabular_scrollbar('".$componentArr[$x]['COMPONENTNAME']."',".$firstItemTop[0].",".$lastItemRightEdge.",".$firstItemLeft.",".$getItemCanvasXPos.",".$getItemCanvasYPos.",'".implode(',',$itemForScrollbar)."');";	
} 
?>
