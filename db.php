<?php

$database = getNodeChild(CON_FILE,'database');

//create connection & object
foreach ( $database as $db )  
{

	$id 			= $db->getAttribute('id'); 
	$engine 		= $db->getAttribute('engine');
	$dbms_name 		= $db->getElementsByTagName( "dbms_name" )->item(0)->nodeValue; 
	$connection 		= $db->getElementsByTagName( "connection" )->item(0)->nodeValue; 
	$databasename		= $db->getElementsByTagName( "name" )->item(0)->nodeValue; 
	$username 		= $db->getElementsByTagName( "username" )->item(0)->nodeValue; 
	$password 		= $db->getElementsByTagName( "password" )->item(0)->nodeValue; 
	$server 		= $db->getElementsByTagName( "server" )->item(0)->nodeValue; 
	$service 		= $db->getElementsByTagName( "service" )->item(0)->nodeValue; 
	$protocol 		= $db->getElementsByTagName( "protocol" )->item(0)->nodeValue; 	
	
	
	//create connection class name
	$connectionName 	= 'dbConnection'.$dbms_name;
	$queryObjName 		= 'dbQuery'.$dbms_name;
	$sqlObjName 		= 'dbSQL'.$dbms_name;
	$sqlObjBatchName 	= 'dbSQLBatch'.$dbms_name;
	$myquery 		= 'myQuery'.$id;
	
	
	//create connection
	if($engine == 'yes') $id='';
	${'myDbConn'.$id} = new $connectionName;  //create db connection object

	if($dbms_name == 'Informix')
		${'myDbConn'.$id} ->init($username,$password,$databasename,$connection,$server,$service,$protocol);
	else
		${'myDbConn'.$id} ->init($username,$password,$databasename,$connection);

	${'dbc' .$id} = ${'myDbConn'.$id} ->getConnString();	 //get connection string

	//create database query object #myQuery = #dbQuery
	${'myQuery'.$id}= new $queryObjName(${'dbc' .$id});

	//create database sql object #mySQL = #dbSQL
	${'mySQL' .$id}= new $sqlObjName(${'dbc' .$id},$dbms_name);
	
}
?>