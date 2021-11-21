// css
import '../scss/message_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js')
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js')
const lang = require('../../datatables.json')

function permuteButton(button) {
        
        let countAlert = $('#countAlert').text(),
            newTitle,newCountAlert
        
        if (button.is(':checked')) {
            newTitle = translations['readNo']
            
            // Update navbar alert dropdown
            newCountAlert = parseInt(countAlert) + 1;
            if ($('#countAlert').hasClass('d-none')
                    && $('#alerts_dropdown').hasClass('d-none'))
                $('#countAlert, #alerts_dropdown').removeClass('d-none')
            
            $('#alerts_dropdown').find('.dropdown-item').each(function(){
                if ($(this).attr('id') == id && $(this).hasClass('d-none'))
                    $(this).removeClass('d-none')
            })
        } else {
            newTitle = translations['readYes']
            
            // Update navbar alert dropdown
            newCountAlert = parseInt(countAlert) - 1;
            if (newCountAlert == 0) 
                $('#countAlert, #alerts_dropdown').addClass('d-none')
            
            $('#alerts_dropdown').find('.dropdown-item').each(function(){
                if ($(this).attr('id') == id)
                    $(this).remove()
            })
        }
    
        // Update checkbox tooltip
        button.tooltip('hide')
                .attr('data-original-title', newTitle)
                .tooltip('show');
        
        // Update navbar alert badge
        $('#countAlert').text(newCountAlert)
        
        button.parents('tr').find('.spinner-border').addClass('d-none')
        button.parent().parent().removeClass('d-none')
    
}

function permuteReadAlert(id, message, button) {
    
    let countAlert = $('#countAlert').text(),
        newTitle, newCountAlert
    
    if (false === message['scanned']) {
        
        // Add item alert
        const date = new Date(message['dateCreate'])
        let item = '<a class="dropdown-item d-flex align-items-center" '
                    +'href="/fr/back/follow-alert/'+ message['id'] +'" '
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
        $('#alerts_dropdown a:last').before(item)
            
        // Update navbar alert dropdown
        if (countAlert == 0) {            
            $('#alertsDropdown')
                    .attr('data-toggle', 'dropdown')
                    .attr('aria-haspopup', 'true')
                    .attr('aria-expanded', 'false')
                    .attr('href', '/fr/back/my-alerts')
        }
        
        newCountAlert = parseInt(countAlert) + 1;
        if ($('#countAlert').hasClass('d-none'))  $('#countAlert').removeClass('d-none')
        if ($('#alerts_dropdown').hasClass('d-none'))  $('#alerts_dropdown').removeClass('d-none')

        newTitle = translations['readNo']        
    } else {

        // Update navbar alert dropdown
        newCountAlert = parseInt(countAlert) - 1;
        if (newCountAlert == 0) 
            $('#countAlert, #alerts_dropdown').addClass('d-none')

        // Remove item alert
        $('#alerts_dropdown').find('.dropdown-item').each(function(){
            if ($(this).attr('id') == id)
                $(this).remove()
        })
        
        newTitle = translations['readYes']        
    }
    
    // Update checkbox tooltip
    button.tooltip('hide').attr('data-original-title', newTitle)

    // Update navbar alert badge
    $('#countAlert').text(newCountAlert)

    // Hide spinner
    button.parents('tr').find('.spinner-border').addClass('d-none')
    button.parent().parent().removeClass('d-none')
}

// Search if alter exist
function permuteScanned(id, button) {
    $.ajax({
        url: '/message/ajaxPermuteScanned',
        method: 'POST',
        data: { id: id },
        success: function(data) {
            if (data.success) permuteReadAlert(id, data['message'], button) 
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
        
        $(this).parents('tr').find('.spinner-border').removeClass('d-none')
        $(this).parent().parent().addClass('d-none')
        
        permuteScanned($(this).parents('tr').attr('id'), $(this))
    });
    
});