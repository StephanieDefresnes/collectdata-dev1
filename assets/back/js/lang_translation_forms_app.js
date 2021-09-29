// css
import '../scss/lang_translation_forms_app.scss';

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
//    addFieldLiDeleteButton(newElem)
    newElem.appendTo(list)
}
    
/**
 * Load status on submission
 */
// Set status value depending on button clicked
function submissionStatus(buttonId) {
    var statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 2
    $('#translation_form_statusId').val(statusId)
}

$(function() {
    
    if ($('#dataTable-translations tbody tr').length == 0) $('#loader').hide()
    
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
    
});