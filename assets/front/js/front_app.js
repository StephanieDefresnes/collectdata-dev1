// css
import '../scss/front_app.scss';

// js
const $ = require('jquery');
window.Popper = require('popper.js');
global.$ = global.jQuery = $;
require('bootstrap');

// js add on
$(document).ready(function(){
    
    $(window).scroll(function(){
        if ($(this).scrollTop() > 10) {
           $('nav.navbar').addClass('scrolled')
        } else {
           $('nav.navbar').removeClass('scrolled')
        }
    })
    
    // UTF8 decode
    $('body').find('.decode').each(function(){
        $(this).html($(this).html($(this).html()).text()).text()
    })
    
    // Bootstrap tooltip
    $('body').find('[data-toggle="tooltip"]').tooltip()
    
    // Flash message display
    if( $('#flash_message').length )
    {
        $('#flash_message').delay(3000).fadeOut();
    }
    
})