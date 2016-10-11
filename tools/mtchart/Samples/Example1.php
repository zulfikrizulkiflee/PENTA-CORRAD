<?php
/**
 * Example 1: A simple line chart
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->importFromCSV("Data/bulkdata.csv",",",array(1,2,3),FALSE,0);
$Test->addAllSeries();
$Test->setAbsciseLabelSerie();
$Test->setSerieName("January","Serie1");
$Test->setSerieName("February","Serie2");
$Test->setSerieName("March","Serie3");
$Test->setYAxisName("Average age");
$Test->setYAxisUnit("ms");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(70,30,680,200);
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
$Test->drawLegend(75,35,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(60,22,"example 1",50,50,50,585);
$Test->Stroke();
