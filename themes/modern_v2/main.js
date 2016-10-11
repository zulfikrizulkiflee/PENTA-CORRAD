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

	//detect layout then arrange
	if(isSidebarRight){
		jQuery('#hide-sidebar').css('left','auto').css('right','0');

		jQuery('.topMenuDiv').each(function(){
			var asal = parseInt(jQuery(this).css('right'),10);
			var mod = asal+33;
			jQuery(this).css('right',mod+'px');
		});

		jQuery('#logo').css('left','17px');
		jQuery('#menu_suggestions').css('right','9px');
	}

	//pindahkan elemen profileblock jika topmenu
	if(jQuery('#topMenuBar').length > 0)
	{
		jQuery('#profileBlock').hide();
		jQuery('#profileBlock').appendTo('#topMenuBar');
	}

	//display profileblock bila click profilename
	jQuery('#profileName').click(function(){
		if(jQuery('#profileBlock').is(':visible')){
			jQuery(this).css({'background-color':'','color':''});
			jQuery('#profileBlock').hide();
		} else {
			jQuery(this).css({'background-color':'#333','color':'#ffffff'});
			jQuery('#profileBlock').show();
		}
	});
	
	//$docHeight = jQuery(document).outerHeight()-jQuery('#footer').outerHeight()-jQuery('#header').outerHeight()-14;
	//jQuery('#sidebar').css('min-height',$docHeight+'px');
	
	//resize type:report
	resizeOverflowedReport();
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
