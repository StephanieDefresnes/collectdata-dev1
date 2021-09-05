$(function() {
    $('.switch-radio[type="radio"]').click(function() {
        $(this).parent().find('label.disabled')
                .css({'padding': '0', 'background': '#4e73df', 'width': 0, 'transition': '250ms'})
        $(this).parent().css({'width': '74px', 'margin': '0 3px', 'transition': '250ms'})
    })
})
