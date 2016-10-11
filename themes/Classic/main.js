//Manipulate DOM elements

function generateSidebarBorder()
{
	var a = jQuery('#sidebar');
	var b = jQuery('#sidebarBG');
	var location = a.css('float');

	if(location == 'left') b.css({'border-right':'1px solid #D5D5D5'});
	if(location == 'right') b.css({'border-left':'1px solid #D5D5D5'});
}


function generateSidebarMargin()
{
	var a = jQuery('#sidebar');
	var location = a.css('float');

	if(location == 'left') a.css({'margin-right':'15px'});
	if(location == 'right') a.css({'margin-left':'15px'});

	if(a.css('display') == 'none') //topmenu
	{
		var marginRight = jQuery('#content').css('margin-right');
		jQuery('#content').css({'margin-left':marginRight});
	}
}

function GetClassicMenuFix()
{
	//bullet at a nochild
	jQuery('#sidebar a.noChild').each(function(){
		var t = jQuery(this).text();
		jQuery(this).html('&#149; '+t);

		var p = jQuery(this).parent().parent().hasClass('menuLevel1');
		if(p)
		{
			jQuery(this).prev().remove();
		}
	});
}

function resizeContent()
{
	var sidebarWidth = jQuery('#sidebar').outerWidth();
	var bodyWidth = jQuery('body').outerWidth();
	jQuery('#content').css('width',bodyWidth-sidebarWidth-40+'px');
}

//execute when document ready
jQuery(document).ready(function(){
	generateSidebarBorder();
	generateSidebarMargin();
	GetClassicMenuFix();
	resizeContent();

	jQuery('#topMenu').find('#topDateText').show();
	jQuery('#topMenu').find('div').not('#topDateIcon, #topHomeLinkIcon, #topLogoutLinkIcon').show();


	//topDateIcon
	//topHomeLinkIcon
	//topLogoutLinkIcon
});
