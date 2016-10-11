<?php 
//include stuff needed for session, database connection, and stuff
include('system_prerequisite.php');
require_once('tools/tcpdf/tcpdf.php');

$html 			= $_SESSION['tcpdf_params']['html'];
$papersize 		= $_SESSION['tcpdf_params']['papersize'];
$orientation 	= $_SESSION['tcpdf_params']['orientation'];
$filename 		= $_SESSION['tcpdf_params']['filename'];
$destination 	= $_SESSION['tcpdf_params']['destination'];
$headerhtml 	= $_SESSION['tcpdf_params']['headerhtml'];
$footerhtml 	= $_SESSION['tcpdf_params']['footerhtml'];

class FLCPDF extends tcpdf
{
	public $headerContent=null;
	public $footerContent=null;

	public function Header(){
		$this->WriteHTML($this->headerContent);
	}
	public function Footer(){
		if($this->footerContent != null)
		{
			$this->WriteHTML($this->footerContent);
		}
		else
		{
			$this->SetFont('Helvetica','','8');
			$this->Cell($w=0, $h=5, $txt=$this->PageNo().' / '.$this->getAliasNumPage(), $border='T', $ln=0, $align='C', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M');
		}
	}
}

$pdf = new FLCPDF($orientation, $unit='mm', $papersize, $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false);
$pdf->headerContent = $headerhtml;
$pdf->footerContent = $footerhtml;
$pdf->SetHeaderMargin(11);
$pdf->SetFooterMargin(13);
$pdf->SetAutoPageBreak(TRUE, $margin=25);
$pdf->SetMargins( $margin_left=15, $margin_top=25, $margin_right=15);
$pdf->AddPage();
$pdf->WriteHTML($html);
$pdf->Output($filename,$destination);
unset($_SESSION['tcpdf_params']);
echo '<script>window.close();</script>';

?>
