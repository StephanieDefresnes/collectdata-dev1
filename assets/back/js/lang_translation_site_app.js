// css
import '../scss/lang_translation_site_app.scss';


// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

$(function() {
    
    if ($('#dataTable-list tbody tr').length == 0) $('#loader').hide()
    
    /**
     * Translations list
     */
    // Datatables configuration
    var table = $('#dataTable-list').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-between row mb-2"<"#length.col-md-5"l><"#search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"row"<"#info.col-md-6 small"i><"#pagination.col-md-6 mt-3"p>>',
        "columnDefs": [{
            orderable: false,
            targets: 'no-sort'
        }],
        "fnDrawCallback": function(oSettings) {
            $('#dataTable-list_filter input').addClass('search')
            
            // Hide length select & pagination if only one page
            if ($('#dataTable-list').dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#length, #pagination .dataTables_paginate').hide()
               $('#search .dataTables_filter').addClass('text-left')
            
            }
            $('#loader').hide()
        },
        initComplete: function () {
            this.api().columns([2]).every( function () {
                var column = this;
                let selectContent = '<select class="form-control form-control-sm">'
                                    +'<option value="">'+ translations['select'] +'</option></select>'
                var select = $(selectContent)
                    .appendTo( $('#filter').empty() )
                    .on('change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
 
                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );
 
                column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option class="text-uppercase" value="'+d+'">'+d+'</option>' )
                } );
            } );
        }
    })

    // Reset search filter
    $('#list').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search"><i class="fas fa-times"></i></span>')
        }
    })
    $('#list').on('click', '.clean-search', function() {
        table.search('').columns().search('').draw();
        $(this).remove()
    })
    
    // Modal
    $('#createTranslation').click(function() {
        let referentId = $('#siteEnvironnement').val()
        let langId = $('#siteLang').val()
        location.href = '/'+ path["locale"] +'/back/translation/create/'+ referentId + '/' + langId
    })
    
});