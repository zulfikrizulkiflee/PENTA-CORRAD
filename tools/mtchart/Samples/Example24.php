<?php
/**
 * Example24 : X versus Y chart
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(300,300);

// Compute the points
for($i=0;$i<=360;$i=$i+10)
{
    $Test->addPoint(cos($i*3.14/180)*80+$i,"Serie1");
    $Test->addPoint(sin($i*3.14/180)*80+$i,"Serie2");
}

$Test->setSerieName("Trigonometric function","Serie1");
$Test->AddSerie("Serie1");
$Test->AddSerie("Serie2");
$Test->SetXAxisName("X Axis");
$Test->SetYAxisName("Y Axis");

// Initialise the graph
$Test->drawGraphAreaGradient(0,0,0,-100,TARGET_BACKGROUND);

// Prepare the graph area
$Test->setFontProperties('DejaVuSansCondensed',8);
$Test->setGraphArea(55,30,270,230);
$Test->drawXYScale("Serie1","Serie2",213,217,221,TRUE,45);
$Test->drawGraphArea(213,217,221,FALSE);
$Test->drawGraphAreaGradient(30,30,30,-50);
$Test->drawGrid(4,TRUE,230,230,230,20);

// Draw the chart
$Test->setShadowProperties(2,2,0,0,0,60,4);
$Test->drawXYGraph("Serie1","Serie2",0);
$Test->clearShadow();

// Draw the title
$Title = "Drawing X versus Y charts trigonometric functions  ";
$Test->drawTextBox(0,280,300,300,$Title,0,255,255,255,ALIGN_RIGHT,TRUE,0,0,0,30);

// Draw the legend
$Test->setFontProperties("DejaVuSansMono",6);
$Test->RemoveSerie("Serie2");
$Test->drawLegend(160,5,0,0,0,0,0,0,255,255,255,FALSE);

$Test->Stroke();