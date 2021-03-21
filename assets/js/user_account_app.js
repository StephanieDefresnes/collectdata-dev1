// css
import '../css/user_account_app.scss';

// js
$(function() {
    
    $('#addTranslationFile .custom-file label').text(translations['file-placeholder'])
    
    /**
     * Modal translation file
     */
    // Replace label with file selected name
    $('.custom-file-input').on('change', function(event) {
        var inputFile = event.currentTarget;
        $(inputFile).parent()
            .find('.custom-file-label')
            .html(inputFile.files[0].name);
    })
    
    // Delete file
    $('.fileDelete').click(function() {
        $('#user_files_form_filename').val($(this).attr('data-filename'))
        $('#modal-lang').text($(this).attr('data-lang'))
        $('#modal-filename').text($(this).attr('data-short-filename'))
    })

    // Empty fields
    $('.cancel-file').click(function() {
        $('#user_files_form_lang,'
            +'#user_files_form_file,'
            +'#user_files_form_filename').val('')
    })
        
})
