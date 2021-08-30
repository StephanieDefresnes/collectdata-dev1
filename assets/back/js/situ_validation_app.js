// css
import '../scss/situ_validation_app.scss';
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

require('select2')

function initSelect2(select) {
    $.fn.select2.defaults.set('language', {
	noResults: function () {
		return translations['noResult']
	},
    })
    $(select+':not(.no-select2)').select2({
        language: "fr",
        width: 'resolve'
    });
}

// Add comment to unvalidated user options
function unvalidatedOption(element) {
        $(element).find('option').each(function() {
            if ($(this).hasClass('to-validate')) {
                $(this).append(' '+ translations['toValidate'])
            }
        })
}

/**
 * Loading selection
 */
// Load Event & Categories choices
function loadDataSitu(name, value) {
    let dataExist = setInterval(function() {
        if ($('#verify_situ_form_'+ name +' option').length > 1) {
            $('#verify_situ_form_'+ name).val(value).trigger('change')
            if (name == 'categoryLevel2'){
                $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
                $('#translator .ggt-row').removeClass('d-none')
            }
            clearInterval(dataExist)
        }
    }, 50);
}

// Modify next form depending on action change
function changeAction(selectId) {
    let nextSelectId = selectId.parents('.formData').next().find('select').attr('id'),
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    loadSelectData($form, data, selectId, '#'+ nextSelectId)
}

// Load options select if exist or create new (eventListener)
function loadSelectData($form, data, selectId, nextSelectId) {
    let divId = selectId.parents('.formData').attr('id')
    
    // Load data from eventListener
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            $(nextSelectId).replaceWith(
                $(html).find(nextSelectId)
            )
            unvalidatedOption(nextSelectId)
            initSelect2(nextSelectId)
            if (divId != 'lang')
                getData(divId, selectId.val())
        }
    })
}

// Get Event & Categorie data depending on action change
function getData(name, value) {
    let event, categoryLevel1, categoryLevel2, dataForm
    
    if (name == 'event') event = value
    else if (name == 'categoryLevel1') categoryLevel1 = value
    else if (name == 'categoryLevel2') categoryLevel2 = value

    dataForm = {
        'event': event,
        'categoryLevel1': categoryLevel1,
        'categoryLevel2': categoryLevel2,
    }
    ajaxGetData(name, dataForm)
}

// Load Event or Categories data on action change
function ajaxGetData(name, dataForm) {
    $.ajax({
        url: "/"+ path['locale'] +"/situ/ajaxGetData",
        method: 'POST',
        data: {dataForm},
        success: function(data) {
            if (data[name]) loadNewData(name, data[name])
        }
    })
}
function loadNewData(name, data) {
    let valid = data.validated === true ? 1 : 0
    $('#'+ name).attr('data-valid', valid)
    $('#'+ name).find('.title').text(data.title)
    if (name != 'event') $('#'+ name).find('.description').text(data.description)
    checkValue(name, valid)
}

/**
 * When selection is done
 */
function removeInfo(name, selector) {
    if (selector == 'all')
        $('#'+ name).find('.check > span').each(function() { $(this).empty() })
    else $('#'+ name).find('.'+ selector).empty()
}
function addInfo(name, action, msg) {
    let textClass = action == 'todo' ? 'danger' : 'success'
    $('#'+ name).find('.'+ action)
            .append('<span class="px-1 text-'+ textClass +'">- '+translations[msg]+'</span>')
}
function addActionBtn(name, action1, action2) {
    if ($('#'+ name).find('.modified > .msg').hasClass('d-none'))
        $('#'+ name).find('.modified > .msg').removeClass('d-none')
    
    if (action2 == '') {
        $('#'+ name).find('.actions > div').each(function() {
            if ($(this).hasClass(action1)) {
                if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
            } else $(this).addClass('d-none')
        })
    } else {
        $('#'+ name).find('.actions > div').each(function() {
            if ($(this).hasClass(action1) || $(this).hasClass(action2)) {
                if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
            } else $(this).addClass('d-none')
        })
        $('#'+ name).find('.modified > .msg').addClass('d-none')
    }
}
function toggleDetails(name, action) {
    $('#'+ name).find('.details').each(function() {
        if (action == 'show') {
            $(this).show()
        } else {
            $(this).hide()
        }
    })
}
function recoverNextValue(name) {
    if (name == 'event') {
        loadDataSitu('categoryLevel1', $('#data-categoryLevel1').attr('data-id'))
        toggleDetails('categoryLevel1', 'show')
        loadDataSitu('categoryLevel2', $('#data-categoryLevel2').attr('data-id'))
        toggleDetails('categoryLevel2', 'show')
    } else if (name == 'categoryLevel1') {
        loadDataSitu('categoryLevel2', $('#data-categoryLevel2').attr('data-id'))
        toggleDetails('categoryLevel2', 'show')
    }
}
function emptyNextSelect(indexFormData) {
    $(':nth-child('+ indexFormData +')').nextAll('.formData').find('select').empty()
}
function hideCurrentActions(name) {
    $('#'+ name).find('.actions > div').each(function() {
        $(this).addClass('d-none')
    })
}
function hideNextActions(indexFormData) {
    $(':nth-child('+ indexFormData +')').nextAll('.formData')
            .find('.actions > div').each(function() {
                $(this).addClass('d-none')
            })
}

function checkValue(name, valid) {
    
    $('#'+ name).attr('data-validate', '').attr('data-modified', '')

    // If value is changed
    if ($('#'+ name).find('select').val() != $('#data-'+ name).attr('data-id')) {

        if (valid == 0) {
            removeInfo(name, 'all')
            addInfo(name, 'done', 'modified')
            addInfo(name, 'todo', 'doValidate')
            addActionBtn(name, 'modified', 'validate')                    
        } else {           
            removeInfo(name, 'all')
            addInfo(name, 'done', 'modified')
            addActionBtn(name, 'modified', '')
        }
        $('#'+ name).attr('data-modified', 1)
        toggleDetails(name, 'show')


        if (name == 'event') {
            emptyNextSelect(3)
            hideNextActions(2)
            
            removeInfo('categoryLevel1', 'all')
            addInfo('categoryLevel1', 'todo', 'doModify')
            toggleDetails('categoryLevel1', 'hide')
            $('#categoryLevel1').attr('data-validate', '').attr('data-modified', '')
            
            removeInfo('categoryLevel2', 'all')
            addInfo('categoryLevel2', 'todo', 'doModify')
            toggleDetails('categoryLevel2', 'hide')
            $('#categoryLevel2').attr('data-validate', '').attr('data-modified', '')

        } else if (name == 'categoryLevel1') {
            removeInfo('categoryLevel2', 'all')
            addInfo('categoryLevel2', 'todo', 'doModify')
            toggleDetails('categoryLevel2', 'hide')
            $('#categoryLevel2').attr('data-validate', '').attr('data-modified', '')
        }
    }
    // If value is unchanged
    else {

        if (valid == 0) {
            removeInfo(name, 'all')
            addInfo(name, 'todo', 'doValidate')
            addActionBtn(name, 'validate', '')        
        } else {           
            removeInfo(name, 'all')
            hideCurrentActions(name)
        }
        recoverNextValue(name)                
    }
    $('#form-loading').hide()
}

/**
 * 
 */
//function resetModification(name) {
function resetModification(name, indexFormData) {
    
    let currentInitialValue = $('.checkData').eq(indexFormData).attr('data-id'),
        parentInitialValue = $('.checkData').eq(indexFormData-1).attr('data-id'),
        parentFormValue = $('.formData').eq(indexFormData-1).find('select').val()
    
    // Parent value unchanged
    if (parentInitialValue == parentFormValue) {        
        $('#verify_situ_form_'+ name).val(currentInitialValue).trigger('change')
        recoverNextValue(name)
    }
    // Parent value changed
    else {
        // Reset current value
        // /!\ trigger normally here but empty select categoryLevel1 instead of null
        // $('#'+ name +' select').val(null).trigger('change')
        removeInfo(name, 'all')
        addInfo(name, 'todo', 'doModify')
        hideCurrentActions(name)
        toggleDetails(name, 'hide')
        
        // Reset empty child value - normalement inutile
        if (name == 'event') {
            console.log('forbiden event')
            location.href = "/"+ path['locale'] + '/back/user/forbiden';
        }
        if (name == 'categoryLevel1')  {
            // /!\ because of bizarre bug - reload to pour reset categoryLevel1 choice
            var eventId = $('#event select').val()
            $('#event select').val(eventId).trigger('change')
            
            emptyNextSelect(2)
            hideNextActions(2)
            removeInfo('categoryLevel2', 'all')
            addInfo('categoryLevel2', 'todo', 'doModify')
            $('#categoryLevel2 .ct-descr').hide()
        }
        else if (name == 'categoryLevel1')  {
            // /!\ because of bizarre bug on categoryLevel1
            $('#'+ name +' select').val(null).trigger('change')
        }
    }
}

function sequentialLoaderFormSitu() {
    $.when(loadDataSitu('lang', $('#data-lang').attr('data-id'))).then(function() {
        $.when(loadDataSitu('event', $('#data-event').attr('data-id'))).then(function() {
            $.when(loadDataSitu('categoryLevel1', $('#data-categoryLevel1').attr('data-id'))).then(function() {
                loadDataSitu('categoryLevel2', $('#data-categoryLevel2').attr('data-id'))
            })
        })
    })
}

$(function() {
    
    initSelect2('.card-verify select')
    
    $(document).ajaxComplete(function () {
        $('#loader').hide()        
    })
    
    $('#translator').on('change', 'select', function() {
        $('#resetGGT, #situGGT').removeClass('d-none')
    })
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $('#resetGGT, #situGGT').addClass('d-none')
    })
    
    
    sequentialLoaderFormSitu()
    
    $('form').on('change',  '#verify_situ_form_lang, '
                            +'#verify_situ_form_event, '
                            +'#verify_situ_form_categoryLevel1, '
                            +'#verify_situ_form_categoryLevel2', function() {
        
        $('#form-loading').show()
        
        if ($(this).val() != '') changeAction($(this))

        else {
            let name = $(this).parents('.formData').attr('id')
            
            $('#'+ name).attr('data-validate', '').attr('data-modified', '')
            removeInfo(name, 'all')
            addInfo(name, 'todo', 'doModify')
            hideCurrentActions(name)
            toggleDetails(name, 'hide')

            if (name == 'event') {
                emptyNextSelect(2)
                hideNextActions(1)
                removeInfo('categoryLevel1', 'all')
                addInfo('categoryLevel1', 'todo', 'doModify')
                toggleDetails('categoryLevel1', 'hide')
                $('#categoryLevel1').attr('data-validate', '').attr('data-modified', '')
                removeInfo('categoryLevel2', 'all')
                addInfo('categoryLevel2', 'todo', 'doModify')
                toggleDetails('categoryLevel2', 'hide')
                $('#categoryLevel2').attr('data-validate', '').attr('data-modified', '')

            } else if (name == 'categoryLevel1') {
                emptyNextSelect(2)
                hideNextActions(2)
                removeInfo('categoryLevel2', 'all')
                addInfo('categoryLevel2', 'todo', 'doModify')
                toggleDetails('categoryLevel2', 'hide')
                $('#categoryLevel2').attr('data-validate', '').attr('data-modified', '')
            }
            $('#form-loading').hide()
        }
        
    })
    
    $('.validate').click(function() {
        let name = $(this).addClass('d-none').parents('.formData').attr('id')
        
        $('#'+ name).attr('data-validate', 1)
        $('#'+ name +' .modified').addClass('d-none')
        if ($('#'+ name +' .validated').hasClass('d-none'))
            $('#'+ name +' .validated').removeClass('d-none')
        removeInfo(name, 'todo')
        addInfo(name, 'done', 'validated')
    })
    
    $('.modified').click(function() {
        let name = $(this).parents('.formData').attr('id'),
            index = $(this).parents('.formData').index()
        resetModification(name, index)
    })
    
    /**
     * Submission
     */
    $('#save-btn, #submit-btn').click(function(){
        
        
    })
})
