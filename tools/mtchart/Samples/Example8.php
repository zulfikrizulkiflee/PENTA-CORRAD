<?php
/**
 * Example8 : A radar graph
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php?ID=8
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(400,400);
$Test->addPoint(array("Memory","Disk","Network","Slots","CPU"),"Label");
$Test->addPoint(array(1,2,3,4,3),"Serie1");
$Test->addPoint(array(1,4,2,6,2),"Serie2");
$Test->AddSerie("Serie1");
$Test->AddSerie("Serie2");
$Test->setAbsciseLabelSerie("Label");

$Test->setSerieName("Reference","Serie1");
$Test->setSerieName("Tested computer","Serie2");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawFilledRoundedRectangle(7,7,393,393,5,240,240,240);
$Test->drawRoundedRectangle(5,5,395,395,5,230,230,230);
$Test->setGraphArea(30,30,370,370);
$Test->drawFilledRoundedRectangle(30,30,370,370,5,255,255,255);
$Test->drawRoundedRectangle(30,30,370,370,5,220,220,220);

// Draw the radar graph
$Test->drawRadarAxis(TRUE,20,120,120,120,230,230,230);
$Test->drawFilledRadar(50,20);

// Finish the graph
$Test->drawLegend(15,15,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(0,22,"Example 8",50,50,50,400);
$Test->Stroke();
