// css
import '../scss/back_app.scss';

// js
const $ = require('jquery');
global.$ = global.jQuery = $;
require('startbootstrap-sb-admin-2/vendor/bootstrap/js/bootstrap.bundle.min.js');
require('startbootstrap-sb-admin-2/vendor/jquery-easing/jquery.easing.min.js');
require('startbootstrap-sb-admin-2/js/sb-admin-2.min.js');
    
$(document).ready(function(){
    
    // Bootstrap tooltip
    $('body').find('[data-toggle="tooltip"]').tooltip({trigger: 'hover'})
    
    // Flash message display
    $('body').find('#hideFlash').click(function() {
        $('#flash_message').fadeOut();
    })
    
    // Hide responsive Sidebar collapse menu on width < 768
    let sidebar = $('#accordionSidebar');
    if ($(window).width() < 768) sidebar.addClass('toggled')

    $(window).resize(function () {
        if ($(window).width() < 768) sidebar.addClass('toggled')
    });
    
})