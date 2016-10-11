<?php
//configuration file
require_once('conf.php');
//include file DB class & SQl class
require_once('func_conn.php');
includeClass();
defineConstantEngine();

$myQueryArr = array();//array utk simpan dbc selain myQuery

$database = getNodeChild(CON_FILE,'database');



//create connection & object
foreach ( $database as $db )  
{

	$id 			= $db->getAttribute('id'); 
	$engine 		= $db->getAttribute('engine');
	$dbms_name 		= $db->getElementsByTagName( "dbms_name" )->item(0)->nodeValue; 
	$connection 		= $db->getElementsByTagName( "connection" )->item(0)->nodeValue; 
	$databasename 		= $db->getElementsByTagName( "name" )->item(0)->nodeValue; 
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
	
	$myQueryArr['myQuery'.$id] = ${'myQuery' .$id};
	
	
}
	
require_once('func_sys.php');					//system function
require_once('func_common.php');				//common function

//create session object
require_once('class/session.php');
if(!is_object($mySession)&&!isset($_SESSION))
	$mySession = new mySessionMgmt(SESSION_NAME);

//cas server
if(CAS_ENABLED)
{
	//by default, cas usage is enabled
	$casUsageFlag = true;

	//if enable secondary login, check connection first
	if(CAS_SECONDARY_LOGIN_ENABLED)
	{
		//check cas server online or not
		$casURL = 'https://'.CAS_HOSTNAME.':'.CAS_PORT.'/'.CAS_URI;
		$casUsageFlag = verifyUrlConnection($casURL);
	}//eof if

	//if cas usage enabled
	if($casUsageFlag)
	{
		require_once('class/cas.php');

		//if cas object not instintiated yet
		if(!is_object($cas))
			$cas = new CAS(CAS_VERSION,CAS_HOSTNAME,CAS_PORT,CAS_URI);
	}//eof if
}//eof if

//add other variable
$_SESSION['IP_REPORT'] = IP_REPORT;

//set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');
?>
