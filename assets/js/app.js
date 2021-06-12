// css
import '../scss/app.scss';

// js
$(function() {
    
    // UTF8 decode
    $('body').find('.decode').each(function(){
        $(this).html($(this).html($(this).html()).text()).text()
    })
    
    // Bootstrap tooltip
    $('body').find('.tooltip-on').tooltip()
})