// ==UserScript==
// @name         PMC Summarize
// @version      0.21
// @description  Get this sum!
// @author       Pavel Goncharenko
// @match        https://pmc.epam.com/pmc/timereport/list.do
// @grant        none
// ==/UserScript==

(function() {
    'use strict';
    if ($('select[name=groupBy]').val() == "assignmentId") {
        var allsum = 0;
        $('tbody.listTableContentUnion').each(function(a,b) {
            var body = $(b);
            var head = $(b).prev();
            var rfc = body.find('tr').eq(0).find('td').eq(2).find('a').text();
            rfc = rfc.split(" ")[0];
            head.find('a').html(head.find('a').html().replace(/Assignment ID:/, rfc + ': ').replace(/ID Назначения:/, rfc + ': '));
            var sum = 0;
            body.find('tr').each(function(c,d) {
                var n = $(d).find('td').eq(5).text();
                sum += parseFloat(n);
            });
            allsum += sum;
            head.find('.wdGroupBy').text(sum);
        });
        $('<div />').css('fontSize', '120%').css('padding', '10px').html('Итого: <strong>' + allsum + '</strong>').insertAfter($('.listTableContainer'));
    }
})();
