<?php
/**
 * Example17 : Playing with axis
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(100,320,200,10,43),"Serie1");
$Test->addPoint(array(23,432,43,153,234),"Serie2");
$Test->addPoint(array(1217541600,1217628000,1217714400,1217800800,1217887200),"Serie3");
$Test->AddSerie("Serie1");
$Test->AddSerie("Serie2");
$Test->setAbsciseLabelSerie("Serie3");
$Test->setSerieName("Incoming","Serie1");
$Test->setSerieName("Outgoing","Serie2");
$Test->SetYAxisName("Call duration");
$Test->SetYAxisFormat("time");
$Test->SetXAxisFormat("date");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(85,30,650,200);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,0,2);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the line graph
$Test->drawLineGraph();
$Test->drawPlotGraph(3,2,255,255,255);

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(90,35,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(60,22,"example 17",50,50,50,585);
$Test->Stroke();
