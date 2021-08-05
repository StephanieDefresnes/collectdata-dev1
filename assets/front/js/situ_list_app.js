// css
import '../scss/situ_list_app.scss'

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

function selectSitu(id) {
    $.ajax({
        url: "/"+ path['locale'] +"/situ/edit",
        method: 'GET',
        data: { id: id, location: true },
        success: function(data) {
            location.href = data['redirection']['targetUrl'];            
        },
        error: function() {
            $('#flash_message').find('.icon').remove()
            $('#flash_message').find('.alert')
                    .prepend('<span class="icon text-danger">'
                                +'<i class="fas fa-exclamation-circle"></i>'
                            +'</span>')
                    .find('.msg').html(translations['flashError'])
            window.scrollTo({top: 0, behavior: 'smooth'});
            $('#flash_message').show().delay(3000).fadeOut();
        }
    })
}


$(document).ready(function() {
    
    $('#dataTable-situs').dataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-between row mb-2"<"#length.col-md-5"l><"#search.col-auto"f>>'
                +'<"table-responsive"t>'
                +'<"row"<"col-md-6 small"i><"#pagination.col-md-6 mt-2"p>>',
        'columnDefs': [{
            'targets': 'no-sort',
            'orderable': false,
        }],
        'order': [[ 2, 'desc' ]],
        'fnDrawCallback': function(oSettings) {
            // Add class to load reset button search
            $('#dataTable-situs_filter input').addClass('search')
            
            // Hide length select & pagination if only one page
            if ($('#dataTable-situs').dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#length, #pagination .dataTables_paginate').hide()
               $('#search .dataTables_filter').addClass('text-left')
            }
            
            $('#loader').hide()
        },
        
    });
    

    // Reset search filter
    let table = $('#dataTable-situs').DataTable()
    $('#situs').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search"><i class="fas fa-times"></i></span>')
        }
    })
    $('#situs').on('click', '.clean-search', function() {
        table.search('').columns().search('').draw();
        $(this).remove()
    })
    
    
    $('.situUpdate').click(function() {
        $('#loader').show()
        selectSitu($(this).parents('tr').attr('data-id'))
    })
    
    
});