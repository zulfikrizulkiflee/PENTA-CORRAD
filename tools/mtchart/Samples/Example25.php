<?php
/**
 * Example25 : Playing with shadow
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(9,9,9,10,10,11,12,14,16,17,18,18,19,19,18,15,12,10,9),"Serie1");
$Test->addPoint(array(10,11,11,12,12,13,14,15,17,19,22,24,23,23,22,20,18,16,14),"Serie2");
$Test->addPoint(array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22),"Serie3");
$Test->addAllSeries();
$Test->RemoveSerie("Serie3");
$Test->setAbsciseLabelSerie("Serie3");
$Test->setSerieName("January","Serie1");
$Test->setSerieName("February","Serie2");
$Test->SetYAxisName("Temperature");
$Test->SetYAxisUnit("°C");
$Test->SetXAxisUnit("h");

// Initialise the graph
$Test->drawGraphAreaGradient(90,90,90,90,TARGET_BACKGROUND);
$Test->setFixedScale(0,40,4);

// Graph area setup
$Test->setFontProperties("DejaVuSansMono",6);
$Test->setGraphArea(60,40,680,200);
$Test->drawGraphArea(200,200,200,FALSE);
$Test->drawScale(SCALE_NORMAL,200,200,200,TRUE,0,2);
$Test->drawGraphAreaGradient(40,40,40,-50);
$Test->drawGrid(4,TRUE,230,230,230,10);

// Draw the line chart
$Test->setShadowProperties(3,3,0,0,0,30,4);
$Test->drawCubicCurve();
$Test->clearShadow();
$Test->drawPlotGraph(3,0,-1,-1,-1,TRUE);

// Write the title
$Test->setFontProperties("DejaVuSansCondensed",18);
$Test->setShadowProperties(1,1,0,0,0);
$Test->drawTitle(0,0,"Average temperatures",255,255,255,700,30,TRUE);
$Test->clearShadow();

// Draw the legend
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(610,5,0,0,0,0,0,0,255,255,255,FALSE);

// Render the picture
$Test->Stroke();