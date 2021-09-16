$(document).ready(function() {
    $('#show_hide_password .btn').on('click', function(event) {
        event.preventDefault();
        if ($('#show_hide_password input').attr('type') == 'text'){
            $('#show_hide_password input').attr('type', 'password')
            $('#show_hide_password i').addClass( 'fa-eye-slash' ).removeClass( 'fa-eye' )
        } else if ($('#show_hide_password input').attr('type') == 'password'){
            $('#show_hide_password input').attr('type', 'text')
            $('#show_hide_password i').removeClass( 'fa-eye-slash' ).addClass( 'fa-eye' )
        }
    });
});