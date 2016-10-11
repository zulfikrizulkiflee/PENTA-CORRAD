<?php
/**
 * Example20 : A stacked bar graph
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(1,4,-3,2,-3,3,2,1,0,7,4),"Serie1");
$Test->addPoint(array(3,3,-4,1,-2,2,1,0,-1,6,3),"Serie2");
$Test->addPoint(array(4,1,2,-1,-4,-2,3,2,1,2,2),"Serie3");
$Test->addAllSeries();
$Test->setAbsciseLabelSerie();
$Test->setSerieName("January","Serie1");
$Test->setSerieName("February","Serie2");
$Test->setSerieName("March","Serie3");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(50,30,680,200);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_ADDALL,150,150,150,TRUE,0,2,TRUE);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the bar graph
$Test->drawStackedBarGraph(100);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(596,150,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(50,22,"Example 20",50,50,50,585);
$Test->Stroke();
