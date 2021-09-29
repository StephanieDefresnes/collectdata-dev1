// css
import '../scss/lang_translation_site_app.scss';

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
        dom: '<"d-flex justify-content-between row mb-2"<"#'+ name +'_length.col-md-5"l><"#'+ name +'_search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"row"<"#'+ name +'_info.col-md-6 small"i><"#'+ name +'_pagination.col-md-6 mt-3"p>>',
        "columnDefs": [{
            orderable: false,
            targets: 0
        }],
        "order": [[ 1, 'asc' ]],
        "fnDrawCallback": function(oSettings) {            
            // Add class to load reset button search
            $('#'+ name +'_filter input').addClass('search')
            
            // Hide length select & pagination if only one page
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ name +'_length, #'+ name +'_pagination .dataTables_paginate').hide()
               $('#'+ name +'_search .dataTables_filter').addClass('text-left')
            }
            
            $('#loader').hide()
        }
    })
}

// Reset search filter
function resetFilter(table) {  
    let name = table.attr('id')
    let oTable = table.DataTable()
    
    $('#'+ name + '-list').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search small pointer"><i class="fas fa-times"></i></span>')
        }
    })
    $('#'+ name + '-list').on('click', '.clean-search', function() {
        oTable.search('').columns().search('').draw();
        $(this).remove()
    })
}

$(function() {
    
    if ($('#siteEmpty').length == 1 && $('#contributorEmpty').length == 1)
        $('#loader').hide()
    
    // Datatables configuration
    setDatatable($('#dataTable-translationsSite'))
    setDatatable($('#dataTable-translationsContributor'))

    // Reset search filter
    resetFilter($('#dataTable-translationsSite'))
    resetFilter($('#dataTable-translationsContributor'))
    
    // Modal
    $('#createTranslation').click(function() {
        let referentId = $('#siteEnvironnement').val()
        let langId = $('#siteLang').val()
        location.href = '/'+ path["locale"] +'/back/translation/'+ referentId + '/' + langId
    })
    
});