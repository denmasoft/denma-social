// JavaScript Document
jQuery(document).ready(function(){
	'use strict';
	
	//Extend Modernizr
	rockthemes_extend_modernizr();
	
	//Detect Browser CSS prefix
	var spfx = '';//Style prefix
	switch(BrowserDetect.browser){
		case 'Chrome':
		spfx = '-webkit-';
		break;
		
		case 'Explorer':
		spfx = '-ms-';
		break;
		
		case 'Firefox':
		spfx = '-moz-';
		break;
		
		case 'Safari':
		spfx = '-webkit-';
		break;
		
		case 'Opera':
		spfx = '-o-';
		break;
	}
	if(spfx != ''){
		rockthemes.frontend_options.style_prefix = spfx;	
	}
	
	//Check if mobile
	if(Modernizr.ismobile){
		jQuery('html').addClass('disable-transition');	
	}
		

	//Activate Nice Scroll
	if(rockthemes.frontend_options.activate_smooth_scroll === 'true' ){
		
		/*
		**	Nice Scroll - the scroll bar from right side
		*/
		if(!(/Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i).test(navigator.userAgent || navigator.vendor || window.opera) && !(navigator.userAgent.match(/msie|trident/i))){
			var rockthemes_nice_s_object = {
				cursorcolor: typeof rockthemes.colors !== 'undefined' && rockthemes.colors ? rockthemes.colors.main_color : "#56CCC8",
				cursorwidth: "14px",
				cursorborder: "none",
				cursorborderradius: "4px",
				zindex: "999999",
				bouncescroll: "true",
				cursoropacitymin: "0.3",
				background: "rgba(0,0,0,0.3)",
				horizrailenabled : (rockthemes.resposivity === 'false') ? true : false
			};

			
			//Set some values to make scroll smoother
			rockthemes_nice_s_object.scrollspeed = 98;
			rockthemes_nice_s_object.mousescrollstep = 48;
			
			if(rockthemes.frontend_options.nicescroll_style_enabled !== 'yes'){
				//Disable Nicescroll scrollbar style
				jQuery('html').addClass('nicescroll-style-disabled');	
				rockthemes_nice_s_object.autohidemode = 'hidden';
			}
			
			var rockthemes_nice_s = jQuery('html').niceScroll(rockthemes_nice_s_object);
			rockthemes.elements = new Object();
			rockthemes.elements.nice_scroll = rockthemes_nice_s;

			jQuery(document).on('rockthemes:nicescroll_enable', function(){
				rockthemes_nice_s.show();
			});
			jQuery(document).on('rockthemes:nicescroll_disable', function(){
				//Remove Nice Scroll HTML transform details (delay breaks the effect header fade effect)
				jQuery('html').css('transform','');
				rockthemes_nice_s.stop();
				rockthemes_nice_s.hide();
			});
		
		}
	}
	
	
	//Enable scrolling events before starting any elements.
	rockthemes_scroll_events();
		
	if(typeof rockthemes.settings == "undefined"){
		rockthemes.settings = new Object();	
	}
	//Undermenu box uses different classes for active cart and search. We store their active class names here
	rockthemes.settings.undermenu_box_classes = ['woocommerce-cart-active', 'search-box-active'];
	
	
	if(typeof rockthemes.init_queue == 'undefined'){
		//Some elements load async, this variable is global and will hold those elements in the queue
		rockthemes.init_queue = new Object();
		
		//Full screen video elements in queue. Youtube loads async
		rockthemes.init_queue.fullscreen_bg_videos = new Array();
	}
	
		
	rockthemes_fullscreen_elements();
	jQuery(window).smartresize(rockthemes_fullscreen_elements);

	
	//Set header motions height. Disable it if it's mobile
	if(jQuery('.rockthemes-before-header.intro-effect-slide').children().length && (jQuery(window).width() >= 768 || jQuery(window).height() >= 768)){
		var eh = jQuery('.rockthemes-before-header.intro-effect-slide').height(); //Effect Height
		if(eh < 667) eh = 667;
		jQuery('.rockthemes-before-header.intro-effect-slide').css('height', eh+'px');
	}else{
		//If there are not header top elements, then remove the class
		jQuery('.rockthemes-before-header.intro-effect-slide').removeClass('intro-effect-slide');
	}
	
	rockthemes_multi_bg_colors();
	jQuery(window).smartresize(function(){
		setTimeout(rockthemes_multi_bg_colors,180);
	});
	
	jQuery('.disable-link').click(function(e){
		e.preventDefault();
	});
	
	rocktheme_wrap_iframe_videos();
	
	rockthemes_main_menu();
	
	rockthemes_mobile_menu();
	
	rockthemes_menu_ajax_search();
	
	//WooCommerce Details
	rockthemes_woocommerce_elements_init();
	rockthemes_menu_ajax_woocommerce_cart();
		
	//Check background videos for muting them. Youtube, Vimeo, HTML5
	rockthemes_check_bg_videos();
	
	//Check if there are any flash. If so make them responsive.
	rockthemes_responsive_flash();
	
	//Activate Hover Effects
	rockthemes_activate_hover();
	
	//Activate if there are load more buttons
	rockthemes_activate_load_more();
	
	//Activate category ajax filters
	rockthemes_activate_cat_filter_ajax();
		
	//Activate element's javascripts
	rockthemes_activate_elements_js();
	
	//Activate Appear for motions
	rockthemes_activate_down_arrows();
	
	//Activate Animations of elements (Uses css/animate.css  file)
	rockthemes_activate_animations();
	
	//Activate Loader
	rockthemes_activate_loader_motion();
	
	//If font loading system is active, we will also set visibility to hidden for the "body". This will set it to visible
	jQuery(document).on('rockthemes:all_fonts_loaded', function(){
		//Full loading system will use this. Currently disabled
		jQuery('body').css('visibility','visible');	
		
		var hmf_elements = ['.sticky-header-wrapper', '.main-header-area', '.header-top-2', '.azoom-title-breadcrumbs'];
		//if(jQuery(hmf_elements[0]+'.not-visible').length){
			for(var i=0;i<hmf_elements.length;i++){
				if(jQuery(hmf_elements[i]+'.not-visible').length){
					jQuery(hmf_elements[i]+'.not-visible').removeClass('not-visible');
				}
			}
		//}
		jQuery(document).trigger('rockthemes:mega_menu_resize');
	});
	
	if(rockthemes.fonts.activate_font_loading === 'true'){
		if(jQuery('html').hasClass('rockthemes_fonts_loaded')){
			jQuery(document).trigger('rockthemes:all_fonts_loaded');
		}else{
			/*
			**	IE Font Loading fix
			**	IE sometimes does not trigger the event for font loading. This fix will trigger it on a timer
			*/
			var font_max_try = 10,
				font_current_try = 0,
				font_int = setInterval(function(){
				if(jQuery('html').hasClass('rockthemes_fonts_loaded') || font_current_try >= font_max_try){
					jQuery(document).trigger('rockthemes:all_fonts_loaded');
					clearInterval(font_int);
				}
				font_current_try++;
			},500);
		}
	}else{
		jQuery(document).trigger('rockthemes:all_fonts_loaded');
	}
	
		

});




function rockthemes_main_menu(){
	
	var on_menu = false,
		temp_om = false,
		out_menu = false,
		t_time	= 180,
		o_time	= 300,
		h_timer	= false,
		o_timer = false,
		nav = jQuery('#nav'),
		sel = '#nav > ul > li.menu-item-has-children, ul#nav > li.menu-item-has-children';
		
	
	var mega = {
		hover : function(that){
			on_menu = that.attr('id');
			
			that.addClass('hovering');
			
			clearTimeout(h_timer);
			h_timer = setTimeout(function(){
							
				//Return if we have moved mouse out of the menu
				if(!on_menu) return;
				
				//Return if this is the same menu item
				if(on_menu != that.attr('id') || !that.hasClass('hovering')){
					return;	
				}
				
				jQuery('#nav .hover, #nav .hovering').removeClass('hover hovering');
				
				that.addClass('hover');
				
			}, t_time);
		},
		out: function(that){
			
			that.removeClass('hovering');
	
			on_menu = false;
			out_menu = that.attr('id');
			
			clearTimeout(o_timer);
			o_timer = setTimeout(function(){
				
				//Return if user hovered another menu item
				if(on_menu || that.hasClass('hovering')) return;
				jQuery('#nav .hover, #nav .hovering').removeClass('hover hovering');
				
			}, o_time);
		}
	}
	
	jQuery(document).on('mouseenter', sel, function(e){
		var that = jQuery(this);
		mega.hover(that);
	});
	jQuery(document).on('mouseleave', sel, function(e){
		var that = jQuery(this);
		mega.out(that);
	});
	
	
	if(1 == 1){
		//TO DO : Bind to an option
		if(typeof Modernizr != "undefined" && Modernizr.touch){
			jQuery(document).on('touchend click', '#nav .menu-item-has-children > a', function(e){
				e.preventDefault();
				var that = jQuery(this).parent();
				if(that.hasClass('hover')){
					that.removeClass('hover');
				}else{
					that.siblings().removeClass('hover').end().addClass('hover');	
				}
			});
		}
	}

}




function rockthemes_activate_loader_motion(){
	var loader_classes = ['.woocommerce-loader', '.load-more-button-loader', '.ajax-category-navigation-loader', '.azoom-search-loader'];

	var use_gif = false;
	if(jQuery('html').hasClass('ie9')){
		use_gif = true;	
	}
	
	if(use_gif){
		if(jQuery('.rockthemes-css-loader').length){
			jQuery('.rockthemes-css-loader > div > div').remove();
			jQuery('.rockthemes-css-loader .gif-loader').removeClass('hidden');
			jQuery('.rockthemes-css-loader').removeClass('rockthemes-css-loader');
		}
		
		if(jQuery('.azoom-search-loader').length){
			jQuery('.azoom-search-loader').after(rockthemes.gif_loader);
		}
		
		for(var i = 0; i<loader_classes.length; i++){
			if(jQuery(loader_classes[i]).length){
				jQuery(loader_classes[i]).remove();	
			}
		}
	}else{
		if(jQuery('.rockthemes-css-loader .gif-loader').length){
			jQuery('.rockthemes-css-loader .gif-loader').remove();
		}
	}
}




function rockthemes_scroll_events(){
	if(typeof rockthemes.events == 'undefined'){
		rockthemes.events = new Object();	
	}
	rockthemes.events.scroll_keys = [37, 38, 39, 40];
	var don_wheel = window.onwheel;// Default On Wheel

	jQuery(document).on('rockthemes:scroll_events_enable', function(){
		window.onwheel = don_wheel;
		if(rockthemes.frontend_options.activate_smooth_scroll === 'true'){
			//If nicescroll acivated trigger this event.
			jQuery(document).trigger('rockthemes:nicescroll_enable');
		}
		
		jQuery(document).off('wheel DOMMouseScroll onmousewheel', wheel);
		jQuery(window).off('onmousewheel onwheel', wheel);
		document.onkeydown = null;
	});
	jQuery(document).on('rockthemes:scroll_events_disable', function(){
		if(rockthemes.frontend_options.activate_smooth_scroll === 'true'){
			//If nicescroll acivated trigger this event.
			jQuery(document).trigger('rockthemes:nicescroll_disable');
		}
		jQuery(document).on('wheel DOMMouseScroll onmousewheel', wheel);
		jQuery(window).on('onmousewheel onwheel', wheel);
		document.onkeydown = keydown;		
	});

	
	function rockthemes_preventDefault(e) {
	  e = e || window.event;
	  if (e.preventDefault)
		  e.preventDefault();
	  if(e.stopImmediatePropagation)
	  	  e.stopImmediatePropagation();
		  
	  e.returnValue = false;  
	  return false;
	}
	function keydown(e) {
		for (var i = rockthemes.events.scroll_keys.length; i--;) {
			if (e.keyCode === rockthemes.events.scroll_keys[i]) {
				rockthemes_preventDefault(e);
				return;
			}
		}
	}
	function wheel(e) {
	  rockthemes_preventDefault(e);
	}

}



function rockthemes_activate_animations(){
	if(!jQuery('.rockthemes-animate').length) return;
	
	/*
	**	Disable animations for mobile devices
	**
	*/
	if(Modernizr.ismobile || !Modernizr.cssanimations){
		jQuery('.rockthemes-animate').removeClass('rockthemes-animate');
		return;
	}
	
		
	var ended_events = 'animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd';

	jQuery('.rockthemes-animate').each(function(){
		var that = jQuery(this);
		that.appear();
		that.on('appear', function(){
			//Avoid firing again and overload on scroll event.
			that.off('appear');
			that.appearOff();
			
			//Delay the animation
			setTimeout(function(){
				if(that.find('img').length){
					that.imagesLoaded(function(){
						setTimeout(function(){
							activate_animate_animation(that, ended_events);	
						},180);
					});
				}else{
					activate_animate_animation(that, ended_events);	
				}
			}, parseInt(that.attr('data-animation-delay-time')));

		});
	});
}

function activate_animate_animation(that, ended_events){
	that.addClass(that.attr('data-animation-class')+' animated');

	if(that.find(".rockthemes-list").length){
		rockthemes_ea_list(that, that.attr('data-animation-class')+' animated');
	}
	
	that.on(ended_events, function(){
		that.removeClass('rockthemes-animate '+that.attr('data-animation-class')+' animated');
		
		that.off(ended_events);
	});
}

//Elements Animation : ea.
function rockthemes_ea_list(el,anim){
	//Use el references instead of "that". Because we use "that" in the setTimeout loop below.
	el.find("li").css("opacity","0");
	
	var latest_i = 0;
	list_element.find(" ul > li").each(function(i){
		var that = jQuery(this);
		setTimeout(function(){
			that.addClass(anim);
		}, 300 * i);
		latest_i = i;
	});
}





function rockthemes_woocommerce_elements_init(){
	//WooCommerce Review Tab - Open from the link above title
	jQuery(document).on("click", ".woocommerce-review-link", function(){
		var tab_id = jQuery(this).attr('href');
		var content = jQuery(tab_id+'.rock-tab-header')
		if(typeof content == 'undefined' || content.length < 1) return;
		content.trigger('click');
	});
	
	
}




function rocktheme_wrap_iframe_videos(){
	var elems = ['iframe','embed', 'video', '.video-player'];	
	var dismiss = ['.fp-player', '.flowplayer-video', '.curvyslider-video'];
	for (var i = 0; i < elems.length; i++){
		jQuery(elems[i]).each(function(){;
			var that = jQuery(this),
				dismissed = false;
			
			//Oauth Escape
			if(that.parent().is('body') || that.parent().is('html')){
				return;	
			}
			
			for(var d = 0; d<dismiss.length; d++){
				if(that.parents(dismiss[d]).length){
					dismissed = true;	
				}
			}
			
			if(!that.parents('.azoom-iframe-container').length && !that.parent().hasClass('rockthemes-background-video') && !dismissed){
				console.log('WRAPPIONG '+that.html());
				that.wrap('<div class="azoom-iframe-container"></div>');
			}
		});
	}
}



//Helper function. Currently disabled but might be useful on updates.
function rockthemes_extend_modernizr(){
	//Extend Modernizr
	if(Modernizr){
		Modernizr.addTest('ipad', function () {
		  return !!navigator.userAgent.match(/iPad/i);
		});
		 
		Modernizr.addTest('iphone', function () {
		  return !!navigator.userAgent.match(/iPhone/i);
		});
		 
		Modernizr.addTest('ipod', function () {
		  return !!navigator.userAgent.match(/iPod/i);
		});
		 
		Modernizr.addTest('appleios', function () {
		  return (Modernizr.ipad || Modernizr.ipod || Modernizr.iphone);
		});
		
		Modernizr.addTest('android', function () {
		  return !!navigator.userAgent.match(/Android/i);
		});
		
		Modernizr.addTest('iemobile', function () {
		  return !!navigator.userAgent.match(/IEMobile/i);
		});
		
		Modernizr.addTest('ismobile', function () {
		  return (Modernizr.ipad || Modernizr.ipod || Modernizr.iphone || Modernizr.android || Modernizr.iemobile);
		});
	}
	
}

/*Activate Elements*/
function rockthemes_activate_elements_js(){
	
	//Toggles
	if(jQuery('.rock-toggles-container').length){
		rockthemes_ae_toggles();
	}
	
	//Tabs
	if(jQuery('.rock-tabs-container').length){
		rockthemes_ae_tabs();
	}
	
	//Achievement
	if(jQuery('.rock-achievement').length){
		rockthemes_ae_achievement();
	}
	
	//Iconic Text
	if(jQuery('.rock-iconictext-container').length){
		rockthemes_ae_iconictext();	
	}
	
	//Button
	if(jQuery('.button[data-button-js-colors="true"]').length){
		rockthemes_ae_buttons();	
	}
	
	//Steps
	if(jQuery('.azoom-steps').length){
		rockthemes_ae_steps();	
	}
	
	//Skills
	if(jQuery('.azoom-skill').length){
		rockthemes_ae_skills();	
	}
	
	//Love 
	if(jQuery('.azoom-love-icon').length){
		rockthemes_ae_love_icon();
	}
	
	//References 
	if(jQuery('.rock-references-builder').length){
		rockthemes_ae_references();
	}
	
	//Alert Boxes
	if(jQuery('.alert-box').length){
		rockthemes_ae_alertbox();	
	}
	
	//Team Members
	if(jQuery('.azoom-team-members').length){
		rockthemes_ae_teammembers();	
	}
	
	//Image element
	if(jQuery('.azoom-single-image').length){
		rockthemes_ae_singleimage();	
	}
	
}


//Activate Elements - Single Image
function rockthemes_ae_singleimage(){
	if(jQuery('.azoom-overflow-image').length){
		rockthemes_overflow_image();
		jQuery(window).smartresize(rockthemes_overflow_image);
	}
	
	if(jQuery('.azoom-snap-image').length){
		rockthemes_snap_image();
		jQuery(window).smartresize(rockthemes_snap_image);
	}
}

function rockthemes_overflow_image(){
	jQuery('.azoom-overflow-image').each(function(){
		if(jQuery(window).width() <= 800){
			jQuery(this).css({'bottom':''});
			return;	
		}
		
		jQuery(this).parents('.columns').css({'min-height':'10px'});
		
		var image = jQuery(this),
			sg = jQuery(this).parents('.rockthemes-unique-grid'),
			vs = typeof sg !== 'undefined' ? sg.hasClass('rsb-vertical-space') : false,
			df = parseInt(image.attr('data-overflow')),
			th = image.parents('.row').outerHeight(true) + df - image.parents('.columns').outerHeight();
		
		if(vs && !image.parents('.row').hasClass('rsb-vertical-space')){
			th += 105;
		}else if(vs){
			th -= 105;	
		}
		
		
		image.css({
			'bottom':'-'+th+'px'
		});
	});
}



function rockthemes_snap_image(){
	jQuery('.azoom-snap-image').each(function(){
		jQuery(this).css({'left':'','right':''});
		
		if(jQuery(window).width() <= 800){
			return;	
		}
						
		var image = jQuery(this),
			m = jQuery(this).parents('.main-container'),
			ds = image.attr('data-snap-image'),
			snap = 0,
			le = image.hasClass('azoom-overflow-image') ? parseInt(image.parent().css('padding-left')) : 0;

		
		switch(ds){
			case 'left':
			snap = m.offset().left - image.offset().left + le;
			image.css({
				'left':snap+'px'
			});
			break;
			
			case 'right':
			image.imagesLoaded(function(){
				snap = -((m.offset().left + m.width()) - (image.offset().left + image.width()) + le);
				image.css({
					'right':snap+'px'
				});
			});
			break;
		}		
		
	});
}




//Activate Elements - Team Members
function rockthemes_ae_teammembers(){
	jQuery('.azoom-team-members').each(function(){
		var that = jQuery(this);
		
		that.find('.team-member-article').click(function(e){
			e.preventDefault();
			var id = jQuery(this).parents('.azoom-team-members').attr('id');
			var no_slide = false;
			
			
			var content = jQuery(this).find('.member-details').clone();
			
			//Remove any fixed overlay if exists
			if(jQuery('.'+id).length){
				no_slide = true;
				jQuery('.'+id).empty();
				jQuery('.'+id).append(jQuery('<div class="team-member-box-close">×</div>'));
				jQuery('.'+id).append(content);
			}else{
				var box = jQuery('<div class="team-member-box '+id+'"><div class="team-member-box-close">×</div></div>');
				box.append(content);
				that.prepend(box);
				that.find('.team-member-box').slideDown();
			}
			
			var go_top = parseInt(jQuery('.'+id).offset().top) - 30;
			if(jQuery('.header-sticky-active').length){
				go_top = go_top - parseInt(rockthemes.menu.sticky_height);	
			}
			
			if(jQuery('#wpadminbar').length){
				go_top = go_top - parseInt(jQuery('#wpadminbar').height());	
			}
			
			jQuery('html, body').animate({scrollTop:go_top},1000);
			
			jQuery(document).on('click touchend', '.team-member-box-close',function(){
				var that_tm = jQuery(this).parents('.team-member-box');
				//Remove any fixed overlay if exists
				if(that_tm.length){
					that_tm.slideUp(480, function(){
						if(that_tm.length){
							that_tm.remove();
						}
					});
				}
			});
		});
	});
}




//Activate Element - Alert Box
function rockthemes_ae_alertbox(){
	jQuery('.alert-box').each(function(){
		var that = jQuery(this);
		if(that.find('.alert-box-close').length){
			that.on("click touchend", ".alert-box-close", function(){
				that.slideUp();
			});
		}
	});
}


//Activate Elements - References
function rockthemes_ae_references(){
	jQuery('.rock-references-builder').each(function(){
		var that = jQuery(this);
		that.rock_ref = new Object();
		that.rock_ref.time = parseInt(that.attr('data-time'));
		that.rock_ref.id = that.attr('id');
		that.rock_ref.timer;
		that.rock_ref.current_row = 0;
		that.rock_ref.total_in_row = that.attr('data-total-in-row');
		that.rock_ref.auto_slide = that.attr('data-auto-slide');
		
		
		that.rock_ref.total_rows = jQuery('#'+that.rock_ref.id+' ul').length;
		
		if(that.rock_ref.auto_slide == 'true'){
			that.rock_ref.timer = setInterval(function(){
				change_references(false,that);	
			}, that.rock_ref.time);
		}
								
		jQuery('#'+that.rock_ref.id+' ul').each(function(i){
			jQuery(this).css({'margin-top':'-'+jQuery(this).position().top+'px'});
		});
		
		jQuery(document).on('click', '#'+that.rock_ref.id+' .references_previous_button', function(){
			if(that.rock_ref.auto_slide == 'true'){
				clearInterval(that.rock_ref.timer);
			}
			
									
			change_references(true,that);
			if(that.rock_ref.auto_slide == 'true'){
				that.rock_ref.timer = setInterval(function(){
					change_references(true,that);	
				}, that.rock_ref.time);
			}
		});
							
		jQuery(document).on('click', '#'+that.rock_ref.id+' .references_next_button', function(){
			if(that.rock_ref.auto_slide == 'true'){
				clearInterval(that.rock_ref.timer);
			}
									
			change_references(false,that);
			
			if(that.rock_ref.auto_slide == 'true'){
				that.rock_ref.timer = setInterval(function(){
					change_references(false,that);	
				}, that.rock_ref.time);
			}
		});
							
		var first_time = false;
		
		that.imagesLoaded(function(){
			rockthemes_references_resize(that.rock_ref.id, first_time);
		});
		
		jQuery(window).smartresize(function(){
			rockthemes_references_resize(that.rock_ref.id, first_time);
		});
		
	
	});
}
function rockthemes_references_resize(id, first_time){
	jQuery('#'+id+' .absolute-class').first().find('li').css('margin-top','0');
	var height = jQuery('#'+id+' .absolute-class').first().height();
	
	jQuery('#'+id+' .rock-references-content').css('height',height);
	if(!first_time){
		height = jQuery('#'+id+' .absolute-class').first().find('li').first().height();
		jQuery('#'+id+' .azoom-element-responsive-header').css({top: height/ 2 - 13});
		first_time = true;
	}
}
function change_references(previous,that){
	//Hide current references
	jQuery('#'+that.rock_ref.id+' .absolute-class').eq(that.rock_ref.current_row).find('li').each(function(i){
		jQuery(this).stop(true,true).animate({'margin-top':'-30px', 'opacity':'0'}, (i * 200) + 100);
	});
	
	jQuery('#'+that.rock_ref.id+' .absolute-class').eq(that.rock_ref.current_row).css({'zIndex':'0'});
	
	if(typeof previous !== 'undefined' && previous === true){
		that.rock_ref.current_row--;
		
		if(that.rock_ref.current_row < 0){
			that.rock_ref.current_row = that.rock_ref.total_rows - 1;	
		}
	}else{
		that.rock_ref.current_row++;
		
		if(that.rock_ref.current_row >= that.rock_ref.total_rows){
			that.rock_ref.current_row = 0;	
		}
	}

	jQuery('#'+that.rock_ref.id+' .absolute-class').eq(that.rock_ref.current_row).css({'zIndex':'1'});
	
	
	jQuery('#'+that.rock_ref.id+' .absolute-class').eq(that.rock_ref.current_row).find('li').each(function(i){
		jQuery(this).css({'margin-top':'60px'});
		jQuery(this).stop(true,true).animate({'margin-top':'0px', 'opacity':'1'}, (i * 150) + 250);
	});
}








//Activate Elements - Skills
function rockthemes_ae_skills(){
	// Single Element
	jQuery('.azoom-skill').each(function(){
		var that = jQuery(this);
		
		if(that.parents('.rockthemes-animate.skill-animating').length){
			
			return;
		}else if(that.parents('.rockthemes-animate').length){
			
			that.parents('.rockthemes-animate').addClass('skill-animating');
			var r = that.parents('.rockthemes-animate.skill-animating');
			r.appear();
			r.on('appear', function(){
				r.off('appear');
				r.appearOff();
				r.find('.azoom-skill').each(function(i){
					var t = jQuery(this);
					if(!t.find('.active').length){
						if(Modernizr.csstransitions){
							setTimeout(function(){
								t.addClass('active');
								t.off('appear');
								t.appearOff();
							}, parseInt(i * 600));
						}else{
							t.addClass('active');
							t.off('appear');
							t.appearOff();
						}
					}
				});
			});
			
		}else{
			
			that.appear();
			
			that.on('appear', function(){
				if(!that.find('.active').length){
					setTimeout(function(){
						that.addClass('active');
						that.off('appear');
						that.appearOff();
					},600);
				}
			});
		}
	});
	
}


//Activate Elements - Love Icon
function rockthemes_ae_love_icon(){
	
	jQuery(document).on('click touchend', '.azoom-love-icon', function(e){
		e.preventDefault();
		
		var that = jQuery(this);
		if(typeof that.attr('data-loved-this') !== 'undefined' && 
			that.attr('data-loved-this') && 
			that.attr('data-loved-this') === 'yes'){
			
			//Loved this already :)
			return;		
		}
		
		if(typeof that.attr('data-post-id') !== 'undefined' && that.attr('data-post-id') && that.attr('data-post-id') !== ''){
			var sd = {post_id:that.attr('data-post-id')};// sd = Send Data
			jQuery.post(rockthemes.ajaxurl, {action:'azoom_love_ajax', _ajax_nonce:rockthemes.nonces.love, data:sd}, 
				function(data){
					
					
					if(data == 'success'){
						var n = jQuery('.azoom-love-icon[data-post-id="'+that.attr('data-post-id')+'"]').attr('data-loved-this', 'yes');
						if(n.find('span').length){
							n.find('span').html(parseInt(n.find('span').html()) + 1);
						}
					}
				}
			);
		}
	});
}


//Activate Elements - Steps
function rockthemes_ae_steps(){
	
	rockthemes_ae_steps_init_resize();
	jQuery(window).smartresize(rockthemes_ae_steps_init_resize);

	jQuery('.azoom-steps .step-icon').click(function(){
		///rockthemes_se_elem_clicked(jQuery(this));
	});
	
	
	jQuery('.azoom-steps').each(function(){
		var that = jQuery(this);
		var size = (parseInt(that.attr('data-min-width')) / that.find('li').length);
		
		var starting_x = 0;//e.originalEvent.touches[0].pageX;
		var ending_x = 0;
		var matrix = (that.css("transform")).substr(7, (that.css("transform")).length - 8).split(', ');
		var last_size = matrix[4];
		
		var first_x_val = -(parseInt(that.find('li').first().offset().left + ((parseInt(that.attr('data-min-width')) / that.find('li').length) / 2)) - 35);
		//We have used .position() in desktop version. Haven't tested it on mobile version. Mobile version might need this change as well.
		first_x_val = first_x_val + Number(last_size);
		
		var first_x_val_desk = -(parseInt(that.find('li').first().position().left + ((parseInt(that.attr('data-min-width')) / that.find('li').length) / 2)) - 35);
		first_x_val_desk = first_x_val_desk + Number(last_size);
		
		that.on('touchstart', function(e){
			starting_x = e.originalEvent.touches[0].pageX;
			ending_x = parseInt(starting_x - e.originalEvent.touches[0].pageX);
			matrix = (that.css("transform")).substr(7, (that.css("transform")).length - 8).split(', ');
			last_size = matrix[4];
			that.removeClass('azoom-transition');
		}).on("touchmove", function(e){
			e.preventDefault();
			ending_x = parseInt(starting_x - e.originalEvent.touches[0].pageX);
			var diff = parseInt(last_size - ending_x);

			if(Math.abs(ending_x) < 5)return;
			//
			that.css({
				'transform':'translateX('+diff+'px)',
				'-webkit-transform':'translateX('+diff+'px)'
			});
		}).on("touchend", function(e){
			that.addClass('azoom-transition');
			if(Math.abs(ending_x) < 5) return;
						
			var diff = parseInt(last_size - ending_x);
			
			var new_val = parseInt(last_size) - (ending_x + (size - ((ending_x) % size)));
			if(ending_x < 0){
				new_val = parseInt(new_val) + (size * 2);
			}
			

			if(new_val < -(parseInt(that.attr('data-min-width')) - (size / 2))){
				new_val = first_x_val;	
			}else if( new_val > (first_x_val - 10)){
				new_val = first_x_val;	
			}

			
			that.css({
				'transform':'translateX('+new_val+'px)',
				'-webkit-transform':'translateX('+new_val+'px)'
			});

		});
		
		
		
		that.on('mousedown', function(e){
			that.addClass('mousedown');
			starting_x = e.pageX - that.offset().left;
			ending_x = parseInt(starting_x - (e.pageX - that.offset().left));
			matrix = (that.css("transform")).substr(7, (that.css("transform")).length - 8).split(', ');
			last_size = matrix[4];
			that.removeClass('azoom-transition');
		});
		jQuery('body').on("mousemove", function(e){		
			if(!that.hasClass('mousedown')){
				e.preventDefault();				
				return true;
			}
			
			e.stopPropagation();
			
			ending_x = parseInt(starting_x - (e.pageX - that.offset().left));
			var diff = parseInt(last_size - ending_x);

			if(Math.abs(ending_x) < 5)return;

			that.css({
				'transform':'translateX('+diff+'px)',
				'-webkit-transform':'translateX('+diff+'px)'
			});
		});
		jQuery('body').on("mouseup", function(e){
			e.preventDefault();
			
			if(!that.hasClass('mousedown')) return;
			that.removeClass('mousedown');
			
			that.addClass('azoom-transition');
			if(Math.abs(ending_x) < 5) return;
			
			
			var diff = parseInt(last_size - ending_x);
			
			var new_val = parseInt(last_size) - (ending_x + (size - ((ending_x) % size)));
			if(ending_x < 0){
				new_val = parseInt(new_val) + (size * 2);
			}
			

			if(new_val < -(parseInt(that.attr('data-min-width')) - (size / 2))){
				new_val = first_x_val_desk;	
			}else if( new_val > (first_x_val_desk - 10)){
				new_val = first_x_val_desk;	
			}

			
			that.css({
				'transform':'translateX('+new_val+'px)',
				'-webkit-transform':'translateX('+new_val+'px)'
			});

		});
		
		
	});
	
	
							
	jQuery('.azoom-steps .step-back').click(function(e){
		var t_steps = jQuery(this).parents('.azoom-steps');
		if(t_steps.parent().width() < parseInt(t_steps.attr('data-min-width')) && t_steps.find('li.active').length > 1){
			var size = t_steps.find('li').first().width() + 20;//parseInt(t_steps.width() / t_steps.find('li').length);
			var matrix = (t_steps.css("transform")).substr(7, (t_steps.css("transform")).length - 8).split(', ');
			var last_size = matrix[4];
			t_steps.css({
				'transform': 'translateX('+(parseInt(last_size) + size)+'px)',
				'-webkit-transform': 'translateX('+(parseInt(last_size) + size)+'px)',
				'-ms-transform': 'translateX('+(parseInt(last_size) + size)+'px)',
			});
		}
		
		rockthemes_se_elem_clicked(jQuery(this).parents('li').find('.step-icon'));
		if(jQuery(this).parents('li').prev().length && jQuery(this).parents('li').prev().hasClass('done')){
			jQuery(this).parents('li').prev().removeClass('done');	
		}
	});
								
	jQuery('.azoom-steps .step-next').click(function(e){		
		var t_steps = jQuery(this).parents('.azoom-steps');
		if(t_steps.parent().width() < parseInt(t_steps.attr('data-min-width')) && t_steps.find('li.active').length < t_steps.find('li').length){
			var size = t_steps.find('li').first().width() + 20;//parseInt(t_steps.width() / t_steps.find('li').length);
			var matrix = (t_steps.css("transform")).substr(7, (t_steps.css("transform")).length - 8).split(', ');
			var last_size = matrix[4];
			t_steps.css({
				'transform': 'translateX('+(parseInt(last_size) - size)+'px)',
				'-webkit-transform': 'translateX('+(parseInt(last_size) - size)+'px)',
				'-ms-transform': 'translateX('+(parseInt(last_size) - size)+'px)',
			});
		}
		
		if(jQuery(this).parents('li').next().length){
			rockthemes_se_elem_clicked(jQuery(this).parents('li').next().find('.step-icon'));
		}
	});
		
		
	jQuery('.azoom-steps').each(function(){
		var that = jQuery(this);
		if(that.attr('data-start_steps') === 'none') return;
		
		that.appear();
		
		that.on('appear', function(){
			if(!that.find('.active').length){
				if(!Modernizr.touch && that.attr('data-start_steps') === 'all' && parseInt(that.attr('data-min-width')) < jQuery(window).width()){					
					rockthemes_se_elem_clicked(that.find('li .step-icon'));
				}else{
					rockthemes_se_elem_clicked(that.find('li').first().find('.step-icon'));
				}
				that.off('appear');
				that.appearOff();
			}
		});
	});
						
}
function rockthemes_ae_steps_init_resize(){
	//Init Steps
	jQuery('.azoom-steps').each(function(){
		var data_width = typeof jQuery(this).attr('data-min-width') !== '' && jQuery(this).attr('data-min-width') !== '' ? parseInt(jQuery(this).attr('data-min-width')) : 960;
		if(parseInt(jQuery(this).find(' > ul > li').length % 2) === 0){
			data_width += jQuery(this).find(' > ul > li').first().width();
		}
		
		
		if(jQuery(this).parent().width() < parseInt(data_width)){
			var f = jQuery(this).find('li').first();
			var x_val = -(parseInt( ((parseInt(jQuery(this).attr('data-min-width')) / jQuery(this).find('li').length) / 2)) - 35);
			
			
			
			if(jQuery(this).hasClass('responsive')){
				var matrix = (jQuery(this).css("transform")).substr(7, (jQuery(this).css("transform")).length - 8).split(', ');
				var last_size = matrix[4];
				
			}
			
			jQuery(this).addClass('responsive');
			jQuery(this).css({
				'min-width':jQuery(this).attr('data-min-width')+'px',
				'transform':'translateX('+x_val+'px)',
				'-webkit-transform':'translateX('+x_val+'px)',
				'-moz-transform':'translateX('+x_val+'px)',
				'-ms-transform':'translateX('+x_val+'px)'
			});
		}else if(jQuery(this).hasClass('responsive')){
			jQuery(this).removeClass('responsive');	
			jQuery(this).css({
				'transform':'translateX(0px)',
				'-webkit-transform':'translateX(0px)',
				'-moz-transform':'translateX(0px)',
				'-ms-transform':'translateX(0px)'
			});
		}
	});
}
function rockthemes_se_elem_clicked(elem){
	var that = elem.parents('li');
	var steps = that.parents('.azoom-steps');
	var start_class = steps.attr('class');
	
	
	
	if(steps.hasClass('connect-steps')){
		var this_i = that.parents('ul').find('li').index(that);
		var not_connected = false;
		
		//Check if all elements till this has activated
		for(var b = 0; b < this_i; b++){
			if(!steps.find('li:eq('+b+')').hasClass('active')){
				not_connected = true;
				break;
			}
		}
		
		for(var a = this_i + 1; a < steps.find('li').length; a++){
			if(steps.find('li:eq('+a+')').hasClass('active')){
				not_connected = true;
				break;
			}
		}
		
		if(not_connected){
			return;	
		}
	}
	
	that.toggleClass('active');
	
	if(that.hasClass('done')){
		that.removeClass('done');
	}
	
	if(that.hasClass('active')){
		elem.css({'background-color':that.attr('data-step-color')});
		that.css({'background-color':that.attr('data-step-color')});
		that.find('.step-details-line').css({'background-color':that.attr('data-step-color')});
		if(that.index() % 2 === 0){
			that.parents('.azoom-steps').addClass('azoom-steps-margin-bottom');
		}else{
			that.parents('.azoom-steps').addClass('azoom-steps-margin-top');
		}
	}else{
		elem.css({'background-color':''});
		that.css({'background-color':''});
		that.find('.step-details-line').css({'background-color':''});
	}
	
	if(elem.parents('.azoom-steps').hasClass('jump-steps')){
		//Check back steps
		if(that.hasClass('active')){
			for(var i = 0; i< that.parents('ul').find('li').index(that); i++){
				
				steps.find('li:eq('+i+')').find('.step-icon').css({'background-color':steps.find('li:eq('+i+')').attr('data-step-color')});
				steps.find('li:eq('+i+')').css({'background-color':steps.find('li:eq('+i+')').attr('data-step-color')});
				steps.find('li:eq('+i+')').find('.step-details-line').css({'background-color':steps.find('li:eq('+i+')').attr('data-step-color')});
				steps.find('li:eq('+i+')').addClass('active done');
				
				if(i % 2 === 0){
					steps.addClass('azoom-steps-margin-bottom');
				}else{
					steps.addClass('azoom-steps-margin-top');
				}
			}
			
		}else{
			for(var t = that.parents('ul').find('li').index(that); t < steps.find('li').length; t++){
				
				steps.find('li:eq('+t+')').removeClass('active done');
				steps.find('li:eq('+t+')').find('.step-icon').css({'background-color':''});
				steps.find('li:eq('+t+')').css({'background-color':''});
				steps.find('li:eq('+t+')').find('.step-details-line').css({'background-color':''});
			}
		}
	}else{
		if(that.prev().length){
			that.prev().addClass('done');
		}
	}
	jQuery('.regtwo').removeClass('done');


	if(!that.parents('.azoom-steps').find('.active').length){
		that.parents('.azoom-steps').removeClass('azoom-steps-margin-top azoom-steps-margin-bottom');
	}
	
}




//Activate Elements - Button
function rockthemes_ae_buttons(){
	jQuery('.button[data-button-js-colors="true"]').each(function(){
		var that = jQuery(this);
		that.on('mouseenter', function(){
			var btn = jQuery(this);
			var bg_hover = btn.attr('data-bg_color_hover');
			var text_hover = btn.attr('data-text_color_hover');
			var border_hover = btn.attr('data-border_color_hover');
	
			if(btn.attr("data-bg-disabled") && btn.attr("data-bg-disabled") == "true"){
				that.css({'border-color':border_hover, 'color':text_hover, 'background':bg_hover});
			}else if(btn.hasClass('button-border-bottom')){
				that.css({'background':bg_hover, 
						  'box-shadow':'0px 3px 0px '+border_hover,
						  '-webkit-box-shadow':'0px 3px 0px '+border_hover,
						  '-moz-box-shadow':'0px 3px 0px '+border_hover,
						  'color':text_hover});
			}else{
				that.css({'background':bg_hover, 'color':text_hover});
			}
		});
							
		that.on('mouseleave', function(){
			var btn = jQuery(this);
			var bg_default = btn.attr("data-bg_color_default");
			var text_default = btn.attr("data-text_color_default");
			var border_color = btn.attr('data-border_color');
	
			if(btn.attr("data-bg-disabled") && btn.attr("data-bg-disabled") == "true"){
				that.css({'border-color':border_color, 'color':text_default, 'background':'none'});
			}else if(btn.hasClass('button-border-bottom')){
				that.css({'background':bg_default, 
						  'box-shadow':'0px 6px 0px '+border_color, 
						  '-webkit-box-shadow':'0px 6px 0px '+border_color, 
						  '-moz-box-shadow':'0px 6px 0px '+border_color, 
						  'color':text_default});
			}else{
				that.css({'background':bg_default, 'color':text_default});
			}
		});
	});
	
	
	
	//Iframe Modal Buttons
	jQuery(document).trigger('rockthemes:activate_lightbox');
}




//Activate Elements - Iconic Text
function rockthemes_ae_iconictext(){
	jQuery('.rock-iconictext-container').each(function(){
		var that = jQuery(this);
		
		if(that.hasClass('full-width-box') && that.hasClass('top')){
			//Do not do anything. Because it's effect will be handled via CSS3
			return;	
		}
		
		that.on('mouseenter', function(){
			var icon = jQuery(this).find('.rockicon-container');
			var bg_color = icon.attr('data-bg-color');
			var bg_hover_color = icon.attr('data-bg-hover-color');
			var icon_color = icon.attr('data-icon-color');
			var icon_hover_color = icon.attr('data-icon-hover-color');

			if(typeof icon.attr('data-box-fill') != 'undefined' && icon.attr('data-box-fill').indexOf('border') > -1){
				icon.stop(true,true).animate({"border-color":bg_hover_color, "color":icon_hover_color},180);
			}else if(icon.attr("data-bg-disabled") && icon.attr("data-bg-disabled") == "true"){
				icon.stop(true,true).animate({"color":icon_hover_color},280);
			}else{
				icon.stop(true,true).animate({"backgroundColor":bg_hover_color, "color":icon_hover_color},180);
			}
		});
							
		that.on('mouseleave', function(){
			var icon = jQuery(this).find(".rockicon-container");
			var bg_color = icon.attr('data-bg-color');
			var bg_hover_color = icon.attr('data-bg-hover-color');
			var icon_color = icon.attr('data-icon-color');
			var icon_hover_color = icon.attr('data-icon-hover-color');
	
			if(typeof icon.attr('data-box-fill') != 'undefined' && icon.attr('data-box-fill').indexOf('border') > -1){
				icon.stop(true,true).animate({"border-color":bg_color, "color":icon_color},180);
			}else if(icon.attr("data-bg-disabled") && icon.attr("data-bg-disabled") == "true"){
				icon.stop(true,true).animate({"color":icon_color},180);
			}else{
				icon.stop(true,true).animate({"backgroundColor":bg_color, "color":icon_color},180);
			}
		});
	});
}





//Activate Elements - Achievement
function rockthemes_ae_achievement(){
	
	
	jQuery('.rock-achievement').each(function(){
		var that = jQuery(this),
			that_id = that.attr('id')+'_number',
			achievement = jQuery('#'+that_id);;
		
		/*
		**	Disable Achievement Odometer on mobile devices
		*/
		if(Modernizr.ismobile){
			achievement.removeClass('odometer');
		}

		switch(that.data('mode')){
			case 'static':
				if(Modernizr.ismobile){
					achievement.html(that.attr('data-value'));
				}else{
					that.appear();
					jQuery(document).on('appear', '#'+that.attr('id'), function(){
						//Set a little delay. This will fix the trigger
						var u = new CountUp(that_id, 0, that.attr('data-value'));
						u.start();
						
						jQuery(document).off('appear', '#'+that.attr('id'));
						that.appearOff();
					});
				}
			break;	
			
			case 'function_php':
				if(Modernizr.ismobile){
					achievement.html(that.attr('data-value'));
				}else{
					that.appear();
					jQuery(document).on('appear', '#'+that.attr('id'), function(){
						var u = new CountUp(that_id, 0, that.attr('data-value'));
						u.start();
						jQuery(document).off('appear', '#'+that.attr('id'));
						that.appearOff();
					});
				}
			break;
			
			case 'function_js':
				if(!Modernizr.ismobile){
					that.appear();
				}
				
				var fn = window[that.data('function_js')];

				if(typeof fn === 'function'){
					var number = fn();
					if(Modernizr.ismobile){
						achievement.html(number);	
					}else{
						jQuery(document).on('appear', '#'+that.attr('id'), function(){
							var u = new CountUp(that_id, 0, number);
							u.start();
							jQuery(document).off('appear', '#'+that.attr('id'));
							that.appearOff();
						});
					}
				}
			break;
			
			case 'function_ajax':
				if(!Modernizr.ismobile){
					that.appear();
				}
				var fn = that.data('function_ajax');
				var data = {function_name:fn};
				if(Modernizr.ismobile){
					jQuery.post(rockthemes.ajaxurl, {action:'rockthemes_achievement_ajax', _ajax_nonce:rockthemes.nonces.achievement, data:data}, function(data){
						if(data && typeof data.number != 'undefined'){
							achievement.html(data.number);	
						}
					});
				}else{
					jQuery(document).on('appear', '#'+that.attr('id'), function(){
						jQuery.post(rockthemes.ajaxurl, {action:'rockthemes_achievement_ajax', _ajax_nonce:rockthemes.nonces.achievement, data:data}, function(data){
							if(data && typeof data.number != 'undefined'){
								var u = new CountUp(that_id, 0, data.number);
								u.start();
							}
						});
						jQuery(document).off('appear', '#'+that.attr('id'));
						that.appearOff();
					});
				}
			break;
			
			default :
				if(Modernizr.ismobile){
					achievement.html(that.attr('data-value'));
				}else{
					that.appear();
					jQuery(document).on('appear', '#'+that.attr('id'), function(){
						var u = new CountUp(that_id, 0, that.attr('data-value'));
						u.start();
						jQuery(document).off('appear', '#'+that.attr('id'));
						that.appearOff();
					});
				}
			break;
		}

	});
}



//Activate Elements - Tabs
function rockthemes_ae_tabs(){
	jQuery('.rock-tabs-container').each(function(){
		var tab = jQuery(this);
		var hash_on = tab.hasClass('tab-hash-active') ? true : false;
		
		//Look for hashtag on the address bar
		var address_hash = location.hash;
		if(address_hash.indexOf("/") > -1){
			address_hash = false;	
		}
		if(typeof address_hash != 'undefined' && address_hash && tab.find(address_hash).length){
			//Remove old active element\'s active class and hide it\'s content
			tab.find('.tabs-motion-content.active').css('display','none').removeClass('active');
			tab.find('.rock-tab-header.active').removeClass('active');
				
			var ref = '#'+jQuery(address_hash).attr('data-tab-ref')+' .'+jQuery(address_hash).attr('data-content-ref');
			var tabRef = jQuery(address_hash).attr('data-tab-ref');
				
			//Add new 
			jQuery(address_hash).addClass('active');
			jQuery(ref).css({'opacity':'0.1', 'display':'block'}).addClass('active');
			jQuery(ref).stop(true,true).animate({'opacity':'1'},280);
		}
		
		
		tab.on('click touchend', '.rock-tab-header', function(e){
			var ref = '#'+jQuery(this).attr('data-tab-ref')+' .'+jQuery(this).attr('data-content-ref');
			var tabRef = jQuery(this).attr('data-tab-ref');
				
			//Remove old active element\'s active class and hide it\'s content
			jQuery('#'+tabRef+' .tabs-motion-content.active').css('display','none').removeClass('active');
			jQuery('#'+tabRef+' .rock-tab-header.active').removeClass('active');
				
			//Add new 
			jQuery(this).addClass('active');
			jQuery(ref).css({'opacity':'0.1', 'display':'block'}).addClass('active');
			jQuery(ref).stop(true,true).animate({'opacity':'1'},280);
			
			//Deeplinking - ID for tab hash
			if(hash_on){
				var this_hash = typeof jQuery(this).attr('id') != 'undefined' && jQuery(this).attr('id') ? jQuery(this).attr('id') : false;
				if(!this_hash) return;
				
				setTimeout(function(){
					if(history.pushState) {
						history.pushState(null, null, '#'+this_hash);
					}else {
						location.hash = this_hash;
					}
				},100);
			}
		});
	});
}



//Activate Elements - Toggles
function rockthemes_ae_toggles(){
	var icon_lib = (typeof rockthemes.fonts.use_icomoon != 'undefined' && rockthemes.fonts.use_icomoon === 'true') ? 'icomoon' : 'fontawesome';
	var address_hash = location.hash;
	if(address_hash.indexOf("/") > -1){
		address_hash = false;	
	}
	
	jQuery('.rock-toggles-container').each(function(){
		var toggle = jQuery(this);
		var hash_on = toggle.hasClass('toggle-hash-active') ? true : false;
		
		//Look for hashtag on the address bar
		if(typeof address_hash != 'undefined' && address_hash && toggle.find(address_hash).length){
			
			if(toggle.hasClass('multiple-mode')){
				toggle.find('.active .rock-toggle-content').slideToggle(280);
				toggle.find('.active .rock-toggle-header .main-toggle-icon').removeClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-up6' : 'fa fa-chevron-up');
				toggle.find('.active .rock-toggle-header .main-toggle-icon').addClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-down6' : 'fa fa-chevron-down');
				toggle.find('.active').removeClass('active');
			}
			
			jQuery(address_hash).addClass('active');
			jQuery(address_hash).find('.rock-toggle-content').slideToggle(280);
			jQuery(address_hash).find('.rock-toggle-header .main-toggle-icon').removeClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-down6' : 'fa fa-chevron-down');
			jQuery(address_hash).find('.rock-toggle-header .main-toggle-icon').addClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-up6' : 'fa fa-chevron-up');
		}
			
			
		//Multiple Toggle
		toggle.on('click touchend', '.rock-toggle-header', function(e){
			e.preventDefault();
			
			if(toggle.hasClass('multiple-mode')){
				if(jQuery(this).parent().hasClass('active') && jQuery(this).parent().find('.rock-toggle-content').css('display') != 'none') return;
				
				toggle.find('.active .rock-toggle-content').slideToggle(280);
				toggle.find('.active .rock-toggle-header .main-toggle-icon').removeClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-up6' : 'fa fa-chevron-up');
				toggle.find('.active .rock-toggle-header .main-toggle-icon').addClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-down6' : 'fa fa-chevron-down');
				toggle.find('.active').removeClass('active');
				
				jQuery(this).parent().addClass('active');
				jQuery(this).parent().find('.rock-toggle-content').slideToggle(280);
				jQuery(this).parent().find('.rock-toggle-header .main-toggle-icon').removeClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-down6' : 'fa fa-chevron-down');
				jQuery(this).parent().find('.rock-toggle-header .main-toggle-icon').addClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-up6' : 'fa fa-chevron-up');
			}else{
				//Single Toggle	
				if(jQuery(this).parent().hasClass('active')){
					jQuery(this).parent().removeClass('active');
					jQuery(this).parent().find('.rock-toggle-header .main-toggle-icon').removeClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-up6' : 'fa fa-chevron-up');
					jQuery(this).parent().find('.rock-toggle-header .main-toggle-icon').addClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-down6' : 'fa fa-chevron-down');
				}else{
					jQuery(this).parent().addClass('active');
					jQuery(this).parent().find('.rock-toggle-header .main-toggle-icon').removeClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-down6' : 'fa fa-chevron-down');
					jQuery(this).parent().find('.rock-toggle-header .main-toggle-icon').addClass(icon_lib === 'icomoon' ? 'icomoon icomoon-icon-arrow-up6' : 'fa fa-chevron-up');
				}
				jQuery(this).parent().find('.rock-toggle-content').slideToggle(280);
			}
				

			if(hash_on){
				var this_hash = typeof jQuery(this).parent().attr('id') != 'undefined' && jQuery(this).parent().attr('id') ? jQuery(this).parent().attr('id') : false;
				if(!this_hash) return;
				
				setTimeout(function(){
					if(history.pushState) {
						history.pushState(null, null, '#'+this_hash);
					}else {
						location.hash = this_hash;
					}
				},480);
			}

		});
	});
}





function rockthemes_activate_down_arrows(){
	
	//Check if header motion element is activated
	if(jQuery('.rockthemes-before-header.intro-effect-slide').length){
		
		jQuery(document).on('rockthemes:intro_effect_canvas_resize', function(){
			jQuery('.rockthemes-before-header.intro-effect-slide').css('height', '');
			if(jQuery('.rockthemes-before-header.intro-effect-slide').children().length){
				//OSX Safari Fix
				jQuery('.rockthemes-before-header.intro-effect-slide').css('height', jQuery('.rockthemes-before-header.intro-effect-slide').children().outerHeight()+'px');
			}else{
				jQuery('.rockthemes-before-header.intro-effect-slide').css('height', jQuery('.rockthemes-before-header.intro-effect-slide').outerHeight()+'px');
			}
		});
		jQuery('.rockthemes-before-header.intro-effect-slide').appear();
		jQuery('div[data-rsb-fullscreen="true"], section[data-rsb-fullscreen="true"]').each(function(){
			if(jQuery(this).find('.rockthemes-curvy-slider').length < 1){
				jQuery(this).addClass('header');
			}
		});
		jQuery('.rockthemes-curvy-slider').addClass('header');

		jQuery(document).trigger('rockthemes:intro_effect_canvas_resize');
		
		var timeOut = null;
		
		jQuery(document).on('appear', '.rockthemes-before-header.intro-effect-slide', function(e,that){

			if(that.hasClass('modify') && !that.hasClass('modify-motion') && parseInt(that.offset().top + that.outerHeight()) - 40 > jQuery(window).scrollTop()){
						
				jQuery(document).trigger('rockthemes:scroll_events_disable');
				rockthemes_overlay_transparent_enable();

				that.addClass('modify-motion').removeClass('modify');
				
				jQuery('html,body').stop(true,true).delay(400).animate({scrollTop:parseInt((that.offset().top))},1100, function(){
					that.removeClass('modify-motion');
					rockthemes_overlay_transparent_disable();
					jQuery(document).trigger('rockthemes:sticky_menu_resize');
					jQuery(document).trigger('rockthemes:scroll_events_enable');
				});
			}
	
		});
		jQuery(document).on('disappear', '.rockthemes-before-header.intro-effect-slide', function(e,that){
			if(that.hasClass('modify-motion') && !that.is(':appeared')){
				that.removeClass('modify-motion');
				jQuery(document).trigger('rockthemes:sticky_menu_resize');
			}
		});
		
	}
	
	
	//Inline navigation down arrow linking. This linking will use smooth scrolling as well.
	jQuery(document).on('click touchend', '.azoom-down-arrow-container', function(e){
		
		var that = jQuery(this).parents('.rockthemes-unique-grid');
		
		if(that.parents('.rockthemes-before-header').length){
			jQuery(document).trigger('rockthemes:scroll_events_disable');
			rockthemes_overlay_transparent_enable();
		}
		
		var next_el = null;
		var found = false;
		
		next_el = that.nextAll('.rockthemes-unique-grid');
		jQuery('.rockthemes-unique-grid').each(function(i){
			if(found){
				next_el = jQuery(this);	
				found = false;
			}
			
			if(typeof jQuery(this).attr('id') !== 'undefined' && jQuery(this).attr('id') == that.attr('id')){
				found = true;	
			}
		});
		
		
		
		var use_intro_effect = false;
		//If effect used in before header area, we will go to the menu as next element
		if(that.parents('.rockthemes-before-header').length){
			if(jQuery('.header-top-2').length){
				next_el = jQuery('.header-top-2');
			}else{
				if(jQuery('.sticky-header-wrapper').length){
					next_el = jQuery('.sticky-header-wrapper');	
				}else{
					if(jQuery('.main-header-area').length){
						next_el = jQuery('.main-header-area');
					}
				}
			}
			
			if(that.parents('.rockthemes-before-header').hasClass('intro-effect-slide')){
				var header_that = that.parents('.rockthemes-before-header');
				
				if(!header_that.hasClass('modify-motion') && !header_that.hasClass('modify')){
					header_that.addClass('modify modify-motion');
				
					use_intro_effect = true;
					if(timeOut)clearTimeout(timeOut);
					timeOut = setTimeout(function(){
						if(header_that.hasClass('modify-motion')){
							header_that.removeClass('modify-motion');
						}
						jQuery(document).trigger('rockthemes:sticky_menu_resize');
						
					},1400);
				}
			}
		}
		
		setTimeout(function(){
			if(that.parents('.rockthemes-before-header').length){
				
				jQuery(document).trigger('rockthemes:scroll_events_enable');
				rockthemes_overlay_transparent_disable();
			}
		},1500);
		
		if(typeof next_el == 'undefined' || !next_el || typeof next_el.offset() == 'undefined' || !next_el.offset()) return;
		
		var wp_nav_height = 0;
		if(rockthemes.frontend_options.is_admin_bar_showing){
			wp_nav_height = jQuery('#wpadminbar').outerHeight();
		}
		
		var scrollTopVal = (parseInt(next_el.offset().top) - parseInt(wp_nav_height));
		if(jQuery('.main-header-area').length && jQuery('.main-header-area').offset().top < scrollTopVal && scrollTopVal - rockthemes.menu.sticky_height > 0){
			scrollTopVal = scrollTopVal - parseInt(rockthemes.menu.sticky_height);
		}
		jQuery('html, body').animate({'scrollTop':scrollTopVal},1000, function(){
			var this_hash = typeof next_el.attr('id') != 'undefined' && next_el.attr('id') ? next_el.attr('id') : false;
			if(!this_hash) return;
			
			setTimeout(function(){
				if(history.pushState) {
					history.pushState(null, null, '#'+this_hash);
				}
				else {
					location.hash = this_hash;
				}
			},100);
		});
	});
		
}

/*
**	Transparent Overlay
*/
function rockthemes_overlay_transparent_enable(){
	if(jQuery('#azoom_overlay_transparent').length) return;
	
	jQuery('body').append(jQuery('<div id="azoom_overlay_transparent" class="azoom-fixed-overlay-transparent"></div>'));
}
function rockthemes_overlay_transparent_disable(){
	if(jQuery('#azoom_overlay_transparent').length){
		jQuery('#azoom_overlay_transparent').remove();
	}
}



function rockthemes_activate_lightbox(){
	jQuery(document).on('rockthemes:activate_lightbox', function(){
		jQuery('a[data-rel^="prettyPhoto"]').prettyPhoto({
			hook:'data-rel',
			overlay_gallery_max:9999
		});
	});
	
	jQuery(document).trigger('rockthemes:activate_lightbox');
}



function rockthemes_activate_cat_filter_ajax(){
	if(jQuery('.ajax-category-navigation').length < 1) return;
	
	//TO DO : Bind this to an option
	var animate_active = true;
	var anim_obj = {_out:'fadeOut',_in:'fadeIn'};
	
	jQuery('.ajax-category-navigation > ul > li').click(function(){
		var that = jQuery(this).parents('.ajax-category-navigation');
		var _this = jQuery(this); // We need to refer to this to get the category/taxonomy slug and post type
		
		if(that.hasClass('loading')) return;
		that.addClass('loading');
		that.find('li a.active').removeClass('active');
		jQuery(this).find(' > a').addClass('active');
		
		//Data element
		var de = jQuery(this).parents('.ajax-category-navigation').find(".data-cat-details");
		//Data send
		var ds = de.data();
		//Last Load
		de.attr('data-last_load', '0');
		//Update the last_load in data
		ds.last_load = 0;
		ds.category = _this.attr('data-slug-holder');
		
		//List and grid support
		var elem_selector = jQuery('#'+ds.id_ref).parents('.azoom-portfolio-container').hasClass('list') ? 'div' : 'li';

		jQuery.post(rockthemes.ajaxurl, {data:ds, _ajax_nonce:rockthemes.nonces.portfolio, action:'rockthemes_portfolio_load_more'}, function(data){
			
			
			if(!data || !data.body){ 
				that.removeClass('loading');
				return;
			}
			
			var new_elems = jQuery(data.body);
				
			var holder = jQuery('#'+ds.id_ref);
							
			holder.find(' > '+elem_selector).remove();
			
			if(animate_active){
				//Hide new elems
				new_elems.addClass('azoom-animate-queue');
			}
			
			holder.append(new_elems);
						
			if(typeof ds.masonry != 'undefined' && (ds.masonry || ds.masonry == 'true')){
				var mo;
				holder.masonry('appended', new_elems);
				rockthemes_init_single_masonry(holder,mo,true,true);
			}else if(animate_active){
				rockthemes_animate_queue(holder,'fadeIn');
			}
			
			//If load more button is active, then update the load more button after filtering
			if(that.parents('.azoom-portfolio-container').find('.load-more').length){
				var lmb = that.parents('.azoom-portfolio-container').find('.load-more');//lmb = Load More Button
				var lmd = lmb.parent().find('.data-details');//lmd = Load More Data
				
				lmd.attr('data-last_load', '0');
				lmd.attr('data-category',ds.category);
				
				if(!data.disable || data.disable !== 'yes'){
					if(lmb.hasClass('hide')) lmb.removeClass('hide');
				}else if(data.disable && data.disable === 'yes'){
					lmb.addClass('hide');	
				}
			}

			jQuery(document).trigger('rockthemes:activate_lightbox');
			that.removeClass('loading');
			return;
		});
		
	});
}




function rockthemes_activate_load_more(){
	if(jQuery('.load-more').length < 1) return;
	
	//TO DO : Bind this to an option
	var animate_active = true;
	var anim_obj = {_out:'fadeOut',_in:'fadeIn'};
	
	jQuery('.load-more').click(function(){
		var that = jQuery(this);
		
		if(that.hasClass('loading')) return;
		that.addClass('loading');
		
		//Data element
		var de = jQuery(this).parent().find('.data-details');
		//Data send
		var ds = de.data();
		
		
		//Last Load
		var ll = parseInt(parseInt(de.attr('data-last_load')) + parseInt(ds.load_amount));
		de.attr('data-last_load', ll);
		//Update the last_load in data
		ds.last_load = ll;
		ds.category = de.attr('data-category');

		
		
		if(ds.archive_load_more === 'true' || ds.archive_load_more === true){
			

			var alme = that.parent().find('.archive_load_more').find('.load-more-link').first();//alme = ajax load more elements
			var alme_link = alme.attr('href');//alme_link = ajax load more elements link
			alme.remove();
			
			var data = Object();
			if(!that.parent().find('.archive_load_more').find('.load-more-link').length){
				data.disable = 'yes';
			}
			var alme_id = '#'+that.attr('data-id_ref');

			

			jQuery('<div>').load(alme_link+' '+alme_id, function() {
				rockthemes_portfolio_ajax_callback_add(that,ds, data, jQuery(jQuery(this).find(alme_id)));
			});
			return;
		}
		
		//Old Load More system which uses exact details of the query. Still in use for RPB default usage.
		jQuery.post(rockthemes.ajaxurl, {data:ds, _ajax_nonce:rockthemes.nonces.portfolio, action:'rockthemes_portfolio_load_more'}, function(data){
			
			if(!data || !data.body){ 
				that.removeClass('loading');
				return;
			}
			

			var new_elems = jQuery(data.body);
			rockthemes_portfolio_ajax_callback_add(that,ds,data,new_elems);
		});
		
	});
}

function rockthemes_portfolio_ajax_callback_add(that,ds,data,new_elems){
		
	var holder = jQuery('#'+that.attr('data-id_ref'));
	var animate_active = true;
	
	if(typeof ds.masonry != 'undefined' && (ds.masonry || ds.masonry == 'true')){
		holder.append(new_elems);
		
		holder.imagesLoaded(function(){
			
			holder.css({'width':''});
			holder.parent().css({'width':''});
			
			var col_size = rockthemes_masonry_get_col_size(holder);
					
			new_elems.filter('.azoom-default-item:not(.widetall):not(.wide)').css('width', (col_size.px)+'px');
			new_elems.filter('.widetall, .wide').css('width', (col_size.px * 2)+'px');
			if(jQuery(window).width() <= parseInt(col_size.px * 2) - 30){
				new_elems.filter('.widetall, .wide').css('width', '');
				new_elems.filter('.azoom-default-item:not(.widetall):not(.wide)').css('width', '');
			}
			
			holder.masonry('appended', new_elems);
			
			rockthemes_animate_queue(holder,'fadeIn');
			
			jQuery(document).trigger('rockthemes:activate_lightbox');
			that.removeClass('loading');
			
			if(data.disable && data.disable === 'yes'){
				that.addClass('hide');	
			}
		});

		return;
	}else if(animate_active){
		
		if(new_elems.find('img').length){
			new_elems.imagesLoaded(function(){
				holder.append(new_elems);
				rockthemes_animate_queue(holder,'fadeIn');
				
				jQuery(document).trigger('rockthemes:activate_lightbox');
				that.removeClass('loading');
				
				if(data.disable && data.disable === 'yes'){
					that.addClass('hide');	
				}
				return;
			});
		}else{
			holder.append(new_elems);
			rockthemes_animate_queue(holder,'fadeIn');
			
			jQuery(document).trigger('rockthemes:activate_lightbox');
			that.removeClass('loading');
			
			if(data.disable && data.disable === 'yes'){
				that.addClass('hide');	
			}
			return;
		}
	}
}


function rockthemes_activate_portfolio(){
	
	//No Masonry No Swiper
	jQuery('.azoom-portfolio-container').each(function(){
		var that = jQuery(this);
		if(!that.hasClass('masonry-active') && !that.find('.swiper-container').length){
			that.imagesLoaded(function(){
				rockthemes_animate_queue(that);
			});
		}
	});
	
	
	//Single Swiper Element
	if(jQuery('.swiper-single-element').length){
		jQuery('.swiper-single-element').each(function(){
			if(!jQuery(this).parents('.masonry-active').length && !jQuery(this).parents('.azoom-portfolio').length){
				var jt = jQuery(this);
				var side_arrows = jQuery(this).find('.side-arrow-left').length ? true : false;
				rockthemes_activate_swiper(jQuery(this), side_arrows);
								
				jQuery(window).smartresize(function(){
					
					rockthemes_activate_swiper(jt, side_arrows);
				});
			}
		});
	};

	
	if(jQuery('.swiper-navigation-active').length){
		jQuery('.swiper-navigation-active').each(function(){
			var that = jQuery(this);
			
			if(!that.find('.rockthemes-masonry').length){
				rockthemes_activate_swiper(that);
				jQuery(window).smartresize(function(){
					rockthemes_activate_swiper(that);
				});
			}
		});
		
		jQuery(document).on('rockthemes:masonry_single_active', function(e,mas){
			var all_ready = true;
			jQuery('#'+mas).find('.azoom-default-item').each(function(){
				if(!jQuery(this).hasClass('masonry-brick')){
					all_ready = false;
				}
			});
			
			if(all_ready){
				
				rockthemes_activate_swiper(jQuery('#'+mas));
			}
		});
	}
	
	if(jQuery('.rockthemes-masonry').length < 1){
		return;
	}
	
	
	jQuery('.rockthemes-masonry').each(function(){
		var that = jQuery(this);
		
		var selector = that.attr('data-masonry-elem');

		if(typeof selector == 'undefined') return;
		
		//Masonry Object
		var mo;
		
		rockthemes_init_single_masonry(that,mo,true,true);


	});
	
	jQuery(window).smartresize(function(){
		jQuery('.rockthemes-masonry').each(function(){
			var that = jQuery(this);
			
			var selector = that.attr('data-masonry-elem');
	
			if(typeof selector == 'undefined') return;
			
			//Masonry Object
			var mo;
			rockthemes_init_single_masonry(that,mo);
				
		});
	});



}

function rockthemes_activate_swiper(that, side_arrows){
	
	var this_id = '';
	if(typeof that.attr('id') !== 'undefined'){
		this_id = '#'+that.attr('id')+' ';	
	}else if(typeof that.parent().attr('id') !== 'undefined'){
		this_id = '#'+that.parent().attr('id')+' ';
	}

	//!that.hasClass('wall-mode-active') &&  was included in if
	if(that.hasClass('azoom-portfolio-container') ){
		if(!that.hasClass('wall-mode-active')){
			//Add margins between slides
			that.css('width', (parseInt(that.width()) + 20)+'px');
		}else{
			//Make them static int values
			that.css('width', (parseInt(that.width()))+'px');
		}
	}
	
	if(that.hasClass('swiper-ready')){

		if(that.hasClass('swiper-single-element') || !that.find('.rockthemes-masonry').length){
			
			that.find('.swiper-container').find('.swiper-slide').css({'height':'', 'width':''});
			that.find('.swiper-wrapper').css({'height':'', 'width':'100%'});
		}
		that.find('.swiper-container').data('swiper').resizeFix(true);
		
		
		if(that.parents('.rockthemes-unique-grid').length){
			
			//If there is a fullscreen grid, resize it
			if(that.parents('.rockthemes-unique-grid[data-rsb-fullscreen="true"]').length){
				jQuery(document).trigger('rockthemes:resize_rsb_fullscreen_grid', [that.parents('.rockthemes-unique-grid[data-rsb-fullscreen="true"]')]);
			}else if(that.parents('.rockthemes-video-bg').length){
				rockthemes_fullscreen_bg_video(that.parents('.rockthemes-video-bg'));
			}else if(that.parents('.rockthemes-parallax').length){
				rockthemes_parallax_bg_image(that.parents('.rockthemes-parallax'));
			}else if(that.parents('.rockthemes-static-bg-image').length){
				rockthemes_static_bg_image(that.parents('.rockthemes-static-bg-image'));
			}
		}
		
		
		
		return;
	}
	

	
	var swiper;
	if(!that.hasClass('swiper-ready')){
		
		var swiper_details_obj = {
			mode:'horizontal',
			calculateHeight:true,
			roundLengths:true,
			grabCursor:false,
			autoResize:false,
			resizeReInit:false,
			moveStartThreshold:1,
			touchRatio: 1,
			longSwipesRatio:0.3,
			loop: false,
			pagination: (typeof side_arrows !== 'undefined' && side_arrows == true) ? '' : this_id+'.swiper-pagination',
			centeredSlides:true,
			keyboardControl:true,
			onFirstInit:function(){

				if(that.find('.azoom-animate-queue').length){
					rockthemes_animate_queue(that);
				}
				that.addClass('swiper-ready');
				
				if(that.parents('.rockthemes-unique-grid').length){
					setTimeout(function(){
						//If there is a fullscreen grid, resize it
						if(that.parents('.rockthemes-unique-grid[data-rsb-fullscreen="true"]').length){
							jQuery(document).trigger('rockthemes:resize_rsb_fullscreen_grid', [that.parents('.rockthemes-unique-grid[data-rsb-fullscreen="true"]')]);
						}else if(that.parents('.rockthemes-video-bg').length){
							rockthemes_fullscreen_bg_video(that.parents('.rockthemes-video-bg'));
						}else if(that.parents('.rockthemes-parallax').length){
							
							rockthemes_parallax_bg_image(that.parents('.rockthemes-parallax'));
						}else if(that.parents('.rockthemes-static-bg-image').length){
							rockthemes_static_bg_image(that.parents('.rockthemes-static-bg-image'));
						}
					},50);
				}
					
			}
		}
		
		//Do not enable autoplay for mobile devices
		if(!Modernizr.ismobile){
			if(typeof that.attr('data-auto-play') !== 'undefined' && that.attr('data-auto-play') === 'true'){
				var auto_play_time = typeof that.attr('data-auto-play-time') !== 'undefined' && that.attr('data-auto-play-time') ? that.attr('data-auto-play-time') : 5000;
				swiper_details_obj.autoplay = parseInt(auto_play_time);
				swiper_details_obj.speed = 1000;
				//To activate Swiper after user intraction, uncomment this code
				//swiper_details_obj.autoplayDisableOnInteraction = false;
			}
		}
		
		if(typeof that.attr('data-use-pagination') !== 'undefined' && that.attr('data-use-pagination') === 'true'){
			swiper_details_obj.pagination = this_id+' .swiper-pagination';
		}

		swiper = that.find('.swiper-container').swiper(swiper_details_obj);
		

		if(typeof side_arrows !== 'undefined' && side_arrows == true){
			if(typeof that.attr('data-use-pagination') !== 'undefined' && that.attr('data-use-pagination') === 'true'){
			
			}else{
				that.find('.swiper-pagination').remove();
			}
		}

		
		that.on('click touchend', '.swiper-pagination .swiper-pagination-switch' , function(){
			swiper.swipeTo(jQuery(this).index());
		});
		
		if(that.hasClass('azoom-portfolio-container') || that.parents('.azoom-portfolio-container').length){
			var main = that.hasClass('azoom-portfolio-container') ? that : that.parents('.azoom-portfolio-container');
			main.on('click touchend', '.swiper-arrow-left', function(){
				swiper.swipePrev();
			});
			main.on('click touchend', '.swiper-arrow-right', function(){
				swiper.swipeNext();
			});
		}
		
		if(typeof side_arrows !== 'undefined' && side_arrows == true){
			that.find('.side-arrow-left').click(function(e){
				e.preventDefault()
				swiper.swipePrev();
			});
			that.find('.side-arrow-right').click(function(e){
				e.preventDefault()
				swiper.swipeNext();
			});
		}
		
	
		jQuery(document).trigger('rockthemes:activate_lightbox');
		

	}
	
}


function rockthemes_init_single_masonry(that, mo, load_images, animate){
	animate = (typeof animate == 'undefined' || !animate) ? false : true;
	
	
	that.css({'height':''});
	
	
	that.parent().css({'width':'auto', 'position':'relative', 'display':'block'});

	if(that.parents('.swiper-slide').length){
		that.find('.azoom-animate-queue').removeClass('azoom-animate-queue');
		that.parent().css('width','100%');
	}
	
	var upto_class = ['.azoom-portfolio-container', '.swiper-slide', '.swiper-wrapper', '.azoom-portfolio-container'];
	for (var u = 0; u < upto_class.length; u++){
		if(that.parents(upto_class[u]).length){
			that.parents(upto_class[u]).css({'width':'100%'});
		}
	}

	
	
	var col_size = rockthemes_masonry_get_col_size(that);
	
	that.parent().css('width',col_size.cd);

	that.find('.azoom-default-item:not(.widetall):not(.wide)').css('width', (col_size.px)+'px');
	that.find('.widetall, .wide').css('width', (col_size.px * 2)+'px');
	if(jQuery(window).width() <= parseInt(col_size.px * 2) - 30){
		that.find('.widetall, .wide').css('width', '');
		that.find('.azoom-default-item:not(.widetall):not(.wide)').css('width', '');
	}

	var selector = that.attr('data-masonry-elem');
	
	
	if(that.find('.masonry-brick').length){
		
		//return;	
	}

	//TO DO : Bind anim to data attribute to "that" element
	var anim = false;
	//Masonry Object
	if(typeof load_images != 'undefined' && load_images){
		that.imagesLoaded(function(){

			mo = that.masonry({
				itemSelector:'.'+selector,
				transitionDuration: false,
				columnWidth:(col_size.px)
			});
									
			jQuery(document).trigger('rockthemes:masonry_single_active', [that.parents('.azoom-portfolio-container').attr('id')]);
			
			if(animate){
				rockthemes_animate_queue(that,anim);
			}
			
		});
	}else{
		mo = that.masonry({
			itemSelector:'.'+selector,
			transitionDuration: false,
			columnWidth:(col_size.px)
		});
		
			
		jQuery(document).trigger('rockthemes:masonry_single_active', [that.parents('.azoom-portfolio-container').attr('id')]);
		
		if(animate){
			rockthemes_animate_queue(that,anim);
		}
		
		
	}
	
}

function rockthemes_animate_queue(that, animation){
	if(typeof that == 'undefined') return;
	if(typeof animation == 'undefined' || !animation || animation == '') animation = 'fadeIn';

	that.find('.azoom-default-item.azoom-animate-queue').each(function(i){

		var ref_elem = jQuery(this);
		if(jQuery(this).parents('.swiper-slide').length){
			
			jQuery(this).addClass('animated '+animation).removeClass('azoom-animate-queue');
		}else{
			setTimeout(function(){
				ref_elem.addClass('animated '+animation).removeClass('azoom-animate-queue');
			}, parseInt(180 * i));
		}
	});
}


function rockthemes_masonry_get_col_size(that){
	if(typeof that == 'undefined') return 540;
	
	
	
	var tc = 4;//Total Columns
	var ec = that.attr('class').split(' ');
	
	
	//Clear class names if there are multiple spaces
	for(var i=0; i<ec.length; i++){
		if(ec[i] == ''){
			ec.splice(i,1);	
		}
	}
	
	tc = rockthemes_check_class_names(ec,that);
	
	
	
	return {px:((that.outerWidth())  / tc), fluid:( 100 / tc), cols:tc, cd:(that.outerWidth())};
}

function rockthemes_check_class_names(ar,that){
	//Return 4 if nothing is there
	if(typeof ar == 'undefined') return 4;
	
	var w = jQuery(window);
	var class_name = '';
	
	if(w.width() > parseInt(rockthemes.grid.block.medium)){
		class_name = 'large-block-grid-';
	}else if(w.width() >= parseInt(rockthemes.grid.block.small)){
		class_name = 'medium-block-grid-';
	}else{
		class_name = 'small-block-grid-';
	}

	var col = 4;
	for(var i = 0; i<ar.length; i++){
		if(ar[i].indexOf(class_name) > -1){
			col = parseInt(ar[i].trim().replace(class_name,''));
		}
	}
	
	return col;
}



function rockthemes_activate_hover(){
	//Add every element like portfolio li to this array
	var hover_elems = ['.azoom-default-item .rockthemes-hover', '.single-box-element', '.entry-thumbnail', '.rockthemes-wp-gallery li'];
	var hover_classes = 'hover-active-medium hover-active hover-active-small';
	
	for(var i = 0; i<hover_elems.length; i++){
		var that = hover_elems[i];
		//Check if it's small for hover effect
		jQuery(document).on('mouseenter', that, function(){
			rockthemes_hover_vertical_center(jQuery(this));
			jQuery(this).addClass(rockthemes_get_hover_active_class(jQuery(this)));
		}).
		on('touchstart', that, function(e){
			jQuery(this).attr('data-touch-start-y', e.originalEvent.touches[0].pageY);
		}).
		on('touchend', that, function(e){
			//e.preventDefault();
			var tend = e.originalEvent.changedTouches[0].pageY;
			if(typeof jQuery(this).attr('data-touch-start-y') !== 'undefined' && 
				Math.abs(parseInt(jQuery(this).attr('data-touch-start-y')) - tend) < 5){
				jQuery(this).addClass(rockthemes_get_hover_active_class(jQuery(this)));
				jQuery(this).attr('data-touch-start-y', tend);
			}
		}).
		on('mouseleave', that, function(){
			jQuery(this).removeClass(rockthemes_get_hover_active_class(jQuery(this)));
		}).
		on('click', '.hover-mobile-back', function(){

		}).
		on('touchend', that+' .hover-mobile-back', {that:that}, function(e){
			e.originalEvent.preventDefault();
			e.stopPropagation();
			
			var el = jQuery(this).parents(e.data.that);
			el.removeClass(rockthemes_get_hover_active_class(el));
			el.attr('data-touch-start-y', e.originalEvent.changedTouches[0].pageY);
		}).
		on('touchend', '.small-hover-elem', {that:that}, function(e){
			e.preventDefault();
			e.stopPropagation();
			if(typeof jQuery(this).attr('data-link_url') != 'undefined'){
				window.location = jQuery(this).attr('data-link_url');
			}
			var el = jQuery(this).parents(that);
			el.removeClass(rockthemes_get_hover_active_class(el));
			el.attr('data-touch-start-y', e.originalEvent.changedTouches[0].pageY);
		});
	}
}

//function rockthemes_hover_vertical_center(hover_elems){
function rockthemes_hover_vertical_center(res){
	res.css('padding-top','');
	var	hi = res.find('.hover-item-details-container'),
		th = 0;
	
	hi.children().each(function(){
		th = th + parseInt(jQuery(this).outerHeight(true));
	});
	
	
	th = parseInt((hi.outerHeight() - th) / 2) - 19;
	if(th < 19) th = 19;
	
	hi.css('padding-top',th+'px');
}

function rockthemes_get_hover_active_class(that){
	
	//For list mode and different elements, only use the hover element's size
	if(that.find('.rockthemes-hover').length){
		that = that.find('.rockthemes-hover');	
	}
	
	var large_width = parseInt(rockthemes.hover_details.hover_width_min_large);
	var large_height = parseInt(rockthemes.hover_details.hover_height_min_large);

	var medium_width = parseInt(rockthemes.hover_details.hover_width_min_medium);
	var medium_height = parseInt(rockthemes.hover_details.hover_height_min_medium);
	
	if(that.find('.woo-grid-hover').length){
		large_height += 75;
	}
	
	if(that.parents('.dunder_image').length){
		medium_height = 140;	
	}
	
	var class_name;
	
	if((that.width() * that.height()) >= (large_width * large_height) && that.height() > large_height){
		//Large display
		class_name = 'hover-active';
	}else if((that.width() * that.height()) >= (medium_width * medium_height) && that.width() > medium_width && that.height() > medium_height){
		//Medium display
		class_name = 'hover-active-medium hover-active';
	}else{
		//Small display
		class_name = 'hover-active-small';
	}
	
	return class_name;
}




/*
**	Makes the flash responsive. This will also require a CSS rule to make flash width 100%;
**
**	@since	:	1.3
**	@return	:	void
*/
function rockthemes_responsive_flash(){
	//Return if no flash exists. 
	if(jQuery('object[type="application/x-shockwave-flash"]').length < 1) return;
	
	var flash = [],
		rebuild = setTimeout(function(){return;},10);

	jQuery('object[type="application/x-shockwave-flash"]').each(function (){
		flash.push({
			element: jQuery(this),
			height: jQuery(this).attr('height'),
			width: jQuery(this).attr('width')
		});
	});
	
	jQuery(window).smartresize(function() {
		clearTimeout(rebuild);
		rebuild = setTimeout(resize_flash, 100);
	});
	
	function resize_flash(){
		if(flash.length < 1) return;
		
		jQuery.each(flash, function (i, value) {
			flash[i].element.height(flash[i].element.width() / flash[i].width * flash[i].height);
		});
	}
	
	resize_flash();
}



function rockthemes_check_bg_videos(){
	if(Modernizr.touch){
		jQuery('.rockthemes-video-bg').each(function(){			
			var that = jQuery(this);
			var img = that.attr('data-video_bg_fallback_url');
			
			if(typeof img === 'undefined' || img === ''){
				if(that.find('.azoom-iframe-container').length){
					that.find('.azoom-iframe-container').remove();
				}
				
				if(that.find('video').length){
					that.find('video').remove();	
				}
				
				return;
			}
			
			//It's HTML5 Video background
			if(!that.find('iframe').length){
				
				//If this is a fullscreen element, fullscreen will handle the details. So return.
				if(typeof that.attr('data-rsb-fullscreen') != 'undefined' && that.attr('data-rsb-fullscreen') == 'true'){
					return;
				}
				
				var ve = that.find('video'),
					th = that.outerHeight(),
					tw = parseInt(ve.width() * th / ve.height()),
					ml = -parseInt((tw - jQuery(window).width()) / 2);

				that.find('video').css({
					'min-height':th+'px',
					'width':tw+'px',
					'margin-left':ml+'px'
				});
			}else{
				that.find('iframe').before('<img src="'+img+'" alt="Video" />');
				that.find('.azoom-iframe-container').css({
					'padding':'0px',
					'height':'auto',
					'top':''			
				});
				that.find('iframe').remove();
			}
		});
		
		return;
	}

	//Vimeo Player API
	jQuery('.azoom-iframe-container.vimeo-video.use-api > iframe').each(function(){
		var iframe = jQuery(this)[0];
		Froogaloop(iframe).addEvent('ready', rockthemes_vimeo_ready);
	});
	function rockthemes_vimeo_ready(player_id){
		if(jQuery('#'+player_id).length && jQuery('#'+player_id).parents('.not-visible').length){
			jQuery('#'+player_id).parents('.not-visible').removeClass('not-visible');	
		}
		var player = Froogaloop(player_id);
		player.api('setVolume',0);
	}


	//Youtube Player API
	if(jQuery('.azoom-iframe-container.youtube-video.use-api .youtube-video-iframe-holder').length){
		//Only works normally when loaded via js
		var tag = document.createElement('script');
		var protocol = window.location.protocol == 'https:' ? 'https' : 'http';
		tag.src = protocol+'://www.youtube.com/iframe_api';
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	}
}


//This function name is static because of Youtube
function onYouTubeIframeAPIReady() {
	jQuery('.azoom-iframe-container.youtube-video.use-api .youtube-video-iframe-holder').each(function(){
		var hid = jQuery(this).attr('id');// HTML ID
		var yid = jQuery(this).parents('.azoom-iframe-container').attr('data-youtube-id');// Youtube ID
		var player = new YT.Player(hid,
		{	
			playerVars: { 'autoplay': 1, 'loop': 1, 'playlist': yid, 'modestbranding': 1, 'controls': 0, 'showinfo' : 0, 'rel': 0, 'wmode':'transparent' },
			videoId:yid,
			events: {
            	'onReady': rockthemes_youtube_onPlayerReady,
			}	
		});
	});
}
	
function rockthemes_youtube_onPlayerReady(event){
	//event.target.mute();
	event.target.mute();
	
	//If there are fullscreen videos that not sized, this queue will size them
	rockthemes_check_queue_videos();
}
	

function rockthemes_get_font_name(font_family){
	font_family = font_family.toString();
	var font_name = '';
	if(font_family.indexOf("'") > -1){
		//Check if using single quote '
		font_name = font_family.match(/\'.*?\'/i);
		if(!font_name[0]){
			return '';	
		}
		font_name = font_name[0].split("'").join("");
	}else if(font_family.indexOf('"') > -1){
		//Check if using double quote "
		font_name = font_family.match(/\".*?\"/i);
		if(!font_name[0]){
			return '';	
		}
		font_name = font_name[0].split('"').join('');
	}else{
		return '';	
	}
		
	return font_name;
}






function rockthemes_fullscreen_elements(){
	
	
	//Check if there are fullscreen elements in the above header area. If not remove the animation.
	if(!jQuery('.rockthemes-before-header.intro-effect-slide div[data-rsb-fullscreen="true"], .rockthemes-before-header.intro-effect-slide section[data-rsb-fullscreen="true"]').length){
		jQuery('.rockthemes-before-header.intro-effect-slide').removeClass('intro-effect-slide');
	}
	
	var clear_classes = ['.rockthemes-before-header.intro-effect-slide', '.rockthemes-unique-grid', '.static-bg-mask-class','.bg-image-overlay'];
	for(var s = 0; s<clear_classes.length; s++){
		var del_style = jQuery(clear_classes[s]);
		if(del_style.length){
			
			del_style.css({'min-height':'', 'height':''});
		}		
		del_style = null;
	}

	
	//Special Grid Full Screen Elements
	jQuery('div[data-rsb-fullscreen="true"], section[data-rsb-fullscreen="true"]').each(function(){
		//Reference
		var that=jQuery(this);
		rockthemes_rsb_fullscreen(that);
	});
	jQuery(document).on('rockthemes:resize_rsb_fullscreen_grid', function(e, elem){
		rockthemes_rsb_fullscreen(elem);
		if(elem.hasClass('.rockthemes-video-bg')){
			rockthemes_fullscreen_bg_video(elem);
		}
		if(elem.hasClass('.rockthemes-parallax')){
			rockthemes_parallax_bg_image(elem);
		}
		if(elem.hasClass('.rockthemes-static-bg-image')){
			rockthemes_static_bg_image(elem);
		}
	});
	
	
	//Special Grid Full Screen Video
	jQuery('.rockthemes-video-bg[data-rsb-fullscreen="true"]').each(function(){
		//Reference
		var that=jQuery(this);
		var w=jQuery(window);
			
		//Youtube load async. If this is a youtube video, it might not be changed to iframe from div
		if(jQuery(this).find('iframe').length < 1 && jQuery(this).find('video').length < 1){
			rockthemes.init_queue.fullscreen_bg_videos.push(that);
			return;
		}
			
		rockthemes_fullscreen_bg_video(that);
	});
	
	
	//Parallax Background Images
	jQuery('.rockthemes-unique-grid.rockthemes-parallax').each(function(){
		rockthemes_parallax_bg_image(jQuery(this));
	});
	
	
	//Background Images for Unique Grid
	jQuery('.rockthemes-unique-grid.rockthemes-static-bg-image').each(function(){
		rockthemes_static_bg_image(jQuery(this));
	});
	
	
}
//Full screen special grid. This will only resize the grid not the content.
function rockthemes_rsb_fullscreen(that){
	var w=jQuery(window),
		m=jQuery('#main-container');
	//Clear Style
	that.css({'min-height':'','height':''});
	
	
	
	var that_height = that.outerHeight();
	
	var e_height = (w.height() > that_height) ? w.height() : 'auto';//that_height was auto
	
	
	//Resize the div. This will avoid weird look on the load
	if(jQuery(this).find('.rockthemes-curvy-slider').length && m.width() < 800 && w.height() < 800){
		that.css({'width':'100%', 'height':'auto'});
	}else{
		that.css({'width':m.width(), 'min-height':e_height});
	}
}



function rockthemes_static_bg_image(that){
	var tile = false;
	//Init the required details
	if(that.find('.static-bg-mask-class').length < 1){
		if(that.hasClass("rockthemes-fullwidth-colored")) that.removeClass("rockthemes-fullwidth-colored").attr("style","");
		
		if(that.attr('data-image-tile') && that.attr('data-image-tile') === 'tile'){
			tile = true;	
		}
		
		var vertical_space = that.attr('data-padding') !== '' ? that.attr('data-padding') : '';
		var overlay_style = that.attr('data-overlay-color') !== '' ? that.attr('data-overlay-color') : ''; 
		var data_image = window.devicePixelRatio >= 2 && typeof that.attr('data-static-bg-image-retina') !== 'undefined' ? that.attr('data-static-bg-image-retina') : that.attr('data-static-bg-image');
		
		var mask = '<div class="static-bg-mask-class" style="background:url('+data_image+') '+(!tile ? 'no-repeat; background-size:100% auto;' : '')+'">'+
						'<div class="bg-image-overlay  '+vertical_space+'" style="'+overlay_style+'"></div>'+
					'</div>';
		
		that.wrapInner(mask);
		
		//Clear the bg style. This style just added for Google Crawl Text Mode		
		that.css({
			'background-image':'',
			'background-color':'',
			'background-attachment': '',
			'background-size': '',
			'background-origin': '',
			'background-clip': '',
			'background-position': '',
			'background-repeat': ''
		});
		
	}
	
	if(tile){
		return;
	}
			
	//Background sizing
	var img = {w:1920, h:1080};
	if(typeof that.attr('data-image-ratio') != 'undefined' && that.attr('data-image-ratio')){
		var img_r = that.attr('data-image-ratio').split('_');
		img.w = img_r[0];
		img.h = img_r[1];	
	}
	
	var mask_elem = that.find('.static-bg-mask-class');
	mask_elem.css('height','');//Clear height value to get the exact content height
	mask_elem.find('.bg-image-overlay').css({'min-height':''});

	var con = {w:that.width(), h:that.height()};
	var factor = Math.max(con.w / img.w, con.h / img.h);
	
	var mask_height = typeof that.attr("data-static-mask-height") !== 'undefined' && that.attr("data-static-mask-height") !== '' ? parseInt(that.attr("data-static-mask-height")) : 0;
	//mask_elem.css('height','');//Clear height value to get the exact content height
	mask_height = Math.max(mask_height, that.height());
	

	mask_elem.find('.bg-image-overlay').css({'min-height':mask_height+'px'});
	mask_elem.css({'background-size':'cover', 'background-position':'0px 50%'});
}




function rockthemes_parallax_bg_image(that){
	
	//Parallax Model
	var parallax_model = typeof that.attr('data-parallax-model') !== 'undefined' && that.attr('data-parallax-model') !== '' ? that.attr('data-parallax-model') : 'height_specific';
	if(Modernizr.ismobile || jQuery(window).width() > 1920){
		parallax_model = 'height_specific';	
	}
	
	//Init the required details
	if(that.find('.parallax-mask-class').length < 1){
		if(that.hasClass("rockthemes-fullwidth-colored")) that.removeClass("rockthemes-fullwidth-colored").attr("style","");
		
		
		var vertical_space = typeof that.attr('data-parallax-padding') !== 'undefined' && that.attr('data-parallax-padding') !== '' ? that.attr('data-parallax-padding') : '';
		var overlay_style = typeof that.attr('data-overlay-color') !== 'undefined' && that.attr('data-overlay-color') !== '' ? that.attr('data-overlay-color') : ''; 
		var data_image = window.devicePixelRatio >= 2 && typeof that.attr('data-parallax-bg-image-retina') !== 'undefined' ? that.attr('data-parallax-bg-image-retina') : that.attr('data-parallax-bg-image');

		var mask = '<div class="parallax-mask-class '+parallax_model+'" style="background-image:url('+data_image+') ; ">'+
						'<div class="bg-image-overlay '+vertical_space+'" style="'+overlay_style+'">'+
						'</div>'+
					'</div>';
	
		that.wrapInner(mask);
		
		//Clear the bg style. This style just added for Google Crawl Text Mode		
		that.css({
			'background-image':'',
			'background-color':'',
			'background-attachment': '',
			'background-size': '',
			'background-origin': '',
			'background-clip': '',
			'background-position': '',
			'background-repeat': ''
		});
		
		rockthemes_add_image_size_data(that.find('.parallax-mask-class'));
		
		if(!Modernizr.ismobile && parallax_model === 'advanced_parallax' && jQuery(window).width() <= 1440) {
			that.find(".parallax-mask-class").parallax("50%", 0.4);
		}

	}
	

	//Background sizing
	var img = {w:1920, h:1080};
	if(typeof that.attr('data-image-main-width') !== 'undefined' && 
		typeof that.attr('data-image-min-height') !== 'undefined'){
		
		img.w = parseInt(that.attr('data-image-main-width'));
		img.h = parseInt(that.attr('data-image-main-height'));
	}else if(typeof that.attr('data-image-ratio') != 'undefined' && that.attr('data-image-ratio')){
		var img_r = that.attr('data-image-ratio').split('_');
		img.w = img_r[0];
		img.h = img_r[1];	
	}
	var con = {w:that.width(), h:jQuery(window).height()};
	var mask_elem = that.find('.parallax-mask-class');
	var factor = Math.max(con.w / img.w, con.h / img.h);
	
	
	var mask_height = typeof that.attr("data-parallax-mask-height") !== 'undefined' && that.attr("data-parallax-mask-height") !== '' && that.attr("data-parallax-mask-height") ? parseInt(that.attr("data-parallax-mask-height")) : 0;
	
	mask_elem.css('height','');//Clear height value to get the exact content height
	mask_elem.find('.bg-image-overlay').css({'min-height':''});
	
	/*
	**	TO DO :
	**	This method will be tested on mobile
	*/
	if(Modernizr.ismobile){
		if(con.h < that.height()){
			factor = (Math.max(con.w / img.w, (that.height()) / img.h));
		}
	}

	
	mask_height = Math.max(mask_height, that.height());


	mask_elem.find('.bg-image-overlay').css({'min-height':mask_height+'px'});
	
	
	if(parallax_model !== 'advanced_parallax') {
		//mask_elem.css({'background-size':parseInt(img.w * factor)+'px '+parseInt(img.h * factor)+'px'});
	}
}

/*
**	Data attribute for default image sizes.
**	http://stackoverflow.com/questions/5106243/how-do-i-get-background-image-size-in-jquery
*/
function rockthemes_add_image_size_data(that){
	var image_url = that.css('background-image'),
		image;
	
	// Remove url() or in case of Chrome url("")
	image_url = image_url.match(/^url\("?(.+?)"?\)$/);
	
	if (typeof image_url != 'undefined' && jQuery.isArray(image_url) && image_url[1]) {
		image_url = image_url[1];
		image = new Image();
	
		// just in case it is not already loaded
		jQuery(image).load(function () {
			that.attr('data-image-main-width',image.width);
			that.attr('data-image-main-height',image.height);
			image = null;
		});
	
		image.src = image_url;
	}	
}




function rockthemes_check_queue_videos(){
	for(var i = 0; i< rockthemes.init_queue.fullscreen_bg_videos.length; i++){
		rockthemes_fullscreen_bg_video(rockthemes.init_queue.fullscreen_bg_videos[i]);	
	}
}

function rockthemes_fullscreen_bg_video(that){
	var w = {
		width:function(){
			return jQuery(window).width();
		}, 
		height:function(){
			return (jQuery(window).height() > that.outerHeight() ? jQuery(window).height() : that.outerHeight()) + 15;		
		}
	}
	//v is Video
	var v;
	var iframe_container = null;
	var user_ratio = {w:16, h:9};
	
	//Check the RPB user video ratio
	if(typeof that.attr('data-rsb-ratio') != 'undefined' && that.attr('data-rsb-ratio')){
		var tem = that.attr('data-rsb-ratio').split('_');
		user_ratio = {w:parseInt(tem[0]), h:parseInt(tem[1])};
	}
	
	//If HTML5 video, video ratio might be different. Check if it's set up before (resize etc)
	if(typeof that.attr('data-html5-video-ratio') != 'undefined' && that.attr('data-html5-video-ratio')){
		var sem = that.attr('data-html5-video-ratio').split('_');
		user_ratio = {w:parseInt(sem[0]), h:parseInt(sem[1])};
	}
	
	
	
	if(that.find('video').length){
		v = that.find('video');
		if(typeof that.attr('data-html5-video-ratio') != 'undefined' && that.attr('data-html5-video-ratio')){
			//Do nothing
		}else{
			v.on('loadedmetadata', function() {
				//It's HTML5 video and ratio might be different than user entry. Get the real ratio and add it as an attribute
				user_ratio.w = parseInt(v.width());
				user_ratio.h = parseInt(v.height() - 4);//+4 for border etc
				that.attr('data-html5-video-ratio', user_ratio.w+'_'+user_ratio.h);
			});
		}
	}else{
		v = that.find('iframe');
		iframe_container = that.find('.azoom-iframe-container');
	}
	
	
	//Video Originial Ratio
	var r = user_ratio.w/user_ratio.h;
	//Window Ratio
	var wr = w.width() / w.height();

	//Resize it
	if(wr > r){
		if(iframe_container){			
			iframe_container.css({'width':w.width()+'px','height':parseInt(user_ratio.w * w.height() / user_ratio.h )+'px'});
		}else{
			v.css({'width':w.width()+'px','height':parseInt( user_ratio.w / user_ratio.h * w.height())+'px'});
		}
	}else{
		if(iframe_container){
			iframe_container.css({'width':parseInt(w.height() * user_ratio.w / user_ratio.h)+'px', 'height':w.height()+'px', 'top':'0px'});
		}else{
			v.css({'width':parseInt(w.height() * user_ratio.w / user_ratio.h)+'px', 'height':w.height()+'px', 'top':'0px'});
		}
	}
	
	//Set to the default area
	if(iframe_container){
		iframe_container.css({'left':'0px','top':'0px'});
	}else{
		v.css({'left':'0px','top':'0px'});
	}

	//Move it
	var l_moved = false;
	if(v.width() > w.width()){
		l_moved = true;
		if(iframe_container){
			iframe_container.css('left', '-'+(parseInt((v.width() - w.width())/2) - 2)+'px');
		}else{
			v.css('left', '-'+parseInt((v.width() - w.width())/2)+'px');
		}
	}
	if(!l_moved && iframe_container){
		iframe_container.css('left', '-2px');
	}
	
	if(v.height() > w.height()){
		if(iframe_container){
			iframe_container.css('top', '-'+(parseInt((v.height() - (w.height()))/2) )+'px');
		}else{
			v.css('top', '-'+parseInt((v.height() - w.height())/2)+'px');
		}
	}
	
	that.css({'width':w.width(), 'height':w.height()});
	
	if(typeof v.attr('src') != 'undefined' && v.attr('src') && v.attr('src').toString().indexOf('vimeo') > -1){
		if(typeof rockthemes.visibility_hidden_elements == 'undefined' || !rockthemes.visibility_hidden_elements){
			rockthemes.visibility_hidden_elements = new Array();
			rockthemes.visibility_hidden_elements.push(iframe_container);
		}
	}
	
	that.css({'visibility':'visible'});
}




function rockthemes_menu_ajax_woocommerce_cart(){
	//Check if WooCommerce details exist
	if(jQuery('.azoom-undermenu-mask').length < 1) return;
	
	var underbox_menu_mask = jQuery('.azoom-undermenu-mask');//Performance increase
	var underbox_menu = jQuery('.azoom-undermenu-box');
	var is_box_active = false;
	//Display cart after a new item added to the cart
	var display_cart_ar = (typeof rockthemes.woocommerce.auto_display_cart_after_refresh != "undefined" && rockthemes.woocommerce.auto_display_cart_after_refresh == true ? true : false);
	//var results_holder = jQuery('.ajax-search-results');
	var to_all_button_cover = jQuery('.undermenu-box-button-cover');
	var to_all_button = jQuery('.undermenu-box-button');
	var to_all_button_height = 63 + 10;//+10 is for radius
	var cart_link_html = '<div class="link-icon azoom-transition-fast">'+
						 	'<i class="icomoon icomoon-icon-next15"></i>'+
						 '</div>';
	if(rockthemes.is_rtl == 'rtl'){
		cart_link_html = '<div class="link-icon azoom-transition-fast">'+
						 	'<i class="icomoon icomoon-icon-previous11"></i>'+
						 '</div>';
	}
	var cart_datas = [];
	var woo_initialized = false;//Add some classes to the widget
	var remove_classes = '';
	for (var r = 0; r < rockthemes.settings.undermenu_box_classes.length; r++){
		if(rockthemes.settings.undermenu_box_classes[r] != 'woocommerce-cart-active'){
			remove_classes += rockthemes.settings.undermenu_box_classes[r]+' ';	
		}
	}
	
	
	//Add to cart clicked, find the product
	jQuery(document).on('click', '.add_to_cart_button', function(){
		var product = null;
		if(jQuery(this).parents('.product:eq(0)').length){
			product = jQuery(this).parents('.product:eq(0)');
		}else if(jQuery(this).parents('.azoom-default-item').length){
			product = jQuery(this).parents('.azoom-default-item');
		}
		
		if(product && product.find('.added_icon').length < 1){
			product.find('.grid-price').append(' <i class="icomoon icomoon-icon-checkmark animated bounceIn added_icon"></i>');
		}
	});
	
	//Get first cart datas
	var data_timer = setTimeout(data_timer_function,50);
	var data_timer_max_count = 10,
		data_timer_current_count = 0;
	function data_timer_function(){
		if(jQuery('.azoom-woocommerce-box .cart_list li').length > 0 && typeof jQuery('.azoom-woocommerce-box .cart_list li').html() != "undefined"){
			clearTimeout(data_timer);
			
			if(!woo_initialized){
				//Initialize WooCommerce Cart for styling
				init_woo_cart();
			}
			
			get_cart_datas();
		}else{
			clearTimeout(data_timer);
			data_timer_current_count++;
			if(data_timer_current_count < data_timer_max_count){
				setTimeout(data_timer_function,50);	
			}
		}
	}
	
	function get_cart_datas(){
		if(jQuery('.azoom-woocommerce-box .cart_list li .ajax-cart-content a .text-overflow').length < 1){
			cart_datas = [];
			return;
		}
		
		var cart_item_html = jQuery('.azoom-woocommerce-box .cart_list li');
		cart_datas = [];
		cart_item_html.each(function(){
			var el = jQuery(this).find('.ajax-cart-content').length > 0 ? jQuery(this).find('.ajax-cart-content') : jQuery(this) ;
			cart_datas.push({name:el.find('a .text-overflow').html(), quantity:el.find('.quantity').html(), link_url:el.find('a').not('.remove').attr('href')});
		});
	}
	
	function return_new_item_in_cart(){
		if(cart_datas.length <= 0) 	get_cart_datas();
		
		var el;
		var found = false;
		var cart_item_html = jQuery('.azoom-woocommerce-box-content .cart_list li');
		if(cart_datas.length > 0 && cart_item_html.find('.ajax-cart-content').length){
			cart_item_html.each(function(){
				if(found) return;
				var that = jQuery(this).find('.ajax-cart-content');
				for(var s = 0; s < cart_datas.length; s++){								
					if(that.find('a .text-overflow').html().trim() == cart_datas[s].name.trim() && that.find('a').not('.remove').attr('href').trim() == cart_datas[s].link_url.trim() 
						&& that.find('.quantity').html().trim() != cart_datas[s].quantity.trim() ){
						el = that;
						
						found = true;
						return;
					}
				}
			});
		}
		
		if(!found){
			el = jQuery('.azoom-woocommerce-cart-wrapper.azoom-woocommerce-box .azoom-woocommerce-box-content ul.cart_list li').last().find('.ajax-cart-content');
		}
		
		//Update datas
		get_cart_datas();
		return el;
	}
	
	
	function update_woo_cart_contents(){
		if(!woo_initialized){
			//Initialize WooCommerce Cart for styling
			init_woo_cart();
		}else{
			jQuery('.azoom-woocommerce-cart-wrapper.azoom-woocommerce-box .azoom-woocommerce-box-content ul.cart_list').addClass('large-block-grid-3 medium-block-grid-2 small-block-grid-1');
			jQuery('.azoom-woocommerce-cart-wrapper.azoom-woocommerce-box .azoom-woocommerce-box-content ul.cart_list > li').wrapInner('<div class="ajax-cart-content azoom-transition"></div>');
			jQuery('.azoom-woocommerce-cart-wrapper .azoom-woocommerce-box-content ul.cart_list li .ajax-cart-content').each(function(){
				var img = jQuery(this).find('a img');
				var text = jQuery(this).find('a').not('.remove').text();
				var span_el = jQuery(this).find('.quantity');
				jQuery(this).find('a').not('.remove').html(img).append('<span class="text-overflow">'+text+'</span>');
				jQuery(this).find('a').not('.remove').append(span_el).append(cart_link_html);
			});
		}
		var last_item = return_new_item_in_cart();
		
		update_special_cart_icon_count();
				
		//TO DO : Bind to an option to activate/deactivate cart contents on "added to cart"
		if(display_cart_ar){
			var delay_time = 1000;
			if(!is_box_active){
				jQuery('.special-cart-container').trigger('click');
				delay_time += 600;
			}
			
			last_item.addClass('ajax-cart-animate-border azoom-transition');
			setTimeout(function(){
				setTimeout(function(){
					if(is_box_active){
						jQuery('.special-cart-container').trigger('click');
					}
				},1500);
				last_item.removeClass('ajax-cart-animate-border');
			},delay_time);
		}
	}
	
	
	
	function update_special_cart_icon_count(){
		var cart = jQuery('.special-cart-container .display-cart-count');
		var cart_con = jQuery('.special-cart-container');
		var new_val = cart_con.find('.new_value').clone();
		
		if(cart.find('.cart-current-count.old').length){

		}
				
		cart.append(new_val.html());
		cart.find('.cart-current-count').first().addClass('old animated slideOutUpSmall');//.css({'top':'-50px'})
		cart.find('.cart-current-count').last().addClass('animated bounceInUp');
		
		if(Modernizr.cssanimations){
			
			cart.find('.cart-current-count.old').on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function(){
				cart.find('.cart-current-count.old').remove();
			});
		}else{
			
			cart.find('.cart-current-count.old').remove();
		}
		
	}
	
	
	
	
	
	function init_woo_cart(){		
		jQuery('.azoom-woocommerce-cart-wrapper.azoom-woocommerce-box .azoom-woocommerce-box-content ul.cart_list').addClass('large-block-grid-3 medium-block-grid-2 small-block-grid-1');
		jQuery('.azoom-woocommerce-cart-wrapper.azoom-woocommerce-box .azoom-woocommerce-box-content ul.cart_list > li').wrapInner('<div class="ajax-cart-content azoom-transition"></div>');
		jQuery('.azoom-woocommerce-cart-wrapper .azoom-woocommerce-box-content ul.cart_list li .ajax-cart-content').each(function(){
			var img = jQuery(this).find('a img');
			var text = jQuery(this).find('a').not('.remove').text();
			var span_el = jQuery(this).find('.quantity');
			jQuery(this).find('a').not('.remove').html(img).append('<span class="text-overflow">'+text+'</span>');
			jQuery(this).find('a').not('.remove').append(span_el).append(cart_link_html);
		});
		
		woo_initialized = true;	
	}
	
	if(jQuery(window).width() >= 800 || jQuery(window).height() >= 800){
		jQuery(document).on('added_to_cart', update_woo_cart_contents);
	}
	
	//Mobile Cart icon for tablets
	if(jQuery('.mobile-cart-holder').length){
		jQuery(document).on('click', '.mobile-cart-holder', function(e){
			if(jQuery(this).hasClass('disabled')){
				e.preventDefault();
				return;
			}
			if(!woo_initialized){
				//Initialize WooCommerce Cart for styling
				init_woo_cart();
			}
			//Only enable cart contents if the width is enough. Otherwise, let the cart link work by disabling e.preventDefault();
			if(jQuery(window).width() > 640){
				e.preventDefault();
				rockthemes_special_cart_motion();
			}
		});
	}

	jQuery(document).on('click', '.special-cart-container', function(e){
		if(jQuery(this).hasClass('disabled')){
			e.preventDefault();
			return;
		}
		if(!woo_initialized){
			//Initialize WooCommerce Cart for styling
			init_woo_cart();
		}
		e.preventDefault();
		rockthemes_special_cart_motion();
	});
	
	function rockthemes_special_cart_motion(){
		is_box_active = underbox_menu_mask.hasClass('active woocommerce-cart-active') ? true : false;
		var to_all_button_cover = jQuery('.undermenu-box-button-cover');
		
		if(!is_box_active){
			if(jQuery('.azoom-woocommerce-box-content li').length){
				to_all_button_cover.addClass('active');
			}else{
				//When switching from search, it might left the button area open. Hide it.
				to_all_button_cover.removeClass('active');
			}
			underbox_menu_mask.removeClass(remove_classes);
			underbox_menu_mask.addClass('active woocommerce-cart-active');
			underbox_menu.addClass('animated slideInDownSmall');
			is_box_active = true;
			underbox_menu_mask.css({'height':underbox_menu.height() + to_all_button_height});
		}else{
			//This is a search feature.
			if(Modernizr.cssanimations){
				if(underbox_menu.height() + to_all_button_height < 400){
					underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUpSmall');
				}else{
					underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUp');
				}
				underbox_menu.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', remove_animation_classes);
			}else{
				remove_animation_classes();
			}
		}
	}
	
	jQuery(document).on('click touchend', '.close-search-icon', function(e){
		jQuery('.rockthemes-ajax-search-input').blur();
		if(Modernizr.cssanimations){
			if(underbox_menu.height() + to_all_button_height < 400){
				underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUpSmall');
			}else{
				underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUp');
			}
			underbox_menu.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', remove_animation_classes);
		}else{
			remove_animation_classes();
		}
	});
	
	function remove_animation_classes(){
		underbox_menu_mask.removeClass('active woocommerce-cart-active').css('height','');//.slideUp();
		jQuery('.rockthemes-ajax-search-input').val('');
		underbox_menu.removeClass('animated slideOutUpSmall slideOutUp slideInDownSmall');
		underbox_menu.off('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', remove_animation_classes);
		to_all_button_cover.removeClass('active');
		is_box_active = false;
	}
	
}



function rockthemes_menu_ajax_search(){
	var underbox_menu_mask = jQuery('.azoom-undermenu-mask');//Performance increase
	var underbox_menu = jQuery('.azoom-undermenu-box');
	var is_box_active = false;
	var results_holder = jQuery('.ajax-search-results');
	var to_all_button_cover = jQuery('.undermenu-box-button-cover');
	var to_all_button = jQuery('.search-results-button');
	var to_all_button_height = 63 + 10;//+10 for radius
	var remove_classes = '';
	for (var r = 0; r < rockthemes.settings.undermenu_box_classes.length; r++){
		if(rockthemes.settings.undermenu_box_classes[r] != 'search-box-active'){
			remove_classes += rockthemes.settings.undermenu_box_classes[r]+' ';	
		}
	}

	jQuery(document).on('click', '.special-search-icon', function(e){
		e.preventDefault();
		is_box_active = underbox_menu_mask.hasClass('active search-box-active') ? true : false;
		if(!is_box_active){
			if(results_holder.find('li').length){
				to_all_button_cover.addClass('active');
			}else{
				//When switching from woo, it might left the button area open. Hide it.
				to_all_button_cover.removeClass('active');
			}
			underbox_menu.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', search_box_animation_ended);
			underbox_menu_mask.removeClass(remove_classes);
			underbox_menu_mask.addClass('active search-box-active');//.slideDown();
			underbox_menu.addClass('animated slideInDownSmall');
			underbox_menu_mask.css({'height':underbox_menu.height() + to_all_button_height});
			is_box_active = true;
		}else{
			jQuery('.rockthemes-ajax-search-input').blur();
			if(Modernizr.cssanimations){
				if(underbox_menu.height() + to_all_button_height < 400){
					underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUpSmall');
				}else{
					underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUp');
				}
				underbox_menu.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', remove_animation_classes);
			}else{
				remove_animation_classes();
			}
		}
	})
	
	jQuery(document).on('click', '.close-search-icon', function(e){
		jQuery('.rockthemes-ajax-search-input').blur();
		if(Modernizr.cssanimations){
			if(underbox_menu.height() + to_all_button_height < 400){
				underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUpSmall');
			}else{
				underbox_menu.removeClass('slideInDownSmall animated').addClass('animated slideOutUp');
			}
			underbox_menu.on('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', remove_animation_classes);
		}else{
			remove_animation_classes();
		}
	});
	
	function remove_animation_classes(){
		underbox_menu_mask.removeClass('active search-box-active').css('height','');
		jQuery('.rockthemes-ajax-search-input').val('');
		results_holder.html('');
		underbox_menu.removeClass('animated slideOutUpSmall slideOutUp slideInDownSmall');
		underbox_menu.off('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', remove_animation_classes);
		to_all_button_cover.removeClass('active');
		is_box_active = false;
	}
	
	function search_box_animation_ended(){
		underbox_menu.off('animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', search_box_animation_ended);
		jQuery('.rockthemes-ajax-search-input').focus();	
	}
	
	var keydown_timer;
	var search_text = '';
	var searching_icon = jQuery('.ajax-loading-icon');
	jQuery(document).on('keydown', '.rockthemes-ajax-search-input', function(e){
		clearTimeout(keydown_timer);		
		keydown_timer = setTimeout(function(){
			search_text = jQuery('.rockthemes-ajax-search-input').val();
			to_all_button.attr('href', rockthemes.home_url+'/?s='+search_text);
			if(search_text != '' && search_text.length > 1){
				make_ajax_search(search_text);
			}
		}, 1080);
	});
	
	
	function make_ajax_search(text){
		searching_icon.parents('.azoom-ajax-search-wrapper').addClass('searching');
		jQuery.post(rockthemes.ajaxurl, {action:'rockthemes_ajax_search', _ajax_nonce:rockthemes.nonces.asearch, search_term:text}, function(data){
			searching_icon.parents('.azoom-ajax-search-wrapper').removeClass('searching');
			results_holder.html(data);
			
			if(results_holder.find('li').length){
				to_all_button_cover.addClass('active');
			}
			
			underbox_menu_mask.stop(true, true).animate({'height':underbox_menu.height() + to_all_button_height},600);
			
		});
		
	}
}


jQuery(window).load(function(){

	//Init Inline Navigation	
	if(rockthemes.frontend_options.display_inline_nav == 'true' && jQuery('#rockthemes-inline-nav').length){
		if(Modernizr.touch && (jQuery(window).width() <= 767 || jQuery(window).height() <= 800)){
			//Remove Inline navigation if the screen size is small
			jQuery('#rockthemes-inline-nav').remove();
		}else{
			//Delay it for smooth show
			setTimeout(function(){
				rockthemes_inline_nav();
			}, 1500);
		}
	}
	
	rockthemes_activate_gototop();
	
	//Buttons and regular links for inline navigation
	rockthemes_button_link_inline_navigation();
	
	if(rockthemes.menu.enable_menu_hash_navigation == 'true'){
		rockthemes_main_nav_inline_links();
	}
	
	jQuery('.rockthemes-video-bg').each(function(){
		var that = jQuery(this);
		//Start it if it's visible
		that.find('video[data-autoplay="true"]:in-viewport').do(function(){
			jQuery(this)[0].play();
		});
		
		//Add To The Scroll Event
		jQuery(window).scroll(function(){
			that.find('video[data-autoplay="true"]:in-viewport').do(function(){
				jQuery(this)[0].play();
			});
		});
	});
	
	//Activate Masonry & Activate Portfolio Elements animation without masonry.
	//TO DO : Bind none animated element to appear or some other function
	rockthemes_activate_portfolio();
	
	
	rockthemes_display_not_visible_elements();
	
	
	//Init Sticky Header
	rockthemes_sticky_header_init();
	

	//Quick fix. First size is not correct for mobile devices. 
	if(jQuery('.azoom-title-breadcrumbs.rockthemes-parallax').length && Modernizr.ismobile){
		//rockthemes_parallax_bg_image(jQuery('.azoom-title-breadcrumbs.rockthemes-parallax'));
	}


	/*
	**	Activate Lightbox Effect
	**
	**	Lightbox will need to be activated after window.load. Otherwise gallery mode does not work
	*/
	rockthemes_activate_lightbox();
	
	jQuery(document).trigger('rockthemes:mega_menu_resize');
});

function rockthemes_activate_gototop(){
	//Return if not enabled
	if(jQuery('#azoom-go-to-top').length < 1) return;
	
	jQuery(window).scroll(function() {
		if(jQuery(this).scrollTop() > 480) {
			jQuery('#azoom-go-to-top').css({'opacity':'1', 'visibility':'visible'});	
		} else {
			jQuery('#azoom-go-to-top').css({'opacity':'', 'visibility':''});	
		}
	});
 
	jQuery('#azoom-go-to-top').click(function() {
		jQuery('body,html').animate({scrollTop:0},800);
	});	
}


//Specially for Vimeo. It starts with buttons. This delay will hide it
function rockthemes_display_not_visible_elements(){
	if(typeof rockthemes.visibility_hidden_elements	== 'undefined' || !rockthemes.visibility_hidden_elements) return;
	
	var els = rockthemes.visibility_hidden_elements;
	for(var i=0;i<els.length;i++){
		els[i].css({'visibility':'visible'});
		if(els[i].hasClass('not-visible')){
			els[i].removeClass('not-visible');	
		}
	}
}



function rockthemes_button_link_inline_navigation(){
	jQuery('.rockthemes-inline-link').each(function(){
				
		var that = jQuery(this),
			this_hash = that.attr('href').replace(/.*?\#/,''),
			wp_nav_height = 0,
			time = 1000;
			
		if(rockthemes.frontend_options.is_admin_bar_showing){
			wp_nav_height = jQuery('#wpadminbar').outerHeight();
		}
			
			
		that.click(function(e){
			
			e.preventDefault();
		
			if(jQuery('#'+this_hash).length < 1) return;
			
			var top_val = jQuery('#'+this_hash).offset().top + wp_nav_height;
			top_val = top_val - parseInt(rockthemes.menu.sticky_height);

			jQuery(document).trigger('rockthemes:scroll_events_disable');
			jQuery('html, body').animate({'scrollTop': top_val}, time, 'easeInOutQuart', function(){
				setTimeout(function(){
					if(history.pushState) {
						history.pushState(null, null, '#'+this_hash);
					}
					else {
						location.hash = this_hash;
					}
				},100);
				jQuery(document).trigger('rockthemes:scroll_events_enable');
			});
			
		});
	});
}


/*
**	Rockthemes - Main Menu Inline Navigation
**
**	@since	:	1.3
*/
function rockthemes_main_nav_inline_links(){
	var rtm_nav = jQuery('#rtm-navigation');
	var hash_links = jQuery('#rtm-navigation a, #rnmm li > a').filter(function(){
		return typeof jQuery(this).attr('href') != 'undefined' && jQuery(this).attr('href').indexOf('#') > -1;
	});
	
	
	
	rockthemes_main_nav_inline_nav_events(hash_links);
	
	if(hash_links.length < 1) return;
	
	var init_ended = false;
	
	var wp_nav_height = 0;
	if(rockthemes.frontend_options.is_admin_bar_showing){
		wp_nav_height = jQuery('#wpadminbar').outerHeight();
	}
	
	var moving = false;
	
	//Activate navigation click
	hash_links.click(function(e){
		
		moving = true;

		var this_hash = jQuery(this).attr('href').replace(/.*?\#/,'');

		if( jQuery(this).attr('href').charAt(0) == '#' || jQuery(this).attr('href').split('#')[0] == window.location.href.split('#')[0]){
			e.preventDefault();	
		}else{
			return;	
		}		
		
		var time = 1000;
		if(rtm_nav.find('li.active').length){
			time = Math.abs(parseInt(rtm_nav.find('li.active').index()) - jQuery(this).index()) * 1000;
			
		}
		
		var top_val = jQuery('#'+this_hash).offset().top - wp_nav_height;
		if(jQuery('.header-sticky-active').length && jQuery('.sticky-header-wrapper').length &&
			jQuery('.sticky-header-wrapper').offset().top < top_val && top_val - rockthemes.menu.sticky_height > 0){
			top_val = top_val - parseInt(rockthemes.menu.sticky_height);
		}
		
		if(rtm_nav.find('li.current-menu-item').length){
			rtm_nav.find('li.current-menu-item').removeClass('current-menu-item');
		}
		if(jQuery('#rnmm li.current-menu-item').length){
			jQuery('#rnmm li.current-menu-item').removeClass('current-menu-item');
		}
		if(jQuery(this).parents('li').length){
			jQuery(this).parents('li').addClass('current-menu-item');
		}
		
		jQuery(document).trigger('rockthemes:scroll_events_disable');
		jQuery('html, body').animate({'scrollTop': top_val}, time, 'easeInOutQuart', function(){
			setTimeout(function(){
				if(history.pushState) {
					history.pushState(null, null, '#'+this_hash);
				}
				else {
					location.hash = this_hash;
				}
				moving=false;
			},100);
			jQuery(document).trigger('rockthemes:scroll_events_enable');
		});
	});
	
	var dt;//Debounce Timer
	jQuery(document).on('rockthemes:in_view_main_nav', function(e, elem, h, li){
		
		if(moving)	return;
		
		var _li = rtm_nav.find('#'+li);
		var rd = (_li.index() == 0) ? 0 : 0 + wp_nav_height; // Required Difference
		var top_val = jQuery('#'+h).offset().top ;
		var bottom_val = jQuery('#'+h).offset().top + jQuery('#'+h).height();

		if(jQuery('.sticky-header-wrapper').length ){
			top_val = top_val - parseInt(rockthemes.menu.sticky_height);
			bottom_val = bottom_val - parseInt(rockthemes.menu.sticky_height);
		}

		//Only chnage navigation if the element is at the top
		if((jQuery(window).scrollTop() + wp_nav_height) <= bottom_val && top_val - jQuery(window).scrollTop() <= rd){
			if(!_li.hasClass('current-menu-item')){
				rtm_nav.find('li.current-menu-item').removeClass('current-menu-item');
				_li.addClass('current-menu-item');
			}
		}
	});
	
}
function rockthemes_main_nav_inline_nav_events(elems){	
	elems.each(function(){
		var that = jQuery(this);
		var en = that.attr('href').replace(/.*?\#/,''); //en refers to elem name
		if(!en.length) return;
		
		
		//For initial selected element
		var that_obj = new Object();
		that_obj.data = {that:that, en:en, li:that.parents('li').attr('id')}
		rockthemes_main_nav_inline_nav_view_event(that_obj);

		jQuery(window).on('scroll', that_obj.data, rockthemes_main_nav_inline_nav_view_event);
	});
}
function rockthemes_main_nav_inline_nav_view_event(e){
	jQuery('#'+e.data.en+':in-viewport').do(function(){
		
		jQuery(document).trigger('rockthemes:in_view_main_nav', [e.data.that, e.data.en, e.data.li]);
	});
}




/*
**	Rockthemes Inline Navigation
**
**	@since	:	1.3
*/
function rockthemes_inline_nav(){
	var init_ended = false;
	
	var wp_nav_height = 0;
	if(rockthemes.frontend_options.is_admin_bar_showing){
		wp_nav_height = jQuery('#wpadminbar').outerHeight();
	}
	
	//Activate navigation click
	jQuery('#rockthemes-inline-nav li').click(function(e){
		var this_hash = jQuery(this).attr('id').replace('rin-','');
		
		var time = 1000;
		if(jQuery('#rockthemes-inline-nav li.active').length){
			time = Math.abs(parseInt(jQuery('#rockthemes-inline-nav li.active').index()) - jQuery(this).index()) * 1000;
			
		}

		
		var top_val = jQuery('#'+this_hash).offset().top - wp_nav_height;
		if(jQuery('.header-sticky-active').length && jQuery('.sticky-header-wrapper').length &&
			jQuery('.sticky-header-wrapper').offset().top < top_val && top_val - rockthemes.menu.sticky_height > 0){
			top_val = top_val - parseInt(rockthemes.menu.sticky_height);
		}
		
		
		jQuery(document).trigger('rockthemes:scroll_events_disable');
		jQuery('html, body').animate({'scrollTop': top_val}, time, 'easeInOutQuart', function(){
			setTimeout(function(){
				if(history.pushState) {
					history.pushState(null, null, '#'+this_hash);
				}
				else {
					location.hash = this_hash;
				}
			},100);
			jQuery(document).trigger('rockthemes:scroll_events_enable');
		});
	});
	
	var dt; //Delete Timer
	jQuery(document).on('rockthemes:in_view_nav', function(e, elem, id){
		if(!init_ended) return;
		var rd = (elem.index() == 0) ? 40 : 10 + wp_nav_height; // Required Difference
		var top_val = jQuery('#'+id).offset().top;
		
		if(jQuery('.main-header-area').length && jQuery('.main-header-area').offset().top < top_val && top_val - rockthemes.menu.sticky_height > 0){
			top_val = top_val - parseInt(rockthemes.menu.sticky_height);
		}
		
		
		
		//Only chnage navigation if the element is at the top
		if(top_val - jQuery(window).scrollTop() <= rd){
			if(!jQuery('#rin-'+id).hasClass('active')){
				clearTimeout(dt);
				jQuery('#rockthemes-inline-nav li.active').removeClass('active title-active');
				jQuery('#rin-'+id).addClass('active title-active');
				dt = setTimeout(function(){
					jQuery('#rin-'+id).removeClass('title-active');
				},1500);
			}
		}
	});
	
	
	//Initialize Inline Navigation
	jQuery('#rockthemes-inline-nav').css('top', parseInt((parseInt(jQuery(window).height() - (jQuery('#rockthemes-inline-nav').height() - 30)) / 2))+'px');
	jQuery(window).smartresize(function(){
		jQuery('#rockthemes-inline-nav').css('top', parseInt((parseInt(jQuery(window).height() - (jQuery('#rockthemes-inline-nav').height() - 30)) / 2))+'px');
	});
	
	var inl = jQuery('#rockthemes-inline-nav li').length; // Inline Nav Length
	
	jQuery(document).on('rockthemes:start_inline_nav', function(){
		jQuery('#rockthemes-inline-nav li.deactive').each(function(i,elem){
			var that = jQuery(this);
			setTimeout(function(){
				that.removeClass('deactive');
				if((parseInt(inl) - i) <= 1){
					init_ended = true;
					dt = setTimeout(function(){
						rockthemes_inline_nav_events(true);
					},100);
				}
			}, parseInt(100 * i));
		});
	});
	
	jQuery(document).on('rockthemes:hide_inline_nav', function(){
		jQuery('#rockthemes-inline-nav li').each(function(i,elem){
			var that = jQuery(this);
			setTimeout(function(){
				that.addClass('deactive');
				if((parseInt(inl) - i) <= 1){
					init_ended = true;
					dt = setTimeout(function(){
						rockthemes_inline_nav_events(false);
					},100);
				}
			}, parseInt(100 * i));
		});
	});
	
	//Main Starter
	jQuery(document).trigger('rockthemes:start_inline_nav');
}

function rockthemes_inline_nav_events(on_add){
	on_add = (typeof on_add != 'undefined' && on_add) ? true : false;
	
	jQuery('#rockthemes-inline-nav li').each(function(){
		var that = jQuery(this);
		var en = that.attr('id').replace('rin-',''); //en refers to elem name
				
		if(on_add){
			var that_obj = new Object();
			that_obj.data = {that:that, en:en}
			rockthemes_inline_nav_view_event(that_obj);
		}
		
		if(on_add){
			jQuery(window).on('scroll', {that:that,en:en}, rockthemes_inline_nav_view_event);
		}else{
			
			jQuery(window).off('scroll', rockthemes_inline_nav_view_event);
		}
	});
	
	if(on_add){
		jQuery('#rockthemes-inline-nav').addClass('initialized');
	}else{
		jQuery('#rockthemes-inline-nav.initialized').removeClass('initialized');
	}
}
function rockthemes_inline_nav_view_event(e){
	jQuery('#'+e.data.en+':in-viewport').do(function(){
		jQuery(document).trigger('rockthemes:in_view_nav', [e.data.that, e.data.en]);
	});
}





function rockthemes_mobile_menu(){
	if(rockthemes.frontend_options.header_location == 'top_navigation' && (rockthemes.resposivity === 'false' || jQuery('html').hasClass('ie9'))) return;
	
	//mm refers to mobile menu
	var mm = jQuery('#rtm-navigation').clone(true);
	
	mm.wrapInner('<div id="rockthemes_mobile_menu"></div>');
	mm = mm.find('#rockthemes_mobile_menu').unwrap();
	
	mm.find('#nav').replaceWith(jQuery('<nav id="rnmm">'+mm.find('#nav').html()+'</nav>'));
	
	mm.find('.subtitle').remove();
	mm.find('.dismiss-mobile').remove();
	mm.find('.nav-icon').unwrap();
	mm.find('.nav-menu').removeClass('nav-menu');
	mm.find('.rtm-menu').removeClass('rtm-menu');
	mm.find('.columns').removeClass('columns');
	mm.find('.megamenu').removeClass('megamenu');
	mm.find('.regularmenu').removeClass('regularmenu');
	mm.find('.menu-item').removeClass('menu-item');
	mm.find('.dismiss-icon').remove();
	mm.find('.rtm-menu-sticker').remove();
	mm.find('.description').remove();
	
	mm.find('.rtm-widget').remove();
	mm.find('hr').remove();
	mm.find('*[data-mm-align]').removeAttr('data-mm-align');
	mm.find('*[data-mm-width]').removeAttr('data-mm-width');
	
	mm.find('.mobile-icon').each(function(){
		if(jQuery(this).parent().parent().hasClass('heading-nav')){
			jQuery(this).remove();	
		}else{
			jQuery(this).parent().prepend('<i class="'+jQuery(this).attr('data-mobile-icon')+'"></i>');
		}
	});

	mm.find('.heading-label.link-disabled').each(function(){
		var tmm = jQuery(this);
		tmm.find('.heading-nav > .azoom-heading-icon').remove();
		tmm.find('.heading-nav > h3').children().unwrap();
		tmm.find('.heading-nav').wrap('<a href="#"></a>');
		tmm.find('.heading-nav').children().unwrap();
	});
	
	//Widget titles are using a different h3 system. Unwrap them
	mm.find('.rtm-has-widget h3.widget-title').contents().unwrap();
	
	for(var i = 1; i<13; i++){
		mm.find('.rtm-menu-depth-'+i).removeClass('rtm-menu-depth-'+i);
		mm.find('.large-'+i).removeClass('large-'+i);
		mm.find('.medium-'+i).removeClass('medium-'+i);
		mm.find('.small-'+i).removeClass('small-'+i);
	}
	
	mm.find('li > ul').each(function(){
		var h2_clone = jQuery(this).parent().find(' > a').clone();
	});

	mm.find('.current-menu-item').addClass('Selected');
	
	var mm_api;//When mm defined, this variable will hold the api
	
	//Only add footer if the side navigation is available
	var mmenu_settings = {
			offCanvas: {
				position: rockthemes.is_rtl === 'rtl' ? 'right' : 'left'
			},
			navbar : {
				title:rockthemes.mobile_menu.main_title
			},
			navbars: [],
			slidingSubmenus:true,
		};

	if(rockthemes.frontend_options.header_location === 'side_navigation'){
		
		mmenu_settings.navbars.push({
            position: 'bottom',
            content: ''
         });
		 
		 mmenu_settings.navbars.push({
			position:'top',
			content:'',
		});
	}

	//Makes the menu open light and fast in mobile devices
	if(rockthemes.frontend_options.header_location == 'top_navigation'){

	}

	if(jQuery(window).width() < 1024 || rockthemes.frontend_options.header_location === 'side_navigation'){
		jQuery('body').append(mm);
		jQuery('#rnmm').mmenu(mmenu_settings);
		mm_api = jQuery('#rnmm').data('mmenu');
		jQuery('body').addClass('mobile-menu-added');
		jQuery('#rockthemes_mobile_menu').remove();
	}else{
		jQuery(window).smartresize(function(){
			if(jQuery(window).width() < 1024 && !jQuery('body').hasClass('mobile-menu-added')){
				jQuery('body').append(mm);
				jQuery('#rnmm').mmenu(mmenu_settings);
				mm_api = jQuery('#rnmm').data('mmenu');
				jQuery('body').addClass('mobile-menu-added');
				jQuery('#rockthemes_mobile_menu').remove();
			}
		});
	}
	
	
	//Disabling regular menu
	if(rockthemes.menu.main_menu_model == 'menu_use_mobile_for_main'){
		
		//Set the style
		jQuery('html').addClass('menu_use_mobile_for_main');
		
		//Remove main menu
		jQuery('#rtm-navigation').remove();
	}
	
	
	if(rockthemes.frontend_options.header_location === 'side_navigation'){
	
		var h_height = 0;
		var f_height = 0;

		jQuery('html').addClass('side_nav_menu');
		var m_logo = jQuery('.logo-container').clone();
		var m_ht2 = jQuery('.header-top-2').clone();
		m_ht2.removeClass('hide');
		jQuery('.header-all-wrapper').children().each(function(){
			if(jQuery(this).hasClass('azoom-title-breadcrumbs')) return;
			jQuery(this).remove();
		});
				
		//Remove header area from the inline navigation
		if(jQuery('#rockthemes-inline-nav').length){
			jQuery('#rockthemes-inline-nav .rockthemes-inline-nav-elem').each(function(){
				if(jQuery('#'+jQuery(this).attr('id').toString().replace('rin-','')).length < 1){
					jQuery(this).remove();
				}
			});
		}
		
		if(m_ht2.hasClass('not-visible')){
			m_ht2.removeClass('not-visible');
		}
		m_ht2.find('.not-visible').removeClass('not-visible');
		m_ht2.find('.columns').css('width','100%');
		
		jQuery('.mm-navbar-top').prepend(m_logo);
		jQuery('.mm-menu .mm-navbar-bottom').append(m_ht2);
		
		var h_height = jQuery('.mm-navbar-top').height();
		var f_height = jQuery('.mm-navbar-bottom').height();
		var n_height = jQuery('#rnmm').height();
		
		jQuery('.mm-navbar-top').imagesLoaded(function(){
			rockthemes_mobile_menu_side_resize();
		});
				
		//Mobile version switcher
		jQuery('body').append(
			jQuery('<div id="mobile-menu-list-icon" class="mm-slideout">'+
						'<span></span>'+
					'</div>')
		);
				
		jQuery(window).resize(function(){
			rockthemes_mobile_menu_side_resize();
			if(typeof mm_api != 'undefined'){
				mm_api.close();	
			}
		});
	}
	
	
	// Base expand
	jQuery(document).on('click touchend',  '.mobile-menu-switcher-holder, #mobile-menu-list-icon', function(e){
		e.preventDefault();

		jQuery(document).trigger('rockthemes:mobile_menu_expand');
				
		//jQuery( '#rnmm' ).trigger("open.mm");
		if(typeof mm_api != 'undefined'){
			mm_api.open();
		}
	});
	
	
}

/*
**	Mobile Menu Used in left Side will need to resize some elements
**
**	@return	:	void
*/
function rockthemes_mobile_menu_side_resize(){
	//We add 140 more for minimum required height
	var _h = parseInt(jQuery('.mm-navbar-bottom').height()) + parseInt(jQuery('.mm-navbar-top').height()) + 140;
	
	if(jQuery('#rnmm').height() < _h){
		jQuery('.logo-container').css({'display':'none'});	
	}else if(jQuery('.logo-container').css('display') === 'none'){
		jQuery('.logo-container').css({'display':''});	
	}
	
	var h_height = jQuery('.mm-navbar-top').outerHeight(),
		f_height = jQuery('.mm-navbar-bottom').outerHeight(),
		n_height = jQuery('#rnmm').height(),
		list_height = n_height - h_height;
	
	if(jQuery('.mm-navbar-bottom').length && f_height){
		list_height = list_height - f_height;	
	}

	if(parseInt(jQuery('.mm-navbar-bottom').css('bottom')) !== 0){
		f_height = f_height + parseInt(jQuery('.mm-navbar-bottom').css('bottom'));
	}
	
	if(parseInt(jQuery('.mm-navbar-bottom').css('bottom')) !== 0){
		f_height = f_height + parseInt(jQuery('.mm-navbar-bottom').css('bottom'));
	}
	jQuery('#rnmm > .mm-panel').css({'padding':'0px', 'top':h_height+'px', 'height':list_height+'px'});
}








/*
**	Sticky Header Configuration
**
*/
	
function rockthemes_sticky_header_init(){
	var header = jQuery('.main-header-area');
	if(header.length < 1) return;
	if(!header.hasClass('header-sticky')) return;
	
	
	var wp_nav_height = 0;
	if(rockthemes.frontend_options.is_admin_bar_showing){
		wp_nav_height = jQuery('#wpadminbar').outerHeight();
	}
		
	header.parents('.sticky-header-wrapper').css('height', header.height()+'px');
	
	var starting_point = header.parents('.sticky-header-wrapper').offset();

	
	if(rockthemes.frontend_options.is_admin_bar_showing){
		starting_point.top -= wp_nav_height;
	}
	
	starting_point.bottom = parseInt(starting_point.top + header.parents('.sticky-header-wrapper').height());
	
	

	//Header top element's will change the coordinates of sticky wrapper. Thus header top elements will trigger this event
	jQuery(document).on('rockthemes:sticky_menu_resize', function(){
		starting_point = header.parents('.sticky-header-wrapper').offset();
		if(rockthemes.frontend_options.is_admin_bar_showing){
			starting_point.top -= 28;
		}
		starting_point.bottom = parseInt(starting_point.top + header.parents('.sticky-header-wrapper').height());
		
		
	});
	
	
	jQuery(window).smartresize(function(){
		jQuery(document).trigger('rockthemes:sticky_menu_resize');
	});

	var sticky_active = false;
	var sticky_animated = false;
	
	jQuery(window).scroll(function(){
		/*
		**	Currently Not Used.
		**	Curvy Slider Fullscreen and Full Width Special Grid breaks the starting coords.
		**	Currently we are using this method to avoid any async element size changes.
		**
		*/	
		
		if(jQuery(this).scrollTop() > starting_point.top){
			if(!sticky_active){
				header.addClass('header-sticky-active');
				if(header.parents('.sticky-header-wrapper').length){
					header.parents('.sticky-header-wrapper').addClass('wrapper-unsticky');	
				}
				jQuery('.header-all-wrapper').addClass('sticky-activated');
				sticky_active = true;
			}
			
			if(jQuery(this).scrollTop() > starting_point.bottom) {
				if(!sticky_animated){
					sticky_animated = true;

					setTimeout(function(){
					header.addClass('header-sticky-animate');
					},30);
				}
			}else if(jQuery(this).scrollTop() <= starting_point.bottom) {
				if(sticky_animated){
					header.removeClass('header-sticky-animate');
					sticky_animated = false;
				}
			}

		}else if(jQuery(this).scrollTop() <= starting_point.top ) {
			if(!sticky_active) return;
			
			header.removeClass('header-sticky-active');
			if(header.parents('.sticky-header-wrapper').length){
				header.parents('.sticky-header-wrapper').removeClass('wrapper-unsticky');	
			}
			jQuery('.header-all-wrapper').removeClass('sticky-activated');
			sticky_active = false;
			
			if(!sticky_animated) return;
			if(jQuery(this).scrollTop() <= starting_point.bottom) {
				setTimeout(function(){
				header.removeClass('header-sticky-animate');
				sticky_animated = false;
				},20);
			}
		}
		
	});
}





/*
**	RPB can use multiple gradient background colors. This function generates a background
**	linear color with Browser prefix.
**
*/
function rockthemes_multi_bg_colors(){
	
	if(jQuery('.multi-bg-colors').length < 0) return;
	
	var spfx = rockthemes.frontend_options.style_prefix,
		value = '',
		is_responsive = jQuery(window).width() >= 800 ? false : true,
		flow = !is_responsive ? 'left' : 'top',
		size_l = '50',
		size_r = '50';
	
	
	jQuery('.multi-bg-colors').each(function(){
		var mbg = jQuery(this);
		var mbc = mbg.attr('data-multibg-colors');
		if(typeof mbc == 'undefined') return;

		var mbca = mbc.split(',');
		
		if(spfx.indexOf('ms') > -1){
			value = spfx+'linear-gradient('+flow+', '+mbca[0]+' '+size_l+'%, '+mbca[1]+' '+size_r+'%)';
		}else{
			value = spfx+'linear-gradient('+flow+', '+mbca[0]+' '+size_l+'%, '+mbca[1]+' '+size_r+'%)';
		}
		
		if(is_responsive){
			mbg.css('background',mbca[0]);
		}else{
			mbg.css('background-image', value);
		}
	});
}






/*External Libraries*/




/*
Plugin: jQuery Parallax
Version 1.1.3
Author: Ian Lunn
Twitter: @IanLunn
Author URL: http://www.ianlunn.co.uk/
Plugin URL: http://www.ianlunn.co.uk/plugins/jquery-parallax/

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html
*/
(function( $ ){
	var $window = $(window);
	var windowHeight = $window.height();

	$window.resize(function () {
		windowHeight = $window.height();
	});

	$.fn.parallax = function(xpos, speedFactor, outerHeight) {
		var $this = $(this);
		var getHeight;
		var firstTop;
		var paddingTop = 0;
		
		//get the starting position of each element to have parallax applied to it		
		$this.each(function(){
		    firstTop = $this.offset().top;
		});
			
		// setup defaults if arguments aren't specified
		if (arguments.length < 1 || xpos === null) xpos = "50%";
		if (arguments.length < 2 || speedFactor === null) speedFactor = 0.1;
		if (arguments.length < 3 || outerHeight === null) outerHeight = true;
				
		if (outerHeight) {
			getHeight = function(jqo) {
				return jqo.outerHeight(true);
			};
		} else {
			getHeight = function(jqo) {
				return jqo.height();
			};
		}
		
		// function to be called whenever the window is scrolled or resized
		function update(){
			var pos = $window.scrollTop();				

			$this.each(function(){
				var $element = $(this);
				var top = $element.offset().top;
				var height = getHeight($element);

				// Check if totally above or totally below viewport
				if (top + height < pos || top > pos + windowHeight) {
					return;
				}
				
				/*
				**	Do not let the background image to above or below the content area
				**
				**	@author	:	XanderRock
				*/
				var new_pos = Math.round(($this.offset().top - pos) * speedFactor),
					ih_obj = parseInt($this.attr('data-image-main-height'));
				
				
				if((new_pos > 0 && new_pos > ($this.offset().top - pos)) || new_pos + ih_obj < windowHeight) return;
				
				$this.css('backgroundPosition', xpos + " " + new_pos + "px");
			});
		}		

		$window.on('scroll', update).resize(update);
		update();
	};
})(jQuery);






var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "Other";
		this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "Unknown";
	},
	searchString: function (data) {
		for (var i = 0; i < data.length; i++) {
			var dataString = data[i].string;
			this.versionSearchString = data[i].subString;

			if (dataString.indexOf(data[i].subString) !== -1) {
				return data[i].identity;
			}
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index === -1) {
			return;
		}

		var rv = dataString.indexOf("rv:");
		if (this.versionSearchString === "Trident" && rv !== -1) {
			return parseFloat(dataString.substring(rv + 3));
		} else {
			return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
		}
	},

	dataBrowser: [
		{string: navigator.userAgent, subString: "Chrome", identity: "Chrome"},
		{string: navigator.userAgent, subString: "MSIE", identity: "Explorer"},
		{string: navigator.userAgent, subString: "Trident", identity: "Explorer"},
		{string: navigator.userAgent, subString: "Firefox", identity: "Firefox"},
		{string: navigator.userAgent, subString: "Safari", identity: "Safari"},
		{string: navigator.userAgent, subString: "Opera", identity: "Opera"}
	]

};
BrowserDetect.init();




//If SVG not supported replace it with png
jQuery.fn.rockthemes_svg_control = function(){
	if(!Modernizr) return;
	if(!Modernizr.svg) {
		jQuery('img[src*="svg"].use_svg').attr('src', function() {
			return jQuery(this).attr('src').replace('.svg', '.png');
		});
	}
}