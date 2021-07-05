// css
import '../scss/situ_create_app.scss'

// js

/**
 * Load datas or create them depending on User action
 */
// Init forms
function initSelectData(dataId) {
    var select = '<select id="create_situ_form_'+ dataId +'"'
                    +' name="create_situ_form['+ dataId +']"'
                    +' class="form-control custom-select"></select>'
    return select
}
function initCreateData(dataId) {
    var mb = dataId  == 'event' ? ' mb-0' : ''
    var fields =
        '<div id="create_situ_form_'+ dataId +'"><div class="form-group mt-1' + mb + '">'
            +'<label for="create_situ_form_'+ dataId +'_title" class="required">Titre :</label>'
            +'<input type="text" id="'+ dataId +'_title"'
                +' name="create_situ_form['+ dataId +'][title]" required="required"'
                +' placeholder="'+ translations[dataId +'-titlePlaceholder'] +'" class="form-control">'
            +'</div>'
    if (dataId == 'categoryLevel1' || dataId == 'categoryLevel2') {
        fields +=
            '<div class="mb-0 form-group">'
            +'<label for="create_situ_form_'+ dataId +'_description" class="required">Description :</label>'
            +'<textarea id="create_situ_form_'+ dataId +'_description"'
                +' name="create_situ_form['+ dataId +'][description]" required="required" rows="3"'
                +' placeholder="'+ translations[dataId +'-descriptionPlaceholder'] +'" class="form-control"></textarea>'
            +'</div>'
    }
    fields += '</div>'
    return fields
}

// Action when option select is changed
function changeAction(selectId) {
    var nextSelectId = selectId.parents('.formData').next().find('select').attr('id'),
        lastData = 'categoryLevel2',
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    loadCategories($form, data, selectId, '#'+ nextSelectId, lastData)
}

// Load categories if exist or create them
function loadCategories($form, data, selectId, nextSelectId, lastData) {
    
    if ($(nextSelectId).parents('.formData').hasClass('d-none')) {
        $(nextSelectId)
                .parents('.formData').removeClass('d-none').addClass('on-load')
                .children('div').each(function() {
                    $(this).animate({ opacity: 0}, 250); 
                })
    } else {
        $(nextSelectId)
                .parents('.formData').addClass('on-load')
                .children('div').each(function() {
                    $(this).animate({ opacity: 0}, 250); 
                })
    }
    
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            $(nextSelectId).replaceWith(
                $(html).find(nextSelectId)
            )
            if (!$(nextSelectId).is('select')
              && $(selectId).attr('id') == 'create_situ_form_lang') {
                $('.colDataLang').each(function(){
                    $(this).html(initCreateData($(this).parents('.formData').attr('id')))
                            .next('.colBtn').hide()
                })
                if ($('#categoryLevel1, #categoryLevel2').hasClass('d-none')
                 || $('#categoryLevel1, #categoryLevel2').hasClass('on-load'))
                    $('#categoryLevel1, #categoryLevel2').removeClass('d-none on-load')
                        .children().animate({ opacity: 1}, 250)
            } else if (!$(nextSelectId).is('select')
              && $(selectId).attr('id') == 'create_situ_form_event') {
                $('.colData').each(function(){
                    $(this).html(initCreateData($(this).parents('.formData').attr('id')))
                            .next('.colBtn').hide()
                })
                if ($('#categoryLevel1, #categoryLevel2').hasClass('d-none')
                 || $('#categoryLevel1, #categoryLevel2').hasClass('on-load'))
                    $('#categoryLevel1, #categoryLevel2').removeClass('d-none on-load')
                        .children().animate({ opacity: 1}, 250)
            } else if ($(nextSelectId).is('select')) {
                $('.formData').each(function(){
                    $(this).find('button').remove()
                    $(this).find('span').show()
                })
            }
            if ( $(selectId).attr('id') == 'create_situ_form_lang') {
                $('.colDataLang').each(function(){
                    if ($(this).children().is('select')) $(this).next('.colBtn').show()
                    else $(this).next('.colBtn').hide()
                })
            } else if ($(selectId).attr('id') == 'create_situ_form_event') {
                $('.colData').each(function(){
                    if ($(this).children().is('select')) $(this).next('.colBtn').show()
                    else $(this).next('.colBtn').hide()
                })
            }
            if ($(nextSelectId).is('select')) {
                $(nextSelectId).find('option').each(function() {
                    if ($(this).hasClass('to-validate')) {
                        $(this).append(' '+ translations['toValidate'])
                    }
                })
            }
            
            if($(nextSelectId).parents('.formData').hasClass('on-load')) {
                $(nextSelectId)
                        .parents('.formData').removeClass('on-load')
                        .children('div').each(function() {
                            $(this).animate({ opacity: 1}, 250);
                        })
            }
        }
    })
}

// Toggle fields to keep current categories
function toggleFields(dataEntity) {
    var objFields = {
        'oldFields': [],
        'newFields': []
    }
    objFields['oldFields'].push($('#form-'+ dataEntity).html())
    objFields['newFields'].push(initCreateData(dataEntity))

    return objFields;
}
// Add Event or category level 1&2
function toggleNewFields(newFields, dataEntity) {
    // Replace select with new fields
    $('#form-'+ dataEntity).html(newFields).next('.colBtn').find('span').hide()
    // Deploy new fields depending on Add button
    if (dataEntity == 'event') {
        // If add eventID, create category level 1&2
        $('#form-categoryLevel1').html(initCreateData('categoryLevel1'))
        $('#form-categoryLevel2').html(initCreateData('categoryLevel2'))
        $('#add-categoryLevel1-btn, #add-categoryLevel2-btn').hide()
        $('#add-categoryLevel1, #add-categoryLevel2').find('button').remove()
        if ($('#categoryLevel1, #categoryLevel2').hasClass('on-load')
         || $('#categoryLevel1, #categoryLevel2').hasClass('d-none')) {
            $('#categoryLevel1, #categoryLevel2').removeClass('d-none on-load')
                .children().animate({ opacity: 1}, 250)
        }
    } else if (dataEntity == 'categoryLevel1') {
        // If add categoryLevelId, create categoryLevel2Id
        $('#form-categoryLevel2').html(initCreateData('categoryLevel2'))
        $('#add-categoryLevel2-btn').hide()
        $('#add-categoryLevel2').find('button').remove()
        if ($('#categoryLevel2').hasClass('on-load')
         || $('#categoryLevel2').hasClass('d-none')) {
            $('#categoryLevel2').removeClass('d-none on-load')
                .children().animate({ opacity: 1}, 250)
        }
    }
}
// Reset Event or category level 1&2 select
function toggleOldFields(oldFields, dataEntity) {
    var resetNewFields = 
        $('<button type="button" class="m-0 pt-0 px-0 border-0 btn bg-transparent h6 text-danger">'
            +'<i class="fas fa-times-circle"></i>'
            +'</button>')
        $('#add-'+ dataEntity).append(resetNewFields)

    resetNewFields.on('click', function() {
        // Get back data from select
        $('#form-'+ dataEntity).html(oldFields).next('.colBtn').find('span').show()
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
    })
}
    

/**
 * Add SituItem collection functions
 */
// Get the ul that holds the collection of tags
var collectionHolder = $('#situItems')

// Add Delete button for each situItem added
function addSituItemLiDeleteButton(situItemLi) {
    var removeFormButton = 
            $('<button type="button" class="btn btn-outline-danger float-right mt-1">'
                +'<i class="far fa-trash-alt"></i>'
            +'</button>')
    situItemLi.prepend(removeFormButton)
    
    removeFormButton.on('click', function() {
        // If Current value is defined
        if (situItemLi.find('select').val() != '') {
            // Get current value
            var scoreSelected = situItemLi.find('select').val()
            // For each SituItemLi select score
            collectionHolder.find('select').each(function() {
                // Check options
                $(this).find('option').each(function() {
                    // If current value = current option from loop
                    if (scoreSelected == $(this).attr('data-id')) {
                        $(this).show()
                    }
                    $(this).removeAttr('disabled')
                })
            })
        }
        situItemLi.remove()
        // Hide Adding button when all options are selected
        if (collectionHolder.find('li').length < 4 ) {
            $('#add-situ').show()
        }
    })
}

// Add reset button for each score situItem seleted
function addResetSituItemScoreButton(scoreSelect){
    var resetChoiceButton = 
            $('<button type="button" class="h5 border-0 bg-transparent position-absolute d-none">'
                +'<i class="fas fa-times-circle"></i>'
                +'</button>')
    scoreSelect.after(resetChoiceButton)
    resetChoiceButton.on('click', function() {

        // Get value into Action change
        var scoreSelected = scoreSelect.val()
            
        // If Action change value is defined
        if (scoreSelected != '') {

            // For each SituItemLi select score
            collectionHolder.find('select').each(function() {

                // Check options
                $(this).find('option').each(function() {
                    // If value from Action change is current option from loop
                    // Remove a fake disabled
                    if ($(this).attr('data-id') == scoreSelected) {
                        $(this).show()
                    } else {
                        $(this).removeAttr('disabled')
                    }
                })

            })
        }
        // Reset Current select
        $(this).addClass('d-none').prev('select').removeClass('bg-readonly')
                .find('option[value=""]').prop('selected', true)
    })
}

// Update free or used scores options for each situItem score selected
function updateAllScores(newValue, oldValue) {
    // Check all Score select fields
    collectionHolder.find('select').each(function() {

        // Value from current select
        var currentValue = $(this).val()

        // Check options from current select
        $(this).find('option').each(function() {

            // If new value into Action change
            if (oldValue == '') {

                // If new value from Action change is current option from loop
                if ($(this).attr('data-id') == newValue) {

                    $(this).hide()
                    // If Value from current select is defined
                    if (currentValue != '') {
                        // Show Reset SituItemScore Button
                        $(this).parent().next('button').removeClass('d-none')

                    } else {
                        $(this).parent().next('button').addClass('d-none')
                    }

                } else if ($(this).attr('data-id') == '') {
                    $(this).parent().next('button').addClass('d-none')
                }

            }
        })
    })
}

// Update free or used scores options for current situItem score action
function updateCurrentScore(newElem) {
    var oldValue = ''
    newElem.find('select').on('focus', function () {
        // Get old value
        oldValue = this.value
    }).change(function(){
    // When change value
        // Get new value
        var newValue = $(this).val()

        // If new value
        if (oldValue == '') {
            $(this).find('option').each(function(){
                $(this).removeAttr('selected');
                if ($(this).attr('data-id') == newValue) {
                    $(this).attr('selected', 'selected')
                // Make a fake disabled
                } else if ($(this).attr('value') == '') {
                    $(this).hide()
                } else {
                    $(this).attr('disabled', 'disabled')
                }
                $(this).parent().addClass('bg-readonly')
            })
        }

        // Check all score selects
        updateAllScores(newValue, oldValue)
    })
}

// Check Items value to show footer
function checkField(formKO, newElem, field) {
    newElem.find(field).each(function() {
        if($(this).val() == '') formKO += 1
        else formKO += 0
    })
    return formKO
}
function showFooter(newElem) {
    newElem.find('select, input, textarea').on('keyup paste change', function() {
        var formKO = 0
        formKO += checkField(formKO, newElem, 'select')
        formKO += checkField(formKO, newElem, 'input')
        formKO += checkField(formKO, newElem, 'textarea')
        if (formKO == 0 && $('.card-footer').hasClass('d-none')) {
            $('.card-footer').delay(1500).removeClass('d-none')
                    .animate({ opacity: 1}, 500);
        }
    })
}

// Add itemSitu from prototype
function addSituItem(button) {

    var list = $(button.attr('data-list-selector'))
    var counter = list.attr('data-widget-counter') || list.children().length
    var newWidget = list.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    list.attr('data-widget-counter', counter)

    var newElem = $(list.attr('data-widget-situItems')).html(newWidget)

    var selected = []
    collectionHolder.find('select').each(function() {
        if ($(this).val() != '') {
            selected.push($(this).val())
        }
    })

    newElem.find('option').each(function() {
        if (selected.includes($(this).attr('data-id'))) {

            if ($(this).is('[selected]')) $(this).removeAttr('selected')
            $(this).hide()
        }
        if ($(this).attr('data-id') == 0) $(this).hide()
    })
    addSituItemLiDeleteButton(newElem)
    addResetSituItemScoreButton(newElem.find('select'))
    updateCurrentScore(newElem)
    newElem.appendTo(list)
    showFooter(newElem)

    // Hide Adding button when all options are selected
    if (collectionHolder.find('li').length == 4 ) {
        $('#add-situ').hide()
    }
}
    
/**
 * Submission
 */
// Set status value depending on submitted button
function submissionStatus(buttonId) {
    var statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 2
    $('#create_situ_form_statusId').val(statusId)
}


$(function() {
    
    // Load translation if need
    $('body').find('select').each(function() {
        $(this).find('option').each(function() {
            if ($(this).hasClass('to-validate')) {
                $(this).append(' '+ translations['toValidate'])
            }
        })
    })
    
    if ( !$('#create_situ_form_event').is('select')
       && $('#categoryLevel1').hasClass('d-none')
       && $('#categoryLevel2').hasClass('d-none') ) {
        $('#categoryLevel1, #categoryLevel2').removeClass('d-none on-load')
                .children().animate({ opacity: 1}, 250)
    }
    
    // Ajustments
    $('#create_situ_form_lang option').first().addClass('text-dark')
    $('.colDataLang').each(function(){
        if (!$(this).children().is('select')) $(this).next().find('span').hide()
    })
    
    /**
     * Load events/categories or create them
     */
    // Load data only on select
    if ($('#create_situ_form_lang').is('select')
     || $('#create_situ_form_event').is('select')) {
        
        $('form').on('change',  '#create_situ_form_lang, '
                                +'#create_situ_form_event, '
                                +'#create_situ_form_categoryLevel1, '
                                +'#create_situ_form_categoryLevel2', function() {
            if ($(this).is('select')) {                
                
                // Reset selects before action
                if ($(this).attr('id') == 'create_situ_form_lang') {
                    $('#categoryLevel1, #categoryLevel2').addClass('d-none')
                    if ($(this).val != '') $(this).addClass('text-capitalize')
                    else {
                        if ($(this).hasClass('text-capitalize')) {
                            $(this).removeClass('text-capitalize')
                        }
                    }
                    $('.formData').each(function(){
                        $(this).find('.colDataLang')
                                .html(initSelectData($(this).attr('id')))
                    })
                } else if ($(this).attr('id') == 'create_situ_form_event') {
                    $('#categoryLevel2').addClass('d-none')
                    $('.formData').each(function(){
                        $(this).find('.colData')
                                .html(initSelectData($(this).attr('id')))
                    })
                } else if ($(this).attr('id') == 'create_situ_form_categoryLevel1') {
                    $(this).parents('.formData').next().find('.colData')
                        .html(initSelectData('categoryLevel2'))
                } else {
                    if ($('.card-body').hasClass('d-none')) {
                        $('.card-body').removeClass('d-none')
                                .animate({ opacity: 1}, 500);
                    }
                }
            }
            // Load event/categories on create it in ajax
            changeAction($(this))
        })
        
    } else {
        // If no optional language neither no eventId, change Bootstrap class & hide add button
        $('.colBtn').each(function(){
            $(this).hide()
        })
    }
    
    // Toogle Add / Select data
    $('form').on('click', '#add-event-btn, #add-categoryLevel1-btn, '+
            '#add-categoryLevel2-btn', function() {
        var objFields = toggleFields($(this).attr('data-id'))
        toggleNewFields(objFields['newFields'], $(this).attr('data-id'))
        toggleOldFields(objFields['oldFields'], $(this).attr('data-id'))
    })
    

    /**
     * Add SituItem collection
     */
    // Add a delete link to all of the existing situItem form li elements
    collectionHolder.find('li').each(function() {
        addSituItemLiDeleteButton($(this))
    })
    
    // Init once required
    $(document).ready(function() {
        addSituItem($('#add-itemSitu-link'))
    });
    
    // Than add until 4 itemSitu
    $('#add-itemSitu-link').click(function () {
        addSituItem($(this))
    })
    
    
    /**
     * Submission
     */
    $('#save-btn, #submit-btn').click(function(){
        submissionStatus($(this).attr('id'))
    })
    
})
