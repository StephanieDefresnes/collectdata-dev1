// css
import '../scss/situ_list_app.scss'

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

function flashMessage(status) {
    $('body').find('#flash_message').remove()
    let i = status == 'success' ? '<i class="fas fa-check-circle"></i>'
                                : '<i class="fas fa-exclamation-circle"></i>'
    let textClass = status == 'success' ? 'text-success' : 'text-danger'
    let flashMessage =
            '<div id="flash_message" class="container">'
                +'<div class="alert alert-secondary alert-dismissible px-3 fade show" role="alert">'
                        +'<span class="sr-only">'+ translations['srOnly-'+status] +'</span>'
                        +'<span class="icon '+ textClass +'">'+ i +'</span>'
                        +'<span class="msg">'+ translations['flashValid-'+status] +'</span>'
                +'</div>'
            +'</div>'
    $('body > .container-fluid').before(flashMessage)
    window.scrollTo({top: 0, behavior: 'smooth'});
    $('#flash_message').delay(3000).fadeOut(); 
}

function selectSitu(id) {
    $.ajax({
        url: "/"+ path['locale'] +"/situ/edit",
        method: 'GET',
        data: { id: id, location: true },
        success: function(data) {
            location.href = data['redirection']['targetUrl'];            
        },
        error: function() {
            flashMessage('error')
        }
    })
}

function requestReceived(button) {
    button.removeClass('pcx-2').addClass('px-1')
            .attr('data-original-title', translations['actionRead'])
            .html('<i class="fas fa-eye"></i>')
            .parents('tr').find('.situStatus').text(translations['statusValidation'])
}

function validationRequest(id, button) {
    $.ajax({
        url: "/"+ path['locale'] +"/ajaxValidationRequest",
        method: 'GET',
        data: { id: id },
        success: function() {            
            requestReceived(button)
            flashMessage('success')
            $('#loader').hide()
        },
        error: function() {
            flashMessage('error')
            $('#loader').hide()
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
    
    // Update situ
    $('.situUpdate').click(function() {
        $('#loader').show()
        selectSitu($(this).parents('tr').attr('data-id'))
    })
    
    // Validation request
    $('.situValidation').click(function() {
        $('#loader').show()
        validationRequest($(this).parents('tr').attr('data-id'), $(this))
    })
    
    
});