// css
import '../scss/event_read_app.scss';

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
            
            // Hide search if only 2 pages
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 20) {
               $('#'+ name +'_search').hide()
            }
            
            // Hide pagination if only one page
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ name +'_pagination .dataTables_paginate').hide()
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

// Search if translations exist
function ajaxEnable(id) {
    $.ajax({
        url: '/'+ path["locale"] +'/back/event/ajaxEnable',
        method: 'POST',
        data: { id: id },
        success: function(data) {
            if (data.success) {
                $('#enable-row').addClass('mt-1').html(translation['yes'])
            } else {
                location.reload();
            }
        }
    })
}

// GG translate
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
    setDatatable($('#dataTableSitus'))

    // Reset search filter
    resetFilter($('#dataTableCategories'))
    resetFilter($('#dataTableSitus'))
    
    
    $('#enableEvent').click(function() {
        ajaxEnable($(this).attr('data-id'))
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