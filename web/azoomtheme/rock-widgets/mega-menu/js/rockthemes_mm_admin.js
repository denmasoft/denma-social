// JavaScript Document
jQuery(document).ready(function(){
	"use strict";
	
	/*
	**	Init the required functions and UI elements
	**
	*/
	rmm_init();
	
	
	
	jQuery(document).on('rockthemes_mm:ready', function(){
		var height = jQuery(window).height() - jQuery('.rockthemes-mm-header').outerHeight() - jQuery('.rmm-actions').outerHeight();
		jQuery('.rockthemes-mm-element').css('height',height+'px');
	});
	
	jQuery(window).resize(function(){
		var height = jQuery(window).height() - jQuery('.rockthemes-mm-header').outerHeight() - jQuery('.rmm-actions').outerHeight();
		jQuery('.rockthemes-mm-element').css('height',height+'px');
	});
	
	
	jQuery(document).on('click', '.rockthemes_mm_tab_nav', function(e){
		e.preventDefault();
		
		var id = jQuery(this).attr('id').replace('_button',''),
			top = parseInt(jQuery('#'+id).position().top);
		
		rmm_log('NEW VALUE '+top);
		
		jQuery('.rockthemes-mm-element').scrollTop(top);
	});
	
	
	jQuery(document).on("click", ".rockthemes-mm-columns-selector .col", function(){
		var i_limit = jQuery(this).index();
		var container = jQuery(this).parent();
		var i = 0;
				
		container.find(".selected").removeClass("selected");
		
		for(i=0; i< i_limit + 1; i++){
			container.find(".col").eq(i).addClass("selected");	
		}
	});
	
	
	jQuery(document).on('click', '.rockthemes_mm_image_uploader', function(e){

		e.preventDefault();
		
		var send_attachment_bkp = wp.media.editor.send.attachment,
			that = jQuery(this),
			ref = that.attr('data-ref'),
			rmmm = true;
							
		wp.media.editor.send.attachment = function(props, attachment) {

			if(rmmm){
				//URL of the image
				jQuery('#'+ref).val(attachment.url);
				//Set an attribute for the image id
				jQuery('#'+ref).attr('data-image_id', attachment.id);
				
				jQuery('#'+ref).parents('.rockthemes-mm-element-setting').addClass('image-added');
				
				//Add Image as reference
				if(jQuery('#'+ref).parent().find('.rockthemes-mm-image-wrapper').length > 0){
					jQuery('#'+ref).parent().find('.rockthemes-mm-image-wrapper').remove();	
				}
				jQuery('#'+ref).before('<div class="rockthemes-mm-image-wrapper"><img src="'+attachment.url+'" /></div>');
			}else{
				return send_attachment_bkp.apply( this, [props, attachment] );
			}
		}

		wp.media.editor.open();

		return false;
	
	});
	
	jQuery(document).on('click', '.rockthemes_mm_image_delete', function(e){
		e.preventDefault();
		
		var send_attachment_bkp = wp.media.editor.send.attachment,
			that = jQuery(this),
			ref = that.attr('data-ref');
		
		jQuery('#'+ref).val('');
		//Set an attribute for the image id
		jQuery('#'+ref).attr('data-image_id', '');
		
		jQuery('#'+ref).parents('.rockthemes-mm-element-setting').removeClass('image-added');
		
		//Add Image as reference
		if(jQuery('#'+ref).parent().find('.rockthemes-mm-image-wrapper').length > 0){
			jQuery('#'+ref).parent().find('.rockthemes-mm-image-wrapper').remove();	
		}
	});
	
	
	jQuery(document).on("click", ".icon-modal-button", function(e){
		
		e.preventDefault();
		
		var that = jQuery(this);
		var ref = that.attr("data-icon-ref");
		
		jQuery('#rockthemes_mm_icon_modal').attr('data-return-ref', ref);
		
		jQuery('#rockthemes_mm_icon_modal').modal('show');
	});
	
	
	jQuery(document).on("click", ".save-icon-modal, .icon-holder", function(e){
		var modal = jQuery(this).parents(".modal");
		var ref = modal.attr("data-return-ref");
		
		var chosen = jQuery(this).attr("data-icon-class");
		jQuery("#"+ref).val(chosen);
		if(jQuery("#"+ref).parent().find('.rockthemes-mm-selected-icon').length){
			jQuery("#"+ref).parent().find('.rockthemes-mm-selected-icon').remove();
		}
		jQuery('#'+ref).before('<i class="rockthemes-mm-selected-icon icon-modal-button icomoon '+chosen+'" data-icon-ref="'+ref+'"></i>');
		
		jQuery('#'+ref).parents('.rockthemes-mm-element-setting').addClass('icon-added');
		
		if(jQuery(this).hasClass("icon-holder")){
			modal.find(".close-modal-button").trigger("click");
		}
	});
	
	jQuery(document).on('click', '.rockthemes_mm_icon_delete', function(e){
		e.preventDefault();
		
		var that = jQuery(this),
			ref = that.attr('data-ref');
		
		jQuery('#'+ref).val('');
		//Set an attribute for the image id
		if(jQuery("#"+ref).parent().find('.rockthemes-mm-selected-icon').length){
			jQuery("#"+ref).parent().find('.rockthemes-mm-selected-icon').remove();
		}
		
		jQuery('#'+ref).parents('.rockthemes-mm-element-setting').removeClass('icon-added');
		
		//Add Image as reference
		if(jQuery('#'+ref).parent().find('.rockthemes-mm-image-wrapper').length > 0){
			jQuery('#'+ref).parent().find('.rockthemes-mm-image-wrapper').remove();	
		}
	});
	
	
	jQuery(document).on("click", ".close-modal-button", function(e){
		jQuery(this).parents(".modal").attr('data-return-ref','');
		jQuery(e.target).parents(".modal").modal("hide");
	});
	
	jQuery(document).on("input", ".rock-search-icons", function(){

		var icons_container = jQuery(this).parent().find(".rock-icon-list");
		var that = jQuery(this);
		var val = "";

		setTimeout(function(){
			val = that.val();

			if(val === ""){
				icons_container.find(".rock-choose-icon").removeClass("hide");	
			}else{
				icons_container.find(".rock-choose-icon").addClass("hide");
				
				icons_container.find(".rock-choose-icon").each(function(){
					if(jQuery(this).attr("data-icon-class").toString().indexOf(val) > -1){
						jQuery(this).removeClass("hide");	
					}
				});
			}
		}, 180);
	});
	
	/*
	**	jQuery checkbox (slide)
	*/
	jQuery(document).on('click', '.slider-button', function(){
		if(jQuery(this).hasClass('on')){
			jQuery(this).removeClass('on').html('NO');
		}else{
			jQuery(this).addClass('on').html('YES');
		}
	});	
	
	
	
	//Select with Image Element
	//jQuery('.image-select-list').each(function(){
		jQuery(document).on('click', '.image-select-elem', function(){
			jQuery(this).parent().find('.selected').removeClass('selected');
			jQuery(this).addClass('selected');
		});
	//});
	
	//Select with Image Vertical Element
	jQuery('.image-select-vertical-list').each(function(){
		jQuery(this).find('.image-select-vertical-elem').on('click',function(){
			jQuery(this).parent().find('.selected').removeClass('selected');
			jQuery(this).addClass('selected');
		});
	});
	
});



function rmm_init(){
	'use strict';
	
	if(typeof rockthemes !== 'undefined' && typeof rockthemes.ajaxurl !== 'undefined' && rockthemes.ajaxurl !== ''){
		rmm_log('INIT');
		rmm_log(rockthemes.ajaxurl);
		jQuery.post(rockthemes.ajaxurl, {action:'rockthemes_mm_details_container', rockthemes_mm:'yes', _ajax_nonce:rockthemes.nonce}, function(data){
			if(data && data !== '' && data !== "0" && data !== 'ERROR'){
				/*
				**	Ajax data received and it's not empty
				**
				*/
				rmm_dir(data);
				jQuery('body').append(data);
				jQuery('html').addClass('rockthemes-mega-menu-admin');
				
				
				/*
				**	If the data is correct, now add the overlay buttons
				**	which will trigger the item details
				*/
				rmm_add_menu_trigger_buttons();
				
				/*
				**	Trigger ready event.
				**
				*/
				jQuery(document).trigger('rockthemes_mm:ready');
				rmm_log('MM READY');
				
			}else{
				/*
				**	There is an issue while getting the ajax data
				**
				*/
				rmm_log('DATA ERROR '+data);
				rmm_dir(data);
				rmm_alert('DATA ERROR');	
			}
		});
	}else{
		rmm_alert('ROCKTHEMES IS NOT DEFINED');
	}
}




function rmm_add_menu_trigger_buttons(){
	var btn = rmm_make_trigger_button();
	
	
	jQuery(document).on('mouseenter', '.menu-item', function(e){
		var that = jQuery(this);
		if(that.find('.rockthemes-mm-trigger-button').length < 1){
			that.find('.item-controls').prepend(btn);
		}
	});
	
	
	jQuery(document).on('click touchend', '.rockthemes-mm-trigger-button', function(e){
		e.preventDefault();
		rmm_menu_trigger_button_clicked(jQuery(this));
	});
	
	jQuery(document).on('click touchend', '.rmm-close-icon', function(e){
		e.preventDefault();
		rmm_close_details_area();
	});
	
	
	jQuery(document).on('click', '.rmm-button-save', function(e){

		e.preventDefault();
		
		var that = jQuery(this);
		
		if(that.find('.rmm-save-loading').length){
			return;	
		}

		that.append('<span class="rmm-save-loading"><i class="icomoon icomoon-icon-spinner2 fullSpin"></i></span>');

		rmm_save_details();
	});
	
}


function rmm_make_trigger_button(){
	return '<div class="rockthemes-mm-trigger-button rockthemes-transition"><i class="icomoon icomoon-icon-cog2"></i> '+rockthemes.text.trigger_button+'</div>';
}


function rmm_menu_trigger_button_clicked(that){
	
	/*
	**	If there are datas still loading to display details from ajax, then return
	**
	*/
	if(jQuery('html').hasClass('rmm-details-loading')) return;
	
	/*
	**	If the same element clicked again then close the details area
	**
	*/
	var menu_li = that.parents('.menu-item');
	
	if(menu_li.hasClass('rmm-details-enabled')){
		jQuery('html').removeClass('rmm-details-loading rmm-open');
		menu_li.removeClass('rmm-details-enabled');
		
		if(jQuery('.rmm-save-loading').length){
			jQuery('.rmm-save-loading').remove();	
		}
		
		return;	
	}
	
	/*
	**	If there are other elements which triggered before remove the enabled class
	**
	*/
	if(jQuery('.rmm-details-enabled').length){
		jQuery('.rmm-details-enabled').removeClass('rmm-details-enabled');
	}
	
	if(jQuery('.rockthemes-mm-element').children().length > 0){
		jQuery('.rockthemes-mm-element').children().remove();
	}
	
	
	jQuery('html').addClass('rmm-details-loading rmm-open');
	menu_li.addClass('rmm-details-enabled');
	
	var menu_html_id = menu_li.attr('id'),
		menu_id = parseInt(menu_html_id.replace('menu-item-', ''));
		
	var trigger_data = {
		menu_id:menu_id
	};
	
	var menu_level_arr = menu_li.attr('class').split(' '),
		menu_level = jQuery.grep(menu_level_arr, function(el,i){
			return el.toString().indexOf('menu-item-depth-') > -1;
		}).toString();
	
	rmm_log('MENU LEVEL '+menu_level);
	
	jQuery('#rockthemes_mm_details_container').attr('data-menu_level',menu_level);
	
	
	/*
	**	Send the data
	**
	*/
	jQuery.post(rockthemes.ajaxurl, {action:'rockthemes_mm_get_data', data:trigger_data, rockthemes_mm:'yes', _ajax_nonce:rockthemes.nonce}, function(data){
		
		rmm_dir(data);
		jQuery('html').removeClass('rmm-details-loading');

		if(data){
			rmm_display_details(data, trigger_data);
		}

	});
}


function rmm_display_details(data, trigger_data){
	/*
	**	If details area closed, do not add details.
	*/
	if(!jQuery('html').hasClass('rmm-open')) return;
	
	/*
	**	If there are details, then remove it
	*/
	if(jQuery('.rockthemes-mm-element').children().length){
		rmm_log('DETAILS HAVE CHILD');
		jQuery('.rockthemes-mm-element').children().remove();	
	}
	
	
	jQuery('.rockthemes-mm-element').append('<div class="rockthemes-mm-details">'+data+'</div>');
	//jQuery('.rmm-details-tabs').append(data.tabs);
	
	/*
	**	Add data information related to the menu item for save
	*/
	jQuery('.rockthemes-mm-element').attr('data-menu_id', trigger_data.menu_id);
	
}



function rmm_save_details(){
	
	rmm_log('STARTING SAVE DETAILS');
	
	var details = jQuery('.rockthemes-mm-element'),
		menu_id = details.attr('data-menu_id');
	
	var data = rockthemes.default_settings,
		data_fe = {};
	
	rmm_dir(data);

	for(var i=0; i<data.length; i++){
		for(var t=0; t<data[i]["elements"].length; t++){
			if(data[i]["elements"][t]["is_hidden"] == "true") continue;
			
			
			if(data[i]["elements"][t]["type"] == "text_field" ){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).val();
			}else if(data[i]["elements"][t]["type"] == "icon" ){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).val();
			}else if(data[i]["elements"][t]["type"] == "colorpicker"){
				data[i]["elements"][t]["default"] = rgb2hex(jQuery("#"+data[i]["elements"][t]["id"]).parent().parent().find("a.wp-color-result").css("background-color"));
			}else if(data[i]["elements"][t]["type"] == "select" || data[i]["elements"][t]["type"] == 'sidebar_list' || data[i]["elements"][t]["type"] == 'sticker_list'){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).find(":selected").val();
			}else if(data[i]["elements"][t]["type"] == "select_images"){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).find(".selected").attr("value");
			}else if(data[i]["elements"][t]["type"] == "select_images_vertical"){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).find(".selected").attr("value");
			}else if(data[i]["elements"][t]["type"] == "image"){
								
				data[i]["elements"][t]["default"] = {
					"image_url" : jQuery("#"+data[i]["elements"][t]["id"]).val(),
					"image_id" : jQuery("#"+data[i]["elements"][t]["id"]).attr("data-image_id"),
					"image_align" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .image_align').find(':selected').val(),
					"image_size" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .image_size').find(':selected').val()
				};
								
				//Image Front End data
				data_fe[data[i]["elements"][t]["id"]] = {
					'url': data[i]["elements"][t]["default"]["image_url"],
					'id': data[i]["elements"][t]["default"]["image_id"],
					'image_align': data[i]["elements"][t]["default"]["image_align"],
					'image_size': data[i]["elements"][t]["default"]["image_size"]
				};
				
				rmm_dir(data_fe[data[i]["elements"][t]["id"]], 'IMAGE FRONT END DATA');
				
				/*
				data_fe[data[i]["elements"][t]["id"]+'_url'] = data[i]["elements"][t]["default"];
				data_fe[data[i]["elements"][t]["id"]+'_id'] = data[i]["elements"][t]["image_id"];
				*/
				
			}else if(data[i]["elements"][t]["type"] == "background_image"){
				
				//Background Image
				data[i]["elements"][t]["default"] = {				
					"image_url" : jQuery("#"+data[i]["elements"][t]["id"]).val(),
					"image_id" : jQuery("#"+data[i]["elements"][t]["id"]).attr("data-image_id"),
					"halign" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .image_halign').find(':selected').val(),
					"valign" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .image_valign').find(':selected').val(),
					"image_size" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .image_size').find(':selected').val(),
					"width" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .width').val(),
					"height" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .height').val(),
					"image_repeat" : jQuery("#"+data[i]["elements"][t]["id"]+'_details .image_repeat').find(':selected').val()
				};
				
				rmm_dir(data[i]["elements"][t]["default"], 'BACKEND BACKGROUND IMAGE DATA');
								
				//Image Front End data
				data_fe[data[i]["elements"][t]["id"]] = {
					'url': data[i]["elements"][t]["default"]["image_url"],
					'id': data[i]["elements"][t]["default"]["image_id"],
					'halign': data[i]["elements"][t]["default"]["halign"],
					'valign': data[i]["elements"][t]["default"]["valign"],
					'image_size': data[i]["elements"][t]["default"]["image_size"],
					'width': data[i]["elements"][t]["default"]["width"],
					'height': data[i]["elements"][t]["default"]["height"],
					'image_repeat': data[i]["elements"][t]["default"]["image_repeat"]
				};
				
				rmm_dir(data_fe, 'FRONT END BACKGROUND IMAGE DATA');
				
				/*
				data_fe[data[i]["elements"][t]["id"]+'_url'] = data[i]["elements"][t]["default"];
				data_fe[data[i]["elements"][t]["id"]+'_id'] = data[i]["elements"][t]["image_id"];
				*/
				
			}else if(data[i]["elements"][t]["type"] == "checkbox"){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).find(".slider-button").html();
			}else if(data[i]["elements"][t]["type"] == "socialicons"){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).val();
			}else if(data[i]["elements"][t]["type"] == "text_area"){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).val();
			}else if(data[i]["elements"][t]["type"] == "page_list"){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).find(":selected").val();
			}else if(data[i]["elements"][t]["type"] == 'columns_selector'){
				data[i]["elements"][t]["default"] = jQuery("#"+data[i]["elements"][t]["id"]).find(".selected").length;
			}
			
			if(data[i]["elements"][t]["type"] != "image" && data[i]["elements"][t]["type"] != "background_image"){
				data_fe[data[i]["elements"][t]["id"]] = data[i]["elements"][t]["default"];
			}
			
		}
	}
	
	rmm_dir(data, 'SAVE DATA');
	rmm_dir(data_fe, 'DATA FRONT END');
	
	jQuery.post(rockthemes.ajaxurl, {data:JSON.stringify(data), data_fe:JSON.stringify(data_fe), action:"rockthemes_mm_save_details", menu_id:menu_id, rockthemes_mm:'yes', _ajax_nonce:rockthemes.nonce}, function(response) {
		rmm_dir(response, 'AFTER SAVE DATA');
		
		if(jQuery('.rmm-save-loading').length){
			jQuery('.rmm-save-loading').remove();	
		}
		
		if(response.indexOf('ERROR') < 0){
			jQuery('.rmm-button-save').append('<span class="rmm-saved"><i class="icomoon icomoon-icon-checkmark"></i></span>');	
			setTimeout(function(){jQuery('.rmm-saved').remove();},2000);
		}
		/*
		if(response.indexOf("saved") > -1) {
			on_saving = false;
			save_button.find(".loading-process").html(" Successfully Saved!");
			setTimeout(function(){save_button.find(".loading-process").html("");},2000);
		} else {
			on_saving = false;
			save_button.find(".loading-process").html(" An Error Occured!");
			setTimeout(function(){save_button.find(".loading-process").html("");},2000);
		}
		*/
	});
	//return false;
	
}




function rmm_close_details_area(){
	/*
	**	Delete the details
	*/
	if(jQuery('.rockthemes-mm-element').children().length){
		jQuery('.rockthemes-mm-element').children().remove();
	}
	
	/*
	**	Delete the enabled menu item class
	*/
	if(jQuery('.rmm-details-enabled').length){
		jQuery('.rmm-details-enabled').removeClass('rmm-details-enabled');
	}
	
	/*
	**	If a button is loading, remove it
	*/
	if(jQuery('.rmm-save-loading').length){
		jQuery('.rmm-save-loading').remove();	
	}
	
	/*
	**	Close the panel
	*/
	jQuery('html').removeClass('rmm-details-loading rmm-open');
}





function rmm_log(l){
	if(rockthemes.debug !== 'yes') return;
	console.log('ROCKTHEMES LOG : '+l);	
}
function rmm_dir(d,e){
	if(rockthemes.debug !== 'yes') return;
	if(typeof e !== 'undefined'){
		console.log(e+' : ');
	}else{
		console.log('ROCKTHEMES DIR :');
	}
	console.dir(d);
}
function rmm_alert(a){
	alert(a);	
}




