// css
import '../scss/page_edit_app.scss';

/**
 * Add pageContent collection
 */
let collectionHolder = $('#pageContents')

// Add pageContent from prototype
function addContent() {
    let counter = collectionHolder.attr('data-widget-counter') || collectionHolder.children().length
    let newWidget = collectionHolder.attr('data-prototype')

    newWidget = newWidget.replace(/__name__/g, counter)
    counter++
    collectionHolder.attr('data-widget-counter', counter)

    let newElem = $(collectionHolder.attr('data-widget-pageContents')).html(newWidget)
    removeContent(newElem.find('.removeContent'))
    newElem.appendTo(collectionHolder)
}

// Delete pageContent with confirm alert
function removeContent(button) {
    let divContent = button.parents('.divContent')
    
    button.on('click', function() {
        divContent.addClass('to-confirm')
        $.confirm({
            animation: 'scale',
            closeAnimation: 'scale',
            animateFromElement: false,
            columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
            type: 'red',
            typeAnimated: true,
            title: translations['deletePage-title'],
            content: translations['deletePage-content'],
            buttons: {
                cancel: {
                    text: translations['no'],
                    action: function () {
                        divContent.removeClass('to-confirm')
                    }
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        divContent.remove()
                    }
                }
            },
        })
    })
}

// Slugify from title
function slugify(title) {
    
    // If latin characters
    if (title.match(/[a-z]/i)) {
        title = title.toString().toLowerCase().trim();

        const sets = [
          {to: 'a', from: '[ÀÁÂÃÄÅÆĀĂĄẠẢẤẦẨẪẬẮẰẲẴẶἀ]'},
          {to: 'c', from: '[ÇĆĈČ]'},
          {to: 'd', from: '[ÐĎĐÞ]'},
          {to: 'e', from: '[ÈÉÊËĒĔĖĘĚẸẺẼẾỀỂỄỆ]'},
          {to: 'g', from: '[ĜĞĢǴ]'},
          {to: 'h', from: '[ĤḦ]'},
          {to: 'i', from: '[ÌÍÎÏĨĪĮİỈỊ]'},
          {to: 'j', from: '[Ĵ]'},
          {to: 'ij', from: '[Ĳ]'},
          {to: 'k', from: '[Ķ]'},
          {to: 'l', from: '[ĹĻĽŁ]'},
          {to: 'm', from: '[Ḿ]'},
          {to: 'n', from: '[ÑŃŅŇ]'},
          {to: 'o', from: '[ÒÓÔÕÖØŌŎŐỌỎỐỒỔỖỘỚỜỞỠỢǪǬƠ]'},
          {to: 'oe', from: '[Œ]'},
          {to: 'p', from: '[ṕ]'},
          {to: 'r', from: '[ŔŖŘ]'},
          {to: 's', from: '[ßŚŜŞŠȘ]'},
          {to: 't', from: '[ŢŤ]'},
          {to: 'u', from: '[ÙÚÛÜŨŪŬŮŰŲỤỦỨỪỬỮỰƯ]'},
          {to: 'w', from: '[ẂŴẀẄ]'},
          {to: 'x', from: '[ẍ]'},
          {to: 'y', from: '[ÝŶŸỲỴỶỸ]'},
          {to: 'z', from: '[ŹŻŽ]'},
          {to: '-', from: '[·/_,:;\']'}
        ];

        sets.forEach(set => {
          title = title.replace(new RegExp(set.from,'gi'), set.to)
        });

        return title
          .replace(/\s+/g, '-')    // Replace spaces with -
          .replace(/[^-a-zа-я\u0370-\u03ff\u1f00-\u1fff]+/g, '') // Remove all non-word chars
          .replace(/--+/g, '-')    // Replace multiple - with single -
          .replace(/^-+/, '')      // Trim - from start of text
          .replace(/-+$/, '')      // Trim - from end of text
  
    } else {
        return encodeURIComponent(title)
    }
}

$(function() {
    
    // Add new Content to collection
    $('#add-content-link').click(function() {
        addContent()
    })
    
    // Remove Content from existing collection (when update Page)
    $('.removeContent').each(function() {
        removeContent($(this))
    })
    
    /**
     * GGTranslation & Slug generation 
     */
    $('#page_form_title').on('keyup paste', function () {
        if ($('#GGTbtn').hasClass('d-none')) $('#GGTbtn').removeClass('d-none')
    })
    
    $('#GGTbtn').on('click', function(){
        // Reset GGTranslate
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        // Load title and slug to translate an generate slug
        $('#ggtTitle').html($('#page_form_title').val())
        $('#ggtSlung').html(slugify($('#page_form_title').val()))
        $('#ggtModal').modal('show')
    })

    $('#GGT').bind('change', '.goog-te-combo', function() {

        // Wait ang get GGtranslation
        $(this).delay(950).find('option').each(function() {
            if ($(this).val() == $('.goog-te-combo').val()){
                let langName = $(this).html()
                $('#page_form_lang').val($(this).val())
                $('#selectedLang .selectedLang').html(langName)
            }
        })
        // Toggle spinner
        $('#modalSpinner').removeClass('d-none')
                .delay(1000).queue(function(next){
                    $(this).addClass('d-none')
                    next();
                })
        // Toggle GGtranslation and reslugify
        $('#ggtSlung').addClass('d-none')
                .delay(1000).queue(function(next){
                    $(this).html(slugify($('#ggtTitle font font').text()))
                        .removeClass('d-none')
                    next();
                })
    })

    // Load slug and title translated
    $('#addTranslation').click(function() {
        $('#page_form_title').val($('#ggtTitle').text())
        $('#page_form_slug').val($('#ggtSlung').text())
        if ($('#selectedLang').hasClass('d-none'))
            $('#selectedLang').removeClass('d-none')
        $('#title-danger').hide()
        $('#ggtModal').modal('hide')
    })
    
    // Submit
    $('#save, #submit').click(function() {
        if ($(this).attr('id') == 'save') $('#page_form_enabled').val(0)
        else $('#page_form_enabled').val(1)
        $('form').submit()
    })
    
})