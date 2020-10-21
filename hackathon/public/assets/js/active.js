(function ($) {
    'use strict';

    var preview_window = $(window);

    // :: 2.0 Menu Active Code
    if ($.fn.classyNav) {
        $('#classyNav').classyNav();
    }

    // :: onePageNav Active JS
    if ($.fn.onePageNav) {
        $('#nav').onePageNav();
    }

    // preventDefault a Click
    $("a[href='#']").on('click', function ($) {
        $.preventDefault();
    });
    
})(jQuery);