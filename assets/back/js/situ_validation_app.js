// css
import '../scss/situ_validation_app.scss';
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

require('select2')

// json
const isoLang = require('../../isoLangs.json')

function initSelect2(select) {
    $.fn.select2.defaults.set('language', {
	noResults: function () {
		return translations['noResult']
	},
    })
    $(select).select2({
        width: 'resolve'
    });
}

// Add comment to unvalidated user options
function unvalidatedOptionData(select) {
    let name = select.parents('formData').attr('id')
    select.find('option').each(function() {
        if ($(this).hasClass('to-validate')) {
            $(this).append(' '+ translations['toValidate'])
        }
        if ($(this).val() == $('#'+ name).attr('data-id'))
            $(this).append(' ✓')
    })
}

function toggleInfo(name, msg) {
    $('#'+ name).find('.check > span').each(function() { $(this).empty() })
    if (msg != 'remove') {        
        let textClass = msg == 'todoValidate' ? 'danger' : 'success'
        let action = msg == 'todoValidate' ? 'todo' : 'done'
        $('#'+ name).find('.'+ action)
                .append('<span class="px-1 text-'+ textClass +'">- '+translations[msg]+'</span>')
    }
}
//function addActionBtn(name, action1, action2) {
function toggleActionBtn(name, action) {
    $('#'+ name).find('.action').each(function() {
        if ($(this).hasClass(action)) {
            if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
        } else $(this).addClass('d-none')
    })
}

function checkValue(name, valid) {
    if (valid == 0) {
        toggleInfo(name, 'todoValidate')
        toggleActionBtn(name, 'validate')
    } else {           
        toggleInfo(name, 'remove')
        $('#'+ name).find('.actions').hide()
    }
}

function checkValidation() {
    let invalid = 0
    $('.validationForm').each(function() {
        if ($(this).val() != 1) invalid += 1
    })
    return invalid
}

function situValidation(dataForm) {
    $.ajax({
        url: '/back/ajaxSituValidation',
        method: 'POST',
        data: {dataForm},
        success: function(data) {
            if (data.success) location.href = data['redirection'];
            else location.reload();
        }
    })
}

function submit(button) {
    let dataForm, entities = []

    if (    button.attr('data-action') == 'validation'
        ||  (   button.attr('data-action') == 'refuse'
            &&  $('#refuseReason').val() != ''
            &&  $('#refuseComment').val() != ''
            && (    $('#translationRefuse').is(':checked')
                ||  $('#EventRefuse').is(':checked')
                ||  $('#CategoryLevel1Refuse').is(':checked')
                ||  $('#CategoryLevel2Refuse').is(':checked')
                ||  $('#SituRefuse').is(':checked')
                ||  $('#ItemsRefuse').is(':checked')
            )
        )
    ) {            
        $(this).attr('data-action') == 'validation'
            ? $('#validModal').modal('hide')
            : $('#refuseModal').modal('hide')

        $('#loader').show()

        entities.push({
            'translation': $('#translationRefuse').prop('checked'),
            'event': $('#EventRefuse').prop('checked'),
            'categoryLevel1': $('#CategoryLevel1Refuse').prop('checked'),
            'categoryLevel2': $('#CategoryLevel2Refuse').prop('checked'),
            'situ': $('#SituRefuse').prop('checked'),
            'items': $('#ItemsRefuse').prop('checked'),
        })

        dataForm = {
            'action': button.attr('data-action'),
            'id': $('#situ').attr('data-id'),
            'statusId': $('#accordion').attr('value-status'),
            'eventId': $('#event').attr('data-id'),
            'eventValidated': $('#validated-event').val(),
            'categoryLevel1Id': $('#categoryLevel1').attr('data-id'),
            'categoryLevel1Validated': $('#validated-categoryLevel1').val(),
            'categoryLevel2Id': $('#categoryLevel2').attr('data-id'),
            'categoryLevel2Validated': $('#validated-categoryLevel2').val(),
            'reason': $('#refuseReason').val(),
            'comment': $('#refuseComment').val(),
            'entities': entities,
        }
        situValidation(dataForm)
    } else {
        $('#refuseError').html('<div class="alert alert-danger" role="alert">'
                +'<i class="fas fa-exclamation-circle"></i> '
                +'<span>'+translations['unvalidRefuse'] +'</span>'
           +'</div>')
    }
}

$(function() {
    
    $('#loader').hide()
    
    // Show validation button if no Event neither Category needs to be validated
    if (checkValidation() == 0 && $('#valid-btn').hasClass('d-none'))
        $('#valid-btn').removeClass('d-none')
    
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
        $('#resetGGT, #situGGT').removeClass('d-none')
        $('.details').each(function() { $(this).removeClass('d-none') })
        $('#situ-data').addClass('h-adjust')
    })
    
    // -- on click reset button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $('#resetGGT, #situGGT').addClass('d-none')
        $('#GGT').find('.goog-te-combo option').each(function() {
            // Translate placeholder
            if ($(this).val() == '') $(this).text(translations['translate'])
        })
        $('.details').each(function() { $(this).addClass('d-none') })
        $('#situ-data').removeClass('h-adjust')
    })
    
    /** Add toValidate info on Event & Category options **/
    $('#situ .card-body').find('select').each(function() {
        let name = $(this).parents('.formData').attr('id')
        $(this).find('option').each(function() {
            if ($(this).hasClass('to-validate')) {
                $(this).append(' '+ translations['toValidate'])
            }
            if ($(this).val() == $('#'+ name).attr('data-id'))
                $(this).prepend('✓ ')
        })
        initSelect2($(this))
        checkValue(name, $('#validated-'+ name).val())
    })
    
    /** Event & Category validation **/
    $('form').find('.validate').click(function() {
        let name = $(this).parents('.formData').attr('id')
        $('#validated-'+ name).val(1)
        $('#valid-'+ name).attr('data-result', 'validated')
        toggleActionBtn(name, 'validated')
        toggleInfo(name, 'doneValidated')
        if (checkValidation() == 0 && $('#valid-btn').hasClass('d-none'))
            $('#valid-btn').removeClass('d-none')
    })
    
    $('form').find('.undo').click(function() {
        let name = $(this).parents('.formData').attr('id')
        $('#validated-'+ name).val(0)
        $('#valid-'+ name).attr('data-result', '')
        checkValue(name, 0)
        $('#valid-btn').addClass('d-none')
    })
    
    /** Translation case, conflict validation **/
    $('form').find('.switch-radio[type="radio"]').click(function() {
        if ($(this).val() == 0) {
            $('#valid-translation').attr('data-result', '')
            $('#validated-situConflict').val(0)
            $('#conflict').addClass('ban')
            $('#valid-btn, #no-conflict').hide()
            $('#conflict-refuse').show()
            $('#refuseComment').prop('required', true)
        }
        else {
            $('#valid-translation').attr('data-result', 'validated')
            $('#validated-situConflict').val(1)
            $('#conflict').removeClass('ban')
            $('#valid-btn, #no-conflict').show()
            $('#conflict-refuse').hide()
            if (checkValidation() == 0) {
                if ($('#valid-btn').hasClass('d-none'))
                    $('#valid-btn').removeClass('d-none')
            } else $('#valid-btn').addClass('d-none')
            $('#refuseComment').prop('required', true)
        }
    })
    if ($('form').find('#situConflict').attr('data-conflict') == 'ko') {
        $('#situ_conflict_0').click()
        $('#situ_conflict_1').prop('disabled', true)
        $('#refuseComment').val(translations['translationRefuse'] +'\n'
                +'<a href="'+ translations['translationPath'] +'">'
                + translations['translationRead'] +'</a>')
    }
        
    /**
     * Confirmation modal
     */
    /** Validated situ **/
    $('#valid-btn').click(function(){
        $('#accordion').attr('value-status', 3)
        $('#validModal').find('.validation').each(function() {
            let result = $(this).attr('data-result')
            $(this).find('.result').each(function() {
                if (!$(this).hasClass(result)) $(this).hide()
            })
        })
        $('#validModal').modal('show')
    })
    
    $('#valid-cancel').click(function() {
        $('#accordion').attr('value-status', '')
        $('#validModal').modal('hide')
                .find('.result').each(function() { $(this).show() })
    })
    
    /** Refused situ **/
    $('#refuse-btn').click(function(){
        if ($('#situ_conflict_0').is(':checked')) {
            $('#refuseModal ul').hide()
            $('#translationRefuse').prop('checked', true)
            $('#refuseReason').val('other')
            $('#refuseComment').prop('required', true)
        }
        $('#accordion').attr('value-status', 4)
        $('#refuseModal').modal('show')
    })
    
    // Add default comment
    // -- on change reason if entities are ckecked
    $('#refuseReason').change(function() {
        $('#refuseComment').val('')
        let reason = $(this).val()
        if (reason == 'other') {
            $('#refuseComment').prop('required', true)
        } else {
            $('#no-conflict').find('input').each(function() {
                if ($(this).is(':checked')) {
                    $('#refuseComment').val(
                        $('#refuseComment').val()
                        + (translations[reason + $(this).attr('id')]).replace("&#039;", "'")
                        + '\n'
                    )
                }
            })
        }
    })
    // -- on check entities if reason is selected
    $('#no-conflict').find('input[type="checkbox"]').click(function() {
        let reason = $('#refuseReason').val()
        if ($(this).is(":checked")) {
            $(this).val(1)
            if ($(this).attr('id') == 'EventRefuse'
             || $(this).attr('id') == 'CategoryLevel2Refuse'
             || $(this).attr('id') == 'CategoryLevel1Refuse') {
                $('#contrib').hide()
                $('#no-contrib').show()
            } else {
                $('#contrib').show()
                $('#no-contrib').hide()
                $('#refuseReason option[value="create"]').addClass('d-none')
            }
                
            if (reason != '') {
                $('#refuseComment').val(
                    $('#refuseComment').val()
                    + (translations[reason + $(this).attr('id')]).replace("&#039;", "'")
                    + '\n'
                )
            }
        } else {
            // Reset refuseModal fields
            $('#refuseModal input').each(function() {
                $(this).prop('checked', false)
                $(this).val('')
            })
            $('#refuseReason, #refuseComment').val('')
            $('#contrib, #no-contrib').show()
            if ($('#refuseReason option[value="create"]').hasClass('d-none'))
                $('#refuseReason option[value="create"]').removeClass('d-none')
        }
    })
    
    $('#refuse-cancel').click(function() {
        $('#refuseModal').modal('hide')
        if ($('form').find('#situConflict').attr('data-conflict') != 'ko') {
            $('#accordion').attr('value-status', '')
            $('#refuseModal').find('input').each(function() { $(this).prop('checked', false) })
                    .parents('.modal-body').find('select').val('')
                    .parents('.modal-body').find('textarea').val('').prop('required', false)
            $('#refuseModal ul, #contrib, #no-contrib').show()
            if ($('#refuseReason option[value="create"]').hasClass('d-none'))
                $('#refuseReason option[value="create"]').removeClass('d-none')
        }
    })
    
    /**
     * Submit
     */
    $('.submit').click(function() { submit($(this)) })
    
})
