// css
import '../scss/message_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js')
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js')
const lang = require('../datatables.json')

function permuteReadMessage(id, message, button, type) {
    
    let countMessage = $('#'+ type +'Count').text(),
        item, newCountMessage, newTitle
    
    if (false === message['scanned']) {
        
        // Add item alert
        const date = new Date(message['dateCreate'])
        
        if ($('#messages').hasClass('back-dataTable')) {            
            item = '<a class="dropdown-item d-flex align-items-center" '
                        +'href="/fr/back/follow-message/'+ message['id'] +'" '
                        +'id="'+ message['id'] +'">'
                            +'<div class="mr-3 position-relative">'
                                +'<div class="icon-circle bg-primary text-white">'
                                    +'<i class="fas fa-hands"></i>'+'</div>'
                            +'</div>'
                            +'<div>'
                                +'<div class="small text-gray-500">'
                                    + new Intl.DateTimeFormat(path['locale'],{dateStyle:'full',timeStyle:'short'}).format(date)   
                                +'</div>'
                                + message['subject']
                            +'</div>'
                        +'</a>'
            $('#'+ type +'_dropdown a:last').before(item)
        } else {
            let title = type == 'alerts'
                    ? message['subject']
                    : translations['envelopeNew'].replace('%sender%', message['sender'])
            item =  '<li class="dropdown-item bg-dark-95" id="'+ message['id'] +'">'
                        +'<a class="text-white" href="/fr/follow-message/'+ message['id'] +'">'
                        + title +'</a>'
                    +'</li>'
            $('#'+ type +'_dropdown li:last').before(item)
        }
            
        newCountMessage = parseInt(countMessage) + 1;
        newTitle = translations['readYes']
        
        if(countMessage == 0) {
            if ($('#'+ type +'Count').hasClass('d-none'))
                $('#'+ type +'Count').removeClass('d-none')
            if ($('#'+ type +'_dropdown').hasClass('d-none'))
                $('#'+ type +'_dropdown').removeClass('d-none')
            
            $('#'+ type +'Dropdown')
                    .attr('data-toggle', 'dropdown')
                    .attr('aria-haspopup', 'true')
                    .attr('aria-expanded', 'false')
                    .attr('href', '#')
        }
        
    } else {

        // Update navbar type dropdown
        newCountMessage = parseInt(countMessage) - 1;
        if (newCountMessage == 0) {
            $('#'+ type +'Count, #'+ type +'_dropdown').addClass('d-none')
            $('#'+ type +'Dropdown')
                    .removeAttr('data-toggle')
                    .removeAttr('aria-haspopup')
                    .removeAttr('aria-expanded')
                    .attr('href', $('#'+ type +'List').attr('href'))
        } else {
            if ($('#'+ type +'Count').hasClass('d-none'))
                $('#'+ type +'Count').removeClass('d-none')
            if ($('#'+ type +'_dropdown').hasClass('d-none'))
                $('#'+ type +'_dropdown').removeClass('d-none')

            $('#'+ type +'Dropdown')
                    .attr('data-toggle', 'dropdown')
                    .attr('aria-haspopup', 'true')
                    .attr('aria-expanded', 'false')
                    .attr('href', '#')
        }

        // Remove item alert
        $('#'+ type +'_dropdown').find('.dropdown-item').each(function(){
            if ($(this).attr('id') == id)
                $(this).remove()
        })
        
        newTitle = translations['readNo']        
    }
    
    // Update checkbox tooltip
    button.tooltip('dispose').attr('title', newTitle).tooltip('enable')

    // Update navbar alert badge
    $('#'+ type +'Count').text(newCountMessage)

    // Hide spinner
    button.parents('tr').find('.spinner-border').addClass('d-none')
    button.parent().parent().removeClass('d-none')
}

// Search if alter exist
function permuteScanned(id, button, type) {
    $.ajax({
        url: '/message/ajaxPermuteScanned',
        method: 'POST',
        data: { id: id },
        success: function(data) {
            if (data.success) permuteReadMessage(id, data['message'], button, type) 
            else location.reload()
        }
    })
}

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
        dom: '<"d-flex justify-content-start row mb-2"<"#search.col-auto"f>>'
                +'<"table-responsive border border-top-0"t>'
                +'<"d-flex justify-content-end"<"#pagination.col-md-6 mt-3"p>>',
        'order': [[ 0, 'desc' ]],
        'fnDrawCallback': function(oSettings) {
            $('#dataTable-list_filter input').addClass('search')
            
            // Hide length select & pagination if only one page
            if ($('#dataTable-list').dataTable().fnSettings().fnRecordsTotal() <= 10) {
                $('#pagination .dataTables_paginate').hide()
            }
            $('#loader').hide()
        }
    })

    // Reset search filter
    $('#list').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().prepend('<span class="clean-search"><i class="fas fa-times"></i></span>')
        }
    })
    $('#list').on('click', '.clean-search', function() {
        table.search('').columns().search('').draw();
        $(this).remove()
    })
    
    // Permute Message scanned and update title & count
    $('.form-check-input').click(function() {
        
        let type = $('#messages').hasClass('alerts') ? 'alerts' : 'envelopes'
        
        $(this).parents('tr').find('.spinner-border').removeClass('d-none')
        $(this).parent().parent().addClass('d-none')
        
        permuteScanned($(this).parents('tr').attr('id'), $(this), type)
    });
    
});