<?php
function getLovNameFromTrigger($trigger)
{
	$lovName = '';
	
	$trgSplit = str_split($trigger);
	$trgSplitCnt = count($trgSplit);
	
	preg_match_all('/Show_Lov[ ]{0,10}[(]{1}[\']{1}[a-zA-Z0-9_]{1,30}[\']{1}[)]{1}/i', $trigger, $matches,PREG_OFFSET_CAPTURE);

	for($x=0; $x < 1; $x++)
	{
		preg_match_all('/[(]{1}[\']{1}[a-zA-Z0-9_]{1,30}[\']{1}[)]{1}/i', $trigger, $matches,PREG_OFFSET_CAPTURE);
		
		if(count($matches[0][0]))
		{
			$lovName = str_replace("('","",$matches[0][0][0]);
			$lovName = str_replace("')","",$lovName);
		}
	}
	
	if($lovName == '')
		return false;
	else
		return $lovName;
}


$trigger = "DECLARE &amp;#10;  show_lov_course BOOLEAN; &amp;#10;  status Varchar2(1);&amp;#10;BEGIN &amp;#10;&#x9;&amp;#10;&#x9;show_lov_course := Show_Lov('CLINIC_TYPE'); &amp;#10;&amp;#10;IF NOT show_lov_course THEN &amp;#10;  Message('You have not selected a value.'); &amp;#10;    RAISE Form_Trigger_Failure;&amp;#10;END IF; &amp;#10;&amp;#10;END;";

$name = getLovNameFromTrigger($trigger);

echo $name;





?>
