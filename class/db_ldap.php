<?php 
//--------------------------------------------------------------------------------------------------
// CLASS NAME: MYSQL dbConnection [ COMPLETED ]
// BASE CLASS: -
// DESCRIPTION: To provide connectivity to database and other database connectivity related methods
// METHODS: init, connect, disconnect, selectDb, getConnString
// COPYRIGHT: ESRA TECHNOLOGY SDN BHD
// AUTHOR: CIKKIM
// EDITED: FAIS
//--------------------------------------------------------------------------------------------------

//USAGE SYNTAX
//$myDbConnection = new dbConnection;												//create new connection object
//$myDbConnection->init("localhost","root","xxx","mpk");							//init connection 
//$myDbConnection->disconnect();													//disconnect
//$dbc = $myDbConnection->getConnString();											//get connection string

//class for MySQL database connectivity
class dbConnection 
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
			if($this->selectDb() == true)
				return true;
			else
				return false;					
		}
	}
	
	//to connect to db, PRIVATE
	private function connect()
	{	
		//if dbconnection and dbusername not null, try connect
		if($this->dbConnection != '' && $this->dbUsername != '')
		{	
			//try connect to database
			$this->dbc = @mysql_connect($this->dbConnection,$this->dbUsername,$this->dbPassword);
			
			//if not connected, show error msg and return false
			if($this->dbc == FALSE)
			{	
				echo 'ERROR! Database connection error.<br>';
				return false;
			}
			
			//if connected, return true
			else
				return true;
		}
		
		//if dbConnection or dbUsername is null
		else
			echo 'ERROR! Database server address or database username must not be null.<br>';
	}
	
	//to disconnect from db
	public function disconnect()
	{	
		//if connection does not exist
		if($this->dbc == FALSE)
		{
			echo 'ERROR! Could not disconnect from database because database is not connected';
			return false;
		}
		else
		{	
			//if database is closed successfully
			if(mysql_close($this->dbc) == true)
				return true;
			
			//if database failed to close, return error 
			else
			{
				echo 'ERROR! Database connection could not be disconnected.';
				return false;
			}
		}
	}
	
	//to select working db, PRIVATE
	private function selectDb()
	{	
		//select db
		$selected = @mysql_select_db($this->dbName,$this->dbc);
		
		//if db select fail, show error message, and return false
		if($selected == FALSE)
		{
			echo 'ERROR! Selected database does not exist.<br>';
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
	
	//-----------------------------------------
	//[ end class methods declaration block   ]
	//-----------------------------------------
}

//--------------------------------------------------------------------------------------------------
// CLASS NAME: myDatabase 
// BASE CLASS: -
// DESCRIPTION: To provide functions for processing common sql queries 
// METHODS: __construct, query, run_sql, bool_sql, count_sql, select_sql
// COPYRIGHT: ESRA TECHNOLOGY SDN BHD
//--------------------------------------------------------------------------------------------------
//class for executing common sql queries
//class myDatabase extends dbConnection
class dbQuery 
{	
	//class member declaration
	private $dbc;
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
		return @mysql_query($sql,$this->dbc) or die('DATABASE INSERT, UPDATE, DELETE QUERY ERROR: '.mysql_errno().':'.mysql_error().'<br>'.$sql);	//run query
	}
	
	//FN NAME: boolean_sql
	//DESC: to run boolean result query
	//RETURN: boolean (true, false)
	private function boolean_sql($sql)
	{
		$sqlRs = @mysql_query($sql,$this->dbc) or die('DATABASE BOOLEAN QUERY ERROR: '.mysql_error());	//run query
		
		//if query success
		if($sqlRs != FALSE)
		{	
			$sqlNumRows = @mysql_num_rows($sqlRs);		//num rows 
			
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
			else
				die('DATABASE BOOLEAN - NUMROWS QUERY ERROR'.mysql_error());
		}
	}
	
	//FN NAME: count_sql
	//DESC: to return count result of query
	//RETURN: integer
	private function count_sql($sql)
	{
		$sqlRs = @mysql_query($sql,$this->dbc) or die('DATABASE COUNT QUERY ERROR: '.mysql_error());	//run query
		
		//if query success		
		if($sqlRs != FALSE)
		{
			$sqlRsRows = mysql_fetch_array($sqlRs,MYSQL_NUM);												//							
			
			//if fetch array success
			if($sqlRsRows != FALSE)
				return (int) $sqlRsRows[0];
			else
				die('DATABASE COUNT - FETCH ARRAY QUERY ERROR'.mysql_error());
		}
	}
	
	//FN NAME: select_sql
	//DESC: to return result of SELECT query
	//RETURN: array
	private function select_sql($sql)
	{	
		$sqlRs = @mysql_query($sql,$this->dbc) or die('DATABASE SELECT QUERY ERROR: '.mysql_errno().':'.mysql_error().'<br>'.$sql);	//run query
		
		//if query success
		if($sqlRs != FALSE)
		{	
			$sqlNumRows = mysql_num_rows($sqlRs);		//num rows 
			
			//if query successs
			if($sqlNumRows != 0)
			{
				$tempArray = array();						//declare temp array
		
				//fetch array
				for($x=0; $x < $sqlNumRows; $x++)
				{
					//copy content to another temp array 
					$tempArray[] = mysql_fetch_array($sqlRs);
				}
				
				$tempArrayCount = count($tempArray);			//count of array
				$tempArrayKeys = array_keys($tempArray[0]);		//keys of array
				$tempArrayKeysCount = count($tempArrayKeys);	//count of array keys

				//loop on count of array
				for($x=0; $x < $tempArrayCount; $x++)
				{
					//loop on count of array column
					for($y=0; $y<$tempArrayKeysCount;$y++)
					{
						//for mysql (escape index)
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
				
				mysql_free_result($sqlRs);					//free result memory
				return $resultArray;							//return array 
			}
			//else
				//die('DATABASE SELECT QUERY - NUMROWS ERROR'.mysql_error());
		}
	}	

	//-----------------------------------------
	//[ end class methods declaration block ]
	//-----------------------------------------
}

?>