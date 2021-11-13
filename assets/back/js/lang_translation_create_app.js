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

    newElem.find('.badge').text(counter)
    removeField(newElem.find('.removeField'))
    newElem.appendTo(collectionHolder)
}

function submitConfirm(button) {
    button.confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
        typeAnimated: true,
        title: translations['validateForm-title'],
        content: '<p class="line-11">'+ translations['validateForm-content'] +'</p>',
        buttons: {
            cancel: {
                text: translations['no'],
                action: function () {}
            },
            confirm: {
                text: translations['yes'],
                btnClass: 'btn-primary',
                action: function () {
                    $('form').submit()
                }
            }
        },
    });
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
    
    // Add count number badge when update
    $('#fields').find('.translationField').each(function(index) {
        $(this).find('.badge').text(index+1)
    })
    
    // Scroll buttons
    $('#scrollBottom').click(function() {
        $('html, body').animate({ scrollTop: $(document).height() });
    })
    $('#scrollTop').click(function() {
        $('html, body').animate({ scrollTop: 0 });
    })
    
    submitConfirm($('#create_translation_form_validate'))
    
});