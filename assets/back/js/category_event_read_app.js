// css
import '../scss/category_event_read_app.scss';
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
require('select2')

// json
const lang = require('../../datatables.json')
const isoLang = require('../../isoLangs.json')

// Datatables configuration
function setDatatable(table) {
    let name = table.attr('id')
    
    table.DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + lang[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-start row"<"#'+ name +'_search.col-auto mt-2"f>>'
                +'<"table-responsive border"t>'
                +'<"d-flex justify-content-end"<"#'+ name +'_pagination.col-md-6 mt-2 p-0"p>>',
        'columnDefs': [{
            targets: 'no-sort',
            orderable: false,
        }],
        'fnDrawCallback': function(oSettings) {            
            // Add class to load reset button search
            $('#'+ name +'_filter input').addClass('search')
            
            // Hide pagination & search if only one page
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ name +'_pagination .dataTables_paginate').hide()
            }
            if (table.dataTable().fnSettings().fnRecordsTotal() <= 10) {
               $('#'+ name +'_search').hide()
            }
        }
    })
}

// Reset search filter
function resetFilter(table) {  
    let oTable = table.DataTable()
    let name = table.parents('.back-dataTable').attr('id')
    
    $('#'+ name).on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search small pointer"><i class="fas fa-times"></i></span>')
        }
    })
    $('#'+ name).on('click', '.clean-search', function() {
        oTable.search('').columns().search('').draw();
        $(this).remove()
    })
}

// Enable event
function ajaxEnable(id, button) {
    let url = button.hasClass('enableEvent')
                ? '/back/ajaxEventEnable' : '/back/ajaxCategoryEnable'
    $.ajax({
        url: url,
        method: 'POST',
        data: { id: id },
        success: function(data) {
            if (data.success) {
                $('#enable-row').addClass('mt-1').html(translation['yes'])
            } else {
                location.reload();
            }
            if ($('#enableData').hasClass('d-none'))
                $('#enableData').removeClass('d-none')
            $('#enable-row > .spinner-border').addClass('d-none')
        }
    })
}

$(function() {
    
    // Datatables configuration
    setDatatable($('#dataTableCategories'))
    if ($('#situs').length == 1) setDatatable($('#dataTableSitus'))

    // Reset search filter
    resetFilter($('#dataTableCategories'))
    if ($('#situs').length == 1) resetFilter($('#dataTableSitus'))
    
    
    $('#enableData').click(function() {
        $(this).addClass('d-none')
        $('#enable-row > .spinner-border').removeClass('d-none')
        ajaxEnable($(this).attr('data-id'), $(this))
    })
    
    /** GGTranslate **/
    // -- on load
    let ggtExist = setInterval(function() {
        // Wait for GGT container
        if ($('#\\:1\\.container').length) {
            
            // Reset menu
            $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
            
            if ($('#GGT').find('.goog-te-combo option').length) {
                $('#GGT').find('.goog-te-combo option').each(function() {
                    // Translate placeholder
                    if ($(this).val() == '') $(this).text(translations['translate'])
                    else {
                        let lang = $(this).val()
                        // Get first lang name & capitalize
                        let nativeLangName = isoLang[lang].nativeName.split(',')[0]
                        $(this).text(nativeLangName.charAt(0).toUpperCase() + nativeLangName.slice(1))
                    }
                })
                clearInterval(ggtExist)
            }
        }
    }, 50);
    
    // -- on change lang
    $('#translator').on('change', 'select', function() {
        $('#resetGGT').removeClass('d-none')
    })
    
    // -- on click reset button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $(this).addClass('d-none')
        $('#GGT').find('.goog-te-combo option').each(function() {
            // Translate placeholder
            if ($(this).val() == '') $(this).text(translations['translate'])
        })
    })
    
});