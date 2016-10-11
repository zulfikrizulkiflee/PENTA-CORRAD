<?php
/**
 * Example10 : A 3D exploded pie graph
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(420,250);
$Test->addPoint(array(10,2,3,5,3),"Serie1");
$Test->addPoint(array("January","February","March","April","May"),"Serie2");
$Test->addAllSeries();
$Test->setAbsciseLabelSerie("Serie2");

// Initialise the graph
$Test->drawFilledRoundedRectangle(7,7,413,243,5,240,240,240);
$Test->drawRoundedRectangle(5,5,415,245,5,230,230,230);
$Test->createColorGradientPalette(195,204,56,223,110,41,5);

// Draw the pie chart
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setAntialiasQuality(0);
$Test->drawPieGraph(180,130,110,PIE_PERCENTAGE_LABEL,FALSE,50,20,5);
$Test->drawPieLegend(330,15,250,250,250);

// Write the title
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(10,20,"Sales per month",100,100,100);

$Test->Stroke();
