// css
import '../scss/register_app.scss'

$(document).ready(function() {
    
    $('#registration_form_agreeTerms, '
            +'label[for="registration_form_agreeTerms"]').bind('click', function() {
        $('#comment').fadeIn()
    })
    
})