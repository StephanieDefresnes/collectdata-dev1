// css
import '../scss/situ_user_app.scss'

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

function flashMessage(status, LangDeny) {
    $('body').find('#flash_message').remove()
    let i = status == 'success' ? '<i class="fas fa-check-circle"></i>'
                                : '<i class="fas fa-exclamation-circle"></i>'
    let textClass = status == 'success' ? 'text-success' : 'text-danger'
    let flashMessage =
            '<div id="flash_message" class="container">'
                +'<div class="alert alert-secondary alert-dismissible px-3 fade show" role="alert">'
                        +'<span class="sr-only">'+ translations['srOnly-'+status] +'</span>'
                        +'<span class="icon '+ textClass +'">'+ i +'</span>'
                        +'<span class="msg">'+ translations['flashValid-'+status+LangDeny] +'</span>'
                +'</div>'
            +'</div>'
    $('body > .container-fluid').before(flashMessage)
    window.scrollTo({top: 0, behavior: 'smooth'});
    $('#flash_message').delay(3000).fadeOut(); 
}

// Update button status when validation is resquested
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
            flashMessage('success', '')
            $('#loader').hide()
        },
        error: function() {
            flashMessage('error', '')
            $('#loader').hide()
        }
    })
}

// Search if translations exist
function translationRequest(id, langId) {
    $.ajax({
        url: "/"+ path['locale'] +"/ajaxFindTranslation",
        method: 'GET',
        data: { id: id, langId: langId},
        success: function(data) {
            if (!data['error']) {
                if ($('#valid').hasClass('createTranslation'))
                    $('#valid').removeClass('createTranslation')
                if ($('#valid').hasClass('readTranslation'))
                    $('#valid').removeClass('readTranslation')
                verifyTranslatedSitu(data['situTranslated'])
            } else {
                $('#translateModal').modal('hide')
                flashMessage('error', 'LangDeny')
            }
            
        }
    })
}

// Load modal to read existing translation or create it
function verifyTranslatedSitu(data) {
    if (data.length == 0) {
        if ($('#result').find('.success').hasClass('d-none'))
            $('#result').find('.success').removeClass('d-none')
        $('#result').find('.error').addClass('d-none')
        $('#valid').text(translations['modalBtnValid'])
                .removeClass('d-none').addClass('createTranslation')
    } else {
        $('#valid').attr('data-id', data[0]['situ_id'])
        if ($('#result').find('.error').hasClass('d-none'))
            $('#result').find('.error').removeClass('d-none')
        $('#result').find('.success').addClass('d-none')
        $('#valid').text(translations['modalBtnRead'])
                .removeClass('d-none').addClass('readTranslation')                        
    }
    $('#spinner').removeClass('show')
    $('#result').removeClass('d-none')
}

$(document).ready(function() {
    
    // Init datatables
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
        'order': [[ 0, 'desc' ]],
        'lengthMenu': [[10, 25, 50], [10, 25, 50]],
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
    
    // Validation request
    $('.situValidation').click(function() {
        $('#loader').show()
        validationRequest($(this).parents('tr').attr('data-id'), $(this))
    })

    // Show modal with data situ to translation and hide language choice if is situ langId
    $('.situTranslate').click(function() {
        
        let title = $(this).parents('tr').find('.situ-title').attr('data-original-title')
        let langId = $(this).attr('data-lang')
        
        $('#situ-title').text(title).attr('data-id', $(this).parents('tr').attr('data-id'))
        
        $('#translateLangs option').each(function() {
            if ($(this).val() != '') {
                if ($(this).val() == langId) $(this).addClass('d-none')
                else {
                    if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
                }
            }
        })
        
        $('#translateLangs').val('')
        if ($('#spinner').hasClass('show')) $('#spinner').removeClass('show')
        $('#translateModal').modal('show')
    })
    
    // Search if translations exist
    $('#translateLangs').focus(function() {
        $('#translateLangs').val('')
    }).change(function() {
        let situId = $('#situ-title').attr('data-id') 
        $('#result, #valid').addClass('d-none')
        $('#spinner').addClass('show')
        translationRequest(situId, $(this).val())
    })
    
    // Read existing translation or create it
    $('#valid').click(function() {
        if ($(this).hasClass('createTranslation')) {            
            location.href = '/'+ path['locale'] 
                    +'/translate/'+ $('#situ-title').attr('data-id')
                    +'/'+ $('#translateLangs').val();            
        } else {
            location.href = '/'+ path['locale'] +'/read/' +$(this).attr('data-id');
        }
    })
    
    // Cancel modal
    $('#cancel').click(function() {
        $('#translateModal').modal('hide')
        $('#result, #valid').addClass('d-none')
    })
    
});