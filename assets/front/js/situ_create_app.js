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

function flashMessage(status) {
    $('body').find('#flash_message').remove()
    let i = status == 'success' ? '<i class="fas fa-check-circle"></i>'
                                : '<i class="fas fa-exclamation-circle"></i>'
    let textClass = status == 'success' ? 'text-success' : 'text-danger'
    let flashMessage =
            '<div id="flash_message" class="container">'
                +'<div class="alert alert-secondary alert-dismissible px-3 fade show" role="alert">'
                        +'<span class="sr-only">'+ translations['srOnly-'+status] +'</span>'
                        +'<span class="icon '+ textClass +'">'+ i +'</span>'
                        +'<span class="msg">'+ translations['flashError'+status] +'</span>'
                +'</div>'
            +'</div>'
    $('body > .container-fluid').before(flashMessage)
    window.scrollTo({top: 0, behavior: 'smooth'});
    $('#flash_message').delay(3000).fadeOut(); 
}


/**
 * Load datas or create them depending on User action
 */
// Init forms
function initSelectData(dataId) {
    let select = '<select id="create_situ_form_'+ dataId +'"'
                    +' name="create_situ_form['+ dataId +']"'
                    +' class="form-control custom-select"></select>'
    return select
}
function initCreateData(dataId) {
    let mb = dataId  == 'event' ? ' mb-0' : ''
    let fields =
        '<div id="create_situ_form_'+ dataId +'">'
            +'<div class="form-group mt-1' + mb + '">'
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

// Action change on select
function changeEvent(selectId) {
    let nextSelectId = selectId.parents('.formData').next().find('select').attr('id'),
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    loadOrCreateData($form, data, selectId, '#'+ nextSelectId)
}

// Show field with effect on action change
function showField(id, classes) {
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
        // Show loader
        nextSelectParent.addClass('on-load')
                .children('div').each(function() {
                    $(this).css({ opacity: 0}); 
                })
        if (nextSelectParent.hasClass('d-none')) nextSelectParent.removeClass('d-none')    
        
    }
    else nextSelectParent.addClass('d-none on-load')
    
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
                
                if ($(selectId).attr('id') == 'create_situ_form_lang')
                    toggleAddingButton('.colDataLang', 'hide')
                else if ($(selectId).attr('id') == 'create_situ_form_event')
                    toggleAddingButton('.colData', 'hide')
                
                showField($('#categoryLevel1, #categoryLevel2'), 'd-none') 
                showField($('#categoryLevel1, #categoryLevel2'), 'on-load')
                
            } else {
            // If options select exist
            
                initSelect2(nextSelectId)
                
                // Show column of adding/remove buttons
                if ( $(selectId).attr('id') == 'create_situ_form_lang')
                    toggleAddingButton('.colDataLang', 'show')
                else if ($(selectId).attr('id') == 'create_situ_form_event')
                    toggleAddingButton('.colData', 'show')
                
                // Reset adding/remove buttons
                $('.formData').each(function(){
                    $(this).find('.btnRemove').remove()
                    $(this).find('.btnAdd').show()
                })
                unvalidatedOption(nextSelectId)
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
        }
    })
}

// Toggle fields to keep current categories
function toggleFields(dataEntity) {
    let objFields = {
        'oldFields': [],
        'newFields': []
    }
    $('#create_situ_form_'+ dataEntity).select2('destroy');
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
    })
} 

/**
 * Add SituItem collection functions
 */
// Get the ul that holds the collection of tags
let collectionHolder = $('#situItems')

function addInfoTooltip(situItemLi) {
    let info =
            '<span class="p-2 score-info" data-toggle="tooltip" data-placement="right"'
                +' title="'+ translations['scoreInfo'] +'">'
                +'<i class="far fa-question-circle"></i></span>'
        
    situItemLi.find('.label-score').append(info)
    situItemLi.find('.score-info').tooltip()
}

// Add Delete button for each situItem added
function addSituItemLiDeleteButton(situItemLi) {
    let removeLiBtn = 
            $('<button type="button" class="btn btn-outline-danger float-right mt-1 mx-3">'
                +'<i class="far fa-trash-alt"></i>'
            +'</button>')
    situItemLi.prepend(removeLiBtn)
    
    $(removeLiBtn).on('click', function() {
        // If Current value is defined
        if (situItemLi.find('select').val() != '') {
            // Get current value
            let scoreSelected = situItemLi.find('select').val()
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
            $('#add-situItem').show()
        }
    })
}

// Add Score selection info tooltip
function addInfo(scoreSelect) {
    let info = '<span class="p-2 score-info" data-toggle="tooltip" data-placement="right"'
                +' title="'+ translations['scoreInfo'] +'">'
                +'<i class="far fa-question-circle"></i>'
    $('.score-info').tooltip()
    scoreSelect.after(info)
}

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

// Check Items value to show footer
function checkField(formKO, newElem, field) {
    newElem.find(field).each(function() {
        if($(this).val() == '') formKO += 1
        else formKO += 0
    })
    return formKO
}
function showFooter(elem) {
    elem.find('select, input, textarea').on('keyup paste change', function() {
        let formKO = 0
        formKO += checkField(formKO, elem, 'select:not(#create_situ_form_situItems_0_score)')
        formKO += checkField(formKO, elem, 'input')
        formKO += checkField(formKO, elem, 'textarea')
        if (formKO == 0 && $('.card-footer').hasClass('d-none'))
            $('.card-footer').removeClass('d-none').animate({ opacity: 1}, 250)
    })
}

// Add itemSitu from prototype
function addSituItem(button) {

    let list = $(button.attr('data-list-selector'))
    let counter = list.attr('data-widget-counter') || list.children().length
    let newWidget = list.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    list.attr('data-widget-counter', counter)

    let newElem = $(list.attr('data-widget-situItems')).html(newWidget)

    // Update newElem depending on scores already selected
    let selected = []
    collectionHolder.find('select').each(function() {
        if ($(this).val() != '' && $(this).val() != 0) {
            selected.push($(this).val())
        }
    })
    newElem.find('option').each(function() {
        if (selected.includes($(this).attr('data-id'))) {
            $(this).addClass('bg-readonly').prop('disabled', true)
        }
        $(this).removeAttr('selected')
    })
    
    addInfoTooltip(newElem)
    addSituItemLiDeleteButton(newElem)  
    addPlaceholderClass(newElem)
    toggleClassSelection(newElem)
    newElem.appendTo(list)
    newScore()
    showFooter(newElem)

    // Hide Adding button when all options are selected
    if (collectionHolder.find('li').length == 4 ) {
        $('#add-situItem').hide()
    }
}
    
/**
 * Submission
 */
// Set status value depending on submitted button
function submissionStatus(buttonId) {
    let statusId;
    buttonId == 'save-btn' ? statusId = 1 : statusId = 2
    $('#create_situ_form_statusId').val(statusId)
}

// Set data if selected or created
function setData(entity) {
    let data;
    if (!$('#create_situ_form_'+entity).is('select')) {
        if (entity != 'event') {
            data = {
                'title':        $('input[name="create_situ_form['+ entity +'][title]"]').val(),
                'description':  $('textarea[name="create_situ_form['+ entity +'][description]"]').val(),
            }
        } else
            data = {
                'title': $('input[name="create_situ_form['+ entity +'][title]"]').val()
            }
    }
    else data = $('#create_situ_form_'+ entity).val()
    
    return data
}

// Send data Situ
function createOrUpdateSitu(dataForm) {
    $.ajax({
        url: "/"+ path['locale'] +"/ajaxCreate",
        method: 'POST',
        data: {dataForm},
        success: function(data) {
            location.href = data['redirection']['targetUrl'];
        },
        error: function() {
            flashMessage('error')
        }
    })
}

/*
 * Update Situ
 */
// Get data Translation
function selectSitu(id) {
    $.ajax({
        url: "/"+ path['locale'] +"/ajaxEdit",
        method: 'GET',
        data: { id: id },
        success: function(data) {
            $('h1').html(translations['h1Update'])
            loadData('lang', data.situ.langId)
            loadData('event', data.situ.eventId)
            loadData('categoryLevel1', data.situ.categoryLevel1Id)
            loadData('categoryLevel2', data.situ.categoryLevel2Id)
            $('#create_situ_form_title').val(data.situ.title)
            $('#create_situ_form_description').val(data.situ.description)
            loadSituItems(data.situItems, data.situItems.length)
            if(data.situItems.length == 4) $('#add-situItem').hide()
        },
        error: function() {
            flashMessage('error')
        }
    })
}
// Load Event & categories
function loadData(name, value) {
    let dataExist = setInterval(function() {
        if ($('#create_situ_form_'+ name +' option').length) {
           $('#create_situ_form_'+ name).val(value).trigger('change')
                .parent().find('.select2-selection__rendered').addClass('selection-on')
           clearInterval(dataExist)
           if (name == 'categoryLevel2') $('#loader').hide()
        }
    }, 50);
}

// Load SituItems from prototype
function loadSituItems(data, counter) {
    let list = $('#situItems')
    
    // Reset persistent SituItem
    list.find('li').remove()
    
    // Reload all SituItems
    for (let i = 0; i < counter; i++) {
        let newWidget = list.attr('data-prototype')
        newWidget = newWidget.replace(/__name__/g, i)
        let newElem = $(list.attr('data-widget-situItems')).html(newWidget)
        
        addInfoTooltip(newElem)
        addSituItemLiDeleteButton(newElem)
        addPlaceholderClass(newElem)
        loadItemsValue(data, newElem, i)
        toggleClassSelection(newElem)
        newElem.appendTo(list)
        newScore()
        showFooter(newElem)
    }
    list.attr('data-widget-counter', counter)
}

// Load Values fields
function loadItemsValue(data, newElem, i) {
    $(newElem).find('input').val(data[i].title)
    $(newElem).find('textarea').val(data[i].description)
    $(newElem).find('select option').each(function() {
        if ($(this).val() == '') {
            $(this).text(translations['scoreLabelAlt'])
        }
        else if ($(this).val() == data[i].score) {
            $(this).prop('selected', true).addClass('selected')
            $(this).parent().addClass('selection-on')
        } else {
            $(this).addClass('bg-readonly').prop('disabled', true)
        }
    })
}

$(function() {
    
    // User lang or optional user lang empty case
    if ($('#create_situ_form_lang option').length <= 2) $('#lang').remove()
    
    initSelect2('.card-header select')

    // Load translation if need
    $('body').find('select').each(function() {
        unvalidatedOption(this)
    })
    
    // Show fields if no event exist on locale
    if (!$('#create_situ_form_event').is('select')) {
        showField($('#categoryLevel1'), 'd-none on-load') 
        showField($('#categoryLevel2'), 'd-none on-load') 
    }
    
    // Hide adding data button if data must have to be created (choices empty)
    $('.colDataLang').each(function(){
        if (!$(this).children().is('select')) $(this).next().find('.btnAdd').hide()
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
                
                let rendered = $(this).parent().find('.select2-selection__rendered')
                if ($(this).val() == '' && rendered.hasClass('selection-on'))
                    rendered.removeClass('selection-on')
                else rendered.addClass('selection-on')
                    
                // Reset selects before loading data
                if ($(this).attr('id') == 'create_situ_form_lang') {
                    if ($(this).val() == '') $('#event').addClass('d-none')
                    $('#categoryLevel1, #categoryLevel2').addClass('d-none')
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
                    if ($('.card-body').hasClass('d-none'))
                        $('.card-body').removeClass('d-none').animate({ opacity: 1}, 250)
                }
            }
            // Load data or create them on action change
            changeEvent($(this))
        })
        
    } else {
        // If no optional language neither no eventId, hide add button
        $('.colBtn').each(function(){ $(this).hide() })
        
    }
    
    // Show card-body if create categoryLevel2
    $('form').on('keyup paste', '#create_situ_form_categoryLevel2_description', function() {
        if ($('.card-body').hasClass('d-none'))
            $('.card-body').removeClass('d-none').animate({ opacity: 1}, 250)
    })
    
    // Toogle Add / Select data
    $('form').on('click', '#add-event-btn, #add-categoryLevel1-btn, '+
            '#add-categoryLevel2-btn', function() {
        let objFields = toggleFields($(this).attr('data-id'))
        toggleNewFields(objFields['newFields'], $(this).attr('data-id'))
        toggleOldFields(objFields['oldFields'], $(this).attr('data-id'))
        let entity = $(this).attr('data-id')
    })
    

    /**
     * Add SituItem collection
     */
    // Show footer depending on SituItems lenght
    collectionHolder.find('li').each(function() {
        showFooter($(this))
    })
    
    // Init once required
    addSituItem($('#add-itemSitu-link'))
    
    // Than add until 4 itemSitu
    $('#add-itemSitu-link').click(function () {
        addSituItem($(this))
    })
    
    
    /**
     * Submission
     */
    $('#save-btn, #submit-btn').click(function(){
        
        $('form').find('.form-control').each(function() {
            if ($(this).attr('id') != 'create_situ_form_situItems_0_score') {
                if ($(this).val() == '') {
                    if (!$(this).is('select')) $(this).addClass('empty-value')
                    else {
                        if ($(this).attr('data-select2-id') !== undefined) {
                            $(this).parent().find('.select2-selection__rendered')
                                .addClass('empty-value')
                        } else {
                            $(this).addClass('empty-value')
                        }
                        
                    }
                } else {
                    if (!$(this).is('select')) {
                        if ($(this).hasClass('empty-value')) $(this).removeClass('empty-value')
                    } else {
                        if ($(this).attr('data-select2-id') !== undefined) {
                            if ($(this).parent().find('.select2-selection__rendered')
                                .hasClass('empty-value')) {
                                $(this).parent().find('.select2-selection__rendered')
                                    .removeClass('empty-value')
                            }
                        } else {
                            if ($(this).hasClass('empty-value')) $(this).removeClass('empty-value')
                        }
                    }
                }
            }
        })
        
        if ($('.empty-value').length == 0) {
        
            $('#loader').show()

            let lang, event, categoryLevel1, categoryLevel2,
                title, description, statusId, id, initialId, dataForm,
                situItems = []

            submissionStatus($(this).attr('id'))

            lang = $('#create_situ_form_lang').val() == '' ? 47
                    : $('#create_situ_form_lang').val()
            event = setData('event')
            categoryLevel1 = setData('categoryLevel1')
            categoryLevel2 = setData('categoryLevel2')        
            title = $('#create_situ_form_title').val()
            description = $('#create_situ_form_description').val()
            statusId = $('#create_situ_form_statusId').val()
            id = $('#situ').attr('data-id')
            initialId = $('#situ').attr('data-initial-id')

            $('#situItems li').each(function() {
                let score, titleItem, descItem
                $(this).find('.form-control').each(function() {
                    if($(this).hasClass('score-item')) {
                        score =     $(this).val()
                    } else if($(this).hasClass('score-title')) {
                        titleItem = $(this).val()
                    } else {
                        descItem =  $(this).val()
                    }
                })
                situItems.push({
                    'score':        score,
                    'title':        titleItem,
                    'description':  descItem
                })
            })

            dataForm = {
                'id': id,
                'initialId': initialId,
                'lang': lang,
                'event': event,
                'categoryLevel1': categoryLevel1,
                'categoryLevel2': categoryLevel2,
                'title': title,
                'description': description,
                'situItems': situItems,
                'statusId': statusId
            }
            createOrUpdateSitu(dataForm)
            
        } else {
            let error =
                    '<div class="alert alert-danger mt-4" role="alert">'
                        +'<span class="icon text-danger">'
                            +'<i class="fas fa-exclamation-circle"></i>'
                            +translations['formError']
                        +'</span>'
                    +'</div>'
            $('#form-error').css('opacity', 0).empty().append(error).animate({ opacity: 1}, 250)
        }
    })
    
    /**
     * Update situ
     */
    let situId = $('#situ').attr('data-id')
    if (situId != '') {
        selectSitu(situId)
        $('#event, #categoryLevel1, #categoryLevel2, .card-body, .card-footer')
                .removeClass('d-none')
        addPlaceholderClass('')
    } else {
        if ($('#situ').attr('data-lang') == '') $('#loader').hide()
    }
    
})
