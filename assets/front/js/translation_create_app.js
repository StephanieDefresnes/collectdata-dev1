// css
import '../scss/translation_create_app.scss'

// Set status value depending on button clicked
function submissionStatus(buttonId) {
    let statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 2
    $('#translation_form_statusId').val(statusId)
}

$(function() {
    
    //Submission
    $('#save-btn, #submit-btn').click(function(){
        submissionStatus($(this).attr('id'))
        $('form').submit()
    })
    
});