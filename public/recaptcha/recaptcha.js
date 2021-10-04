/**
 * The callback function executed
 * once all the Google dependencies have loaded
 */
function onGoogleReCaptchaApiLoad() {
    var widgets = document.querySelectorAll('[data-toggle="recaptcha"]');
    for (var i = 0; i < widgets.length; i++) {
        renderReCaptcha(widgets[i]);
    }
}

/**
 * Render the given widget as a reCAPTCHA 
 * from the data-type attribute
 */
function renderReCaptcha(widget) {
    var form = widget.closest('form');
    var widgetType = widget.getAttribute('data-type');
    var widgetParameters = {
        'sitekey': recaptcha['sitekey']
    };

    if (widgetType == 'invisible') {
        widgetParameters['callback'] = function () {
            form.submit()
        };
        widgetParameters['size'] = "invisible";
    }

    var widgetId = grecaptcha.render(widget, widgetParameters);

    if (widgetType == 'invisible') {
        bindChallengeToSubmitButtons(form, widgetId);
    }
}

/**
 * Prevent the submit buttons from submitting a form
 * and invoke the challenge for the given captcha id
 */
function bindChallengeToSubmitButtons(form, reCaptchaId) {
   getSubmitButtons(form).forEach(function (button) {
       button.addEventListener('click', function (e) {
           e.preventDefault();
           grecaptcha.execute(reCaptchaId);
       });
   });
}

/**
 * Get the submit buttons from the given form
 */
function getSubmitButtons(form) {
    var buttons = form.querySelectorAll('button, input');
    var submitButtons = [];

    for (var i= 0; i < buttons.length; i++) {
        var button = buttons[i];
        if (button.getAttribute('type') == 'submit') {
            submitButtons.push(button);
        }
    }

    return submitButtons;
}