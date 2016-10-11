/*
Uncompressed Javascript | Modern-v2 Theme
Author : Luqman Shariffudin
*/

// Document Ready
jQuery(document).ready(function(){

	var isSidebarRight = (jQuery('#sidebar').css('float')=='right' ? true : false);

	//mouseup
	jQuery(document).mouseup(function (e)
	{
		//hide menu suggest if click outside
		var menuSuggest = jQuery('#menu_suggestions');
		if(!menuSuggest.is(e.target) && menuSuggest.has(e.target).length===0) {
			menuSuggest.hide();
		}
	});

	//Click event : hide-sidebar
	//Toggle show/hide sidebar
	jQuery('#hide-sidebar').click(function(){
		var sidebar = jQuery('#sidebar');
		var content = jQuery('#content');
		var sidebarWidth = sidebar.width();

		if(sidebar.is(':visible'))  { 
			sidebar.hide();
			if(!isSidebarRight) content.css({'left':'0'});
			if(isSidebarRight)  content.css({'right':'0'});
		} 
		else if(sidebar.is(':hidden')){ 
			jQuery('#sidebar').show();
			if(!isSidebarRight) content.css({'left':sidebarWidth+'px'});
			if(isSidebarRight)  content.css({'right':sidebarWidth+'px'});
		}
	});

	//hide top bar
	jQuery('#hide-topbar').click(function(){
		var topbar = jQuery('#topMenuBar');
		var content = jQuery('#content');
		var h = topbar.height();
		var t = parseFloat(content.css('top'));
		var visible = topbar.is(':visible');
		if(visible){
			topbar.hide();
			content.css('top',(t-h)+'px');
		} else {
			topbar.show();
			content.css('top',(t+h)+'px');
		}
	});

	//detect layout then arrange
	if(isSidebarRight){
		jQuery('#content').css({
			'left':'0',
			'right':jQuery('#sidebar').width()+'px'
		});

		jQuery('#hide-sidebar').css('left','auto').css('right','0');

		jQuery('.topMenuDiv').each(function(){
			var asal = parseInt(jQuery(this).css('right'),10);
			var mod = asal+33;
			jQuery(this).css('right',mod+'px');
		});

		jQuery('#logo').css('left','17px');

		jQuery('#menu_suggestions').css('right','9px');
	}
});

//Header ready
jQuery('#header').ready(function(){
	//Clear top icon text
	jQuery('.topMenuDiv').each(function(){
		var text = jQuery(this).text();

		jQuery(this).contents().filter(function(){
			return (this.nodeType == 3);
		}).remove();

		if( jQuery(this).attr('id') == 'topTime' ){
			jQuery(this).prepend('<div style="float: left; line-height: 40px; margin-right: 10px; color: #ffffff; ">'+text+'</div>');
		}	
	});
});