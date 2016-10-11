<?php
/**
 * Minimal example
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(1,4,3,2,3,3,2,1,0,7,4,3,2,3,3,5,1,0,7));
$Test->AddSerie();
$Test->setSerieName("Sample data","Serie1");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->setGraphArea(40,30,680,200);
$Test->drawGraphArea(252,252,252,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,0,2);
$Test->drawGrid(4,TRUE,230,230,230,70);

// Draw the line graph
$Test->drawLineGraph();
$Test->drawPlotGraph(3,2,255,255,255);

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(45,35,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(60,22,"My pretty graph",50,50,50,585);
$Test->Stroke();
