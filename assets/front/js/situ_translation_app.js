// css
import '../scss/situ_translation_app.scss'

// Load translation lang & show fields
function loadTranslation(langId) {
    $('#create_situ_form_lang').val(langId).trigger('change')
         .parent().find('.select2-selection__rendered').addClass('selection-on')
 
    let dataExist = setInterval(function() {
        if ($('#create_situ_form_event option').length) {
            clearInterval(dataExist)
            $('#event, #categoryLevel1, #categoryLevel2, .card-body').removeClass('d-none')
            $('#loader').hide()
        }
    }, 50);
}

$(function() {
    
    let langId = $('#situ').attr('data-lang')
    loadTranslation(langId)
    
    // Show situItems depending on Situ to translate
    let itemsLength = $('#initialSituItems').attr('data-initial')
    for(var i = 1; i < itemsLength; i++) {
        $('#add-itemSitu-link').trigger('click')
    }
    
})
