<?php
//--------------------------------------------------------------------------------------------------
// CLASS NAME: Informix dbConnection [ COMPLETED ]
// BASE CLASS: -
// DESCRIPTION: To provide connectivity to oracle and other database connectivity related methods
// METHODS: init, connect, disconnect, selectDb, getConnString
// COPYRIGHT: ENCORAL DIGITAL SOLUTIONS SDN BHD
// AUTHOR: CIKKIM
// EDITED: SALEHA
//--------------------------------------------------------------------------------------------------

//USAGE SYNTAX
//$myDbConnection = new dbConnection;												//create new connection object
//$myDbConnection->init("root","xxx","mpk");										//init connection 
//$myDbConnection->disconnect();													//disconnect
//$dbc = $myDbConnection->getConnString();											//get connection string

/*
putenv("INFORMIXDIR=/opt/IBM/informix2");
putenv("LD_LIBRARY_PATH=/opt/IBM/informix2/lib/:/opt/IBM/informix2/lib/cli:/opt/IBM/informix2/lib/esql");
putenv("PATH=/usr/local/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/home/nikfarid/.local/bin:/home/nikfarid/bin:/opt/IBM/informix2/bin:/opt/IBM/informix2/lib");
*/


//class for Oracle database connectivity

class dbConnectionInformix 
{	
	//class member declaration
	private $dbConnection;			//db server address
	private $dbName;			//db name
	private $dbUsername;			//db username
	private $dbPassword;			//db password
	private $server;			//ifx db server
	private $service;			//ifx service
	private $protocol;			//ifx protocol
	private $dbc;				//db connection string id - visible to child class
	

	//-----------------------------------------
	//[ start class methods declaration block ]
	//-----------------------------------------

	//to initialize connection to db server
	public function init($usr,$pwd,$db,$conn,$server,$service,$protocol)
	{	
		//default values
		$this->dbConnection = $conn;
		$this->dbUsername = $usr;
		$this->dbPassword = $pwd;
		$this->dbName = $db;
		$this->server = $server;
		$this->service =  $service;
		$this->protocol = $protocol;		
		
		//connect to db
		if($this->connect() == true)	
			return true;				
	}
	
	//to connect to db, PRIVATE
	private function connect()
	{	
		//if dbconnection and dbusername not null, try connect
		//if($this->dbConnection != '' && $this->dbUsername != '')
		if( $this->dbUsername != '')
		{	

			//try connect to database
			$this->dbc = new PDO("informix:host=".$this->dbConnection."; service=".$this->service."; database=".$this->dbName."; server=".$this->server.";protocol=".$this->protocol."; EnableScrollableCursors=1;",
					$this->dbUsername, $this->dbPassword);
			$this->dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbc->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->dbc->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
			$this->dbc->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

			//if not connected, show error msg and return false
			if($this->dbc == FALSE)
			{	
				//echo 'ERROR! Database connection error.<br>';
				$this->dbc->errorInfo();
				return false;
			}
			
			//if connected, return true
			else
			{	//echo 'Database is Connect on Oracle Database';
				return true;
			}
		}
		
		//if dbConnection or dbUsername is null
		else
			$this->dbc->errorInfo();
			//echo 'ERROR! Database server address or database username must not be null.<br>';

	}
	
	//to disconnect from db
	public function disconnect()
	{	
		//if connection does not exist
		if($this->dbc == FALSE)
		{
			$this->dbc->errorInfo();
			//echo 'ERROR! Could not disconnect from database because database is not connected';
			return false;
		}
		else
		{	
			
			try
			{
				$this->dbc == null;
			}			
			catch(PDOException $e)
			{
				echo $e->getMessage();
			}			
			
			//if database is closed successfully
			if($this->dbc == null)
				return true;
			
			//if database failed to close, return error 
			else
			{
				//sqlErrorLog(oci_error());
				//echo 'ERROR! Database connection could not be disconnected.';
				return false;
			}
		}
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
// CLASS NAME: myDatabase [ COMPLETED ] 
// BASE CLASS: -
// DESCRIPTION: To provide functions for processing common sql queries 
// METHODS: __construct, query, run_sql, bool_sql, count_sql, select_sql
// COPYRIGHT: ESRA TECHNOLOGY SDN BHD
// AUTHOR: CIKKIM
//--------------------------------------------------------------------------------------------------
//class for executing common sql queries
//class myDatabase extends dbConnection
class dbQueryInformix 
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
		//assign connection string to private var	
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
			
			//run type query (insert/update/delete/etc..)
			case 'RUNUP':
				return $this->runup_sql($sql);
				break;

			//run type query (insert/update/delete/etc..)
			case 'RUNTEXT':
				return $this->runtext_sql($sql);
				break;
		}
	}
	
	//FN NAME: run_sql
	//DESC: to run insert, delete, update query
	//RETURN: boolean (true, false)
	private function run_sql($sql)
	{	
		
		//parse sql statement
		$sqlParsed = $this->dbc->exec($sql);		
					
		//if query success
		if($sqlParsed == TRUE)
		{
			//commit transaction and return boolean 
			//return oci_commit($this->dbc);
			return true;
		}	
			
	}

	
	//FN NAME: run1_sql
	//DESC: to run1 insert, delete, update query
	//RETURN: boolean (true, false)
	private function runup_sql($sql)
	{	
		/*echo "<pre>";
		print_r($sql[1]);
		echo "</pre>";*/

		$sql = explode("####",$sql);
		//$unlimitedChar = str_replace("''", "'", $sql[0]);
		$unlimitedChar = $sql[0];
		
		//print_r($sql[1]);
		if(trim($unlimitedChar))
		{
			//parse sql statement
			$sqlParsed = $this->dbc->prepare($sql[1]);
			$sqlParsed->bindParam( ':unlimitedChar' , $unlimitedChar , PDO::PARAM_LOB ,strlen($unlimitedChar));
			$sqlParsed->execute();	
			
		echo "<pre>";
		print_r($sql[1]);
		echo "</pre>";
		
			//if query success
			if($sqlParsed == TRUE)
			{
				//commit transaction and return boolean 
				//return oci_commit($this->dbc);
				return true;
			}	
		} // eof if($unlimitedChar)
			
	}


	//FN NAME: run1_sql
	//DESC: to run1 insert, delete, update query
	//RETURN: boolean (true, false)
	private function runtext_sql($sql)
	{	
		$sql = explode("####",$sql);
		$unlimitedChar = $sql[0];
		
		//print_r($sql[1]);
		if(trim($unlimitedChar))
		{
			//parse sql statement
			$sqlParsed = $this->dbc->prepare($sql[1]);
			$sqlParsed->bindParam( ':unlimitedChar' , $unlimitedChar , PDO::PARAM_LOB ,strlen($unlimitedChar));
			$sqlParsed->execute();	

		
			//if query success
			if($sqlParsed == TRUE)
			{
				//commit transaction and return boolean 
				//return oci_commit($this->dbc);
				return true;
			}	
		} // eof if($unlimitedChar)
			
	}
	

	//FN NAME: boolean_sql
	//DESC: to run boolean result query
	//RETURN: boolean (true, false)
	private function boolean_sql($sql)
	{   
		//parse sql statement
		$sqlParsed = $this->dbc->query($sql);		
		//$sqlParsed = oci_parse($this->dbc,$sql) or sqlErrorLog(oci_error(), $sql);
		//$sqlParsed = oci_parse($this->dbc,$sql) or die('DATABASE BOOLEAN QUERY ERROR: '.oci_error());	
		
		//execute parsed sql statement
		$successFlag = $sqlParsed->execute();
		
		//if query success
		if($successFlag == TRUE)
		{	
			//fetch all rows into array
			$sqlNumRows = oci_fetch_all($sqlParsed,$sqlRsArr);					

			//if theres record, return true
			if($sqlNumRows > 0)
				return TRUE;
			   
			//else return false
			else
				return FALSE;
		}
		
		//if query fail, show sql
		else
		{
			$this->dbc->errorInfo();
			//echo '<code>'.$sql.'</code>';
		}
	}
	
	//FN NAME: count_sql
	//DESC: to return count result of query
	//RETURN: integer
	private function count_sql($sql)
	{
		//parse sql statement
		$sqlParsed = $this->dbc->query($sql);		

		
		//execute parsed sql statement
		//$sqlParsed->execute();
		
		//if query success		
		if($sqlParsed != FALSE)
		{
			//fetch all array into array
			$sqlRsRows = $sqlParsed->fetchColumn();
						
			//return 
			return (int)$sqlRsRows;
		}
		
		//if query fail, show sql
		else 
		{	
			$this->dbc->errorInfo();
			//echo '<code>'.$sql.'</code>';
		}
	}


	
	//FN NAME: select_sql
	//DESC: to return result of SELECT query
	//RETURN: array
	private function select_sql($sql)
	{	
		//parse sql statement
		$sqlParsed = $this->dbc->query($sql);
		 
		//execute parsed sql statement
		$successFlag = $sqlParsed->execute();		
		
		//if query success
		if($successFlag != FALSE)
		{
			//fetch all rows into array
			$sqlRsArr = $sqlParsed->fetchAll(PDO::FETCH_BOTH);

			$sqlNumRows = count($sqlRsArr);
			
			$keysList = array_keys($sqlRsArr);
					

			//if num rows more than zero
			//if($sqlNumRows != FALSE)				//original
			if($sqlNumRows > 0)
			{
								
				//convert from column based to row based
				for($x=0; $x < $sqlNumRows; $x++)
				{
	
					foreach($sqlRsArr[$x] as $key => $value)
					{	/*echo '<pre>';
						print_r($key);
						echo '<pre>';*/

						if(is_int($key) == 'TRUE' )
						{
							//copy to temp array, use field name as key
							$indexArr[(int)$x][$key] = ($value);
						}
						else
						{
							//copy to temp array, use field name as key
							$nameArr[$x][$key] = ($value);
						}

					}
			
				}//end for x

				
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
						$resultArray = $indexArr;
						break;
				}//eof switch
				//return temp array

				return $resultArray;
				
			}
			$successFlag = null;

		}
		
		//if query fail, show sql
		else
		{
			$this->dbc->errorInfo();
			//echo '<code>'.$sql.'</code>';
		}
			
	}
	


	//-----------------------------------------
	//[ end class methods declaration block ]
	//-----------------------------------------
}
?>
