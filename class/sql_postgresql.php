<?php

/*SQL function for oracle*/
class dbSQLPostgreSQL extends dbSQLBatch
{
	protected $dbQuery;
	protected $dbc;
	
	public function __construct($dbc,$dbms)
	{	
		$this->dbc = $dbc;
		$this->dbQuery = $this->getObj($dbc,$dbms);
	}
//========================================== database ==============================================
	//list table fr db by schema
	public function listTable($schema)
	{
		$user_profile_schema = USER_PROFILE;
		$sql="select distinct OWNER||'.'||TABLE_NAME TABLE_NAME
					from all_TABLES 
					where OWNER in ('".implode("','",explode(',',strtoupper($schema)))."') 
					or TABLE_NAME='$user_profile_schema'
					order by 1";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//list view fr db by schema
	public function listView($schema)
	{
		$sql="select distinct OWNER||'.'||VIEW_NAME VIEW_NAME
				from all_views 
				where OWNER in ('".implode("','",explode(',',strtoupper($schema)))."') 
				order by 1";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//column name fr db by table
	public function listTableColumn($schema,$user,$table,$columnExclude='')
	{
		//check for tablespace
		$temp=explode('.',$table);
		
		//if have tablespace
		if(count($temp)>1)
		{
			$user = $temp[0];
			$table = $temp[1];
		}//eof if
		
		//column to be excluded
		if($columnExclude)
			$extra = " and upper(COLUMN_NAME) not in ('".implode("','",explode(',',strtoupper($columnExclude)))."') ";
		
		//selected fr owner
		if($user)
			$extra .= " and upper(OWNER) = upper('".$user."') ";
				
		$sql="select upper(COLUMN_NAME) COLUMN_NAME
				from all_tab_columns 
				where upper(TABLE_NAME) = upper('".$table."')
				".$extra."
				order by COLUMN_NAME";
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
			$user = $temp[0];
			$table = $temp[1];
		}//eof if
		
		//if have table schema
		if($user)
			$extra = " and upper(OWNER) = upper('".$user."')";		//pending
		
		$sql="select upper(a.COLUMN_NAME) COLUMN_NAME
					from ALL_CONS_COLUMNS a  
					join ALL_CONSTRAINTS c on a.CONSTRAINT_NAME = c.CONSTRAINT_NAME  
					where upper(c.TABLE_NAME) = upper('".$table."')
					and c.CONSTRAINT_TYPE = 'P'";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//datatype fr db by column
	public function columnDatatype($schema,$user,$table,$column='')
	{
		//check for tablespace
		$temp=explode('.',$table);
		
		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if
		
		if($column)
			$extra=" and upper(COLUMN_NAME) = upper('".$column."')";
		
		$sql="select upper(COLUMN_NAME) COLUMN_NAME, upper(DATA_TYPE) DATA_TYPE
				from all_tab_columns 
				where upper(TABLE_NAME) = upper('".$table."')
				and upper(OWNER) in ('".implode("','",explode(',',strtoupper($user)))."')".$extra;
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function
	
	//execute fr dump file
	public function dbExecute($conn,$database,$user,$password,$sql)
	{
		//$fileStr=str_replace("\r\n","",file_get_contents($file));
		
		return $this->dbQuery->query('BEGIN '.$sql.' END;','RUN');
	}//eof function
	
	//store unlimited character
	public function storeUnlimitedChar($tableName, $columnName, $character, $whereClause='')
	{
		//update unlimited character into db
		echo $sql = "update ".$tableName." set ".$columnName." = '".$character."' ".$whereClause;
		return $this->dbQuery->query($sql,'RUN');
	}//eof function
//======================================== eof database ============================================

//========================================= basic stuff ============================================
	//dual
	public function fromDual()
	{
		return " from dual";
	}//eof function
	
	//max value
	public function maxValue($table,$column,$startValue=0,$where='')
	{
		//if have where clause
		if($where)
			$extra=" where ".$where;

		$result = $this->dbQuery->query("select nvl(max(to_number(".$column.")),".$startValue.") from ".$table.$extra);
		return $result[0][0];
	}//eof function
	
	//sql for if null
	public function isNullSQL($expr1,$expr2)
	{
		return "nvl(".$expr1.",".$expr2.")";
	}//eof function
	
	//sql for limit
	public function limit($rownum,$concatSQL)
	{
		return $concatSQL." rownum<=".$rownum;
	}//eof function
	
	//concat string
	public function concat()
	{
		$vargs = func_get_args();
		$vargsCount = count($vargs);
		
		for($x=0; $x<$vargsCount; $x++)
		{
			if($x==0)
				$result=$vargs[$x];
			else
				$result.="||".$vargs[$x];
		}//eof for
		
		return $result;
	}//eof function
	
	//convert to char
	public function convertToChar($string)
	{
		return "to_char(".$string.")";
	}//eof function
	
	//convert to number
	public function convertToNumber($string)
	{
		return "to_number(".$string.")";
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
			
			$orderByStr .= "'".$fieldValueArray[$x]."', ".$x;
		}//eof for
		
		//return the order by string
		return "decode(".$fieldName.", ".$orderByStr.", ".$fieldValueArrayCount.")";
	}//eof function
//======================================= eof basic stuff ==========================================

//======================================= date functions ===========================================
	//current date
	public function currentDate()
	{
		return 'CURRENT_DATE';
	}//eof function
	
	//date format
	public function date_format()
	{
		//convert to oracle date
		$date_format=str_replace('format-','',DEFAULT_DATE_FORMAT);
		$date_format=str_replace('Y','rrrr',$date_format);		//year
		$date_format=str_replace('m','mm',$date_format);		//month
		$date_format=str_replace('d','dd',$date_format);		//day
		
		return $date_format;
	}//eof function
	
	//date format (from)
	public function convertFromDate($date)
	{
		return "to_char(".$date.",'".$this->date_format()."')";
	}//eof function
	
	//date format (to)
	public function convertToDate($date)
	{
		return "to_date('".$date."','".$this->date_format()."')";
	}//eof function
//===================================== eof date functions ===========================================

//============================================ menu ==================================================
	//function to get menu
	public function menu($filter='')
	{
		$sql="select (decode(c.MENUTITLE,null,'',c.MENUTITLE ||' / ')|| b.MENUTITLE ||' / ' || a.MENUTITLE) as MENUTITLE,
							a.MENUID, d.PAGEID
							from FLC_MENU a, FLC_MENU b, FLC_MENU c, FLC_PAGE d 
							where a.MENUPARENT = b.MENUID (+) and b.MENUPARENT = c.MENUID (+)
							and a.MENUID = d.MENUID
							and a.MENUPARENT != 0 
							and upper((decode(c.MENUTITLE,null,'',c.MENUTITLE ||' / ')|| b.MENUTITLE ||' / ' || a.MENUTITLE)) like upper('%".$filter."%')
							order by 1";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function page
	
	//function to get menu with page excluded
	public function menuExcludePage($page='',$filter='')
	{
		//if modify page
		if($page){$appendWhere = " where PAGEID != ".$page;}		//append where clause
		
		$sql="select (decode(c.MENUTITLE,null,'',c.MENUTITLE ||' / ')|| b.MENUTITLE || ' / ' || a.MENUTITLE) as MENUTITLE, a.MENUID 
					from FLC_MENU a, FLC_MENU b, FLC_MENU c
					where a.MENUPARENT = b.MENUID (+) and b.MENUPARENT = c.MENUID (+)
					and a.MENUID not in (select MENUID from FLC_PAGE ".$appendWhere.")
					and a.MENUID not in (select MENUPARENT from FLC_MENU where MENUPARENT is not null)
					and a.MENUPARENT != 0 
					and a.MENULINK like '%page_wrapper%'
					and upper((decode(c.MENUTITLE,null,'',c.MENUTITLE ||' / ')|| b.MENUTITLE ||' / ' || a.MENUTITLE)) like upper('%".$filter."%')
					order by 1";
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
		$user_profile_username 	= USER_PROFILE_USERNAME;		
		$sql="select a.REFERENCEID, 
				decode(P.lookup,null,a.GROUPCODE, nvl((select REFERENCECODE from ".$tablename." where to_char(referenceid)=a.GROUPCODE),a.GROUPCODE||'*')) groupcode,
				decode(Q.lookup,null,a.REFERENCECODE, nvl((select REFERENCECODE from ".$tablename." where to_char(referenceid)=a.REFERENCECODE),a.REFERENCECODE||'*')) referencecode,
				decode(R.lookup,null,a.DESCRIPTION1, nvl((select REFERENCECODE from ".$tablename." where to_char(referenceid)=a.DESCRIPTION1),a.DESCRIPTION1||'*')) description1, 
				decode(S.lookup,null,a.DESCRIPTION2, nvl((select REFERENCECODE from ".$tablename." where to_char(referenceid)=a.DESCRIPTION2),a.DESCRIPTION2||'*')) description2, 
				decode(T.lookup,null,a.PARENTCODE, nvl((select REFERENCECODE from ".$tablename." where to_char(referenceid)=a.PARENTCODE),a.PARENTCODE||'*')) parentcode, 
				decode(U.lookup,null,a.PARENTROOTCODE, nvl((select REFERENCECODE from ".$tablename." where to_char(referenceid)=a.PARENTROOTCODE),a.PARENTROOTCODE||'*')) parentrootcode,
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
			$sql="select * from (".$sql.") where ".$filter;
		
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
					nvl(a.GROUPCODE,b.GROUPCODEDEFAULTVALUE),
					nvl(a.REFERENCECODE,b.CODEDEFAULTVALUE),
					nvl(a.DESCRIPTION1,b.DESCRIPTION1DEFAULTVALUE),
					nvl(a.DESCRIPTION2,b.DESCRIPTION2DEFAULTVALUE),
					nvl(a.PARENTCODE,b.PARENTCODEDEFAULTVALUE),
					nvl(a.PARENTROOTCODE,b.PARENTROOTCODEDEFAULTVALUE),
					to_char(a.TIMESTAMP,'dd-Mon-yyyy'), 
					a.REFERENCESTATUSCODE, (select $user_profile_username from $user_profile_schema where $user_profile_userid =a.USERID) userid 
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
			$sql="select REFERENCEID, REFERENCECODE||' - '||DESCRIPTION1 from ".$tablename." 
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
				decode(b.GROUPCODE,'".$groupcode."',a.GROUPCODENAME,null),
				decode(b.REFERENCECODE,'".$referencecode."',a.CODENAME,null),
				decode(b.DESCRIPTION1,'".$description1."',a.DESCRIPTION1NAME,null),
				decode(b.DESCRIPTION2,'".$description2."',a.DESCRIPTION2NAME,null),
				decode(b.PARENTCODE,'".$parentcode."',a.PARENTCODENAME,null),
				decode(b.PARENTROOTCODE,'".$parentrootcode."',a.PARENTROOTCODENAME,null)
				from (
					select 
						decode(groupCODEUNIQUE,null,null,GROUPCODENAME) GROUPCODENAME, 
						decode(CODEUNIQUE,null,null,CODENAME) CODENAME, 
						decode(DESCRIPTION1UNIQUE,null,null,DESCRIPTION1NAME) DESCRIPTION1NAME, 
						decode(DESCRIPTION2UNIQUE,null,null,DESCRIPTION2NAME) DESCRIPTION2NAME, 
						decode(PARENTCODEUNIQUE,null,null,PARENTCODENAME) PARENTCODENAME, 
						decode(PARENTROOTCODEUNIQUE,null,null,PARENTROOTCODENAME) PARENTROOTCODENAME 
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