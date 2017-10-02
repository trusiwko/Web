// ==UserScript==
// @name         TIME.EPAM
// @version      0.2
// @author       usbo
// @match        https://time.epam.com/journal/4060741400007522479/*
// @grant        none
// @require      http://code.jquery.com/jquery-1.12.4.min.js
// ==/UserScript==

$.noConflict();

(function() {
    'use strict';
    jQuery('<button />').text('Суммировать').prependTo('#root').on('click', function() {
        jQuery('.table-task').each(function(a,b) {
            var s = 0;
            jQuery(b).find('.table-activity-cell').find('input').each(function(a1,b1) {
                var v = jQuery(b1).val();
                if (v != "") {
                    s += parseFloat(v);
                }
            });
            var xa = jQuery('<span />').html('<strong>[' + s + ']</strong>&nbsp;&nbsp;&nbsp;');
            jQuery(b).find('.table-name-container').prepend(xa);
            jQuery(b).find('.nested-data').hide();
            var ya = jQuery('<a />').attr('href', '#').html('🔽&nbsp;').on('click', function() {
                jQuery(this).parent().parent().parent().next().toggle();
                return false;
            });
            jQuery(b).find('.table-name-container').prepend(ya);
            if (s == 0) jQuery(b).hide();
        });
    });
    // Your code here...
})();
