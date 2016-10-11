//function to add decimal point to last 2 digits
//parameter: the string
function addDecimalPoint(str)
{
	var theLength = str.length;											//get length of string
	var decimalPart = str.substring((theLength-2),theLength);			//set last 2 digit as decimal 
	var numericPart = str.substring(0,(theLength-2));					//set char from 0 to last digit - 2 as numeric part
	var formattedStr = numericPart + '.' + decimalPart;					//concat numberic and decimal part with dot
	
	return formattedStr;												//return formatted string
}

//function to remove char
//parameter: char to remove, char to replace, the string
function removeChar(toRemove,toReplace,str)
{ 
	strLength = str.length;													//get length of string
	
	//for length of string, remove the char
	for(x=0; x < strLength; x++)
		str = str.replace(toRemove,toReplace);
	
	return str;																//return cleaned string
}

//function to format number to currency
//source: http://www.sonofsofaman.com/hobbies/code/js/formatcurrency.asp
function formatCurrency(strValue)
{
	strValue = strValue.toString().replace(/\$|\,/g,'');
	dblValue = parseFloat(strValue);

	blnSign = (dblValue == (dblValue = Math.abs(dblValue)));
	dblValue = Math.floor(dblValue*100+0.50000000001);
	intCents = dblValue%100;
	strCents = intCents.toString();
	dblValue = Math.floor(dblValue/100).toString();
	if(intCents < 10)
		strCents = "0" + strCents;
	for (var i = 0; i < Math.floor((dblValue.length-(1+i))/3); i++)
		dblValue = dblValue.substring(0,dblValue.length-(4*i+3))+','+
		dblValue.substring(dblValue.length-(4*i+3));
	
	//return (((blnSign)?'':'-') + '$' + dblValue + '.' + strCents);		//return with $ sign
	return (((blnSign)?'':'-') + dblValue + '.' + strCents);				//return without $ sign
}

//format number to X decimal places
//parameter: the number, decimal places
function numberToDecimal(str,decimalPlaces)
{
	//if string is a number
	if(!isNaN(str)) 
		str = parseFloat(str).toFixed(decimalPlaces); 						//parse to float and to X decimal places
	
	else 
		str = 0;															//else, str value to empty string

	return str;																//return formatted string
}

//split values in str to array, and assign to current row
//parameter: tableID, string to split, row number, start to explode from column
function splitAssignValToCurrRow(tableName,str,rowno,startcol,delimeter)
{
	var splittedStr = str.split(delimeter); 					//split by delimeter
	var splitStrCount = splittedStr.length;						//count length of splitted array
	
	//if splitted str more than 1
	if(splitStrCount > 1)
	{
		//for all items in splitted str array
		for($x=0; $x < splitStrCount; $x++)
		{
			var theControl = GetControlObjByRef(tableName,rowno,(startcol+$x),0);				//get control
			theControl.value = splittedStr[$x];												//assign value to control
		}//end for
	}//end if
	
	//else, 
	else
	{
	}
}

//convert to thousand format
function formatThousand(strValue)
{
	//instintiate variable
	decPlace=null
	strDec=''
	tempValue=new Array
	tempDec=new Array
	
	//check for decimal
	if(new RegExp("\\.").test(strValue))
	{
		strValue=parseFloat(strValue).toFixed(2)			//set as float
		
		var decSearch = new RegExp("\\.").exec(strValue)	//check position of decimal
		
		//if have decimal
		if(decSearch != null)
			decPlace = decSearch.index	//position of decimal
	}
	
	//convert to string
	strValue = strValue.toString()
	
	//check comma for thousand and remove if found
	if(new RegExp(",").test(strValue))
		strValue = removeChar(',','',strValue)
	
	//get length of strValue
	strValueLength=strValue.length

	//loop on length of strValue
	for(x=0;x<strValueLength;x++)
	{
		//if have decimal position and x is less than position
		if(!decPlace || x<decPlace)
			tempValue[x]=strValue[x]	//insert into temporary integer value
		else
			tempDec[x]=strValue[x]		//insert into temporary decimal value
	}
	
	//length of temporary integer and decimal value
	tempValueLength=tempValue.length
	tempDecLength=tempDec.length
	
	//loop backwards
	for(x=tempValueLength-1,y=0; x>-1; x--,y++)
	{
		//if not start and multiply of 3
		if(y!=0 && (y)%3==0)
			tempValue[x]=tempValue[x]+','
	}
	
	//loop on length of temp integer
	for(x=0;x<tempValueLength;x++)
	{
		if(x==0)
			strValue=tempValue[x];
		else
			strValue+=tempValue[x];
	}
	
	//loop on length of temp decimal
	for(x=0;x<tempDecLength;x++)
	{
		//if temp decimal have value
		if(tempDec[x])
		{
			if(x==0)
				strDec=tempDec[x];
			else
				strDec+=tempDec[x];
		}
	}

	//return result
	return strValue+strDec
}//eof function formatThousand

/**
TO REPLACE WHITESPACE
-element: element ID
*/
function trim(elem)
{
	var trimValue = elem.value;
	trimValue = trimValue.replace ('&nbsp;', '');	
	trimValue = trimValue.replace(/\s+/g, '');
	
	//re-set the value
	elem.value = trimValue;
}