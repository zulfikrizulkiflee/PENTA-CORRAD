<?php

// Author : Luqman Shariffudin

include('tcpdf.php');

class EXTTCPDF extends TCPDF
{
	//convert % to unit
	public function Percent($percentage, $mother) 
	{
		if($percentage == 0) {
			return 0.0000001; //nyorok
		} else {
			$dimensions = $this->getPageDimensions(); //get all dimensions
			if($mother == 'w' || $mother == 'W') {
				$content_width = $dimensions['wk'] - $dimensions['lm'] - $dimensions['rm'];
				return ($percentage/100) * $content_width;
			} elseif($mother == 'h' || $mother == 'H') {
				$content_height = $dimensions['hk'] - $dimensions['tm'] - $dimensions['bm'];
				return ($percentage/100) * $content_height;
			}
		}
	}

	public function PercentWidth($figure)
	{
		return $this->Percent($figure, 'w');
	}

	//detect pagebreak and print
	public function PageBreakIfRequire($height) 
	{
		$dimensions = $this->getPageDimensions();
		$pb = $dimensions['hk'] - $dimensions['bm'];
		$pb -= $this->GetY();
		if($pb < $height) {
			$this->AddPage();
			return true;
		}
	}
}

?>