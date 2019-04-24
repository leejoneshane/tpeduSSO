
jQuery(document).ready(function() {
	
    /*
        Fullscreen background
    */
    $.backstretch([
                    "/img/backgrounds/1.jpg"
	              , "/img/backgrounds/2.jpg"
	              , "/img/backgrounds/3.jpg"
	              , "/img/backgrounds/4.jpg"
	              , "/img/backgrounds/5.jpg"
	              , "/img/backgrounds/6.jpg"
				 ], {duration: 5000, fade: 750});
	$('.backstretch img').attr('alt', 'this is a background image');  
    /*
        Form validation
    */
    $('.login-form input[type="text"], .login-form input[type="password"], .login-form textarea').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    $('.login-form').on('submit', function(e) {
    	
    	$(this).find('input[type="text"], input[type="password"], textarea').each(function(){
    		if( $(this).val() == "" ) {
    			e.preventDefault();
    			$(this).addClass('input-error');
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    	
    });
    
    
});
