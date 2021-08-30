$(function() {
   
    let windowHeight = $(window).height()
    let lastHeight = $('body').height()

    if (windowHeight > lastHeight) {
        $('#footerEnd').height(windowHeight - lastHeight)
    }

    let footerEnd = setInterval(function() {
        let newHeight = $('body').height()
        let contentHeight = newHeight - $('#footerEnd').height()

        if (lastHeight != newHeight) {
            lastHeight = newHeight;
        }
        if (windowHeight > contentHeight) {
            $('#footerEnd').height(windowHeight - contentHeight)
        } else {
            $('#footerEnd').height(0)
            clearInterval(footerEnd)
        }
    }, 100)
    
})