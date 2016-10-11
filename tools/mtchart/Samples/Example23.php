<?php
/**
 * Example23 : Playing with background continued
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(9,9,9,10,10,11,12,14,16,17,18,18,19,19,18,15,12,10,9),"Serie1");
$Test->addPoint(array(10,11,11,12,12,13,14,15,17,19,22,24,23,23,22,20,18,16,14),"Serie2");
$Test->addPoint(array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22),"Serie3");
$Test->addAllSeries();
$Test->RemoveSerie("Serie3");
$Test->setAbsciseLabelSerie("Serie3");
$Test->setSerieName("January","Serie1");
$Test->setSerieName("February","Serie2");
$Test->SetYAxisName("Temperature");
$Test->SetYAxisUnit("°C");
$Test->SetXAxisUnit("h");

// Initialise the graph
$Test->drawGraphAreaGradient(132,173,131,50,TARGET_BACKGROUND);

$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(120,20,675,190);
$Test->drawGraphArea(213,217,221,FALSE);
$Test->drawScale(SCALE_ADDALL,213,217,221,TRUE,0,2,TRUE);
$Test->drawGraphAreaGradient(163,203,167,50);
$Test->drawGrid(4,TRUE,230,230,230,20);

// Draw the bar chart
$Test->drawStackedBarGraph(70);

// Draw the title
$Title = "  Average Temperatures during\r\n  the first months of 2008  ";
$Test->drawTextBox(0,0,50,230,$Title,90,255,255,255,ALIGN_BOTTOM_CENTER,TRUE,0,0,0,30);

// Draw the legend
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(610,10,236,238,240,52,58,82);

// Render the picture
$Test->addBorder(2);
$Test->Stroke();
