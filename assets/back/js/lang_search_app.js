// css
import '../scss/lang_search_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

$(document).ready(function(){
    
    // Datatables configuration
    var table = $('#dataTable-langsList').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-between row mb-2"<"#length.col-md-5"l><"#search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"row"<"#info.col-md-6 small"i><"#pagination.col-md-6 mt-3"p>>',
        "fnDrawCallback": function(oSettings) {
            $('#dataTable-langsList_filter input').addClass('search')
        }
    })

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
})