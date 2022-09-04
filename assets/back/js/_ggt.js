const isoLang = require('../../isoLangs.json')

$(function() {
    
    /** GGTranslate **/
    // -- on load
    let ggtExist = setInterval(function() {
        
        // Wait for GGT container
        if ( $('.goog-te-gadget').length ) {
            
            if ( $('#GGT').find('.goog-te-combo option').length ) {
                
                // Reset menu
                $('#resetGGT').click()
                
                $('#GGT').find('.goog-te-combo option').each(function() {
                    // Translate placeholder
                    if ( '' === $(this).val() ) $(this).text(translations['translate'])
                    
                    else {
                        let lang = $(this).val()
                        // Get first lang name & capitalize
                        let nativeLangName = isoLang[lang].nativeName.split(',')[0]
                        $(this).text(nativeLangName.charAt(0).toUpperCase() + nativeLangName.slice(1))
                    }
                })
                clearInterval(ggtExist)
                $('#translator .ggt-row').removeClass('d-none')
            }
        }
    }, 50)
    
    // -- on change lang
    $('#translator').on('change', 'select', function() {
        
        $('#resetGGT').removeClass('d-none')
        
        if ( $('#translator').hasClass('do-ggt') ) {
            $('#situGGT').removeClass('d-none')
            $('.details').each(function() { $(this).removeClass('d-none') })
            $('#situ-data').addClass('h-adjust')
        }
    })
    
    // -- on click reset button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $(this).addClass('d-none')
        $('#GGT').find('.goog-te-combo option').each(function() {
            // Translate placeholder
            if ( '' === $(this).val() ) $(this).text(translations['translate'])
        })
    })
    
    // -- on click reset button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        
        $(this).addClass('d-none')
        
        $('#GGT').find('.goog-te-combo option').each(function() {
            // Translate placeholder
            if ( '' === $(this).val() ) $(this).text(translations['translate'])
        })
        
        if ( $('#translator').hasClass('do-ggt') ) {
            $('#situGGT').addClass('d-none')
            $('.details').each(function() { $(this).addClass('d-none') })
            $('#situ-data').removeClass('h-adjust')
        }
    })
    
})

