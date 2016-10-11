<?php
/**
* Example13: A 2D exploded pie graph
*
* Taken from the pChart example library
*
* @link http://pchart.sourceforge.net/screenshots.php
*/

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(300,200);
$Test->addPoint(array(10,2,3,5,3),"Serie1");
$Test->addPoint(array("Jan","Feb","Mar","Apr","May"),"Serie2");
$Test->addAllSeries();
$Test->setAbsciseLabelSerie("Serie2");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawFilledRoundedRectangle(7,7,293,193,5,240,240,240);
$Test->drawRoundedRectangle(5,5,295,195,5,230,230,230);

// Draw the pie chart
$Test->setAntialiasQuality(0);
$Test->setShadowProperties(2,2,200,200,200);
$Test->drawFlatPieGraphWithShadow(120,100,60,PIE_PERCENTAGE,8);
$Test->clearShadow();

$Test->drawPieLegend(230,15,250,250,250);

$Test->Stroke();
