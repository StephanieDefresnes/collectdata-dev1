// Remove translation file with confirm alert
function removeFile(button) {
    let fileName = button.parents('tr').find('.fileName').text() 
    
    button.confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-lg-6 col-md-8 mx-auto',
        type: 'red',
        typeAnimated: true,
        title: translations['title'],
        content: '<p class="text-center font-weight-bold">'+ fileName +'</p>'
                +'<p class="mb-2 text-center font-weight-bold text-danger">'+ translations["warning"] +'</p>'
                +'<p class="line-11">'+ translations['question'] +'</p>',
        buttons: {
            cancel: {
                text: translations['no'],
            },
            formSubmit: {
                text: translations['yes'],
                btnClass: 'btn-red',
                action: function () {
                    $('#loader').show()
                    location.href = this.$target.attr('href');
                }
            }
        },
    })
}

$(function() {
    
    // Uncheck inputs (after ajax)
    $('input[type="checkbox"]').each(function() {
        this.checked=false;
    })
    
    // Read Yaml content
    $('.showFile').click(function() {
        let fileName = $(this).parents('tr').find('.fileName').text()
        let fileContent = $(this).parents('tr').find('.fileContent').html().replace(/\r\n/g,'<br/>');
        $('#showFileLabel').text(fileName)
        $('#fileContent').html('<pre>'+ fileContent + '</pre>')
        $('#showFile').modal('show')
    })
    
    $('.removeFile').each(function() {
        removeFile($(this))
    })
    
})