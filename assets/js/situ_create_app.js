// css
import '../css/situ_create_app.scss'

// js

/**
 * Load categories or create them functions
 */
// Init forms
function initSelectData(dataId) {
    var select = '<select id="create_situ_form_'+ dataId +'"'
                    +' name="create_situ_form['+ dataId +']"'
                    +' class="form-control"></select>'
    return select
}
function initCreateData(dataId) {
    var fields =
        '<div id="create_situ_form_'+ dataId +'"><div class="form-group mt-1">'
            +'<label for="create_situ_form_'+ dataId +'_title" class="required">Titre :</label>'
            +'<input type="text" id="'+ dataId +'_title"'
                +' name="create_situ_form['+ dataId +'][title]" required="required"'
                +' placeholder="'+ translations[dataId +'-titlePlaceholder'] +'" class="form-control">'
            +'</div>'
    if (dataId == 'categoryLevel1' || dataId == 'categoryLevel2') {
        fields +=
            '<div class="form-group">'
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
function changeCategory(selectId) {
    var nextSelectId = selectId.parents('.formData').next().find('select').attr('id'),
        lastData = 'categoryLevel2',
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    console.log(data)
    loadCategories($form, data, selectId, '#'+ nextSelectId, lastData)
}

// Load categories if exist or create them
function loadCategories($form, data, selectId, nextSelectId, lastData) {
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            $(nextSelectId).replaceWith(
                $(html).find(nextSelectId)
            )
            if (!$(nextSelectId).is('select')
              && $(selectId).attr('id') == 'create_situ_form_event') {
                $('#'+ lastData).find('.colData').html(initCreateData(lastData))
            } else {
                $(nextSelectId).parent().next().find('button').remove()
                $(nextSelectId).parent().next().find('a').show()
            }
            $('.colData').each(function(){
                if ($(this).children().is('select')) $(this).next('.colBtn').show()
                else $(this).next('.colBtn').hide()
            })
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
    $('#form-'+ dataEntity).html(newFields).next('.colBtn').find('a').hide()
    // Deploy new fields depending on Add button
    if (dataEntity == 'event') {
        // If add eventID, create category level 1&2
        $('#form-categoryLevel1').html(initCreateData('categoryLevel1'))
        $('#form-categoryLevel2').html(initCreateData('categoryLevel2'))
        $('#add-categoryLevel1-btn, #add-categoryLevel2-btn').hide()
    } else if (dataEntity == 'categoryLevel1Id') {
        // If add categoryLevelId, create categoryLevel2Id
        $('#form-categoryLevel2').html(initCreateData('categoryLevel2'))
        $('#add-categoryLevel2-btn').hide()
    }
}
// Reset Event or category level 1&2 select
function toggleOldFields(oldFields, dataEntity) {
    var resetNewFields = 
        $('<button type="button" class="pl-0 border-0 btn bg-transparent h6 text-danger">'
            +'<i class="fas fa-times-circle"></i>'
            +'</button>')
        $('#add-'+ dataEntity).append(resetNewFields)

    resetNewFields.on('click', function() {
        // Get back data from select
        $('#form-'+ dataEntity).html(oldFields).next('.colBtn').find('a').show()
        resetNewFields.remove()

        // Reset select depending on ResetNewFields button
        if (dataEntity == 'event') {
            $('#form-categoryLevel1').html(initSelectData('categoryLevel1'))
            $('#form-categoryLevel2').html(initSelectData('categoryLevel2'))
            $('#add-categoryLevel1-btn, #add-categoryLevel2-btn').show()
        } else if (dataEntity == 'categoryLevel1') {
            $('#form-categoryLevel2').html(initSelectData('categoryLevel2'))
            $('#add-categoryLevel2-btn').show()
        }
    })
}
    

/**
 * Add SituItem collection functions
 */
// Get the ul that holds the collection of tags
var collectionHolder = $('#situItems')

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

function addResetSituItemScoreButton(scoreSelect){
    var resetChoiceButton = 
            $('<button type="button" class="h5 border-0 bg-transparent position-absolute d-none">'
                +'<i class="fas fa-times-circle"></i>'
                +'</button>')
    scoreSelect.after(resetChoiceButton)
    resetChoiceButton.on('click', function() {

        // If Action change value is defined
        if (scoreSelect.parent().find('select').val() != '') {

            // Get value into Action change
            var scoreSelected = scoreSelect.parent().find('select').val()

            // For each SituItemLi select score
            collectionHolder.find('select').each(function() {

                // Check options
                $(this).find('option').each(function() {
                    // If value from Action change is current option from loop
                    // Remove a fake disabled
                    if ($(this).attr('data-id') == scoreSelected
                     || $(this).attr('value') == '') {
                        $(this).show()
                    } else {
                        $(this).removeAttr('disabled')
                    }
                    $(this).parent().removeClass('bg-readonly')
                })

            })
        }
        // Reset Current select
        $(this).addClass('d-none').parent().find('select option[value=""]')
                .prop('selected', true).parent().attr('disabled', false)
    })
}

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

// Action change
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
    
/**
 * Submission
 */
function submissionStatus(buttonId) {
    var statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 2
    $('#create_situ_form_statusId').val(statusId)
}
    
$(function() {
    
    /**
     * Load categories or create them
     */
    // Load Categories only on select
    if ($('#create_situ_form_event').is('select')) {
        $('form').on('change', '#create_situ_form_event, #create_situ_form_categoryLevel1', function() {
            if ($(this).is('select')) {
                // Reset selects before event
                if ($(this).attr('id') == 'create_situ_form_event') {
                    $('.colData').each(function(){
                        $(this).html(initSelectData($(this).parents('.formData').attr('id')))
                    })
                } else {
                    $(this).parents('.formData').next().find('.colData')
                        .html(initSelectData('categoryLevel2'))
                }
            }
            // Load categories on create it in ajax
            changeCategory($(this))
        })
    }
    // If no eventId, change Bootstrap class & hide add button
    else {
        $('#form-event').removeClass('col-11').addClass('col-12')
        $('.colData').each(function(){
            $(this).removeClass('col-11').addClass('col-12')
        })
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
    // Add a delete link to all of the existing tag form li elements
    collectionHolder.find('li').each(function() {
        addSituItemLiDeleteButton($(this))
    })
    
    $('#add-itemSitu-link').click(function () {
        
        var list = $($(this).attr('data-list-selector'))
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
        
        // Hide Adding button when all options are selected
        if (collectionHolder.find('li').length == 4 ) {
            $('#add-situ').hide()
        }
    })
    
    /**
     * Submission
     */
    $('#save-btn, #submit-btn').click(function(){
        submissionStatus($(this).attr('id'))
    })
    
})
