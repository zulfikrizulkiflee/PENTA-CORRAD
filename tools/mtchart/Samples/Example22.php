<?php
/**
 * Example22 : Customizing plot graphs
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(700,230);
$Test->addPoint(array(60,70,90,110,100,90),"Serie1");
$Test->addPoint(array(40,50,60,80,70,60),"Serie2");
$Test->addPoint(array("Jan","Feb","Mar","Apr","May","Jun"),"Serie3");
$Test->AddSerie("Serie1");
$Test->AddSerie("Serie2");
$Test->setAbsciseLabelSerie("Serie3");
$Test->setSerieName("Company A","Serie1");
$Test->setSerieName("Company B","Serie2");
$Test->SetYAxisName("Product sales");
$Test->SetYAxisUnit("k");
$Test->SetSerieSymbol("Serie1","Data/Point_Asterisk.gif");
$Test->SetSerieSymbol("Serie2","Data/Point_Cd.gif");

// Initialise the graph
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(65,30,650,200);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale(SCALE_NORMAL,150,150,150,TRUE,0,2,TRUE);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Draw the title
$Test->setFontProperties("DejaVuSansMono",6);
$Title = "Comparative product sales for company A & B  ";
$Test->drawTextBox(65,30,650,45,$Title,0,255,255,255,ALIGN_RIGHT,TRUE,0,0,0,30);

// Draw the line graph
$Test->drawLineGraph();
$Test->drawPlotGraph(3,2,255,255,255);

// Draw the legend
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->drawLegend(80,60,255,255,255);

// Render the chart
$Test->Stroke();
