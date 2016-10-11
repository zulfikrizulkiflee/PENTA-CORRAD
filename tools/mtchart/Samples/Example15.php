<?php
/**
 * Example15 : Playing with line style & pictures inclusion
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');
 
// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(10,9.4,7.7,5,1.7,-1.7,-5,-7.7,-9.4,-10,-9.4,-7.7,-5,-1.8,1.7),"Serie1");
$Test->addPoint(array(0,3.4,6.4,8.7,9.8,9.8,8.7,6.4,3.4,0,-3.4,-6.4,-8.6,-9.8,-9.9),"Serie2");
$Test->addPoint(array(7.1,9.1,10,9.7,8.2,5.7,2.6,-0.9,-4.2,-7.1,-9.1,-10,-9.7,-8.2,-5.8),"Serie3");
$Test->addPoint(array("Jan","Jan","Jan","Feb","Feb","Feb","Mar","Mar","Mar","Apr","Apr","Apr","May","May","May"),"Serie4");
$Test->addAllSeries();
$Test->setAbsciseLabelSerie("Serie4");
$Test->setSerieName("Max Average","Serie1");
$Test->setSerieName("Min Average","Serie2");
$Test->setSerieName("Temperature","Serie3");
$Test->SetYAxisName("Temperature");
$Test->SetXAxisName("Month of the year");

// Initialise the graph
$Test->reportWarnings("GD");
$Test->setFixedScale(-12,12,5);
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(65,30,570,185);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE,3);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the 0 line
$Test->setFontProperties('DejaVuSansCondensed',6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Draw the area
$Test->RemoveSerie("Serie4");
$Test->drawArea("Serie1","Serie2",239,238,227,50);
$Test->RemoveSerie("Serie3");
$Test->drawLineGraph();

// Draw the line graph
$Test->setLineStyle(1,6);
$Test->RemoveAllSeries();
$Test->AddSerie("Serie3");
$Test->drawLineGraph();
$Test->drawPlotGraph(3,2,255,255,255);

// Write values on Serie3
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->writeValues("Serie3");

// Finish the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(590,90,255,255,255);
$Test->setFontProperties('DejaVuSansCondensed',10);
$Test->drawTitle(60,22,"example 15",50,50,50,585);

// Add an image
$Test->drawFromPNG("Data/logo.png",584,35);

// Render the chart
$Test->Stroke();
