<?php

function getNodeChild($xml,$parentNode){
	
	$dom = new DOMDocument();
	$dom->load($xml);
	$database = $dom->getElementsByTagName($parentNode);
	
	return $database;
}

function uniqueDBMS(){
	
	$database = getNodeChild(CON_FILE,'database');
	$i=0;
	
	//get dbms name
	foreach ( $database as $db )  
	{
		$dbms_name = $db->getElementsByTagName("dbms_name")->item(0)->nodeValue; 
		$dbmsNames[$i] = $dbms_name;
		$i++;
	}	
	
	//remove duplicate value
	$dbmsName = array_unique($dbmsNames);
	
	return $dbmsName;
}	

function includeClass(){
	
	$dbmsNames = uniqueDBMS();
	
	foreach($dbmsNames as $dbmsName)  
	{	
		if($dbmsName == 'Oracle') 			
			$dbms = 'oracle';
		
		else if($dbmsName == 'MySQL') 		
			$dbms = 'mysql';
		
		else if($dbmsName == 'Sybase') 		
			$dbms = 'sybase';
		
		else if($dbmsName == 'SybaseASE') 	
			$dbms = 'sybase_ASE';
		
		else if($dbmsName == 'MsSQL') 		
			$dbms = 'mssql';
		
		else if($dbmsName == 'PostgreSQL') 	
			$dbms = 'postgresql';
		
		else if($dbmsName == 'SQLServer') 	
			$dbms = 'sqlsrv';
		
		else if($dbmsName == 'Informix') 	
			$dbms = 'informix';
		
		//required libs, classes, and files
		require_once('class/db_'.strtolower($dbms).'.php');		//database class		
	}
	
	include_once('class/dbQueryFactory.php');
	include_once('class/sql_batch.php');
	
	foreach ($dbmsNames as $dbmsName)  
	{		
		if($dbmsName == 'Oracle') 		
			$dbms = 'oracle';
		
		else if($dbmsName == 'MySQL') 		
			$dbms = 'mysql';
		
		else if($dbmsName == 'Sybase') 		
			$dbms = 'sybase';
		
		else if($dbmsName == 'SybaseASE') 	
			$dbms = 'sybase_ASE';
		
		else if($dbmsName == 'MsSQL') 		
			$dbms = 'mssql';
		
		else if($dbmsName == 'PostgreSQL') 
			$dbms = 'postgresql';
		
		else if($dbmsName == 'SQLServer') 	
			$dbms = 'sqlsrv';
		
		else if($dbmsName == 'Informix') 	
			$dbms = 'informix';
		
		require_once('class/sql_'.strtolower($dbms).'.php');		//database class			
	}
}

//define constant to use in class
function defineConstantEngine(){
	
	$database = getNodeChild(CON_FILE,'database');

	//get detail
	foreach($database as $db)  
	{
		$id 			= $db->getAttribute('id'); 
		$engine 		= $db->getAttribute('engine');
		$dbms_name 		= $db->getElementsByTagName('dbms_name')->item(0)->nodeValue; 
		$connection 	= $db->getElementsByTagName('connection')->item(0)->nodeValue; 
		$databasename 	= $db->getElementsByTagName('name')->item(0)->nodeValue; 
		$username 		= $db->getElementsByTagName('username')->item(0)->nodeValue; 
		$password 		= $db->getElementsByTagName('password')->item(0)->nodeValue;
		$server 		= $db->getElementsByTagName('server')->item(0)->nodeValue; 
		$service 		= $db->getElementsByTagName('service')->item(0)->nodeValue; 
		$protocol 		= $db->getElementsByTagName('protocol')->item(0)->nodeValue; 	 	
		
		if($engine == 'yes')
		{
			define('DBMS_NAME',strtolower($dbms_name));
			define('DB_CONNECTION',$connection);			//connection/server
			define('DB_DATABASE',$databasename);			//db name
			define('DB_USERNAME',$username );				//db username
			define('DB_PASSWORD',$password );
			break;
		}
	}
}

function getDatabaseName($idConnection){
	
	$database = getNodeChild(CON_FILE,'database');

	//get detail
	foreach($database as $db)  
	{
		$id = $db->getAttribute('id'); 
		
		if($id == $idConnection)
		{	
			$databasename = $db->getElementsByTagName( "name" )->item(0)->nodeValue; 
			break;
		}
	}
	return $databasename;
}

function getDatabaseDetail($idConnection){
	
	$database = getNodeChild(CON_FILE,'database');

	//get detail
	foreach ( $database as $db )  
	{
		$dbDetail['id'] 		= $db->getAttribute('id'); 
		$dbDetail['engine'] 	= $db->getAttribute('engine');
		$dbDetail['dbms_name'] 	= $db->getElementsByTagName('dbms_name')->item(0)->nodeValue; 
		$dbDetail['connection'] = $db->getElementsByTagName('connection')->item(0)->nodeValue; 
		$dbDetail['name'] 		= $db->getElementsByTagName('name')->item(0)->nodeValue; 
		$dbDetail['username'] 	= $db->getElementsByTagName('username')->item(0)->nodeValue; 
		$dbDetail['password'] 	= $db->getElementsByTagName('password')->item(0)->nodeValue;
		$dbDetail['server'] 	= $db->getElementsByTagName('server')->item(0)->nodeValue; 
		$dbDetail['service'] 	= $db->getElementsByTagName('service')->item(0)->nodeValue; 
		$dbDetail['protocol'] 	= $db->getElementsByTagName('protocol')->item(0)->nodeValue;  
		
		if($dbDetail['id'] == $idConnection)
		{		
			return $dbDetail;
			break;
		}
	}
}

function createConnection($idConnection){
	
	$database = getDatabaseDetail($idConnection);

	$id 			= $database['id']; 
	$engine 		= $database['engine'];
	$dbms_name 		= $database['dbms_name']; 
	$connection 	= $database['connection']; 
	$databasename 	= $database['name']; 
	$username 		= $database['username']; 
	$password 		= $database['password']; 
	$server 		= $database['server']; 
	$service 		= $database['service']; 
	$protocol 		= $database['protocol']; 		
	$connectionName = 'dbConnection'.$dbms_name;
	
	//create database connection
	$myDbConn = new $connectionName;											//create db connection object
	
	if(strtolower($dbms_name) == 'informix')
		$myDbConn->init(
						$database['username'],
						$database['password'],
						$database['name'],
						$database['connection'],
						$database['server'],
						$database['service'],
						$database['protocol']
						);
	else
		$myDbConn->init(
						$database['username'],
						$database['password'],
						$database['name'],
						$database['connection']
						);
						
	$dbc = $myDbConn->getConnString();										//get connection string

	return $dbc;	
}

function databaseQuery($idConnection,$dbc){
	
	$database 		= getDatabaseDetail($idConnection);
	$queryObjName 	= 'dbQuery'.$database['dbms_name'];
	
	//create database query object
	return new $queryObjName($dbc);
}

function databaseSQL($idConnection,$dbc){

	$database 	= getDatabaseDetail($idConnection);
	$sqlObjName = 'dbSQL'.$database['dbms_name'];
	
	//create database sql object
	return new $sqlObjName($dbc,$database['dbms_name']);
}

function isEngine($idConnection)
{
	$database = getNodeChild(CON_FILE,'database');
	
	foreach ( $database as $db )  
	{
		$id = $db->getAttribute('id');
		$engine = $db->getAttribute('engine');
		
		if($id == $idConnection && $engine == 'yes')
			return true;
			
		break;
	}
}
