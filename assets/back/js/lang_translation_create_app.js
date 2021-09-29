// css
import '../scss/lang_translation_create_app.scss';

require('jquery-ui');
require('jquery-ui/ui/widgets/sortable');

// Remove Field
function removeField(button) {
    let fieldDiv = button.parents('.translationField')
    button.on('click', function() {
        fieldDiv.addClass('to-confirm')
        $.confirm({
            animation: 'scale',
            closeAnimation: 'scale',
            animateFromElement: false,
            columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
            type: 'red',
            typeAnimated: true,
            title: translations['deleteField-title'],
            content: translations['deleteField-content'],
            buttons: {
                cancel: {
                    text: translations['no'],
                    action: function () {
                        fieldDiv.removeClass('to-confirm')
                    }
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        fieldDiv.remove()
                    }
                }
            },
        });
    })
}

// Add Field from prototype
function addField() {
    let collectionHolder = $('#fields')
    let counter = collectionHolder.attr('data-widget-counter') || collectionHolder.children().length
    let newWidget = collectionHolder.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    collectionHolder.attr('data-widget-counter', counter)
    let newElem = $(collectionHolder.attr('data-widget-fields')).html(newWidget)

    removeField(newElem.find('.removeField'))
    newElem.appendTo(collectionHolder)
}

// Set status value depending on button clicked
function submissionStatus(buttonId) {
    let statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 3
    $('#translation_form_statusId').val(statusId)
}

$(function() {
    
    // Init jQuery ui sortable on fields list item
    $('#fields').sortable();
    
    if ($('.translationField').length >= 1 && $('#form-submit').hasClass('d-none'))
        $('#form-submit').removeClass('d-none')
    if ($('#card-form').attr('data-id') != '')
        $('#translation_form_name').addClass('disabled').find('option').each(function() {
            if(!$(this).is(':selected'))
                $(this).prop('disabled', true)
        })
    
    // Add new field into collection & show lirs fields and submit buttons
    $('#add-field-link').click(function() {
        addField()
        if ( $('#form-submit').hasClass('d-none')) $('#form-submit').removeClass('d-none')
        if ($('#add-error').hasClass('d-none')) $('#add-error').addClass('d-none')
    })
    
    // Remove
    $('.removeField').each(function() {
        removeField($(this))
    })
    
    //Submission
    $('#save-btn, #submit-btn').click(function(){
        $('#loader').show()
        submissionStatus($(this).attr('id'))
        $('form').submit()
    })
    
});