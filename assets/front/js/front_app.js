// css
import '../scss/front_app.scss';

// js
const $ = require('jquery');
window.Popper = require('popper.js');
global.$ = global.jQuery = $;
require('bootstrap');

// js add on
$(document).ready(function(){
    
    // Navbar scroll effect
    $(window).scroll(function(){
        if ($(this).scrollTop() > 10) {
           $('nav.navbar').addClass('scrolled')
        } else {
           $('nav.navbar').removeClass('scrolled')
        }
    })
    $('.navbar-toggler').click(function() {
        if ($('.navbar-toggler').attr('aria-expanded') == 'false') {
            $('#navbarCollapse').toggleClass('toggler-expanded')
        }
    })
    
    // Bootstrap tooltip
    $('body').find('[data-toggle="tooltip"]').tooltip()
    
    // Flash message display
    $('body').find('#hideFlash').click(function() {
        $('#flash_message').fadeOut();
    })
    
})