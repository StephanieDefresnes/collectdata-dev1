// css
import '../scss/category_event_read_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

// Datatables configuration
function setDatatable(table) {
    let name = table.attr('id')
    
    table.DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-start row"<"#'+ name +'_search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"d-flex justify-content-end"<"#'+ name +'_pagination.col-md-6 mt-1"p>>',
        'columnDefs': [{
            targets: 'no-sort',
            orderable: false,
        }],
        'fnDrawCallback': function(oSettings) {            
            // Add class to load reset button search
            $('#'+ name +'_filter input').addClass('search')
            
            // Hide pagination & search if only one page
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ name +'_pagination .dataTables_paginate').hide()
            }
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ name +'_search').hide()
            }
        }
    })
}

// Reset search filter
function resetFilter(table) {  
    let oTable = table.DataTable()
    let name = table.parents('.back-dataTable').attr('id')
    
    $('#'+ name).on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search small pointer"><i class="fas fa-times"></i></span>')
        }
    })
    $('#'+ name).on('click', '.clean-search', function() {
        oTable.search('').columns().search('').draw();
        $(this).remove()
    })
}

// Enable event
function ajaxEnable(id, button) {
    let url = button.hasClass('enableEvent')
                ? '/back/ajaxEventEnable' : '/back/ajaxCategoryEnable'
    $.ajax({
        url: url,
        method: 'POST',
        data: { id: id },
        success: function(data) {
            if (data.success) {
                $('#enable-row').addClass('mt-1').html(translation['yes'])
            } else {
                location.reload();
            }
            if ($('#enableData').hasClass('d-none'))
                $('#enableData').removeClass('d-none')
            $('#enable-row > .spinner-border').addClass('d-none')
        }
    })
}

// GG translate (hidden if locale == category or event lang)
function resetGGT() {
    let dataExist = setInterval(function() {
        if ($('iframe').length) {
           $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
           clearInterval(dataExist)
        }
    }, 50);
}

$(function() {
    
    // Datatables configuration
    setDatatable($('#dataTableCategories'))
    if ($('#situs').length == 1) setDatatable($('#dataTableSitus'))

    // Reset search filter
    resetFilter($('#dataTableCategories'))
    if ($('#situs').length == 1) resetFilter($('#dataTableSitus'))
    
    
    $('#enableData').click(function() {
        $(this).addClass('d-none')
        $('#enable-row > .spinner-border').removeClass('d-none')
        ajaxEnable($(this).attr('data-id'), $(this))
    })
    
    
    /**
     * Reset GGT
     */
    // -- on load
    setTimeout(resetGGT, 2000)
    
    // -- on click button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $(this).addClass('d-none')
    })
    
    // Lang change
    $('#translator').on('change', 'select', function() {
        $('#resetGGT').removeClass('d-none')
    })
    
});