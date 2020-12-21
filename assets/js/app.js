// css
import '../css/app.scss';

// js add on
$(function() {
    
    if( $('#flash_message').length )
    {
        $('#flash_message').delay(3000).fadeOut();
    }
    
});