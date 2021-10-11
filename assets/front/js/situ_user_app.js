// css
import '../scss/situ_user_app.scss'

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');
const lang = require('../../datatables.json')

// Search if translations exist
function translationRequest(id, langId) {
    $.ajax({
        url: "/"+ path['locale'] +"/situ/ajaxFindTranslation",
        method: 'GET',
        data: { id: id, langId: langId},
        success: function(data) {
            if (data.success) {
                if ($('#valid').hasClass('createTranslation'))
                    $('#valid').removeClass('createTranslation')
                if ($('#valid').hasClass('readTranslation'))
                    $('#valid').removeClass('readTranslation')
                verifyTranslatedSitu(data['situTranslated'])
            } else {
                location.href = data.redirect;
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
        $('#valid').attr('data-id', data[0]['id'])
        if ($('#result').find('.error').hasClass('d-none'))
            $('#result').find('.error').removeClass('d-none')
        $('#result').find('.success').addClass('d-none')
        $('#valid').text(translations['modalBtnRead'])
                .removeClass('d-none').addClass('readTranslation')                        
    }
    $('#spinner').removeClass('show')
    $('#result').removeClass('d-none')
}

// jQuery confirm for update & delete situ action
function confirmActionSitu(action, button) {
    let situTr = button.parents('tr')
    let title = button.parents('tr').find('.situ-title').attr('data-original-title')

    let typeColor, btnClassColor, translationTitle, translationContent

    if (action == 'update') {
        typeColor = 'orange', btnClassColor = 'btn-orange'
        translationTitle = translations['updateTitle']
        translationContent = translations['updateQuestion']
    } else {
        typeColor = 'red', btnClassColor = 'btn-red'
        translationTitle = translations['deleteTitle']
        translationContent = translations['deleteQuestion']
                + '<p class="mt-3 text-center font-weight-bold">'+ title +'</p>'
    }

    situTr.addClass('to-confirm')
    button.confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-xl-6 col-xl-offset-3 col-lg-8 col-lg-offset-2',
        type: typeColor,
        typeAnimated: true,
        title: translationTitle,
        content: translationContent,
        buttons: {
            cancel: {
                text: translations['no'],
            },
            formSubmit: {
                text: translations['yes'],
                btnClass: btnClassColor,
                action: function () {
                    $('#loader').show()
                    location.href = this.$target.attr('href');
                }
            }
        },
    })
}

$(document).ready(function() {
    
    if ($('#situs').attr('data-empty') == 'yes') $('#loader').hide()
    
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
    
    // Update validated situ
    $('.updateConfirm').each(function() {
        confirmActionSitu('update', $(this))
    })
    
    // Delete situ
    $('.situDelete').each(function() {
        confirmActionSitu('detele', $(this))
    })

    /**
     * Translation situ
     */
    // Show modal with data situ to translate and hide language choice if is situ langId
    $('.situTranslate').click(function() {
        let title = $(this).parents('tr').find('.situ-title').attr('data-original-title')
        let langId = $(this).attr('data-lang')
        
        $('#translateModal .situ-title')
                .text(title)
                .attr('data-id', $(this).parents('tr').attr('data-id'))
        
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
        let situId = $('#translateModal .situ-title').attr('data-id') 
        $('#result, #valid').addClass('d-none')
        $('#spinner').addClass('show')
        translationRequest(situId, $(this).val())
    })
    
    // Read existing translation or create it
    $('#valid').click(function() {
        $('#loader').show()
        if ($(this).hasClass('createTranslation')) {            
            location.href = '/'+ path['locale'] 
                    +'/translate/'+ $('#translateModal .situ-title').attr('data-id')
                    +'/'+ $('#translateLangs').val();            
        } else {
            location.href = '/'+ path['locale'] +'/read/'+ $(this).attr('data-id') +'?preview';
        }
    })
    
    // Cancel translateModal
    $('#translateModal .cancel').click(function() {
        $('#translateModal').modal('hide')
        $('#result, #valid').addClass('d-none')
    })
    
});