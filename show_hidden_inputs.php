<?php 
for($a=0; $a<$countItemHidden; $a++) 
{
	//ni TEMPORARY UNTUK SHOW HIDDEN ITEMS FIRST

	//if item default value is not set, check if bind to database columns and pre process is SELECT
	if($itemsArrHidden[$a]['ITEMDEFAULTVALUE'] == '' && $componentArr[$x]['COMPONENTPREPROCESS'] == 'select')
	{
		//for all items
		for($g=0; $g<$countGetMappedItem; $g++)
		{
			//if input name is in getMappedItem array, get mapping id
			if($getMappedItem[$g]['COMPONENTIDNAME'] == $itemsArrHidden[$a]['ITEMNAME'])
			{
				//for column to find, find value in getDataRs
				$columnValueToFind = strtoupper($getMappedItem[$g]['MAPPINGID']);
				
				//set the value to default variable
				$itemsArrHidden[$a]['ITEMDEFAULTVALUE'] = $getDataRs[0][$columnValueToFind];
			}//end if
		}//end for g
	}//end if
	
	//build the item
	echo buildInput($myQueryArr, $itemsArrHidden[$a], $a+1);
}
?>