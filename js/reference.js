// JavaScript Document for General Reference

/**
TO SET THE VISIBLITY OF BLOCK
-choice: selection 'yes' or 'no'
-blockID: the ID of the block
*/
function hideBlock(choice,blockID)
{
	if(choice=='yes')
		document.getElementById([blockID]).style.display=''
	else
		document.getElementById([blockID]).style.display='none'
}

/**
TO CHECK IF VALUE IS NOT NULL
-var args: can have as many variable passed
*/
function havValue()
{
	var result=true
	
	// iterate through arguments
	for (var i=0; i<arguments.length; i++)	//loop through list of arguments
	{
		if(arguments[i]=='')
			result=false;
	}
	// end list
	
	if(result==false)
		alert('Please insert value into Code Name and Description 1 Name');
		
	return result;
}

/**
TO SET THE DISABILITY (FOR LOOKUP ONLY)
-choice: selection 'no', 'predefined' or 'advanced'
-predefinedID: the ID of the predefined tag
-advancedID: the ID of the advanced tag
*/
function disableLookup(choice,predefinedID,advancedID)
{
	switch(choice)
	{
		case 'predefined':
			document.getElementById(predefinedID).disabled = false
			document.getElementById(advancedID).disabled = true
		break;
		
		case 'advanced':
			document.getElementById(predefinedID).disabled = true
			document.getElementById(advancedID).disabled = false
		break;
		
		default:
			document.getElementById(predefinedID).disabled = true
			document.getElementById(advancedID).disabled = true
		break;
	}
}

/**
TO APPEND DROPDOWN BUTTON (SELECT) 
-tableID: the ID of the table
-elementName: name for the 'select' tag
-arrayItem: array of items (value=[0]; text=[1])
*/
function addDropDown(tableID, elementName, arrayItem)
{
	var a=document.getElementById(tableID).rows.length;
	var b=document.getElementById(tableID).insertRow(0+a);
	
	var x=b.insertCell(0);
	
	//create element select
	var theSelect=document.createElement("select");
	theSelect.setAttribute("name",elementName);		//set the name of dropdown
	theSelect.setAttribute("class","inputList");	//set the class of dropdown
	
	var sizeOfArrayItem=arrayItem.length;			//size of the array
	
	//set the value if option tag and loop until end of array
	for(var i=0;i<sizeOfArrayItem;i++)
	{
		var theOption=document.createElement("option");			//create an option tag
		theOption.setAttribute("value",arrayItem[i][0]);		//set the value to option
		
		var theText=document.createTextNode(arrayItem[i][1]);	//create the text	
		theOption.appendChild(theText);							//add the text to the option
		
		theSelect.appendChild(theOption);						//add the option to the select
	}
	
	//append the dropdown
	x.appendChild(theSelect);
}

/**
TO APPEND DROPDOWN BUTTON (SELECT) FOR ROLE
-tableID: the ID of the table
-elementName: name for the 'select' tag
-arrayItem: array of items (value=[0]; text=[1])
*/
function addRoleField(tableID, elementName, arrayItem)
{
	var a=document.getElementById(tableID).rows.length;
	var b=document.getElementById(tableID).insertRow(0+a);
	
	var x=b.insertCell(0);
	
	//create element select
	var theSelect=document.createElement("select");
	theSelect.setAttribute("name",elementName);		//set the name of dropdown
	theSelect.setAttribute("class","inputList");	//set the class of dropdown
	
	var sizeOfArrayItem=arrayItem.length;			//size of the array
	
	//set the value if option tag and loop until end of array
	for(var i=0;i<sizeOfArrayItem;i++)
	{
		var theOption=document.createElement("option");			//create an option tag
		theOption.setAttribute("value",arrayItem[i][0]);		//set the value to option
		
		var theText=document.createTextNode(arrayItem[i][1]);	//create the text	
		theOption.appendChild(theText);							//add the text to the option
		
		theSelect.appendChild(theOption);						//add the option to the select
	}
	
	var inputFrom=document.createElement("input");
	inputFrom.setAttribute("name","DataRangeFrom[]");
	inputFrom.setAttribute("class","inputInput");
	inputFrom.setAttribute("size","5");
	inputFrom.setAttribute("type","text");
	
	var inputTo=document.createElement("input");
	inputTo.setAttribute("name","DataRangeTo[]");
	inputTo.setAttribute("class","inputInput");
	inputTo.setAttribute("size","5");
	inputTo.setAttribute("type","text");
	
	var textFrom=document.createTextNode("Jarak Data Daru");
	var textTo=document.createTextNode("Hingga");
	var textSpace1=document.createTextNode("\u00a0");		//&nbsp; (space)
	var textSpace2=document.createTextNode("\u00a0");
	var textSpace3=document.createTextNode("\u00a0");
	var textSpace4=document.createTextNode("\u00a0");
	
	//append the dropdown
	x.appendChild(theSelect);
	x.appendChild(textSpace1);
	x.appendChild(textFrom);
	x.appendChild(textSpace2);
	x.appendChild(inputFrom);
	x.appendChild(textSpace3);
	x.appendChild(textTo);
	x.appendChild(textSpace4);
	x.appendChild(inputTo);
}

/**
TO REPLACE QUOTE
-element: element ID
*/
function replaceQuote(elem)
{
	var a = elem.value;
	a = a.replace (/\'/g, '[QS]');
	a = a.replace (/\"/g, '[QD]');
	elem.value = a;
}