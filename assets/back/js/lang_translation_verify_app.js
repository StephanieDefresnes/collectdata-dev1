// css
import '../scss/lang_translation_verify_app.scss';

// json
const isoLang = require('../../isoLangs.json')

$(function() {
    
    /** GGTranslate **/
    // -- on load
    let ggtExist = setInterval(function() {
        // Wait for GGT container
        if ($('#\\:1\\.container').length) {
            
            // Reset menu
            $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
            
            if ($('#GGT').find('.goog-te-combo option').length) {
                $('#GGT').find('.goog-te-combo option').each(function() {
                    // Translate placeholder
                    if ($(this).val() == '') $(this).text(translations['translate'])
                    else {
                        let lang = $(this).val()
                        // Get first lang name & capitalize
                        let nativeLangName = isoLang[lang].nativeName.split(',')[0]
                        $(this).text(nativeLangName.charAt(0).toUpperCase() + nativeLangName.slice(1))
                    }
                })
                clearInterval(ggtExist)
            }
        }
    }, 50);
    
    // -- on change lang
    $('#translator').on('change', 'select', function() {
        $('#resetGGT').removeClass('d-none')
    })
    
    // -- on click reset button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $(this).addClass('d-none')
        $('#GGT').find('.goog-te-combo option').each(function() {
            // Translate placeholder
            if ($(this).val() == '') $(this).text(translations['translate'])
        })
    })
    
    /** **/
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