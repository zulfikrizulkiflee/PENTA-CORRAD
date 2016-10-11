<?php
/**
 * Example for the homepage : A single stacked bar graph
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(380,400);
$Test->addPoint(array(1,2,5),"Serie1");
$Test->addPoint(array(3,2,2),"Serie2");
$Test->addPoint(array(3,4,1),"Serie3");
$Test->addPoint(array("A#~1","A#~2","A#~3"),"Labels");
$Test->addAllSeries();
$Test->RemoveSerie("Labels");
$Test->setAbsciseLabelSerie("Labels");
$Test->setSerieName("Alpha","Serie1");
$Test->setSerieName("Beta","Serie2");
$Test->setSerieName("Gama","Serie3");
$Test->SetXAxisName("Samples IDs");
$Test->SetYAxisName("Test Marker");
$Test->SetYAxisUnit("μm");

// Initialise the graph
$Test->drawGraphAreaGradient(90,90,90,90,TARGET_BACKGROUND);

// Graph area setup
$Test->setFontProperties("DejaVuSansMono",6);
$Test->setGraphArea(110,180,350,360);
$Test->drawGraphArea(213,217,221,FALSE);
$Test->drawScale(SCALE_ADDALLSTART0,213,217,221,TRUE,0,2,TRUE);
$Test->drawGraphAreaGradient(40,40,40,-50);
$Test->drawGrid(4,TRUE,230,230,230,5);

// Draw the title
$Test->setFontProperties('DejaVuSansCondensed',10);
$Title = "  Average growth size for selected\r\n  DNA samples  ";
$Test->setLineStyle(2);
$Test->drawLine(51,-2,51,402,0,0,0);
$Test->setLineStyle(1);
$Test->drawTextBox(0,0,50,400,$Title,90,255,255,255,ALIGN_BOTTOM_CENTER,TRUE,0,0,0,30);
$Test->setFontProperties("DejaVuSansMono",6);

// Draw the bar graph
$Test->drawStackedBarGraph(70);

// Second chart
$Test->SetXAxisName("");
$Test->clearScale();
$Test->setGraphArea(110,20,350,140);
$Test->drawGraphArea(213,217,221,FALSE);
$Test->drawScale(SCALE_START0,213,217,221,TRUE,0,2);
$Test->drawGraphAreaGradient(40,40,40,-50);
$Test->drawGrid(4,TRUE,230,230,230,5);

// Draw the line chart
$Test->setShadowProperties(0,3,0,0,0,30,4);
$Test->drawFilledCubicCurve(.1,40);
$Test->clearShadow();

// Write the legend
$Test->drawLegend(-2,3,0,0,0,0,0,0,255,255,255,FALSE);

// Finish the graph
$Test->addBorder(1);
$Test->Stroke();