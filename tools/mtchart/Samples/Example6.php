<?php
/**
 * Example6 : A simple filled line graph
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php?ID=6
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->ImportFromCSV("Data/datawithtitle.csv",",",array(1,2,3),TRUE,0);
$Test->addAllSeries();
$Test->setAbsciseLabelSerie();

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(60,30,680,200);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,0,2);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the filled line graph
$Test->drawFilledLineGraph(50,TRUE);

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(65,35,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(60,22,"Example 6",50,50,50,585);
$Test->Stroke();