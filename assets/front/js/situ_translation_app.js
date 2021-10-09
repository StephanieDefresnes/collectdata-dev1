// css
import '../scss/situ_translation_app.scss'

// Load translation lang & show fields
function loadTranslation(langId) {
    $('#situ_form_lang').val(langId).trigger('change')
         .parent().find('.select2-selection__rendered').addClass('selection-on')
 
    let dataExist = setInterval(function() {
        if ($('#situ_form_event option').length) {
            $('#loader').removeClass('d-block')
            clearInterval(dataExist)
        }
    }, 50);
}

$(function() {
    
    loadTranslation($('#situ').attr('data-lang'))
    
    $('#details').find('.infoCollapse').each(function() {
        $(this).addClass('d-none')
    })
    
    // Show situItems depending on Situ to translate
    let itemsLength = $('#initialSituItems').attr('data-initial')
    for(var i = 1; i < itemsLength; i++) {
        $('#add-itemSitu-link').trigger('click')
    }
    
})
