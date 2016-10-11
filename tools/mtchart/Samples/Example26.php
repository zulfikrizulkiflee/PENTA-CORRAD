<?php
/**
 * Example26 : Two Y axis / shadow demonstration
 *
 * Taken from the pChart example library
 *
 * @link http://pchart.sourceforge.net/screenshots.php
 */

// Standard includes
include('../mtChart.php');

// Dataset definition
$Test = new mtChart(660,230);
$Test->addPoint(array(110,101,118,108,110,106,104),"Serie1");
$Test->addPoint(array(700,2705,2041,1712,2051,846,903),"Serie2");
$Test->addPoint(array("03 Oct","02 Oct","01 Oct","30 Sep","29 Sep","28 Sep","27 Sep"),"Serie3");
$Test->AddSerie("Serie1");
$Test->setAbsciseLabelSerie("Serie3");
$Test->setSerieName("SourceForge Rank","Serie1");
$Test->setSerieName("Web Hits","Serie2");

// Initialise the graph
$Test->drawGraphAreaGradient(90,90,90,90,TARGET_BACKGROUND);

// Prepare the graph area
$Test->setFontProperties("DejaVusSans",8);
$Test->setGraphArea(60,40,595,190);

// Initialise graph area
$Test->setFontProperties("DejaVusSans",8);

// Draw the SourceForge Rank graph
$Test->SetYAxisName("Sourceforge Rank");
$Test->drawScale(SCALE_NORMAL,213,217,221,TRUE,0,0);
$Test->drawGraphAreaGradient(40,40,40,-50);
$Test->drawGrid(4,TRUE,230,230,230,10);
$Test->setShadowProperties(3,3,0,0,0,30,4);
$Test->drawCubicCurve();
$Test->clearShadow();
$Test->drawFilledCubicCurve(.1,30);
$Test->drawPlotGraph(3,2,255,255,255);

// Clear the scale
$Test->clearScale();

// Draw the 2nd graph
$Test->RemoveSerie("Serie1");
$Test->AddSerie("Serie2");
$Test->SetYAxisName("Web Hits");
$Test->drawRightScale(SCALE_NORMAL,213,217,221,TRUE,0,0);
$Test->drawGrid(4,TRUE,230,230,230,10);
$Test->setShadowProperties(3,3,0,0,0,30,4);
$Test->drawCubicCurve();
$Test->clearShadow();
$Test->drawFilledCubicCurve(.1,30);
$Test->drawPlotGraph(3,2,255,255,255);

// Write the legend (box less)
$Test->setFontProperties("DejaVusSans",8);
$Test->drawLegend(530,5,0,0,0,0,0,0,255,255,255,FALSE);

// Write the title
$Test->setFontProperties("DejaVusSans",18);
$Test->setShadowProperties(1,1,0,0,0);
$Test->drawTitle(0,0,"SourceForge ranking summary",255,255,255,660,30,TRUE);
$Test->clearShadow();

// Render the picture
$Test->Stroke();
