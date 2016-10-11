<?php 
//--------------------------------------------------------------------------------------------------
// CLASS NAME: MSSQL dbConnection [ COMPLETED ]
// BASE CLASS: -
// DESCRIPTION: To provide connectivity to database and other database connectivity related methods
// METHODS: init, connect, disconnect, selectDb, getConnString
// AUTHOR: CIKKIM
// EDITED: SALEHA
//--------------------------------------------------------------------------------------------------

//class for MsSQL database connectivity
class dbConnectionSQLServer 
{	
	//class member declaration
	private $dbConnection;			//db server address
	private $dbName;				//db name
	private $dbUsername;			//db username
	private $dbPassword;			//db password
	private $dbc;					//db connection string id - visible to child class
	
	//-----------------------------------------
	//[ start class methods declaration block ]
	//-----------------------------------------

	//to initialize connection to db server
	public function init($usr,$pwd,$db,$conn)
	{
		//default values
		$this->dbConnection = $conn;
		$this->dbUsername = $usr;
		$this->dbPassword = $pwd;
		$this->dbName = $db;
		
		//connect to db
		if($this->connect() == true)	
		{	
			//if connected, select db
			//if db selected return true, else return false
			/*if($this->selectDb() == true)
				return true;
			else
				return false;
			*/
		}
	}
	
	//to connect to db, PRIVATE
	private function connect()
	{	
		//if dbconnection and dbusername not null, try connect
		if($this->dbConnection != '')
		{
			$serverName = $this->dbConnection;
			
			//if have username, connect using username/password, else use windows authentication
			if($this->dbUsername)
				$connectionInfo = array("UID" => $this->dbUsername, "PWD" => $this->dbPassword, "Database"=> $this->dbName);
			else
				$connectionInfo = array("Database"=> $this->dbName);
			
			//try connect to database
			$this->dbc = sqlsrv_connect($serverName, $connectionInfo) or sqlErrorLog(getError());
			
			//if not connected, show error msg and return false
			if($this->dbc == FALSE)
			{	
				//echo 'ERROR! Database connection mssql 2008<br>';
				sqlErrorLog(sqlErrorLog(getError()));
				return false;
			}
			
			//if connected, return true
			else
				return true;
		}
		
		//if dbConnection or dbUsername is null
		else
			sqlErrorLog(sqlErrorLog(getError()));
			//echo 'ERROR! Database server address or database username must not be null.<br>';
	}
	
	//to disconnect from db
	public function disconnect()
	{	
		//if connection does not exist
		if($this->dbc == FALSE)
		{
			//echo 'ERROR! Could not disconnect from database because database is not connected';
			sqlErrorLog(sqlErrorLog(getError()));
			return false;
		}
		else
		{	
			//if database is closed successfully
			if(sqlsrv_close($this->dbc) == true)
				return true;
			
			//if database failed to close, return error 
			else
			{
				//echo 'ERROR! Database MSSQL connection could not be disconnected.';
				sqlErrorLog(sqlErrorLog(getError()));
				return false;
			}
		}
	}
	
	//to select working db, PRIVATE
	private function selectDb()
	{	
		//select db
		$selected = @sqlsrv_select_db($this->dbName,$this->dbc);
		
		//if db select fail, show error message, and return false
		if($selected == FALSE)
		{
			//echo 'ERROR! Selected database does not exist.<br>';
			sqlErrorLog(sqlErrorLog(getError()));
			return false;
		}
		
		//else, return true
		else
			return true;
	}
	
	//to return connection string - dbc (publicly called)
	public function getConnString()
	{	
		return $this->dbc;
	}
	
	/*//to return error message
	public function getError()
	{
		//db error
		$error = sqlsrv_errors(SQLSRV_ERR_ERRORS);

		return $error[0]['code'].': '.$error[0]['message'];
	}*/
	
	//-----------------------------------------
	//[ end class methods declaration block   ]
	//-----------------------------------------
}

//--------------------------------------------------------------------------------------------------
// CLASS NAME: myDatabase 
// BASE CLASS: -
// DESCRIPTION: To provide functions for processing common sql queries 
// METHODS: __construct, query, run_sql, bool_sql, count_sql, select_sql
//--------------------------------------------------------------------------------------------------
//class for executing common sql queries
//class myDatabase extends dbConnection
class dbQuerySQLServer 
{	
	//class member declaration
	protected $dbc;
	private $sql;
	private $type;					//RUN, BOOL, COUNT, SELECT
	private $selectReturnType;		//
	
	//-----------------------------------------
	//[ start class methods declaration block ]
	//-----------------------------------------
		
	//to get connection string (php 5 constructor)
	public function __construct($dbc)
	{	
		$this->dbc = $dbc;
	}
	
	//to provide common sql function interface
	//parameter: the sql, type (run/bool/count/select), return type (singlearr/doublearr)
	
	//INDEX / NAME / BOTH (return either single array (index), single array (name), or double array (index and name) in select_sql)
	public function query($sql,$type = 'SELECT',$selectReturnType = 'INDEX')
	{			
		//if type of sql is of SELECT
		if($type == 'SELECT')
		{	
			//choose type of return values			
			//if to return index
			if($selectReturnType == 'INDEX')
				$this->selectReturnType = 'INDEX';
			
			//if to return name
			else if($selectReturnType == 'NAME')
				$this->selectReturnType = 'NAME';
			
			//if to return both index and name in single array
			else if($selectReturnType == 'BOTH_SINGLE')
				$this->selectReturnType = 'BOTH_SINGLE';
			
			//if to return both index and name in separate array
			else if($selectReturnType == 'BOTH_DOUBLE')
				$this->selectReturnType = 'BOTH_DOUBLE';
			
			//if to return both index and keys in separate array
			else if($selectReturnType == 'BOTH_INDEX_KEYS')
				$this->selectReturnType = 'BOTH_INDEX_KEYS';
			
			//else, default to name
			else
				$this->selectReturnType = 'NAME';
		}
		
		//query function type switcher
		switch($type)
		{	
			//switch to related class method
			
			//run type query (insert/update/delete/etc..)
			case 'RUN':
				return $this->run_sql($sql);
				break;
			
			//boolean type query (true/false)
			case 'BOOL':
				return $this->boolean_sql($sql);
				break;
			
			//count type query (return integer. eg: count)
			case 'COUNT':
				return $this->count_sql($sql);
				break;
			
			//select type query (select stmt.)	
			case 'SELECT':	
				return $this->select_sql($sql);
				break;
		}
	}
	
	//FN NAME: run_sql
	//DESC: to run insert, delete, update query
	//RETURN: boolean (true, false)
	private function run_sql($sql)
	{
		return @sqlsrv_query($this->dbc, $sql) or sqlErrorLog(sqlErrorLog(getError()), $sql);	//run query
	}
	
	//FN NAME: boolean_sql
	//DESC: to run boolean result query
	//RETURN: boolean (true, false)
	private function boolean_sql($sql)
	{
		$sqlRs = @sqlsrv_query($this->dbc, $sql) or sqlErrorLog(sqlErrorLog(getError()), $sql);	//run query
		
		//if query success
		if($sqlRs != FALSE)
		{	
			$sqlNumRows = @sqlsrv_num_rows($sqlRs);		//num rows 
			
			//if num rows success
			if($sqlNumRows != FALSE)
			{
				//if theres record, return true
				if($sqlNumRows > 0)
					return TRUE;
				
				//else return false
				else
					return FALSE;
			}
			//else
				//sqlErrorLog(sqlErrorLog(getError()), $sql);
				//die('DATABASE BOOLEAN - NUMROWS QUERY ERROR');
		}
	}
	
	//FN NAME: count_sql
	//DESC: to return count result of query
	//RETURN: integer
	private function count_sql($sql)
	{
		$sqlRs = @sqlsrv_query($this->dbc, $sql) or sqlErrorLog(sqlErrorLog(getError()), $sql);	//run query
		
		//if query success		
		if($sqlRs != FALSE)
		{
			$sqlRsRows = sqlsrv_fetch_array($sqlRs);												//							
			
			//if fetch array success
			if($sqlRsRows != FALSE)
				return (int) $sqlRsRows[0];
			//else
				//die('DATABASE COUNT - FETCH ARRAY QUERY ERROR');
		}
	}
	
	//FN NAME: select_sql
	//DESC: to return result of SELECT query
	//RETURN: array
	private function select_sql($sql)
	{
		$params = array();
		$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

		$sqlRs = @sqlsrv_query($this->dbc, $sql, $params, $options) or sqlErrorLog(sqlErrorLog(getError()), $sql);	//run query
		
		//if query success
		if($sqlRs != FALSE)
		{
			$sqlNumRows = sqlsrv_num_rows($sqlRs);		//num rows 
			
			//if query successs
			if($sqlNumRows != 0)
			{
				$tempArray = array();						//declare temp array
		
				//fetch array
				for($x=0; $x < $sqlNumRows; $x++)
				{
					//copy content to another temp array
					$tempArray[] = sqlsrv_fetch_array($sqlRs);
				}
				
				//if have array
				if($tempArray[0])
				{
					$tempArrayCount = count($tempArray);			//count of array
					$tempArrayKeys = array_keys($tempArray[0]);		//keys of array
					$tempArrayKeysCount = count($tempArrayKeys);	//count of array keys
				}//eof if
				
				//loop on count of array
				for($x=0; $x < $tempArrayCount; $x++)
				{
					//loop on count of array column
					for($y=0; $y<$tempArrayKeysCount;$y++)
					{
						//trim white space in data, sybase save empty string value as ' '
						$tempArray[$x][$tempArrayKeys[$y]] = trim($tempArray[$x][$tempArrayKeys[$y]]);
						
						//for mssql (escape index)
						if($y%2)
						{
							//convert column name to uppercase
							if($tempArrayKeys[$y]==strtoupper($tempArrayKeys[$y])||$tempArrayKeys[$y]==strtolower($tempArrayKeys[$y]))
								$nameArr[$x][strtoupper($tempArrayKeys[$y])] = $tempArray[$x][$tempArrayKeys[$y]];
							//default
							else
								$nameArr[$x][$tempArrayKeys[$y]] = $tempArray[$x][$tempArrayKeys[$y]];
						}//eof else
						else
							$indexArr[$x][$tempArrayKeys[$y]] = $tempArray[$x][$tempArrayKeys[$y]];						
					}//eof for
				}//eof for

				//separate by return value	
				switch($this->selectReturnType)
				{
					//if type index, return index array	
					case 'INDEX': 
						$resultArray = $indexArr;
						break;

					//if type name, return name array
					case 'NAME':
						$resultArray = $nameArr;
						break;
					
					default:
						$resultArray = $tempArray;
						break;
				}//eof switch
				
				sqlsrv_free_stmt ($sqlRs);					//free result memory
				return $resultArray;							//return array 
			}
			//else
		}
	}	

	public function close()
	{
		sqlsrv_close() ; // to close the opened sql
	}
	
	
	//-----------------------------------------
	//[ end class methods declaration block ]
	//-----------------------------------------
}

/*
to return error message
this function currently only enable in sql server as it returns different error results than other db
*/
function getError()
{
	//db error
	$error = sqlsrv_errors(SQLSRV_ERR_ERRORS);

	return $error[0]['code'].': '.$error[0]['message'];
}
?>