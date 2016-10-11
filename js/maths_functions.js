//function to aggregate data in table columns IN ARRAY (amount[], amount[])
function aggregateColumn(aggType,theElem,target,format)
{
	var total = 0;													//define total var
	var returnValue = 0;											//define return value
	var theMin;														//define minimum value
	var theMax;														//define maximum value
	
	if(document.getElementsByName(theElem).length>0)
		theElemByName = document.getElementsByName(theElem);			//create element reference
	
	else
		theElemByName = document.getElementsByName(theElem.name);		//create element reference
	
	theTarget = document.getElementById(target);					//create target reference

	totalElems = theElemByName.length;								//count total number of elements by the name of theElem
	
	//if aggregate type is count, return number of elems as COUNT		
	if(aggType == 'count')
	{
		returnValue = totalElems
		
		for(var x=0; x < totalElems; x++)
		{
			theValue = theElemByName[x].value;	
			if(theValue==null)
				returnValue--
		}
	}
		
	//else if aggregate type is sum, min, max
	else if(aggType == 'sum' || aggType == 'min' || aggType == 'max')
	{
		//for all element by the name of theElem, total up
		for(var x=0; x < totalElems; x++)
		{
			//check thousand
			if(new RegExp(",").test(theElemByName[x].value))
			{	
				//strip ',' sign
				theValue = removeChar(',','',theElemByName[x].value)
				
				//if format not set
				if(!format)
					format = 'thousand'		//set as thousand format
			}//eof if
			else
				theValue = theElemByName[x].value;				//parse the value to float
			
			if(format != 'currency')
			{
				//check for decimal places			
				if(new RegExp("\\.").test(theValue))
					theValue=parseFloat(theValue).toFixed(2)	//set as float
				else
					theValue=parseInt(theValue)
			}
			
			//if the current element is not NOT a number
			if(!isNaN(theValue))
			{	
				//if aggregate type = min
				if(aggType == 'min')
				{
					//if first iteration, thevalue is the minimum
					if(x == 0)
						returnValue = theValue;
					else
						returnValue = Math.min(returnValue,theValue);					//else, compare
				}//eof if
				
				//if aggregate type = max
				else if(aggType == 'max')
				{
					//if first iteration, thevalue is the maximum
					if(x == 0)
						returnValue = theValue;
					else
						returnValue = Math.max(returnValue,theValue);					//else compare
				}//eof else if
				
				//if aggregate type = sum
				else if(aggType == 'sum')
				{
					if(format != 'currency' && (new RegExp("\\.").test(theValue) || new RegExp("\\.").test(returnValue)))
					{
						returnValue = parseFloat(returnValue) + parseFloat(theValue);					//total up
						returnValue=parseFloat(returnValue).toFixed(2)									//set as 2 decimal
					}
					else
						returnValue = parseFloat(returnValue) + parseFloat(theValue);					//total up
				}
			}//end if not is nan
		}//end for
	}//end else if
	
	if(format != 'currency')
	{
		//if number
		if(!isNaN(returnValue))
		{
			//check for decimal places			
			if(new RegExp("\\.").test(returnValue))
				returnValue=parseFloat(returnValue).toFixed(2)	//set as float
			else
				returnValue=parseInt(returnValue)
		}//eof if
	}//eof if
	
	//if format is currency
	if(format == 'thousand')
		theTarget.value = formatThousand(returnValue);					//set target with return value
	else if(format == 'currency')
		theTarget.value = formatCurrency(returnValue);					//set target with return value
	else
		theTarget.value = returnValue;									//set target with return value
}