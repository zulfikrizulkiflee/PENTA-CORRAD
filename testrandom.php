<?php 
function generateUAPRandomNo()
{
	$digits = 10;
	$str = rand(pow(10, $digits-1), pow(10, $digits)-1);
	$str .= rand(pow(10, $digits-1), pow(10, $digits)-1);
	$str .= rand(pow(10, $digits-1), pow(10, $digits)-1);
	$str .= rand(pow(10, $digits-1), pow(10, $digits)-1);
	
	return $str;
}

for($x=0; $x< 500; $x++)
{
	echo generateUAPRandomNo();
	echo '<br>';
}




?>
