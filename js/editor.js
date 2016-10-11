// JavaScript Document for editors

//swap menu link options
function swapMenuLinkOption(linkType, targetID, linkID)
{
	if(linkType==1)
	{
		//enable
		if(document.getElementById(targetID)&&document.getElementById(linkID))
		{
			//document.getElementById(targetID).disabled=false;		//enable
			document.getElementById(targetID).selectedIndex=0;		//set initial index
		}

		if(document.getElementById(linkID))
		{
			document.getElementById(linkID).value='index.php?page=';	//initial link
		}
	}
	else
	{
		//disable
		if(document.getElementById(targetID))
		{
			//document.getElementById(targetID).disabled=true;		//enable
			document.getElementById(targetID).selectedIndex=1;		//set initial index
		}

		if(document.getElementById(linkID))
		{
			document.getElementById(linkID).value='';	//initial link
		}
	}
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

/**
TO PLACE QUOTE
-element: element ID
*/
function placeQuote(elem)
{
	var a = elem.value;
	a = a.replace (/\[QS\]/g, '\'');
	a = a.replace (/\[QD\]/g, '"');
	elem.value = a;
}

//change display of component attributes by component type
function changeComponentType(componentType)
{
	//tbody
	var dataBindingTbody = document.getElementById('dataBindingTbody');
	var prePostTbody = document.getElementById('prePostTbody');
	var listingTbody = document.getElementById('listingTbody');
	var customTbody = document.getElementById('customTbody');
	var masterDetailTbody = document.getElementById('masterDetailTbody');

	//switch componentType
	switch(componentType)
	{
		case 'custom':
		case 'iframe':
			customTbody.style.display = '';

			dataBindingTbody.style.display = 'none';
			prePostTbody.style.display = 'none';
			listingTbody.style.display = 'none';
			masterDetailTbody.style.display = 'none';
		break;

		case 'form_1_col':
		case 'form_2_col':
		case 'query':
		case 'query_2_col':
			dataBindingTbody.style.display = '';
			prePostTbody.style.display = '';
			listingTbody.style.display = 'none';
			customTbody.style.display = 'none';
			masterDetailTbody.style.display = 'none';
		break;

		case 'report':
		case 'tabular':
			dataBindingTbody.style.display = '';
			prePostTbody.style.display = '';
			listingTbody.style.display = '';
			customTbody.style.display = 'none';
			masterDetailTbody.style.display = 'none';
		break;

		case 'search_constraint':
			masterDetailTbody.style.display = '';

			dataBindingTbody.style.display = 'none';
			prePostTbody.style.display = 'none';
			listingTbody.style.display = 'none';
			customTbody.style.display = 'none';
		break;
	}//eof switch
}//eof function

//change display of item attributes by item type
function changeItemType(itemType)
{
	//tbody
	var lookupTbody = document.getElementById('lookupTbody');
	var additionalTbody = document.getElementById('additionalTbody');
	var chartTbody = document.getElementById('chartTbody');
	var fileUploadTbody = document.getElementById('fileUploadTbody');

	//span
	var lovEditorSpan = document.getElementById('lovEditorSpan');

	//tr
	var itemMappingTr = document.getElementById('row_itemMapping');

	jQuery('#row_itemHints').show();
	jQuery('#row_itemPlaceholder').show();
	jQuery('#row_itemTextAlign').show();
	jQuery('#row_itemAppend').show();
	jQuery('#row_itemTab').show();
	jQuery('#newItemDefaultValueText').val('');
	jQuery('#row_itemNotes').show();
	jQuery('#row_itemLenRows .inputLabel').html('Length / Rows :');
	jQuery('#row_itemLenRows .labelNote').html('Note: Width / Height');

	jQuery('#listingTbody').show();


	//switch itemtype
	switch(itemType)
	{
		case 'ajax_updater':
		case 'checkbox':
		case 'color_picker':
		case 'dropdown':
		case 'hidden':
		case 'date':
		case 'label':
		case 'label_with_hidden':
		case 'listbox':
		case 'password':
		case 'password_md5':
		case 'radio':
		case 'text':
		case 'textarea':
		case 'text_editor':
			lookupTbody.style.display = '';
			additionalTbody.style.display = '';

			chartTbody.style.display = 'none';
			fileUploadTbody.style.display = 'none';
			lovEditorSpan.style.display = 'none';
			itemMappingTr.style.display = 'none';
		break;

		case 'chart':
			chartTbody.style.display = '';

			lookupTbody.style.display = 'none';
			additionalTbody.style.display = 'none';
			fileUploadTbody.style.display = 'none';
			lovEditorSpan.style.display = 'none';
			itemMappingTr.style.display = 'none';
		break;

		case 'file':
			fileUploadTbody.style.display = '';

			lookupTbody.style.display = 'none';
			additionalTbody.style.display = 'none';
			chartTbody.style.display = 'none';
			lovEditorSpan.style.display = 'none';
			itemMappingTr.style.display = 'none';
		break;

		case 'image':
		case 'lov':
			lookupTbody.style.display = '';

			additionalTbody.style.display = 'none';
			chartTbody.style.display = 'none';
			fileUploadTbody.style.display = 'none';
			lovEditorSpan.style.display = 'none';
			itemMappingTr.style.display = 'none';
		break;

		case 'url':
			lookupTbody.style.display = '';
			lovEditorSpan.style.display = '';

			additionalTbody.style.display = 'none';
			chartTbody.style.display = 'none';
			fileUploadTbody.style.display = 'none';
			itemMappingTr.style.display = 'none';
		break;

		case 'plugin':
			lookupTbody.style.display = '';
			additionalTbody.style.display = '';
			itemMappingTr.style.display = '';

			chartTbody.style.display = 'none';
			fileUploadTbody.style.display = 'none';
			lovEditorSpan.style.display = 'none';
		break;
	}//eof switch
}//eof function

//ajax usage
var xmlHttp

//show selected component drop down
function showComponent(page, componentExcepted)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="ajax_editor.php"
	url=url+"?editor=component&page="+page+"&componentExcepted="+componentExcepted
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("hideEditorList").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//show selected component item drop down
function showComponentItem(page, component, itemExcepted)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="ajax_editor.php"
	url=url+"?editor=component&page="+page+"&component="+component+"&itemExcepted="+itemExcepted
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("hideEditorList").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//show selected control drop down
function showControl(page, controlExcepted)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="ajax_editor.php"
	url=url+"?editor=control&page="+page+"&controlExcepted="+controlExcepted
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("hideEditorList").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//show selected menu drop down
function showMenu(menuParent, menuExcepted)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="ajax_editor.php";
	url=url+"?editor=menu&menuParent="+menuParent+"&menuExcepted="+menuExcepted;
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("hideEditorList").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//show selected database drop down
function showDatabase(component, mapping)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="ajax_editor.php"
	url=url+"?editor=database&component="+component
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("hideDatabaseList").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//to filter menu editor's list
function filterMenuEditorList(parent,title)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="menu_editor.php"
	url=url+"?filter_menu=true&parentmenu="+parent+"&title="+title
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("menu_editor_list").innerHTML=xmlHttp.responseText;
			hoverTable();
			deleteFunction();
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//to filter bl editor's list
function filterBLEditorList(type,name)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="bl_editor.php"
	url=url+"?filter_bl=true&type="+type+"&name="+name
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("bl_editor_list").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

//to filter message editor's list
function filterMessageEditorList(type,name)
{
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
		alert ("Browser does not support HTTP Request")
		return
	}

	var url="message_editor.php"
	url=url+"?filter_message=true&type="+type+"&name="+name
	xmlHttp.onreadystatechange=function ()
	{
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
		{
			document.getElementById("message_editor_list").innerHTML=xmlHttp.responseText
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
}

function GetXmlHttpObject()
{
var xmlHttp=null;
try
{
// Firefox, Opera 8.0+, Safari
xmlHttp=new XMLHttpRequest();
}
catch (e)
{
//Internet Explorer
try
{
xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
}
catch (e)
{
xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
}
}
return xmlHttp;
}
