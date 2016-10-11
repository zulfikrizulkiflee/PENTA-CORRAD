//to redirect window
function redirect(url)
{
    window.location = url;
}//eof function

//to open lov popup
function show_lov(p, id)
{
    //size
    var windowWidth = 500;
    var windowHeight = 580;

	//center position
	var centerWidth = (window.screen.width - windowWidth) / 2;
	var centerHeight = (window.screen.height - windowHeight) / 2;

	//popup parameter
	var param = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width="+windowWidth+", height="+windowHeight+", left="+centerWidth+", top="+centerHeight;

	//-----------------
	//get passing param
	//-----------------
	var id = id.split('||').pop();
	
	var postData = jQuery('#'+id).attr('data-post');
	var getData = jQuery('#'+id).attr('data-get');
	var formData = jQuery('#'+id).attr('data-form');
	
	if(postData)
		postData.split(',');
		
	if(getData)
		getData = getData.split(',');
		
	if(formData)
		formData = formData.split(',');	
		
	var postURLEncode = '';
	var getURLEncode = []
	var formURLEncode = [];
	
	var getURLEncodeStr = '';
	var formURLEncodeStr = '';
	
	//todo here
	//post data
	//---------
	
	
	//get data
	//--------
	var urlParams = {};
	window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(str,key,value){urlParams[key] = value;});
	var urlParamsLength = Object.keys(urlParams).length;
	
	for(key in urlParams)
		getURLEncode.push(key + '|' + urlParams[key]);
	
	getURLEncodeStr = getURLEncode.join('||');
	
	//form data
	//---------
	if(formData)
	{	
		var formCollection = jQuery('#form1').serializeArray();
		var formCollectionLength = formCollection.length;
		
		for(var x=0; x < formCollectionLength; x++)
		{
			for(var y=0; y < formData.length; y++)
			{
				if(formCollection[x].name == formData[y])
					formURLEncode.push(formCollection[x].name + '|' + formCollection[x].value);
			}			
		}
		
		formURLEncodeStr = formURLEncode.join('||');
	}

	console.log(getURLEncodeStr);
	console.log(formURLEncodeStr);

	//set action and target to open the lov
	document.getElementById('form1').action = 'lov_view.php?p='+p+'&id='+id;
	document.getElementById('form1').target = 'lov';
	document.getElementById('form1').onsubmit = window.open('', 'lov', param);
	document.getElementById('form1').submit();

	//reset the action and target
	document.getElementById('form1').action = '';
	document.getElementById('form1').target = '';
	document.getElementById('form1').onsubmit = '';
}

//to open new popup window
function my_popup(page,varz,resizeable,scrollbar,windowWidth,windowHeight)
{
	var t_resize = "no";			//default value
	var t_scroll = "no";

	if(varz !== "pic")
			page = page + ".php?" + varz;

	if(resizeable == 1) t_resize = "yes";
	if(scrollbar == 1) t_scroll = "yes";

	//center position
	var centerWidth = (window.screen.width - windowWidth) / 2;
	var centerHeight = (window.screen.height - windowHeight) / 2;

	//popup parameter
	var param = "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars="+t_scroll+", resizable="+t_resize+", width="+windowWidth+", height="+windowHeight+", left="+centerWidth+", top="+centerHeight;

	window.open(page,'ee',param);
}

//to open new popup window
function my_new_window(page,width2,height2)
{
	var paramz = "toolbar=yes, location=yes, directories=yes, status=yes, menubar=yes, scrollbars=yes, resizable=yes, copyhistory=yes, width=" + width2 + ", height=" + height2;
	window.open(page,'ee',paramz);
}

function logoutFader()
{
	if(window.confirm('Anda pasti untuk log keluar?'))
    {
        window.location = 'index.php?logout=true';
        return true;
    }
    else
        return false;
}

function logoutUAP()
{
	if(window.confirm('Anda pasti untuk log keluar?'))
    {
        window.location = 'index.php?logout=true';
        return true;
    }
    else
        return false;
}

//function to prototype.js error reporting
function reportError(request)
{
	//jQuery('#locationDiv').val('Error');
}

function addTabularRow(rowToAdd,componentid)
{
	var url = 'page_wrapper.php';
	//var url = 'class/Table.php';
	var params = 'id=1&row=' + rowToAdd + '&componentid=' + componentid;
	var ajax = new Ajax.Updater({success: 'tableRowSub' + rowToAdd},url,{method: 'get', parameters: params, onFailure: reportError});
}

//function to add new row in table
function addNewRowButton(tableName,newRowSubName,componentid)
{
	//get table row element reference
	var oRows = document.getElementById(tableName).getElementsByTagName('tr');

	//count number rows length (number of rows)
	var iRowCount = oRows.length;

	//create table reference
	//var myTable = document.getElementById(tableName);

	//tbody reference
	//var tBody = myTable.getElementsByTagName('tbody')[0];
	var tBody = document.getElementById('addRow');

	//create new row
	var newTR = document.createElement('tr');

	//assign name to newly creaete row
	newTR.id = newRowSubName + '' +  iRowCount;

	//append tr to tbody
	tBody.appendChild(newTR);

	//call ajax function to add items in the array
	addTabularRow(iRowCount,componentid);
}

//to access element in table using DOM
//parameter: table ID, row index, column index, control index
function GetControlObj(tableID,rowIndex,colIndex,ctrlIndex)
{
	var row;
	var cell;
	var controlObj;

	//parse index as int
	rowIndex = parseInt(rowIndex);

	//declare tablename
	tableID = document.getElementById(tableID);

	//declare row
	row = tableID.rows[rowIndex];

	//get cell
	cell = row.cells[colIndex];

	//get current node and assign as control object
	controlObj = cell.childNodes[ctrlIndex];

	//return control element
	return controlObj;
}

//to access element in table using DOM by INDEX
//parameter: table ID, row index, column index, control index
function GetControlObjByRef(tableRef,rowIndex,colIndex,ctrlIndex)
{
	var row;
	var cell;
	var controlObj;

	//parse index as int
	rowIndex = parseInt(rowIndex);

	//declare tablename
	tableID = tableRef;

	//declare row
	row = tableID.rows[rowIndex];

	//get cell
	cell = row.cells[colIndex];

	//get current node and assign as control object
	controlObj = cell.childNodes[ctrlIndex];

	//return control element
	return controlObj;
}

//function to strip unused item from window.location.href
//example: http://192.168.1.1/corrad/index.php?page=page_wrapper&id=1&p=412
//will return index.php?page=page_wrapper&id=1&p=412
function stripHREF(str)
{
	var newStr = str.split("/");					//the separator
	var newStrLength = newStr.length;				//count the array
	var concatStr = '';

	//for all length of newStr
	for(var x=4; x < newStrLength; x++)
	{
		//if concatStr is empty string, do not append '/'
		if(concatStr.length == 0)
			concatStr = concatStr + newStr[x];

		//else, append '/'
		else
			concatStr = concatStr + '/' + newStr[x];
	}

	return concatStr;				//return stripped href
}

//function to call listing filter function
//parameter: filter column, filter value, target id, target url
//example: customerID,82,theSearchResult, index.php?page=xxxxxx
function ajaxUpdateQuery(filterColumn,filterValue,target,theUrl,postStrKey,postStrVal)
{
	//the url to call
	var url = theUrl;

	var filterColumn = document.getElementById(filterColumn).value;					//create dom reference
	var filterValue = document.getElementById(filterValue).value;					//create dom reference
	var target = document.getElementById(target);									//create dom reference

	window.alert('zzzzzzz');
	window.alert('filtercol:' + filterColumn + 'filtervalue' + filterValue);
	window.alert(target.id);

	//process post string key
	var postStrKeyArr = postStrKey.split('|#|#|');

	//process post string value
	var postStrValArr = postStrVal.split('|#|#|');
	var postStrValArrLength = postStrValArr.length;

	//var url = 'ajax_menu_wrapper.php';
	//var params = 'id=2&toggle=CFI4&state=' + state;
	var params =  'ajax=1&filterCol=' + filterColumn + '&filterVal=' + filterValue;

	//for all item in thePostArray array, append to params string
	for(var x=0; x < postStrValArrLength; x++)
	{
		//if first iteration, append &
		if(x == 0)
			params += '&';

		//append to params string
		params = params + postStrKeyArr[x] + '=' + postStrValArr[x];

		//if last iteration, do not append & to params string
		if(x+1 != postStrValArrLength)
			params += '&';
	}

	//var ajax = new Ajax.Updater({success: 'menusub2'},url,{method: 'get', parameters: params, onFailure: reportError});
	var ajax = new Ajax.Updater({success: target},url,{method: 'post', parameters: params, onFailure: reportError});
}

//get list of input list
//requirement: prototype.js
function getInputList(theForm,theUrl)
{
	var formLength = $(theForm).length;					//get form length
	var theQueryString = new Array(formLength);			//declare new array for querystring
	var lastLocation = 0;								//declare and assign lastlocation

	//for all length of form
	for(var x=0; x < formLength; x++)
	{
		//if type of elems is the following..
		if($(theForm).elements[x].type == 'text' || $(theForm).elements[x].type == 'checkbox' || $(theForm).elements[x].type == 'radio'
			|| $(theForm).elements[x].type == 'hidden' || $(theForm).elements[x].type == 'textarea' || $(theForm).elements[x].type == 'select-one')
		{
			//if id of element is not empty string
			if($(theForm).elements[x].id != '')
			{
				if($(theForm).elements[x].type == 'radio')
				{
					//if element is checked
					if($(theForm).elements[x].checked)
						theQueryString[x] = $(theForm).elements[x].id + '=' + $(theForm).elements[x].value;
				}
				//if other than radio button
				else
				{
					//store the id and value as querystring (eg: id1=xxx)
					theQueryString[x] = $(theForm).elements[x].id + '=' + $(theForm).elements[x].value;
				}
			}//end if
		}//end if
	}//end for

	//check for existance of '?'
	//if \u003f of question mark (?) is found, do nothing
	//if(theUrl.search('/\e/') == -1)
		//theUrl = theUrl + '?';

	var theUrlLength = theUrl.length;					//the url length

	//for all length of window href
	for(var x=0; x < theUrlLength; x++)
	{
		//find position of last '&'
		if(theUrl.charAt(x) == '&')
			lastLocation = x;
	}

	theQueryString = theQueryString.compact();					//remove null, undefined items from array
	var convertToStr = theQueryString.toString();				//convert array to string
	convertToStr = convertToStr.replace(/\,/g,'&');				//replace all commas to &

	return theUrl + convertToStr;								//return converted string
}

//function to toggle ajax side bar menu (new)
function ajaxToggleMenu(theItem)
{
	var id = jQuery(theItem).attr('id');
	id =id.substring(8);

	jQuery.ajax({
		url:'menu_wrapper.php',
		type:'post',
		data:{task:'ajax_toggleMenu',menuId:id}
	});
}

//function to update page editor page selector dropdown
function ajaxUpdatePageSelector(type,target,thevalue,theSelected)
{
	var url = 'ajax_editor.php?updater=filter&type=' + type + '&value=' + thevalue + '&select=' + theSelected;
	var params = '';
	var ajax = new Ajax.Updater({success: target},url,{method: 'get', parameters: params, onFailure: reportError});
}

//function to update multiple select dropdown from left to right, and vice versa
function ajaxUpdateMultipleLeftRight(source,target,thevalue)
{
	var url = 'ajax_editor.php?updater=filter&type=' + type + '&value=' + thevalue + '&select=' + theSelected;			//data source
	var params = '';
	var ajax = new Ajax.Updater({success: target},url,{method: 'get', parameters: params, onFailure: reportError});
}

/* ************************* */
//	source: lupa
//	Copy the code inside head portion of your HTML code
//   Created on : Nov 12,2007
//   List movement script
//   Moves the items between two HTML Select elements
//   it looks like a movement between two lists.
//
/* ************************* */

//edited by cikkim
function moveoutid(source,target)
{
	var sda = document.getElementById(source);
	var len = sda.length;
	var sda1 = document.getElementById(target);

	for(var j=0; j<len; j++)
	{
		if(sda[j])
		{
			if(sda[j].selected)
			{
				var tmp = sda.options[j].text;
				var tmp1 = sda.options[j].value;
				sda.remove(j);
				j--;
				var y=document.createElement('option');
				y.text=tmp;
				y.value=tmp1;
				try
				{sda1.add(y,null);
				}
				catch(ex)
				{
				sda1.add(y);
				}
			}
		}
	}
}

function moveinid(source,target)
{
	var sda = document.getElementById(source);
	var sda1 = document.getElementById(target);
	var len = sda1.length;

	try
	{
	for(var j=0; j<len; j++)
	{
		if(sda1[j].selected)
		{
			var tmp = sda1.options[j].text;
			var tmp1 = sda1.options[j].value;
			sda1.remove(j);
			j--;
			var y=document.createElement('option');
			y.text=tmp;
			y.value=tmp1;
			try
			{
			sda.add(y,null);}
			catch(ex){
			sda.add(y);
			}

		}
	}
	}
	catch(ex)
	{
	}
}

//http://www.delphifaq.com/faq/javascript/f1038.shtml
//function to sort multiple data list box
//2006-08-09, 17:47:04. modified by anonymous poster
function sortListBox(target)
{
	var lb = document.getElementById(target);
	arrTexts = new Array();
	arrValues = new Array();
	arrOldTexts = new Array();

	for(i=0; i<lb.length; i++)
	{
		arrTexts[i] = lb.options[i].text;
		arrValues[i] = lb.options[i].value;

		arrOldTexts[i] = lb.options[i].text;
	}

	arrTexts.sort();

	for(i=0; i<lb.length; i++)
	{
		lb.options[i].text = arrTexts[i];
		for(j=0; j<lb.length; j++)
		{
			if (arrTexts[i] == arrOldTexts[j])
			{
				lb.options[i].value = arrValues[j];
				j = lb.length;
			}
		}
	}
}

//http://lists.evolt.org/pipermail/javascript/2004-February/006557.html
//edited: cikkim
function listBoxSelectall(target) {
	aOptions = document.getElementById(target).options;
	if(aOptions.length) {
		for(var i=0; i<aOptions.length; i++)
			aOptions[i].selected = 'selected';
	}
}

//swap display of 2 input
function swapItemDisplay(itemEnable, itemDisable)
{
	//split the variable into array
	itemEnable = itemEnable.split('|');
	itemDisable = itemDisable.split('|');

	//loop on size of array
	for(x=0; x<itemEnable.length; x++)
	{
		//enable
		if(document.getElementById(itemEnable[x]))
		{
			document.getElementById(itemEnable[x]).disabled=false;			//enable
			document.getElementById(itemEnable[x]).style.display='';		//display
		}
	}

	//loop on size of array
	for(x=0; x<itemDisable.length; x++)
	{
		//disable
		if(document.getElementById(itemDisable[x]))
		{
			document.getElementById(itemDisable[x]).disabled=true;			//disable
			document.getElementById(itemDisable[x]).style.display='none';	//hide
		}
	}
}//eof function

function swapItemDisabled(itemEnable, itemDisable)
{
	//split the variable into array
	itemEnable = itemEnable.split('|');
	itemDisable = itemDisable.split('|');

	//loop on size of array
	for(x=0; x<itemEnable.length; x++)
	{
		//enable
		if(document.getElementById(itemEnable[x]))
		{
			document.getElementById(itemEnable[x]).disabled=false;			//enable
		}
	}

	//loop on size of array
	for(x=0; x<itemDisable.length; x++)
	{
		//disable
		if(document.getElementById(itemDisable[x]))
		{
			document.getElementById(itemDisable[x]).disabled=true;			//disable
		}
	}
}//eof function
//swap enable of 2 input
function swapItemEnabled(itemEnable, itemDisable)
{
	//split the variable into array
	itemEnable = itemEnable.split('|');
	itemDisable = itemDisable.split('|');

	//loop on size of array
	for(x=0; x<itemEnable.length; x++)
	{
		//enable
		if(document.getElementById(itemEnable[x]))
		{
			document.getElementById(itemEnable[x]).disabled=false;
			document.getElementById(itemEnable[x]).style.color = '#000000';
		}
	}

	//loop on size of array
	for(x=0; x<itemDisable.length; x++)
	{
		//disable
		if(document.getElementById(itemDisable[x]))
		{
			document.getElementById(itemDisable[x]).disabled=true;
			document.getElementById(itemDisable[x]).style.color = '#999999';
		}
	}
}//eof function

//required: prototype.js
//to select ALL checkboxes
function prototype_selectAllCheckbox()
{
	//find all checkbox item
	var checkboxArr = $$('input[type="checkbox"]');

	for(var x=0; x < checkboxArr.size(); x++)
		checkboxArr[x].checked = true;
}

//required: prototype.js
//to unselect ALL checkboxes
function prototype_unselectAllCheckbox()
{
	//find all checkbox item
	var checkboxArr = $$('input[type="checkbox"]');

	for(var x=0; x < checkboxArr.size(); x++)
		checkboxArr[x].checked = false;
}

//to run ajax for ajax_updater (component item)
function exec_ajax_updater(target,itemid)
{
	var url = 'ajax_updater_wrapper.php';
	var params = 'itemid=' + itemid;
	var ajax = new Ajax.Updater({success: target},url,{method: 'get', parameters: params, onFailure: reportError});

	setTimeout("exec_ajax_updater('"+target+"','"+itemid+"')", 1000);
}

//popup window
function winconfirm(){
	question = confirm("CONFIRM DELETE");
	if (question != "0"){
		window.open("LINK LOCATION", "NewWin", "toolbar=yes,location=yes,directories=no,status=no,menubar=yes,scrollbars=yes,resizable=no,copyhistory=yes,width=635,height=260");
	}
}

//http://stackoverflow.com/questions/5999118/add-or-update-query-string-parameter
function setParameterByName(key,value,url) {
    if (!url) url = window.location.href;
    var re = new RegExp("([?|&])" + key + "=.*?(&|#|$)(.*)", "gi");

    if (re.test(url)) {
        if (typeof value !== 'undefined' && value !== null)
            return url.replace(re, '$1' + key + "=" + value + '$2$3');
        else {
            var hash = url.split('#');
            url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
            if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                url += '#' + hash[1];
            return url;
        }
    }
    else {
        if (typeof value !== 'undefined' && value !== null) {
            var separator = url.indexOf('?') !== -1 ? '&' : '?',
                hash = url.split('#');
            url = hash[0] + separator + key + '=' + value;
            if (typeof hash[1] !== 'undefined' && hash[1] !== null)
                url += '#' + hash[1];
            return url;
        }
        else
            return url;
    }
}

//http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function flc_ajax_cascade(elem,targetName,column,type,family,selectorType,getData,postData,appendMode)
{
	var allAssociatedItems = family.split(',');
	var targetLoc = 0;

	//post and get data transfer
	//-----------------------------------------------
	var getDataVal = [];
	var postDataVal = [];

	if(getData !== undefined && getData !== null)
	{
		var getDataArr = getData.split(',');

		for(var x=0; x < getDataArr.length; x++)
		{
			var a = {
				name: getDataArr[x],
				value: getParameterByName(getDataArr[x])
			};
			getDataVal.push(a);
		}
	}

	if(postData !== undefined && getData !== null)
	{
		var postDataArr = postData.split(',');

		for(var x=0; x < postDataArr.length; x++)
		{
			var a = {
				name: postDataArr[x],
				value: jQuery('#'+postDataArr[x]).val()
			};
			postDataVal.push(a);
		}
	}

	var getDataValJSON = JSON.stringify(getDataVal);
	var postDataValJSON = JSON.stringify(postDataVal);

	var allData = [];
	allData.push({postval:postDataVal});
	allData.push({getval:getDataVal});
	allDataJSON = JSON.stringify(allData);
	//-----------------------------------------------

	for(var x=0; x < allAssociatedItems.length; x++)
	{
		if(allAssociatedItems[x] == targetName)
			targetLoc = x;
	}
		
	if(jQuery(elem).val() == '')
			elemVal = 'flc_null';
	else
		elemVal = jQuery(elem).val();

	if(type == 'tabular')
	{
		var elemIndex = elem.id.split('_');
		elemIndex = elemIndex[elemIndex.length-1];

		jQuery.ajax({

			type:			'POST',
			url: 			'ajax_cascade_feeder.php?item='+targetName+'&col='+column+'&colval='+elemVal+'&index='+elemIndex+'&type='+type+
							'&menuID='+getParameterByName('menuID')+'&selector='+selectorType,
			contentType: 	'application/json',
			dataType: 		"json",
			data: 			allDataJSON,
			complete: 		function(data)
							{
								jQuery('#'+targetName+'_'+elemIndex).parent().html(data.responseText);
							}
		});

		for(var x=targetLoc; x < allAssociatedItems.length; x++)
			jQuery('#'+allAssociatedItems[x]+'_'+elemIndex).prop('selectedIndex',0).change();
	}
	else if(type == 'form')
	{
		jQuery.ajax({

			type:			'POST',
			url: 			'ajax_cascade_feeder.php?item='+targetName+'&col='+column+'&colval='+elemVal+'&type='+type+
							'&menuID='+getParameterByName('menuID')+'&selector='+selectorType,
			contentType: 	'application/json',
			dataType: 		"json",
			data: 			allDataJSON,
			complete: 		function(data)
							{
								//if(appendMode == 'append')
									//jQuery('#'+targetName).parent().html(data.responseText);
								//else
									jQuery('#'+targetName).parent().html(data.responseText);
							}
		});

		for(var x=targetLoc; x < allAssociatedItems.length; x++)
			jQuery('#'+allAssociatedItems[x]).prop('selectedIndex',0).change();
	}
}

/*
function ajaxCascadeForm(elem,targetName,where)
{
	if($F(elem).strip().length > 0)
	{
		//for reuslt target id
		var resultTargetID = 'result_' + targetName;

		var elemName = elem.name;
		var target = $(targetName);

		//create the container div
		var newDiv = document.createElement('div');
		newDiv.id = resultTargetID;

		target.up().appendChild(newDiv);

		//get result
		var url = 'ajax_cascade_feeder_form.php';
		var params = '?item=' + target.name + '&where=' + where;
		var ajax = new Ajax.Updater({success: resultTargetID},url,{method: 'get', parameters: params});

		target.up().removeChild(target);
	}
}
*/
// ajaxdropdown added by Rosli Bin Amir
// Usage : to populate ajax dropdown list from component item , item type dropdownlist in the advanced lookup SQL.
// My first javascript on 23th January 2011 3 days after my birthday.
function ajaxdropdown(CurrentElement,TargetElement,CustomFile) {
	var ajaxRequest;  // The variable that makes Ajax possible!

	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke, did not support AJAX, consult your support team!");
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
			var xmlDoc=ajaxRequest.responseXML;
			var TotalRecord = xmlDoc.getElementsByTagName('DROPDOWNLIST').length ;
			document.getElementById(TargetElement).options.length = 0;
			document.getElementById(TargetElement).options[0]  = new Option("","");
			for (i=1; i< TotalRecord+1; i++) {
				document.getElementById(TargetElement).options[i] = new Option(xmlDoc.getElementsByTagName('FLC_NAME')[i-1].childNodes[0].nodeValue
																				,xmlDoc.getElementsByTagName('FLC_ID')[i-1].childNodes[0].nodeValue );
			}
		}
	};
	var AjaxPHPFile = "ajax_dropdownlist.php";
	if ( CustomFile != "" ) {
		AjaxPHPFile = CustomFile ;
	}
    var queryString = "?TargetElement=" + TargetElement +
	                  "&ElementValue=" + document.getElementById(CurrentElement).value  +
	                  "&CurrentElement=" + CurrentElement  ;
	ajaxRequest.open("POST", AjaxPHPFile + queryString , true);
	ajaxRequest.send(null);
}

// flcCheckBoxBArray added by Rosli Bin Amir
// Usage : To assign checkbox item in the array for tabular or report component and assign it to component item
function flcCheckBoxArray(CheckBoxElement,CheckItemElement,CheckedValue,UncheckedValue) {

        var arr = new Array();
        arr = document.getElementsByName(CheckBoxElement + '[]');
        for(var i = 0; i < arr.length; i++)
        {
            var obj  = document.getElementsByName(CheckBoxElement + '[]').item(i);
            var obj2 = document.getElementsByName(CheckItemElement +'[]').item(i);
            if (obj.checked === true)
               obj2.value = CheckedValue;
            else
              obj2.value =  UncheckedValue;
        }
}

// added by Rosli Amir @ 25th Feb 2011
// Usage : to use in the flc_component_tree_checkbox.

 function showChildren(obj)
 {
     var children = obj.immediateDescendants();
     for(var i=0;i<children.length;i++)
     {
         if(children[i].tagName.toLowerCase()=='ul')
             children[i].toggle();
     }
 }

 function checkChildren(obj,srcObj)
 {
     var children = obj.immediateDescendants();
     for(var i=0;i<children.length;i++)
     {
         if(children[i].tagName.toLowerCase()=='input' && children[i].type=='checkbox' && children[i]!=srcObj)
             children[i].checked = srcObj.checked;

         // recursive call
         checkChildren(children[i],srcObj);
     }
 }

 function ajaxbltextxx(bl_name,para_field,para_return_field) {

	var ajaxRequest;  // The variable that makes Ajax possible!

	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				document.getElementById(para_return_field).value = false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
			var returnresult = ajaxRequest.responseText;
			document.getElementById(para_return_field).value = returnresult;
		}
	};
        var queryString = "?bl_name=" + bl_name + '&' + para_field;
		ajaxRequest.open("GET", "flc_ajax_bl_text.php" + queryString , true);

		//console.log(queryString);

	ajaxRequest.send(null);

}

function ajaxbltext(bl_name,para_field,para_return_field)
{
	var queryString = "?bl_name=" + bl_name + '&' + para_field;
	jQuery.get('flc_ajax_bl_text.php'+queryString,function(data)
	{
		jQuery('#'+para_return_field).val(data);
	});
}



//ckm - frm internet - multiple on load function
function addLoadEvent(func) {
	  var oldonload = window.onload;
	  if (typeof window.onload != 'function') {
	    window.onload = func;
	  } else {
	    window.onload = function() {
	      if (oldonload) {
	        oldonload();
	      }
	      func();
	    }
	  }
	}

//function to filter extension of the uploaded file
function filterFileExtension(filename, allowedExtension)
{
	var allowedExtensionFlag = true;

	//file extension
	var tempExt = filename.split('.');
	var fileExtension = tempExt[tempExt.length-1];

	//allowed extension
	allowedExtensionCount = allowedExtension.length;

	//if given allowed extension
	if(allowedExtensionCount>0)
	{
		allowedExtensionFlag = false;

		//loop on count of allowed extension
		for(x=0; x<allowedExtensionCount; x++)
		{
			//if file entension matched allowed extension
			if(fileExtension == allowedExtension[x])
				allowedExtensionFlag = true;
		}//eof for
	}//eof for

	return allowedExtensionFlag;
}//eof function

//function to filter size of the uploaded file
function filterFileSize(elem, allowedSize)
{
	var allowedSizeFlag = true;

	//browser not support
    if(typeof window.FileReader !== 'function')
        return true;
	else
	{
		//browser not support
		if(!elem.files)
			return true;
		else
		{
			//if exceeds the allowed size
			if(allowedSize && (elem.files[0].size/1000)>allowedSize)
				allowedSizeFlag	= false;
			else
				return true;
		}//eof else
	}//eof else

	return allowedSizeFlag;
}//eof function

//item running number
function setItemRunningNumber(itemname,defaultNo)
{
	var tempObj=document.getElementsByName(itemname);

	for($x=0;$x<tempObj.length;$x++)
		tempObj[$x].value=$x+parseInt(defaultNo);

	setTimeout("setItemRunningNumber('"+itemname+"',"+defaultNo+")", 100);
}//eof function

//countdown timer, variable 'countdownTimer' is set to global
function setCountdown(duration)
{
	//countdown timer
	window.countdownTimer;

	//duration valid
	if(duration>=0)
	{
		countdownTimer = duration--;
		setTimeout("setCountdown("+duration+");",1000);
	}//eof if
}//eof function

//check session timeout
function checkSessionTimeout()
{
	console.log('check!!!');
	//set session timeout countdown
	setCountdown(10);

	//enable flag session timeout, start countdown
	window.sessionTimeoutFlag = true;
	countdownSessionTimeout();
}//eof function

//countdown session timeout, logout if countdown to 0
function countdownSessionTimeout()
{
	//if flag true
	if(sessionTimeoutFlag)
	{
		jQuery('#sessionTimeout').show();
		jQuery('#sessionTimeoutBg').attr('style','position: fixed;top: 0;bottom: 0;left: 0;right: 0;background-color: black;z-index: 999; opacity:0.6');
		jQuery('#sessionTimeoutLabel').text(countdownTimer).attr('style','font-size:13pt; color:red; font-weight:bold;');
		jQuery('#sessionTimeoutDialog').attr('style','position: fixed;top: 40%; left: 50%; margin-top:-100px; margin-left:-210px; width:400px; background-color: white;z-index: 1000; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px; border:5px solid #b4b4b4; text-align:center; font-size:10pt; padding:20px 20px;');

		//logout if countdown 0
		if(countdownTimer == 0)
			redirect('index.php?logout=true');

		setTimeout("countdownSessionTimeout();",1000);
	}//eof if
}//eof function


var xxx;

//continue current session, stop timeout timer
function continueSession(duration,caller)
{
//	console.log(caller);
	if(caller == 'monitor')
	{
		if(sessionTimeoutFlag === false)
			xxx = setTimeout("checkSessionTimeout();",duration);
	}
	else
	{
		//stop timer, hide timeout layer
		sessionTimeoutFlag = false;
		document.getElementById('sessionTimeout').style.display = 'none';

		//start back the session timeout, after a given duration
		setTimeout("checkSessionTimeout();",duration);
	}
}//eof function

//monitor session activity - mouse movement and keystrokes
function monitorSessionActivity(duration)
{
	console.log(jQuery('#sessionTimeout').css('display'));
	jQuery(document).mousemove(function(){	continueSession(duration,'monitor');});
	jQuery(document).keyup(function(){	continueSession(duration,'monitor');});
}

//execute php bl (using ajax)
//@blName is the name of PHP BL to be executed
//@parameterArray is the array of parameters to be sent to PHP BL (array name/key must be the variable name to be used in PHP BL)
function executeBL(blName, parameterArray)
{
	var url = 'bl_generator.php';
	var params = 'blName=' + blName;

	//if have parameterArray
	if(parameterArray)
	{
		//loop until end of array
		for(var key in parameterArray)
		{
			if (parameterArray.hasOwnProperty(key))
				params += '&' + key + '=' + parameterArray[key];
		}//eof for
	}//eof if

	//execute the ajax
	var ajax = new Ajax.Updater({success: 'ajaxOnSuccess'},url,{method: 'post', parameters: params, onLoading: function(request) {jQuery('#ajaxOnLoad').show();}, onLoaded: function(request) {jQuery('#ajaxOnLoad').hide();}, onFailure: getFailureReport, evalScripts: true});
}//eof function

//add slashes
function addSlashes(value)
{
	//if have backslash (\)
	if(value.indexOf('\\'))
		value = value.replace(/\\/g, "&bcksl;");
		//value = value.replace(/\\/g, "\\\\");

	return value;
}//eof function

//add date separator by format
function addDateSeparator(e, elem, format, separator)
{
	this.Format = format;
	var keycode = (e.which) ? e.which : e.keyCode;

	//check keypress
	if(keycode > 31 && (keycode < 48 || keycode > 57))
		return false;
	else
	{
		/*//date format pattern
		var DateFormatPattern = /^dd\+mm\/yyyy$|^mm\/dd\/yyyy$|^mm\/dd\/yy$|^yyyy\/mm\/dd|dd\-mm\-yyyy$|^mm\-dd\-yyyy$|^mm\-dd\-yy$|^yyyy\-mm\-dd$/;*/

		//if have separator
		if(separator == '-')
			var DateFormatPattern = /^dd\-mm\-yyyy$|^mm\-dd\-yyyy$|^mm\-dd\-yy$|^yyyy\-mm\-dd$/;
		else if(separator == '/')
			var DateFormatPattern = /^dd\/mm\/yyyy$|^mm\/dd\/yyyy$|^mm\/dd\/yy$|^yyyy\/mm\/dd$/;

		//if supported date format
		if(DateFormatPattern.test(this.Format))
		{
			//split format (by separator)
			var SplitFormat = this.Format.split(separator);

			//check length
			if(elem.value.length >= this.Format.length)
			{
				//not backspace
				if(keycode !=8)
					return false;
			}//eof if

			//day split
			if(elem.value.length == SplitFormat[0].length)
			{
				//not backspace
				if (keycode !=8)
					elem.value += separator;
			}//eof if

			//month split
			if(elem.value.length == (SplitFormat[1].length + SplitFormat[0].length +1))
			{
				//not backspace
				if(keycode !=8)
					elem.value += separator;
			}//eof if
		}//eof if
	}//eof else
}//eof function

//function to retreive central message by name
function flc_message(name, timer)
{
	jQuery.post('message_generator.php', { name: name, timer: timer }, function(data) {
		jQuery('body').append(data);
	});
}//eof function

//function to show notification (Info)
function showNotificationInfo(message, timer, style)
{
	return showNotification('Info', message, timer, style);
}//eof function

//function to show notification (Error)
function showNotificationError(message, timer, style)
{
	return showNotification('Error', message, timer, style);
}//eof function

//function to show notification
function showNotification(type, message, timer, style)
{
	if(message)
	{
		if(!type)
			type = 'Info';
		else
			type = initialCap(type);

		//random id
		var randId = Math.floor((Math.random()*1000)+1);

		//notification div
		var notificationHTML = '<div id="notification_'+randId+'" class="notification" style="'+style+'" onclick="closeNotification(\'notification_'+randId+'\');">' +
		  '<div class="notification'+type+'">' +
			'<img class="notificationClose" onclick="closeNotification(\'notification_'+randId+'\');" />' +
			'<table border="0" cellspacing="0" cellpadding="0" style="width:100%">' +
			  '<tr>' +
				'<td class="notificationIconInfo"></td>' +
				'<td>'+message+'</td>' +
			  '</tr>' +
			'</table>' +
		  '</div>' +
		'</div>';

		//show notification
		jQuery("body").append(notificationHTML);

		//if have timer
		if(timer)
		{
			setTimeout("closeNotification('notification_"+randId+"');",timer*1000);
		}//eof if
	}//eof if

	return 'notification_'+randId;
}//eof function

//close notification window
function closeNotification(notificationId)
{
	jQuery('#'+notificationId).fadeOut('fast',function(){ jQuery(this).remove(); });

	//kalau ada theme yang pakai dim
	if( jQuery('#dimBlack').length > 0 )
	{
		jQuery('#dimBlack').remove();
	}
}

function flc_validation(type,str,target)
{
	var typeArr = type.split('|');
	var returnVal = '';
	var reOkFlag = true;
	var msg = '';

	for(var x=0; x < typeArr.length; x++)
	{
		type = typeArr[x];

		if(type == 'uppercase')
			returnVal = str.toUpperCase();

		else if(type == 'lowercase')
		   returnVal = str.toLowerCase();

		else if(type == 'email')
		{
			var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,6}$/;

			if(re.test(str))
				returnVal = str;
			else
			{
				reOkFlag = false;
				msg = 'Sila isikan semula email!';
			}
		}
		else if(type == 'ext')
		{
			var re = /^[1-9]{1}[0-9]{0,3}$/;

			if(re.test(str))
				returnVal = str;
			else
			{
				reOkFlag = false;
				msg = 'Sila isikan semula nombor sambungan!';
			}
		}
		else if(type == 'phone')
		{
			var re = /^0[1-9]{1}[-]{0,1}[1-9]{1}[0-9]{6,7}$/;
			msg = 'Sila isikan semula nombor telefon!';

			//check if it contains dash (-)
			if(str.indexOf('-') == -1)
				msg = msg.substr(0,msg.length-1) + " dan letakkan tanda sengkang (-)!";

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'phone_with_dash')
		{
			var re = /^0[1-9]{1}[-]{1}[1-9]{1}[0-9]{6,7}$/;
			msg = 'Sila isikan semula nombor telefon!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'phone_without_dash')
		{
			var re = /^0[1-9]{1}[1-9]{1}[0-9]{6,7}$/;
			msg = 'Sila isikan semula nombor telefon!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'nokp_with_dash')
		{
			var re = /^[1-9]{1}[0-9]{5}[-]{1}[0-9]{2}[-]{1}[0-9]{4}$/;
			msg = 'Sila isikan semula nombor kad pengenalan. Pastikan ada tanda sengkang (-)!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'nokp_without_dash')
		{
			var re = /^[1-9]{1}[0-9]{5}[0-9]{2}[0-9]{4}$/;
			msg = 'Sila isikan semula nombor kad pengenalan. Sila pastikan TIADA tanda sengkang (-)!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'nokp_old')
		{
			var re = /^[1-9]{1}[0-9]{6}$/;	//old 1234567
			msg = 'Sila isikan semula nombor kad pengenalan! Format adalah: 1234567.';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'nokp_old_2')
		{
			var re = /^[A-Z]{1}[0-9]{7}$/;	//old A1234567
			msg = 'Sila isikan semula nombor kad pengenalan! Format adalah: A1234567.';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'hp')
		{
			var re = /^01[0-9]{1}[-]{0,1}[1-9]{1}[0-9]{6,7}$/;
			msg = 'Sila isikan semula nombor telefon!';

			//check if it contains dash (-)
			if(str.indexOf('-') == -1)
				msg = msg.substr(0,msg.length-1) + " dan letakkan tanda sengkang di tempat yang betul jika perlu(-)!";

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'hp_without_dash')
		{
			var re = /^01[0-9]{1}[1-9]{1}[0-9]{6,7}$/;
			msg = 'Sila isikan semula nombor telefon!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'hp_with_dash')
		{
			var re = /^01[0-9]{1}[-]{1}[1-9]{1}[0-9]{6,7}$/;
			msg = 'Sila isikan semula nombor telefon!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'username')
		{
			var re = /^[A-Z]{1,20}[0-9]{0,10}$/;
			msg = 'Nama pengguna tidak menepati format. Sila isi sekali lagi.';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'numbers')
		{
		   var re = /^[0-9]{0,999}$/;
		   msg = 'Sila isikan nombor sahaja';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'characters')
		{
			var re = /^[a-zA-Z]{0,999}$/;
			msg = 'Sila isikan nilai A sehingga Z sahaja';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'poskod')
		{
			var re = /^\d{5}$/;
			msg = 'Sila isikan poskod nombor sahaja';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'creditcard')
		{
			var re = /^4[0-9]{12}(?:[0-9]{3})?$/; 	//visa
			var re2 = /^5[1-5][0-9]{14}$/; 			//mastercard
			var re3 = /^3[47][0-9]{13}$/; 			//amex
			msg = 'Sila isikan format nombor kredit card yang betul sahaja';

			if(re.test(str) || re2.test(str) || re3.test(str) )
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'hex')
		{
			var re = /^#?([a-f0-9]{6}|[a-f0-9]{3})$/;
			msg = 'Sila isikan nilai hexadesimal yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'url')
		{
			var re = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
			msg = 'Sila isikan URL yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}
		else if(type == 'ip_address')
		{
			var re = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
			msg = 'Sila isikan IP Address yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}

		//http://stackoverflow.com/questions/16242449/regex-currency-validation
		else if(type == 'currency_decimal_comma_required')
		{
			var re = /^\$?(([1-9][0-9]{0,2}(,[0-9]{3})*)|0)?\.[0-9]{1,2}$/;
			msg = 'Sila isikan nilai desimal yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}

		//http://stackoverflow.com/questions/16242449/regex-currency-validation
		else if(type == 'currency_decimal_allowed_comma_required')
		{
			var re = /^\$?(([1-9][0-9]{0,2}(,[0-9]{3})*)|0)?\.[0-9]{1,2}$/;
			msg = 'Sila isikan nilai desimal yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}

		//http://stackoverflow.com/questions/16242449/regex-currency-validation
		else if(type == 'currency_decimal_comma_optional')
		{
			var re = /(?=.)^\$?(([1-9][0-9]{0,2}(,[0-9]{3})*)|[0-9]+)?(\.[0-9]{1,2})?$/;
			msg = 'Sila isikan nilai desimal yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}

		//http://stackoverflow.com/questions/16242449/regex-currency-validation
		else if(type == 'currency_decimal_required_comma_optional')
		{
			var re = /^\$?(([1-9][0-9]{0,2}(,[0-9]{3})*)|[0-9]+)?\.[0-9]{1,2}$/;
			msg = 'Sila isikan nilai desimal yang betul!';

			if(re.test(str))
				returnVal = str;
			else
				reOkFlag = false;
		}

		//if regex does not pass
		if(reOkFlag === false)
		{
			window.alert(msg);
			
			setTimeout(function(){	
				jQuery(target).val('').focus();
			},0);
		}
		else
			jQuery(target).val(jQuery.trim(returnVal));				//return value to target

		//reassign target value to str
		//if(typeArr.length > 1)
		//	str = jQuery(target).val();
	}
}

function flc_required(itemnamelist, button)
{
	jQuery('#'+button).on('click',function(event){

		var itemList = itemnamelist.split('|');
		var itemListLength = itemList.length;
		
		var emptyFlag = false;
		var errorMsg = '<span class="requiredClass" style="font-weight:bold;color:red;">* Maklumat ini wajib!</span>';
		var errorMsgWithBreak = '<span class="requiredClass" style="font-weight:bold;color:red;">* Maklumat ini wajib!<br></span>';

		var compType = '';
		var tableName = '';

		//component type checker
		if(jQuery('[name="'+itemList[0]+'[]"]').length >=1)
		{
			compType = 'report';
			parentTable = jQuery('[name="'+itemList[0]+'[]"]').closest('.flcReport, .flcTabular');

		}
		else if(jQuery('[name="'+itemList[0]+'[0]"]').length >= 1)
		{
			compType = 'report';
			parentTable = jQuery('[name="'+itemList[0]+'[0]"]').closest('.flcReport, .flcTabular');
		}

		if(compType == 'report')
			var parentTableDataRowsCnt = jQuery(parentTable).find('tr').length-2;

		for(var x=0; x < itemListLength; x++)
		{
			if(compType == 'report')
			{
				for(var y=0; y < parentTableDataRowsCnt; y++)
				{
					var elemType = jQuery('[name="'+itemList[x]+'[]"]').eq(y).prop('tagName');
					var inputType = jQuery('[name="'+itemList[x]+'[]"]').eq(y).attr('type');

					if(elemType === undefined)
					{
						elemType = jQuery('[name="'+itemList[x]+'['+y+']"]').prop('tagName');
						inputType = jQuery('[name="'+itemList[x]+'['+y+']"]').attr('type');
					}

					if(elemType == 'INPUT' || elemType == 'SELECT'  || elemType == 'TEXTAREA')
					{
						if(inputType == 'radio' || inputType == 'checkbox')
						{
							if(inputType == 'checkbox')
								var item = jQuery('[name="'+itemList[x] + '[]"]').eq(y);
							else if(inputType == 'radio')
								var item = jQuery('[name="'+itemList[x] + '['+y+']"]');

							if(item.parent().find('input:checked').length == 0)
							{
								emptyFlag = true;
								if(item.parent().parent().find('.requiredClass').length > 0) {}
								else
								{
									if(inputType == 'checkbox')
										item.parent().parent().prepend(errorMsgWithBreak);
									else
										item.parent().parent().append(errorMsg);
								}
							}
							else
								item.parent().parent().find('.requiredClass').remove();
						}
						else
						{
							if(jQuery('[name="'+itemList[x]+'[]"]').eq(y).val() == '')
							{
								emptyFlag = true;
								
								//check for text editor - second checking
								if(jQuery('iframe.cke_wysiwyg_frame[title*="'+itemList[x]+'"]').eq(y).length > 0)
								{	
									if(CKEDITOR.instances[itemList[x]+'_'+y].getData() == '')
									{
										if(jQuery('[name="'+itemList[x]+'[]"]').eq(y).parent().find('.requiredClass').length > 0) {}
										else
											jQuery('[name="'+itemList[x]+'[]"]').eq(y).parent().append(errorMsg);
									}
									else
										jQuery('[name="'+itemList[x]+'[]"]').eq(y).parent().find('.requiredClass').remove();
								}
								else
								{
									if(jQuery('[name="'+itemList[x]+'[]"]').eq(y).parent().find('.requiredClass').length > 0) {}
									else
										jQuery('[name="'+itemList[x]+'[]"]').eq(y).parent().append(errorMsg);
								}
							}
							else
								jQuery('[name="'+itemList[x]+'[]"]').eq(y).parent().find('.requiredClass').remove();
						}
					}
				}
			}
			else
			{
				var elemType = jQuery('#'+itemList[x]).prop('tagName');

				if(elemType == 'INPUT' || elemType == 'SELECT' || elemType == 'TEXTAREA')
				{
					var inputType = jQuery('#'+itemList[x]).attr('type');

					if(inputType == 'radio' || inputType == 'checkbox')
					{
						var nameAppend = '';

						if(inputType == 'checkbox')
							nameAppend = '[]';

						var item = jQuery('[name="'+itemList[x] + nameAppend + '"]').eq(0);

						if(jQuery('[name="'+itemList[x] + nameAppend + '"]:checked').length == 0)
						{
							emptyFlag = true;
							if(item.parent().parent().find('.requiredClass').length > 0) {}
							else
							{
								if(inputType == 'checkbox')
									item.parent().parent().prepend(errorMsgWithBreak);
								else
									item.parent().parent().append(errorMsg);
							}
						}
						else
							item.parent().parent().find('.requiredClass').remove();
					}
					else
					{
						//check if select multiple (listbox)
						if(jQuery('#'+itemList[x]).attr('multiple') == 'multiple')
						{
							//if no children
							if(jQuery('#'+itemList[x]).children().length == 0 || jQuery('#'+itemList[x]).find(':selected').length == 0)
							{
								if(jQuery('#'+itemList[x]).parent().find('.requiredClass').length > 0) {}
								else
									jQuery('#'+itemList[x]).parent().append(errorMsg);
							}
							else
								jQuery('#'+itemList[x]).parent().find('.requiredClass').remove();
						}
						else
						{
							if(jQuery('#'+itemList[x]).val() == '')
							{
								emptyFlag = true;
								
								//check for text editor - second checking
								if(jQuery('iframe.cke_wysiwyg_frame[title*="'+itemList[x]+'"]').length > 0)
								{					
									if(CKEDITOR.instances[itemList[x]].getData() == '')
									{
										if(jQuery('#'+itemList[x]).parent().find('.requiredClass').length > 0) {}
										else
											jQuery('#'+itemList[x]).parent().append(errorMsg);
									}
									else
										jQuery('#'+itemList[x]).parent().find('.requiredClass').remove();
								}
								else
								{
									if(jQuery('#'+itemList[x]).parent().find('.requiredClass').length > 0) {}
									else
										jQuery('#'+itemList[x]).parent().append(errorMsg);
								}
							}
							else
								jQuery('#'+itemList[x]).parent().find('.requiredClass').remove();
						}
					}
				}
			}
		}

		if(emptyFlag === false){}
		else
		{	
			alert('Sila isikan semua maklumat yang diperlukan!');
			event.preventDefault();
			return false;
		}
	});
}

//http://davidwalsh.name/javascript-debounce-function
/*
	Returns a function, that, as long as it continues to be invoked, will not
	be triggered. The function will be called after it stops being called for
	N milliseconds. If `immediate` is passed, trigger the function on the
	leading edge, instead of the trailing
*/
	
function debounce(func, wait, immediate){
	
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}


//for auto suggest item
function flc_auto_suggest_lookup(inputString,itemID,name)
{
	var callback = debounce(function(){
		
		var inputString = jQuery('[name="auto_text_'+name+'"]').val();
			
		if(inputString.length == 0)
			jQuery('#suggestions_'+itemID).fadeOut();
		else
		{	
			jQuery('<div style="position:relative;display:inline; top:0px; margin-left:5px;" class="loading">loading..</div>').insertAfter(jQuery('#suggestions_'+itemID).prev());
			
			jQuery.post("ajax_auto_sugg.php?itemid="+itemID+"&name="+name,{queryString: inputString}, function(data)
			{	
				jQuery('#suggestions_'+itemID).closest('td').find('.loading').hide();
				jQuery('#suggestions_'+itemID).fadeIn().html(data);
			});
		}
	},700);
	
	jQuery('[name="auto_text_'+name+'"]').on('keyup',callback);
}

//for auto suggest item
function flc_auto_suggest_setvalue(s,r,id,name)
{
	jQuery('#auto_text_'+name).val(r);
	jQuery('#'+name).val(s);
	jQuery('#suggestions_'+id).fadeOut();
}

//for menu search
function flc_auto_suggest_menu(inputString)
{
	if(inputString.length == 0)
		jQuery('#menu_suggestions').hide();
	else
	{
		jQuery.post("ajax_auto_sugg.php?type=menu",{queryString: ""+inputString+""}, function(data)
		{
			jQuery('#menu_suggestions').show().html(data);
		});
	}
}

//for menu search
function flc_auto_suggest_menu_goto(link)
{
	window.location = link;
}

//for searching in component
function flc_comp_search(elem)
{
	var searchStr = jQuery(elem).val().toLowerCase();
	var callerTable = jQuery(elem).parent().parent().parent().parent().parent().parent().parent().parent();

	if(searchStr == '')
	{
		jQuery(callerTable).parent().find('[name="restorePagingButton"]').click();

		//get all tr
		var tr = jQuery(callerTable).find('tr');

		for(var x=3; x < tr.length; x++)
		{
			var td = jQuery(tr[x]).find('td');
			jQuery(td[0]).css('color','#000000');

			jQuery(tr[x]).show();
		}
	}
	else
	{
		jQuery(callerTable).parent().find('[name="showAllButton"]').click();

		//get all tr
		var tr = jQuery(callerTable).find('tr');

		for(var x=3; x < tr.length; x++)
		{
			var td = jQuery(tr[x]).find('td');
			var tdLength = td.length;
			var trShowFlag = false;

			for(var y=0; y < tdLength; y++)
			{
				//if first column, the running no
				if(y==0)
					jQuery(td[0]).css('color','#e0e0e0');
				else
				{
					if(jQuery(td[y]).html().toLowerCase().indexOf(searchStr) != -1)
						trShowFlag = true;
				}
			}
			if(trShowFlag === true)
				jQuery(tr[x]).show();
			else
				jQuery(tr[x]).hide();
		}
	}

}

//get GET variable
function ParseURLParams(url) {
	var queryStart = url.indexOf("?") + 1;
	var queryEnd   = url.indexOf("#") + 1 || url.length + 1;
	var query      = url.slice(queryStart, queryEnd - 1);

	if (query === url || query === "") return;

	var params  = {};
	var nvPairs = query.replace(/\+/g, " ").split("&");

	for (var i=0; i<nvPairs.length; i++) {
		var nv = nvPairs[i].split("=");
		var n  = decodeURIComponent(nv[0]);
		var v  = decodeURIComponent(nv[1]);
		if ( !(n in params) ) {
			params[n] = [];
		}
		params[n].push(nv.length === 2 ? v : null);
	}
	return params;
}//eof function

//pdf settings for report component
function PromptPDFExport(elem)
{
	var button    = jQuery('#'+elem.id);
	var boxheight = 130;
	var boxwidth  = 300;

	var a = boxwidth - button.outerWidth();
	var b = button.offset().left - a;
	var c = button.offset().top - boxheight;

	var listPaper = new Array();
	var listOrientation = new Array();

	var preloader = '<div id="pdfpreloader" style="position:relative"><img style="position: absolute; left: 144px; top: 30px;" src="img/ajax-loader2.gif"/></div>';

	header = '<h2 style="text-align: center; background-color: #EBEBEB; margin: 0; padding: 5px; border-bottom: 1px solid #C9C9C9; font-size: 11px;">PDF Properties</h2>';
	shadowstyle = '-webkit-box-shadow: 0px 0px 22px 0px rgba(97,97,97,1); -moz-box-shadow: 0px 0px 22px 0px rgba(97,97,97,1); box-shadow: 0px 0px 22px 0px rgba(97,97,97,1);';

	//button.before('<div id="promptbox" style="'+shadowstyle+' position:absolute; top:'+c+'px; left:'+b+'px; width:'+(boxwidth-4)+'px; height:'+(boxheight-4)+'px; background-color:#fff; border: 2px solid #757575;">'+header+preloader+'</div>');
	jQuery('body').append('<div id="promptbox" style="'+shadowstyle+' position:absolute; top:'+c+'px; left:'+b+'px; width:'+(boxwidth-4)+'px; height:'+(boxheight-4)+'px; background-color:#fff; border: 2px solid #757575;">'+header+preloader+'</div>');

	jQuery.ajax({
		url:'ajax_pdf_settings.php',
		type:'post',
		dataType:'json'
	}).done(function(data){

		var listPaper = '';
		jQuery.each(data.sizes,function(){
			var checked = (this.CHECKED==null ? '' : 'selected="selected"');
			listPaper += '<option '+checked+' value="'+this.VALUE+'">'+this.NAME+'</option>';
		});

		var listOrientation = '';
		jQuery.each(data.orientations,function(){
			var checked = (this.CHECKED==null ? '' : 'selected="selected"');
			listOrientation += '<option '+checked+' value="'+this.VALUE+'">'+this.NAME+'</option>';
		});

		var content = '<table cellpadding="2" style="border:none; width:100%; text-align:left;">';
		content += '<tr><td style="border:none;padding-left:10px;">Paper</td><td style="border:none"><select id="pdf_paperSize">'+listPaper+'</select></td></tr>';
		content += '<tr><td style="border:none;padding-left:10px;">Orientation</td><td style="border:none"><select id="pdf_paperOrientation">'+listOrientation+'</select></td></tr>';
		//content += '<tr><td style="border:none"></td><td style="border:none"><input class="inputButton" type="button" onclick="ExportReport(\'pdf\',jQuery(\'#'+elem.id+'\'))" value="Print"/></td></tr>';
		content += '<tr><td style="border:none"></td><td style="border:none"><input class="inputButton" type="button" onclick="GetPrintParamThenPrint(\''+elem.id+'\')" value="Print"/></td></tr>';
		content += '</table>';

		jQuery('#pdfpreloader').remove();
		jQuery('#promptbox').append(content);
	});
}

function GetPrintParamThenPrint(buttonID)
{
	var s = jQuery('#pdf_paperSize').val();
	var o = jQuery('#pdf_paperOrientation').val();
	var b = jQuery('#'+buttonID);
	ExportReport('pdf',b,s,o);
}

//generate pdf and csv button process
function ExportReport(type,elem,paper,orientation)
{
	//jquery object
	var item = jQuery(elem);

	//dapatkan get current url
	var get = ParseURLParams(document.URL);

	var getVars = '';
	for(var k in get)
	{
		getVars += '&'+k+'='+get[k][0];
	}

	//item->td->tr->tbody->table->comp
	var componentParent = item.parent().parent().parent().parent().parent();
	var componentParentId = jQuery(componentParent).attr('id');

	//form
	var form = jQuery(item).closest('form');

	if(type=='pdf')
	{
		//total width table
		var totalWidth = jQuery('#'+componentParentId+' > table:first-child').width();

		//columns width
		var collectionOfWidth = '';

		//dapatkan setiap td punya width
		var thead = jQuery('#'+componentParentId).find('table:first').find('thead').length;
		if(thead > 0) //if thead exist
		{
			jQuery('#'+componentParentId).find('table:first').find('thead').find('tr:nth-child(2)').find('th').each(function(){
				collectionOfWidth += jQuery(this).width()+'||';
			});
		}

		//action string
		var action = 'control_file.php?gen=pdf&size='+paper+'&orientation='+orientation+getVars+'&compName='+componentParentId+'&width='+totalWidth+'&cols='+collectionOfWidth;
	}
	else if(type=='csv')
	{
		var action = 'control_file.php?gen=csv&compName='+componentParentId+getVars;
	}

	form.attr('action',action);
	form.attr('target','_blank');
	form.submit();
	//reset
	form.attr('action','');
	form.attr('target','');
}//eof function

//function collapse component syafiq
function collapseComponent(elem)
{
	var callerCompDiv = jQuery(elem).parent().parent().parent().parent().parent().parent().attr('id');

	//if already collapsed
	if(jQuery(elem).find('img').attr('src') == 'img/arrow_up.gif')
	{
		jQuery('#'+callerCompDiv).find('table:first-child').find('tr:not(:first)').hide();
		jQuery(elem).find('img').attr('src','img/arrow_down.gif');
	}
	else if(jQuery(elem).find('img').attr('src') == 'img/arrow_down.gif')
	{
		jQuery('#'+callerCompDiv).find('table:first-child').find('tr:not(:first)').show();
		jQuery(elem).find('img').attr('src','img/arrow_up.gif');
	}
}

function collapseComponentCalendar(elem)
{
	var callerCompDiv = jQuery(elem).parent().parent().parent().parent().parent().parent().attr('id');

	//if already collapsed
	if(jQuery(elem).find('img').attr('src') == 'img/arrow_up.gif')
	{
		jQuery('#'+callerCompDiv).find('tr').eq(1).hide();
		jQuery(elem).find('img').attr('src','img/arrow_down.gif');
	}
	else if(jQuery(elem).find('img').attr('src') == 'img/arrow_down.gif')
	{
		jQuery('#'+callerCompDiv).find('tr').eq(1).show();
		jQuery(elem).find('img').attr('src','img/arrow_up.gif');
	}
}

function collapseComponentSearch(elem)
{
	var callerCompDiv = jQuery(elem).parent().parent().parent().parent().parent().parent().attr('id');

	//if already collapsed
	if(jQuery(elem).find('img').attr('src') == 'img/arrow_up.gif')
	{
		jQuery('#'+callerCompDiv+' table tr').not(':first').hide();
		jQuery(elem).find('img').attr('src','img/arrow_down.gif');
	}
	else if(jQuery(elem).find('img').attr('src') == 'img/arrow_down.gif')
	{
		jQuery('#'+callerCompDiv+' table tr').not(':first').show();
		jQuery(elem).find('img').attr('src','img/arrow_up.gif');
	}
}

function collapseComponentReport(elem,caller)
{
	if(caller == 'onload_default')
	{
		var img1 = 'img/arrow_down.gif';
		var img2 = 'img/arrow_up.gif';
	}
	else
	{
		var img1 = 'img/arrow_down.gif';
		var img2 = 'img/arrow_up.gif';
	}

	var callerCompDiv = jQuery(elem).parent().parent().parent().parent().parent().parent().parent().parent().parent().attr('id');
	var allTBody = jQuery('#'+callerCompDiv).find('table tbody');
	var allTBodyCnt = allTBody.length;

	var allColumnHeaderTR = jQuery('#'+callerCompDiv+' .listingHead').first().parent();

	//if already collapsed
	if(jQuery(elem).find('img').attr('src') == img2)
	{
		allColumnHeaderTR.hide();

		for(var x=1; x < allTBodyCnt; x++)
			jQuery(allTBody[x]).hide();

		jQuery(elem).find('img').attr('src',img1);
	}
	else if(jQuery(elem).find('img').attr('src') == img1)
	{
		allColumnHeaderTR.show();

		for(var x=1; x < allTBodyCnt; x++)
			jQuery(allTBody[x]).show();

		jQuery(elem).find('img').attr('src',img2);
	}
}

//http://css-tricks.com/snippets/javascript/move-cursor-to-end-of-input/
function moveCursorToEnd(el) {

	console.log(123123123);
    if (typeof el.selectionStart == "number") {
        el.selectionStart = el.selectionEnd = el.value.length;
    } else if (typeof el.createTextRange != "undefined") {
        el.focus();
        var range = el.createTextRange();
        range.collapse(false);
        range.select();
    }
}

function collectTimepickerValues(elem,name)
{
	var valueInput = jQuery(elem).parent().find('input.timepicker_value');

	var h = jQuery(elem).parent().find('[id^="timepicker_h_"]').val().strip();
	var m = jQuery(elem).parent().find('[id^="timepicker_m_"]').val().strip();

	if(jQuery(elem).parent().find('[id^="timepicker_s_"]').length > 0)
		var s = jQuery(elem).parent().find('[id^="timepicker_s_"]').val().strip();

	//var ampm 	= jQuery('#timepicker_ampm_'+name).val().strip();

	if(h == '')
		h ='00';
	if(m == '')
		m ='00';

	if(s == '' || typeof(s) == 'undefined')
		s ='00';

	jQuery(valueInput).val(h+':'+m+':'+s);
}

function flc_common_func(element,type,state,otherAttr,otherAttr2)
{
	/*
	flc_common_func('page','title','show');
	flc_common_func('page','title','hide');
	flc_common_func('page','breadcrumbs','show');
	flc_common_func('page','breadcrumbs','hide');
	flc_common_func('page','size','600');
	flc_common_func('page','html_title','replace','html code here');
	flc_common_func('page','html_title','append','html code here');
	flc_common_func('page','html_footer','html code here');
	flc_common_func('page','controls','top');
	flc_common_func('page','controls','bottom');

	flc_common_func('component','title','show','comp_name');
	flc_common_func('component','title','hide','comp_name');
	flc_common_func('component','border','show','comp_name');
	flc_common_func('component','border','hide','comp_name');
	flc_common_func('component','show','comp_name');
	flc_common_func('component','hide','comp_name');
	flc_common_func('component','html_title','replace','comp_name','html code here');
	flc_common_func('component','html_title','append','comp_name','html code here');
	flc_common_func('component','height','300','comp_name');
	flc_common_func('component','column_hide',column_index,'comp_name');


	flc_common_func('item','show','item_name');
	flc_common_func('item','hide','item_name');
	flc_common_func('item','title','hide','item_name');
	flc_common_func('item','input','show','item_name');
	flc_common_func('item','input','hide','item_name');
	flc_common_func('item','html_title','replace','item_name','html code here');
	flc_common_func('item','html_title','append','item_name','html code here');
	*/

	if(element == 'page')
	{
		if(type == 'title')
		{
			if(state == 'show')
				jQuery('h1').show();
			else if(state == 'hide')
				jQuery('h1').hide();
		}
		else if(type == 'breadcrumbs')
		{
			if(state == 'show')
				jQuery('#breadcrumbs').show();
			else if(state == 'hide')
				jQuery('#breadcrumbs').hide();
		}
		else if(type == 'size')
		{
			console.log();
			jQuery('#content').css('width',state+'px');

		}
		else if(type == 'html_title')
		{
			if(state == 'replace')
				jQuery('h1').html(otherAttr);
			else if(state == 'append')
				jQuery('h1').html(jQuery('h1').html()+otherAttr);
		}
		else if(type == 'html_footer')
		{
			var html = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableContent" style="margin-bottom:20px; border:none;"><tr><td style="border:none;">';
			html += ''+state+'';
			html += '</td></tr></table>';
			jQuery('#form1 div').eq(0).append(html);
		}
		else if(type == 'controls')
		{
			if(state == 'top')
				jQuery('.pageLevelControls').after('').prependTo('#form1').css('margin-bottom','10px');

			else if(state == 'bottom')
				jQuery('.pageLevelControls').after('').appendTo('#form1').css('margin-bottom','');
		}
	}
	else if(element == 'component')
	{
		//conflicts with component collapsible
		if(type == 'title')
		{
			if(state == 'show')
				jQuery('#'+otherAttr+' table th').eq(0).parent().show();
			else if(state == 'hide')
				jQuery('#'+otherAttr+' table th').eq(0).parent().hide();

		}
		else if(type == 'border')
		{
			if(state == 'show') {}
			else if(state == 'hide')
				jQuery('#'+otherAttr+' .tableContent').css('border','none');
		}
		else if(type == 'html_title')
		{
			if(state == 'replace')
				jQuery('#'+otherAttr+' table th').eq(0).html(otherAttr2);
			else if(state == 'append')
				jQuery('#'+otherAttr+' table th').eq(0).html(jQuery('#'+otherAttr+' table th').eq(0).html()+otherAttr2);
		}
		else if(type == 'show')
		{
			jQuery('#'+state).show();
		}
		else if(type == 'hide')
		{
			jQuery('#'+state).hide();
		}
		else if(type == 'height')
		{
			jQuery('#'+otherAttr).css('height',state+'px');
		}
		else if(type == 'column_hide')
		{
			var tr = jQuery('#'+otherAttr).find('table').eq(0).find('tr');

			jQuery(tr).each(function(){

				var td = jQuery(this).find('td');

				jQuery(td).each(function(x){
					if(x == state)
						td[x].hide();
				});
			});
		}
	}
	else if(element == 'item')
	{
		var theItemTitle = jQuery('#'+otherAttr).parent().parent().children().eq(0).children();
		var theItemInput = jQuery('#'+otherAttr).parent().parent().children().eq(1).children();

		if(type == 'title')
		{
			if(state == 'show')
				theItemTitle.show();
			else if(state == 'hide')
				theItemTitle.hide();
		}
		else if(type == 'input')
		{
			if(state == 'show')
				theItemInput.show();
			else if(state == 'hide')
				theItemInput.hide();
		}
		else if(type == 'html_title')
		{
			if(state == 'replace')
				theItemTitle.html(otherAttr2);
			else if(state == 'append')
				theItemTitle.html(theItemTitle.html()+otherAttr2);
		}
		else if(type == 'show')
		{
			jQuery('#'+state).parent().parent().show();
		}
		else if(type == 'hide')
		{
			jQuery('#'+state).parent().parent().hide();
		}
	}
}

//to create component side by side
function flc_multiple_component_row(compIDList,maxHeight,tabGroupName,caller)
{
	//console.log(caller);
	jQuery(document).ready(function()
	{
		var compIDArr 		= compIDList.split(',');
		var compIDLength 	= compIDArr.length;
		var leftMargin 		= 10; 			//px
		var rightMargin 	= leftMargin; 	//px

		//for multiple component in tab
		if(tabGroupName != undefined)
		{
			jQuery('#'+tabGroupName).css('margin-bottom','15px');
			var parentContent = jQuery('#'+tabGroupName).find('.componentArea');			//the only difference?
		}
		else
			var parentContent = jQuery('#content');

		var pageWidth = parentContent.width();
		var avgWidth = Math.floor(pageWidth/compIDLength) - leftMargin - rightMargin;

		for(var x=0; x < compIDLength; x++)
		{
			var comp = jQuery('#'+compIDArr[x]);
			comp.css('float','left')
				.css('overflow','hidden')
				.css('height',maxHeight+'px')
				.css('position','relative')
				.css('margin-right',rightMargin+'px')
				.css('margin-left','0px')
				.css('margin-bottom','15px')
				.perfectScrollbar({wheelSpeed:50,wheelPropagation:true,minScrollbarLength:50})
				.addClass('flcMultiCompRow');

			//if last component
			if(x == compIDLength-1 && compIDLength >= 2)
			{
				avgWidth = pageWidth-(x*(avgWidth+rightMargin+leftMargin))+leftMargin*2-(30-(compIDLength*10));
				comp.css('width',avgWidth+'px').css('margin-right','0px');
			}
			else
				comp.css('width',avgWidth+'px');

			//to scroll to top when restore paging button is clicked
			var restorePaging = comp.find('[name="restorePagingButton"]');

			if(restorePaging.length > 0)
				jQuery(restorePaging).attr('onclick','jQuery(\'#'+compIDArr[x]+'\').animate({scrollTop: 0},200);'+jQuery(restorePaging).attr('onclick')+';');

			//todo - repair paging button and button table
			var compTable = jQuery('#'+compIDArr[x]).find('table');
			var compTableWidth = jQuery(compTable).eq(0).width()-20;

			//resize the paging and add row rows
			for(var y=0; y < compTable.length; y++)
			{
				if(jQuery(compTable).eq(y).hasClass('flcReportButton') || jQuery(compTable).eq(y).hasClass('flcReportPaging'))
					jQuery(compTable).eq(y).css('width',compTableWidth+22+'px');
			}
		}

		//for footer
	//jQuery('#bottom').css('clear','both');
		//jQuery('<div style="clear:both; margin:top:200px;"></div>').insertAfter('#'+compIDArr[compIDLength-1]);
		//jQuery('<div style="float:left; margin:top:200px;border:1px solid black">dddddd</div>').insertAfter('#'+compIDArr[compIDLength-1]);
		jQuery('<div style="clear:both; margin:top:200px;"></div>').insertAfter('#'+compIDArr[compIDLength-1]);
		parentContent.css('overflow','hidden');
	});

	if(caller != 'window_resize')
	{
		jQuery(window).resize(function() {
			flc_multiple_component_row(compIDList,maxHeight,tabGroupName,'window_resize');
		});
	}
}

function flc_change_tab(tabGroupName,tabID,allTab,mode)
{
	var allTabArrGroup = allTab.split('|');
	var allTabID = tabID.split(',');
	
	//console.log(allTabID);
	
	if(mode == 'sticky')
	{	
		//todo (ie9 failed)
		var key = btoa(document.URL+tabGroupName);
		createCookie(key,allTabID,1);
		
		if(readCookie(key) != allTabID)
		{	
			eraseCookie(key);
			createCookie(key,allTabID,1);
		}
	}
	
	if(allTabArrGroup.length > 0)
	{
		//set tab class
		for(var a=0; a < allTabID.length; a++)
			jQuery('#tab_'+allTabID[0]).addClass('active');

		for(var a=0; a < allTabArrGroup.length; a++)
		{
			var allTabArr = allTabArrGroup[a].split(',');

			for(var x=0; x < allTabArr.length; x++)
			{
				for(var y=0; y < allTabID.length; y++)
				{
					if(allTabArr[x] == allTabID[y])
					{
						//if type:multiple tab
						if(allTabArrGroup.length > 1)
						{
							for(z=0; z < allTabArr.length; z++)
							{
								jQuery('#tab_'+allTabArr[0]).addClass('active');
								flc_common_func('component','show',allTabArr[z]);
							}
						}
						//if single tab
						else
						{
							jQuery('#tab_'+allTabArr[x]).addClass('active');
							flc_common_func('component','show',allTabArr[x]);
						}
					}
					else
					{
						jQuery('#tab_'+allTabArr[x]).removeClass('active');
						flc_common_func('component','hide',allTabArr[x]);
					}
				}
			}
		}
	}
}

//to create single component tab
function flc_tabbed_component_single(tabGroupName,compIDList,mode)
{
	var compIDListArr = compIDList.split(',');
	var activeTabClassName = '';
	var tabHTML = '<div id="'+tabGroupName+'" class="tab" style=""><table width="" border="0" cellpadding="0" cellspacing="0" class=""><tr><td style="width:5px;"></td>';

	for(var x=0; x < compIDListArr.length; x++)
	{
		//hide comp title
		flc_common_func('component','title','hide',compIDListArr[x]);

		//hide other component except first one
		if(x > 0)
			flc_common_func('component','hide',compIDListArr[x]);
	}

	for(var x=0; x < compIDListArr.length; x++)
	{
		if(x == 0)
			activeTabClassName = 'active ';
		else
			activeTabClassName = '';

		tabHTML += '<td class="'+activeTabClassName+'tabHead" id="tab_'+compIDListArr[x]+'" onclick="flc_change_tab(\''+tabGroupName+'\',\''+compIDListArr[x]+'\',\''+compIDList+'\','+mode+')">'+jQuery('#'+compIDListArr[x]).find('th').eq(0).html();
		tabHTML += '</td><td style="width:1px;"></td>';
		
	}

	tabHTML += '</tr></table><div class="componentAreaSingle"></div></div>';

	//add tab before first component in list
	jQuery(tabHTML).insertBefore('#'+compIDListArr[0]);
}

//to create multiple component tab
function flc_tabbed_component_multiple(tabGroupName,compIDList,titleList,mode)
{
	var compIDGroupArr = compIDList.split('|');
	var titleList = titleList.split('|');

	var tabHTML = '<div id="'+tabGroupName+'" class="tab" style=""><table width="" border="0" cellpadding="0" cellspacing="0" class=""><tr><td style="width:5px;"></td>';

	for(var x=0; x < compIDGroupArr.length; x++)
	{
		if(x == 0)
			activeTabClassName = 'active ';
		else
			activeTabClassName = '';

		var compIDListArr = compIDGroupArr[x].split(',');

		tabHTML += '<td class="'+activeTabClassName+'tabHead" id="tab_'+compIDListArr[0]+'" onclick="flc_change_tab(\''+tabGroupName+'\',\''+compIDGroupArr[x]+'\',\''+compIDList+'\','+mode+')">'+titleList[x];
		tabHTML += '</td><td style="width:1px;"></td>';

		for(var y=0; y < compIDListArr.length; y++)
		{
			//hide other component except first component in first tab
			if(x > 0)
				flc_common_func('component','hide',compIDListArr[y]);
		}
	}

	tabHTML += '</tr></table><div class="componentArea"></div></div>';

	//add tab before first component in list
	jQuery(tabHTML).insertBefore('#'+compIDListArr[0]);

	for(var x=0; x < compIDGroupArr.length; x++)
	{
		var compIDListArr = compIDGroupArr[x].split(',');

		//cut and paste component into tab area
		for(var y=0; y < compIDListArr.length; y++)
			jQuery('#'+compIDListArr[y]).appendTo('#'+tabGroupName+' .componentArea');
	}
}

function flc_tabbed_component(tabGroupName,type,compIDList,titleList,mode)
{
	if(mode == 'sticky')
	{	
		//todo (ie9 failed)
		var key = btoa(document.URL+tabGroupName);
		var cookieVal = readCookie(key);
		
		jQuery(document).ready(function(){
			jQuery('.tabHead[id="tab_'+cookieVal+'"]').trigger('click');
		});
	}	
	
	//mode = sticky (maintain selected tab on page refresh)
	//mode = normal (reset tab on page refresh)
	if(mode !== undefined)
		mode = "'"+mode+"'";
	else
		mode = "'normal'";
		
	//type: single/multiple
	if(type == 'single')
		flc_tabbed_component_single(tabGroupName,compIDList,mode);
	else if(type == 'multiple')
		flc_tabbed_component_multiple(tabGroupName,compIDList,titleList,mode);
}

function flc_modal_window()
{
	jQuery(document).ready(function(){

		var viewerStr = '<div id="flcModalWindowDiv"><div>';
		viewerStr += '</div></div>'

		var viewer = jQuery(viewerStr);
		var iframe = jQuery('<iframe id="flcModalWindowIframe" style="border:none;"></iframe>').css('height','96%').css('width','100%');
		
		//set css
		jQuery(viewer)
			.css('position','absolute')
			.css('left','110px')
			.css('width','90%')
			.css('height','85%')
			.css('background-color','white')
			.css('z-index','100')
			.css('border','5px solid #888')
			.css('-moz-box-shadow','0 0 30px 5px #888')
			.css('-webkit-box-shadow','0 0 30px 5px #888')
			.css('box-shadow','0 0 30px 5px #888')
			.hide();
			
		jQuery(viewer).append(iframe);
		jQuery('#content').prepend(viewer);

		jQuery('.flcModalWindowCaller').on('click',function(){

			var fixedWidthMode = false;
			var fixedHeightMode = false;
			
			var allClass = jQuery(this).attr('class').split(' ');
			
			jQuery.each(allClass, function(key,value){
				
				if(value.indexOf("flcModalWindowWidth_") == 0)
				{
					jQuery(viewer).css('width',value.split('_').pop());
					fixedWidthMode = true;
				}
					
				if(value.indexOf("flcModalWindowHeight_") == 0)
				{	
					//tak betol sgt untuk height ni kalau klik byk2 kali 
					console.log(value.split('_').pop());
					jQuery(viewer).css('height',value.split('_').pop());
					fixedHeightMode = true;
				}
			});
			
			//reset fixed width
			if(!fixedWidthMode)
				jQuery(viewer).css('width','90%');

			jQuery(viewer).css('top',30+jQuery(document).scrollTop()+'px');

			if(jQuery('#flcModalWindowDiv').length > 0)
				jQuery('#flcModalWindowDiv')
				.html('<div style="background-color:#F5F5F5;font-size:12px; color:#e0e0e0; padding:5px;text-align:right;border-bottom:1px solid #9EE0D6"><a href="javascript:void(0)" style="margin-bottom:10px;text-decoration:none;" onclick="jQuery(this).closest(\'#flcModalWindowDiv\').hide();"><span style="color:#A0A0A0;line-height:20px;margin-right:5px;">Close</span> <img style="vertical-align:top;" title="Close Window" src="img/close.png" /></a></div>')
				.append(iframe);

			jQuery('iframe#flcModalWindowIframe')
				.attr('src',jQuery(this).data('url'))
				.load(function(){
				
					var f = window.frames.flcModalWindowIframe;
					var theIframe = jQuery(f).contents();
						
					if(!fixedHeightMode)	
					{
						var iframeHeight = jQuery(theIframe).find('#content').height();	
						
						//if iframe height lebih dari viewer height or mozilla is used (cannot get iframe height), use standard height
						if(iframeHeight > jQuery(viewer).height() || jQuery.browser.mozilla)
						{
							jQuery(viewer).css('height','85%');
							jQuery(iframe).css('height','96%');
						}
						else
						{
							jQuery(viewer).height((iframeHeight)+'px');
							jQuery(iframe).height((iframeHeight-30)+'px');
						}
					}
					
					jQuery(theIframe).find('#sidebar').remove();

					jQuery(theIframe).find('#content').css('margin-left','10px');
					jQuery(theIframe).find('#header').remove();
					jQuery(theIframe).find('#pageProperties').remove();
					jQuery(theIframe).find('#bottom').remove();
							
					jQuery('#flcModalWindowDiv').show();
				});
			
			jQuery('#flcModalWindowDiv').css('cursor','move').draggable();
			
			//color manipulation
			var originalColor = '';
			
			jQuery('#flcModalWindowDiv').find('div').eq(0).on('mouseover',function(){
				originalColor = jQuery(this).css('background-color');
				jQuery(this).css('background-color','#'+shadeColor1(originalColor,'98'));
			});
			
			jQuery('#flcModalWindowDiv').find('div').eq(0).on('mouseout',function(){
				jQuery(this).css('background-color',originalColor);
			});
			
		});
	});
}

//language selector popup footer
function LanguageSelectorPopup()
{
	var popup = jQuery('#languageSelectorPopup');
	if(popup.is(':hidden')) popup.show();
}

//mouse up
jQuery(document).mouseup(function (e)
{
	var languagebox = jQuery('#languageSelectorPopup');
    if (!languagebox.is(e.target) && languagebox.has(e.target).length === 0) { languagebox.hide(); }

    var promptbox = jQuery('#promptbox');
    if (!promptbox.is(e.target) && promptbox.has(e.target).length === 0) { promptbox.remove(); }
});

//hide/show header
function ToggleHeader(elem)
{
	var header = jQuery('#header');
	var content = jQuery('#content');
	var sidebar = jQuery('#sidebar');
	var topbar = jQuery('#topMenuBar');

	var triggerid = elem.id;
	var img = jQuery('#'+triggerid+' img');

	var header_height = header.outerHeight();
	var topbar_height = topbar.outerHeight();

	if(header.is(':visible')) { //hide
		header.hide();
		if(jQuery('#topMenuBar').length > 0) { //layout top menu
			topbar.css('top',0);
			content.css('top',(content.offset().top - header_height)+'px');
		} else { //layout side menu
			content.css('top',0);
			sidebar.css('top',0);
			sidebar.css('margin-top',0);
		}
		jQuery.ajax({
			url:'ajax_showhide.php',
			type:'post',
			data:{task:'hideHeader', contentTop:content.offset().top}
		});
		jQuery('#hide-header img').attr('src','img/toggleon.png');
	} else { //show
		header.show();
		if(jQuery('#topMenuBar').length > 0) { //layout top menu
			topbar.css('top',header_height+'px');
			content.css('top',(content.offset().top + header_height)+'px');
		} else { //layout side menu
			content.css('top',header_height+'px');
		}
		jQuery.ajax({
			url:'ajax_showhide.php',
			type:'POST',
			data:{task:'showHeader', contentTop:content.offset().top}
		});
		jQuery('#hide-header img').attr('src','img/toggleoff.png');
	}
}

//hide/show sidebar
function ToggleSidebar()
{
	var isSidebarRight = (jQuery('#sidebar').css('float')=='right' ? true : false);
	var sidebar = jQuery('#sidebar');
	var content = jQuery('#content');
	var sidebarWidth = sidebar.outerWidth();
	var isPageBorderEnabled = content.hasClass('pageBorderEnabled');

	if(sidebar.is(':visible'))  {
		sidebar.hide();

		if(isPageBorderEnabled){
			content.css('margin','10px');
		} else {
			if(!isSidebarRight) content.css({'margin-left':'0px'});
			if(isSidebarRight)  content.css({'margin-right':'0px'});
		}

		jQuery.ajax({
			url:'ajax_showhide.php',
			type:'post',
			data:{task:'hideSidebar', contentTop:content.offset().top}
		});
	}
	else if(sidebar.is(':hidden')){
		jQuery('#sidebar').show();

		content.css("margin","");

		jQuery.ajax({
			url:'ajax_showhide.php',
			type:'post',
			data:{task:'showSidebar', contentTop:content.offset().top}
		});
	}
}

//hide topbar(menu)
function ToggleTopbar()
{
	var isSidebarRight = (jQuery('#sidebar').css('float')=='right' ? true : false);
	var topbar = jQuery('#topMenuBar');
	var content = jQuery('#content');
	var h = topbar.outerHeight();
	var t = content.offset().top;
	var visible = topbar.is(':visible');

	if(visible){
		topbar.hide();
		content.css('top',(t-h)+'px');
		jQuery.ajax({
			url:'ajax_showhide.php',
			type:'post',
			data:{task:'hideTopbar', contentTop:content.offset().top}
		});
	} else {
		topbar.show();
		content.css('top',(t+h)+'px');
		jQuery.ajax({
			url:'ajax_showhide.php',
			type:'post',
			data:{task:'showTopbar', contentTop:content.offset().top}
		});
	}
}

function flcPrint(elem)
{
	var button = jQuery(elem);
	var table  = button.closest('table');

	if (table.hasClass('pageLevelControls')) { //print page level
		jQuery('head').append('<link href="css/print.css" rel="stylesheet" type="text/css" media="print" />');
		window.print();
		setTimeout(function(){
			jQuery('link[href="css/print.css"]').remove();
		},1000);

	} else { //print component level
		var selectedId = button.closest('div').attr('id');
		selectedId = selectedId.trim();
		jQuery('#content #form1 > div > *').each(function(){
			if(this.id != selectedId){
				jQuery(this).addClass('temporary-print-hide');
			}
		});
		var style = '#breadcrumbs,#debug,#header,#pageProperties,#pagetitle,#sidebar,#bottom,.temporary-print-hide{display: none;}';
		style += '#'+selectedId+'{ position:absolute !important; right:0; left:0; width:100% !important; }';
		style += '#content{ background:none !important; border:none !important; }';
		jQuery('head').append('<style id="component-printing-style" type="text/css" media="print">'+style+'</style>');
		window.print();
		setTimeout(function(){
			jQuery('#component-printing-style').remove();
			jQuery('.temporary-print-hide').each(function(){
				jQuery(this).removeClass('temporary-print-hide');
			});
		},1000);
	}
}

function resizeOverflowedReport()
{
	var pageWidthRef = jQuery('#form1').width();
	var allReport = jQuery('#content .flcReport, #content .flcTabular');			//detect all reports in content page
	var tabbedComponentModifier = 0;

	for(var x=0; x < allReport.length; x++)
	{

		if(jQuery(allReport[x]).parent().hasClass('flcMultiCompRow')){}
		else
		{
			if(jQuery(allReport[x]).parent().parent().hasClass('componentArea'))
				tabbedComponentModifier = -22;

			jQuery(allReport[x]).parent()
				.css('width',pageWidthRef+tabbedComponentModifier+'px')
				.css('position','relative')
				.css('overflow','auto')
				.css('margin-left','0px')
				.css('margin-bottom','10px');

			//remove br after table
			jQuery(allReport[x]).parent().find('br').remove();

			//get paging and add row rows for the report
			var compTable = jQuery(allReport[x]).parent().find('table');
			var compTableWidth = jQuery(compTable).eq(0).width()+2;

			//resize the paging and add row rows
			for(var y=0; y < compTable.length; y++)
			{
				if(jQuery(compTable).eq(y).hasClass('flcReportButton') || jQuery(compTable).eq(y).hasClass('flcReportPaging'))
					jQuery(compTable).eq(y).css('width','100%');
			}
		}
	}

	jQuery(window).resize(function() {
		resizeOverflowedReport();
	});

}

//generate random character in the given length
function randomCharacter(charLength)
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < charLength; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

//http://www.quirksmode.org/js/cookies.html
function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = escape(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}

//http://stackoverflow.com/questions/5560248/programmatically-lighten-or-darken-a-hex-color-or-rgb-and-blend-colors
function shadeColor1(color, percent) {  
    var num = parseInt(color,16),
    amt = Math.round(2.55 * percent),
    R = (num >> 16) + amt,
    G = (num >> 8 & 0x00FF) + amt,
    B = (num & 0x0000FF) + amt;
    return (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255)).toString(16).slice(1);
}
