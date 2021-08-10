// css
import '../scss/lang_translation_form_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

require('jquery-ui');
require('jquery-ui/ui/widgets/sortable');

/**
 * Add Field to collection
 */
// Get the ul that holds the collection of tags
var collectionHolder = $('#fields')

// Add Delete button for each Field added
function addFieldLiDeleteButton(fieldLi) {
    var removeFormButton = 
            $('<button type="button" class="btn btn-outline-danger float-right mt-1">'
                +'<i class="far fa-trash-alt"></i>'
            +'</button><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>')
    fieldLi.prepend(removeFormButton)

    removeFormButton.on('click', function() {
        fieldLi.addClass('to-confirm')
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
                        fieldLi.removeClass('to-confirm')
                    }
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        fieldLi.remove()
                    }
                }
            },
        });
    })
}

/*
 * Create Transaltion
 */
// Add Field from prototype
function addField(button) {
    var list = $(button.attr('data-list-selector'))
    var counter = list.attr('data-widget-counter') || list.children().length
    var newWidget = list.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    list.attr('data-widget-counter', counter)

    var newElem = $(list.attr('data-widget-fields')).html(newWidget)
    addFieldLiDeleteButton(newElem)
    newElem.appendTo(list)
}

/*
 * Update Translation
 */
// Get data Translation
function selectMessage(id, action) {
    $.ajax({
        url: "/"+ translations['locale'] +"/back/translation/edit",
        method: 'GET',
        data: { id: id },
        success: function(data) {
            $('#message_form_name').addClass('disabled')
                    .attr('data-message', data.message[0].id)
                    .attr('data-status', data.message[0].statusId)
                    .val(data.message[0].name)
                    .find('option').each(function() {
                if ($(this).val() == '')
                    $(this).removeAttr('selected').prop('disabled', true)
                else if ($(this).val() == data.message[0].name)
                    $(this).attr('selected', 'selected')
                else $(this).removeAttr('selected').prop('disabled', true)
            })
            loadFieldsToUpdate(data.fields, data.fields.length)
            $('#card-form h6').text(translations['updateTranslation'])
            $('#card-form, #cancel').show()
            $('#card-list, #add-translation').hide()
            $('#add-field').removeClass('d-none')
            $('#form-submit').attr('data-action', action).removeClass('d-none')
        }
    })
}

// Load Current fields from prototype
function loadFieldsToUpdate(data, counter) {
    var list = $('#fields')
    for (var i = 0; i < counter; i++) {
        var newWidget = list.attr('data-prototype')
        newWidget = newWidget.replace(/__name__/g, i)
        var newElem = $(list.attr('data-widget-fields')).html(newWidget)
        addFieldLiDeleteButton(newElem)
        loadFieldsValue(data, newElem, i)
        newElem.appendTo(list)
    }
    list.attr('data-widget-counter', counter)
}

// Load Values fields
function loadFieldsValue(data, newElem, i) {
    $(newElem).find('input').val(data[i].name)
    $(newElem).find('select option').each(function() {
        if ($(this).val() == data[i].type) $(this).prop('selected', true)
    })
}

// Set data Transaltion
function updateTranslation(messageId, statusId, dataForm) {    
    $.ajax({
        url: "/"+ translations['locale'] +"/back/translation/updateTranslation",
        method: 'POST',
        data: { id: messageId, statusId: statusId, data: dataForm },
        success: function() {
            // To reload page to the top
            var pathname = window.location.pathname;
            window.location.replace(pathname)
        }
    })
}
    
/**
 * Load status on submission
 */
// Set status value depending on button clicked
function submissionStatus(buttonId) {
    var statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 2
    $('#message_form_statusId').val(statusId)
}

$(function() {
    
    /**
     * Translations list
     */
    // Datatables configuration
    var table = $('#dataTable-translations').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-between row mb-2"<"#length.col-md-5"l><"#search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"row"<"#info.col-md-6 small"i><"#pagination.col-md-6 mt-3"p>>',
        "columnDefs": [{
            orderable: false,
            targets: 0
        }],
        "order": [[ 1, 'asc' ]],
        "fnDrawCallback": function(oSettings) {
            $('#dataTable-translations_filter input').addClass('search')
            $('#loader').hide()
        }
    })
    
    // Hide length select & pagination if only one page
    if (table.data().count() <= 10) {
       $('#length, #pagination .dataTables_paginate').hide()
       $('#search .dataTables_filter').addClass('text-left')
    }

    // Reset search filter
    $('#langs-list').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search"><i class="fas fa-times"></i></span>')
        }
    })
    $('#langs-list').on('click', '.clean-search', function() {
        table.search('').columns().search('').draw();
        $(this).remove()
    })
    
    // Add new Translation
    $('#add-translation').click(function() {
        $('#card-form, #cancel').show()
        $('#card-list, #add-translation').hide()
    })
    
    // Load data Translation from table
    $('.selectMessage').click(function (evt) {
        evt.stopPropagation()
        selectMessage($(this).data('id'), $(this).data('action'))
    })
    
    /**
     * Translation form
     */
    // Cancel Translation creation or update
    $('#cancel').click(function() {
        $('#card-form h6').text(translations['createTranslation'])
        $('#card-form, #cancel').hide()
        $('#card-list, #add-translation').show()
        $('#message_form_name').removeClass('disabled').val('')
                .find('option').each(function() {
            if ($(this).val() == '')
                $(this).attr('selected', 'selected').prop('disabled', false)
            else
                $(this).removeAttr('selected').prop('disabled', false)
        })
        $('#fields').empty()
        $('#add-field').addClass('d-none')
        $('#form-submit').attr('data-action', '').addClass('d-none')
        $('#message_form_name').attr('data-message', '')
        $('#message_form_name').attr('data-status', '')
    })
    
    // Add new field into collection then show fields and buttons
    // & Placeholder disabled
    $('#message_form_name').change(function() {
        if ($(this).val() != '' && $('#add-field').hasClass('d-none'))
            $('#add-field').removeClass('d-none')
    }).find('option').each(function() {
        if ($(this).val() == '') $(this).prop('disabled', true)
    })
    
    // Init jQuery ui sortable on fields list item
    $('#fields').sortable();
    
    // Add new field into collection & show lirs fields and submit buttons
    $('#add-field-link').click(function() {
        addField($(this))
        if ($('#form-fields').hasClass('d-none') && $('#form-submit').hasClass('d-none'))
            $('#form-fields').removeClass('d-none')
            $('#form-submit').removeClass('d-none')
        if ($('#add-error').hasClass('d-none')) $('#add-error').addClass('d-none')
    })
    
    /*
     * Submission */
    $('#save-btn, #submit-btn').click(function(){  
        
        var messageId = $('#message_form_name').attr('data-message')
        var statusId = $('#message_form_name').attr('data-status')
        var action = $('#form-submit').attr('data-action')

        if ($('#fields li').length == 0 && $('#add-error').hasClass('d-none')) {
            $('#add-error').removeClass('d-none')
        } else {
            
            // Load statusId
            submissionStatus($(this).attr('id'))
            
            // Create Translation
            //  -- if status is validated (3) clone TranslationMessage
            if ( (messageId == '' && statusId == '') || statusId == 3
               || action == 'clone') {
                $('#submit').click()
            }            
            // Or Update Translation
            else {
                var dataForm = []
                $('#fields li').each(function(index) {
                    var name,type;
                    $(this).find('.form-control').each(function() {
                        if($(this).is('input')) {
                            name = $(this).val()
                        } else {
                            type = $(this).val()
                        }
                    })
                    dataForm.push({'name': name, 'type': type})
                })
                // Update Translation in ajax 
                updateTranslation(messageId, statusId, dataForm)
            }
            
        }
    })
    
});