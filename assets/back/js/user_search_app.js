// css
import '../scss/user_search_app.scss';

// js
require('datatables.net/js/jquery.dataTables.min.js');
require('datatables.net-bs4/js/dataTables.bootstrap4.min.js');

// Langs array to load Datattables i18n
var langs = {
    "af" : "Afrikaans", "ak" : "Akan", "sq" : "Albanian", "am" : "Amharic",
    "ar" : "Arabic", "hy" : "Armenian", "rup" : "Aromanian", "as" : "Assamese",
    "az" : "Azerbaijani", "az" : "Azerbaijani", "ba" : "Bashkir", "eu" : "Basque",
    "bel" : "Belarusian", "bn" : "Bengali", "bs" : "Bosnian", "bg" : "Bulgarian",
    "my" : "Burmese", "ca" : "Catalan", "bal" : "Catalan", "zh" : "Chinese",
    "co" : "Corsican", "hr" : "Croatian", "cs" : "Czech", "da" : "Danish",
    "dv" : "Dhivehi", "nl" : "Dutch", "en" : "English", "eo" : "Esperanto",
    "et" : "Estonian", "fo" : "Faroese", "fi" : "Finnish", "fr" : "French",
    "fy" : "Frisian", "fuc" : "Fulah", "gl" : "Galician", "ka" : "Georgian",
    "de" : "German", "el" : "Greek", "gn" : "Guaraní", "gu" : "Gujarati",
    "he" : "Hebrew", "hi" : "Hindi", "huU" : "Hungarian", "is" : "Icelandic",
    "id" : "Indonesian", "ga" : "Irish", "it" : "Italian", "ja" : "Japanese",
    "jv" : "Javanese", "kn" : "Kannada", "kk" : "Kazakh", "km" : "Khmer",
    "kin" : "Kinyarwanda", "ky" : "Kirghiz", "ko" : "Korean", "ckb" : "Kurdish",
    "lo" : "Lao", "lv" : "Latvian", "li" : "Limburgish", "lin" : "Lingala",
    "lt" : "Lithuanian", "lb" : "Luxembourgish", "mk" : "Macedonian", "ms" : "Malay",
    "ml" : "Malayalam", "mr" : "Marathi", "xmf" : "Mingrelian", "mn" : "Mongolian",
    "ne" : "Nepali", "nb" : "Norwegian-Bokmal", "nn" : "Norwegian-Nynorsk",
    "ps" : "Pashto", "fa" : "Persian", "pl" : "Polish", "pt" : "Portuguese",
    "pa" : "Punjabi", "ro" : "Romanian", "ru" : "Russian", "sr" : "Serbian",
    "sd" : "Sindhi", "si" : "Sinhala", "sk" : "Slovak", "sl" : "Slovenian",
    "es" : "Spanish", "sw" : "Swahili", "sv" : "Swedish", "tg" : "Tajik",
    "ta" : "Tamil", "te" : "Telugu", "th" : "Thai", "tr" : "Turkish", "uk" : "Ukrainian",
    "ur" : "Urdu", "uz" : "Uzbek", "vi" : "Vietnamese", "cy" : "Welsh"
}

$(document).ready(function(){
    
    // Datatables configutration
    var table = $('#dataTable-usersList').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/'
                    + langs[$('html').attr('lang')] +'.json',
        },
        dom: '<"d-flex justify-content-between row mb-2"<"#length.col-md-5"l><"#search.col-auto"f>>'
                +'<"table-responsive border"t>'
                +'<"row"<"col-md-6 small"i><"#pagination.col-md-6 mt-3"p>>',
        "columnDefs": [{
            orderable: false,
            targets: 9
        }],
        "order": [[ 0, 'asc' ],[ 1, 'asc' ]],
        "fnDrawCallback": function(oSettings) {
            $('#dataTable-usersList_filter input').addClass('search')
            $('#loader').hide()
        }
    })
    
    // Hide length select & pagination if only one page
    if (table.data().count() <= 10) {
       $('#length, #pagination .dataTables_paginate').hide()
       $('#search .dataTables_filter').addClass('text-left')
    }

    // Reset search filter
    $('#users-list').on('keyup paste', 'input.search', function() {
        $(this).parent().find('.clean-search').remove('.clean-search')
        if ($(this).val() != '') {
            $(this).parent().append('<span class="clean-search"><i class="fas fa-times"></i></span>')
        }
    })
    $('#users-list').on('click', '.clean-search', function() {
        table.search('').columns().search('').draw();
        $(this).remove()
    })
    
})