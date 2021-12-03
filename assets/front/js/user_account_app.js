// css
import '../scss/user_account_app.scss';

$(function() {
    
    let footerHeight = $('#footerEnd').height()

    $('#collapse').on('hide.bs.collapse', function () {
        $('#footerEnd').height(footerHeight)
    }).on('show.bs.collapse', function () {
        $('#footerEnd').height(0)
    })
    
})