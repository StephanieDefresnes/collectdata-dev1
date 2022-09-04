// css
import '../scss/page_edit_app.scss';
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

require('select2')

/**
 * Add pageContent collection
 */
let collectionHolder = $('#pageContents')

// Add pageContent from prototype
function addContent() {
    let counter = collectionHolder.attr('data-widget-counter') || collectionHolder.children().length
    let newWidget = collectionHolder.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    collectionHolder.attr('data-widget-counter', counter)

    let newElem = $(collectionHolder.attr('data-widget-pageContents')).html(newWidget)
    removeContent(newElem.find('.removeContent'))
    newElem.appendTo(collectionHolder)
}

// Delete pageContent with confirm alert
function removeContent(button) {
    let divContent = button.parents('.divContent')
    
    button.on('click', function() {
        divContent.addClass('to-confirm')
        $.confirm({
            animation: 'scale',
            closeAnimation: 'scale',
            animateFromElement: false,
            columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
            type: 'red',
            typeAnimated: true,
            title: translations['deletePage-title'],
            content: translations['deletePage-content'],
            buttons: {
                cancel: {
                    text: translations['no'],
                    action: function () {
                        divContent.removeClass('to-confirm')
                    }
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        divContent.remove()
                    }
                }
            },
        })
    })
}

$(function() {
    
    // Init select2
    $('#page_form_type').select2({
        minimumResultsForSearch: Infinity,
        width: 'resolve'
    });
    $('#page_form_lang').select2({
        width: 'resolve'
    });
    
    // Add new Content to collection
    $('#add-content-link').click(function() {
        addContent()
    })
    
    // Remove Content from existing collection (when update Page)
    $('.removeContent').each(function() {
        removeContent($(this))
    })
    
    $('select').change(function() {
        if ( $(this).parent().find('.select2-selection__rendered').hasClass('selection-on') 
                && '' === $(this).val() )
        {
            $(this).parent().find('.select2-selection__rendered').removeClass('selection-on')
            return
        }
        $(this).parent().find('.select2-selection__rendered').addClass('selection-on')
    })
    
    if ( $('#page_form_save').hasClass('d-none') ) {
        $('.card-footer .form-inline').removeClass('border-left ml-lg-4 ml-0 pl-lg-3 pl-2')
    }
    
})