<?php 
//todo - cips officecentral

require_once('system_prerequisite.php');

//validate that user have session
validateUserSession();

//func_sys.php
//createXMLFromQry($myQuery,"select * from pruser",'PRUSER');

//TODO - FOR CIPS
//-------
//PAYMENT
//-------
$qry = "select 
			'' as Transact_No,
			'' as TransactType,
			'' as TransactDate,
			'' as Receipt_No,
			'' as Amount,
			'' as Credit_AccCode,
			'' as Cheque_No,
			'' as Debit_AccCode
		from dual";
createXMLFromQry($myQuery,$qry,'Transaction');

//---------------
//VOUCHER REQUEST
//---------------
$qry = "select 
			'' as TransactDate,
			'' as Invoice_No,
			'' as Amount,
			'' as AccCode
		from dual";
//createXMLFromQry($myQuery,$qry,'PaymentRequisition');

//-------
//JOURNAL
//-------
$qry = "select 
			'' as Transact_No,
			'' as TransactType,
			'' as TransactDate,
			'' as Amount,
			'' as AccCode
		from dual";
//createXMLFromQry($myQuery,$qry,'Transaction');

?>


