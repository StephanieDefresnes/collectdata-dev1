// css
import '../scss/message_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js')
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js')
const lang = require('../../datatables.json')

function addUnreadAlert(message) {
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
    return item
}

// Search if alter exist
function permuteScanned(id) {
    $.ajax({
        url: "/message/ajaxPermuteScanned",
        method: 'POST',
        data: { id: id },
        success: function(data) {
            if (data.success) {
                let message = data['message']
                if (!message['scanned']) {
                    $('#alerts_dropdown a:last').before(addUnreadAlert(message))
                }
            } else {
                location.reload();
            }
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
            $(this).parent().append('<span class="clean-search"><i class="fas fa-times"></i></span>')
        }
    })
    $('#list').on('click', '.clean-search', function() {
        table.search('').columns().search('').draw();
        $(this).remove()
    })
    
    // Permute Message scanned and update title & count
    $('.form-check-input').change(function() {
        
        let id = $(this).parents('tr').attr('id')
        permuteScanned(id)
        
        let countAlert = $('#countAlert').text()
        let newTitle,newCountAlert
        
        if (this.checked) {
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
        $(this).tooltip('hide')
                .attr('data-original-title', newTitle)
                .tooltip('show');
        
        // Update navbar alert badge
        $('#countAlert').text(newCountAlert)
        
    });
    
});

