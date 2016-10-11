<?php

/*SQL function for sybase*/
class dbSQLSybaseASE extends dbSQLBatch
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
		//separate schema
		$dbSchema = explode(',',$schema);
		$dbSchemaCount = count($dbSchema);

		//loop on count of schema
		for($x=0; $x< $dbSchemaCount; $x++)
		{
			//append
			if($x!=0)
				$sql .= " union ";

			$sql .= "select '".$dbSchema[$x]."..'||name TABLE_NAME
						from ".$dbSchema[$x]."..sysobjects
						where type = 'U'";
		}//eof for

		//sort alphabetically
		$sql = "select * from (".$sql.") a order by 1";

		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function

	//list view fr db by schema
	public function listView($schema)
	{
		//separate schema
		$dbSchema = explode(',',$schema);
		$dbSchemaCount = count($dbSchema);

		//loop on count of schema
		for($x=0; $x< $dbSchemaCount; $x++)
		{
			//append
			if($x!=0)
				$sql .= " union ";

			$sql .= "select '".$dbSchema[0]."..'||name VIEW_NAME
					from ".$dbSchema[0]."..sysobjects
					where type = 'V' and name != 'sysquerymetrics'";
			}//eof for

		//sort alphabetically
		$sql = "select * from (".$sql.") a order by 1";

		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function

	//column name fr db by table
	public function listTableColumn($schema,$user,$table,$columnExclude='')
	{
		//check for tablespace
		$temp=explode('..',$table);

		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if

		//column to be excluded
		if($columnExclude)
			$extra = " and a.name not in ('".implode("','",explode(',',strtoupper($columnExclude)))."') ";

		$sql = "select a.name as COLUMN_NAME
					from ".$schema."..syscolumns a, ".$schema."..sysobjects b
					where a.id = b.id and b.type = 'U' and b.name = '".$table."'
					".$extra."
					order by 1";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function

	//list primary key fr db by table
	public function listPrimaryKey($table)
	{
		//check for tablespace
		$temp=explode('..',$table);

		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0].'..';
			$table = $temp[1];
		}//eof if

		//get PK count
		$keyCount = "select a.keycnt
						from ".$schema."sysindexes a, ".$schema."sysobjects b
						where a.id = b.id and b.type = 'U' and b.name  = '".$table."'";
		$keyCountRs = $this->dbQuery->query($keyCount,'SELECT','NAME');

		//loop on count of key
		for($x=0; $x<$keyCountRs[0]['KEYCNT']; $x++)
		{
			//append
			if($x!=0)
				$sql .= " union ";

			$sql .= "select index_col('".$schema.$table."', a.indid, ".($x+1).") COLUMN_NAME
						from ".$schema."sysindexes a, ".$schema."sysobjects b
						where a.id = b.id
						and b.type = 'U' and b.name  = '".$table."'";
		}//eof for

		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function

	//datatype fr db by column
	public function columnDatatype($schema,$user,$table,$column='',$extraInfo=array())
	{
		//check for tablespace
		$temp=explode('..',$table);

		//if have tablespace
		if(count($temp)>1)
		{
			$schema = $temp[0];
			$table = $temp[1];
		}//eof if

		if(count($extraInfo))
		{
			if(in_array('size',$extraInfo))
				$extraInfoCol .= ', b.length as SIZE';

			if(in_array('primary',$extraInfo))
			{

				$checkPK = "select index_col
							(	'".$schema.'..'.$table."',
								(select i.indid
									from ".$schema."..sysobjects o, ".$schema."..sysindexes i
									where o.id=i.id
									and i.indid = 1
									and o.type = 'U'
									and o.name = '".$table."'
								),
								(select i.keycnt
									from ".$schema."..sysobjects o, ".$schema."..sysindexes i
									where o.id=i.id
									and i.indid = 1
									and o.type = 'U'
									and o.name = '".$table."'
								)
							) as PRI_KEY";
				$checkPKRs = $this->dbQuery->query($checkPK,'SELECT','NAME');

				if($table == 't015negeri')
					echo '<br><br>'.$checkPK;

				if($checkPKRs[0]['PRI_KEY'] == $column)
					$extraInfoCol .= ", 'PRI' as PRI_KEY";
			}
			if(in_array('extra',$extraInfo))
			{
				$checkIdentity = "select c.name as IDENTITY_COL
									from
										".$schema."..sysobjects a,
										".$schema."..sysindexes b,
										".$schema."..syscolumns c
									where a.type = 'U'
										and a.id = b.id
										and b.id = c.id
										and b.keycnt <> 0
										and c.status = 128";
				$checkIdentityRs = $this->dbQuery->query($checkIdentity,'SELECT','NAME');

				if($checkIdentityRs[0]['IDENTITY_COL'] == $column)
					$extraInfoCol .= ", 'auto_increment' as AUTO_INC";
			}
		}

		if($column)
			$extra=" and upper(b.name) = '".strtoupper($column)."'";

		$sql = "select upper(b.name) COLUMN_NAME, upper(a.name) DATA_TYPE ".$extraInfoCol."
					from
					".$schema."..syscolumns b, systypes a
					where a.type = b.type
					and a.usertype = b.usertype
					".$extra."
					order by 1";
		$sqlRs = $this->dbQuery->query($sql,'SELECT','NAME');

		if(count($sqlRs))
			return $sqlRs;
		else
		{
		$sql = "select upper(b.name) COLUMN_NAME, upper(a.name) DATA_TYPE ".$extraInfoCol."
					from
					".$schema."..syscolumns b, systypes a
					where a.type = b.type
					".$extra."
					order by 1";
		return $this->dbQuery->query($sql,'SELECT','NAME');
		}
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

		$result = $this->dbQuery->query("select coalesce(max(".$column."),".$startValue.") x from ".$table.$extra);
		return $result[0][0];
	}//eof function

	//sql for if null
	public function isNullSQL($expr1,$expr2)
	{
		return "coalesce(".$expr1.",".$expr2.")";
	}//eof function

	//sql for limit
	public function limit($rownum)
	{
		//return " top ".$rownum;
	}//eof function

	//concat string
	public function concat()
	{
		$vargs = func_get_args();
		$vargsCount = count($vargs);

		//loop on count of parameter sent
		for($x=0; $x<$vargsCount; $x++)
		{
			//append if have value
			if($result)
				$result.="||";

			$result.=$vargs[$x];
		}//eof for

		return $result;
	}//eof function

	//convert to char
	public function convertToChar($string)
	{
		return "convert(varchar,".$string.")";
	}//eof function

	//convert to number
	public function convertToNumber($string)
	{
		return "convert(int,".$string.")";
	}//eof function

	//substring
	public function substring($string,$start,$length='')
	{
		//if no length is specified, the remainder of the string is returned
		if(!$length)
			$length = "char_length(".$string.")";

		return "substring(".$string.",".$start.",".$length.")";
	}//eof if

	//custom order by
	public function customOrder($fieldName, $fieldValueArray)
	{
		//count of field value
		$fieldValueArrayCount = count($fieldValueArray);

		//loop on count of field value
		for($x=0; $x<$fieldValueArrayCount; $x++)
		{
			//append comma (,), if have value
			//if($orderByStr)	$orderByStr .= ", ";

			$orderByStr .= " WHEN ".$fieldName." = '".$fieldValueArray[$x]."' then ".$x;
		}//eof for

		//return the order by string
		return "CASE ".$orderByStr." END";
	}//eof function
//======================================= eof basic stuff ==========================================

//======================================= date functions ===========================================
	//current date
	public function currentDate()
	{
		return 'getdate()';
	}//eof function

	//date format
	public function date_format()
	{
		//convert to sybase date
		$date_format=str_replace('format-','',DEFAULT_DATE_FORMAT);
		$date_format=str_replace('Y','yyyy',$date_format);		//year
		$date_format=str_replace('m','mm',$date_format);		//month
		$date_format=str_replace('d','dd',$date_format);		//day

		return $date_format;
	}//eof function

	//date format (from)
	public function convertFromDate($date)
	{
		//style of date
		switch($this->date_format())
		{
			case 'dd/mm/yyyy': $style=103;
				break;
			case 'dd-mm-yyyy': $style=105;
				break;
			case 'mm/dd/yyyy': $style=101;
				break;
			case 'mm-dd-yyyy': $style=110;
				break;
			case 'yyyy/mm/dd': $style=111;
				break;
			case 'yyyymmdd': $style=112;
				break;
			case 'yyyy-mm-dd': $style=120;
				break;
		}//eof switch

		return "convert(char(10),".$date.",".$style.")";
	}//eof function

	//datetime format (from)
	public function convertFromDatetime($datetime)
	{
		//style of date
		switch($this->date_format())
		{
			case 'dd/mm/yyyy': $style=103;
				break;
			case 'dd-mm-yyyy': $style=105;
				break;
			case 'mm/dd/yyyy': $style=101;
				break;
			case 'mm-dd-yyyy': $style=110;
				break;
			case 'yyyy/mm/dd': $style=111;
				break;
			case 'yyyymmdd': $style=112;
				break;
			case 'yyyy-mm-dd': $style=120;
				break;
		}//eof switch

		return "convert(char(10),".$datetime.",".$style.")";
	}//eof function

	//date format (to)
	public function convertToDate($date)
	{
		return "convert(date,'".$date."','".$this->date_format()."')";
	}//eof function

	//datetime format (to)
	public function convertToDatetime($datetime)
	{
		return "convert(datetime'".$datetime."','".$this->date_format()."')";
	}//eof function
//===================================== eof date functions =========================================

//============================================ menu ==================================================
	//function to get menu
	public function menu($filter='')
	{
		$sql = "select case when c.MENUTITLE is null then '' else c.MENUTITLE||' / ' end || b.MENUTITLE || ' / ' || a.MENUTITLE as MENUTITLE, a.MENUID, d.PAGEID
					from FLC_PAGE d, FLC_MENU a
							left outer join FLC_MENU b on a.MENUPARENT = b.MENUID
							left outer join FLC_MENU c on b.MENUPARENT = c.MENUID
					where a.MENUID = d.MENUID and a.MENUPARENT != 0
						and upper(case when c.MENUTITLE is null then '' else c.MENUTITLE||' / ' end || b.MENUTITLE || ' / ' || a.MENUTITLE) like upper('%".$filter."%')
					order by upper(case when c.MENUTITLE is null then '' else c.MENUTITLE||' / ' end || b.MENUTITLE || ' / ' || a.MENUTITLE)";
		return $this->dbQuery->query($sql,'SELECT','NAME');
	}//eof function page

	//function to get menu with page excluded
	public function menuExcludePage($page='',$filter='')
	{
		//if modify page
		if($page){$appendWhere = " where PAGEID != ".$page;}		//append where clause

		$sql = "select case when c.MENUTITLE is null then '' else c.MENUTITLE||' / ' end || b.MENUTITLE || ' / ' || a.MENUTITLE as MENUTITLE, a.MENUID
					from FLC_MENU a
						left outer join FLC_MENU b on a.MENUPARENT = b.MENUID
						left outer join FLC_MENU c on b.MENUPARENT = c.MENUID
					where a.MENUID not in (select MENUID from FLC_PAGE ".$appendWhere.")
						and a.MENUID not in (select MENUPARENT from FLC_MENU where MENUPARENT is not null)
						and a.MENUPARENT != 0
						and a.MENULINK like '%page_wrapper%'
						and upper(case when c.MENUTITLE is null then '' else c.MENUTITLE||' / ' end || b.MENUTITLE || ' / ' || a.MENUTITLE) like upper('%".$filter."%')
					order by upper(case when c.MENUTITLE is null then '' else c.MENUTITLE||' / ' end || b.MENUTITLE || ' / ' || a.MENUTITLE)";
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
				case when P.LOOKUP is null or P.LOOKUP='' then a.GROUPCODE else coalesce((select REFERENCECODE from ".$tablename." where REFERENCEID=".$this->convertToNumber('a.GROUPCODE')."),a.GROUPCODE||'*') end as GROUPCODE,
				case when Q.LOOKUP is null or P.LOOKUP='' then a.REFERENCECODE else coalesce((select REFERENCECODE from ".$tablename." where REFERENCEID=".$this->convertToNumber('a.REFERENCECODE')."),a.REFERENCECODE||'*') end as REFERENCECODE,
				case when R.LOOKUP is null or P.LOOKUP='' then a.DESCRIPTION1 else coalesce((select REFERENCECODE from ".$tablename." where REFERENCEID=".$this->convertToNumber('a.DESCRIPTION1')."),a.DESCRIPTION1||'*') end as DESCRIPTION1,
				case when S.LOOKUP is null or P.LOOKUP='' then a.DESCRIPTION2 else coalesce((select REFERENCECODE from ".$tablename." where REFERENCEID=".$this->convertToNumber('a.DESCRIPTION2')."),a.DESCRIPTION2||'*') end as DESCRIPTION2,
				case when T.LOOKUP is null or P.LOOKUP='' then a.PARENTCODE else coalesce((select REFERENCECODE from ".$tablename." where REFERENCEID=".$this->convertToNumber('a.PARENTCODE')."),a.PARENTCODE||'*') end as PARENTCODE,
				case when U.LOOKUP is null or P.LOOKUP='' then a.PARENTROOTCODE else coalesce((select REFERENCECODE from ".$tablename." where REFERENCEID=".$this->convertToNumber('a.PARENTROOTCODE')."),a.PARENTROOTCODE||'*') end as PARENTROOTCODE,
				".$this->convertFromDate('a.TIMESTAMP')." TIMESTAMP,
				(select DESCRIPTION1 from REFSYSTEM where REFERENCECODE=a.REFERENCESTATUSCODE
					and MASTERCODE=(select REFERENCECODE from REFSYSTEM where DESCRIPTION1='REFERENCE_STATUS')) REFERENCESTATUSCODE,
				(select $user_profile_username from $user_profile_schema where $user_profile_userid = ".$this->convertToNumber('a.USERID').") USERID
				from ".$tablename." a,
				(".$this->getLookupTableString('GROUPCODELOOKUPTABLE', $referenceid).") P,
				(".$this->getLookupTableString('CODELOOKUPTABLE', $referenceid).") Q,
				(".$this->getLookupTableString('DESCRIPTION1LOOKUPTABLE', $referenceid).") R,
				(".$this->getLookupTableString('DESCRIPTION2LOOKUPTABLE', $referenceid).") S,
				(".$this->getLookupTableString('PARENTCODELOOKUPTABLE', $referenceid).") T,
				(".$this->getLookupTableString('PARENTROOTCODELOOKUPTABLE', $referenceid).") U
				where MASTERCODE=(select REFERENCECODE from ".$tablename." where REFERENCEID=".$referenceid.")
				".$extra;

		//if have filter
		if($filter)
			$constraintFilter = " where ".$filter;

		$sql="select * from (".$sql.") a".$constraintFilter." order by 2,3,4,5,6,7";

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
			$sql="select 'Internal Key' as 'Internal Key', GROUPCODENAME,CODENAME,DESCRIPTION1NAME, DESCRIPTION2NAME,PARENTCODENAME,PARENTROOTCODENAME,
					'Tarikh' as 'Tarikh', 'Status' as 'Status', 'Oleh' as 'Oleh'
					from SYSREFCONTAINER
					where REFERENCEID=".$referenceid;
			$dataName=$this->dbQuery->query($sql);
			$dataNameCount=count($dataName[0]);
		}//eof if
		//else use dataid
		else
			//data name
			$sql="select 'Internal Key' as 'Internal Key', GROUPCODENAME,CODENAME,DESCRIPTION1NAME, DESCRIPTION2NAME,PARENTCODENAME,PARENTROOTCODENAME,
					'Tarikh' as 'Tarikh', 'Status' as 'Status', 'Oleh' as 'Oleh'
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
					coalesce(a.GROUPCODE,b.GROUPCODEDEFAULTVALUE),
					coalesce(a.REFERENCECODE,b.CODEDEFAULTVALUE,a.REFERENCECODE),
					coalesce(a.DESCRIPTION1,b.DESCRIPTION1DEFAULTVALUE,a.DESCRIPTION1),
					coalesce(a.DESCRIPTION2,b.DESCRIPTION2DEFAULTVALUE,a.DESCRIPTION2),
					coalesce(a.PARENTCODE,b.PARENTCODEDEFAULTVALUE,a.PARENTCODE),
					coalesce(a.PARENTROOTCODE,b.PARENTROOTCODEDEFAULTVALUE,a.PARENTROOTCODE),
					".$this->convertFromDate('a.TIMESTAMP')." TIMESTAMP,
					a.REFERENCESTATUSCODE,
					(select $user_profile_username from $user_profile_schema where $user_profile_userid = ".$this->convertToNumber('a.USERID').") USERID
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
			$sql="select REFERENCEID, REFERENCECODE||' - '||DESCRIPTION1 as DESCRIPTION1 from ".$tablename."
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
				case when b.GROUPCODE='".$groupcode."' then a.GROUPCODENAME else null end,
				case when b.REFERENCECODE='".$referencecode."' then a.CODENAME else null end,
				case when b.DESCRIPTION1='".$description1."' then a.DESCRIPTION1NAME else null end,
				case when b.DESCRIPTION2='".$description2."' then a.DESCRIPTION2NAME else null end,
				case when b.PARENTCODE='".$parentcode."' then a.PARENTCODENAME else null end,
				case when b.PARENTROOTCODE='".$parentrootcode."' then a.PARENTROOTCODENAME else null end
				from (
					select
						case when GROUPCODEUNIQUE is null then GROUPCODENAME end as GROUPCODENAME,
						case when CODEUNIQUE is null then CODENAME end as CODENAME,
						case when DESCRIPTION1UNIQUE is null then DESCRIPTION1NAME end as DESCRIPTION1NAME,
						case when DESCRIPTION2UNIQUE is null then DESCRIPTION2NAME end as DESCRIPTION2NAME,
						case when PARENTCODEUNIQUE is null then PARENTCODENAME end as PARENTCODENAME,
						case when PARENTROOTCODEUNIQUE is null then PARENTROOTCODENAME end as PARENTROOTCODENAME
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
