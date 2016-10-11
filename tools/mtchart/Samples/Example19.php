<?php
/**
 * Example19 : Error reporting
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(10,4,3,2,3,3,2,1,0,7,4,3,2,3,3,5,1,0,7),"Serie1");
$Test->addPoint(array(1,4,2,6,2,3,0,1,-5,1,2,4,5,2,1,0,6,4,30),"Serie2");
$Test->addAllSeries();
$Test->setAbsciseLabelSerie();
$Test->SetXAxisName("Samples");
$Test->SetYAxisName("Temperature");
$Test->setSerieName("January","Serie1");

// Initialise the graph
$Test->reportWarnings("GD");
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(60,30,585,185);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,0,2);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the cubic curve graph
$Test->drawCubicCurve();

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(600,30,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(50,22,"Example 19",50,50,50,585);
$Test->Stroke();
