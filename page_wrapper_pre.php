<?php
if($componentArr[$x]['COMPONENTID'])
{
	//unset previous list
	unset($listOfColumn);

	//get list of mapped items
	$getMappedItem = "select a.ITEMID, a.ITEMNAME, a.MAPPINGID, b.COMPONENTID
						from FLC_PAGE_COMPONENT_ITEMS a, FLC_PAGE_COMPONENT b
						where a.COMPONENTID = b.COMPONENTID and a.MAPPINGID is not null
							and b.COMPONENTID = ".$componentArr[$x]['COMPONENTID'];
	$getMappedItem = $myQuery->query($getMappedItem,'SELECT','NAME');
	$countGetMappedItem = count($getMappedItem);
	
	//loop on count of mapped items
	for($a=0; $a<$countGetMappedItem; $a++)
	{
		$listOfColumn[] = $getMappedItem[$a]['MAPPINGID'];
		$getMappedItem[$a]['COMPONENTIDNAME'] = $getMappedItem[$a]['ITEMNAME'];
	}//eof for
		
	//check datatype for column
	for($a=0; $a<count($listOfColumn); $a++)
	{
		//get datatype
		$chkColumnTypeRs = $mySQL->columnDatatype(DB_OTHERS,DB_OTHERS,$componentArr[$x]['COMPONENTBINDINGSOURCE'], $listOfColumn[$a]);
		
		//if type date, use date format
		if($chkColumnTypeRs[0]['DATA_TYPE']=='DATE' || $chkColumnTypeRs[0]['DATA_TYPE']=='DATETIMN')
			$listOfColumn[$a] = $mySQL->convertFromDate($listOfColumn[$a])." as ".$listOfColumn[$a];
	}//eof for
	
	//----- get data from database table -----
	//if type is FORM 1 COL / FORM 2 COL
	if($componentArr[$x]['COMPONENTTYPE'] == 'form_1_col' || $componentArr[$x]['COMPONENTTYPE'] == 'form_2_col')
	{
		//if binding type is VIEW
		if($componentArr[$x]['COMPONENTBINDINGTYPE'] == 'view')
		{
			//get PRIMARY KEY from component
			$getPrimaryKeyFromComponentItem = "select MAPPINGID from FLC_PAGE_COMPONENT_ITEMS 
												where ITEMPRIMARYCOLUMN = 1 
												and COMPONENTID = ".$componentArr[$x]['COMPONENTID'];
			$getPrimaryKeyFromComponentItemRs = $myQuery->query($getPrimaryKeyFromComponentItem,'SELECT','NAME');
			$getPrimaryKeyFromComponentItemRsCount = count($getPrimaryKeyFromComponentItemRs);
			
			//loop on number of primary key
			for($f=0; $f < $getPrimaryKeyFromComponentItemRsCount; $f++)
			{
				//if not 1st count
				if($f>0)
					$tempKey=$f+1;	
				
				//if keyid is set
				if(isset($_GET['keyid'.$tempKey]))
				{
					//datatype
					$itemDataType = $mySQL->columnDatatype(NULL,NULL,$componentArr[$x]['COMPONENTBINDINGSOURCE'],$getPrimaryKeyFromComponentItemRs[$f]['MAPPINGID']);
					$itemType = $itemDataType[0]['DATA_TYPE'];
					
					//check datatype decimal, float, int
					if($itemType == 'DECIMAL' || $itemType == 'FLOAT' || $itemType == 'INT' || $itemType == 'INTN' || $itemType == 'NUMERIC')
					{
						//list of primary key in array
						$listOfPrimaryKey[$f] = $getPrimaryKeyFromComponentItemRs[$f]['MAPPINGID']." = ".$_GET['keyid'.$tempKey];
					}//eof if
					else
					{
						//list of primary key in array
						$listOfPrimaryKey[$f] = $getPrimaryKeyFromComponentItemRs[$f]['MAPPINGID']." = '".$_GET['keyid'.$tempKey]."'";
					}//eof else
				}//eof if
			}//eof for				
			
			//construct the sql
			$getData = "select ".implode(', ',$listOfColumn)." 
						from ".$componentArr[$x]['COMPONENTBINDINGSOURCE']."
						where ".implode(' and ',$listOfPrimaryKey);
		}
		
		//else, use standard primary key
		else
		{	
			$getPrimaryKeyRs = $mySQL->listPrimaryKey($componentArr[$x]['COMPONENTBINDINGSOURCE']);
			$getPrimaryKeyRsCount = count($getPrimaryKeyRs); 

			//loop on number of primary key
			for($f=0; $f < $getPrimaryKeyRsCount; $f++)
			{
				//if not 1st count
				if($f>0)
					$tempKey=$f+1;	//to be appended on keyid
				
				//if keyid is set
				if(isset($_GET['keyid'.$tempKey]))
				{
					//datatype
					$itemDataType = $mySQL->columnDatatype(NULL,NULL,$componentArr[$x]['COMPONENTBINDINGSOURCE'],$getPrimaryKeyRs[$f]['COLUMN_NAME']);
					$itemType = $itemDataType[0]['DATA_TYPE'];
					
					//check datatype decimal, float, int
					if($itemType == 'DECIMAL' || $itemType == 'FLOAT' || $itemType == 'INT' || $itemType == 'INTN' || $itemType == 'NUMERIC')
					{
						//list of primary key in array
						$listOfPrimaryKey[$f] = $getPrimaryKeyRs[$f]['COLUMN_NAME']." = ".$_GET['keyid'.$tempKey];
					}//eof if
					else
					{
						//list of primary key in array
						$listOfPrimaryKey[$f] = $getPrimaryKeyRs[$f]['COLUMN_NAME']." = '".$_GET['keyid'.$tempKey]."'";
					}//eof else
				}//eof if
			}//eof for
			
			//if listofcolumn and listofprimarykey area arrays
			if(is_array($listOfColumn)&&is_array($listOfPrimaryKey))
			{
				//construct the sql
				$getData = "select ".implode(', ',$listOfColumn)." 
							from ".$componentArr[$x]['COMPONENTBINDINGSOURCE']."
							where ".implode(' and ', $listOfPrimaryKey);
			}//eof if
		}
	}//end if componetn form 1 col / form 2 col
	
	//if type is TABULAR
	else if($componentArr[$x]['COMPONENTTYPE'] == 'tabular')
	{
		//construct the sql
		$getData = "select ".implode(', ',$listOfColumn)." 
					from ".$componentArr[$x]['COMPONENTBINDINGSOURCE'];

		//if get KEYID is set		
		if(isset($_GET['keyid']) || isset($_GET['keyid2']) || isset($_GET['keyid3'])) 
		{
			//get tabular primary key
			$tabularPrimaryKey = $mySQL->listPrimaryKey($componentArr[$x]['COMPONENTBINDINGSOURCE']);
			
			//append where clause
			$getData .= " where ";
			
			//if tabularPrimaryKey is array - means multiple primary key
			if(is_array($tabularPrimaryKey))
			{
				//for all rows in tabular primary key, append to where statement
				for($f=0; $f < count($tabularPrimaryKey); $f++)
				{	
					//if first iteration
					if($f == 0)
						$keyID = '';		//append empty string to keyid name
					else
						$keyID = $f+1;		//else, append current loop iteration number
					
					//if keyid is set
					if(isset($_GET['keyid'.$keyID]))
					{
						//datatype
						$itemDataType = $mySQL->columnDatatype(NULL,NULL,$componentArr[$x]['COMPONENTBINDINGSOURCE'],$tabularPrimaryKey[$f]['COLUMN_NAME']);
						$itemType = $itemDataType[0]['DATA_TYPE'];
						
						//check datatype decimal, float, int
						if($itemType == 'DECIMAL' || $itemType == 'FLOAT' || $itemType == 'INT' || $itemType == 'INTN' || $itemType == 'NUMERIC')
						{
							//list of primary key in array
							$tempGetData[] = $tabularPrimaryKey[$f]['COLUMN_NAME']." = ".$_GET['keyid'.$keyID];
						}//eof if
						else
						{
							//list of primary key in array
							$tempGetData[] = $tabularPrimaryKey[$f]['COLUMN_NAME']." = '".$_GET['keyid'.$keyID]."'";
						}//eof else
					}//eof if
				}
				
				//append tempgetdata to getData string
				$getData .= implode(' and ',$tempGetData);
			}//end if is_array
			
			//if string is returned
			else if(is_string($tabularPrimaryKey))
			{	
				//if keyid is set
				if(isset($_GET['keyid']))
				{
					//datatype
					$itemDataType = $mySQL->columnDatatype(NULL,NULL,$componentArr[$x]['COMPONENTBINDINGSOURCE'],$tabularPrimaryKey);
					$itemType = $itemDataType[0]['DATA_TYPE'];
					
					//check datatype decimal, float, int
					if($itemType == 'DECIMAL' || $itemType == 'FLOAT' || $itemType == 'INT' || $itemType == 'INTN' || $itemType == 'NUMERIC')
					{
						//list of primary key in array
						$getData .= $tabularPrimaryKey." = ".$_GET['keyid'];
					}//eof if
					else
					{
						//list of primary key in array
						$getData .= $tabularPrimaryKey." = '".$_GET['keyid']."'";
					}//eof if
				}//eof if
			}//end if is string
		}//end if isset keyid(s)
		//if no keyid set
		else
		{
			//get tabular primary key
			$tabularPrimaryKey = $mySQL->listPrimaryKey($componentArr[$x]['COMPONENTBINDINGSOURCE']);
			
			//get position of primary key in column name
			$tabularPrimaryKeyPos = array_search($tabularPrimaryKey,$listOfColumn);
			
			//append ordering on number of column of primary key
			$getData .= " order by ".($tabularPrimaryKeyPos+1);
			
		}//eof else
	}//end if tabular
	
	//get the data using constructed sql
	if($getData)
		$getDataRs = $myQuery->query($getData,'SELECT','NAME');
}//eof if
?>