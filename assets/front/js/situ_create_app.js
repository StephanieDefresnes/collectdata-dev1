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

// Recalculate the "footer" height, because of fullscreenslide
// #footerEnd filling the empty space and hides fullscreenslide after footer
function footerHeight() {
   
    let windowHeight = $(window).height()
    let lastHeight = $('body').height()

    if (windowHeight > lastHeight)
        $('#footerEnd').height(windowHeight - lastHeight)

    let footerEnd = setInterval(function() {
        let newHeight = $('body').height()
        let contentHeight = newHeight - $('#footerEnd').height()

        if (lastHeight != newHeight) lastHeight = newHeight;
        if (windowHeight > contentHeight)
            $('#footerEnd').height(windowHeight - contentHeight)
        else {
            $('#footerEnd').height(0)
            clearInterval(footerEnd)
        }
    }, 100)
}

/**
 * Select data functions
 */
// Action change on select
function changeSelect(selectId) {
    let nextSelectId = selectId.parents('.formData').next().find('.colForm').attr('id'),
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    loadSelectData($form, data, selectId, '#'+ nextSelectId)
}

// Show field with effect on action change
function removeClass(id, classes) {
    if (id.hasClass(classes))
        id.removeClass(classes).children().animate({ opacity: 1}, 250)
}

// Toogle adding data button
function toggleAddingButton(colClass, event) {
    if (event == 'hide')
        $(colClass).each(function(){ $(this).next('.colBtn').hide() })
    else {
        $(colClass).each(function(){
            if ($(this).children().is('select')) $(this).next('.colBtn').show()
            else $(this).next('.colBtn').hide()
        })
    }
}

// Add comment to unvalidated user options (not yet validated)
function unvalidatedOption(element) {
    $(element).find('option').each(function() {
        if ($(this).hasClass('to-validate'))
            $(this).append(' '+ translations['toValidate'])
    })
}

// Get Event or Category data depending on value
//  - show edit button if not yet validated
//  - show category description
function getData(name, value) {
    let data, url, categoryLevel1, categoryLevel2
            
    if (name == 'event') {
        url = '/front/ajaxGetEvent'
        data = {'event': value}
    } else {
        url = '/front/ajaxGetCategory'
        
        if (name == 'categoryLevel1') categoryLevel1 = value
        else categoryLevel2 = value
        data = {
            'categoryLevel1': categoryLevel1,
            'categoryLevel2': categoryLevel2,
        }
    }
           
    $.ajax({
        url: url,
        method: 'POST',
        data: {data},
        success: function (data) {
            // Category description
            if (name != 'event') {
                $('#'+ name).find('.description').text(data['description'])
                toggleInfoCollapse('show', $('#form-'+ name))
            }
            // Edit button
            if (data['id']) addEditButton(name, data['id'])
            
            // Hide loader at the end of loop (if necessary)
            if (name == 'categoryLevel2')
                $(document).ajaxSuccess(function() { $('#loader').hide() })
        }
    })
}

// Toggle category description from collapse
function toggleInfoCollapse(action, selector) {
    selector.parents('.formData').find('.editEntity').each(function() {
        $(this).remove()
    })
    
    if (action == 'hide') {
        // Hide current
        selector
                .parents('.formData').find('.pointer').each(function(){
                     $(this).removeClass('pointer')
                })
                .parents('.formData').find('.infoCollapse').each(function(){
                    $(this).addClass('d-none')
                })
        // Hide next
        selector.parents('.formData').nextAll().each(function(i, obj){
            $(obj).find('.pointer').each(function(){
                $(this).removeClass('pointer')
            }).parents('.formData').find('.infoCollapse').each(function(){
                $(this).addClass('d-none')
            })
        })
    } else {
        // Show current
        selector.parents('.formData').find('.linkCollapse').addClass('pointer')
                                    .find('label').addClass('pointer')
            .parents('.formData').find('.infoCollapse').each(function(){
                if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
            })
    }
}

/**
 * Load options select if exist or create new
 */
function loadSelectData($form, data, selectId, nextSelectId) {
    if ($(selectId).is('select')) {
        let nextSelectParent = $(nextSelectId).parents('.formData')

        if ($(selectId).val() != '') {
            // Show card-header loader
            nextSelectParent.addClass('on-load')
                    .children('div').each(function() {
                        $(this).css('opacity', 0); 
                    })
            if (nextSelectParent.hasClass('d-none'))
                nextSelectParent.removeClass('d-none')
        } else  nextSelectParent.addClass('d-none on-load')
        
        // Load data from form eventListener
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                
                // Load next fields
                let nextAll = $(selectId).parents('.formData').nextAll()
                nextAll.each(function(i, obj){
                    let objId = $(obj).find('.colForm').attr('id')
                    
                    // Destroy select2
                    if ($('#'+ objId).data('select2'))
                        $('#'+ objId).select2('destroy')
                    
                    // Replace from ajax
                    $('#'+ objId).replaceWith($(html).find('#'+ objId))
                    
                    // Init select if necessary
                    if ($('#'+ objId).is('select')) initSelect2('#'+ objId) 
                })
                
                // If data have to be created (no data exists)
                if (!$(nextSelectId).is('select')) {
                    
                    // Hide adding buttons
                    if ($(selectId).attr('id') == 'situ_form_lang')
                        toggleAddingButton('.colDataLang', 'hide')
                    else if ($(selectId).attr('id') == 'situ_form_event')
                        toggleAddingButton('.colData', 'hide')
                    
                    // Show all header fields container (event shown by default)
                    removeClass($('#categoryLevel1, #categoryLevel2'), 'd-none') 
                    removeClass($('#categoryLevel1, #categoryLevel2'), 'on-load')
                }
                // If options select exist (data exists)
                else {
                    // Add info in case of user has data not yet validated
                    unvalidatedOption(nextSelectId)
                    
                    initSelect2(nextSelectId)
                    
                    // Show column of adding/remove buttons
                    if ( $(selectId).attr('id') == 'situ_form_lang')
                        toggleAddingButton('.colDataLang', 'show')
                    else if ($(selectId).attr('id') == 'situ_form_event')
                        toggleAddingButton('.colData', 'show')
                    
                    // Reset adding/remove buttons in case of necessity
                    $('.formData').each(function(){
                        $(this).find('.btnRemove').remove()
                        $(this).find('.btnAdd').show()
                    })
                }
                
                // Show next select container
                if ($(selectId).val() != '') {
                    if(nextSelectParent.hasClass('on-load')) {
                        nextSelectParent
                                .removeClass('d-none on-load')
                                .children('div').each(function() {
                                    $(this).animate({ opacity: 1}, 250);
                                })
                    }
                    if (selectId == 'situ_form_categoryLevel2') {
                        if ($('.card-body').hasClass('d-none')
                                && $('.card-footer').hasClass('d-none')) {
                            $('.card-body, .card-footer')
                                    .removeClass('d-none')
                                    .animate({ opacity: 1}, 250)
                        }
                    }
                } else {
                    nextSelectParent.addClass('d-none on-load')
                            .children('div').each(function() {
                                $(this).css('opacity', 0); 
                            })
                }
                footerHeight()
            }
        })
    }
}

/**
 * Create data instead of choosing an option if user wants to
 */
// Set fields
function loadCreateData(button) {
    
    let $form = button.closest('form'),
        currentId = button.parents('.formData').find('.colForm').attr('id'),
        nextAll = button.parents('.formData').nextAll(),
        data = {}
    data[button.attr('name')] = true
        
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            
            // Set & show current entity new fields
            // - Destroy current select2
            if ($('#'+ currentId).data('select2'))
                $('#'+ currentId).select2('destroy')
            // - Replace current news fields
            $('#'+ currentId).replaceWith(
                $(html).find('#'+currentId)
            )
            // - Show new fields container
            removeClass($('#'+ currentId).parents('.formData'), 'd-none') 
            removeClass($('#'+ currentId).parents('.formData'), 'on-load')
            
            // Set & show next new fields
            nextAll.each(function(i, obj){
                let objId = $(obj).find('.colForm').attr('id')
                // - Destroy next select2
                if ($('#'+ objId).data('select2'))
                    $('#'+ objId).select2('destroy')
                // - Replace next news fields
                $('#'+ objId).replaceWith(
                    $(html).find('#'+ objId)
                )
                // - Hide next adding button
                $(obj).find('.colBtn').hide()
                // - Show next new fields container
                removeClass($(obj), 'd-none') 
                removeClass($(obj), 'on-load')
            })
            
            // If user wants to cancel adding
            removeCreateData(button)
        }
    });
}

// Cancel adding data
function removeCreateData(button) {
    
    // Hide Adding button & add Reset button
    button.addClass('d-none')
    let dataEntity = button.parents('.formData').attr('id'),
        resetButton = 
        $('<button type="button" '
            +'class="btn btnRemove mt-1 px-0 border-0">'
            +'<i class="fas fa-times-circle text-danger bg-light"></i>'
            +'</button>')
    $('#add-'+ dataEntity).append(resetButton)

    // Cancel adding
    resetButton.on('click', function() {
        
        // Get back data from select
        let prevSelect = $('#'+ dataEntity).prev().find('select'),
            prevSelectValue = prevSelect.val()
        $(prevSelect).val(prevSelectValue).trigger('change')
        
        // Remove Reset button & show current Adding button
        $(this).remove()
        button.removeClass('d-none')
        
        // Show next Adding buttons
        let nextAll = $(this).parents('.formData').nextAll()
        nextAll.each(function(i, obj){ $(obj).find('.colBtn').show() })
        
        // Update footer height if necessary
        footerHeight()
    })
}

/**
 * Update entity not yet validated
 */
// Add edit button depending on getData() result
function addEditButton(name, id) {
    
    // Add edit button
    let updateButton = $('<button type="button" id="situ_form_edit-'+ name +'" '
            +'name="situ_form[edit-'+ name +']" '
            +'class="editEntity d-flex bg-none border-0 mx-2 py-2 small" '
            +'data-toggle="tooltip" data-placement="top" '
            +'title="'+ translations["update"] +'">'
            +'<i class="fas fa-edit bg-none text-light"></i></button>')
    $('#'+ name + ' > .d-flex').append(updateButton)
    editEntity(name, updateButton, id)
}
// Get form into modal
function editEntity(name, button, id) {
    button.on('click', function() {
        $('#loader').show()
        let $form = button.closest('form'),
            data = {}
        data[button.attr('name')] = id
        
        // Set modal
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                $('#loader').show()
                $('#editEntity').attr('data-entity', name).attr('data-id', id)
                $('#editEntity .modal-body').html(
                    $(html).find('#situ_form_'+name)
                )
                $('#editEntity h5').text(translations[name+ 'Update'])
                $('#editEntity').modal('show')
            }
        });
    })
}

// editEntity() flash result
function setFlash(type, msg) {
    let icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle',
        flash = '<span class="icon text-'+ type +'"><i class="fas '+ icon +'"></i></span>'
                +'<span class="msg">'+ msg +'</span>'
    return flash
}

// Update with url depending on entity requested form modal
function updateEntity() {
    let type, data = {},
        url = $('#editEntity').attr('data-entity') === 'event'
                    ? '/front/ajaxUpdateEvent'
                    : '/front/ajaxUpdateCategory',
        entity = $('#editEntity').attr('data-entity'),
        id = $('#editEntity').attr('data-id')

    // Set data values
    data['id'] =            id,
    data['title'] =         $('#editEntity').find('input[type=text]').val(),
    data['description'] =   entity != 'event'
                                ? $('#editEntity').find('textarea').val() : ''
                                
    $.ajax({
        url: url,
        method: 'POST',
        data: {data},
        success: function (response) {
        
            // Set flash message
            if (response.success) type = 'success'
            else type = 'warning'
            $('#contentMessage').empty()
                    .append(setFlash(type, response.msg))
            $('#flash_message').show()
            
            // Reset update button
            $('#'+ entity).find('.editEntity').remove()
            
            // Replace new title
            $('#situ_form_'+ entity +' option').each(function() {
                if ($(this).val() == id) {
                    
                    // Replace option text
                    $(this).text($('#editEntity input').val())
                    unvalidatedOption($(this).parent()) 
                    
                    // Reinit select2 to set new text option
                    initSelect2($(this).parent())
                    $(this).parent().val(id).trigger('change')
                    
                    // Remove next update entity buttons
                    $(this).parents('.formData').next().find('.editEntity').each(function() {
                        $(this).remove()
                    })
               }
            })
            
            // Reload current category description
            if (entity == 'categoryLevel1' || entity == 'categoryLevel2') {
                toggleInfoCollapse('hide', $('#form-'+ entity))
            }
            
            // Empty modal
            $('#editEntity').attr('data-entity', '').attr('data-id', '')
            $('#editEntity h5, #editEntity .modal-body').empty()
            
            $('#loader').hide()
        }
    });
    
}

/**
 * Add SituItem collection functions
 */
// Get the container that holds the collection of situItems
const collectionHolder = $('#situItems')

// Disable score already selected
function updateScore(collectionHolder, newElem) {
    let select = newElem.find('select'),
        values = [],
        // timeout because of situItems dynamic adding
        timeout = setTimeout(function(){
            $(collectionHolder).find('select').each(function() {
                if ($(this).val() != '') values.push($(this).val())
            })
            // Ckeck all except first situItem (success score)
            if (select.attr('id') !== 'situ_form_situItems_0_score') {
                if (values.length > 0) {
                    select.find('option').each(function() {
                        // If value selected disabled it from newElem
                        if (values.includes($(this).val())) {
                            $(this).addClass('bg-readonly').prop('disabled', true)
                            clearTimeout(timeout)
                        }
                    })
                }
            }
        }, 500);
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

// Add css class to empty option score selection to set placeholder later
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

// Toggle style depending on selection value
function toggleClassSelection(newElem) {
    newElem.find('select').on('change', function() {
        if ($(this).val() == '' && $(this).hasClass('selection-on'))
            $(this).removeClass('selection-on')
        else $(this).addClass('selection-on')
    })
}

// Update all options for each score when adding a new situItem
function newScore() {
    collectionHolder.find('select').each(function() {
        
        if ($(this).attr('id') != 'situ_form_situItems_0_score' )
            $(this).find('option[value="0"]').remove()
        
        
        let newValue = '', oldValue = ''
        $(this).on('focus', function () { oldValue = this.value })
                .change(function(){
                    // Add a class to current
                    $(this).addClass('onSelect')
                    newValue = $(this).val()
                    checkScores(newValue, oldValue)
                    setScorePlaceholder($(this), newValue)
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
                if (oldValue != ''
                        && $(this).val() == oldValue
                        && $(this).hasClass('selected')) {
                    $(this).removeClass('selected')
                }
            })
        }
        // Any else Score selection
        else {
            $(this).find('option').each(function() {
                if ($(this).val() != 0) {
                    // Disable selected option from current Score selection
                    if ($(this).val() == newValue && newValue != '') {
                        $(this).addClass('bg-readonly').prop('disabled', true)
                    }
                    // Enable unselected option from current Score selection
                    if ($(this).val() == oldValue && oldValue != '') {
                        $(this).removeClass('bg-readonly').prop('disabled', false)
                    }
                }
            })
        }
        // And the end of change, reset current Score selection
        if ($(this).hasClass('onSelect')) $(this).removeClass('onSelect')
    })
}

// Set Score selection placeholder text
function setScorePlaceholder(select, newValue) {
    if (newValue == '')
        select.find('.placeholder').text(translations['scoreLabel'])
    else
        select.find('.placeholder').text(translations['scoreLabelAlt'])
}

// Add situItem from prototype
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
 * When update Situ
 */
// Show content & check selected SituItems
function updateSitu() {

    // Show all card sections
    $('#event, #categoryLevel1, #categoryLevel2, .card-body, .card-footer')
            .removeClass('d-none').css('opacity', 1)
    
    // Header fields
    $('.formData').each(function() {
        // Hide header loader
        if ($(this).hasClass('on-load') && $(this).find('select').val() != '')
            $(this).removeClass('on-load')
        
        // Show collapsed
        $(this).find('.infoCollapse').each(function() {
            if ($(this).hasClass('d-none')) $(this).removeClass('d-none')
        })
        
        // Show data
        if ($(this).attr('id') != 'lang') {
            let value = $(this).find('select').val()
            if (value) getData($(this).attr('id'), value)
        }
    })
    
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
            setScorePlaceholder($(this), $(this).val())
        })
        
        // Add class to SituItems scores
        addPlaceholderClass('')
        
        // Allow management SituItems score changes
        newScore()
        
        // Allow remove SituItems
        $('.removeSituItem').each(function() {
            removeSituItem($(this))
        })
        
        $('#loader').hide()
    }
    // Add button display
    if (collectionHolder.find('.situItem').length == 4) $('#add-situItem').hide()
}

// Add BS error class to empty field if ErrorForm exist
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

// Set values to submit
function modalSubmit() {
    $('#modalLang').text(
            $('#situ_form_lang option[value="'+$('#situ_form_lang').val()+'"]')
                .text()
            )
    
    let eventInput = $('#form-event').find('input').val(),
        eventTitle = eventInput === undefined 
                                ?  $('#situ_form_event option[value="'+$('#situ_form_event').val()+'"]').text()
                                : eventInput
    $('#modalEvent').text(eventTitle)
    
    let categoryLevel1Input = $('#form-categoryLevel1').find('textarea').val(),
        categoryLevel1Title = categoryLevel1Input === undefined 
                                ? $('#situ_form_categoryLevel1 option[value="'+$('#situ_form_categoryLevel1').val()+'"]').text()
                                : categoryLevel1Input
    $('#modalCategoryLevel1-title').text(categoryLevel1Title)
    let categoryLevel1Text = $('#form-categoryLevel1').find('textarea').val(),
        categoryLevel1Description = categoryLevel1Text === undefined 
                                ? $('#categoryLevel1 .description').text()
                                : categoryLevel1Text
    $('#modalCategoryLevel1-description').text(categoryLevel1Description)
    
    let categoryLevel2Input = $('#form-categoryLevel2').find('textarea').val(),
        categoryLevel2Title = categoryLevel2Input === undefined 
                                ? $('#situ_form_categoryLevel2 option[value="'+$('#situ_form_categoryLevel2').val()+'"]').text()
                                : categoryLevel2Input
    $('#modalCategoryLevel2-title').text(categoryLevel2Title)
    let categoryLevel2Text = $('#form-categoryLevel2').find('textarea').val(),
        categoryLevel2Description = categoryLevel2Text === undefined 
                                ? $('#categoryLevel2 .description').text()
                                : categoryLevel2Text
    $('#modalCategoryLevel2-description').text(categoryLevel2Description)
    
    $('#modalTitle').text($('#situ_form_title').val())
    $('#modalDescription').text($('#situ_form_description').val())
    
    $('#situItems').find('.situItem').each(function(index) {
        if (index == 0) {
            $('#modalSuccess .title').text($('#situ_form_situItems_0_title').val())
            $('#modalSuccess .description').text($('#situ_form_situItems_0_description').val())
        } else {
            let prototype = $('#modalSituItems').attr('data-prototype')
            
            let scoreValue =
                    $('#situ_form_situItems_'+ index +'_score option[value="'
                            +$('#situ_form_situItems_'+ index +'_score').val()
                        +'"]').attr('class')
            scoreValue = scoreValue.replace('selectable ', '').replace(' selected', '')
                
            let item = prototype.replace(/__item__/g, translations["item"])
                    .replace(/__scoreTitle__/g, translations["scoreTitle"])
                    .replace(/__score__/g, scoreValue)
                    .replace(/__scoreText__/g, translations[scoreValue+"Item"])
                    .replace(/__titleItem__/g, translations["titleItem"])
                    .replace(/__title__/g, $('#situ_form_situItems_'+ index +'_title').val())
                    .replace(/__descriptionItem__/g, translations["descriptionItem"])
                    .replace(/__description__/g,$('#situ_form_situItems_'+ index +'_description').val())
            $('#optionScore').append(item)
        }
    })
    
    $('#confirmSubmit').modal('show')
}

$(function() {
    
    // Debug focus
    $('.form-control').unbind('blur')
    
    // For dynamic tooltip
    $('body').tooltip({ selector: '.editEntity'})
    
    // When update Situ
    if ($('#situ').attr('data-id')) updateSitu()
    else {
        // Create SituItem once required
        addSituItem()
        $('#loader').hide()
    }
    
    // Init header selects
    $('.card-header').find('select').each(function() {
        unvalidatedOption(this)
        initSelect2($(this))
        // When update Situ
        if ($(this).val() != '') {
            $(this).parent().find('.select2-selection__rendered')
                    .addClass('selection-on')
        }
    })
    
    // Hide adding events/categories button if must have to be created (choice empty)
    $('.colDataLang').each(function(){
        if (!$(this).children().is('select')) $(this).next().find('.btnAdd').hide()
    })
    
    /**
     * Load events/categories or create them if choice is empty
     */
    if ($('#situ_form_lang').is('select')
     || $('#situ_form_event').is('select')) {
        
        $('form').on('change',  '#situ_form_lang, '
                                +'#situ_form_event, '
                                +'#situ_form_categoryLevel1, '
                                +'#situ_form_categoryLevel2', function() {
            // Toggle styles
            let rendered = $(this).parent().find('.select2-selection__rendered')
            if ($(this).val() == '' && rendered.hasClass('selection-on'))
                rendered.removeClass('selection-on')
            else rendered.addClass('selection-on')
            footerHeight()

            // Hide collapse
            toggleInfoCollapse('hide', $(this))
            
            // Event or Category data
            let divId = $(this).parents('.formData').attr('id')
                // Get & show data
            if (divId != 'lang' && $(this).val() != '')
                getData(divId, $(this).val())
            // Hide next header field
            if ($(this).is('select'))
                $(this).parents('.formData').nextAll().addClass('d-none')

            // Show card body on select categoryLevel2
            if ($(this).attr('id') == 'situ_form_categoryLevel2') {
                if ($('.card-body').hasClass('d-none') && $('.card-footer').hasClass('d-none')) {
                    $('.card-body, .card-footer').removeClass('d-none')
                        .animate({ opacity: 1}, 250)
                }
            }
                
            // Load data or create them on action change
            changeSelect($(this))
        })
        
    } else {
        // If create data, hide create button & show header fields
        $('.colBtn').each(function(){ $(this).hide() })
        removeClass($('#event'), 'd-none on-load') 
        removeClass($('#categoryLevel1'), 'd-none on-load') 
        removeClass($('#categoryLevel2'), 'd-none on-load') 
    }
    
    /**
     * Add new Event or Categories
     */
    $('form').on('click', '.btnAdd', function() {
        
        // Show current & next header loaders
        $(this).parents('.formData').addClass('on-load')
                .children('div').each(function() {
                    $(this).css('opacity', 0); 
                })
        $(this).parents('.formData').addClass('on-load')
                .nextAll().each(function(i, obj){
                    $(obj).addClass('on-load')
                            .children('div').each(function() {
                                $(this).css('opacity',0); 
                            })
                    // Remove next edit entity buttons
                    $(obj).find('.editEntity').remove()
                })
        // Hide useless info collapse
        toggleInfoCollapse('hide', $(this))
        
        loadCreateData($(this))
    })
    
    /**
     * Update event or categries not yet validated
     */
    $('#editEntity .btnModal').click(function() {
        if ($(this).hasClass('modalValidate')) {
            $('#editEntity').modal('hide')
            updateEntity()
        } else {
            $('#editEntity').modal('hide').attr('data-entity', '').attr('data-id', '')
            $('#editEntity h5, #editEntity .modal-body').empty()
            $('#loader').hide()
        }
    })
    
    /**
     * When translate situ
     */
    if ($('#loader').hasClass('translateSitu')) {
        
        // Load lang to set events
        $('#situ_form_lang').val($('#situ').attr('data-lang')).trigger('change')
                .parent().find('.select2-selection__rendered').addClass('selection-on')
        
        // Then hide loader
        $(document).ajaxComplete(function () {
            $('#loader').removeClass('d-block')
        });
        
        // Show situItems depending on Situ to translate
        let itemsLength = $('#initialSituItems').attr('data-initial')
        for(var i = 1; i < itemsLength; i++) {
            addSituItem()
        }
    }
    
    /**
     * Error management
     */
    if ($('#form-error > .alert').length !== 0) {
        $('.formData').each(function() {
            if ($(this).hasClass('on-load')) $(this).removeClass('on-load')
        })
        checkForm()
        $('#loader').hide()
    }
    
    /**
     * Confirm submit
     */
    $('#modalSubmit').click(function() {
        modalSubmit()
    })
    $('#cancelSubmit').bind('click', function() {
        $('#optionScore').empty()
    })
    
    
    /**
     * Then..
     */
    // Show card-body when fill categoryLevel2 description
    $('form').on('keyup paste', '#situ_form_categoryLevel2_description', function() {
        if ($('.card-body').hasClass('d-none') && $('.card-footer').hasClass('d-none')) {
            $('.card-body, .card-footer').removeClass('d-none').animate({ opacity: 1}, 250)
        }
    })
    
    // Add SituItem until 4
    $('#add-itemSitu-link').click(function() {
        addSituItem()
    })
    
    $('.card-footer > button').bind('click', function() {
        $('#loader').show() // Comment if html5 browser is disabled (attr novalidate)
        $('#form-error').empty()
    })
    
})