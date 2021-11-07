// css
import '../scss/situ_create_app.scss'
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

require('select2')

function initSelect2(select) {
    $(select).select2({
        minimumResultsForSearch: Infinity,
        width: 'resolve'
    });
}

function footerHeight() {
   
    let windowHeight = $(window).height()
    let lastHeight = $('body').height()

    if (windowHeight > lastHeight) {
        $('#footerEnd').height(windowHeight - lastHeight)
    }

    let footerEnd = setInterval(function() {
        let newHeight = $('body').height()
        let contentHeight = newHeight - $('#footerEnd').height()

        if (lastHeight != newHeight) {
            lastHeight = newHeight;
        }
        if (windowHeight > contentHeight) {
            $('#footerEnd').height(windowHeight - contentHeight)
        } else {
            $('#footerEnd').height(0)
            clearInterval(footerEnd)
        }
    }, 100)
}

/**
 * Load datas or create them depending on User action
 */
// Init forms
function initSelectData(dataId) {
    let select = '<select id="situ_form_'+ dataId +'"'
                    +' name="situ_form['+ dataId +']"'
                    +' class="form-control custom-select"></select>'
    return select
}
function initCreateData(dataId) {
    let mb = dataId  == 'event' ? ' mb-0' : ''
    let fields =
        '<div id="situ_form_'+ dataId +'">'
            +'<div class="form-group mt-1' + mb + '">'
                +'<label for="situ_form_'+ dataId +'_title" class="required">Titre :</label>'
                +'<input type="text" id="'+ dataId +'_title"'
                    +' name="situ_form['+ dataId +'][title]" required="required"'
                    +' placeholder="'+ translations[dataId +'-titlePlaceholder'] +'" class="form-control">'
            +'</div>'
    if (dataId == 'categoryLevel1' || dataId == 'categoryLevel2') {
        fields +=
            '<div class="mb-0 form-group">'
                +'<label for="situ_form_'+ dataId +'_description" class="required">Description :</label>'
                +'<textarea id="situ_form_'+ dataId +'_description"'
                    +' name="situ_form['+ dataId +'][description]" required="required" rows="3"'
                    +' placeholder="'+ translations[dataId +'-descriptionPlaceholder'] +'" class="form-control"></textarea>'
            +'</div>'
    }
    fields += '</div>'
    return fields
}

// Action change on select
function changeEvent(selectId) {
    let nextSelectId = selectId.parents('.formData').next().find('select').attr('id'),
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    loadOrCreateData($form, data, selectId, '#'+ nextSelectId)
}

// Show field with effect on action change
function removeClass(id, classes) {
    if (id.hasClass(classes))
        id.removeClass(classes).children().animate({ opacity: 1}, 250)
}

// Toogle adding data button
function toggleAddingButton(colClass, event) {
    if (event == 'hide') {
        $(colClass).each(function(){
            $(this).html(initCreateData($(this).parents('.formData').attr('id')))
                    .next('.colBtn').hide()
        })
    } else {
        $(colClass).each(function(){
            if ($(this).children().is('select')) $(this).next('.colBtn').show()
            else $(this).next('.colBtn').hide()
        })
    }
}

// Add comment to unvalidated user option
function unvalidatedOption(element) {
    $(element).find('option').each(function() {
        if ($(this).hasClass('to-validate')) {
            $(this).append(' '+ translations['toValidate'])
        }
    })
}

// Load options select if exist or create new (eventListener)
function loadOrCreateData($form, data, selectId, nextSelectId) {
    
    let nextSelectParent = $(nextSelectId).parents('.formData')
    
    if ($(selectId).val() != '') {
        // Show card-header loader
        nextSelectParent.addClass('on-load')
                .children('div').each(function() {
                    $(this).css({ opacity: 0}); 
                })
        if (nextSelectParent.hasClass('d-none')) nextSelectParent.removeClass('d-none')
        
    }
    else nextSelectParent.addClass('d-none on-load')
        
    toggleInfoCollapse('hide', selectId)
    
    // Load data from eventListener
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            $(nextSelectId).replaceWith(
                $(html).find(nextSelectId)
            )
            // If data have to be created
            if (!$(nextSelectId).is('select')) {
                
                if ($(selectId).attr('id') == 'situ_form_lang')
                    toggleAddingButton('.colDataLang', 'hide')
                else if ($(selectId).attr('id') == 'situ_form_event')
                    toggleAddingButton('.colData', 'hide')
                
                removeClass($('#categoryLevel1, #categoryLevel2'), 'd-none') 
                removeClass($('#categoryLevel1, #categoryLevel2'), 'on-load')
        
                toggleInfoCollapse('hide', selectId)
                
            } else {
            // If options select exist
            
                initSelect2(nextSelectId)
                
                // Show column of adding/remove buttons
                if ( $(selectId).attr('id') == 'situ_form_lang')
                    toggleAddingButton('.colDataLang', 'show')
                else if ($(selectId).attr('id') == 'situ_form_event')
                    toggleAddingButton('.colData', 'show')
                else toggleCollapse(nextSelectParent, 'hide')
                
                // Reset adding/remove buttons
                $('.formData').each(function(){
                    $(this).find('.btnRemove').remove()
                    $(this).find('.btnAdd').show()
                })
                unvalidatedOption(nextSelectId)
        
                toggleInfoCollapse('showNext', selectId)
            }
            
            // Show next selection container
            if ($(selectId).val() != '') {
                if(nextSelectParent.hasClass('on-load')) {
                    nextSelectParent.removeClass('d-none on-load')
                            .children('div').each(function() {
                                $(this).animate({ opacity: 1}, 250);
                            })
                }
            } else {
                nextSelectParent.addClass('d-none on-load')
                        .children('div').each(function() {
                            $(this).css({ opacity: 0}); 
                        })
            }
            footerHeight()
        }
    })
}

// Get Event & Categorie data depending on action change
function getData(name, value) {
    let categoryLevel1, categoryLevel2, dataForm
    
    if (name == 'categoryLevel1') categoryLevel1 = value
    if (name == 'categoryLevel2') categoryLevel2 = value

    dataForm = {
        'categoryLevel1': categoryLevel1,
        'categoryLevel2': categoryLevel2,
    }
    ajaxGetData(name, dataForm)
}

// Toggle i & content collapse
function toggleCollapse(selectParent, action) {
    selectParent.find('.infoCollapse').each(function() {
        if (action == 'show') {
            if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
        } else $(this).addClass('d-none')
    })
}

// Load Categories description on action change
function ajaxGetData(name, dataForm) {
    $.ajax({
        url: "/"+ path['locale'] +"/situ/ajaxGetData",
        method: 'POST',
        data: {dataForm},
        success: function(data) {
            if (data[name])
                $('#'+ name).find('.description').text(data[name])
                toggleCollapse($('#'+ name), 'show')
        }
    })
}

// Toggle fields to keep current categories
function toggleFields(dataEntity) {
    let objFields = {
        'oldFields': [],
        'newFields': []
    }
    $('#situ_form_'+ dataEntity).select2('destroy');
    objFields['oldFields'].push($('#form-'+ dataEntity).html())
    objFields['newFields'].push(initCreateData(dataEntity))

    return objFields;
}

// Add Data
function toggleNewFields(newFields, dataEntity) {
    
    // Replace select with new fields
    $('#form-'+ dataEntity).html(newFields).next('.colBtn').find('.btnAdd').hide()
    
    // Deploy new fields depending on Add button click
    if (dataEntity == 'event') {
        
        // If add event, create categories too and show fields
        $('#form-categoryLevel1').html(initCreateData('categoryLevel1'))
        $('#form-categoryLevel2').html(initCreateData('categoryLevel2'))
        
        // Reset adding/remove buttons
        $('#add-categoryLevel1-btn, #add-categoryLevel2-btn').hide()
        $('#add-categoryLevel1, #add-categoryLevel2').find('.btnRemove').remove()
        
        // And show fields for adding data
        if ($('#categoryLevel1, #categoryLevel2').hasClass('on-load')
         || $('#categoryLevel1, #categoryLevel2').hasClass('d-none')) {
            $('#categoryLevel1, #categoryLevel2').removeClass('d-none on-load')
                .children().animate({ opacity: 1}, 250)
        }
        
    } else if (dataEntity == 'categoryLevel1') {
        // If add category Level 1, create category Level
        $('#form-categoryLevel2').html(initCreateData('categoryLevel2'))
        
        // Reset adding/remove buttons
        $('#add-categoryLevel2-btn').hide()
        $('#add-categoryLevel2').find('.btnRemove').remove()
        
        // And show fields for adding data
        if ($('#categoryLevel2').hasClass('on-load')
         || $('#categoryLevel2').hasClass('d-none')) {
            $('#categoryLevel2').removeClass('d-none on-load')
                .children().animate({ opacity: 1}, 250)
        }
    }
}

// Reset Event or category level 1&2 select
function toggleOldFields(oldFields, dataEntity) {
    let resetNewFields = 
        $('<button type="button" class="btnRemove m-0 pt-0 px-0 border-0 btn bg-transparent h6 text-danger">'
            +'<i class="fas fa-times-circle"></i>'
            +'</button>')
        $('#add-'+ dataEntity).append(resetNewFields)

    resetNewFields.on('click', function() {
        // Get back data from select
        $('#form-'+ dataEntity).html(oldFields).next('.colBtn').find('.btnAdd').show()
        resetNewFields.remove()

        // Reset select depending on ResetNewFields button
        if (dataEntity == 'event') {
            $('#form-categoryLevel1').html(initSelectData('categoryLevel1'))
            $('#form-categoryLevel2').html(initSelectData('categoryLevel2'))
            $('#add-categoryLevel1-btn, #add-categoryLevel2-btn').show()
            $('#categoryLevel1, #categoryLevel2').addClass('d-none on-load')
                .children().animate({ opacity: 0}, 250)
        } else if (dataEntity == 'categoryLevel1') {
            $('#form-categoryLevel2').html(initSelectData('categoryLevel2'))
            $('#add-categoryLevel2-btn').show()
            $('#categoryLevel2').addClass('d-none on-load')
                .children().animate({ opacity: 1}, 250)
        }
        initSelect2('select')
        $('select').each(function() {
            if ($(this).val() != '')
                $(this).parent().find('.select2-selection__rendered').addClass('selection-on')
        })
        footerHeight()
    })
} 

/**
 * Add SituItem collection functions
 */
// Get the ul that holds the collection of tags
const collectionHolder = $('#situItems')

// Add a placeholder class to empty option score selection
function addPlaceholderClass(newElem) {
    if (newElem == '') {
        collectionHolder.find('select').each(function() {
            $(this).find('option').each(function() {
                if ($(this).val() == '') $(this).addClass('placeholder')
            })
        })
    } else {
        newElem.find('select').each(function() {
            $(this).find('option').each(function() {
                if ($(this).val() == '') $(this).addClass('placeholder')
            })
        })
    }
}

// Toggle Score selection placeholder text
function togglePlaceholder(select, newValue) {
    if (newValue == '')
        select.find('.placeholder').text(translations['scoreLabel'])
    else
        select.find('.placeholder').text(translations['scoreLabelAlt'])
}

// Toggle class to placeholder if empty
function toggleClassSelection(newElem) {
    newElem.find('select').on('change', function() {
        if ($(this).val() == '' && $(this).hasClass('selection-on'))
            $(this).removeClass('selection-on')
        else $(this).addClass('selection-on')
    })
}

// Update all options for each score when adding a situItem
function newScore() {
    collectionHolder.find('select').each(function() {
        let newValue = '', oldValue = ''
        $(this).on('focus', function () { oldValue = this.value })
                .change(function(){
                    // Add a class to current
                    $(this).addClass('onSelect')
                    newValue = $(this).val()
                    checkScores(newValue, oldValue)
                    togglePlaceholder($(this), newValue)
                    $(this).find('option').each(function() {
                        if ($(this).val() == newValue && newValue != '')
                            $(this).addClass('selected')
                    })
                })
    })
}

// Update all options for each situItem score depending on new elem value
function checkScores(newValue, oldValue) {
    collectionHolder.find('select').each(function() {
        // If current Score selection
        if ($(this).hasClass('onSelect')) {
            $(this).find('option').each(function() {
                // If reset selection
                if ($(this).hasClass('selectable')
                        && oldValue != ''
                        && $(this).attr('data-id') == oldValue
                        && $(this).hasClass('selected')) {
                    $(this).removeClass('selected')
                }
            })
        }
        // Any else Score selection
        else {
            $(this).find('option').each(function() {
                if ($(this).hasClass('selectable')) {
                    // Disable selected option from current Score selection
                    if ($(this).attr('data-id') == newValue && newValue != '') {
                        $(this).addClass('bg-readonly').prop('disabled', true)
                    }
                    // Enable unselected option from current Score selection
                    if ($(this).attr('data-id') == oldValue && oldValue != '') {
                        $(this).removeClass('bg-readonly').prop('disabled', false)
                    }
                }
            })
        }
        // And the end of change, reset current Score selection
        if ($(this).hasClass('onSelect')) $(this).removeClass('onSelect')
    })
}

// Check selected SituItems when updating Situ
function updateSitu() {

    // Show all card sections
    $('#event, #categoryLevel1, #categoryLevel2, .card-body, .card-footer')
            .removeClass('d-none').animate({ opacity: 1}, 250)
    
    // Hide header loader
    $('.formData').each(function() {
        if ($(this).hasClass('on-load') && $(this).find('select').val() != '')
            $(this).removeClass('on-load')
    })
    
    // Show collapsed
    if ($('#situ').attr('data-id') != '') {
        $('.infoCollapse').each(function() {
            if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
        })
    }
    
    // Check SituItems
    if (collectionHolder.find('.situItem').length > 1) {
        let values = []
        
        collectionHolder.find('select').each(function() {
            values.push($(this).val())
        })
        
        // Check SituItems scores
        collectionHolder.find('select').each(function() {
            $(this).addClass('selection-on')
            
            let value = $(this).val()
            
            $(this).find('option').each(function() {
                if ($(this).val() == value)
                    $(this).addClass('selected')
                else if (values.includes($(this).val()))
                    $(this).addClass('bg-readonly').prop('disabled', true)
                else if ($(this).val() == '')
                    $(this).text(translations['scoreLabelAlt'])
            })
            
            // Allow updating placeholder on change
            togglePlaceholder($(this), $(this).val())
        })
        
        // Add class to SituItems scores
        addPlaceholderClass('')
        
        // Allow management SituItems score changes
        newScore()
        
        // Allow remove SituItems
        $('.removeSituItem').each(function() {
            removeSituItem($(this))
        })
    }
    
    // Add button display
    if (collectionHolder.find('.situItem').length == 4) $('#add-situItem').hide()
}

// Delete situItem with confirm alert
function removeSituItem(button) {
    let divItem = button.parents('.situItem')
    
    button.on('click', function() {
        divItem.addClass('to-confirm')
        $.confirm({
            animation: 'scale',
            closeAnimation: 'scale',
            animateFromElement: false,
            columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
            type: 'red',
            typeAnimated: true,
            title: translations['deleteItem-title'],
            content: translations['deleteItem-content'],
            buttons: {
                cancel: {
                    text: translations['no'],
                    action: function () {
                        divItem.removeClass('to-confirm')
                    }
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        // If Current value is defined
                        if (divItem.find('select').val() != '') {
                            // Get current value
                            let scoreSelected = divItem.find('select').val()
                            // For each SituItemLi select score
                            collectionHolder.find('select').each(function() {
                                $(this).find('option').each(function() {
                                    if ($(this).val() == scoreSelected)
                                        $(this).removeClass('bg-readonly').removeAttr('disabled')
                                })
                            })
                        }
                        divItem.remove()
                        // Hide Adding button when all options are selected
                        if (collectionHolder.find('.situItem').length < 4 ) {
                            $('#add-situItem').show()
                        }
                    }
                }
            },
        })
    })
}

function updateScore(collectionHolder, newElem) {
    let select = newElem.find('select')
    
    // To hide value success
    if (select.attr('id') !== 'situ_form_situItems_0_score') {
        select.find('option').each(function() {
            if ($(this).attr('data-id') == 0) {
                $(this).removeAttr('selected')
            }
        })
    }
            
    // timeout because og dynamic fields added
    let values = [],
        timeout = setTimeout(function(){
        $(collectionHolder).find('select').each(function() {
            if ($(this).val() != '' && $(this).val() != 0) {
                values.push($(this).val())
            }
        })

        if (select.attr('id') !== 'situ_form_situItems_0_score') {
            if (values.length > 0) {

                select.find('option').each(function() {
                    // If value selected disabled it from newElem
                    if (values.includes($(this).attr('data-id'))) {
                        $(this).addClass('bg-readonly').prop('disabled', true)
                        clearTimeout(timeout)
                    }
                })
            }
        }
    }, 500);
}


// Add itemSitu from prototype
function addSituItem() {
    let counter = collectionHolder.attr('data-widget-counter') || collectionHolder.children().length
    let newWidget = collectionHolder.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    collectionHolder.attr('data-widget-counter', counter)

    let newElem = $(collectionHolder.attr('data-widget-situItems')).html(newWidget)

    updateScore(collectionHolder, newElem) 
    removeSituItem(newElem.find('.removeSituItem'))
    addPlaceholderClass(newElem)
    toggleClassSelection(newElem)
    newElem.find('.score-info').tooltip()
    newElem.appendTo(collectionHolder)
    newScore()

    // Hide Adding button when all options are selected
    if (collectionHolder.find('.situItem').length == 4 ) {
        $('#add-situItem').hide()
    }
}
    
/**
 * Submission
 */
// Set data if selected or created
function setData(entity) {
    let data;
    if (!$('#situ_form_'+entity).is('select')) {
        if (entity != 'event') {
            data = {
                'title':        $('input[name="situ_form['+ entity +'][title]"]').val(),
                'description':  $('textarea[name="situ_form['+ entity +'][description]"]').val(),
            }
        } else
            data = {
                'title': $('input[name="situ_form['+ entity +'][title]"]').val()
            }
    }
    else data = $('#situ_form_'+ entity).val()
    
    return data
}

// Send data Situ
function createOrUpdateSitu(dataForm) {
    $.ajax({
        url: "/"+ path['locale'] +"/situ/ajaxCreate",
        method: 'POST',
        data: {dataForm},
        success: function(data) {
            if (data.success) location.href = '/'+ path["locale"] +'/my-contribs'
            else location.reload()
        }
    })
}

function checkForm() {
    $('form').find('.form-control').each(function() {
        if ($(this).attr('id') != 'situ_form_situItems_0_score') {
            if ($(this).val() == '') {
                if (!$(this).is('select')) $(this).addClass('is-invalid')
                else {
                    if ($(this).attr('data-select2-id') !== undefined) {
                        $(this).parent().find('.select2-selection__rendered')
                            .addClass('is-invalid')
                    } else {
                        $(this).addClass('is-invalid')
                    }

                }
            } else {
                if (!$(this).is('select')) {
                    if ($(this).hasClass('is-invalid')) $(this).removeClass('is-invalid')
                } else {
                    if ($(this).attr('data-select2-id') !== undefined) {
                        if ($(this).parent().find('.select2-selection__rendered')
                            .hasClass('is-invalid')) {
                            $(this).parent().find('.select2-selection__rendered')
                                .removeClass('is-invalid')
                        }
                    } else {
                        if ($(this).hasClass('is-invalid')) $(this).removeClass('is-invalid')
                    }
                }
            }
        }
    })
}

function toggleInfoCollapse(toogle, selectId) {
    if (toogle == 'hide') {
        $(selectId).parents('.formData').nextAll().each(function(){
            $(this).find('.btn-collapse').removeClass('pointer')
            $(this).find('.infoCollapse ').each(function(){ $(this).addClass('d-none') })
        })
    } else {
        $(selectId).parents('.formData').next().find('.btn-collapse').addClass('pointer')
                .parents('.formData').find('.infoCollapse ').each(function(){
                    $(this).addClass('d-none')
                })
    }
}

function sendData(btn) {
    let action, lang, event, categoryLevel1, categoryLevel2,
        title, description, id, translatedSituId,
        dataForm, situItems = []


    action = $(btn).attr('id')
    lang = $('#situ_form_lang').val()
    event = setData('event')
    categoryLevel1 = setData('categoryLevel1')
    categoryLevel2 = setData('categoryLevel2')        
    title = $('#situ_form_title').val()
    description = $('#situ_form_description').val()
    id = $('#situ').attr('data-id')
    translatedSituId = $('#situ_form_translatedSituId').val()

    $('#situItems').find('.situItem').each(function() {
        let scoreItem, titleItem, descItem
        $(this).find('.form-control').each(function() {
            if($(this).hasClass('score-item')) {
                scoreItem =     $(this).val()
            } else if($(this).hasClass('score-title')) {
                titleItem = $(this).val()
            } else {
                descItem =  $(this).val()
            }
        })
        situItems.push({
            'score':        scoreItem,
            'title':        titleItem,
            'description':  descItem
        })
    })

    dataForm = {
        'id': id,
        'translatedSituId': translatedSituId,
        'lang': lang,
        'event': event,
        'categoryLevel1': categoryLevel1,
        'categoryLevel2': categoryLevel2,
        'title': title,
        'description': description,
        'situItems': situItems,
        'action': action,
    }
    createOrUpdateSitu(dataForm)
}

// Load translation lang & show event field
function loadTranslation(langId) {
    $('#situ_form_lang').val(langId).trigger('change')
         .parent().find('.select2-selection__rendered').addClass('selection-on')
 
    let dataExist = setInterval(function() {
        if ($('#situ_form_event option').length) {
            $('#loader').removeClass('d-block')
            clearInterval(dataExist)
        }
    }, 50);
}

$(function() {
    
    // Preselect user lang if no optional exists
    if ($('#situ_form_lang option').length <= 2)    
        $('#situ_form_lang').val($('#situ').attr('data-default')).trigger('change')
    
    // When update Situ
    if ($('#situ').attr('data-id') != '') updateSitu()
    // Create SituItem once required
    else addSituItem()
    
    $('.card-header').find('select').each(function() {
        unvalidatedOption(this)
        initSelect2($(this))
        // When update Situ
        $(this).parent().find('.select2-selection__rendered')
                .addClass('selection-on')
    })
    
    // Hide adding events/categories button if must have to be created (choices empty)
    $('.colDataLang').each(function(){
        if (!$(this).children().is('select')) $(this).next().find('.btnAdd').hide()
    })
    
    $('#loader').hide()
    
    /**
     * Load events/categories or create them
     */
    // Load data only on select
    if ($('#situ_form_lang').is('select')
     || $('#situ_form_event').is('select')) {
        
        $('form').on('change',  '#situ_form_lang, '
                                +'#situ_form_event, '
                                +'#situ_form_categoryLevel1, '
                                +'#situ_form_categoryLevel2', function() {
            
            let divId = $(this).parents('.formData').attr('id')
            if (divId == 'categoryLevel1' || divId == 'categoryLevel2') {
                toggleCollapse($(this).parents('.formData'), 'hide')
            }
            
            if ($(this).is('select')) {
                
                let rendered = $(this).parent().find('.select2-selection__rendered')
                if ($(this).val() == '' && rendered.hasClass('selection-on'))
                    rendered.removeClass('selection-on')
                else rendered.addClass('selection-on')
                
                // Load category description
                if ($(this).val() != '') {
                    if (divId == 'categoryLevel1' || divId == 'categoryLevel2') {
                        getData(divId, $(this).val())
                    }
                }
                
                // Hide next header forms
                $(this).parents('.formData').nextAll().addClass('d-none')
                
                // Reset selects before loading data
                if ($(this).attr('id') == 'situ_form_lang') {
                    $('.formData').each(function(){
                        $(this).find('.colDataLang').html(initSelectData($(this).attr('id')))
                    })                  
                } else if ($(this).attr('id') == 'situ_form_event') {
                    $('.formData').each(function(){
                        $(this).find('.colData').html(initSelectData($(this).attr('id')))
                    })                    
                } else if ($(this).attr('id') == 'situ_form_categoryLevel1') {                    
                    $(this).parents('.formData').next().find('.colData')
                        .html(initSelectData('categoryLevel2'))                
                } else {
                    // Show card body on select categoryLevel2
                    if ($('.card-body').hasClass('d-none') && $('.card-footer').hasClass('d-none')) {
                        $('.card-body, .card-footer').removeClass('d-none')
                            .animate({ opacity: 1}, 250)
                    }
                }
            }
            // Load data or create them on action change
            changeEvent($(this))
        })
        
    } else {
        $('.colBtn').each(function(){ $(this).hide() })
        removeClass($('#event'), 'd-none on-load') 
        removeClass($('#categoryLevel1'), 'd-none on-load') 
        removeClass($('#categoryLevel2'), 'd-nonloadTranslatione on-load') 
    }
    
    // Show card-body if create categoryLevel2
    $('form').on('keyup paste', '#situ_form_categoryLevel2_description', function() {
        if ($('.card-body').hasClass('d-none') && $('.card-footer').hasClass('d-none')) {
            $('.card-body, .card-footer').removeClass('d-none').animate({ opacity: 1}, 250)
        }
    })
    
    // Add or Select Event & Categories
    $('form').on('click', '#add-event-btn, #add-categoryLevel1-btn, '+
            '#add-categoryLevel2-btn', function() {
        let objFields = toggleFields($(this).attr('data-id'))
        toggleNewFields(objFields['newFields'], $(this).attr('data-id'))
        toggleOldFields(objFields['oldFields'], $(this).attr('data-id'))
    })
    
    // Add SituItem until 4
    $('#add-itemSitu-link').click(function () {
        addSituItem()
    })    
    
    // When translate situ
    if ($('#translation-situ').length == 1) {
    
        loadTranslation($('#situ').attr('data-lang'))

        // Show info
        $('#details').find('.infoCollapse').each(function() {
            $(this).addClass('d-none')
        })
        // Show situItems depending on Situ to translate
        let itemsLength = $('#initialSituItems').attr('data-initial')
        for(var i = 1; i < itemsLength; i++) {
            $('#add-itemSitu-link').trigger('click')
        }
    }
    
    /**
     * Submission
     */
    $('#save, #submit').click(function(){
        $('#loader').show()
        checkForm()
        
        if ($('.is-invalid').length == 0) {
             sendData($(this))            
        } else {
            let error =
                    '<div class="alert alert-danger" role="alert">'
                        +'<span class="icon text-danger">'
                            +'<i class="fas fa-exclamation-circle"></i>'
                            + translations['formError']
                        +'</span>'
                    +'</div>'
            $('#form-error').css('opacity', 0).empty().append(error).animate({ opacity: 1}, 250)
            $('html, body').animate({scrollTop: $('#page').offset().top })
            $('#loader').hide()
        }
    })
    
})
