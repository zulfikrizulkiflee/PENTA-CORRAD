<div id="<?php echo $itemArr['ITEMNAME'];?>">

<?php
// paramter setup for this file
//$CalanderTitle="Kalendar";  		
$CalanderTitle = $itemArr['ITEMTITLE'];  		
$CalanderFooter = "&nbsp;"; 								 

$calanderOffDay="Sunday,Saturday";   // add more separate with ,if null will take from page component  (Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday)
$calanderDayStart=6;   // Will take from page component if set. 0=Monday, 1=Tuesday, 2=Wednesday, 3=Thursday, 4=Friday, 5=Saturday, 6=Sunday
$calanderLanguage="BI" ;  // Will take from page component if set. (BM or BI)
$titlewidth=0; // 0 for unlimited title width
$calanderPopWidth=400; // to define width popwindows when click at the title
$calanderPopHeight=300; // to define height popwindows when click at the title

$calanderMonth=$_GET["month"]; 	// can pass through url
$calanderYear=$_GET["year"];	// can pass through url

$currentYear=date('Y');			// get the current year
$currentMonth=date('m');		// get the current month
$currentMonth2=date('F');		// get the current month in name format
$currentDay=date('d');			// get the current day

if ( $calanderMonth =="" )  {
	$calanderMonth = $currentMonth;
	$calanderYear = $currentYear;
}

if ($_GET["calWidth"] == "" ) $calanderCellWidth=30;  else $calanderCellWidth=$_GET["calWidth"] ;
if ($_GET["calHeigth"] == "" ) $calanderCellHeigth=50; else $calanderCellHeigth=$_GET["calHeigth"] ;
if ($_GET["format"] == "" ) $calFormat="month";  else $calFormat=$_GET["format"] ;


$pageURL="";
$paramNum=0; 
//get all param url and remove month & year from param 
while ($getParam = current($_GET)) {
	if(key($_GET) != 'month' &&  key($_GET) != 'year') {
		if($paramNum==0) {
			$pageURL = "index.php?" . key($_GET) .'='. $getParam;
		} else {
			$pageURL .= "&" . key($_GET) .'='. $getParam;
		}
		$paramNum++;
	}
	next($_GET);
}
//echo $pageURL;

$SQL_TYPE = 1;   // 1 : with column have "FLC_DATE_FROM" and "FLC_DATE_TO" ,   0 : with column have "FCL_DATE"

//This sql is and example for SQL_TYPE=1 with date from and to
$SQL1 = "
select 
title_1 FLC_HINTS,
date_format(date_from,'%Y%m') FLC_YEARMONTH_FROM,  
date_format(date_to,'%Y%m') FLC_YEARMONTH_TO,
date_format(date_from,'%Y%m%d') FLC_DATE_FROM,
date_format(date_to,'%Y%m%d') FLC_DATE_TO,
url FLC_URL
from maca.flc_cms_content_detail where content_id in (45,40) and status_code=1
";


$SQL1 = "
select 
'Hari Raya Aidilfitri' FLC_HINTS,
date_format(STR_TO_DATE('08-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_FROM,  
date_format(STR_TO_DATE('12-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_TO,
date_format(STR_TO_DATE('08-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_FROM,
date_format(STR_TO_DATE('12-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_TO,
'http://google.com' FLC_URL
from dual
union
select 
'Code Spring' FLC_HINTS,
date_format(STR_TO_DATE('19-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_FROM,  
date_format(STR_TO_DATE('20-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_TO,
date_format(STR_TO_DATE('19-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_FROM,
date_format(STR_TO_DATE('20-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_TO,
'' FLC_URL
from dual
union
select 
'Rumah Terbuka Kak Ani' FLC_HINTS,
date_format(STR_TO_DATE('20-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_FROM,  
date_format(STR_TO_DATE('20-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_TO,
date_format(STR_TO_DATE('20-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_FROM,
date_format(STR_TO_DATE('20-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_TO,
'http://mail.google.com' FLC_URL
from dual
union
select 
'Hari Raya di Esra' FLC_HINTS,
date_format(STR_TO_DATE('23-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_FROM,  
date_format(STR_TO_DATE('23-08-2013','%d-%m-%Y'),'%Y%m') FLC_YEARMONTH_TO,
date_format(STR_TO_DATE('23-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_FROM,
date_format(STR_TO_DATE('23-08-2013','%d-%m-%Y'),'%Y%m%d') FLC_DATE_TO,
'index.php' FLC_URL
from dual
";


//This sql is and example for SQL_TYPE=0 with date 
$SQL0 = "";

//$SQL = $componentArr[$x]['COMPONENTTYPEQUERY'];
$SQL = convertDBSafeToQuery($itemArr['ITEMLOOKUP']);
//$SQL = $SQL1;

// Must put this additional SQL for query for calanderYear and  calanderMonth declare
if ($SQL_TYPE)  $SQL = "select * from (" . $SQL . " ) flc_calander where '" . $calanderYear . $calanderMonth . "' between FLC_YEARMONTH_FROM and FLC_YEARMONTH_TO" ;
else $SQL = "select * from (" . $SQL . " ) flc_calander where '" . $calanderYear .  $calanderMonth . "' = FLC_YEARMONTH" ;


$calDay[0]="Monday";
$calDay[1]="Tuesday";
$calDay[2]="Wednesday";
$calDay[3]="Thursday";
$calDay[4]="Friday";
$calDay[5]="Saturday";
$calDay[6]="Sunday";

//$calDay=array('0'=>"SUN",'1'=>"MON",'2'=>"TUE",'3'=>"WED",'4'=>"THU",'5'=>"FRI",'6'=>"SAT");


$PreviousMonthMth = date ( 'm' , strtotime ( '-1 month' , strtotime ( $calanderYear . "-" . $calanderMonth . "-01" ) ) );
$PreviousMonthYr = date ( 'Y' , strtotime ( '-1 month' , strtotime ( $calanderYear . "-" . $calanderMonth . "-01" ) ) );

$PreviousYearMth = $calanderMonth ;
$PreviousYearYr = date ( 'Y' , strtotime ( '-1 month' , strtotime ( $calanderYear . "-" . $calanderMonth . "-01" ) ) ) - 1;

$NextMonthMth = date ( 'm' , strtotime ( '+1 month' , strtotime ( $calanderYear . "-" . $calanderMonth . "-01" ) ) );
$NextMonthYr = date ( 'Y' , strtotime ( '+1 month' , strtotime ( $calanderYear . "-" . $calanderMonth . "-01" ) ) );

$NextYearMth = $calanderMonth ;
$NextYearYr = date ( 'Y' , strtotime ( '+1 month' , strtotime ( $calanderYear . "-" . $calanderMonth . "-01" ) ) ) + 1;


$monthLabel = "Month";
$yearLabel = "Year";
$heightLabel = "Height";
$widthLabel = "Witdh";
if ( $calanderLanguage == "BM" ) {
	$monthLabel = "Bulan";
	$yearLabel = "Tahun";
	$heightLabel = "Tinggi";
	$widthLabel = "Lebar";
}

$calanderMonthDisplay = date('F', strtotime( $calanderMonth . "/01/" . $calanderYear . " 00:00:00") );
$calanderdisplay=mktime(0,0,0,'01',$calanderMonth,$calanderYear);
$calanderFristDay = date("l",strtotime( $calanderMonth . "/01/" . $calanderYear . " 00:00:00") );
$calanderLastDay = date("d", strtotime('-1 second',strtotime('+1 month',strtotime( $calanderMonth . "/01/" . $calanderYear . " 00:00:00") )));

$sqlRS =  $myQuery->query($SQL,'SELECT','NAME');
?>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent">
	<tr>
		<th>
		<?php 
			//translation for component
			$tr8nStr = getElemTranslation($myQuery,$_SESSION['language'],4,$itemArr['ITEMID'],'ITEMTITLE');
			($tr8nStr[0]['TRANS_SOURCE_COLUMN'] == 'ITEMTITLE') ? $compTitle = $tr8nStr[0]['TRANS_TEXT'] : $compTitle = $itemArr['ITEMTITLE'];
			echo $compTitle;
			echo ' [ '.$calanderMonthDisplay . ' '. $calanderYear.' ]'; 
		?>
		</th>
	
	<tr style="border:none;<?php echo $compCollapseHTML; ?>">
	<td style="margin:0px; padding:0px;border:none">
		
		
		
		<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5" class="calMonthYear">
		  <tr>
			<td align="Left" style="border:none">
				<?php echo $monthLabel; ?> : 
				<?php
					$bulanNames = Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
					$bulan=array("01","02","03","04","05","06","07","08","09","10","11","12");		
					echo '<select name="CalMonth" id="CalMonth" class="inputInput" style="background-color:white">';
					for ($b=0;$b<count($bulan); $b++) {
						echo '<option value="'.$bulan[$b].'" ';
						if ($bulan[$b] == $calanderMonth) { echo 'selected="selected"'; }
						echo ' >'.$bulanNames[$b].'</option>';
					}
					echo '</select>';
				?>

				<?php echo $yearLabel; ?> : 
				<input name="CalYear" id="CalYear" type="text" MaxLength="4" style="width:35px;text-align:left;" class="inputInput" value="<?php echo $calanderYear; ?>" size="4" /> 
				<span class="calPointerGo" onclick="window.location = '<?php echo $pageURL; ?>&month='+document.getElementById('CalMonth').value  +'&year='+ document.getElementById('CalYear').value ;" >
				GO </span>
			</td>
			<td align="Right"  style="border:none">
			
				<a class="calPointer" title="Previous Year Month" href="<?php echo $pageURL; ?>&month=<?php echo $PreviousYearMth; ?>&year=<?php echo $PreviousYearYr; ?>" > << </a> &nbsp;
				<a class="calPointer" title="Previous Month" href="<?php echo $pageURL; ?>&month=<?php echo $PreviousMonthMth; ?>&year=<?php echo $PreviousMonthYr; ?>" > < </a> &nbsp;
				<a class="calPointer" title="<?php echo $currentDay.' '.$currentMonth2.' '.$currentYear; ?>" href="<?php echo $pageURL; ?>" > Today </a> &nbsp;
				<a class="calPointer" title="Next Year Month" href="<?php echo $pageURL; ?>&month=<?php echo $NextMonthMth; ?>&year=<?php echo $NextMonthYr; ?>" > > </a> &nbsp;
				<a class="calPointer" title="Next Month" href="<?php echo $pageURL; ?>&month=<?php echo $NextYearMth; ?>&year=<?php echo $NextYearYr; ?>" > >> </a> &nbsp;
				
			</td>
		  </tr>
		</table>

		<table align="center" width="100%" border="0" cellspacing="0" cellpadding="2" class="calBodyContainer">
		  <tr>
			<?php 
				$y=1;
				for($p=$calanderDayStart ; $p <=($calanderDayStart + 6)   ; $p++){
					if ( $p > 6 ) $day= $p - 7 ;
					else $day = $p;
					$displayDay[$y]= $calDay[$day];	
					$y = $y + 1;
			?>
			<td class="calDay"  style="border:none;<?php if($p < 12) { ?>border-right:1px solid white;<?php } ?>">
				<?php 
					echo $calDay[$day]; 
					$calDaySet[$y] = $calDay[$day] ;
				?>
			</td>
			<?php } ?>
		</tr>
			<?php
			$nos = 0;
			$nosDisplayFlag = 0;
			for($p=1;$p<=6;$p++){ ?>
		  <tr>
		  <?php for($y=1;$y<=7;$y++){ ?>
		  <?php 
			$nos++;
			if ($nos > $calanderLastDay ) break;
			if ($p == 1 ) { 
				if ( $calDaySet[$y+1] == $calanderFristDay ) {
					$nos = 1;
					$nosDisplayFlag = 1;	
				}	
			}
			
			//set class name
			$caldateClass = "calOffDay";
			if (strpos($calanderOffDay,$calDaySet[$y+1]) === false) $caldateClass ="calDate" ;
			
			
			if ( $nosDisplayFlag ) {
				$all_hints = "";
				$eventList = "";
				$hints_num = 0;
				$all_hintsdisplay = '';
				$displayDate = $nos.' '.$calanderMonthDisplay.' '.$calanderYear;
				
				for ($i=0; $i<count($sqlRS); $i++ ) {
					if ( $calanderYear . $calanderMonth  . str_pad($nos, 2, "0", STR_PAD_LEFT) >= $sqlRS[$i]['FLC_DATE_FROM'] and
						 $calanderYear . $calanderMonth  . str_pad($nos, 2, "0", STR_PAD_LEFT) <= $sqlRS[$i]['FLC_DATE_TO'] ) {
						if ( $sqlRS[$i]['FLC_HINTS'] != "" ) {
							$all_hints = $all_hints . $sqlRS[$i]['FLC_HINTS']."<hr>";
							//$eventList .= '<li>'.$sqlRS[$i]['FLC_DATE_FROM'].' - '.$sqlRS[$i]['FLC_DATE_TO'].'<br/>'. $sqlRS[$i]['FLC_HINTS'] .'<br/><br/></li>';
							//$eventList .= '<li>'. addslashes($sqlRS[$i]['FLC_HINTS']) .'</li>';
							
							 if($sqlRS[$i]['FLC_URL']) {
								$eventList .= '<li> <a href=\\\''.$sqlRS[$i]['FLC_URL'].'\\\' target=\\\'_blank\\\'> ';
								$eventList .= addslashes($sqlRS[$i]['FLC_HINTS']) .' </a></li>';
							 } else	{
								$eventList .= '<li>'. addslashes($sqlRS[$i]['FLC_HINTS']) .'</li>';
							 }					
							
							$hints_num++;
						}	
					}
				}
				
				// if current day
				if ($nos == $currentDay && $calanderMonthDisplay == $currentMonth2 && $calanderYear == $currentYear) {
					$all_hints = str_replace("'","\'",$all_hints);
					if ( $all_hints == "" ) {
						$caldateClass = "calToday";
						$CalCell = "";
						
					} else {
						$all_hintsdisplay = " onmouseover=\"tooltip.show('" . $all_hints . "')\"; onmouseout=\"tooltip.hide()\"; onclick=\"klikShowDayEvent('".$displayDate."','".$eventList."')\" ";
						//$CalCell = "<div $all_hintsdisplay style='width:100%; height:100%; background:#97A9FC;' >$nos</div>";
						$caldateClass = "calDateEvent2";
						$CalCell = "<div class='calDateEventNum'> $hints_num <div>";
					}
					
				} else {
					$all_hints = str_replace("'","\'",$all_hints);
					if ( $all_hints == "" ) {	
						$CalCell = "";
					}
					else {
						$all_hintsdisplay = " onmouseover=\"tooltip.show('" . $all_hints . "')\"; onmouseout=\"tooltip.hide()\";  onclick=\"klikShowDayEvent('".$displayDate."','".$eventList."')\" ";
						$caldateClass = "calDateEvent1";	
						$CalCell = "<div class='calDateEventNum'> $hints_num <div>";				
					}
				}	
			}
			?>
			
			<td  style="border:none;<?php if($y < 7) { ?>border-right:1px solid white;<?php } ?>border-bottom:1px solid white;" class="<?php if($nosDisplayFlag) echo $caldateClass; ?>" height="<?php echo $calanderCellHeigth; ?>" width="<?php echo $calanderCellWidth; ?>" <?php echo $all_hintsdisplay; ?> >
				<span style="font-size:15px; padding: 20px 10px; line-height: 40px;"><?php if($nosDisplayFlag) echo $nos; ?></span>
				<?php echo $CalCell; ?>
			</td>
			
		  <?php } 	?>
		  </tr>
		<?php } 	?>

		<?php
			$monthEventList = '';
			for ($i=0; $i<count($sqlRS); $i++ ) {
				if ( $calanderYear.$calanderMonth >= $sqlRS[$i]['FLC_YEARMONTH_FROM'] and
					 $calanderYear.$calanderMonth <= $sqlRS[$i]['FLC_YEARMONTH_TO'] ) {

					 if($sqlRS[$i]['FLC_URL']) {
						$monthEventList .= '<tr><td> <a href=\\\''.$sqlRS[$i]['FLC_URL'].'\\\' target=\\\'_blank\\\'> ';
						$monthEventList .= addslashes($sqlRS[$i]['FLC_HINTS']) .' </a></td>';
					 } else	{
						$monthEventList .= '<tr><td> '. addslashes($sqlRS[$i]['FLC_HINTS']) .' </td>';
					 }
					 
					 $monthEventList .= '<td> '. date("d-m-Y",strtotime($sqlRS[$i]['FLC_DATE_FROM']." 00:00:00") ) .' </td>';
					 $monthEventList .= '<td> '. date("d-m-Y",strtotime($sqlRS[$i]['FLC_DATE_TO']." 00:00:00") ) .' </td></tr>';
				}
			}
		?>

		</table>
		<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5" class="calMonthYear">
		  <tr>
			<td align="Left" style="border:none"><?php echo $CalanderFooter; ?>  </td> 
			<td align="right"  style="border:none"> <u style="cursor:pointer;" onclick="klikShowMonthEvent('<?php echo $calanderMonthDisplay.' '.$calanderYear; ?>', '<?php echo $monthEventList; ?>')">Show All Event In This Month</u> </td> 
		  </tr>
		</table>  
		</td>
	</tr>
</table>
</div>
