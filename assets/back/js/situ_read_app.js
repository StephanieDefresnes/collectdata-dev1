// css
import '../scss/situ_read_app.scss';

// json
const isoLang = require('../../isoLangs.json')

$(function() {
    
    $('#confirmDelete').confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
        type: 'red',
        typeAnimated: true,
        title: translations['removeTitle'],
        content: translations["removeText"],
        buttons: {
            cancel: {
                text: translations['no'],
            },
            formSubmit: {
                text: translations['yes'],
                btnClass: 'btn-red',
                action: function () {
                    location.href = this.$target.attr('href');
                }
            }
        },
    })
    
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
})
