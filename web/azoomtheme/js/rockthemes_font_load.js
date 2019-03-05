(function(){
	'use strict';
	/*
	**	Check if the fonts loaded
	**
	**	@since	:	1.3
	*/

	var menu_font_fam = rockthemes.fonts.menu_font_family;
	if(menu_font_fam.indexOf('"') > -1){
		menu_font_fam = menu_font_fam.match(/".*?"/g).toString().split('"').join('');
	}else if(menu_font_fam.indexOf("'") > -1){
		menu_font_fam = menu_font_fam.match(/'.*?'/g).toString().split("'").join("");
	}	
	var all_fonts = [menu_font_fam];
	for(var f = 0; f< rockthemes.fonts.font_families.length; f++){
		all_fonts.push(rockthemes.fonts.font_families[f]);
	}

	/*
	**	All of the test strings for the font icons should be here in dts (defined test strings)
	**
	*/
	var cfms = ['icomoon'],
		cfus = [rockthemes.fonts.icomoon_url],
		dts = {
			'icomoon': '\uE003\uE005',
			'FontAwesome': '\uf000',
			'Pe-icon-7-stroke': '\uf000',
		},
		nts = {};
	
	//Azoom uses icomoon as default. So add it to the "new test string" : nts
	nts.icomoon = dts.icomoon;
	
	if(typeof rockthemes.fonts.libs != 'undefined' && rockthemes.fonts.libs.length > 0){
		for(var l = 0; l < rockthemes.fonts.libs.length; l++){
			cfms.push(rockthemes.fonts.libs[l].name);
			cfus.push(rockthemes.fonts.libs[l].url);
			nts[rockthemes.fonts.libs[l].name] = dts[rockthemes.fonts.libs[l].name];
		}
	}
	
	//Header and Menu Font = hmf
	WebFont.load({
		google: {
			families: all_fonts,
		},
		custom: {
			families: cfms,
			testStrings: nts,
			urls :cfus
		},
		fontactive:function(font_name, fvd){
			//Do Nothing
		},
		fontinactive:function(font_name, fvd){
			//Do Nothing
		},
		active: function(){
			jQuery(document).trigger('rockthemes:mega_menu_resize');
			jQuery(document).trigger('rockthemes:all_fonts_loaded');
			jQuery('html').addClass('rockthemes_fonts_loaded');
		},
		inactive: function(){
			jQuery(document).trigger('rockthemes:mega_menu_resize');
			jQuery(document).trigger('rockthemes:all_fonts_loaded');
			jQuery('html').addClass('rockthemes_fonts_loaded');
		}
	});
})();
