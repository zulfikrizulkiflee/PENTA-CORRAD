<?php

/*SQL function for mysql*/
class dbSQLMySQL extends dbSQLBatch
{
	protected $dbQuery;
	protected $dbc;
	
	public function __construct($dbc,$dbms)
	{	
		$this->dbc = $dbc;
		$this->dbQuery = $this->getObj($dbc,$dbms);
	}
//========================================= datatabase =============================================
	//list table fr db by schema
	public function listTable($schema)
	{
		$sql = "select concat(TABLE_SCHEMA,'.',TABLE_NAME) TABLE_NAME
					from information_schema.tables
					where TABLE_SCHEMA in ('".implode("','",explode(',',strtoupper($schema)))."')
					order by 1";
					
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//list view fr db by schema
	public function listView($schema)
	{
		$sql = "select concat(TABLE_SCHEMA,'.',TABLE_NAME) VIEW_NAME
					from information_schema.views
					where TABLE_SCHEMA in ('".implode("','",explode(',',strtoupper($schema)))."')
					order by 1";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//column name fr db by table
	public function listTableColumn($schema,$user,$table,$columnExclude='',$order='COLUMN_NAME')
	{
		//check for tablespace
		$temp=explode('.',$table);
		
		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if
		
		//column to be excluded
		if($columnExclude)
			$extra = " and upper(COLUMN_NAME) not in ('".implode("','",explode(',',strtoupper($columnExclude)))."') ";
		
		//selected fr owner
		if($schema)
			$extra .= " and upper(TABLE_SCHEMA) = upper('".$schema."') ";
		
		$sql = "select upper(COLUMN_NAME) COLUMN_NAME
					from information_schema.columns 
					where upper(TABLE_NAME) = upper('".$table."')
					".$extra."
					group by COLUMN_NAME
					order by ".$order.";";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//list primary key fr db by table
	public function listPrimaryKey($table)
	{
		//check for tablespace
		$temp=explode('.',$table);
		
		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if
		
		//if have table schema
		if($schema)
			$extra = " and upper(TABLE_SCHEMA) = upper('".$schema."')";
		
		$sql="select upper(COLUMN_NAME) COLUMN_NAME
				from information_schema.columns  
				where upper(TABLE_NAME) = upper('".$table."')
				".$extra."
				and COLUMN_KEY='PRI'";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//datatype fr db by column
	public function columnDatatype($schema,$user,$table,$column='',$extraInfo=array())
	{
		//check for tablespace
		$temp=explode('.',$table);
		
		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if
		
		if(count($extraInfo))
		{
			if(in_array('size',$extraInfo))
			{
				$extraInfoCol .= ', a.CHARACTER_MAXIMUM_LENGTH as SIZE';
			}
			if(in_array('primary',$extraInfo))
			{
				$extraInfoCol .= ', a.COLUMN_KEY as PRI_KEY';
			}
			if(in_array('extra',$extraInfo))
			{
				$extraInfoCol .= ', a.EXTRA as AUTO_INC';
			}
		}
		
		if($column)
			$extra=" and upper(COLUMN_NAME) = upper('".$column."')";
		
		$sql="select upper(a.COLUMN_NAME) COLUMN_NAME, upper(a.DATA_TYPE) DATA_TYPE ".$extraInfoCol."
				from information_schema.columns a
				where upper(a.TABLE_SCHEMA) = '".strtoupper($schema)."'
				and upper(a.TABLE_NAME) = upper('".$table."')
				and upper(a.TABLE_SCHEMA) in ('".implode("','",explode(',',strtoupper($schema)))."')".$extra;
		
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//execute fr dump file
	public function dbExecute($conn,$database,$user,$password,$sql)
	{
		$mysqli = new mysqli($conn, $user, $password, $database);

		return $mysqli->multi_query($sql);
	}//eof function
	
	//store unlimited character
	public function storeUnlimitedChar($tableName, $columnName, $character, $whereClause='')
	{
		//update unlimited character into db
		$sql = "update ".$tableName." set ".$columnName." = '".$character."' ".$whereClause;
		return $this->dbQuery->query($sql,'RUN');
	}//eof function
//======================================= eof datatabase ===========================================

//========================================= basic stuff ============================================
	//dual
	public function fromDual()
	{
		return "";
	}//eof function
	
	//max value
	public function maxValue($table,$column,$startValue=0,$where='')
	{
		//if have where clause
		if($where)
			$extra=" where ".$where;

		$result = $this->dbQuery->query("select ifnull(max(".$column."),".$startValue.") from ".$table.$extra);
		return $result[0][0];
	}//eof function
	
	//sql for if null
	public function isNullSQL($expr1,$expr2)
	{
		return "ifnull(".$expr1.",".$expr2.")";
	}//eof function
	
	//sql for limit
	public function limit($rownum)
	{
		return " limit ".$rownum;
	}//eof function
	
	//concat string
	public function concat()
	{
		$vargs = func_get_args();
		$vargsCount = count($vargs);
		
		//loop on count of parameter sent
		for($x=0; $x<$vargsCount; $x++)
		{
			if($x==0)
				$result="concat(".$vargs[$x];
			else
				$result.=",".$vargs[$x];
		}//eof for
		
		//if result have value
		if($result)
			$result.=")";
		
		return $result;
	}//eof function
	
	//convert to char
	public function convertToChar($string)
	{
		return $string;
	}//eof function
	
	//convert to number
	public function convertToNumber($string)
	{
		return $string;
	}//eof function
	
	//substring
	public function substring($string,$start,$length='')
	{
		//if no length is specified, the remainder of the string is returned 
		if($length)
			return "substr(".$string.",".$start.",".$length.")";
		else
			return "substr(".$string.",".$start.")";
	}//eof function
	
	//custom order by
	public function customOrder($fieldName, $fieldValueArray)
	{
		//count of field value
		$fieldValueArrayCount = count($fieldValueArray);
		
		//loop on count of field value
		for($x=0; $x<$fieldValueArrayCount; $x++)
		{
			//append comma (,), if have value
			if($orderByStr)	$orderByStr .= ", ";
			
			$orderByStr .= "'".$fieldValueArray[$x]."'";
		}//eof for
		
		//return the order by string
		return "FIELD(".$fieldName.", ".$orderByStr.")";
	}//eof function
//======================================= eof basic stuff ==========================================

//======================================= date functions ===========================================
	//current date
	public function currentDate()
	{
		return 'now()';
	}//eof function
	
	//date format
	public function date_format()
	{
		//convert to mysql date
		$date_format=str_replace('format-','',DEFAULT_DATE_FORMAT);
		$date_format=str_replace('Y','%Y',$date_format);		//year
		$date_format=str_replace('m','%m',$date_format);		//month
		$date_format=str_replace('d','%d',$date_format);		//day
		
		return $date_format;
	}//eof function
	
	//date format (from)
	public function convertFromDate($date)
	{
		return "date_format(".$date.",'".$this->date_format()."')";
	}//eof function
	
	//datetime format (from)
	public function convertFromDatetime($datetime)
	{
		return "date_format(".$datetime.",'".$this->date_format()." %H:%i:%s')";
	}//eof function
	
	//date format (to)
	public function convertToDate($date)
	{
		return "str_to_date('".$date."','".$this->date_format()."')";
	}//eof function
	
	//datetime format (to)
	public function convertToDatetime($datetime)
	{
		return "str_to_date('".$datetime."','".$this->date_format()." %H:%i:%s')";
	}//eof function
//===================================== eof date functions =========================================

//============================================ menu ==================================================
	//function to get menu
	public function menu($filter='')
	{
		$sql = "select concat(if(isnull(c.MENUTITLE),'',concat(c.MENUTITLE ,' / ')),b.MENUTITLE ,' / ' , a.MENUTITLE) as MENUTITLE, a.MENUID, d.PAGEID
					from FLC_PAGE d, FLC_MENU a
							left outer join FLC_MENU b on a.MENUPARENT = b.MENUID 
							left outer join FLC_MENU c on b.MENUPARENT = c.MENUID
					where a.MENUID = d.MENUID and a.MENUPARENT != 0 
						and upper(concat(if(isnull(c.MENUTITLE),'',concat(c.MENUTITLE ,' / ')), b.MENUTITLE ,' / ' , a.MENUTITLE)) like upper('%".$filter."%')
					order by upper(concat(if(isnull(c.MENUTITLE),'',concat(c.MENUTITLE ,' / ')),b.MENUTITLE ,' / ' , a.MENUTITLE))";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function page
	
	//function to get menu with page excluded
	public function menuExcludePage($page='',$filter='')
	{
		//if modify page
		if($page){$appendWhere = " where PAGEID != ".$page;}		//append where clause
		
		$sql = "select concat(if(isnull(c.MENUTITLE),'',concat(c.MENUTITLE,' / ')), b.MENUTITLE,' / ',a.MENUTITLE) as MENUTITLE, a.MENUID 
					from FLC_MENU a
						left outer join FLC_MENU b on a.MENUPARENT = b.MENUID 
						left outer join FLC_MENU c on b.MENUPARENT = c.MENUID
					where a.MENUID not in (select MENUID from FLC_PAGE ".$appendWhere.")
						and a.MENUID not in (select MENUPARENT from FLC_MENU where MENUPARENT is not null)
						and a.MENUPARENT != 0
						and a.MENULINK like '%page_wrapper%' 
						and upper(concat(if(isnull(c.MENUTITLE),'',concat(c.MENUTITLE,' / ')),b.MENUTITLE,' / ',a.MENUTITLE)) like upper('%".$filter."%')
					order by upper(concat(if(isnull(c.MENUTITLE),'',concat(c.MENUTITLE,' / ')), b.MENUTITLE,' / ',a.MENUTITLE))";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function menu
//========================================== eof menu ================================================

//========================================= reference ================================================
	//get reference data
	public function referenceData($tablename,$referenceid,$usertype=3,$filter='')
	{
		//extra field for admin and system
		if($usertype!=3)
			$extra=", 'Tarikh' as tarikh, 'Status' as status, 'Oleh' as oleh";
			
		//container
		$sql="select GROUPCODENAME, CODENAME, DESCRIPTION1NAME, DESCRIPTION2NAME, 
					PARENTCODENAME, PARENTROOTCODENAME ".$extra."
				from SYSREFCONTAINER
				where REFERENCEID=".$referenceid;
		$refContainer=$this->dbQuery->query($sql);
		$refContainerCount=count($refContainer[0]);
		
		//restricted view admin and user type
		if($usertype==1)
			$extra='';
		else
			$extra=" and REFERENCESTATUSCODE='00'";
		
		//reference data
		$user_profile_schema = USER_PROFILE;
		$user_profile_userid = USER_PROFILE_USERID;
		$user_profile_username = USER_PROFILE_USERNAME;
		$sql="select a.REFERENCEID, 
				if(!P.lookup,a.GROUPCODE, ifnull((select REFERENCECODE from ".$tablename." where REFERENCEID=a.GROUPCODE),concat(a.GROUPCODE,'*'))) groupcode,
				if(!Q.lookup,a.REFERENCECODE, ifnull((select REFERENCECODE from ".$tablename." where REFERENCEID=a.REFERENCECODE),concat(a.REFERENCECODE,'*'))) referencecode,
				if(!R.lookup,a.DESCRIPTION1, ifnull((select REFERENCECODE from ".$tablename." where REFERENCEID=a.DESCRIPTION1),concat(a.DESCRIPTION1,'*'))) DESCRIPTION1, 
				if(!S.lookup,a.DESCRIPTION2, ifnull((select REFERENCECODE from ".$tablename." where REFERENCEID=a.DESCRIPTION2),concat(a.DESCRIPTION2,'*'))) description2, 
				if(!T.lookup,a.PARENTCODE, ifnull((select REFERENCECODE from ".$tablename." where REFERENCEID=a.PARENTCODE),concat(a.PARENTCODE,'*'))) parentcode, 
				if(!U.lookup,a.PARENTROOTCODE, ifnull((select REFERENCECODE from ".$tablename." where REFERENCEID=a.PARENTROOTCODE),concat(a.PARENTROOTCODE,'*'))) parentrootcode,
				".$this->convertFromDate('a.TIMESTAMP').", 
				(select DESCRIPTION1 from REFSYSTEM where REFERENCECODE=a.REFERENCESTATUSCODE
					and MASTERCODE=(select REFERENCECODE from REFSYSTEM where DESCRIPTION1='REFERENCE_STATUS')) referencestatuscode,
				(select $user_profile_username from $user_profile_schema where $user_profile_userid = a.USERID) userid
				from ".$tablename." a,
				(".$this->getLookupTableString('GROUPCODELOOKUPTABLE', $referenceid).") P,
				(".$this->getLookupTableString('CODELOOKUPTABLE', $referenceid).") Q,
				(".$this->getLookupTableString('DESCRIPTION1LOOKUPTABLE', $referenceid).") R,
				(".$this->getLookupTableString('DESCRIPTION2LOOKUPTABLE', $referenceid).") S,
				(".$this->getLookupTableString('PARENTCODELOOKUPTABLE', $referenceid).") T,
				(".$this->getLookupTableString('PARENTROOTCODELOOKUPTABLE', $referenceid).") U
				where MASTERCODE=(select REFERENCECODE from ".$tablename." where REFERENCEID=".$referenceid.") 
				".$extra."
				order by 2,3,4,5,6,7";
		
		//if have filter
		if($filter)
			$sql="select * from (".$sql.")a where ".$filter;
		
		//execute select statement
		$refData=$this->dbQuery->query($sql);
		$refDataCount=count($refData);	//count data
		
		//loop on count
		for($x=0;$x<$refDataCount;$x++)
		{
			for($y=0;$y<$refContainerCount;$y++)
				if($refContainer[0][$y]!='')
					$refArray[$x][$refContainer[0][$y]]=$refData[$x][$y+1];
			
			$refArray[$x]['Perincian']='<a href="'.$_SERVER['REQUEST_URI'].'&dataid='.$refData[$x][0].'">Perincian</a>';
			
			if($usertype!=3)
				$refArray[$x]['Padam']='<input name="deleteID[]" type="checkbox" value="'.$refData[$x][0].'" />';
		}//eof for
		
		return $refArray;
	}//eof function
	
	//get data
	public function data($tablename,$referenceid, $dataid='')
	{
		//return in array (index form) - $data['Name'][<index>],$data[0][<index>]
		//index = 	0 - referenceid
		//			1 - groupcode
		//			2 - referencecode
		//			3 - description1
		//			4 - description2
		//			5 - parentcode
		//			6 - parentrootcode
		//			7 - timestamp
		//			8 - referencestatuscode,
		//			9 - userid
		
		//if receive referenceid
		if($referenceid)
		{
			//data name
			$sql="select 'Internal Key', GROUPCODENAME,CODENAME,DESCRIPTION1NAME, DESCRIPTION2NAME,PARENTCODENAME,PARENTROOTCODENAME,
					'Tarikh', 'Status', 'Oleh'
					from SYSREFCONTAINER 
					where REFERENCEID=".$referenceid;
			$dataName=$this->dbQuery->query($sql);
			$dataNameCount=count($dataName[0]);
		}//eof if
		//else use dataid
		else
			//data name
			$sql="select 'Internal Key', GROUPCODENAME,CODENAME,DESCRIPTION1NAME, DESCRIPTION2NAME,PARENTCODENAME,PARENTROOTCODENAME,
					'Tarikh', 'Status', 'Oleh'
					from SYSREFCONTAINER 
					where REFERENCEID=(select REFERENCEID 
											from ".$tablename." 
											where REFERENCECODE=(select MASTERCODE from ".$tablename." where REFERENCEID=".$dataid."))";
			$dataName=$this->dbQuery->query($sql);
			$dataNameCount=count($dataName[0]);

		//if receive dataid, fetch data
		if($dataid)
		{
			//data
			$user_profile_schema = USER_PROFILE;
			$user_profile_userid = USER_PROFILE_USERID;
			$user_profile_username 	= USER_PROFILE_USERNAME;
			$sql="select a.REFERENCEID, 
					ifnull(a.GROUPCODE,b.GROUPCODEDEFAULTVALUE),
					ifnull(a.REFERENCECODE,b.CODEDEFAULTVALUE),
					ifnull(a.DESCRIPTION1,b.DESCRIPTION1DEFAULTVALUE),
					ifnull(a.DESCRIPTION2,b.DESCRIPTION2DEFAULTVALUE),
					ifnull(a.PARENTCODE,b.PARENTCODEDEFAULTVALUE),
					ifnull(a.PARENTROOTCODE,b.PARENTROOTCODEDEFAULTVALUE),
					".$this->convertFromDate('a.TIMESTAMP').", 
					a.REFERENCESTATUSCODE, (select $user_profile_username from $user_profile_schema where $user_profile_userid = a.USERID) USERID 
					from ".$tablename." a, 
						(select GROUPCODEDEFAULTVALUE,CODEDEFAULTVALUE, DESCRIPTION1DEFAULTVALUE, 
							DESCRIPTION2DEFAULTVALUE, PARENTCODEDEFAULTVALUE, PARENTROOTCODEDEFAULTVALUE
						from SYSREFCONTAINER where REFERENCEID = 
							(select REFERENCEID from ".$tablename." where REFERENCECODE= 
								(select MASTERCODE from ".$tablename." where REFERENCEID=".$dataid.")
							and REFERENCESTATUSCODE='00'
        					and MASTERCODE='XXX')) b
					where a.REFERENCEID=".$dataid;
			$data=$this->dbQuery->query($sql);
		}//eof if

		//loop on dataname
		for($x=0;$x<$dataNameCount;$x++)
			$data['Name'][$x]=$dataName[0][$x];
		
		return $data;
	}//eof function
	
	//get lookup item
	public function getLookupItem($tablename,$type,$referenceid)
	{
		//search for lookup table (predefined)
		$sql=$this->getLookupTableString($type.'LOOKUPTABLE', $referenceid);
		$tempLookup=$this->dbQuery->query($sql);
		
		//value for predefined
		$lookupTable=$tempLookup[0][0];
		
		//if predefined
		if($lookupTable)
		{
			$sql="select REFERENCEID, concat(REFERENCECODE,' - ',DESCRIPTION1) DESCRIPTION1 from ".$tablename." 
					where MASTERCODE=(select REFERENCECODE from ".$tablename." where REFERENCEID=".$lookupTable.")
					and REFERENCESTATUSCODE='00'
					order by REFERENCECODE||' - '||DESCRIPTION1";
			$lookupArray=$this->dbQuery->query($sql);
		}//eof if
		else
		{
			$sql="select ".$type."QUERY from SYSREFCONTAINER where REFERENCEID=".$referenceid;
			$tempQuery=$this->dbQuery->query($sql);
			
			//query
			$lookupQuery=$tempQuery[0][0];
			
			//if hav query
			if($lookupQuery)
				$lookupArray=$this->dbQuery->query(convertToDBQry($lookupQuery));
		}//eof else
		
		return $lookupArray;
	}//eof function
	
	//check uniqueness
	public function checkUnique($table,$referenceid,$groupcode,$referencecode,$description1,$description2,$parentcode,$parentrootcode,$statuscode,$dataid='')
	{
		//if dataID is sent (for update only)
		if($dataid)
			$extra=" and b.REFERENCEID != ".$dataid;
			
		//check uniqueness (get container name if have duplicate in unique field)
		$sql="select distinct
				if(b.GROUPCODE='".$groupcode."',a.GROUPCODENAME,null),
				if(b.REFERENCECODE='".$referencecode."',a.CODENAME,null),
				if(b.DESCRIPTION1='".$description1."',a.DESCRIPTION1NAME,null),
				if(b.DESCRIPTION2='".$description2."',a.DESCRIPTION2NAME,null),
				if(b.PARENTCODE='".$parentcode."',a.PARENTCODENAME,null),
				if(b.PARENTROOTCODE='".$parentrootcode."',a.PARENTROOTCODENAME,null)
				from (
					select 
						if(groupCODEUNIQUE,null,GROUPCODENAME) GROUPCODENAME, 
						if(CODEUNIQUE,null,CODENAME) CODENAME, 
						if(DESCRIPTION1UNIQUE,null,DESCRIPTION1NAME) DESCRIPTION1NAME, 
						if(DESCRIPTION2UNIQUE,null,DESCRIPTION2NAME) DESCRIPTION2NAME, 
						if(PARENTCODEUNIQUE,null,PARENTCODENAME) PARENTCODENAME, 
						if(PARENTROOTCODEUNIQUE,null,PARENTROOTCODENAME) PARENTROOTCODENAME 
						from SYSREFCONTAINER
						where REFERENCEID=".$referenceid."
					) a, ".$table." b
				where b.MASTERCODE=(select REFERENCECODE from ".$table." where REFERENCEID=".$referenceid.")
				and b.REFERENCESTATUSCODE='00'
				and (b.GROUPCODE='".$groupcode."'
				or b.REFERENCECODE='".$referencecode."'
				or b.DESCRIPTION1='".$description1."'
				or b.DESCRIPTION2='".$description2."'
				or b.PARENTCODE='".$parentcode."'
				or b.PARENTROOTCODE='".$parentrootcode."')
				and '".$statuscode."' = '00'
				".$extra;
		
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
//======================================= eof reference ==============================================
}//eof class
?>
