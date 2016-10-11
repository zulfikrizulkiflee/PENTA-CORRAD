<?php if($componentArr[$x]['COMPONENTID']){?>
<div id="<?php echo $componentArr[$x]['COMPONENTNAME'];?>" <?php echo $js[$x];?>>
<?php
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
		
		//get the canvas for the item
		$getItemCanvas = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
							where ATTR_ID = 13 
							and ATTR_PARENT_ID = ".$itemsArr[$a]["ITEMID"],'SELECT','NAME');

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
			
			//get foreground color (font color)
			$itemFontColor = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
										where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
										and ATTR_ID = 33",'SELECT','NAME');
															
			//get back color
			$itemBackColor = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
											where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
											and ATTR_ID = 34",'SELECT','NAME');
			
			//get zindex
			$itemZIndex = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
										where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
										and ATTR_ID = 78",'SELECT','NAME');
									
			//get window
			$itemWindow = $myQuery->query("select ATTR_VALUE from FLC_EXTENDED_ATTR_VAL 
										where ATTR_PARENT_ID = ".$itemsArr[$a]['ITEMID']."
										and ATTR_ID = 14",'SELECT','NAME');
		
		//to build input
		/*
		$tempStr = buildInput($myQuery,$myQueryArr,$itemsArr[$a], $a+1,
						array(	'xpos'=>$itemXpos[0]['ATTR_VALUE'],
								'ypos'=>$itemYpos[0]['ATTR_VALUE'],
								'width'=>$itemWidth[0]['ATTR_VALUE'],
								'height'=>$itemHeight[0]['ATTR_VALUE'],
								'color'=>$itemFontColor[0]['ATTR_VALUE'],
								'backgroundColor'=>$itemBackColor[0]['ATTR_VALUE'],
								'zindex'=>$itemZIndex[0]['ATTR_VALUE'],
								'canvas'=>$getItemCanvas[0]['ATTR_VALUE'],
								'window'=>$itemWindow[0]['ATTR_VALUE'],
								'form_type'=>'form'
							)
						);
		*/
		$tempStr = buildInput($myQueryArr,$itemsArr[$a], '',
						array(	'xpos'=>$itemXpos[0]['ATTR_VALUE'],
								'ypos'=>$itemYpos[0]['ATTR_VALUE'],
								'width'=>$itemWidth[0]['ATTR_VALUE'],
								'height'=>$itemHeight[0]['ATTR_VALUE'],
								'color'=>$itemFontColor[0]['ATTR_VALUE'],
								'backgroundColor'=>$itemBackColor[0]['ATTR_VALUE'],
								'zindex'=>$itemZIndex[0]['ATTR_VALUE'],
								'canvas'=>$getItemCanvas[0]['ATTR_VALUE'],
								'window'=>$itemWindow[0]['ATTR_VALUE'],
								'form_type'=>'form'
							)
						);
		
		if(strlen($tempStr) > 0) 
				echo $tempStr.' '. $itemnoteurl;
	
		
		
		//else
		switch($itemsArr[$a]['ITEMTYPE'])
		{
			case 'text' :
				$labelStr = 'div_text_'; 		break;
			case 'lov' :
				$labelStr = 'div_lov_'; 		break;
			case 'date' :
				$labelStr = 'div_date_'; 		break;
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
	?>
	  <?php
	//if theres page control associated with the component
	if($controlArrCount > 0)
	{ 
		for($y=0;$y<$controlArrCount;$y++)
		{$controlid[] = $controlArr[$y][0];}
 
    buildControl($myQuery,$controlid);
   }?>
<br />
</div>
<?php }?>
