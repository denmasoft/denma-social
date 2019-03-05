//Ultima HTML5 Landing Page v2.1
//Copyright 2014 8Guild.com
//All scripts for Ultima Landing Page version #2

/*Checking if it's touch device we disable some functionality due to inconsistency*/
if (Modernizr.touch) { 
	$('*').removeClass('animated');
}

/*Document Ready*/
$(document).ready(function(e) {
    
         $('#spinner').fadeOut();
        $('#preloader').delay(300).fadeOut('slow');
        setTimeout(function(){$('.first-slide div:first-child').addClass('fadeInDown');},100);
        setTimeout(function(){$('.first-slide div:last-child').addClass('fadeInRight');},100);
        setTimeout(function(){$('.color-switcher').addClass('slideInLeft');},100);
	
	
	/********Responsive Navigation**********/
	$('.navi-toggle').on('click',function(){
		$('.main-navi').toggleClass('open');
	});
	
	$('.main-navi .has-dropdown a i').click(function(){
		$(this).parent().parent().find('.dropdown').toggleClass('expanded');
		return false
	});
	
	/*Tooltips*/
	$('.tooltipped').tooltip();	

});/*/Document ready*/