// css
import '../scss/situ_validation_app.scss';
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

require('select2')

function initSelect2( select ) {
    $.fn.select2.defaults.set('language', {
	noResults: function () {
		return translations['noResult']
	},
    })
    $(select).select2({
        width: 'resolve'
    });
}

function doValidation( btn ) {
    let name = btn.parents('.formData').attr('id')
    $('#validated-'+ name).val(1)
    $('#valid-'+ name).attr('data-result', 'validated')
    toggleActionBtn( name, 'validated' )
    toggleInfo( name, 'doneValidated' )
    if ( 0 === checkValidation()) {
        if ( $('#valid-btn').hasClass('d-none') ) {
            $('#valid-btn').removeClass('d-none')
            $('#refuse-btn').addClass('d-none')
        }
        return
    }
    if ( $('#refuse-btn').hasClass('d-none') )
        $('#refuse-btn').removeClass('d-none')
    // Modal
    checkValidModalResult()
}

function undoValidation( btn ) {
    let name = btn.parents('.formData').attr('id')
    $('#validated-'+ name).val(0)
    checkValue( name, 0 )
    $('#valid-btn').addClass('d-none')
    if ( $('#refuse-btn').hasClass('d-none') )
        $('#refuse-btn').removeClass('d-none')
    // Modal
    $('#valid-'+ name).attr('data-result', '')
    checkValidModalResult() 
}

function toggleInfo( name, msg ) {
    
    $('#'+ name).find('.check > span').each(function() { $(this).empty() })
    
    if ( 'remove' !== msg ) {        
        let textClass   = 'todoValidate' === msg ? 'danger' : 'success',
            action      = 'todoValidate' === msg ? 'todo' : 'done',
            actionClass = 'todoValidate' === msg ? 'toValidate' : ''
        
        $('#'+ name).find('.'+ action).addClass(actionClass)
                .append('<span class="px-1 text-'+ textClass +'">- '+ translations[msg] +'</span>')
        
        if ( '' !== actionClass ) $('#valid-'+ name).attr('data-result', '')
            
    }
}

function toggleActionBtn( name, action ) {
    $('#'+ name).find('.action').each(function() {
        
        if ( $(this).hasClass(action) ) {
            if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
            return
        }
        
        $(this).addClass('d-none')
    })
}

function checkValue( name, valid ) {
    
    if ( 0 == valid ) {
        toggleInfo( name, 'todoValidate' )
        toggleActionBtn( name, 'validate' )
        return
    }
    
    toggleInfo( name, 'remove' )
    $('#'+ name).find('.actions').hide()
}

function checkValidation() {
    let invalid = 0
    $('.validationForm').each(function() {
        if ( $(this).val() != 1 ) invalid += 1
    })
    return invalid
}

function checkTranslation( input ) {
    
    if ( 0 == input.val() ) {
        $('#valid-translation').attr('data-result', '')
        $('#validated-situConflict').val(0)
        $('#conflict').addClass('ban')
        $('#valid-btn, #no-conflict').hide()
        $('#conflict-refuse').show()
        $('#refuseComment').prop('required', true)
        return
    }

    $('#valid-translation').attr('data-result', 'validated')
    $('#validated-situConflict').val(1)
    $('#conflict').removeClass('ban')
    $('#valid-btn, #no-conflict').show()
    $('#conflict-refuse').hide()
    $('#refuseComment').prop('required', true)

    if ( 0 === checkValidation() ) {
        if ( $('#valid-btn').hasClass('d-none') ) $('#valid-btn').removeClass('d-none')
        return
    }

    $('#valid-btn').addClass('d-none')
}

/**
 * Modal events
 */
function submit( button ) {
    
    let dataForm, entities = []

    if (    ( 'validation' === button.attr('data-action')
            && 0 === checkValidation() )
        ||  (   'refuse' === button.attr('data-action')
            &&  '' !== $('#refuseReason').val()
            &&  '' !== $('#refuseComment').val()
            && (    $('#translationRefuse').is(':checked')
                ||  $('#EventRefuse').is(':checked')
                ||  $('#CategoryLevel1Refuse').is(':checked')
                ||  $('#CategoryLevel2Refuse').is(':checked')
                ||  $('#SituRefuse').is(':checked')
                ||  $('#ItemsRefuse').is(':checked')
            )
        )
    ) {
        $('#loader').show()
        button.parents('.modal').modal('hide')

        entities.push({
            'translation'       : $('#translationRefuse').prop('checked'),
            'event'             : $('#EventRefuse').prop('checked'),
            'categoryLevel1'    : $('#CategoryLevel1Refuse').prop('checked'),
            'categoryLevel2'    : $('#CategoryLevel2Refuse').prop('checked'),
            'situ'              : $('#SituRefuse').prop('checked'),
            'items'             : $('#ItemsRefuse').prop('checked'),
        })

        dataForm = {
            'action'                    : button.attr('data-action'),
            'id'                        : $('#situ').attr('data-id'),
            'statusId'                  : $('#accordion').attr('value-status'),
//            'eventId'                   : $('#event').attr('data-id'),
            'eventInitial'              : $('#validated-event').attr('data-initial'),
            'eventValidated'            : $('#validated-event').val(),
//            'categoryLevel1Id'          : $('#categoryLevel1').attr('data-id'),
            'categoryLevel1Initial'     : $('#validated-categoryLevel1').attr('data-initial'),
            'categoryLevel1Validated'   : $('#validated-categoryLevel1').val(),
//            'categoryLevel2Id'          : $('#categoryLevel2').attr('data-id'),
            'categoryLevel2Initial'     : $('#validated-categoryLevel2').attr('data-initial'),
            'categoryLevel2Validated'   : $('#validated-categoryLevel2').val(),
            'reason'                    : $('#refuseReason').val(),
            'comment'                   : $('#refuseComment').val(),
            'entities'                  : entities,
        }
        
        $.ajax({
            url: '/back/ajaxSituValidation',
            method: 'POST',
            data: {dataForm},
            success: function(data) {
                if ( data.success ) {
                    location.href = data['redirection'];
                    return
                }
                location.reload();
            }
        })
        
        return
    }
    
    if ( 'validation' === button.attr('data-action') ) {
        $('#validError').html('<div class="alert alert-danger" role="alert">'
                +'<i class="fas fa-exclamation-circle"></i> '
                +'<span>'+ translations['unvalidValidation'] +'</span>'
           +'</div>')
        $('#validModal .btn.submit').addClass('d-none')
        
        $('#validModal .validation').each(function() {
            if ( '' === $(this).attr('data-result') )
                $(this).parent().addClass('alert-danger py-2 line-11')
        })        
        return
    }
    
    $('#refuseError').html('<div class="alert alert-danger" role="alert">'
            +'<i class="fas fa-exclamation-circle"></i> '
            +'<span>'+ translations['unvalidRefuse'] +'</span>'
       +'</div>')
}

// Valid modal
function checkValidModalResult() {
    $('#validModal').find('.validation').each(function() {
        let result = $(this).attr('data-result')
        
        if ( '' === result ) {
            $(this).children().each(function() { $(this).addClass('d-none') })
            return
        }
        
        $(this).find('.result').each(function() {
            if ( $(this).hasClass(result) ) $(this).removeClass('d-none')
        })
    })
}

function cancelValid() {
    $('#accordion').attr('value-status', '')
    $('#validModal .modal-body').hide()
    $('#validModal').modal('hide')
    
    $('#validModal').on('hidden.bs.modal', function (e) {
        $('#validModal .result').each(function() { $(this).show() })
        $('#validError').empty()
        if ( $(this).parent().find('.submit').hasClass('d-none') )
                $(this).parent().find('.submit').removeClass('d-none')
        $('#validModal .modal-body').show()
        $('#validModal .modal-body .row').each(function() {
            if ( $(this).hasClass('alert-danger py-2 line-11') )
                    $(this).removeClass('alert-danger py-2 line-11')
        })
    })
}

// Refuse modal
function refuseSitu() {
    if ( $('#situ_conflict_0').is(':checked') ) {
            $('#refuseModal ul').hide()
            $('#translationRefuse').prop('checked', true)
            $('#refuseReason').val('other')
            $('#refuseComment').prop('required', true)
        }
        $('#accordion').attr('value-status', 4)
        $('#refuseModal').modal('show')
}

function changeReason( select ) {
    $('#refuseComment').val('')

    let reason = select.val()

    if ( 'other' === reason ) {
        $('#refuseComment').prop('required', true)
        return
    }

    $('#no-conflict').find('input').each(function() {
        if ( $(this).is(':checked') ) {
            $('#refuseComment').val(
                $('#refuseComment').val()
                + ( translations[reason + $(this).attr('id')] ).replace("&#039;", "'")
                + '\n'
            )
        }
    })
}

function selectRefusedEntities( input ) {
    
    if ( input.is(':checked') ) {
        input.val(1)

        let reason = $('#refuseReason').val()
        if ( '' !== reason && 'other' !== reason ) {
            $('#refuseComment').val(
                $('#refuseComment').val()
                + ( translations[reason + input.attr('id')] ).replace("&#039;", "'")
                + '\n'
            )
        }

        if ( 'EventRefuse' === input.attr('id') 
          || 'CategoryLevel2Refuse' === input.attr('id') 
          || 'CategoryLevel1Refuse' === input.attr('id')  )
        {
            $('#contrib').hide()
            $('#no-contrib').show()
            return
        }

        $('#contrib').show()
        $('#no-contrib').hide()
        $('#refuseReason option[value="create"]').addClass('d-none')
        return
    }

    // Reset refuseModal fields
    $('#refuseModal input').each(function() {
        $(this).val('').prop('checked', false)
    })
    $('#refuseReason, #refuseComment').val('')
    $('#contrib, #no-contrib').show()
    if ( $('#refuseReason option[value="create"]').hasClass('d-none') )
            $('#refuseReason option[value="create"]').removeClass('d-none')
}

function cancelRefuse() {
    $('#refuseModal').modal('hide')
    $('#refuseError').empty()
    
//    if ( 'ko' === $('form').find('#situConflict').attr('data-conflict') ) {
        $('#accordion').attr('value-status', '')
        
        $('#refuseModal').find('input').each(function() { $(this).val('').prop('checked', false) })
                .parents('.modal-body').find('select').val('')
                .parents('.modal-body').find('textarea').val('').prop('required', false)
        
        $('#refuseModal ul, #contrib, #no-contrib').show()
        
        if ( $('#refuseReason option[value="create"]').hasClass('d-none') )
                $('#refuseReason option[value="create"]').removeClass('d-none')
//    }
}

$(function() {
    
    $('#loader').hide()
    
    // Show validation button if no Event neither Category needs to be validated
    if ( 0 === checkValidation() && $('#valid-btn').hasClass('d-none') )
        $('#valid-btn').removeClass('d-none')
    
    // Add toValidate info on Event & Category options
    $('#situ .card-body').find('select').each(function() {
        
        let name = $(this).parents('.formData').attr('id')
        
        $(this).find('option').each(function() {
            if ( $(this).hasClass('to-validate') )
                    $(this).append(' '+ translations['toValidate'])
            if ( $('#'+ name).attr('data-id') === $(this).val() )
                    $(this).prepend('✓ ')
        })
        
        initSelect2( $(this) )
        checkValue( name, $('#validated-'+ name).val() )
    })
    
    // Event & Category validation
    $('form').find('.validate').click(function() { doValidation( $(this) ) })
    $('form').find('.undo').click(function() { undoValidation( $(this) ) })
    
    // Translation case, conflict validation
    $('form').find('.switch-radio[type="radio"]')
            .click(function() { checkTranslation( $(this) ) })
    
    if ( 'ko' === $('form').find('#situConflict').attr('data-conflict') ) {
        $('#situ_conflict_0').click()
        $('#situ_conflict_1').prop('disabled', true)
        $('#refuseComment').val(translations['translationRefuse'] +'\n'
                +'<a href="'+ translations['translationPath'] +'">'
                + translations['translationRead'] +'</a>')
    }
        
    /**
     * Valid modal
     */
    $('#valid-btn').click(function(){
        $('#accordion').attr('value-status', 3)
        checkValidModalResult()
        $('#validModal').modal('show')
    })
    
    $('#valid-cancel').click(function() { cancelValid() })
    
    /**
     * Refuse modal
     */
    $('#refuse-btn').click(function(){ refuseSitu() })
    
    // Add default comment
    // -- on change reason if entities are ckecked
    $('#refuseReason').change(function() { changeReason(  $(this) ) })
    // -- on check entities if reason is selected
    $('#no-conflict').find('input[type="checkbox"]')
            .click(function() { selectRefusedEntities( $(this) ) })
    
    // Cancel
    $('#refuse-cancel').click(function() { cancelRefuse() })
    
    /**
     * Submit
     */
    $('.submit').click(function() { submit( $(this) ) })
    
})