<?php 	
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

function get_paper_and_orientation()
{
	global $myQuery;
	$sizes        = $myQuery->query('select REFERENCECODE as "value", DESCRIPTION1 as "name", DESCRIPTION2 as "checked" from REFSYSTEM where MASTERCODE=\'30013\' order by DESCRIPTION1 asc','SELECT','NAME');
	$orientations = $myQuery->query('select REFERENCECODE as "value", DESCRIPTION1 as "name", DESCRIPTION2 as "checked" from REFSYSTEM where MASTERCODE=\'30014\' order by DESCRIPTION1 asc','SELECT','NAME');

	$data['sizes'] = $sizes;
	$data['orientations'] = $orientations;

	return $data;
}

//return to js
echo json_encode(get_paper_and_orientation());

?>
