<?php
class dbQueryFactory{
	
	public function getObj($dbc,$dbms)
	{	
		$queryObjName = 'dbQuery'.$dbms;
			
		return new $queryObjName($dbc);
	}
}
?>
