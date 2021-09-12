// css
import '../scss/user_update_app.scss';
import 'select2/src/scss/core.scss';

require('select2')

// js
$(function() {
    
    // Init select2 to multpiple
    $('.select-multiple').select2({
        placeholder: translations['multiple-search'],
        allowClear: true,
        width: 'resolve'
    })
    
    // Init select2 to single
    $('.select-single').select2({
        minimumResultsForSearch: Infinity,
        width: 'resolve'
    });

    // Remove User lang form Oprional langs
    $('#user_update_form_langs').find('option').each(function(){
        if ($(this).val() == '' 
         || $(this).val() == $('#select_user_langId').find("option:selected").val()) {
            $(this).remove()
        }
    })
    
    // Unselect optional lang if is locale
    function unSelectLocal(newLangName) {
        $('body').find('.select2-selection__choice').each(function(){
            if ($(this).attr('title') == newLangName) $(this).find('button').click()
            $('#select_user_langId').focus()
        })
    }
        
    // Toggle optional langs depending on user locale
    var previousLangName,previousLangID;
    $('#select_user_langId').on('focusin', function () {
        
        // Get locale before change
        previousLangName = $(this).find('option:selected').text()
        previousLangID = $(this).find('option:selected').attr('value');
        
    }).change(function() {
        
        var previousLang = '<option value ="'+previousLangID+'" '
                +'data-id ="'+previousLangID+'">'+previousLangName+'</option>'
        
        // Get locale after change
        var newLangName = $(this).find('option:selected').text()
        var newLangID = $(this).val();
        
        // Set new locale user
        $('#user_update_form_langId').val(newLangID);
        
        if ((previousLangID == 47 && newLangID == '')
         || (previousLangID == '' && newLangID == 47)) {
            // If previous fr or default and new default or fr
            // Remove or unselect 47 from optional langs
            unSelectLocal('français')
            $('#user_update_form_langs option').each(function(){
                if ($(this).attr('data-id') == 47) {
                    $(this).remove()
                }
            })
        } else if ((previousLangID == '' || previousLangID == 47)
                && (newLangID != '' || newLangID != 47)) {
            // If previous fr or default and new not default nor fr
            // Unselect new from optional langs
            // Add 47 and remove new from optional langs
            unSelectLocal(newLangName)
            $('#user_update_form_langs')
                    .prepend('<option value ="47" data-id ="47">français</option>')
                    .find('option').each(function(){
                        if ($(this).attr('data-id') == newLangID) {
                            $(this).remove()
                        }
                    })
        } else if ((previousLangID != '' || previousLangID != 47)
              && (newLangID == '' || newLangID == 47) ){
            // If previous not fr nor default and new default or fr
            // Unselect fr from optional langs
            // Add previous and remove fr from optional langs
            unSelectLocal(newLangName)
            $('#user_update_form_langs').prepend(previousLang)
                    .find('option').each(function(){
                        if ($(this).attr('data-id') == 47) {
                            $(this).remove()
                        }
                    })
        } else {
            // Unselect and remove new from optional langs
            // Add previous into optional langs
            unSelectLocal(newLangName)
            $('#user_update_form_langs').prepend(previousLang)
                    .find('option').each(function(){
                        if ($(this).attr('data-id') == newLangID) {
                            $(this).remove()
                        }
                    })
        }
    });

    // Toggle contributorLangs
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
    })
    
    $('.unupload-img').click(function() {
        $(this).addClass('d-none')
        if (!$(this).hasClass('no-img')) $('.delete-img').removeClass('d-none')
        $('#previewImg').addClass('d-none')
        $('#defaultImg').removeClass('d-none')
        $('#user_update_form_imageFilename').val('')
    })
    
    $('.delete-img').click(function() {
        $(this).addClass('d-none')
        $('.unupload-img, #defaultImg').addClass('d-none')
        $('#previewImg').attr('src', img['default']).removeClass('d-none')
        $('#user_update_form_imageFilename').val('')
    })
    
});