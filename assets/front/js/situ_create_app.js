// css
import '../scss/situ_create_app.scss'
import 'select2/src/scss/core.scss';
import 'select2-theme-bootstrap4/dist/select2-bootstrap.min.css'

require('select2')

function initSelect2( select ) {
    $(select).select2({
        minimumResultsForSearch: Infinity,
        width: 'resolve'
    });
}

// Show card-body when fill categoryLevel2 description
function showCards() {
    if ( $('.card-body').hasClass('d-none') && $('.card-footer').hasClass('d-none') ) {
        $('.card-body, .card-footer').removeClass('d-none').animate({ opacity: 1}, 250)
    }
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

        if ( lastHeight !== newHeight ) lastHeight = newHeight;
        if ( windowHeight > contentHeight )
            $('#footerEnd').height(windowHeight - contentHeight)
        else {
            $('#footerEnd').height(0)
            clearInterval(footerEnd)
        }
    }, 100)
}

// Show dynamic form section content depending on the need to create first data or not
function setDetailsFormSection() {
    if ( $('[id$="_form_lang"]').is('select') || $('[id$="_form_event"]').is('select') )
    {
        // If lang and event is select, set event JS 
        $('form').on('change', '[id$="_form_lang"], '
                                +'[id$="_form_event"], '
                                +'[id$="_form_categoryLevel1"], '
                                +'[id$="_form_categoryLevel2"]',
            function() { formChange( $(this) ) }
            
        )
        return
    }
    
    // If create data, hide create button & show all header fields
    $('.colBtn').each(function(){ $(this).hide() })

    $('.formData:not(#lang)').each(function(i, obj){
        removeClass( $(obj), 'd-none on-load' ) 
    })
}

// Show next field depending on prevent choice
function formChange( field ) {

    // Toggle styles
    let rendered = field.parent().find('.select2-selection__rendered')
    if ( '' === field.val() && rendered.hasClass('selection-on') )
        rendered.removeClass('selection-on')
    else rendered.addClass('selection-on')
    footerHeight()

    // Hide collapse
    toggleInfoCollapse( 'hide', field )

    // Event or Category data
    let divId = field.parents('.formData').attr('id')
    
    // Get & show data
    if ('lang' !== divId && '' !== field.val() )
        getData( divId, field.val() )
    
    // Hide next header field
    if ( field.is('select') )
        field.parents('.formData').nextAll().addClass('d-none')

    // Show card body on select categoryLevel2
    if ( 'situ_dynamic_data_form_categoryLevel2' === field.attr('id') ) {
        if ( $('.card-body').hasClass('d-none') && $('.card-footer').hasClass('d-none') ) {
            $('.card-body, .card-footer').removeClass('d-none')
                .animate({ opacity: 1}, 250)
        }
    }

    // Remove edit next entity buttons if exist
    field.parents('.formData').find('.editEntity').remove()
    field.parents('.formData').nextAll().each(function(i, obj){
                $(obj).find('.editEntity').remove()
            })

    // Load data or create them on action change
    changeSelect( field )
}

// Add BS is-invalid class to empty field
function checkForm() {
    $('#form-error').empty()
        
    let emptyValue = 0
    $('form').find('.form-control').each(function() {
        if ( 'situ_form_situItems_0_score' !== $(this).attr('id') ) {
            
            if ( '' === $(this).val() ) {
                if ( ! $(this).is('select') ) {
                    $(this).addClass('is-invalid')
                    emptyValue++
                    return
                }
                if ( undefined !== $(this).attr('data-select2-id') ) {
                    $(this).parent().find('.select2-selection__rendered')
                        .addClass('is-invalid')
                    emptyValue++
                    return
                }
                $(this).addClass('is-invalid')
                emptyValue++
                return
            }
            
            if ( undefined !== $(this).attr('data-select2-id') ) {
                if ( $(this).parent().find('.select2-selection__rendered')
                                        .hasClass('is-invalid') )
                {
                    $(this).parent().find('.select2-selection__rendered')
                        .removeClass('is-invalid')
                }
                return
            }
            if ( $(this).hasClass('is-invalid') ) $(this).removeClass('is-invalid')
        }
    })
    
    let formValid = emptyValue > 0 ? false : true
    
    if ( ! formValid ) {
        let msg = '<div class="alert alert-danger" role="alert">'
                +'<i class="fas fa-exclamation-circle"></i>'
                + translations["formError"]+'</div>'
        $('#form-error').append(msg)
        
        if ( $('#loader').hasClass('translateSitu') ) {
            $('#details').addClass('error-excerpt')
            $('#toggle-btn > span').addClass('alert alert-danger py-0 px-1')
        }
    }
    return formValid
}

// Set fields events
function keypressPasteCut( field ) {
    field
        .change(function() {  checkField( $(this) ) })
        .keypress(function() {  checkField( $(this) ) })
        .focusout(function() {  checkField( $(this) ) })
        .on( 'paste cut', function(e){ 
            checkField( $(this), e )
    })
}

// Check invalid fields
function checkField( elemt, e = null ) {
    
    if ( 0 === $('#form-error .alert').length ) return
    
    let select2Attr = elemt.attr('data-select2-id')
    
    if ( ! elemt.is('select') ) {
        
        if ( e && '' === e.originalEvent.clipboardData.getData('text') ) {
            elemt.addClass('is-invalid')
            return
        }
        if ( '' === elemt.val() )
        {
            elemt.addClass('is-invalid')
            return
        }
        if ( elemt.hasClass('is-invalid') ) elemt.removeClass('is-invalid')
        return
    }
    
    if ( typeof select2Attr !== 'undefined' && select2Attr !== false ) {
        if ( '' === elemt.val() ) {
            elemt.parent().find('.select2-selection__rendered')
                                .addClass('is-invalid')
            return
        }
        if ( elemt.parent().find('.select2-selection__rendered')
                                .hasClass('is-invalid') )
        {
            elemt.parent().find('.select2-selection__rendered')
                    .removeClass('is-invalid')
            return     
        }
        return
    }
    
    if ( '' === elemt.val() ) {
        elemt.addClass('is-invalid')
        return     
    }
    if ( elemt.hasClass('is-invalid') ) elemt.removeClass('is-invalid')
    return
}

/**
 * Select data functions
 */
// Action change on select
function changeSelect( selectId ) {
    let nextSelectId = selectId.parents('.formData').next().find('.colForm').attr('id'),
        $form = selectId.closest('form'),
        data = {}
    data[selectId.attr('name')] = selectId.val()
    loadSelectData( $form, data, selectId, '#'+ nextSelectId )
}

// Show field with effect on action change
function removeClass( id, classes ) {
    if ( id.hasClass(classes) )
        id.removeClass(classes).children().animate({ opacity: 1}, 250)
}

// Toogle adding data button
function toggleAddingButton( colClass, event ) {
    if ( 'hide' === event )
        $(colClass).each(function(){ $(this).next('.colBtn').hide() })
    else {
        $(colClass).each(function(){
            if ( $(this).children().is('select') ) $(this).next('.colBtn').show()
            else $(this).next('.colBtn').hide()
        })
    }
}

// Add comment to unvalidated user options (not yet validated)
function unvalidatedOption( element ) {
    $(element).find('option').each(function() {
        if ( $(this).hasClass('to-validate') )
            $(this).append(' '+ translations['toValidate'])
    })
}

// Get Event or Category data depending on value
//  - show edit button if not yet validated
//  - show category description
function getData( name, value ) {
    let categoryLevel1, categoryLevel2,
        url = '/front/ajaxGetEvent',
        data = { 'event': value }
            
    if ( 'event' !== name ) {
        if ('categoryLevel1' === name ) categoryLevel1 = value
        else categoryLevel2 = value
        
        url = '/front/ajaxGetCategory'
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
            if ( 'event' !== name ) {
                $('#'+ name).find('.description').text(data.description)
                toggleInfoCollapse( 'show', $('#form-'+ name) )
            }
            // Edit button
            if ( data.id ) addEditButton( name, data.id )
            
            // Hide loader at the end of loop (if necessary)
            if ( 'categoryLevel2' === name )
                $(document).ajaxSuccess(function() { $('#loader').hide() })
        }
    })
}

// Toggle category description from collapse
function toggleInfoCollapse( action, selector ) {
    selector.parents('.formData').find('.editEntity').each(function() {
        $(this).remove()
    })
    
    if ( 'hide' === action ) {
        // Hide current
        selector.parents('.formData').find('.pointer').each(function(){
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
        return
    }
    
    // Show current
    selector.parents('.formData').find('.linkCollapse').addClass('pointer')
                                .find('label').addClass('pointer')
        .parents('.formData').find('.infoCollapse').each(function(){
            if ( $(this).hasClass('d-none') ) $(this).removeClass('d-none')
        })
}

/**
 * Load options select if exist or create new
 */
function loadSelectData( $form, data, selectId, nextSelectId ) {
    
    if ( $('#toggle-btn').length ) $('#toggle-btn').remove()
    
    if ( $('#form-error alert').length ) checkForm()

    if ( $(selectId).is('select') ) {
        let nextSelectParent = $(nextSelectId).parents('.formData')

        if ( '' !== $(selectId).val() ) {
            // Show card-header loader
            nextSelectParent.addClass('on-load')
                    .children('div').each(function() {
                        $(this).css('opacity', 0); 
                    })
            if ( nextSelectParent.hasClass('d-none') )
                nextSelectParent.removeClass('d-none')
        } else  nextSelectParent.addClass('d-none on-load')
        
        // Load data from form eventListener
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: data,
            success: function (html) {
                $(nextSelectId).find('.editEntity').remove()
                checkField( selectId )
                
                if ( '' === $(selectId).val() ) {
                    $(selectId).parents('.formData').nextAll().each(function(i, obj){
                        $(obj).addClass('d-none')
                    })
                    footerHeight()
                    return
                }

                // Show next select container
                if ( nextSelectParent.hasClass('on-load') ) {
                    nextSelectParent
                            .removeClass('d-none on-load')
                            .children('div').each(function() {
                                $(this).animate({ opacity: 1}, 250);
                            })
                }
                
                // Load next fields
                let nextAll = $(selectId).parents('.formData').nextAll()
                nextAll.each(function(i, obj){
                    let objId = $(obj).find('.colForm').attr('id')

                    // Destroy select2
                    if ( $('#'+ objId).data('select2') )
                        $('#'+ objId).select2('destroy')

                    // Replace from ajax
                    $('#'+ objId).replaceWith( $(html).find('#'+ objId) )
                    keypressPasteCut( $('#'+ objId).find('input') )
                    keypressPasteCut( $('#'+ objId).find('textarea') )

                    // Init select if necessary
                    if ( $('#'+ objId).is('select') ) initSelect2( '#'+ objId ) 
                })

                // If data have to be created (no data exists)
                if ( ! $(nextSelectId).is('select') ) {

                    // Hide adding buttons
                    if ( 'situ_dynamic_data_form_lang' === $(selectId).attr('id') )
                        toggleAddingButton( '.colDataLang', 'hide' )
                    if ( 'situ_dynamic_data_form_event' === $(selectId).attr('id') )
                        toggleAddingButton( '.colData', 'hide' )

                    // Show all header fields container (event shown by default)
                    removeClass( $('#categoryLevel1, #categoryLevel2'), 'd-none') 
                    removeClass( $('#categoryLevel1, #categoryLevel2'), 'on-load')
                    
                    if ( 'situ_dynamic_data_form_categoryLevel2' !== $(selectId).attr('id') )
                        translationToggleBtn()
                    
                    return
                }
                
                // Add info in case of user has data not yet validated
                unvalidatedOption( nextSelectId )

                initSelect2( nextSelectId )

                // Show column of adding/remove buttons
                if ( 'situ_dynamic_data_form_lang' === $(selectId).attr('id') )
                    toggleAddingButton( '.colDataLang', 'show' )
                if ( 'situ_dynamic_data_form_event' === $(selectId).attr('id') )
                    toggleAddingButton( '.colData', 'show' )

                // Reset adding/remove buttons in case of necessity
                $('.formData').each(function(){
                    $(this).find('.btnRemove').remove()
                    $(this).find('.colBtn').show()
                    $(this).find('.btnAdd').show()
                })

                if ( 'situ_dynamic_data_form_categoryLevel2' === selectId
                        && $('.card-body').hasClass('d-none')
                        && $('.card-footer').hasClass('d-none') )
                {
                    $('.card-body, .card-footer')
                            .removeClass('d-none')
                            .animate({ opacity: 1}, 250)
                }
                
                footerHeight()
            }
        })
    }
}

/**
 * Create data instead of choosing an option if user wants to
 */
// Add new Event or Categories
function createData( button ) {
    
        // Show current & next header loaders
        button.parents('.formData').addClass('on-load')
                .children('div').each(function() {
                    $(this).css('opacity', 0); 
                })
            .parents('.formData').addClass('on-load')
                .nextAll().each(function(i, obj){
                    $(obj).addClass('on-load')
                            .children('div').each(function() {
                                $(this).css('opacity',0); 
                            })
                    // Remove next edit entity buttons
                    $(obj).find('.editEntity').remove()
                })
        // Hide useless info collapse
        toggleInfoCollapse( 'hide', button )
        
        loadCreateData( button ) 
}

// Set fields
function loadCreateData( button ) { 
    
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
            
            /**
             * Set & show current new field
             */ 
            // Destroy current select2
            if ( $('#'+ currentId).data('select2') ) $('#'+ currentId).select2('destroy')
            
            // Replace current news fields
            $('#'+ currentId).replaceWith(  $(html).find('#'+currentId) )
            
            // Set fields events js
            keypressPasteCut( $('#'+ currentId).find('input') )
            if ( $('#'+currentId).find('textarea').length )
                keypressPasteCut( $('#'+currentId).find('textarea') )
            
            // Show new fields container
            removeClass( $('#'+ currentId).parents('.formData'), 'd-none' ) 
            removeClass( $('#'+ currentId).parents('.formData'), 'on-load' )
            
            /**
             * Set & show next new fields
             */ 
            nextAll.each(function(i, obj){
                let objId = $(obj).find('.colForm').attr('id')
                
                // Destroy next select2
                if ( $('#'+ objId).data('select2') )  $('#'+ objId).select2('destroy')
                
                // Set events js && replace next news fields
                $('#'+ objId).replaceWith( $(html).find('#'+ objId) )
                $('#'+ objId).replaceWith( $(html).find('#'+ objId) )
                
                // Set fields events js
                keypressPasteCut( $('#'+ objId).find('input') )
                keypressPasteCut( $('#'+ objId).find('textarea') )
                
                // Hide next adding button
                $(obj).find('.colBtn').hide()
                
                // Show next new fields container
                removeClass( $(obj), 'd-none' ) 
                removeClass( $(obj), 'on-load' )
                
            })
            
            translationToggleBtn()
            
            // If user wants to cancel adding
            removeCreateData(button)
        }
    });
}

// Cancel adding data
function removeCreateData( button ) {
    
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
    
        if ( $('#toggle-btn').length ) $('#toggle-btn').remove()
        
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

// Set values to submit
function modalSubmit() {
    $('#modalLang').text(
        $('[id$="_form_lang"] option[value="'+$('[id$="_form_lang"]').val()+'"]')
            .text()
    )
    
    let eventInput = $('#form-event').find('input').val(),
        eventTitle = eventInput === undefined 
                                ?  $('[id$="_form_event"] option[value="'+$('[id$="_form_event"]').val()+'"]').text()
                                : eventInput
    $('#modalEvent').text(eventTitle)
    
    let categoryLevel1Input = $('#form-categoryLevel1').find('textarea').val(),
        categoryLevel1Title = categoryLevel1Input === undefined 
                                ? $('[id$="_form_categoryLevel1"] option[value="'+$('[id$="_form_categoryLevel1"]').val()+'"]').text()
                                : categoryLevel1Input
    $('#modalCategoryLevel1-title').text(categoryLevel1Title)
    
    let categoryLevel1Text = $('#form-categoryLevel1').find('textarea').val(),
        categoryLevel1Description = categoryLevel1Text === undefined 
                                ? $('#categoryLevel1 .description').text()
                                : categoryLevel1Text
    $('#modalCategoryLevel1-description').text(categoryLevel1Description)
    
    let categoryLevel2Input = $('#form-categoryLevel2').find('textarea').val(),
        categoryLevel2Title = categoryLevel2Input === undefined 
                                ? $('[id$="_form_categoryLevel2"] option[value="'+$('[id$="_form_categoryLevel2"]').val()+'"]').text()
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
        if ( index == 0 ) {
            $('#modalSuccess .title').text($('[id$="_situItems_0_title"]').val())
            $('#modalSuccess .description').text($('[id$="_situItems_0_description"]').val())
            return
        }
        
        let prototype   = $('#modalSituItems').attr('data-prototype'),
            scoreValue  = $('[id$="_situItems_'+ index +'_score"] option[value="'
                                + $('[id$="_situItems_'+ index +'_score"]').val()
                            +'"]').attr('class')
                    
        scoreValue = scoreValue.replace('selectable ', '').replace(' selected', '')

        let item = prototype.replace(/__item__/g, translations["item"])
                .replace(/__scoreTitle__/g, translations["scoreTitle"])
                .replace(/__score__/g, scoreValue)
                .replace(/__scoreText__/g, translations[scoreValue+"Item"])
                .replace(/__titleItem__/g, translations["titleItem"])
                .replace(/__title__/g, $('[id$="_situItems_'+ index +'_title"]').val())
                .replace(/__descriptionItem__/g, translations["descriptionItem"])
                .replace(/__description__/g,$('[id$="_situItems_'+ index +'_description"]').val())
        $('#optionScore').append(item)
    })
    
    $('#confirmSubmit').modal('show')
}

function errorMangement() {
    $('.formData').each(function() {
        if ( $(this).hasClass('on-load') ) $(this).removeClass('on-load')
    })
    checkForm()
    $('#loader').hide()
}

function bindFooter() {
    $('#loader').show() // Comment if html5 browser is disabled (attr novalidate)
    $('#form-error').empty()
}

/**
 * Load translation
 */
function loadTranslation() {
        
    // Load lang to set events
    $('[id$="_form_lang"]').val($('#situ').attr('data-lang')).trigger('change')
            .parent().find('.select2-selection__rendered').addClass('selection-on')

    // Then hide loader
    $(document).ajaxComplete(function () {
        if ( ! $('[id$="_form_event"]').is('select') ) translationToggleBtn()
        $('#loader').removeClass('d-block')
    })

    // Show situItems depending on Situ to translate
    let itemsLength = $('#initialSituItems').attr('data-initial')
    for( var i = 1; i < itemsLength; i++ ) {
        addSituItem()
    }
}

function translationToggleBtn() {
    
    if ( $('#loader').hasClass('translateSitu') && 0 === $('#toggle-btn').length )
    {
        
        let toggleBtn = '<div id="toggle-btn" class="d-block text-center pt-3">'
                            +'<span class="small show-less">'+ translations['showLess'] +'</span>'
                        +'</div>'

        $('#details').append(toggleBtn)
    
        $('#details').on('click', '#toggle-btn > span', function() {
            $(this).toggleClass('show-less').toggleClass('show-more')
            if ( ! $(this).hasClass('show-less') ) {
                $(this).addClass('show-more').text( translations['showMore'] )
                        .parent().removeClass('pt-3').addClass('mt-n3')
                $('#details').addClass('do-excerpt')
                return
            }
            $(this).text( translations['showLess'] )
                    .parent().removeClass('mt-n3').addClass('pt-3')
            $('#details').removeClass('do-excerpt')
        })
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
        if ( $(this).hasClass('on-load') && '' !== $(this).find('select').val() )
            $(this).removeClass('on-load')
        
        // Show collapsed
        $(this).find('.infoCollapse').each(function() {
            if ( $(this).hasClass('d-none') ) $(this).removeClass('d-none')
        })
        
        // Show data
        if ( 'lang' !== $(this).attr('id') ) {
            let value = $(this).find('select').val()
            if ( value ) getData( $(this).attr('id'), value )
        }
    })
    
    // Check SituItems
    if ( collectionHolder.find('.situItem').length > 1 ) {
        let values = []
        
        collectionHolder.find('select').each(function() {
            values.push($(this).val())
        })
        
        // Check SituItems scores
        collectionHolder.find('select').each(function() {
            $(this).addClass('selection-on')
            
            let value = $(this).val()
            
            $(this).find('option').each(function() {
                if ( value === $(this).val() )
                    $(this).addClass('selected')
                else if ( values.includes( $(this).val() ) )
                    $(this).addClass('bg-readonly').prop('disabled', true)
                else if ( '' === $(this).val() )
                    $(this).text( translations['scoreLabelAlt'] )
            })
            
            // Allow updating placeholder on change
            setScorePlaceholder( $(this), $(this).val() )
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
    
    // SituItem add button
    if ( collectionHolder.find('.situItem').length == 4 ) $('#add-situItem').hide()
}

/**
 * Update data not yet validated
 */
// Update event or categries not yet validated
function editData( button ) {
        if ( button.hasClass('modalValidate') ) {
            $('#editEntity').modal('hide')
            updateEntity()
            return
        }
        $('#editEntity').modal('hide').attr('data-entity', '').attr('data-id', '')
        $('#editEntity h5, #editEntity .modal-body').empty()
        $('#loader').hide()
}

// Add edit button depending on getData() result
function addEditButton( name, id ) {
    
    // Add edit button
    let updateButton = $('<button type="button" id="situ_dynamic_data_form_edit-'+ name +'" '
            +'name="situ_dynamic_data_form[edit-'+ name +']" '
            +'class="editEntity d-flex bg-none border-0 mx-2 py-2 small" '
            +'data-toggle="tooltip" data-placement="top" '
            +'title="'+ translations["update"] +'">'
            +'<i class="fas fa-edit bg-none text-light"></i></button>')
    $('#'+ name + ' > .d-flex').append(updateButton)
    editEntity( name, updateButton, id )
}
// Get form into modal
function editEntity( name, button, id ) {
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
                    $(html).find('[id$="_form_'+name+'"]')
                )
                $('#editEntity h5').text( translations[name+ 'Update'] )
                $('#editEntity').modal('show')
            }
        });
    })
}

// editEntity() flash result
function setFlash( type, msg ) {
    let icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle',
        flash = '<span class="icon text-'+ type +'"><i class="fas '+ icon +'"></i></span>'
                +'<span class="msg">'+ msg +'</span>'
    return flash
}

// Update with url depending on entity requested form modal
function updateEntity() {
    let type, data = {},
        url = 'event' === $('#editEntity').attr('data-entity')
                    ? '/front/ajaxUpdateEvent'
                    : '/front/ajaxUpdateCategory',
        entity = $('#editEntity').attr('data-entity'),
        id = $('#editEntity').attr('data-id')

    // Set data values
    data['id'] =            id,
    data['title'] =         $('#editEntity').find('input[type=text]').val(),
    data['description'] =   'event' !== entity
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
            $('[id$="_form_'+ entity +'"] option').each(function() {
                if ( id === $(this).val() ) {
                    
                    // Replace option text
                    $(this).text($('#editEntity input').val())
                    unvalidatedOption( $(this).parent() ) 
                    
                    // Reinit select2 to set new text option
                    initSelect2( $(this).parent() )
                    $(this).parent().val(id).trigger('change')
                    
                    // Remove next update entity buttons
                    $(this).parents('.formData').next().find('.editEntity').each(function() {
                        $(this).remove()
                    })
               }
            })
            
            // Reload current category description
            if ('categoryLevel1' === entity || 'categoryLevel2' === entity ) {
                toggleInfoCollapse( 'hide', $('#form-'+ entity) )
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
function updateScore( collectionHolder, newElem ) {
    let select = newElem.find('select'),
        values = [],
        // timeout because of situItems dynamic adding
        timeout = setTimeout(function(){
            $(collectionHolder).find('select').each(function() {
                if ($(this).val() != '') values.push($(this).val())
            })
            // Ckeck all except first situItem (success score)
            if ( 'situ_form_situItems_0_score' !== select.attr('id') ) {
                if ( values.length > 0 ) {
                    select.find('option').each(function() {
                        // If value selected disabled it from newElem
                        if ( values.includes( $(this).val() ) ) {
                            $(this).addClass('bg-readonly').prop('disabled', true)
                            clearTimeout(timeout)
                        }
                    })
                }
            }
        }, 500);
}

// Delete situItem with confirm alert
function removeSituItem( button ) {
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
                        if ( divItem.find('select').val() != '' ) {
                            // Get current value
                            let scoreSelected = divItem.find('select').val()
                            // For each SituItemLi select score
                            collectionHolder.find('select').each(function() {
                                $(this).find('option').each(function() {
                                    if ( scoreSelected === $(this).val() )
                                        $(this).removeClass('bg-readonly').removeAttr('disabled')
                                })
                            })
                        }
                        divItem.remove()
                        // Hide Adding button when all options are selected
                        if ( collectionHolder.find('.situItem').length < 4 ) {
                            $('#add-situItem').show()
                        }
                    }
                }
            },
        })
    })
}

// Add css class to empty option score selection to set placeholder later
function addPlaceholderClass( newElem ) {
    if ( '' === newElem ) {
        collectionHolder.find('select').each(function() {
            $(this).find('option').each(function() {
                if ( '' === $(this).val() ) $(this).addClass('placeholder')
            })
        })
    } else {
        newElem.find('select').each(function() {
            $(this).find('option').each(function() {
                if ( '' === $(this).val() ) $(this).addClass('placeholder')
            })
        })
    }
}

// Toggle style depending on selection value
function toggleClassSelection( newElem ) {
    newElem.find('select').on('change', function() {
        if ( '' === $(this).val() && $(this).hasClass('selection-on') )
            $(this).removeClass('selection-on')
        else $(this).addClass('selection-on')
    })
}

// Update all options for each score when adding a new situItem
function newScore() {
    collectionHolder.find('select').each(function() {
        
        if ( 'situ_form_situItems_0_score' !== $(this).attr('id') )
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
                        if ( newValue === $(this).val() && '' !== newValue )
                            $(this).addClass('selected')
                    })
                })
    })
}

// Update all options for each situItem score depending on new elem value
function checkScores( newValue, oldValue ) {
    collectionHolder.find('select').each(function() {
        // Current Score selection
        if ( $(this).hasClass('onSelect') ) {
            $(this).find('option').each(function() {
                // If reset selection
                if ( '' !== oldValue && $(this).val() == oldValue
                                        && $(this).hasClass('selected') )
                {
                    $(this).removeClass('selected')
                }
            })
            $(this).removeClass('onSelect')
            return
        }
        // Other Score selections        
        $(this).find('option').each(function() {
            if ( 0 !== $(this).val() ) {
                // Disable selected option from current Score selection
                if ( newValue === $(this).val() && '' !== newValue ) {
                    $(this).addClass('bg-readonly').prop('disabled', true)
                }
                // Enable unselected option from current Score selection
                if ( oldValue === $(this).val() && '' !== oldValue ) {
                    $(this).removeClass('bg-readonly').prop('disabled', false)
                }
            }
        })
    })
}

// Set Score selection placeholder text
function setScorePlaceholder( select, newValue ) {
    if ( '' === newValue ) select.find('.placeholder').text( translations['scoreLabel'] )
    else select.find('.placeholder').text( translations['scoreLabelAlt'] )
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
    
    keypressPasteCut( newElem.find('input[type="text"]') ) 
    keypressPasteCut( newElem.find('textarea') ) 
    keypressPasteCut( newElem.find('select') ) 
    
    newElem.find('.score-info').tooltip()
    newElem.appendTo(collectionHolder)
    newScore()

    // Hide Adding button when all options are selected
    if ( collectionHolder.find('.situItem').length === 4 ) {
        $('#add-situItem').hide()
    }
}

function resizeWindows() {
        
    if ( $(window).width() > 767 ) {
        
        $('#initialSitu .h-title').each(function() {
            if ( $(this).height() < $(this).children().height() )
                $(this).addClass('px-1 border border-top-0 border-secondary')
        })

        $('#initialSitu .h-description').each(function() {
            if ( $(this).height() < $(this).children().height() )
                $(this).addClass('px-1 border border-top-0 border-secondary')
        })

        if ( $('#details').height() < $('#initialSitu .card-header:eq(1)').height() )
            $('#details').css( 'height', $('#initialSitu .card-header:eq(1)').height() + 30 )
        
    }

    $(window).resize((event) => {
    
        const attr = $('#details').attr('style')

        if ( innerWidth > 767 ) {

            if ( $('#details').height() < $('#initialSitu .card-header:eq(1)').height() )
                $('#details').css( 'height', $('#initialSitu .card-header:eq(1)').height() + 30 )
            else if ( typeof attr !== 'undefined' && attr !== false )
                $('#details').removeAttr('style')
            
            $('#initialSitu .h-title').each(function() {
                if ( $(this).height() < $(this).children().height() )
                    $(this).addClass('px-1 border border-top-0 border-secondary')
                else if ( $(this).hasClass('px-1 border border-top-0 border-secondary') )
                    $(this).removeClass('px-1 border border-top-0 border-secondary')
            })

            $('#initialSitu .h-description').each(function() {
                if ( $(this).height() < $(this).children().height() )
                    $(this).addClass('px-1 border border-top-0 border-secondary')
                else if ( $(this).hasClass('px-1 border border-top-0 border-secondary') )
                    $(this).removeClass('px-1 border border-top-0 border-secondary')
            })
            return
        }
        
        if ( typeof attr !== 'undefined' && attr !== false )
            $('#details').removeAttr('style')
            
            $('#initialSitu .h-title').each(function() {
                if ( $(this).hasClass('px-1 border border-top-0 border-secondary') )
                    $(this).removeClass('px-1 border border-top-0 border-secondary')
            })

            $('#initialSitu .h-description').each(function() {
                if ( $(this).hasClass('px-1 border border-top-0 border-secondary') )
                    $(this).removeClass('px-1 border border-top-0 border-secondary')
            })

    })
}

$(function() {
    
    // Debug focus
    $('.form-control').unbind('blur')
    
    // Dynamic BS tooltip
    $('body').tooltip({ selector: '.editEntity'})
    
    // Set multiple events
    $('input[type="text"], textarea').each(function() { keypressPasteCut( $(this) ) })
    
    // Show first fields
    setDetailsFormSection()
    
    // Init the selects of the details section
    $('.card-header').find('select').each(function() {
        unvalidatedOption( $(this) )
        initSelect2( $(this) )
        // When update Situ
        if ( '' !== $(this).val() ) {
            $(this).parent().find('.select2-selection__rendered')
                    .addClass('selection-on')
        }
    })
    
    // Hide adding events/categories button if need to be created
    $('.colDataLang').each(function(){
        if ( ! $(this).children().is('select') ) $(this).next().find('.btnAdd').hide()
    })
    
    // When update Situ
    if ( $('#situ').attr('data-id') ) updateSitu()
    else {
        // Reset lang
        if ( $('#situ_dynamic_data_form_lang option').length > 1 )
            $('#situ_dynamic_data_form_lang').val(null).trigger('change')
        
        // Create SituItem once required
        addSituItem()
        $('#loader').hide()
    }
    
    // If translate situ
    if ( $('#loader').hasClass('translateSitu') ) {
        $('#page > .container').addClass('w-100')
        loadTranslation()
        resizeWindows()
    }
    
    // Create new event or categories
    $('form').on('click', '.btnAdd', function() { createData( $(this) ) })
    
    // If new categoryLevel2
    $('form').on('keyup paste', '[id$="_form_categoryLevel2_description"]',
        function() { showCards() }
    )
    
    // Update event or categries not yet validated
    $('#editEntity .btnModal').click(function() { editData( $(this) ) })
    
    // Add SituItem until 4
    $('#add-itemSitu-link').click(function() { addSituItem() })
    
    // Error management
    if ( 0 !== $('#form-error > .alert').length ) { errorMangement() }
    
    /**
     * Confirm submit
     */
    $('#actionSave').click(function() { if ( checkForm() ) $('#situ_form_save').click() })   
    $('#modalSubmit').click(function() { if ( checkForm() ) modalSubmit() })     
    $('#cancelSubmit').bind('click', function() { $('#optionScore').empty() })
    
    $('.card-footer > button').bind('click', function() { bindFooter() })
    
})