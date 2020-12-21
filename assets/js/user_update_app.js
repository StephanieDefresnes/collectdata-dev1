$(function() {
    
    $('#select_user_langId').change(function() {
        $('#user_update_form_langId').val($('#select_user_langId').val());
    });
    
});