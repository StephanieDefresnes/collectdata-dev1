// css
import '../scss/lang_translation_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');

require('jquery-ui');
require('jquery-ui/ui/widgets/sortable');

/**
 * Load Fields collection
 */
// Get the ul that holds the collection of tags
var collectionHolder = $('#fields')

 // Add Delete button for each Field added
function addFieldLiDeleteButton(fieldLi) {
    var removeFormButton = 
            $('<button type="button" class="btn btn-outline-danger float-right mt-1">'
                +'<i class="far fa-trash-alt"></i>'
            +'</button><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>')
    fieldLi.prepend(removeFormButton)

    removeFormButton.on('click', function() {
        fieldLi.remove()
    })
}

// Add Field from prototype
function addField(button) {

    var list = $(button.attr('data-list-selector'))
    var counter = list.attr('data-widget-counter') || list.children().length
    var newWidget = list.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    list.attr('data-widget-counter', counter)

    var newElem = $(list.attr('data-widget-fields')).html(newWidget)
    addFieldLiDeleteButton(newElem)
    newElem.appendTo(list)
}

$(function() {
    
    // Init select2 to multpiple
    $('.select-multiple').select2({
        placeholder: translations['multiple-search'],
        allowClear: true
    })
    
    // Init jQuery ui sortable on fields list
    $('#fields').sortable();
    
    // Add new field into collection
    $('#add-field-link').click(function () {
        addField($(this))
    })
});