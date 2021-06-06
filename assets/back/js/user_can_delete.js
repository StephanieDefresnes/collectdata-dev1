$(document).ready(function(){
    
    $('.btn-delete').click(function(){
        var title = $(this).attr('data-title');
        var path = $(this).attr('data-path');
        $('#form_back_user_delete').attr('action', path);
        $('#modal_body_title').html(translations['deleteConfirm'] + " : <strong>"+title+"</strong>");
    });
    
});