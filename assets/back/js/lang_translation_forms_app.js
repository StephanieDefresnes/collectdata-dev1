$(function(){
    
    // Entries selection
    $('#select_all').change(function() {
        if (this.checked) {
            $(".select").each(function() {
                this.checked=true;
            });
            // Show actions row
            $('#formActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else {
            $('.select').each(function() {
                this.checked=false;
            });
            // Hide actions row
            $('#formActions').addClass('d-none').css('opacity', 0)
        }
    });

    // Toggle actions menu
    $('.form-check-input').change(function() {
        if (this.checked && $('#formActions').hasClass('d-none')) {
            $('#formActions').removeClass('d-none').animate({opacity: 1}, 450)
        } else if (!this.checked && !$('#formActions').hasClass('d-none')) {
            var check = 0;
            $('.select').each(function() {
                if (this.checked) check += 1
            });
            if(check == 0) $('#formActions').addClass('d-none').css('opacity', 0)
        }
    })
    
    // Open actions menu if rows are checked
    $('.form-check-input').each(function() {
        if ($(this).prop('checked') == true && $('#formActions').hasClass('d-none'))
            $('#formActions').removeClass('d-none')
    })
    
});