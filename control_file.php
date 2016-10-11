<?php
/*
Luqman Shariffudin
Generate PDF or CSV from report component
*/

require_once('system_prerequisite.php');

//validate that user have session
require_once('func_common.php');
validateUserSession();

/*---- START FUNCTIONS ----*/
//generate and sync index from name(key)
function SyncNameWithIndex($array)
{
	$final_array = array();

	if(is_array($array))
	{
		foreach ($array as $key => $value) {
			$final_array[] = $key;
		}
		return $final_array;
	}
	else{
		return false;
	}
}

//pecahkan widths pipe dan jadikan array
function GetCollectionWidth($number_with_pipes=null)
{
	if($number_with_pipes)
	{
		$cols = explode('||', $number_with_pipes);
		array_pop($cols);
		return $cols;
	}
}

function GetAndConvertColumnWidth($current_key, $sync_name, $collectionWidth, $totalWidth, $pdf, $is_bil=false)
{
	$index = array_search($current_key, $sync_name);
	if($is_bil) $key_sync = $collectionWidth[0];
	if(!$is_bil) $key_sync = $collectionWidth[$index+1];
	$percent = ($key_sync/$totalWidth)*100;
	$width_converted = $pdf->PercentWidth($percent);

	return $width_converted;
}

function DisplayTableHeader()
{
	//amik dari global terus
	$pdf = $GLOBALS['pdf'];
	$width_col_no = $GLOBALS['width_col_no'];
	$sync_name_index = $GLOBALS['sync_name_index'];
	$collectionWidth = $GLOBALS['collectionWidth'];
	$totalWidth = $GLOBALS['totalWidth'];

	//setkan font jadi bold untuk header
	$pdf->SetFont('helvetica','B',9);

	//koleksi tinggi column header
	$header_cols_height = array();

	//tinggi untuk column 'No'
	$header_cols_height[] = $pdf->getNumLines('No',$width_col_no);

	//dapatkan tinggi untuk setiap column
	foreach ($sync_name_index as $key => $value)
	{
		$width_converted = GetAndConvertColumnWidth($value, $sync_name_index, $collectionWidth, $totalWidth, $pdf);
		$header_cols_height[] = $pdf->getNumLines($value,$width_converted);
	}

	//cari siapa paling tinggi
	$max_height = max($header_cols_height);

	//display
	$pdf->SetFillColor(211,211,211);

	$pdf->MultiCell($w=$width_col_no, $h=5*$max_height+3, $txt='No', $border=1, $align='L', $fill=1, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=$h, $v='M');
	foreach ($sync_name_index as $key => $value)
	{
		$width_converted = GetAndConvertColumnWidth($value, $sync_name_index, $collectionWidth, $totalWidth, $pdf);
		$pdf->MultiCell($w=$width_converted, $h=5*$max_height+3, $txt=$value, $border=1, $align='C', $fill=1, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=$h, $v='M');
	}

	//reset font
	$pdf->SetFont('helvetica','',9);
	$pdf->Ln();
}

function ConverVarToValue($str)
{
	$words = explode(' ', $str);
	$totalWord = count($words);

	for ($w=0; $w<$totalWord; $w++)
	{
		$word = $words[$w];

		if (substr($word, 0, 5) == '{GET|')
		{
			$word = substr($word, 5);
			$word = substr($word, 0, -1);
			$word = $_GET[$word];
			$words[$w] = $word;
		}
		elseif (substr($word, 0, 6) == '{POST|')
		{
			$word = substr($word, 6);
			$word = substr($word, 0, -1);
			$word = $_POST[$word];
			$words[$w] = $word;
		}
		elseif (substr($word, 0, 9) == '{SESSION|')
		{
			$word = substr($word, 9);
			$word = substr($word, 0, -1);
			$word = $_SESSION[$word];
			$words[$w] = $word;
		}
		elseif (substr($word, 0, 7) == '{CONST|')
		{
			$word = substr($word, 7);
			$word = substr($word, 0, -1);
			$word = constant($word);
			$words[$w] = $word;
		}
	}
	
	$str = implode(' ', $words);
	return $str;
}
/*---- END FUNCTIONS ----*/

//dapatkan page id
$q = "select PAGEID from FLC_PAGE where MENUID=".$_GET['menuID'];
$r = $myQuery->query($q,'SELECT','INDEX');
$pageId = $r[0][0];

//if page id valid
if($pageId != '')
{
	//cek component
	$q = "select COMPONENTID, COMPONENTTITLE from FLC_PAGE_COMPONENT where COMPONENTNAME='".$_GET['compName']."' and PAGEID=".$pageId." and COMPONENTTYPE='report'";
	$r = $myQuery->query($q,'SELECT','NAME');
	$componentId = $r[0]['COMPONENTID'];
	$componentTitle = $r[0]['COMPONENTTITLE'];

	//jika component itu 'report', baru proceed
	if($componentId)
	{
		//generate pdf
		if($_GET['gen']=='pdf')
		{
			include('tools/tcpdf/exttcpdf.php');

			class EXTTCPDF2 extends EXTTCPDF
			{
				public $title;

				public function Header(){
					$this->title = $GLOBALS['componentTitle'];
					$this->SetFont('helvetica','B',10);
					$this->MultiCell($w=0, $h=5, $txt=strtoupper(ConverVarToValue($this->title)), $border='B', $align='L', $fill=0, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=0, $v='M');
				}

				public function Footer(){
					$this->SetFont('helvetica','',9);
					$this->MultiCell($w=0, $h=5, $txt=$this->PageNo().' / '.$this->getAliasNbPages(), $border='', $align='C', $fill=0, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=0, $v='M');
				}
			}

			$orientation = ($_GET['orientation']=='' ? 'P' : $_GET['orientation']);
			$paper_size = ($_GET['size']=='' ? 'A4' : $_GET['size']);

			$pdf = new EXTTCPDF2($orientation, $unit='mm', $paper_size, $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false);

			$pdf->SetAutoPageBreak(FALSE, $margin=25);
			$pdf->SetMargins( $margin_left=10, $margin_top=22, $margin_right=10);
			$pdf->SetHeaderMargin(11);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

			$pdf->AddPage();

			$pdf->SetFont('helvetica','',9);

			//get query
			$get_query = "select COMPONENTTYPEQUERY from FLC_PAGE_COMPONENT where COMPONENTID = ".$componentId;
			$res_get_query = $myQuery->query($get_query,'SELECT','INDEX');
			$res_get_query = $res_get_query[0][0];

			//execute query
			$exe = $myQuery->query(convertDBSafeToQuery($res_get_query),'SELECT','NAME');

			//sync nama key dengan index
			$sync_name_index = SyncNameWithIndex($exe[0]);

			//dapatkan setiap column punya width
			$collectionWidth = GetCollectionWidth($_GET['cols']);

			//total column
			$total_details_rows = count($collectionWidth) - 1;

			//total width (hasil tambah semua width)
			$totalWidth = 0;
			foreach ($collectionWidth as $col) { $totalWidth+=(float)$col; }

			//array ni akan simpan koleksi height - untuk cari siapa paling tinggi
			$cols_height = array();

			//start bilangan
			$n = 1;

			//jumlah data (hujung bilangan)
			$nx = count((array)$exe);

			//dapatkan width untuk column bilangan
			$width_converted = GetAndConvertColumnWidth($key, $sync_name_index, $collectionWidth, $totalWidth, $pdf, true);
			$cols_height[] = $pdf->getNumLines($nx,$width_converted);

			//height untuk column 'No'
			$col_word_no = $pdf->getNumLines('No',$width_converted);

			//width untuk column 'No'
			$width_col_no = 0;

			//selagi tak jadi satu line
			while( $col_word_no>1 || $cols_height[0]>1)
			{
				$collectionWidth[0]++;
				$collectionWidth[$total_details_rows]--;

				$width_converted = GetAndConvertColumnWidth($key, $sync_name_index, $collectionWidth, $totalWidth, $pdf, true);

				$cols_height[0] = $pdf->getNumLines($nx,$width_converted);

				$pdf->SetFont('helvetica','B',9); //detect width kalau tulisan bold
				$col_word_no = $pdf->getNumLines('No',$width_converted);
				$pdf->SetFont('helvetica','',9);
			}
			$width_col_no = $width_converted;

			//table header
			DisplayTableHeader();

			//process display data
			foreach ((array)$exe as $arr)
			{
				//loop satu row, untuk simpan height
				foreach($arr as $key => $value)
				{
					$width_converted = GetAndConvertColumnWidth($key, $sync_name_index, $collectionWidth, $totalWidth, $pdf);
					$cols_height[] = $pdf->getNumLines($value,$width_converted);
				}

				//dapatkan height paling tinggi
				$max_height = max($cols_height);

				//detect page break jika dah lebih
				$break = $pdf->PageBreakIfRequire(5*$max_height);

				if($break)
				{
					DisplayTableHeader();
				}

				//display column 'No' untuk bilangan
				$pdf->MultiCell($w=$pdf->PercentWidth(($collectionWidth[0]/$totalWidth)*100), $h=5*$max_height, $txt=$n, $border=1, $align='L', $fill=0, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=$h, $v='M');

				//loop untuk display data
				foreach($arr as $key => $value)
				{
					$width_converted = GetAndConvertColumnWidth($key, $sync_name_index, $collectionWidth, $totalWidth, $pdf);
					$pdf->MultiCell($w=$width_converted, $h=5*$max_height, $txt=$value, $border=1, $align='L', $fill=0, $ln=0, $x='', $y='', $reseth=true, $stretch=0, $ishtml=true, $autopadding=true, $maxh=$h, $v='M');
				}

				//kosongkan balik koleksi height
				unset($cols_height);

				$pdf->Ln();
				$n++;
			}

			$pdf->Output('filename.pdf',$destination='I');
		}
		elseif($_GET['gen']=='csv')
		{
			//get query
			$get_query = "select COMPONENTTYPEQUERY from FLC_PAGE_COMPONENT where COMPONENTID = ".$componentId;
			$res_get_query = $myQuery->query($get_query,'SELECT','INDEX');
			$res_get_query = $res_get_query[0][0];

			//execute query
			$exe = $myQuery->query(convertDBSafeToQuery($res_get_query),'SELECT','NAME');

			//filename
			$filename = str_replace(' ', '_', $componentTitle);

			if($exe)
			{
				//bina array untuk column title
				foreach((array)$exe[0] as $title => $value)
				{
					$titles[] = $title;
				}

				header('Content-Type: text/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename='.$filename.'.csv');

				//create a file pointer connected to the output stream
				$output = fopen('php://output', 'w');

				//output the column headings
				fputcsv($output, $titles);

				foreach((array)$exe as $row)
				{
					fputcsv($output, $row);
				}
			}
		}
	}
	//jika bukan component report
	else
	{
		echo 'Invalid Report Component. <a href="javascript:void(0)" onclick="window.close()">Close[X]</a>';
	}
}
else
{
	echo 'Invalid Page ID. <a href="javascript:void(0)" onclick="window.close()">Close[X]</a>';
}


?>