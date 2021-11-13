// Get data to set confirm
function getConfirm() {
    let name, lang, btnColor, titleClass, title, content, confirm = []
    let value = $('#translation_batch_action').val()
    
    // Styles
    if (value === 'delete') {
        btnColor = 'btn-danger'
        titleClass = ' text-danger'
    } else {
        btnColor = 'btn-primary'
        titleClass = ' text-primary'
    }

    let checked = $('tbody input:checkbox:checked').length

    // Multiple rows selected
    if (checked > 1) {
        // Set title
        title = '<div class="d-sm-flex justify-content-start'+ titleClass +'">'
                +'<span class="'+ titleClass +'">'+ translations[value +'-titles'] + '</span>'
                +'</div>'
        
        // Set content
        content = '<ul>'
        $('tbody input:checkbox:checked').each(function() {
            name = $(this).parents('tr').find('td.name').text()
            content += '<li class="d-sm-flex justify-content-start"><span class="px-2">• '+ name +'</span>'
            
            lang = $(this).parents('tr').find('td.lang').text()
            if (lang)
                content += '<span class="text-uppercase">'+ lang +'</span>'
                
            content += '</li>'
        })
        content += '</ul>'
                +'<p class="line-11">'+ translations[value +'-confirms'] +'</p>'
    }
    // Single row selected
    else {
        name = $('tbody input:checkbox:checked').parents('tr').find('td.name').text()
        lang = $('tbody input:checkbox:checked').parents('tr').find('td.lang').text()
        
        // Set title
        title = '<div class="d-sm-flex justify-content-start'+ titleClass +'">'
                +'<span>'+ translations[value +'-title'] + '</span>'
                + '<span class="px-2">'+ name + '</span>'
                +'<span class="text-uppercase">'+ lang +'</span></div>'
        
        // Set content
        content = '<p class="line-11">'+ translations[value +'-confirm'] +'</p>'        
    }
    
    if (value === 'delete')
        content += '<p class="text-danger text-center">'+ translations['warning'] +'</p>'
    
    confirm['titleClass'] = titleClass
    confirm['btnColor'] = btnColor
    confirm['title'] = title
    confirm['content'] = content
    
    return confirm 
}

function submitConfirm(button) {    
    button.confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-lg-6 col-md-8 col-sm-10 mx-auto',
        typeAnimated: true,
        title: function(){
            return getConfirm()['title']
        },
        content: function(){
            return getConfirm()['content']
        },
        buttons: {
            cancel: {
                text: translations['no'],
                action: function () {}
            },
            confirm: {
                text: translations['yes'],
                btnClass: function(){
                    return getConfirm()['btnColor']
                },
                action: function () {
                    $('form').submit()
                }
            }
        },
    });    
}

$(function(){
    
    // Entries selection
    $('#select_all').change(function() {
        if (this.checked) {
            $(".select").each(function() {
                this.checked=true;
            });
            // Show actions row
            $('#formActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else {
            $('.select').each(function() {
                this.checked=false;
            });
            // Hide actions row
            $('#formActions').addClass('d-none').css('opacity', 0)
        }
    });

    // Toggle actions menu
    $('.form-check-input').change(function() {
        if (this.checked && $('#formActions').hasClass('d-none')) {
            $('#formActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else if (!this.checked && !$('#formActions').hasClass('d-none')) {
            var check = 0;
            $('.select').each(function() {
                if (this.checked) check += 1
            });
            if(check == 0) $('#formActions').addClass('d-none').css('opacity', 0)
        }
    })
    
    // Show actions menu if rows are checked
    $('.form-check-input').each(function() {
        if ($(this).prop('checked') == true && $('#formActions').hasClass('d-none'))
            $('#formActions').removeClass('d-none')
    })
    
    // Confirm before submit
    submitConfirm($('#translation_batch_submit'))
    
});