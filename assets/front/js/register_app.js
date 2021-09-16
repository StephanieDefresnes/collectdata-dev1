// css
import '../scss/register_app.scss'

$(document).ready(function() {
    $('.togglePassword .btn').on('click', function(event) {
        event.preventDefault();
        if ($(this).parents('.togglePassword').find('input').attr('type') == 'text'){
            $(this).parents('.togglePassword').find('input').attr('type', 'password')
            $(this).parents('.togglePassword').find('i')
                    .addClass('fa-eye-slash').removeClass('fa-eye')
        } else if ($(this).parents('.togglePassword').find('input').attr('type') == 'password'){
            $(this).parents('.togglePassword').find('input').attr('type', 'text')
            $(this).parents('.togglePassword').find('i')
                    .removeClass('fa-eye-slash').addClass('fa-eye')
        }
    });
});