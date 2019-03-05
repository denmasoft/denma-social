/*
 * Updated version of jQuery appear plugin
 *
 * Copyright (c) 2014 Xander Rock
 *
 * Based on the 2012 Andrey Sidorov appear plugin.
 * licensed under MIT license.
 *
 * Improved version of scroll
 * https://github.com/morr/jquery.appear/
 *
 * Version: 0.1
 */
(function($) {
	var selectors = [];
	
	var check_binded = false;
	var check_lock = false;
	var defaults = {
		interval: 250,
		force_process: false
	}
	var $window = $(window);
	
	var $prior_appeared;

	function process() {
		//If there isn't any element in selectors array then return
		if(selectors.length < 1) return;
		
		check_lock = false;
		
		var win = {
			width:$window.width(),
			height:$window.height(),
			left:$window.scrollLeft(),
			top:$window.scrollTop()	
		}
		
		for (var index = 0; index < selectors.length; index++) {
			var $appeared = $(selectors[index]).filter(function() {
				var el = $(this);
				var offset = el.offset();
				var top = offset.top;
				var left = offset.left;
		
				if (top + el.height() >= win.top &&
				top - (el.data('appear-top-offset') || 0) <= win.top + win.height &&
				left + el.width() >= win.left &&
				left - (el.data('appear-left-offset') || 0) <= win.left + win.width) {
					return true;
				} else {
					return false;
				}
				
				//Garbage Collector				
				offset = null;
				top = null;
				el = null;
			
			});
			
			$appeared.trigger('appear', [$appeared]);
			
			if ($prior_appeared) {
				var $disappeared = $prior_appeared.not($appeared);
				$disappeared.trigger('disappear', [$disappeared]);
			}
			$prior_appeared = $appeared;
		}
		
		//Garbage Collector
		win=null;
	}
  


	// "appeared" custom filter
	$.expr[':']['appeared'] = function(element) {
		var $element = $(element);
		if (!$element.is(':visible')) {
			return false;
		}
		
		var window_left = $window.scrollLeft();
		var window_top = $window.scrollTop();
		var offset = $element.offset();
		var left = offset.left;
		var top = offset.top;
		
		if (top + $element.height() >= window_top &&
		top - ($element.data('appear-top-offset') || 0) <= window_top + $window.height() &&
		left + $element.width() >= window_left &&
		left - ($element.data('appear-left-offset') || 0) <= window_left + $window.width()) {
			return true;
		} else {
			return false;
		}
	}

	$.fn.extend({
		// watching for element's appearance in browser viewport
		appear: function(options) {
			var opts = $.extend({}, defaults, options || {});
			var selector = this.selector || this;
			selectors.push(selector);
			
			if (!check_binded) {
				var on_check = function() {
					if (check_lock) {
						return;
					}
					check_lock = true;
					
					process();
				};
			
				check_binded = true;
			}
			process();

			return $(selector);
		}
	});

  
  
	$.fn.extend({
		// Removing the element from the list. Increases the loop performance.
		appearOff: function() {
			var selector = this.selector || this;
			var exists_int = selectors.indexOf(selector);
			if(exists_int > 1){
				selectors.splice(exists_int,1);
			}
			return $(selector);
		}
	});

	
	/*
	**	This function is required. But currently disabled as it's increasing the event numbers.
	**
	*/  
	var throt_func = process;
	/*
	**	This sometimes stops the event and animation won't start on some elements.
	**	Thus it's removed.
	**
	if(typeof window.requestAnimationFrame !== 'undefined' && window.requestAnimationFrame){
		throt_func = function(){
			window.requestAnimationFrame(process);
		}
	}
	*/
	/*
	**	Instead of adding the event on each element, we use document.ready to add event only once.
	**
	*/
	$(window).load(function(){		
		$(window).scroll(throt_func).resize(throt_func);
	
	/*
		$(window).scroll(
			_.throttle( 
				throt_func, 
				100
			)
		).resize(
			_.debounce(
				throt_func,
				150
			)
		);
		*/
		//Default starter
		setTimeout(
			throt_func, 
			400
		);
	});


})(jQuery);