var displayList = [];

jQuery('ul.topMenuList.menuLevel1').ready(function(){
	var list = jQuery('ul.topMenuList.menuLevel1 > li');
	var maxWidth = TOP_MENU_MAX_WDITH;
	var totalWidth = 0;
	list.each(function(){ totalWidth += jQuery(this).width(); });

	var turn = 1;
	var w = 0;

	list.each(function(){
		w += jQuery(this).width();

		function Categorized(id){
			if(w < maxWidth*turn){
				if(displayList[turn-1] === undefined) displayList[turn-1] = [];
				if(displayList[turn-1] !== undefined) displayList[turn-1].push(id);
			} else {
				turn += 1;
				Categorized(id);
			}
		}
		Categorized(this.id);
	});

	DisplayMenuCat(0);
});

function DisplayMenuCat(cat)
{
	jQuery('ul.topMenuList.menuLevel1 > li').each(function(){ jQuery(this).hide(); });

	var displayListCatLen = displayList[cat].length;

	for(var i=0; i < displayListCatLen; i++){
		jQuery('#'+displayList[cat][i]).css('visibility','').show();
	}
	if(cat == 0 || cat<0){
		if(displayList[cat+1] != undefined && jQuery('#buttonMenuNext').length == 0){ jQuery('ul.topMenuList.menuLevel1').append('<li><a id="buttonMenuNext" onclick="DisplayMenuCat('+(cat+1)+')" style="display:block;padding:0;width:16px;text-align:center;" href="javascript:void(0)">&#187;</a></li>'); }
		else{
			jQuery('#buttonMenuNext').attr('onclick','DisplayMenuCat('+(cat+1)+')');
			jQuery('#buttonMenuNext').parent().show();
		}
	}
	if(cat>0){
		if(jQuery('#buttonMenuPrevious').length == 0){ jQuery('ul.topMenuList.menuLevel1').prepend('<li><a id="buttonMenuPrevious" onclick="DisplayMenuCat('+(cat-1)+')" style="display:block;padding:0;width:16px;text-align:center;" href="javascript:void(0)">&#171;</a></li>');	}
		else{jQuery('#buttonMenuPrevious').parent().show();}
		if(displayList[cat+1] != undefined){
			jQuery('#buttonMenuNext').attr('onclick','DisplayMenuCat('+(cat+1)+')');
			jQuery('#buttonMenuNext').parent().show();
		}
		if(displayList[cat-1] != undefined){
			jQuery('#buttonMenuPrevious').attr('onclick','DisplayMenuCat('+(cat-1)+')');
			jQuery('#buttonMenuPrevious').parent().show();
		}
	}
}