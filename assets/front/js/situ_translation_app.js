// css
import '../scss/situ_translation_app.scss'

// Load translation lang & show fields
function loadTranslation(langId) {
    $('#create_situ_form_lang').val(langId).trigger('change')
         .parent().find('.select2-selection__rendered').addClass('selection-on')
    
    $('#translateTo').text($('#select2-create_situ_form_lang-container').html())
 
    let dataExist = setInterval(function() {
        if ($('#create_situ_form_event option').length) {
            $('#categoryLevel1, #categoryLevel2').removeClass('d-none')
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
