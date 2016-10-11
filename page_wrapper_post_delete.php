<?php
//if have component
if($getPostComponentRsCount)
{
	//loop on count of component
	for($i=0; $i<$getPostComponentRsCount; $i++)
	{
		//get component info
		$getComponentInfo = "select * from FLC_PAGE_COMPONENT where COMPONENTID = ".$getPostComponentRs[$i]['COMPONENTID'];
		$getComponentInfoRs = $myQuery->query($getComponentInfo,'SELECT','NAME');

		//delete statement
		if($getComponentInfoRs[0]['COMPONENTPOSTPROCESS'] == 'update')
		{
			//get items PK info
			$getItemKeyInfo = "select * from FLC_PAGE_COMPONENT_ITEMS 
								where COMPONENTID = ".$getPostComponentRs[$i]['COMPONENTID']." 
									and ITEMPRIMARYCOLUMN is not null and ITEMSTATUS = 1";
			$getItemKeyInfoRs = $myQuery->query($getItemKeyInfo,'SELECT','NAME');
			$getItemKeyInfoRsCount = count($getItemKeyInfoRs);
			
			//loop on count of item PK
			for($j=0; $j<$getItemKeyInfoRsCount; $j++)
			{				
				//if have mapping id
				if($getItemKeyInfoRs[$j]['MAPPINGID'])
				{
					//mapping column
					$mapKeyColumnName[] = $getItemKeyInfoRs[$j]['MAPPINGID'];
					$mapKeyIndex = count($mapKeyColumnName)-1;
					
					//datatype
					$itemKeyDataType = $mySQL->columnDatatype(NULL,NULL,$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE'],$getItemKeyInfoRs[$j]['MAPPINGID']);
					$itemKeyType = $itemKeyDataType[0]['DATA_TYPE'];
					
					//check datatype decimal, float, int
					if($itemKeyType == 'DECIMAL' || $itemKeyType == 'FLOAT' || $itemKeyType == 'INT' || $itemKeyType == 'INTN' || $itemKeyType == 'NUMERIC')
					{
						//array value
						if(is_array($_POST[$getItemKeyInfoRs[$j]['ITEMNAME']]))
						{
							$itemKeyArrayCount = count($_POST[$getItemKeyInfoRs[$j]['ITEMNAME']]);
							
							//loop on count of item PK array
							for($k=0; $k<$itemKeyArrayCount; $k++)
							{
								//if have value
								if($_POST[$getItemKeyInfoRs[$j]['ITEMNAME']][$k] != '')
									$mapKeyColumnValue[$mapKeyIndex][$k] = $_POST[$getItemKeyInfoRs[$j]['ITEMNAME']][$k];
								else
									$mapKeyColumnValue[$mapKeyIndex][$k] = "NULL";
							}//eof for
						}//eof if
						else
						{
							//if have value
							if($_POST[$getItemKeyInfoRs[$j]['ITEMNAME']] != '')
								$mapKeyColumnValue[$mapKeyIndex] = $_POST[$getItemKeyInfoRs[$j]['ITEMNAME']];
							else
								$mapKeyColumnValue[$mapKeyIndex] = "NULL";
						}//eof else
					}//eof if
					else
					{
						//array value
						if(is_array($_POST[$getItemKeyInfoRs[$j]['ITEMNAME']]))
						{
							$itemKeyArrayCount = count($_POST[$getItemKeyInfoRs[$j]['ITEMNAME']]);
							
							//loop on count of item array
							for($k=0; $k<$itemKeyArrayCount; $k++)
								$mapKeyColumnValue[$mapKeyIndex][$k] = "'".$_POST[$getItemKeyInfoRs[$j]['ITEMNAME']][$k]."'";
						}//eof if
						else
						{
							$mapKeyColumnValue[$mapKeyIndex] = "'".$_POST[$getItemKeyInfoRs[$j]['ITEMNAME']]."'";
						}//eof else
					}//eof else
				}//eof if
			}//eof for
			
			//component type is form_1_col / form_2_col
			if($getComponentInfoRs[0]['COMPONENTTYPE'] == 'form_1_col' || $getComponentInfoRs[0]['COMPONENTTYPE'] == 'form_2_col')
			{
				//===== VALUE OF WHERE ClAUSE =====
				//column PK count
				$mapKeyColumnNameCount = count($mapKeyColumnName);
				
				//loop on count of PK column
				for($j=0; $j<$mapKeyColumnNameCount; $j++)
				{
					//set PK column value
					$mapKeyColumnNameValue[$j] = $mapKeyColumnName[$j].'='.$mapKeyColumnValue[$j];
				}//eof for
				//=== EOF VALUE OF WHERE ClAUSE ===
				
				//generate delete statement
				$deleteStmt = "delete from ".$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE']." where ".implode(' and ',$mapKeyColumnNameValue);
				$deleteStmtRs = $myQuery->query($deleteStmt,'RUN');
				
				//if statement deleted
				if($deleteStmtRs)
					$deleteRsCount++;
			}//eof if
			//component type is tabular / report
			else if($getComponentInfoRs[0]['COMPONENTTYPE'] == 'tabular' || $getComponentInfoRs[0]['COMPONENTTYPE'] == 'report')
			{
				//===== VALUE OF WHERE ClAUSE =====
				//column PK count
				$mapKeyColumnNameCount = count($mapKeyColumnName);
				
				//loop on count of PK column
				for($j=0; $j<$mapKeyColumnNameCount; $j++)
				{
					//count PK column value
					$mapKeyColumnValueArrayCount = count($mapKeyColumnValue[$j]);
					
					//loop on count of PK column value
					for($k=0; $k<$mapKeyColumnValueArrayCount; $k++)
					{
						//set PK column value
						$mapKeyColumnNameValueArray[$k][$j] = $mapKeyColumnName[$j].'='.$mapKeyColumnValue[$j][$k];
					}//eof for
				}//eof for
				//=== EOF VALUE OF WHERE ClAUSE ===
				
				//count of value array
				$mapColumnNameValueArrayCount = count($mapColumnNameValueArray);
				
				//loop on count of value array
				for($j=0; $j<$mapColumnNameValueArrayCount; $j++)
				{
					//generate delete statement
					$deleteStmt = "delete from ".$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE']." 
									where ".implode(' and ',$mapKeyColumnNameValueArray[$j]);
					$deleteStmtRs = $myQuery->query($deleteStmt,'RUN');
					
					//if statement updated
					if($deleteStmtRs)
						$deleteRsCount++;
				}//eof for
			}//eof elseif
		}//eof if
	}//eof for
	
	//==================== NOTIFICATION ====================
	//if delete tabular
	if($deleteRsCount)
	{
		//rows not deleted
		$tabularNotDeleteCount = $mapColumnNameCount - $deleteRsCount;
		
		//if have rows can't be deleted
		if($tabularNotInsertCount>0)
		{
			//check pre-set (if any)
			if($getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'])
				$message = $getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'];
			else
				$message = DB_DELETE_SUCCESS;
			
			//notification
			showNotificationError($message.' ('.$tabularNotDeleteCount.' rows NOT updated.)');
		}//eof if
		else
		{
			//check pre-set (if any)
			if($getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'])
				$message = $getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'];
			else
				$message = DB_DELETE_SUCCESS;
			
			//notification
			showNotificationInfo($message);
		}//eof else
	}//eof if
	//================== EOF NOTIFICATION ==================
}//eof if have component
?>