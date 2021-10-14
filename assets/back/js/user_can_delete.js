function deleteUser(button) {
    button.on('click', function() {
        $.confirm({
            animation: 'scale',
            closeAnimation: 'scale',
            animateFromElement: false,
            columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
            type: 'red',
            typeAnimated: true,
            title: translations["user"] + button.attr('data-user'),
            content: '<p class="line-11">'+ translations["confirm"] +'</p>'
                    +'<p class="mb-2 text-center font-weight-bold text-danger">'+ translations["warning"] +'</p>',
            buttons: {
                cancel: {
                    text: translations['no'],
                },
                formSubmit: {
                    text: translations['yes'],
                    btnClass: 'btn-red',
                    action: function () {
                        location.href = button.attr('data-url')
                    }
                }
            },
        })
    })
}

$(function(){
    
    $('.userDelete').each(function() {
        deleteUser($(this))
    })
});