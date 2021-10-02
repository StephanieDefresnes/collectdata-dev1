// Remove translation file with confirm alert
function removeFile(button) {
    let fileName = button.parents('tr').find('.fileName').text()
    button.confirm({
        animation: 'scale',
        closeAnimation: 'scale',
        animateFromElement: false,
        columnClass: 'col-lg-4 col-lg-offset-4 col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2',
        type: 'red',
        typeAnimated: true,
        title: translations['confirmTitle'],
        content: '<p class="text-center font-weight-bold">'+ fileName +'</p>'
                +'<p class="mb-2 text-center font-weight-bold text-danger">'+ translations["confirmWarning"] +'</p>'
                +'<p class="line-11">'+ translations["confirmQuestion"] +'</p>',
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
}


$(function() {
    
    // Read Yaml content
    $('.showFile').click(function() {
        let fileName = $(this).parents('tr').find('.fileName').text()
        let fileContent = $(this).parents('tr').find('.fileContent').html().replace(/\r\n/g,'<br/>');
        $('#showFileLabel').text(fileName)
        $('#fileContent').html('<pre>'+ fileContent + '</pre>')
        $('#showFile').modal('show')
    })
    
    // Remove Content from existing collection (when update Page)
    $('.removeFile').each(function() {
        removeFile($(this))
    })
    
})