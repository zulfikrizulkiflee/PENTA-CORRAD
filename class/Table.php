<?php
require_once('HTML.php');
/**
* class Table
*/
class Table extends HTML
{
	//=====class members declaration================
	//attribute
	private $bgcolor;			//background color
	private $border;			//border
	private $bordercolor;		//bordercolor
	private $cellspacing;		//cellspacing
	private $cellpadding;		//cellpadding
	private $row;
	private $column;
	private $tableData;

	//class
	protected $tableHead;			//class for header
	protected $tableFoot;			//class for footer
	protected $tableRow;			//class for row
	protected $tableColumn;			//class for column

	//additional class
	protected $tableHeaderAt;		//array class for table column at
	protected $tableColumnAt;		//array class for table column at
	protected $tableDataAt;			//array class for table data at

	//=====class functions =========================
	//constructor
	public function __construct($width=null,$border=null,$cellspacing=null,$cellpadding=null)
	{
		$this->width = $width;
		$this->border = $border;
		$this->cellspacing = $cellspacing;
		$this->cellpadding = $cellpadding;
	}

	//set table's attribute
	public function setAttribute($attribute,$value)
	{
		switch(strtolower($attribute))
		{
			case 'bgcolor'		: $this->bgcolor = $value;
				break;
			case 'border'		: $this->border = $value;
				break;
			case 'bordercolor'	: $this->bordercolor = $value;
				break;
			case 'cellspacing'	: $this->cellspacing = $value;
				break;
			case 'cellpadding'	: $this->cellpadding = $value;
				break;
			default				: parent::setAttribute($attribute,$value);
		}//eof switch
	}//eof function

	//return table's attribute
	public function getAttribute($att='')
	{
		//if attribute not given, return all
		if($att=='')
		{
			if(isset($this->bgcolor))
				$value.=' bgcolor="'.$this->bgcolor.'"';

			if(isset($this->border))
				$value.=' border="'.$this->border.'"';

			if(isset($this->bordercolor))
				$value.=' bordercolor="'.$this->bordercolor.'"';

			if(isset($this->cellspacing))
				$value.=' cellspacing="'.$this->cellspacing.'"';

			if(isset($this->cellpadding))
				$value.=' cellpadding="'.$this->cellpadding.'"';

			$value.=parent::getAttribute();
		}//eof if
		//else, return by attribute given
		else
		{
			//switch / select attribute type
			switch(strtolower($att))
			{
				case 'bgcolor'		: $value=$this->bgcolor;
					break;
				case 'border'		: $value=$this->border;
					break;
				case 'bordercolor'	: $value=$this->bordercolor;
					break;
				case 'cellspacing'	: $value=$this->cellspacing;
					break;
				case 'cellpadding'	: $value=$this->cellpadding;
					break;
				default				: $value=parent::getattribute($att);
			}//eof switch
		}//eof else

		return $value;
	}//eof function

	//set the header attribute
	public function setHeaderAttribute($attribute, $value)
	{
		if(!isset($this->tableHead))
			$this->tableHead=new Header();

		$this->tableHead->setAttribute($attribute, $value);
	}

	//set the header's data
	public function setHeader($headerData)
	{
		if(!$this->tableHead)
			$this->tableHead=new Header();

		$this->tableHead->setHeader($headerData);
	}

	//added cikkim
	//append to header's data
	public function appendToHeader($dataToAppend)
	{
		$this->tableHead->setHeader($this->getHeader().$dataToAppend);
	}

	//return the header's data
	public function getHeader()
	{
		return $this->tableHead->getHeader();
	}

	//display the header
	public function showHeader()
	{
		if($this->tableHead)
			$this->tableHead->showHeader();
	}

	//set the footer attribute
	public function setFooterAttribute($attribute, $value)
	{
		if(!$this->tableFoot)
			$this->tableFoot=new Footer();

		$this->tableFoot->setAttribute($attribute, $value);
	}

	//set the footer's data
	public function setFooter($footerData)
	{
		if(!$this->tableFoot)
			$this->tableFoot=new Footer();

		$this->tableFoot->setFooter($footerData);
	}

	//return footer's data
	public function getFooter()
	{
		return $this->tableFoot->getFooter();
	}

	//display the footer
	public function showFooter()
	{
		if($this->tableFoot)
			$this->tableFoot->showFooter();
	}

	//set table row<t
	public function setRow($row)
	{
		$this->tableRow = $row;
	}

	//set table column
	public function setColumn($column)
	{
		$this->tableColumn = $column;
	}

	//create table given ist row and column
	public function createTable($row=1, $column=1)
	{
		$this->row=$row;
		$this->column=$column;
	}

	//set the table's data
	public function setTableData($tableData)
	{
		$this->tableData=$tableData;
	}

	//display the table
	public function showTable()
	{
		$this->open();

		if($this->tableHead)
			$this->showHeader();

		for($x=0;$x<$this->row;$x++)
		{
			if($this->tableRow)
				$this->tableRow->open($x);
			else
			{
				$this->tableRow=new Row();
				$this->tableRow->open($x);
			}

			for($y=0;$y<$this->column;$y++)
			{
				if($this->tableColumn)
					$this->tableColumn->open();
				else
				{
					$this->tableColumn=new Column();
					$this->tableColumn->open();
				}

				if($this->tableData[$x][$y])
					echo $this->tableData[$x][$y];
				else
					echo '&nbsp;';

				$this->tableColumn->close();
			}

			$this->tableRow->close();
		}

		$this->close();
	}

	//
	public function open()
	{
		echo '<table'.$this->getAttribute().'>';
	}

	//
	public function close()
	{
		echo '</table>
		';
	}

	//insert header at column index given the object
	public function insertHeaderAt($header, $columnIndex)
	{
		$tableHeaderAtCount=count($this->tableHeaderAt);

		$this->tableHeaderAt[$tableHeaderAtCount]['obj'] = $header;
		$this->tableHeaderAt[$tableHeaderAtCount]['col'] = $columnIndex;
	}//eof function

	//insert column at column index given the object
	public function insertColumnAt($column, $columnIndex)
	{
		$tableColumnAtCount=count($this->tableColumnAt);

		$this->tableColumnAt[$tableColumnAtCount]['obj'] = $column;
		$this->tableColumnAt[$tableColumnAtCount]['col'] = $columnIndex;
	}//eof function

	//insert data at row and column index given the object
	public function insertDataAt($row, $column, $value)
	{
		$tableDataAtCount=count($this->tableDataAt);

		$this->tableDataAt[$tableDataAtCount]['row'] = $row;
		$this->tableDataAt[$tableDataAtCount]['col'] = $column;
		$this->tableDataAt[$tableDataAtCount]['value'] = $value;
	}//eof function
}//eof class Table

/**
* class Row
*/
class Row extends Table
{
	private $rowspan;			//rowspan
	private $valign;			//vertical alignment

	//
	public function setAttribute($attribute,$value)
	{
		switch(strtolower($attribute))
		{
			case 'rowspan'		: $this->rowspan = $value;
				break;
			case 'valign'		: $this->valign = $value;
				break;
			default				: parent::setAttribute($attribute,$value);
				break;
		}
	}

	//
	public function getAttribute($att='')
	{
		//if attribute not given, return all
		if($att=='')
		{
			if(isset($this->rowspan))
				$value.=' rowspan="'.$this->rowspan.'"';

			if(isset($this->valign))
				$value.=' valign="'.$this->valign.'"';

			$value.=parent::getAttribute();
		}//eof if
		//else, return by attribute given
		else
		{
			//switch / select attribute type
			switch(strtolower($att))
			{
				case 'rowspan': $value=$this->rowspan;
					break;
				case 'valign': $value=$this->valign;
					break;
			}//eof switch
		}//eof else
		return $value;
	}

	//open row (tr)
	public function open($tr_id="0")
	{
		echo '<tr id="row_'.$tr_id.'" '.$this->getAttribute().' >';
	}//eof function

	//close row (/tr)
	public function close()
	{
		echo '</tr>';
	}//eof function
}//eof class Row

/**
* class Column
*/
class Column extends Table
{
	private $colspan;			//rowspan
	private $nowrap;			//nowrap
	private $valign;			//vertical alignment
	private $columnData;		//data of header

	//class for row
	protected $columnRow;

	//
	public function setAttribute($attribute,$value)
	{
		switch(strtolower($attribute))
		{
			case 'colspan'		: $this->colspan = $value;
				break;
			case 'nowrap'		: $this->nowrap = $value;
				break;
			case 'valign'		: $this->valign = $value;
				break;
			default				: parent::setAttribute($attribute,$value);
				break;
		}//eof switch
	}//eof function

	//
	public function getAttribute($att='')
	{
		//if attribute not given, return all
		if($att=='')
		{
			if(isset($this->colspan))
				$value.=' colspan="'.$this->colspan.'"';

			if(isset($this->nowrap))
				$value.=' nowrap="'.$this->nowrap.'"';

			if(isset($this->valign))
				$value.=' valign="'.$this->valign.'"';

			$value.=parent::getAttribute();
		}//eof if
		//else, return by attribute given
		else
		{
			//switch / select attribute type
			switch(strtolower($att))
			{
				case 'colspan'	: $value=$this->colspan;
				break;

				case 'nowrap'	: $value=$this->nowrap;
				break;

				case 'valign'	: $value=$this->valign;
				break;

				default			: $value=parent::getattribute($att);
				break;
			}//eof switch
		}//eof else

		return $value ;
	}//eof function

	//
	public function open()
	{
		echo '<td'.$this->getAttribute().'>';
	}

	//
	public function close()
	{
		echo '</td>';
	}

	//
	public function addColumn($data)
	{
		$this->open();
		echo $data;
		$this->close();
	}

	//
	public function setColumnAttribute($attribute, $value)
	{
		$this->setAttribute($attribute, $value);
	}

	//
	public function setColumnRow($row)
	{
		$this->columnRow=$row;
	}

	//
	public function setColumn($columnData)
	{
		$this->columnData=$columnData;
	}

	//
	public function getColumn()
	{
		return $this->columnData;
	}

	//
	public function showColumn()
	{
		$column='';

		if(!$this->columnRow)
			$this->columnRow = new Row();

		$this->columnRow->open();

		//if array
		if(is_array($this->columnData))
		{
			//count of data
			$columnCount=count($this->columnData);

			//if multiple array
			if(is_array($this->columnData[0]))
			{
				//name/index of array
				$keysList=array_keys($this->columnData[0]);

				for($x=0; $x<$columnCount; $x++)
				{
					$this->open();
					echo $this->columnData[$x][$keysList[0]];
					$this->close();
				}
			}//eof if
			else
			{
				//name/index of array
				$keysList=array_keys($this->columnData);

				for($x=0; $x<$columnCount; $x++)
				{
					$this->open();
					echo $this->columnData[$keysList[$x]];
					$this->close();
				}
			}//eof else
		}//eof if

		else
		{
			$this->open();
			echo $this->columnData;
			$this->close();
		}//eof else

		$this->columnRow->close();
	}//eof function
}//eof class Column

/**
* class Header
*/
class Header extends Column
{
	private $headerData;	//data of header

	//class for row
	protected $headerRow;

	//
	public function open()
	{
		echo '<th'.$this->getAttribute().'>';
	}

	//
	public function close()
	{
		echo '</th>';
	}

	//
	public function setHeaderAttribute($attribute, $value)
	{
		$this->setAttribute($attribute, $value);
	}

	//
	public function setHeaderRow($row)
	{
		$this->setColumnRow($row);
	}

	//
	public function setHeader($headerData)
	{
		$this->setColumn($headerData);
	}

	//
	public function getHeader()
	{
		return $this->getColumn();
	}

	public function showHeader()
	{
		$this->showColumn();
	}//eof function
}//eof class Header

/**
* class Footer
*/
class Footer extends Column
{
	private $footerData;	//data of footer

	//class for row
	protected $footerRow;

	//
	public function setFooterAttribute($attribute, $value)
	{
		$this->setAttribute($attribute, $value);
	}

	//
	public function setFooterRow($row)
	{
		$this->setColumnRow($row);
	}

	//
	public function setFooter($footerData)
	{
		$this->setColumn($footerData);
	}

	//
	public function getFooter()
	{
		return $this->getColumn();
	}

	public function showFooter()
	{
		echo '<tfoot>';
		$this->showColumn();
		echo '</tfoot>';
	}//eof function
}//eof class Footer

/**
* class TableGrid
*/
class TableGrid extends Table
{
	//initial members variable
	private $tableGridData;			//data of tablegrid
	private $numoflimit;			//number of limit (rows limit)
	private $numofrepeat;			//number of repeating (for rows)
	private $addRowClass;			//set class for button add row
	private $addRowId;				//set id for button add row
	private $addRowValue;			//set value for button add row
	private $addRowStatus;			//set status of add row function
	private $addRowType;			//set type of add row
	private	$addRowJs;				//javascript for delete row
	private	$addRowDisabled;		//enable/disable
	private $delRowClass;			//set class for button delete row
	private $delRowId;				//set id for button delete row
	private $delRowValue;			//set value for button delete row
	private $delRowStatus;			//set status of delete row function
	private	$delRowJs;				//javascript for delete row
	private	$delRowDisabled;		//enable/disable
	private $keysStatus;			//status of array keys
	private $runningStatus;			//status of running number
	private $runningKey;			//key of running number
	private $pagingType;			//set type of paging
	private $pagingClass;			//set class of paging type
	private $pagingCss;				//set css for paging
	private $sortStatus;			//set status of sorting
	private $sortIndex;				//index of sorting

	//class variable
	private $primaryRow;			//primary row
	private $secondaryRow;			//secondary row
	private $primaryColumn;			//primary column
	private $secondaryColumn;		//secondary column
	private $tableKeys;				//keys

	//additional class
	private $tableKeysAt;

	//set the tablegrid data
	public function setTableGridData($tableGridData)
	{
		$this->tableGridData=$tableGridData;

		//if data in array and keys hasn't been set
		if(is_array($tableGridData) && !$this->getKeys())
			$this->setKeys(array_keys($tableGridData[0]));			//set the data keys
	}//eof function

	//set the tablegrid data
	public function getTableGridData()
	{
		return $this->tableGridData;
	}//eof function

	//show the tablegrid by given input or query
	public function showTableGrid($input='')
	{
		//if data has been set
		if($this->tableGridData)
		{
			//data
			$array=$this->tableGridData;

			//read keys of array
			if(is_array($array[0]))
				$keysList=array_keys($array[0]);
			else
				$keysList=$array;
		}
		else
		{
			$array=$input;

			//read keys of array
			if(is_array($array[0]))
				$keysList=array_keys($array[0]);
			else
				$keysList=$array;

			//if keysStatus true
			if($this->keysStatus)
				$this->setKeys($keysList);
		}

		//set row and column count
		$rowCount=count($array);		//count row
		$columnCount=count($array[0]);	//count column

		//if not set limit
		if(!isset($this->numoflimit))
			$this->numoflimit=$rowCount;	//limit equal to number of row

		//if running number is true
		if($this->runningStatus)
		{
			//if header have array data
			if($this->tableHead && is_array($this->tableHead->getHeader()))
			{
				$tempHeadData=$this->tableHead->getHeader();	//get header data
				$tempHeadData[-1]='';							//add data for numbering
				ksort($tempHeadData);							//sort into correct ordering

				$this->tableFoot->setHeader($tempHeadData);		//re-set the header data
			}//eof if
			else if($this->tableHead && $this->tableHead->getHeader())	//if header have value
				if($this->tableHead->getattribute('colspan'))		//if header have colspan
					$this->tableHead->setAttribute('colspan',$this->tableHead->getattribute('colspan')+1);	//increment colspan by 1
		}//eof if

		//if sorting is true
		if($this->sortStatus)
		{
			//loop by num of row
			for($x=0; $x<$rowCount; $x++)
				$tempSort[$x]=$array[$x][$keysList[$this->sortIndex]];		//set indexed data into temporary for sorting

			//if array valid in temporary
			if(is_array($tempSort))
			{
				natcasesort($tempSort);						//sort data by column index
				$tempSortKeys=array_keys($tempSort);		//get keys of sorted data (as temporary variable)
			}//eof if
		}//eof if

		//generate random id for tableGrid (currently used for addRow)
		$randTableGrid = rand();

		//generate the tablegrid
		/*======>start table<======*/
		$this->open();			//open table

		echo '<thead>';
		//if have header
		if($this->tableHead)
			$this->showHeader();	//show header

		//if have keys
		if($this->keysStatus && $this->getKeys())
			$this->showKeys();		//show keys
		echo '</thead>';

		//check class Row
		if(!$this->tableRow)
			$this->tableRow=new Row();

		//check class Column
		if(!$this->tableColumn)
			$this->tableColumn=new Column();

		//if array true
		if(is_array($array[0]))
		{
			//for repeating data
			$a=0;	//initial state of do while
			do{
				//loop by num of row
				for($x=0; $x<$rowCount; $x++)
				{
					/*======>start open paging section<======*/
					if(($this->numoflimit && $rowCount>$this->numoflimit && $x%$this->numoflimit==0)||$x==0)
					{
						if(!$randNum)
							$randNum=rand();		//get random number

						$currentLayer='tableGridLayer'.$randNum.(++$layer);		//current layer
					?>
						<tbody id="<?php echo $currentLayer;?>" <?php if($x!=0){ ?>style="display:none"<?php }?>>
					<?php }//eof if
					/*=======>end open paging section<=======*/

					/*======>start row<======*/
					$tr_id = $x + 1 ;
					$this->tableRow->open($tr_id );		//open row

					//if running number true
					if($this->runningStatus)
					{
						//set numbering
						if($this->numofrepeat>0)
							$numbering=$a+1 .'.';	//repeat
						else
							$numbering=$x+1 .'.';	//count

						//add column and display numbering
						$tempColumn=new Column();					//create new object for column
						$tempColumn= clone $this->tableColumn;		//clone new object with existing object
						$tempColumn->setAttribute('width','10');	//set new attribute for width
						$tempColumn->addColumn($numbering);			//add / show the column
					}//eof if

					//data / column of datas will display here
					for($y=0; $y<$columnCount;$y++)
					{
						//if sorting is true
						if($this->sortStatus)
							$row=$tempSortKeys[$x];
						else
							$row=$x;

						//if empty data
						if($array[$row][$keysList[$y]]=='')
							$array[$row][$keysList[$y]]=' ';	 //$array[$x][$keysList[$y]]='&nbsp;';		//set whitespace if no data

						/*======>start column<======*/
						$this->tableColumn->open();				//open column
						/*
							previously disabled because of conflict if user use label item
							now re-enabled back to test have error or not
						*/
						$displaydata = $array[$row][$keysList[$y]];

						if (URL_SECURITY)
							if ( $displaydata != "" )
								$displaydata = href_encoding( $displaydata );

						echo $displaydata;
						//echo $array[$row][$keysList[$y]];		//display the data
						$this->tableColumn->close();			//close column
						/*======>end column<======*/
					}//eof for

					$this->tableRow->close();			//close row
					/*======>end row<======*/

					/*======>start close paging section<======*/
					if(($this->numoflimit && $rowCount>$this->numoflimit && ($x+1)%$this->numoflimit==0) || $x+1 == $rowCount)
					{?>
						</tbody>
					<?php }
					/*======>end close paging section<======*/
				}//eof for
				$a++;
			}while($a<$this->numofrepeat);//eof do while
		}//eof if array
		else
		{
			$this->tableColumn->open();				//open column
			echo $array;							//display the data
			$this->tableColumn->close();			//close column
		}//eof else

		//if add row is true and type is ajax
		if($this->addRowStatus&&$this->addRowType=='ajax')
		{
			//======== ROW TO APPEND =======
			echo '<tbody id="addRow'.$randTableGrid.'"></tbody>';		//this is where the appendable row will display
			//====== EOF TO ROW APPEND =====
		}//eof if
		//else if add row is true and type is ajax
		else if($this->addRowStatus&&$this->addRowType=='js')
		{
			//======== ROW TO APPEND =======
			//currently loop defaulted to 10
			for($x=0;$x<10;$x++)
			{
				//set new attribute for append row
				$this->tableRow->setAttribute('id','addRow'.$randTableGrid.'_'.($x));
				$this->tableRow->setAttribute('style','display:none');

				$this->tableRow->open($x);			//open row

				//if running number true
				if($this->runningStatus)
				{
					//add column and display numbering
					$tempColumn=new Column();					//create new object for column
					$tempColumn= clone $this->tableColumn;		//clone new object with existing object
					$tempColumn->setAttribute('width','10');	//set new attribute for width
					$tempColumn->addColumn($row+2+$x.'.');			//add / show the column
				}//eof if

				//loop on count of keylist
				for($y=0;$y<$columnCount;$y++)
				{
					//current id
					$begin = explode('id="',$array[$row][$keysList[$y]]);

					//loop on number of id exist
					for($z=1; $z<count($begin); $z++)
					{
						$end = explode('"',$begin[$z]);
						$idOld = $end[0];

						//new id
						$idChunk = explode('_',$idOld);
						$idNew = $idChunk[0].'_'.$idChunk[1].'_'.$idChunk[2].'_'.$idChunk[3].'_'.($rowCount+$x);
						$idHiddenArr[$x][] = $idNew;

						//change id
						$array[$row][$keysList[$y]] = str_replace($idOld,$idNew,$array[$row][$keysList[$y]]);
					}//eof for

					$this->tableColumn->open();				//open column
					echo $array[$row][$keysList[$y]];		//display the data
					$this->tableColumn->close();			//close column

					//loop on count of new id / idhiddenarr
					for($z=0; $z<count($idHiddenArr[$x]); $z++)
					{
					?>
						<script>
							if(document.getElementById('<?php echo $idHiddenArr[$x][$z];?>'))
								document.getElementById('<?php echo $idHiddenArr[$x][$z];?>').disabled=true;
						</script>
					<?php
					}//eof for
				}//eof for

				$this->tableRow->close();			//close row
			}//eof for
			//====== EOF TO ROW APPEND =====
		}//eof if

		//if running number is true (footer)
		if($this->runningStatus)
		{
			//footer have array data
			if($this->tableFoot && is_array($this->tableFoot->getFooter()))
			{
				$tempFootData=$this->tableFoot->getFooter();	//get footer data
				$tempFootData[-1]='';							//add data for numbering
				ksort($tempFootData);							//sort into correct ordering

				$this->tableFoot->setFooter($tempFootData);		//re-set the footer data
			}//eof if
			else if($this->tableFoot && $this->tableFoot->getFooter())	//if footer have value
				if($this->tableFoot->getattribute('colspan'))		//if footer have colspan
					$this->tableFoot->setAttribute('colspan',$this->tableFoot->getattribute('colspan')+1);	//increment colspan by 1
		}//eof if

		//if have footer
		if($this->tableFoot)
			$this->showFooter();	//show footer

		$this->close();				//close table
		/*======>end table<======*/
		//======== PAGING SECTION =======
		//if rowcount bigger than page limit
		if($rowCount>$this->numoflimit)
		{
			//temporary class for paging
			$tempPage=new TableGrid();							//create new class as temporary for paging row
			$tempPage=clone $this;								//clone current table
			$tempPage->setAttribute('class','tableContent flcReportPaging');
			$tempPage->setAttribute('border','0');				//set border 0
			$tempPage->setAttribute('style','border-top:none');		//set border 0 in css

			$tempPage->open();		//ppen table
			?>
			<tr>
			<td align="left" style="border-right:none">
				<span id="hidePaging<?php echo $randNum;?>"><input name="showAllButton" type="button" value="[ + ]" class="inputButton" onclick="toggleLayer('showPaging<?php echo $randNum;?>|<?php for($y=1; $y<($layer+1); $y++){?>tableGridLayer<?php echo $randNum.$y;?>|<?php }?>','hidePaging<?php echo $randNum;?>|<?php for($y=1; $y<($layer+1); $y++){?>triggerLayer<?php echo $randNum.$y;?>|<?php }?>')" style="margin-top:5px;" /></span>
				<span id="showPaging<?php echo $randNum;?>" style="display:none"><input name="restorePagingButton" type="button" value="[ - ]" class="inputButton" onclick="toggleLayer('hidePaging<?php echo $randNum;?>|triggerLayer<?php echo $randNum;?>1','showPaging<?php echo $randNum;?>|<?php for($y=1; $y<($layer+1); $y++){if($y!=1){?>tableGridLayer<?php echo $randNum.$y;?>|<?php }}?>')" style="margin-top:5px;" /></span>
			</td>
			<td align="right" style="border-left:none;">
			  <?php
				for($x=1; $x<($layer+1); $x++)
				{
					?>
					<span id="triggerLayer<?php echo $randNum.$x;?>" <?php if($x!=1){ ?> style="display:none"<?php }?>>

					<?php if($x!=1){ ?>
						<input name="" type="button" value="<<" class="inputButton" onclick="toggleLayer('triggerLayer<?php echo $randNum.(1);?>|tableGridLayer<?php echo $randNum.(1);?>','<?php for($y=1; $y<($layer+1); $y++){if($y!=1){?>triggerLayer<?php echo $randNum.$y;?>|tableGridLayer<?php echo $randNum.$y;?>|<?php }}?>')" style="margin-top:5px" />
						&nbsp;<input name="" type="button" value="<?php echo $x-1;?>" class="inputButton" onclick="toggleLayer('triggerLayer<?php echo $randNum.($x-1);?>|tableGridLayer<?php echo $randNum.($x-1);?>','<?php for($y=1; $y<($layer+1); $y++){if($y!=$x-1){?>triggerLayer<?php echo $randNum.$y;?>|tableGridLayer<?php echo $randNum.$y;?>|<?php }}?>')" style="margin-top:5px" />
					<?php }?>
					<input name="" type="button" value="<?php echo  $x;?>" class="inputButton" style="margin-top:5px; color:#999999" />
					<?php if($x!=$layer){ ?>
						<input name="" type="button" value="<?php echo $x+1;?>" class="inputButton" onclick="toggleLayer('triggerLayer<?php echo $randNum.($x+1);?>|tableGridLayer<?php echo $randNum.($x+1);?>','<?php for($y=1; $y<($layer+1); $y++){if($y!=$x+1){?>triggerLayer<?php echo $randNum.$y;?>|tableGridLayer<?php echo $randNum.$y;?>|<?php }}?>')" style="margin-top:5px" />
						&nbsp;<input name="" type="button" value=">>" class="inputButton" onclick="toggleLayer('triggerLayer<?php echo $randNum.($layer);?>|tableGridLayer<?php echo $randNum.($layer);?>','<?php for($y=1; $y<($layer+1); $y++){if($y!=$layer){?>triggerLayer<?php echo $randNum.$y;?>|tableGridLayer<?php echo $randNum.$y;?>|<?php }}?>')" style="margin-top:5px" />
					<?php }?>
					</span>
					<?php
				}
				?>
			</td>
			</tr>
			<?php

			$tempPage->close();	//close table
		}//eof if
		//====== EOF PAGING SECTION =====

		//======== ADD ROW TRIGGER SECTION =======
		if($this->addRowStatus)
		{
			//if numbering true
			if($this->runningStatus)
			{
				?>
				<script language="javascript">
				var addRow<?php echo $randTableGrid;?>RunningNumber = <?php echo str_replace('.','',$numbering);?>;
				</script>
				<?php
			}//eof if

			//set delimiter between column
			$tempInput=implode('<databreak>',$array[$rowCount-1]);

			//replace " with \' for javascript (attribute)
			//$tempAtt=str_replace('"','\\\'',$this->tableColumn->getAttribute());
			$tempAtt=str_replace('"','[DQ]',$this->tableColumn->getAttribute());

			//replace quote
			//$tempInput=str_replace('"','\\\'',$tempInput);	//" sign
			$tempInput=str_replace("\n",' ',$tempInput);		//" sign
			$tempInput=str_replace("\r",' ',$tempInput);		//" sign
			$tempInput=str_replace("\n\n",' ',$tempInput);		//" sign
			$tempInput=str_replace('"','[DQ]',$tempInput);		//" sign
			$tempInput=str_replace('\'','[SQ]',$tempInput);		//" sign

			//replace for javascript (input)
			$tempInput=str_replace('&','\\\'amp',$tempInput);		//& sign
			$tempInput=str_replace('#','\\\'hash',$tempInput);		//# sign
			$tempInput=str_replace('%','\\\'percent',$tempInput);	//% sign
			$tempInput=str_replace('?','\\\'questionmark',$tempInput);	//? sign

			//temporary class for addrow
			$tempT = new TableGrid();							//create new class as temporary for append row
			$tempT = clone $this;								//clone current table
			$tempT->setAttribute('class','tableContent flcReportButton');
			$tempT->setAttribute('border','0');				//set border 0
			$tempT->setAttribute('style','border-top:none');		//set border 0 in css

			$tempT->open();		//open table

			//=====================JAVASCRIPT=================
			//if add row have javascript
			if(is_array($this->addRowJs))
			{
				//count javascript added
				$addRowJsCount=count($this->addRowJs['event']);

				//loop on count of js
				for($y=0; $y<$addRowJsCount; $y++)
				{
					//wsitch event
					switch(strtoupper($this->addRowJs['event'][$y]))
					{
						case 'ONCLICK':
							//replace any double quote (") with single quote (') and append to javascript event
							$onclick['add'].=str_replace('"','\'',$this->addRowJs['js'][$y]).';';
						break;
					}//eof switch
				}//eof for
			}//eof if addRowJs

			//if delete row have javascript
			if(is_array($this->delRowJs))
			{
				//count javascript added
				$delRowJsCount=count($this->delRowJs['event']);

				//loop on count of js
				for($y=0; $y<$delRowJsCount; $y++)
				{
					//wsitch event
					switch(strtoupper($this->delRowJs['event'][$y]))
					{
						case 'ONCLICK':
							//replace any double quote (") with single quote (') and append to javascript event
							$onclick['delete'].=str_replace('"','\'',$this->delRowJs['js'][$y]).';';
						break;
					}//eof switch
				}//eof for
			}//eof if delRowJs
			//===================EOF JAVASCRIPT===============
			?>

			<tr><td align="right">
			<script>
				var addRow<?php echo $randTableGrid;?>_refNum=0;
				var addRow<?php echo $randTableGrid;?>_item = new Array();

				<?php for($x=0;$x<count($idHiddenArr);$x++){?>
					addRow<?php echo $randTableGrid;?>_item[<?php echo $x;?>] = new Array();

					<?php for($y=0;$y<count($idHiddenArr[0]);$y++){?>
						if(document.getElementById('<?php echo $idHiddenArr[$x][$y];?>'))
						{
							addRow<?php echo $randTableGrid;?>_item[<?php echo $x;?>][<?php echo $y;?>] = document.getElementById('<?php echo $idHiddenArr[$x][$y];?>');
						}
					<?php }?>
				<?php }?>
			</script>
			<?php if($this->addRowStatus){?>
			<input type="button" id="<?php echo $this->addRowId;?>" name="<?php echo $this->addRowId;?>" class="<?php echo $this->addRowClass;?>" value="<?php echo $this->addRowValue;?>" <?php if($this->addRowDisabled){?> disabled="disabled"<?php }?> style="margin-top:5px; <?php if($this->addRowDisabled){?> color:#666666;<?php }?>"
				onclick="
				<?php
					//user set js
					echo $this->addRowJs['js']['0'];

					//if ajax add row
					if($this->addRowType=='ajax'){?>
						appendNewRow('addRow<?php echo $randTableGrid;?>','tableRowSub<?php echo $randTableGrid;?>','<?php echo $tempAtt;?>','<?php echo $tempInput;?>',++addRow<?php echo $randTableGrid;?>RunningNumber);
					<?php }
					//else if js add row
					else if($this->addRowType=='js'){?>

						if(document.getElementById('addRow<?php echo $randTableGrid;?>_'+(addRow<?php echo $randTableGrid;?>_refNum)))
						{
							document.getElementById('addRow<?php echo $randTableGrid;?>_'+(addRow<?php echo $randTableGrid;?>_refNum)).style.display='';

							var temp = addRow<?php echo $randTableGrid;?>_item;
							var tempRowCount = temp.length;
							var tempColumnCount = temp[0].length;

							for(y=0;y<tempColumnCount;y++)
								temp[addRow<?php echo $randTableGrid;?>_refNum][y].disabled=false;

							addRow<?php echo $randTableGrid;?>_refNum++;
						}
					<?php }?>

					">
			<?php }?>
			<?php if($this->delRowStatus){?>
			<input type="button" id="<?php echo $this->delRowId;?>" name="<?php echo $this->delRowId;?>" class="<?php echo $this->delRowClass;?>" value="<?php echo $this->delRowValue;?>" <?php if($this->delRowDisabled){?> disabled="disabled"<?php }?>  style="margin-top:5px; <?php if($this->delRowDisabled){?> color:#666666;<?php }?>"
				onclick="
					<?php
					//user set js
					echo $this->delRowJs['js']['0'];

					//if ajax add row
					if($this->addRowType=='ajax'){?>
						var a=document.getElementById('addRow'+<?php echo $randTableGrid;?>).getElementsByTagName('tr');

						if(a.length>0)
                        {
							a[a.length-1].remove();
                            addRow<?php echo $randTableGrid;?>RunningNumber--;
                        }
					<?php }
					//else if js delete row
					else if($this->addRowType=='js'){?>
						if(document.getElementById('addRow<?php echo $randTableGrid;?>_'+(addRow<?php echo $randTableGrid;?>_refNum-1)))
						{
							addRow<?php echo $randTableGrid;?>_refNum--;
							document.getElementById('addRow<?php echo $randTableGrid;?>_'+(addRow<?php echo $randTableGrid;?>_refNum)).style.display='none';

							var temp = addRow<?php echo $randTableGrid;?>_item;
							var tempRowCount = temp.length;
							var tempColumnCount = temp[0].length;

							for(y=0;y<tempColumnCount;y++) {
								temp[addRow<?php echo $randTableGrid;?>_refNum][y].disabled=true;
								temp[addRow<?php echo $randTableGrid;?>_refNum][y].value='';
							}

						}
					<?php }?>">
			<?php }?>
			</td></tr>
			<!--<div id=randTableGrid value=<?php //echo $randTableGrid;?> ???-->
			<?php
			$tempT->close();	//close table
		}//eof if addrowstatus true
		//====== EOF ADD ROW TRIGGER SECTION =====
	}//eof function

	//set number of limit for data
	public function setLimit($numoflimit=0)
	{
		if($numoflimit>=0)
			$this->numoflimit=$numoflimit;
	}//eof function

	//set number of repeating for data
	public function setRepeat($numofrepeat=0)
	{
		if($numofrepeat>=0)
			$this->numofrepeat=$numofrepeat;
	}//eof function

	//set status of running number
	public function setRunningStatus($runningStatus=false)
	{
		$this->runningStatus=$runningStatus;
	}//eof function

	//set key of running number
	public function setRunningKeys($runningKey)
	{
		$this->runningKey=$runningKey;
	}//eof function

	//set status of array keys
	public function setKeysStatus($keysStatus)
	{
		$this->keysStatus=$keysStatus;
	}//eof function

	//set attribute of array keys column
	public function setKeysAttribute($attribute, $value)
	{
		//if class not available
		if(!isset($this->tableKeys))
			$this->tableKeys=new Header();

		$this->tableKeys->setAttribute($attribute, $value);
	}//eof function

	//set the tablegrid keys
	public function setKeys($keysList)
	{
		//if class not available
		if(!isset($this->tableKeys))
			$this->tableKeys=new Column();

		$this->tableKeys->setColumn($keysList);
	}//eof function

	//return the tablegrid keys
	public function getKeys()
	{
		return $this->tableKeys->getColumn();
	}//eof function

	//display the tablegrid keys
	public function showKeys()
	{
		//if have table keys
		if($this->tableKeys)
		{
			//if running number true
			if($this->runningStatus)
			{
				//set keys for running number
				if($this->runningKey)
					$runningKey=$this->runningKey;
				else
					$runningKey='#';

				//if array current keys true
				if(is_array($this->tableKeys->getColumn()))
				{
					//get current key
					$tempKey=$this->tableKeys->getColumn();		//get data of column
					$tempKey[-1]=$runningKey;					//put new key (head) for running number
					ksort($tempKey);							//sort the key -> put running number in front

					//re-set the keys
					$this->tableKeys->setColumn($tempKey);
				}//eof if
			}//eof if

			//show keys
			$this->tableKeys->showColumn();
		}//eof keys
	}//eof function

	//insert keys at column index given the object
	public function insertKeysAt($keys, $value)
	{
		$tableKeysAtCount=count($this->tableKeysAt);

		$this->tableKeysAt[$tableDataAtCount]['obj'] = $keys;
		$this->tableKeysAt[$tableDataAtCount]['col'] = $column;
	}//eof function

	//----- add row -------

	public function setAddRowId($addRowId)
	{
		$this->addRowId=$addRowId;
	}//eof function


	public function setAddRowValue($addRowValue='Add New Row')
	{
		$this->addRowValue=$addRowValue;
	}//eof function

	//set usage of appendable row / status(boolean) and its name / button value
	public function setAddRowStatus($addRowStatus)
	{
		$this->addRowStatus=$addRowStatus;
	}//eof function


	//set type of appendable row (ajax/js)
	public function setAddRowType($addRowType='ajax')
	{
		$this->addRowType=strtolower($addRowType);
	}//eof function

	//enable/disable
	public function setAddRowDisabled($addRowDisabled)
	{
		$this->addRowDisabled=$addRowDisabled;
	}//eof function

	//set html class for appendable row button
	public function setAddRowClass($addRowClass)
	{
		$this->addRowClass=$addRowClass;
	}//eof function

	//add javascript for add appendable row button
	public function addAddRowJs($event, $js)
	{
		//if javascript not end with semi colon (;), add semi colon (;)
		if(substr($js,-1,1)!=';')
			$js.=';';

		//if hav array
		if(is_array($this->addRowJs))
		{
			//push javascript
			array_push($this->addRowJs['event'],$event);
			array_push($this->addRowJs['js'],$js);
		}//eof if
		else
		{
			//1st javascript
			$this->addRowJs['event'][0]=$event;
			$this->addRowJs['js'][0]=$js;
		}//eof else
	}//eof function
	//----- eof add row -------

	//----- delete row -------

	public function setDelRowId($delRowId)
	{
		$this->delRowId=$delRowId;
	}//eof function


	public function setDelRowValue($delRowValue='Delete Row')
	{
		$this->delRowValue=$delRowValue;
	}//eof function

	//set usage of appendable row / status(boolean) and its name / button value
	public function setDelRowStatus($delRowStatus)
	{
		$this->delRowStatus=$delRowStatus;
	}//eof function

	//enable/disable
	public function setDelRowDisabled($delRowDisabled)
	{
		$this->delRowDisabled=$delRowDisabled;
	}//eof function

	//set html class for delete appendable row button
	public function setDelRowClass($delRowClass)
	{
		$this->delRowClass=$delRowClass;
	}//eof function

	//add javascript for delete appendable row button
	public function addDelRowJs($event, $js)
	{
		//if javascript not end with semi colon (;), add semi colon (;)
		if(substr($js,-1,1)!=';')
			$js.=';';

		//if have array
		if(is_array($this->delRowJs))
		{
			//push javascript
			array_push($this->delRowJs['event'],$event);
			array_push($this->delRowJs['js'],$js);
		}//eof if
		else
		{
			//1st javascript
			$this->delRowJs['event'][0]=$event;
			$this->delRowJs['js'][0]=$js;
		}//eof else
	}//eof function
	//----- eof delete row -------

	//set paging type and class
	function setPagingDisplay($pagingType, $pagingClass='', $pagingCss='')
	{
		$this->pagingType=$pagingType;
		$this->pagingClass=$pagingClass;
		$this->pagingCss=$pagingCss;
	}//eof function

	//set sort index
	function sortIndex($sortStatus, $index='0')
	{
		$this->sortStatus=$sortStatus;
		$this->sortIndex=$index;
	}//eof function
}//eof class TableGrid
?>

<script language="javascript">
//to toggle between enabled and disabled layer
function toggleLayer(layerEnable,layerDisable)
{
	//split the layer into array
	layerEnable = layerEnable.split('|')
	layerDisable = layerDisable.split('|')

	//loop on size of array
	for(x=0; x<layerEnable.length; x++)
	{
		//enable
		if(document.getElementById(layerEnable[x]))
			document.getElementById(layerEnable[x]).style.display='';	//display
	}

	//loop on size of array
	for(x=0; x<layerDisable.length; x++)
	{
		//disable
		if(document.getElementById(layerDisable[x]))
			document.getElementById(layerDisable[x]).style.display='none';	//hide
	}
}
</script>

<script language="javascript">
//== JAVASCRIPT FUNCTION TO APPEND NEW ROW===
function getFailureReport(request)
{
	$F('locationDiv') = "Error";
}

//append the row
function appendTabularRow(rowToAdd,colAtt,component,numbering)
{
	var url = 'class/Table.php';	//refer to same page as class
	var params = 'colAtt=' + colAtt + '&component=' + component + '&numbering=' + numbering;
	var ajax = new Ajax.Updater({success: rowToAdd},url,{method: 'post', parameters: params, onFailure: getFailureReport, evalScripts: true});
}

//function to add new row in table
function appendNewRow(blockName,newRowSubName,colAtt,component,numbering)
{
	//get table row element reference
	var tBody = document.getElementById(blockName);	//tbody reference
	var oRows = tBody.getElementsByTagName('tr');
	var iRowCount = oRows.length;					//count number rows length (number of rows)
	var newTR = document.createElement('tr');		//create new row

	newRowSubName += iRowCount;
	newTR.id = newRowSubName;		//assign name to newly create row
	tBody.appendChild(newTR);						//append tr to tbody

	//call ajax function to add items in the array
	appendTabularRow(newRowSubName,colAtt,component,numbering);
}
//== EOF JAVASCRIPT FUNCTION TO APPEND NEW ROW===
</script>

<?php
//======== APPENDED ROW SECTION =======
//if attribute and component is posted
if($_POST['colAtt']&&$_POST['component'])
{
	//re-assign post data
	$attribute=$_POST['colAtt'];		//attribute for column
	$input=$_POST['component'];			//string of concated input or component

	//replace \' with " for php (original syntax)
	//$attribute=str_replace('\\\'','"',$attribute);
	$attribute=str_replace('[DQ]','"',$attribute);

	//replace for php (original syntax)
	$input=str_replace('\'hash','#',$input);			//# sign
	$input=str_replace('\'amp','&',$input);			//& signpercent
	$input=str_replace('\'percent','%',$input);		//% sign
	$input=str_replace('\'questionmark','?',$input);	//? sign

	//quote
	//$input=str_replace('\\\'','"',$input);		//" sign
	$input=str_replace('[DQ]','"',$input);			//" sign
	$input=str_replace('[SQ]','\'',$input);			//" sign

	//explode by '~~' sign (separate input)
	$output=explode('<databreak>',$input);
	$outputCount=count($output);		//count ouput

	//if numbering is sent
	if($_POST['numbering'])
		echo '<td '.$attribute.'>'.$_POST['numbering'].'.</td>';	//display column with incremented numbering

	//loop for display by row
	for($x=0;$x<$outputCount;$x++)
	{
		//check input item
		if(strpos($output[$x],' id="') !== false)
		{
			//fix item indexing in id and name
			$output[$x] = fixItemIndex($output[$x],$_POST['numbering']-1);
		}//eof if

		//display data by column
		echo '<td '.$attribute.'>'.$output[$x].'</td>';
	}//eof for
}//eof if

//fix item indexing in id and name
function fixItemIndex($str, $index)
{
	$charStart = ' id="';
	$charStartLength = strlen($charStart);
	$charEnd = '"';

	//position of start char
	$offset = strpos($str, $charStart);

	//loop on position of start char exist
	do
	{
		$position_1[] = $offset;
		$position_2[] = strpos($str, $charEnd, $offset + $charStartLength);
	}
	while($offset = strpos($str, $charStart, $offset + $charStartLength));

	//loop on count of position
	for($x=0; $x < count($position_1); $x++)
	{
		//get item id sub string
		$itemId[$x] = substr($str,$position_1[$x]+$charStartLength,$position_2[$x]-$position_1[$x]-$charStartLength);
	}//eof for

	//loop on count of itemId
	for($x=0; $x < count($itemId); $x++)
	{
		//explode '_'
		$itemIdTemp = explode('_',$itemId[$x],-1);
		$itemIdTempCount = count($itemIdTemp);

		//reset variable
		$itemIdActual = '';

		//loop on count of chunk exploded
		for($y=0; $y < $itemIdTempCount; $y++)
		{
			//append '_'
			if($itemIdActual)
				$itemIdActual .= '_';

			//actual itemId
			if(strpos($str, ' type="radio"'))
				$itemIdActual .= $itemIdTemp[$y-1];
			else
				$itemIdActual .= $itemIdTemp[$y];
		}//eof for

		if(strpos($str, ' type="radio"'))
		{
			$radioExp = explode('_',$itemIdActual);
			$itemIdActual = $radioExp[0];
		}

		//if have $itemIdActual, re-set the id index
		if($itemIdActual)
		{
			if(strpos($str, ' type="radio"'))
				$itemIdNew = $itemIdActual.'_'.$index.'_'.$x;
			else
				$itemIdNew = $itemIdActual.'_'.$index;

			$str = str_replace($itemId[$x], $itemIdNew, $str);

			//also fix name index for radio button
			if(strpos($str, ' type="radio"') !== false && strpos($str, ' name="'.$itemIdActual.'[') !== false)
			{
				$charStart = ' name="'.$itemIdActual.'[';
				$charStartLength = strlen($charStart);
				$charEnd = ']"';

				//position of start char
				$position_1 = strpos($str, $charStart);
				$position_2 = strpos($str, $charEnd, $offset + $charStartLength);

				//current index for name
				$nameIndex = substr($str,$position_1+$charStartLength,$position_2-$position_1-$charStartLength);

				//if have current, replace with new index, else skip
				if((int)$nameIndex > -1)
					$str = str_replace($charStart.$nameIndex.$charEnd, $charStart.$index.$charEnd, $str);
			}//eof if
		}//eof if
	}//eof for

	return $str;
}//eof function
//======= EOF APPENDED ROW SECTION ======
?>
