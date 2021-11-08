// css
import '../scss/user_update_app.scss';
import 'select2/src/scss/core.scss';

require('select2')

function setMultiSelect(select) {
    select.select2({
        placeholder: translations['multiple-search'],
        allowClear: true,
        width: 'resolve'
    })
}

function removeLangtoOptions(value) {
    $('#user_update_form_langs').find('option').each(function(){
        if ($(this).val() == value) $(this).remove()
    })
}
    
// Replace new current lang by old into optional langs
function unSelectLocal(previousValue, newValue) {
    // Get enabled langs
    $.ajax({
        url: '/'+ path["locale"] +'/ajaxLangEnabled',
        method: 'POST',
        success: function(data) {

            // Destroy langs select2
            $('#user_update_form_langs').select2('destroy')

            // Remove new current lang into langs choices
            removeLangtoOptions(newValue)

            // Add previous current lang into langs choices
            let langs = data['langs']
            for (var i = 0; i < langs.length; i++) {
                if (langs[i]['id'] == previousValue) {
                    let option = '<option value="'+ langs[i]['id'] +'">'+ langs[i]['name'] +'</option>'
                    $('#user_update_form_langs').append(option)
                }
            }

            // Set langs select2
            setMultiSelect($('#user_update_form_langs'))
        }
    })
}

// js
$(function() {
    
    // Init select2 to multiple
    $.when(setMultiSelect($('.select-multiple'))).done(function() {
        $('#loader').hide()
    })
    
    // Init select2 to single
    $('.select-single').select2({
        minimumResultsForSearch: Infinity,
        width: 'resolve'
    });
    
    // Init select2 to single
    $('.single-search').select2({
        width: 'resolve'
    });

    // Remove User current lang form optional langs
    removeLangtoOptions($('#user_update_form_lang').val())
        
    /*
     * Toggle optional langs depending on user locale
     */
    // Save value before change
    $('#select2-user_update_form_lang-container').on('click', function () {
        $('#user_update_form_lang').attr('data-val',$('#user_update_form_lang').val())
    })
    
    $('#user_update_form_lang').change(function() {
        let newLangName = $('#select2-user_update_form_lang-container').attr('title')
        
        $('#select2-user_update_form_langs-container').find('li').each(function(){
            if ($(this).attr('title') == newLangName) $(this).find('button').click()
        })
        unSelectLocal($(this).attr('data-val'),$(this).val())
        $('#user_update_form_langs').select2('close')
        $(this).blur()
    });

    /*
     * Toggle contributorLangs
     */
    if ($('#user_update_form_langContributor').is(':checked')) {
        $('#contributorLangs').removeClass('d-none').css('opacity', 1)
    } else {
        $('#contributorLangs').addClass('d-none').css('opacity', 0)
    }
    $('#user_update_form_langContributor').bind('click', function() {
        if ($(this).is(':checked') && $('#contributorLangs').hasClass('d-none')) {
            $('#contributorLangs').removeClass('d-none').animate({opacity: 1},250)
        } else {
            $('#contributorLangs').addClass('d-none').animate({opacity: 0})
        }  
    })
    
    /*
     * Avatar
     */
    // Add avatar
    $('.img-circle').click(function() {
        $('.custom-file-input').click()
    })

    // Avatar preview manager
    $('.custom-file-input').on('change', function() {
        var file = $('input[type=file]').get(0).files[0]
        if(file){
            var reader = new FileReader()
            reader.onload = function(){
                $('#previewImg').attr('src', reader.result).removeClass('d-none')
            }
            reader.readAsDataURL(file)
            $('#defaultImg, .delete-img').addClass('d-none')
            $('.unupload-img').removeClass('d-none')
        }
        $('#removeImg').val('')
    })
    
    // Change avatar, toggle anonymous/file user   
    $('.unupload-img').click(function() {
        $(this).addClass('d-none')
        if (!$(this).hasClass('no-img')) $('.delete-img').removeClass('d-none')
        $('#previewImg').addClass('d-none')
        $('#defaultImg').removeClass('d-none')
        $('#user_update_form_imageFilename').val('')
        $('#removeImg').val('')
    })    
    $('.delete-img').click(function() {
        $(this).addClass('d-none')
        $('.unupload-img, #defaultImg').addClass('d-none')
        $('#previewImg').attr('src', img['default']).removeClass('d-none')
        $('#removeImg').val(true)
    })
    
    /*
     * Update select2 style on choice country value
     */
    if ($('#user_update_form_country').val() == '')
        $('#select2-user_update_form_country-container').addClass('empty')
    
    $('#user_update_form_country').change(function() {
        if ($('#user_update_form_country').val() != ''
                && $('#select2-user_update_form_country-container').hasClass('empty'))
            $('#select2-user_update_form_country-container').removeClass('empty')
        else $('#select2-user_update_form_country-container').addClass('empty')
    }) 

    
});