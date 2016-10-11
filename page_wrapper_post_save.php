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

		//get component items info
		$getItemInfo = "select * from FLC_PAGE_COMPONENT_ITEMS 
							where COMPONENTID = ".$getPostComponentRs[$i]['COMPONENTID']." and ITEMPRIMARYCOLUMN is null
							order by ITEMORDER";
		$getItemInfoRs = $myQuery->query($getItemInfo,'SELECT','NAME');
		$getItemInfoRsCount = count($getItemInfoRs);
		
		//loop on count of item
		for($j=0; $j<$getItemInfoRsCount; $j++)
		{
			//uppercase
			if($getItemInfoRs[$j]['ITEMUPPERCASE'])
			{
				//check array
				if(is_array($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
				{
					//count item array
					$itemArrayCount = count($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
					
					//loop on count of item array
					for($k=0; $k<$itemArrayCount; $k++)
						$_POST[$getItemInfoRs[$j]['ITEMNAME']][$k] = strtoupper($_POST[$getItemInfoRs[$j]['ITEMNAME']][$k]);
				}//eof if
				else
					$_POST[$getItemInfoRs[$j]['ITEMNAME']] = strtoupper($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
			}//eof if
			
			//---------- INSERT / UPDATE ----------
			if($getComponentInfoRs[0]['COMPONENTPOSTPROCESS'] == 'insert' || $getComponentInfoRs[0]['COMPONENTPOSTPROCESS'] == 'update')
			{
				//if have mapping id
				if($getItemInfoRs[$j]['MAPPINGID'])
				{
					//mapping column
					$mapColumnName[] = $getItemInfoRs[$j]['MAPPINGID'];
					$mapIndex = count($mapColumnName)-1;
					
					//if item post-ed
					if(isset($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
					{
						//switch item type
						switch($getItemInfoRs[$j]['ITEMTYPE'])
						{
							case 'date':
								//array value
								if(is_array($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
								{
									$itemArrayCount = count($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
									
									//loop on count of item array
									for($k=0; $k<$itemArrayCount; $k++)
									{
										//if have value
										if($_POST[$getItemInfoRs[$j]['ITEMNAME']][$k] != '')
											$mapColumnValue[$mapIndex][$k] = $mySQL->convertToDate($_POST[$getItemInfoRs[$j]['ITEMNAME']][$k]);
										else
											$mapColumnValue[$mapIndex][$k] = "NULL";
									}//eof for
								}//eof if
								else
								{
									//if have value
									if($_POST[$getItemInfoRs[$j]['ITEMNAME']] != '')
										$mapColumnValue[$mapIndex] = $mySQL->convertToDate($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
									else
										$mapColumnValue[$mapIndex] = "NULL";
								}//eof else
							break;
							
							case 'listbox':
								$mapColumnValue[$mapIndex] = implode(',', $_POST[$getItemInfoRs[$j]['ITEMNAME']]);
							break;
							
							case 'password_md5':
								//array value
								if(is_array($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
								{
									$itemArrayCount = count($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
									
									//loop on count of item array
									for($k=0; $k<$itemArrayCount; $k++)
										$mapColumnValue[$mapIndex][$k] = "'".md5($_POST[$getItemInfoRs[$j]['ITEMNAME']][$k])."'";
								}//eof if
								else
								{
									$mapColumnValue[$mapIndex] = "'".md5($_POST[$getItemInfoRs[$j]['ITEMNAME']])."'";
								}//eof else
							break;
							
							default:
								//check datatype
								$getItemType = $mySQL->columnDatatype('','',$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE'],$getItemInfoRs[$j]['MAPPINGID']);
								$itemType = $getItemType[0]['DATA_TYPE'];
								
								//check datatype decimal, float, int
								if($itemType == 'DECIMAL' || $itemType == 'FLOAT' || $itemType == 'INT' || $itemType == 'INTN' || $itemType == 'NUMERIC')
								{
									//array value
									if(is_array($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
									{
										$itemArrayCount = count($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
										
										//loop on count of item array
										for($k=0; $k<$itemArrayCount; $k++)
										{
											//if have value
											if($_POST[$getItemInfoRs[$j]['ITEMNAME']][$k] != '')
												$mapColumnValue[$mapIndex][$k] = $_POST[$getItemInfoRs[$j]['ITEMNAME']][$k];
											else
												$mapColumnValue[$mapIndex][$k] = "NULL";
										}//eof for
									}//eof if
									else
									{
										//if have value
										if($_POST[$getItemInfoRs[$j]['ITEMNAME']] != '')
										{
											$mapColumnValue[$mapIndex] = $_POST[$getItemInfoRs[$j]['ITEMNAME']];
										}//eof if
										else
											$mapColumnValue[$mapIndex] = "NULL";
									}//eof else
								}//eof if
								else
								{
									//array value
									if(is_array($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
									{
										$itemArrayCount = count($_POST[$getItemInfoRs[$j]['ITEMNAME']]);
										
										//loop on count of item array
										for($k=0; $k<$itemArrayCount; $k++)
											$mapColumnValue[$mapIndex][$k] = "'".$_POST[$getItemInfoRs[$j]['ITEMNAME']][$k]."'";
									}//eof if
									else
									{
										$mapColumnValue[$mapIndex] = "'".$_POST[$getItemInfoRs[$j]['ITEMNAME']]."'";
									}//eof else
								}//eof else
							break;
						}//eof switch
					}//eof if
					//else if item post as file
					else if(is_uploaded_file($_FILES[$getItemInfoRs[$j]['ITEMNAME']]['tmp_name']))
					{
						//get upload parameter from component item
						$getUploadParam = "select ITEMUPLOAD from FLC_PAGE_COMPONENT_ITEMS where ITEMID = ".$getItemInfoRs[$j]['ITEMID'];
						$getUploadParamRs = $myQuery->query($getUploadParam,'SELECT','NAME');

						//explode the data in ITEMUPLOAD FIELD
						$uploadParam = explode("|",$getUploadParamRs[0]['ITEMUPLOAD']);
						
						//upload extension allowed
						$tempUploadExt = explode(';',$uploadParam[0]);
						$tempUploadExtCount = count($tempUploadExt);
						
						//loop on count of extension
						for($i=0; $i<$tempUploadExtCount; $i++)
							if($tempUploadExt[$i])
								$uploadExt[] = trim($tempUploadExt[$i]);
						
						//upload parameter
						$uploadDir = $uploadParam[1];
						$uploadMaxSize = $uploadParam[2];
						
						//check upload dir does not exist
						if(!is_dir($uploadDir))
							mkdir($uploadDir,0700);		//create upload directory
						
						//upload the file
						$theUploadedFile = upload_file($uploadDir.'/',$_FILES[$getItemInfoRs[$j]['ITEMNAME']],$uploadExt,$uploadMaxSize);
						
						//array value
						if(is_array($_POST[$getItemInfoRs[$j]['ITEMNAME']]))
						{
							$itemArrayCount = count($theUploadedFile);
							
							//loop on count of item array
							for($k=0; $k<$itemArrayCount; $k++)
								$mapColumnValue[$mapIndex][$k] = "'".$theUploadedFile[$k]."'";
						}//eof if
						else
						{
							//value to save
							$mapColumnValue[$mapIndex] = "'".$theUploadedFile."'";
						}//eof else
					}//end if
					else
						$mapColumnValue[$mapIndex] = NULL;
				}//eof if
			}//eof if
			//-------- EOF INSERT / UPDATE --------
		}//eof for
		
		//insert statement
		if($getComponentInfoRs[0]['COMPONENTPOSTPROCESS'] == 'insert')
		{
			//component type is form_1_col / form_2_col
			if($getComponentInfoRs[0]['COMPONENTTYPE'] == 'form_1_col' || $getComponentInfoRs[0]['COMPONENTTYPE'] == 'form_2_col')
			{
				//create insert statement
				$insertStmt = "insert into ".$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE']." (".implode(',',$mapColumnName).") 
								values (".implode(',',$mapColumnValue).")";
				$insertStmtRs = $myQuery->query($insertStmt,'RUN');
				
				//if insert statement is ok, add +1 to tabular ok count
				if($insertStmtRs)
					$insertRsCount++;
			}//eof if
			//component type is tabular / report
			else if($getComponentInfoRs[0]['COMPONENTTYPE'] == 'tabular' || $getComponentInfoRs[0]['COMPONENTTYPE'] == 'report')
			{
				//count of item
				$itemArrayCount = count($mapColumnValue);
				
				//loop on count of item
				for($j=0; $j<$itemArrayCount; $j++)
				{
					//count of value for each item
					$mapColumnValueTemp = $mapColumnValue[$j];
					$mapColumnValueTempCount = count($mapColumnValueTemp);
					
					//loop on count of value for each item
					for($k=0; $k<$mapColumnValueTempCount; $k++)
					{
						//convert array dimension
						$mapColumnValueArray[$k][$j] = $mapColumnValue[$j][$k];
					}//eof for
				}//eof for
				
				//count of value array
				$mapColumnValueArrayCount = count($mapColumnValueArray);
				
				//loop on count of value array
				for($j=0; $j<$mapColumnValueArrayCount; $j++)
				{
					//generate insert statement
					$insertStmt = "insert into ".$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE']." (".implode(',',$mapColumnName).") 
									values (".implode(',',$mapColumnValueArray[$j]).")";
					$insertStmtRs = $myQuery->query($insertStmt,'RUN');
				}//eof for
				
				//if insert statement is ok, add +1 to tabular ok count
				if($insertStmtRs)
					$insertRsCount++;
			}//eof elseif
		}//eof if
		//update statement
		else if($getComponentInfoRs[0]['COMPONENTPOSTPROCESS'] == 'update')
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
				//===== VALUE OF SET ClAUSE =====
				//column count
				$mapColumnNameCount = count($mapColumnName);
				
				//loop on count of column
				for($j=0; $j<$mapColumnNameCount; $j++)
				{
					//set column value
					$mapColumnNameValue[$j] = $mapColumnName[$j].'='.$mapColumnValue[$j];
				}//eof for
				//=== EOF VALUE OF SET ClAUSE ===
				
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
				
				//generate update statement
				$updateStmt = "update ".$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE']. " 
								set ".implode(', ',$mapColumnNameValue)."  
								where ".implode(' and ',$mapKeyColumnNameValue);
				$updateStmtRs = $myQuery->query($updateStmt,'RUN');
				
				//if statement updated
				if($updateStmtRs)
					$updateRsCount++;
			}//eof if
			//component type is tabular / report
			else if($getComponentInfoRs[0]['COMPONENTTYPE'] == 'tabular' || $getComponentInfoRs[0]['COMPONENTTYPE'] == 'report')
			{
				//===== EOF VALUE OF SET ClAUSE =====
				//column count
				$mapColumnNameCount = count($mapColumnName);
				
				//loop on count of column
				for($j=0; $j<$mapColumnNameCount; $j++)
				{
					//count column value
					$mapColumnValueArrayCount = count($mapColumnValue[$j]);
					
					//loop on count of column value
					for($k=0; $k<$mapColumnValueArrayCount; $k++)
					{
						//set column value
						$mapColumnNameValueArray[$k][$j] = $mapColumnName[$j].'='.$mapColumnValue[$j][$k];
					}//eof for
				}//eof for
				//=== EOF VALUE OF SET ClAUSE ===
				
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
					//generate update statement
					$updateStmt = "update ".$getComponentInfoRs[0]['COMPONENTBINDINGSOURCE']. " 
									set ".implode(', ',$mapColumnNameValueArray[$j])."  
									where ".implode(' and ',$mapKeyColumnNameValueArray[$j]);
					$updateStmtRs = $myQuery->query($updateStmt,'RUN');
					
					//if statement updated
					if($updateStmtRs)
						$updateRsCount++;
				}//eof for
			}//eof elseif
		}//eof if
	}//eof for
	
	//==================== NOTIFICATION ====================
	//if insert
	if($insertRsCount)
	{	
		//rows not inserted
		$tabularNotInsertCount = $mapColumnNameCount - $insertRsCount;
		
		//if have rows can't be inserted
		if($tabularNotInsertCount>0)
		{
			//check pre-set (if any)
			if($getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'])
				$message = $getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'];
			else
				$message = DB_INSERT_SUCCESS;
			
			//notification
			showNotificationError($message.' ('.$tabularNotInsertCount.' rows NOT inserted.)');
		}//eof if
		else
		{
			//check pre-set (if any)
			if($getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'])
				$message = $getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'];
			else
				$message = DB_INSERT_SUCCESS;
			
			//notification
			showNotificationInfo($message);
		}//eof else
	}//eof if
	
	//if update tabular
	if($updateRsCount)
	{
		//rows not updated
		$tabularNotUpdateCount = $mapColumnNameCount - $updateRsCount;
		
		//if have rows can't be updated
		if($tabularNotInsertCount>0)
		{
			//check pre-set (if any)
			if($getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'])
				$message = $getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'];
			else
				$message = DB_UPDATE_SUCCESS;
			
			//notification
			showNotificationError($message.' ('.$tabularNotUpdateCount.' rows NOT updated.)');
		}//eof if
		else
		{
			//check pre-set (if any)
			if($getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'])
				$message = $getComponentInfoRs[0]['COMPONENTPOSTSCRIPT'];
			else
				$message = DB_UPDATE_SUCCESS;
			
			//notification
			showNotificationInfo($message);
		}//eof else
	}//eof if
	//================== EOF NOTIFICATION ==================
}//eof if have component
?>