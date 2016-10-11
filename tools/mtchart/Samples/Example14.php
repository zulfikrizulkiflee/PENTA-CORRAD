<?php
/**
 * Example14: A smooth flat pie graph
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
$Test->loadColorPalette("Data/softtones.txt");
$Test->drawFilledRoundedRectangle(7,7,293,193,5,240,240,240);
$Test->drawRoundedRectangle(5,5,295,195,5,230,230,230);

// This will draw a shadow under the pie chart
$Test->drawFilledCircle(122,102,70,200,200,200);

// Draw the pie chart
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setAntialiasQuality(0);
$Test->drawBasicPieGraph(120,100,70,PIE_PERCENTAGE,255,255,218);
$Test->drawPieLegend(230,15,250,250,250);

$Test->Stroke();