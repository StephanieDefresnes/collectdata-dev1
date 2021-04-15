// css
import '../scss/front_app.scss';

// js
const $ = require('jquery');
window.Popper = require('popper.js');
global.$ = global.jQuery = $;
require('bootstrap');

// js add on
$(function() {
    
    if( $('#flash_message').length )
    {
        $('#flash_message').delay(3000).fadeOut();
    }
    
    // Init Bootstrap tooltip
    $('[data-toggle="tooltip"]').tooltip()
    
    // Decode UTF8
    $('body').find('.decode').each(function(){
        $(this).html($(this).html($(this).html()).text()).text()
    })
    
});