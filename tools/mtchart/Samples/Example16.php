<?php
/**
 * Example16 : Importing CSV data
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->ImportFromCSV("Data/CO2.csv",",",array(1,2,3,4),TRUE,0);
$Test->addAllSeries();
$Test->setAbsciseLabelSerie();
$Test->SetYAxisName("CO2 concentrations");

// Initialise the graph

$Test->reportWarnings("GD");
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(60,30,680,180);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,90,2);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the line graph
$Test->drawLineGraph();
$Test->drawPlotGraph(3,2,255,255,255);

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(70,40,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(60,22,"CO2 concentrations at Mauna Loa",50,50,50,585);
$Test->Stroke();
