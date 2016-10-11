<?php
//cikkim 20131207
function createPngChart($width=500,$height=350,$data,$label,$fileName)
{
	/**
	 * Example14: A smooth flat pie graph
	 *
	 * Taken from the pChart example library
	 *
	 * @link http://pchart.sourceforge.net/screenshots.php
	 */

	// Standard includes
	include_once('../mtChart.php');

	//declaration
	//-----------

	//chart size
	$pieRadius = ($width/4*2)/2;
	$pieX = $pieRadius+50;
	$pieY = $pieRadius+30;

	$legendLongestLabel = 12;
	$legendLongestLabelToPixel = $legendLongestLabel*10;

	// Dataset definition
	$Test = new mtChart($width,$height);														//size
	$Test->addPoint($data,"data");															//points - data
	$Test->addPoint($label,"legend");	//legend
	$Test->addAllSeries();
	$Test->setAbsciseLabelSerie("legend");

	// Initialise the graph
	$Test->loadColorPalette("Data/softtones.txt");											//color info
	$Test->drawFilledRoundedRectangle(7,7,$width-10,$height-10,5,240,240,240);				//chart border - cosmetic	drawFilledRoundedRectangle	($X1, $Y1, $X2, $Y2, $Radius = 5, $R, $G, $B)
	$Test->drawRoundedRectangle(5,5,$width-8,$height-8,5,230,230,230);						//chart border - cosmetic	drawRoundedRectangle		($X1, $Y1, $X2, $Y2, $Radius = 5, $R, $G, $B) 

	// This will draw a shadow under the pie chart
	$Test->drawFilledCircle($pieX+1,$pieY+1,$pieRadius+2,200,200,200);										//cosmetic - drawFilledCircle($Xc, $Yc, $Height, $R, $G, $B, $Width = NULL)

	// Draw the pie chart
	$Test->setFontProperties('DejaVuSansCondensed',8);
	$Test->setAntialiasQuality(0);															//0 best to 100 worst
	$Test->drawBasicPieGraph($pieX,$pieY,$pieRadius,PIE_PERCENTAGE,255,255,218,1);			//drawBasicPieGraph	($XPos, $YPos, $Radius = 100, $DrawLabels = PIE_NOLABEL, $R = 255, $G = 255, $B = 255, $Decimals = 0)
	$Test->drawPieLegend($width-$legendLongestLabelToPixel,15,250,250,250,250);				//drawPieLegend		($XPos, $YPos, $R = 255, $G = 255, $B = 255, $Rs = NULL, $Gs = NULL, $Bs = NULL, $Rt = 0, $Gt = 0, $Bt = 0, $Border = TRUE)

	//output
	if($fileName == '')
		$Test->Stroke();																	//display
	else
		$Test->Render($fileName);															//save as file
}

$data = array(1,2,3,4,5);
$label = array('lab1','lab2','lab3','lab4','lab5');
//$fileName = 'bbb_'.date('YmdHis').'.png';

createPngChart(500,350,$data,$label,$fileName);
