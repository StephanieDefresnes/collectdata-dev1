// css
import '../scss/back_app.scss';

// js
const $ = require('jquery');
global.$ = global.jQuery = $;
require('startbootstrap-sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js');
require('startbootstrap-sb-admin-2/vendor/jquery-easing/jquery.easing.min.js');
require('startbootstrap-sb-admin-2/js/sb-admin-2.min.js');
    
$(document).ready(function(){
    
    // Responsive Navbar collapse menu
    if ($(window).width() < 768) $('.sidebar .collapse').removeClass('show');

    $(window).resize(function () {
        if ($(window).width() >= 768) $('.sidebar .nav-item.active .collapse').collapse('show');
    });
        
    // UTF8 decode
    $('body').find('.decode').each(function(){
        $(this).html($(this).html($(this).html()).text()).text()
    })
    
    // Bootstrap tooltip
    $('body').find('[data-toggle="tooltip"]').tooltip()
    
    // Flash message display
    if( $('#flash_message').length )
    {
        $('#flash_message').delay(3000).fadeOut()
    }
    
})