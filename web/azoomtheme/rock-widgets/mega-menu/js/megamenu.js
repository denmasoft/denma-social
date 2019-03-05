jQuery( document ).ready(function( ) {
	'use strict';
	
	//Init Megamenu
	function rockthemes_mm_init(){
		jQuery('.megamenu').each(function(){
			var padding = 15;
			var position = jQuery(this).offset();
			var container_pos = jQuery('.header-row').offset();
			position.left = position.left - container_pos.left - padding;

						
			var width = (typeof jQuery(this).attr('data-mm-width') != 'undefined' && jQuery(this).attr('data-mm-width')) ? jQuery(this).attr('data-mm-width') : parseInt(jQuery(this).parents('.header-row').width()) - (2 * padding);
			var align = (typeof jQuery(this).attr('data-mm-align') != 'undefined') ? jQuery(this).attr('data-mm-align') : -position.left;
			var left = 0;

			if(align == 'left'){
				left = 0;
			}else if(align == 'right'){
				left = -parseInt(width) + parseInt(jQuery(this).width());		
			}else{
				left = -position.left;
			} 
						
			jQuery(this).find(' > ul').css({
				'width': width,
				'left' : left
			});
						
			var tl = 0;
			jQuery(this).find(' > ul > li').css('min-height','');
			
			jQuery(this).find(' > ul > li').each(function(){
				if(jQuery(this).find('.azoom-swiperslider').length){
					return;	
				}
				tl = Math.max(tl,jQuery(this).height());
			});
			
			/*
			**	Disabled for complex mega menu (multiple headings in the same columns)
			**	Enabled for menu order error. All heights must be the same. 
			**
			**	http://stackoverflow.com/questions/26737984/zurb-foundation-grid-repeating-columns-without-row
			*/
			//jQuery(this).find(' > ul > li').css({'min-height':tl+'px'});
			
		});		
		
		//Init left right styling or width styling for flyout menu
		jQuery('#nav ul.rtm-menu > li, #nav .rtm-menu > ul > li').each(function(){
			var padding = 15;
			var position = jQuery(this).offset();
			var container_pos = jQuery('.header-row').offset();
			position.left = position.left - container_pos.left - padding;
			var this_data_width = jQuery(this).attr('data-mm-width');
			var this_data_align = jQuery(this).attr('data-mm-align');
			
			if(!this_data_width || !this_data_align) return;
						
			var width = (typeof jQuery(this).attr('data-mm-width') != 'undefined' && jQuery(this).attr('data-mm-width')) ? jQuery(this).attr('data-mm-width') : parseInt(jQuery(this).parents('.header-row').width()) - (2 * padding);
			var align = (typeof jQuery(this).attr('data-mm-align') != 'undefined') ? jQuery(this).attr('data-mm-align') : -position.left;
			var left = 0;

			if(align == 'left'){
				left = 0;
			}else if(align == 'right'){
				left = -parseInt(width) + parseInt(jQuery(this).width());		
			}else{
				left = -position.left;
			} 
			
			jQuery(this).find(' > ul ').css({
				'width': width,
				'left' : left
			});
			
		});
		
		jQuery('.rtm-menu li').each(function(){
			//Check if this have background image 
			rockthemes_mm_bg_img_check(jQuery(this));
		});

	}
	
	
	function rockthemes_mm_bg_img_check(that){
		if(that.find('ul').length < 1 ||
			typeof that.attr('data-sub-bg-img') == 'undefined' || that.attr('data-sub-bg-img') == '' ||
			typeof that.attr('data-sub-bg-img-halign') == 'undefined' || that.attr('data-sub-bg-img-halign') == '' ||
			typeof that.attr('data-sub-bg-img-valign') == 'undefined' || that.attr('data-sub-bg-img-valign') == '' ||
			typeof that.attr('data-sub-bg-img-repeat') == 'undefined' || that.attr('data-sub-bg-img-repeat') == '' || 
			typeof that.attr('data-sub-bg-img-width') == 'undefined' || typeof that.attr('data-sub-bg-img-height') == 'undefined') {return;}
	
		var img = 'url("'+that.attr('data-sub-bg-img')+'")',
			alignh = that.attr('data-sub-bg-img-halign'),
			alignv = that.attr('data-sub-bg-img-valign'),
			pos = ' '+alignv+' '+alignh+' ',
			repeat = that.attr('data-sub-bg-img-repeat'),
			width =  that.attr('data-sub-bg-img-width'),
			height = that.attr('data-sub-bg-img-height'),
			size = ((width.indexOf('px') > -1 || width.indexOf('%') > -1) ? width : width+'px')+' '+((height.indexOf('px') > -1 || height.indexOf('%') > -1) ? height : height+'px');
			
		that.find(' > ul').css({
			'background-image':img,
			'background-repeat':repeat,
			'background-position':pos,
			'background-size':size
		});
		
	}
	
	jQuery(window).on('resize', rockthemes_mm_init);
	jQuery(document).on('rockthemes:mega_menu_resize', rockthemes_mm_init);
	rockthemes_mm_init();
	jQuery(document).trigger('rockthemes:mega_menu_ready');

});
