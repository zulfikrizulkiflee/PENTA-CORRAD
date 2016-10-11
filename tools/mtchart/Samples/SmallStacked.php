<?php
/**
 * Example : A single stacked bar graph
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(210,230);
$Test->addPoint(1,"Serie1");
$Test->addPoint(3,"Serie2");
$Test->addPoint(3,"Serie3");
$Test->addPoint("A#~1","Labels");
$Test->addAllSeries();
$Test->RemoveSerie("Labels");
$Test->setAbsciseLabelSerie("Labels");
$Test->setSerieName("Alpha","Serie1");
$Test->setSerieName("Beta","Serie2");
$Test->setSerieName("Gama","Serie3");
$Test->SetYAxisName("Test Marker");
$Test->SetYAxisUnit("μm");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(65,30,125,200);
$Test->drawFilledRoundedRectangle(7,7,203,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,205,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_ADDALLSTART0,150,150,150,TRUE,0,2,TRUE);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the bar graph
$Test->drawStackedBarGraph(50);

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(135,150,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(0,22,"Sample size",50,50,50,210);
$Test->Stroke();
