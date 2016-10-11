<?php
/**
*  CLASS NAME: Chart [ COMPLETED ]
*  BASE CLASS: -
*  DESCRIPTION: To generate charts (currently using FusionCharts library)
*  				In case of library is changed, main functions to be edited are chooseFlashFile(), constructChartXML(), generateChartHTML() and generateChartJS()
*  COPYRIGHT: ESRA TECHNOLOGY SDN BHD
*  AUTHOR: MOHDFAISALIMRAN
*/

//class Chart
class Chart
{
	//attribute
	private $chartId;
	private $chartCaption;
	private $chartWidth;
	private $chartHeight;
	private $chartBgColor;
	private $chartNoPrefix;
	private $chartDecimalPrecision;
	private $chartCategory;
	private $chartType;
	private $chartFile;
	private $chartXML;
	private $chartXAxisLabel;
	private $chartPrimaryYAxisLabel;
	private $chartSecondaryYAxisLabel;
	private $chartPrimaryData;
	private $chartSecondaryData;
	private $chartPrimaryShowValue;
	private $chartSecondaryShowValue;
	private $chartTrendLabel;
	private $chartTrendValue;
	private $chartTrendColor;
	
	//init
	public function __construct($chartId, $chartCategory, $chartType, $chartWidth, $chartHeight, $chartPrimaryData)
	{
		//default value
		$this->chartId = $chartId;
		$this->chartCategory = $chartCategory;
		$this->chartType = $chartType;
		$this->chartWidth = $chartWidth;
		$this->chartHeight = $chartHeight;
		$this->chartPrimaryData = $chartPrimaryData;
		$this->chartPrimaryShowValue = 1;
		$this->chartDecimalPrecision = 0;
	}//eof function
	
	//choose file to generate Flash Chart (Fusion Chart)
	private function chooseFlashFile()
	{
		//switch file by category
		switch($this->chartCategory)
		{
			//default (single series)
			default:
				//switch file by type
				switch($this->chartType)
				{
					case 'area': $this->chartFile = 'Area2D';
						break;
					case 'bar': $this->chartFile = 'Bar2D';
						break;
					case 'column': $this->chartFile = 'Column3D';
						break;
					case 'doughnut': $this->chartFile = 'Doughnut2D';
						break;
					case 'line': $this->chartFile = 'Line';
						break;
					default: $this->chartFile = 'Pie3D';
						break;
				}//eof switch
				break;
				
			//multiple series
			case 'multiple':
				//switch file by type
				switch($this->chartType)
				{
					case 'area': $this->chartFile = 'MSArea2D';
						break;
					case 'bar': $this->chartFile = 'MSBar2D';
						break;
					case 'column': $this->chartFile = 'MSColumn3D';
						break;
					default: $this->chartFile = 'MSLine';
						break;
				}//eof switch
				break;
			
			//stacked
			case 'stacked':
				//switch file by type
				switch($this->chartType)
				{
					case 'area': $this->chartFile = 'StackedArea2D';
						break;
					case 'bar': $this->chartFile = 'StackedBar2D';
						break;
					default: $this->chartFile = 'StackedColumn3D';
						break;
				}//eof switch
				break;
			
			//combination
			case 'combination':
				$this->chartFile = 'MSColumn3DLineDY';
				break;
		}//eof switch
		
		//actual filename
		$this->chartFile = "tools/fusionChart/Charts/FCF_".$this->chartFile.".swf";
	}//eof function
	
	private function flashentities($string){ 
	return str_replace(array("&","'"),array("%26","%27"),$string); 
	} 

	//construct chart XML (Fusion Chart)
	private function constructChartXML()
	{
		//data
		$primaryData = $this->chartPrimaryData;
		$primaryDataCount = count($primaryData);
		
		//data keys
		$primaryDataKeys = array_keys($primaryData[0]);
		$primaryDataKeysCount = count($primaryDataKeys);
		
		//switch XML construction by category
		switch($this->chartCategory)
		{
			//default (single series)
			default:
				//convert primary data into XML
				for($x=0; $x<$primaryDataCount; $x++)
				{
					//chart data
					$chartXML .= "<set name='".$this->flashentities($primaryData[$x][$primaryDataKeys[0]])."' value='".$primaryData[$x][$primaryDataKeys[1]]."' color='".getFCColor(++$colorIndex)."' />";
				}//eof for
				break;
				
			//multiple / stacked series
			case 'multiple':
			case 'stacked':
				//convert primary data into XML
				for($x=0; $x<$primaryDataCount; $x++)
				{
					//chart category
					$multipleChartCategory .= "<category name='".$this->flashentities($primaryData[$x][$primaryDataKeys[0]])."' />";
					
					//loop on count of primary data columns (series)
					for($y=1; $y<$primaryDataKeysCount; $y++)
					{
						//chart dataset
						$primaryDataset[$y] .= "<set value='" . $primaryData[$x][$primaryDataKeys[$y]] . "' />";
					}//eof for
				}//eof for
				
				//loop on count of primary data columns (series)
				for($y=1; $y<$primaryDataKeysCount; $y++)
				{
					//append into chart data (create dataset)
					$primaryDataset[$y] = "<dataset seriesName='".$this->flashentities($primaryDataKeys[$y])."' color='".getFCColor(++$colorIndex)."'>".$primaryDataset[$y]."</dataset>";
				}//eof for
				
				//chart category
				$multipleChartCategory = "<categories>".$multipleChartCategory."</categories>";
				
				//Assemble the entire XML now
				$chartXML = $multipleChartCategory.implode('',$primaryDataset);
				break;
			
			//combination
			case 'combination':
				//convert primary data into XML
				for($x=0; $x<$primaryDataCount; $x++)
				{
					//chart category
					$multipleChartCategory .= "<category name='".$this->flashentities($primaryData[$x][$primaryDataKeys[0]])."' />";
					
					//loop on count of primary data columns (series)
					for($y=1; $y<$primaryDataKeysCount; $y++)
					{
						//chart dataset
						$primaryDataset[$y] .= "<set value='".$primaryData[$x][$primaryDataKeys[$y]]."' />";
					}//eof for
				}//eof for
				
				//loop on count of primary data columns (series)
				for($y=1; $y<$primaryDataKeysCount; $y++)
				{
					//append into chart data (create dataset)
					$primaryDataset[$y] = "<dataset seriesName='".$this->flashentities($primaryDataKeys[$y])."' parentYAxis='P' showValues='".$this->chartPrimaryShowValue."' color='".getFCColor(++$colorIndex)."'>".$primaryDataset[$y]."</dataset>";
				}//eof for
				
				//secondary data
				$secondaryData = $this->chartSecondaryData;
				$secondaryDataCount = count($secondaryData);
				
				//secondary keys
				$secondaryDataKeys = array_keys($secondaryData[0]);
				$secondaryDataKeysCount = count($secondaryDataKeys);
				
				//convert secondary data into XML
				for($x=0; $x<$secondaryDataCount; $x++)
				{
					//loop on count of secondary data columns (series)
					for($y=0; $y<$secondaryDataKeysCount; $y++)
					{
						//chart dataset
						$secondaryDataset[$y] .= "<set value='".$secondaryData[$x][$secondaryDataKeys[$y]]."' />";
					}//eof for
				}//eof for
				
				//loop on count of secondary data columns (series)
				for($y=0; $y<$secondaryDataKeysCount; $y++)
				{
					//append into chart data (create dataset)
					$secondaryDataset[$y] = "<dataset seriesName='".$this->flashentities($secondaryDataKeys[$y])."' parentYAxis='S' showValues='".$this->chartSecondaryShowValue."' color='".getFCColor(++$colorIndex)."'>".$secondaryDataset[$y]."</dataset>";
				}//eof for
				
				//chart category
				$multipleChartCategory = "<categories>".$multipleChartCategory."</categories>";
				
				//Assemble the entire XML now
				$chartXML = $multipleChartCategory.implode('',$primaryDataset).implode('',$secondaryDataset);
				break;
		}//eof switch
		
		$this->chartXML = "<graph caption='".$this->chartCaption."' xaxisname='".$this->chartXAxisLabel."' yaxisname='".$this->chartPrimaryYAxisLabel."' PYAxisName='".$this->chartPrimaryYAxisLabel."' SYAxisName='".$this->chartSecondaryYAxisLabel."' bgcolor='".$this->chartBgColor."' canvasBgColor='' showValues='".$this->chartPrimaryShowValue."' numberPrefix='".$this->chartNoPrefix."' formatNumberScale='0' decimalPrecision='".$this->chartDecimalPrecision."'>".$chartXML."</graph>";
	}//eof function
	
	//generate chart using HTML
	public function generateChartHTML()
	{
		//choose (Fusion Chart) Flash File
		$this->chooseFlashFile();
		
		//construct (Fusion Chart) Chart XML
		$this->constructChartXML();
		
		return renderChartHTML($this->chartFile, "", $this->chartXML, $this->chartId, $this->chartWidth, $this->chartHeight);
	}//eof function
	
	//generate chart using Javascript
	public function generateChartJS()
	{
		//choose (Fusion Chart) Flash File
		$this->chooseFlashFile();
		
		//construct (Fusion Chart) Chart XML
		$this->constructChartXML();
		
		?><script language="javascript" src="tools/fusionChart/FusionCharts.js"></script><?php
		return renderChart($this->chartFile, "", $this->chartXML, $this->chartId, $this->chartWidth, $this->chartHeight);
	}//eof function
	
	//================ SETTER & GETTER ================
	//set chart id
	public function setChartId($chartId)
	{
		$this->chartId = $chartId;
	}//eof function
	
	//get chart id
	public function getChartId()
	{
		return $this->chartId;
	}//eof function
	
	//set chart category
	public function setChartCategory($chartCategory)
	{
		$this->chartCategory = $chartCategory;
	}//eof function
	
	//get chart category
	public function getChartCategory()
	{
		return $this->chartCategory;
	}//eof function
	
	//set chart type
	public function setChartType($chartType)
	{
		$this->chartType = $chartType;
	}//eof function
	
	//get chart type
	public function getChartType()
	{
		return $this->chartType;
	}//eof function
	
	//set chart file
	public function setChartFile($chartFile)
	{
		$this->chartFile = $chartFile;
	}//eof function
	
	//get chart file
	public function getChartFile()
	{
		return $this->chartFile;
	}//eof function
	
	//set chart XML
	public function setChartXML($chartXML)
	{
		$this->chartXML = $chartXML;
	}//eof function
	
	//get chart XML
	public function getChartXML()
	{
		return $this->chartXML;
	}//eof function
	
	//set chart width
	public function setChartWidth($chartWidth)
	{
		$this->chartWidth = $chartWidth;
	}//eof function
	
	//get chart width
	public function getChartWidth()
	{
		return $this->chartWidth;
	}//eof function
	
	//set chart height
	public function setChartHeight($chartHeight)
	{
		$this->chartHeight = $chartHeight;
	}//eof function
	
	//get chart height
	public function getChartHeight()
	{
		return $this->chartHeight;
	}//eof function
	
	//set chart caption
	public function setChartCaption($chartCaption)
	{
		$this->chartCaption = $chartCaption;
	}//eof function
	
	//get chart caption
	public function getChartCaption()
	{
		return $this->chartCaption;
	}//eof function
	
	//set chart decimal precision flag
	public function setChartDecimalPrecision($chartDecimalPrecision)
	{
		$this->chartDecimalPrecision = $chartDecimalPrecision;
	}//eof function
	
	//get chart decimal precision flag
	public function getChartDecimalPrecision()
	{
		return $this->chartDecimalPrecision;
	}//eof function
	
	//set chart background color
	public function setChartBgColor($chartBgColor)
	{
		$this->chartBgColor = str_replace('#','',$chartBgColor);
	}//eof function
	
	//get chart background color
	public function getChartBgColor()
	{
		return $this->chartBgColor;
	}//eof function
	
	//set chart prefix character
	public function setChartNoPrefix($chartNoPrefix)
	{
		$this->chartNoPrefix = $chartNoPrefix;
	}//eof function
	
	//get chart prefix character
	public function getChartNoPrefix()
	{
		return $this->chartNoPrefix;
	}//eof function
	
	//set chart X-Axis Label
	public function setChartXAxisLabel($chartXAxisLabel)
	{
		$this->chartXAxisLabel = $chartXAxisLabel;
	}//eof function
	
	//get chart X-Axis Label
	public function getChartXAxisLabel()
	{
		return $this->chartXAxisLabel;
	}//eof function
	
	//set chart Primary Y-Axis Label
	public function setChartPrimaryYAxisLabel($chartPrimaryYAxisLabel)
	{
		$this->chartPrimaryYAxisLabel = $chartPrimaryYAxisLabel;
	}//eof function
	
	//get chart Primary Y-Axis Label
	public function getChartPrimaryYAxisLabel()
	{
		return $this->chartPrimaryYAxisLabel;
	}//eof function
	
	//set chart Secondary Y-Axis Label
	public function setChartSecondaryYAxisLabel($chartSecondaryYAxisLabel)
	{
		$this->chartSecondaryYAxisLabel = $chartSecondaryYAxisLabel;
	}//eof function
	
	//get chart Secondary Y-Axis Label
	public function getChartSecondaryYAxisLabel()
	{
		return $this->chartSecondaryYAxisLabel;
	}//eof function
	
	//set chart Primary Data
	public function setChartPrimaryData($chartPrimaryData)
	{
		$this->chartPrimaryData = $chartPrimaryData;
	}//eof function
	
	//get chart Primary Data
	public function getChartPrimaryData()
	{
		return $this->chartPrimaryData;
	}//eof function
	
	//set chart Secondary Data
	public function setChartSecondaryData($chartSecondaryData)
	{
		$this->chartSecondaryData = $chartSecondaryData;
	}//eof function
	
	//get chart Secondary Data
	public function getChartSecondaryData()
	{
		return $this->chartSecondaryData;
	}//eof function
	
	//set chart primary show value flag
	public function setChartPrimaryShowValue($chartPrimaryShowValue)
	{
		$this->chartPrimaryShowValue = $chartPrimaryShowValue;
	}//eof function
	
	//get primary chart show value flag
	public function getChartPrimaryShowValue()
	{
		return $this->chartPrimaryShowValue;
	}//eof function
	
	//set chart secondary show value flag
	public function setChartSecondaryShowValue($chartSecondaryShowValue)
	{
		$this->chartSecondaryShowValue = $chartSecondaryShowValue;
	}//eof function
	
	//get secondary chart show value flag
	public function getChartSecondaryShowValue()
	{
		return $this->chartSecondaryShowValue;
	}//eof function
	
	//set chart trend
	public function setChartTrend($chartTrendLabel, $chartTrendValue, $chartTrendColor)
	{
		$this->chartTrendLabel = $chartTrendLabel;
		$this->chartTrendValue = $chartTrendValue;
		$this->chartTrendColor = $chartTrendColor;
	}//eof function
	
	//get chart trend label
	public function getChartTrendLabel()
	{
		return $this->chartTrendLabel;
	}//eof function
	
	//get chart trend value
	public function getChartTrendValue()
	{
		return $this->chartTrendValue;
	}//eof function
	
	//get chart trend color
	public function getChartTrendColor()
	{
		return $this->chartTrendColor;
	}//eof function
	//============== EOF SETTER & GETTER ===============
}//eof class
?>