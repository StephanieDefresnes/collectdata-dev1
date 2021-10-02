$(function() {
    
    $('.showFile').click(function() {
        let fileName = $(this).parents('tr').find('.fileName').text()
        let fileContent = $(this).parents('tr').find('.fileContent').html().replace(/\r\n/g,'<br/>');
        $('#showFileLabel').text(fileName)
        $('#fileContent').html('<pre>'+ fileContent + '</pre>')
        $('#showFile').modal('show')
    })
    
})