// css
import '../scss/lang_translation_verify_app.scss';

function resetGGT() {
    let dataExist = setInterval(function() {
        if ($('iframe').length) {
           $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
           clearInterval(dataExist)
        }
    }, 50);
}

$(document).ready(function(){
    
    /** GGTranslate **/
    // Reset GGT
    // -- on load
    setTimeout(resetGGT, 2000)
    
    // -- on click button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $('#resetGGT, #situGGT').addClass('d-none')
        $('.details').each(function() { $(this).addClass('d-none') })
        $('#situ-data').removeClass('h-adjust')
    })
    
    // Lang selected
    $('#translator').on('change', 'select', function() {
        $('#resetGGT, #situGGT').removeClass('d-none')
        $('.details').each(function() { $(this).removeClass('d-none') })
        $('#situ-data').addClass('h-adjust')
    })
    
    
    $('.translationField').click(function() {
        if ($(this).find('.form-check-input').prop('checked') == true) {
            $(this).find('.form-check-input').prop('checked', false)
            $(this).find('.pointer').removeClass('border-danger alert alert-danger')
        } else {
            $(this).find('.form-check-input').prop('checked', true)
                $(this).find('.pointer').addClass('border-danger alert alert-danger')
        }
    })
    
    
    
})