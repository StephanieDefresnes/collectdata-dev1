// css
import '../scss/situ_read_app.scss';

function resetGGT() {
    let dataExist = setInterval(function() {
        if ($('iframe').length) {
           $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
           clearInterval(dataExist)
        }
    }, 50);
}

$(function() {
    
    // Reset GGT
    // -- on load
    setTimeout(resetGGT, 2000)
    
    // -- on click button
    $('#resetGGT').click(function() {
        $('#\\:1\\.container').contents().find('#\\:1\\.restore').click()
        $(this).addClass('d-none')
    })
    
    // Lang change
    $('#translator').on('change', 'select', function() {
        $('#resetGGT').removeClass('d-none')
    })
    
    $('#confirmDelete').confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
        type: 'red',
        typeAnimated: true,
        title: translations['removeTitle'],
        content: translations["removeText"],
        buttons: {
            cancel: {
                text: translations['no'],
            },
            formSubmit: {
                text: translations['yes'],
                btnClass: 'btn-red',
                action: function () {
                    location.href = this.$target.attr('href');
                }
            }
        },
    })
})
