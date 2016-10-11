<?php
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');

//json data collection
$jsonData = json_decode(json_decode(file_get_contents('php://input')));
$postData = $jsonData[0]->postval;
$getData = $jsonData[1]->getval;

for($x=0; $x < count($postData); $x++)
	$_POST[$postData[$x]->name] = $postData[$x]->value;

for($x=0; $x < count($getData); $x++)
	$_GET[$getData[$x]->name] = $getData[$x]->value;

//have item
if($_GET['item'])
{
	//get item id
	$_GET['item'] = str_replace('[]', '',$_GET['item']);

	//get item query
	$sql = "select a.*
				from FLC_PAGE_COMPONENT_ITEMS a, FLC_PAGE_COMPONENT b, FLC_PAGE c
				where a.COMPONENTID = b.COMPONENTID and b.PAGEID = c.PAGEID
					and c.MENUID = ".$_GET['menuID']." and a.ITEMNAME = '".$_GET['item']."'";
	$sqlRs = $myQuery->query($sql,'SELECT','NAME');

	$sql = $sqlRs[0]['ITEMLOOKUP'];
	$lookupDB = $sqlRs[0]['ITEMLOOKUPDB'];

	//and val=".$_GET['val'];
	$sql = str_replace('\\','',convertDBSafeToQuery($sql));
	$sql = str_replace('"',"'",$sql);
	$sql = str_replace('&quot;',"'",$sql);

	$where = '';

	//if sql is empty (no {POST|whatever}
	if($sql == '')
	{
		//check if theres sql source from post data 
		if(isset($_POST[$sqlRs[0]['ITEMNAME'].'_source']))
			$sql = flc_url_decode($_POST[$sqlRs[0]['ITEMNAME'].'_source']);
	}

	if(isset($_GET['selector']))
	{
		if($_GET['colval'] == 'flc_null')
		{
			$where = " where 1 < 0";
		}
		else
		{
			if($_GET['selector'] == 'where')
				$where = " where ".$_GET['col']." = '".$_GET['colval']."'";
			else if($_GET['selector'] == 'like')
				$where = " where ".$_GET['col']." like '".$_GET['colval']."%' ";
		}
	}
	else
		$where = " where ".$_GET['col']." like '".$_GET['colval']."%'";

	//run the itemlookup query
	$run = "select * from (".$sql.") a ".str_replace("\\",'',$where);
	$run .= " order by FLC_NAME";
	$runRs = $myQueryArr['myQuery'.$lookupDB]->query($run,'SELECT','NAME');
	$runRsCount = count($runRs);

	//========= JAVASCRIPT ===================
	//js trigger
	$getJsTrigger = getJsTrigger('item', $sql = $sqlRs[0]['ITEMID']);

	//javascript, append js trigger with pre-set js
	if($onblur || $getJsTrigger['onblur']) 				$js .= ' onblur="'.$onblur.$getJsTrigger['onblur'].'"';
	if($onchange || $getJsTrigger['onchange']) 			$js .= ' onchange="'.$onchange.$getJsTrigger['onchange'].'"';
	if($onclick || $getJsTrigger['onclick']) 			$js .= ' onclick="'.$onclick.$getJsTrigger['onclick'].'"';
	if($ondblclick || $getJsTrigger['ondblclick']) 		$js .= ' ondblclick="'.$ondblclick.$getJsTrigger['ondblclick'].'"';
	if($onfocus || $getJsTrigger['onfocus']) 			$js .= ' onfocus="'.$onfocus.$getJsTrigger['onfocus'].'"';
	if($onkeydown || $getJsTrigger['onkeydown']) 		$js .= ' onkeydown="'.$onkeydown.$getJsTrigger['onkeydown'].'"';
	if($onkeypress || $getJsTrigger['onkeypress']) 		$js .= ' onkeypress="'.$onkeypress.$getJsTrigger['onkeypress'].'"';
	if($onkeyup || $getJsTrigger['onkeyup']) 			$js .= ' onkeyup="'.$onkeyup.$getJsTrigger['onkeyup'].'"';
	if($onmousedown || $getJsTrigger['onmousedown']) 	$js .= ' onmousedown="'.$onmousedown.$getJsTrigger['onmousedown'].'"';
	if($onmousemove || $getJsTrigger['onmousemove']) 	$js .= ' onmousemove="'.$onmousemove.$getJsTrigger['onmousemove'].'"';
	if($onmouseout || $getJsTrigger['onmouseout']) 		$js .= ' onmouseout="'.$onmouseout.$getJsTrigger['onmouseout'].'"';
	if($onmouseover || $getJsTrigger['onmouseover']) 	$js .= ' onmouseover="'.$onmouseover.$getJsTrigger['onmouseover'].'"';
	if($onmouseup || $getJsTrigger['onmouseup']) 		$js .= ' onmouseup="'.$onmouseup.$getJsTrigger['onmouseup'].'"';
	if($onselect || $getJsTrigger['onselect']) 			$js .= ' onselect="'.$onselect.$getJsTrigger['onselect'].'"';
	//========= EOF JAVASCRIPT ===============
}

//for tabular
if(isset($_GET['index']))
{
	$index = '_'.$_GET['index'];
	$name = $_GET['item'].'[]';
}
else
{
	$index = '';
	$name = $_GET['item'];
}
?>
<select name="<?php echo $name;?>" id="<?php echo $_GET['item'].$index;?>" <?php echo $js;?> class="inputList">
<option value="">&nbsp;</option>
<?php for($x=0; $x<$runRsCount; $x++){?>
<option value="<?php echo $runRs[$x]['FLC_ID'];?>"><?php echo $runRs[$x]['FLC_NAME'];?></option>
<?php }?>
</select>
