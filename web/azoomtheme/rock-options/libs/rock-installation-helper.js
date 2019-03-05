// JavaScript Document
jQuery(document).ready(function(){
	'use strict';
	
	rockthemes_ih_func(parseInt(rockthemes_ih.current_pointer));
    function rockthemes_ih_func(i) {
		if(typeof i === 'undefined' || typeof rockthemes_ih.pointers[i] === 'undefined') return;
        var point = rockthemes_ih.pointers[i],
        options = jQuery.extend( point.options, {
			pointerWidth:400,
			buttons: function (event, t) {
				var button = jQuery('<a id="pointer-close" style="margin-left:5px;" class="button-secondary"><span class="dashicons dashicons-no-alt" style="margin-top:3px;"></span> Dismiss</a>');
				button.bind('click.pointer', function (e) {
					e.preventDefault();
					
					jQuery.post( ajaxurl, {
						action: 'rockthemes_ih_dismiss'
					},function(data){
						t.element.pointer('close');
					});
				});
				return button;
			},
            close: function(e) {
				//Do Nothing
            }
        });
		
 		
		var open_pointer = jQuery(point.target);
		
        open_pointer.pointer( options ).pointer('open');
		
		if(typeof options.button == 'undefined' || !options.button) return;
		
		var attr = (typeof options.button !== 'undefined' && typeof options.button.link != 'undefined' && options.button.link && options.button.link != '') ? 'href="'+options.button.link+'"' : '';
		
		jQuery('.wp-pointer-buttons').append('<a '+attr+' id="pointer-primary" class="button-primary"><span class="dashicons dashicons-arrow-right" style="margin-top:3px;"></span>'+options.button.text+'</a>');
		
		jQuery('#pointer-primary').click(function(e){
			e.preventDefault();
			
			var is_linked = false;
			var the_link = '';
			var finish = 'no';
			if(typeof jQuery(this).attr('href') != 'undefined' && jQuery(this).attr('href') && jQuery(this).attr('href') != '' && jQuery(this).attr('href') != 'finish'){
				is_linked = true;
				the_link = jQuery(this).attr('href');
			}else if(typeof jQuery(this).attr('href') != 'undefined' && jQuery(this).attr('href') && jQuery(this).attr('href') == 'finish'){
				finish = 'yes';
			}
			open_pointer.pointer('close');
			jQuery('.wp-pointer').remove();
			
			jQuery.post(ajaxurl, {
					pointer: point.pointer_id,
					steps: rockthemes_ih,
					rih_finished:finish,
					action: 'rockthemes_ih_last_step'
				},
				function(data){
					
					if(is_linked) {
						window.location = the_link;
						return;
					};
					
					
					
					if(typeof options.button.link != 'undefined' && !options.button.link && options.button.link != ''){
						return;	
					}
					
					
					
					//Check if the new number of pointer is valid for the current url
					if(typeof rockthemes_ih.pointers[parseInt(data)] !== 'undefined' && rockthemes_ih.pointers[parseInt(data)].location.indexOf(window.location) < 0){
						return;	
					}
					
					
					if(parseInt(data) < rockthemes_ih.pointers.length){
						rockthemes_ih_func(parseInt(data));
					}
					
					if(jQuery('.wp-pointer').length){
						
						jQuery('html, body').animate({'scrollTop':(parseInt(jQuery('.wp-pointer').last().offset().top) - 180)},180);
					}
				}
			);			
		});
    }
});