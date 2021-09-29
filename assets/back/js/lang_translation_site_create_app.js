// css
import '../scss/lang_translation_site_create_app.scss'

// Add pageContent from prototype
let collectionHolder = $('#translationFields')
function addField() {
    let counter = collectionHolder.attr('data-widget-counter')
    let newWidget = collectionHolder.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    collectionHolder.attr('data-widget-counter', counter)

    let newElem = $(collectionHolder.attr('data-widget-pageContents')).html(newWidget)
    removeContent(newElem.find('.removeContent'))
    newElem.appendTo(collectionHolder)
}

// Set status value depending on button clicked
function submissionStatus(buttonId) {
    let statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 3
    $('#translation_form_statusId').val(statusId)
}

$(function() {
    
    //Submission
    $('#save-btn, #submit-btn').click(function(){
        submissionStatus($(this).attr('id'))
        $('form').submit()
    })
    
});