$(function(){
    
    // Entries selection
    $('#select_all').change(function() {
        if (this.checked) {
            $(".select").each(function() {
                this.checked=true;
            });
            // Show actions row
            $('#userActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else {
            $('.select').each(function() {
                this.checked=false;
            });
            // Hide actions row
            $('#userActions').addClass('d-none').animate({opacity: 0})
        }
    });

    // Toggle actions row
    $('.form-check-input').change(function() {
        if (this.checked && $('#userActions').hasClass('d-none')) {
            $('#userActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else if (!this.checked && !$('#userActions').hasClass('d-none')) {
            var check = 0;
            $(".select").each(function() {
                if (this.checked) check += 1
            });
            if(check == 0) $('#userActions').addClass('d-none').animate({opacity: 0})
        }
    })
    
});