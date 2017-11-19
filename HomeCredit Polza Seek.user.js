// ==UserScript==
// @name         HomeCredit Polza Seek
// @version      0.1
// @description  The best sum for compensation
// @author       usbo
// @match        https://polza.homecredit.ru/ib
// @grant        none
// ==/UserScript==

findTr = function(e) {
    var maxLimit = parseFloat($('.main-circle .value').text().replace(',', '.').replace(' ', ''));
    var maxSum = 0;
    var mtr = null;
    $('.main-operations tr[data-sum]').each(function(a,b) {
        var s = parseFloat($(b).data('sum'));
        if (s > maxSum && s <= maxLimit && $(b).data('sum-words') != "0 рублей") {
            maxSum = s;
            mtr = b;
        }
    });
    if (mtr !== null) {
        $(mtr).css('background-color', 'maroon');
        $('html, body').animate({
            scrollTop: $(mtr).offset().top
        }, 500);
    } else {
        alert('Не найдено ни одной подходящей записи.');
    }
};
loadTr = function(e) {
   $("html, body").animate({ scrollTop: $(document).height() }, "fast");
};

(function() {
    'use strict';
    $(function() {
        var $d = $('<div />').attr('id', 'plugbar').css({position: "fixed", top: 0, backgroundColor: "blue", padding: "5px"}).prependTo($('body'));
        $('<button />').text('Найти').on('click', findTr).appendTo($d);
        $('<button />').text('Подгрузить').on('click', loadTr).appendTo($d);
    });
})();
