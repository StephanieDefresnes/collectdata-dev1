// css
import '../scss/lang_translation_site_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

// Datatables configuration
function setDatatable(table) {
    $('#'+ table).DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-between row mb-2"<"#'+ table +'_length.col-md-5"l><"#'+ table +'_search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"row"<"#'+ table +'_info.col-md-6 small"i><"#'+ table +'_pagination.col-md-6 mt-3"p>>',
        "columnDefs": [{
            orderable: false,
            targets: 0
        }],
        "order": [[ 1, 'asc' ]],
        "fnDrawCallback": function(oSettings) {            
            // Add class to load reset button search
            $('#'+ table +'_filter input').addClass('search')
            
            // Hide length select & pagination if only one page
            if ($('#'+ table).dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ table +'_length, #'+ table +'_pagination .dataTables_paginate').hide()
               $('#'+ table +'_search .dataTables_filter').addClass('text-left')
            }
            
            $('#loader').hide()
        }
    })
}

// Reset search filter
function resetFilter(table) {  
    let oTable = $('#'+ table).DataTable()
    
    $('#'+ table + '-list').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search small pointer"><i class="fas fa-times"></i></span>')
        }
    })
    $('#'+ table + '-list').on('click', '.clean-search', function() {
        oTable.search('').columns().search('').draw();
        $(this).remove()
    })
}

// Select entries
function selectEntries(checkBox) {
    $('#'+ checkBox).change(function() {
        let listType = $(this).parents('table').attr('data-list')
        
        if (this.checked) {
            $(this).parents('table').find('.select').each(function() {
                this.checked=true;
            });
            // Show actions
            $('#'+ listType +'Actions').removeClass('d-none').animate({opacity: 1}, 450)
            
            $(this).parents('.tab-pane').find('#create')
                    .addClass('d-none').css('opacity', 0)
            
        } else {
            $(this).parents('table').find('.select').each(function() {
                this.checked=false;
            });
            // Hide actions row
            $('#'+ listType +'Actions').addClass('d-none').css('opacity', 0)
            
            $(this).parents('.tab-pane').find('#create')
                    .removeClass('d-none').animate({opacity: 1}, 450)
        }
    });
}

function checkActionResult(name) {
    if ($('form[name="translation_batch_'+ name +'"] > .alert').length == 1) {
        // Open actions menu if rows are checked
        $('#'+ name +' input[type="checkbox"]').each(function() {
            if ($(this).prop('checked') == true && $('#'+ name +'Actions').hasClass('d-none'))
                $('#'+ name +'Actions').removeClass('d-none')
        })
    } else {
        // Uncheck input
        $('#'+ name +' input[type="checkbox"]').each(function() {
            $(this).prop('checked', false)
        })
    }
}

$(function() {
    
    if ($('#siteEmpty').length == 1 && $('#contributorEmpty').length == 1)
        $('#loader').hide()
    
    // Switch navtab if error on contributor form
    if ($('form[name="translation_batch_contributor"] > .alert').length == 1) {
        $('#contrib-tab').tab('show')
    }
    
    // Check result to toggle actions menu & checkboxes
    checkActionResult('site')
    checkActionResult('site')
    
    // Datatables configuration
    setDatatable('dataTable-site')
    setDatatable('dataTable-contributor')
    resetFilter('dataTable-site')
    resetFilter('dataTable-contributor')
    
    // Check boxes management
    selectEntries('selectAllSite')
    selectEntries('selectAllContributor')
    
    // Toggle actions menu
    $('.form-check-input').change(function() {
        let listType = $(this).parents('table').attr('data-list')
        
        if (this.checked && $('#'+ listType +'Actions').hasClass('d-none')) {
            
            $('#'+ listType +'Actions').removeClass('d-none').animate({opacity: 1}, 450)
            
            $(this).parents('.tab-pane').find('#create')
                    .addClass('d-none').css('opacity', 0)
            
        } else if (!this.checked && !$('#'+ listType +'Actions').hasClass('d-none')) {
            var check = 0;
            $(this).parents('table').find('.select').each(function() {
                if (this.checked) check += 1
            });
            if(check == 0) $('#'+ listType +'Actions').addClass('d-none').css('opacity', 0)
            
            $(this).parents('.tab-pane').find('#create')
                    .removeClass('d-none').animate({opacity: 1}, 450)
        }
    })
    
    // Modal
    $('#createTranslation').click(function() {
        let referentId = $('#siteEnvironnement').val()
        let langId = $('#siteLang').val()
        location.href = '/'+ path["locale"] +'/back/translation/'+ referentId + '/' + langId
    })
    
});