<?php
/**
 * SmallGraph: Let's go fast, draw small!
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(100,30);
$Test->addPoint(array(1,4,-3,2,-3,3,2,1,0,7,4,-3,2,-3,3,5,1,0,7),"Serie1");
$Test->addAllSeries();
$Test->setAbsciseLabelSerie();
$Test->setSerieName("January","Serie1");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawFilledRoundedRectangle(2,2,98,28,2,230,230,230);
$Test->setGraphArea(5,5,95,25);
$Test->drawGraphArea(255,255,255);
$Test->drawScale(SCALE_NORMAL,220,220,220,FALSE);

// Draw the line graph
$Test->drawLineGraph();

// Finish the graph
$Test->Stroke();
