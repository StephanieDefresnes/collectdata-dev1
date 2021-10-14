function submit() {
 
    if ($('#user_batch_action').val() == 'delete') {
        let ulNames = '<ul>'
        $('.form-check-input').each(function() {
            if($(this).prop('checked') == true){
                let userName = $(this).parents('tr').find('.userName').text()
                ulNames = ulNames + '<li>'+ userName +'</li>'
            }
        })
        ulNames = ulNames + '</ul>'
        
        $.confirm({
            animation: 'scale',
            closeAnimation: 'scale',
            animateFromElement: false,
            columnClass: 'col-xl-5 col-lg-6 col-md-8 mx-auto',
            type: 'red',
            typeAnimated: true,
            title: translations["users"],
            content: '<p class="line-11">'+ translations["confirms"] +'</p>'
                    + '<p class="line-11">'+ ulNames +'</p>'
                    +'<p class="mb-2 text-center font-weight-bold text-danger">'+ translations["warning"] +'</p>',
            buttons: {
                cancel: {
                    text: translations['no'],
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        $('form').submit()
                    }
                }
            },
        })
    } else $('form').submit()
}

$(function(){
    
    // Entries selection
    $('#select_all').change(function() {
        if (this.checked) {
            $(".select").each(function() {
                this.checked=true;
            });
            // Show actions row
            $('#userActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else {
            $('.select').each(function() {
                this.checked=false;
            });
            // Hide actions row
            $('#userActions').addClass('d-none').animate({opacity: 0})
        }
    });

    // Toggle actions row
    $('.form-check-input').change(function() {
        if (this.checked && $('#userActions').hasClass('d-none')) {
            $('#userActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else if (!this.checked && !$('#userActions').hasClass('d-none')) {
            var check = 0;
            $(".select").each(function() {
                if (this.checked) check += 1
            });
            if(check == 0) $('#userActions').addClass('d-none').animate({opacity: 0})
        }
    })
    
    $('#submit').click(function() {
        submit()
    })
    
    
});