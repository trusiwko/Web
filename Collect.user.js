// ==UserScript==
// @name         Collect
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  Bank operations collector
// @author       usbo
// @match        https://mybank.oplata.kykyryza.ru/
// @match        https://iclick.imoneybank.ru/card/*
// @match        https://my.tinkoff.ru/*
// @updateURL    https://raw.githubusercontent.com/trusiwko/Web/master/Collect.user.js
// @grant        GM_getValue
// @grant        GM_setValue
// ==/UserScript==
/* jshint -W097 */
'use strict';

// GM_setValue( 'acc_need', false );
// GM_setValue( 'secret', 'secret' );

if (location.hostname != "iclick.imoneybank.ru") {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = "https://code.jquery.com/jquery-2.1.4.min.js";
    s.innerHTML = '$.noConflict();'
    document.head.appendChild(s);
}

function waitForFnc(){
  if(typeof jQuery == "undefined"){
      window.setTimeout(waitForFnc,100);
  }
  else{
      start();
  }
}

waitForFnc();

// Секретное слово для запроса в usbo.info
var usbo_secret;

function start() {
    console.log('Btn');
    usbo_secret = GM_getValue( 'secret', '-' );
    if (usbo_secret == '-') 
        alert('ВНИМАНИЕ! Необходимо установить секрет');
    var div = jQuery('<div />').attr('id', 'usbo_btn').css('z-index', 2).css('position', 'fixed').css('top', 0).css('right', 0).css('padding', 10).css('padding-right', 20).appendTo($('body'));
    var btn = jQuery('<button />').html('+ usbo.info').appendTo(div).on('click', syncStart);
}

var nsend = 0;
var arr = new Array();
var res = new Array();
var account;
var type;

function open_tinkoff() {
  if($('.m-timeline__dropdown-menu').length == 0){
      window.setTimeout(open_tinkoff,100);
  }
  else{
      start_tinkoff();
  }
}

function start_tinkoff() {
    var a = $('.m-timeline__dropdown-menu').find('.ui-menu__link:first').attr('href');
    $.ajax({
        url: a,
        contentType: 'Content-type: text/csv; charset=windows-1251',
        success: function(data) {
            //console.log(data);
            arr = data.split("\n");
            next();
        },
        beforeSend: function(jqXHR) { jqXHR.overrideMimeType('text/csv;charset=windows-1251'); }
    });
    if (!GM_getValue( 'acc_need', true )) {
        account = '-';
    }
}

function syncStart() {
    console.log('start');
    arr = new Array();
    res = new Array();
    nsend = 0;
    if (location.hostname == "mybank.oplata.kykyryza.ru") {
        account = $('.slider_cards_card_shirt_inner_back_ean_first').html() + $('.slider_cards_card_shirt_inner_back_ean_second').html() + $('.slider_cards_card_shirt_inner_back_ean_third').html();
        type = 'Кукуруза';
        $('.history_operations_day_operation').each(function(a, b) {

            var o = {id: '', date: '', desc: '', group: '', sum: 0.0, curr: '', cb: 0.0};

            var c = $(b).attr('data-reactid').match(/\$([\d]{4})\.\$([\d]{1,2})\.\$([\d]{1,2})/); // .0.$main-view.0.$ad1d6be9.0.$operations.$2014.$11.$1.$21611673.$operation
            if (c != null)
                o.date = c[1] + '-' + (parseInt(c[2]) + 1) + '-' + c[3];

            o.id = $(b).attr('data-hst-item-id');

            var c = $(b).find('.history_operations_day_operation_info_title');
            o.desc = c.html();

            var c = $(b).find('.history_operations_day_operation_info_tag');
            if (c.length) {
                o.group = c.html();
            }

            var c = $(b).find('.history_operations_day_operation_amount_money');

            var d = $(c).find('.currency');
            if (d.length) {
                var s = d.find('.currency_integer').html();
                s = s.replace(/ /g, '').replace(/ /g, '');
                o.sum = parseInt(s) + parseInt(d.find('.currency_decimal').html()) / 100;
                if (d.hasClass('currency__negative')) o.sum = -o.sum;
                o.curr = d.attr('data-symbol');
            }

            var c = $(b).find('.history_operations_day_operation_amount_bonus');
            var d = $(c).find('.currency');
            if (d.length) {
                var s = d.find('.currency_integer').html();
                s = s.replace(/ /g, '').replace(/ /g, '');
                o.cb = parseInt(s);
                if (d.hasClass('currency__negative')) o.cb = -o.cb;
            }
            arr.push(o);
        });
    } else if (location.hostname == 'iclick.imoneybank.ru') {
        type = 'iMoney';
        account = $('#StatementForm').find('dd').find('a[href^="/account/"]').html().trim();
        $('#StatementForm .items .item p').each(function(a,b) {
            var o = {date: '', desc: '', sum: 0.0};
            $(b).children('span').each(function(c,d) {
                if (c == 0) 
                    o.date = $(d).html();
                if (c == 1) {
                   o.sum = $(d).find('span').html();
                }
                if (c == 2) {
                   o.desc = $(d).html();
                }
            });
            arr.push(o);
        });
    } else if (location.hostname == 'my.tinkoff.ru') {
        type = 'Tinkoff';
        account = $('#ui-accounts-info').find('.ui-module__header-title').text();
        if ($('.m-timeline__dropdown-menu').length == 0)
          $('.m-timeline__export-tooltip').click();
        open_tinkoff();
        return;
        
        /*
        $('.m-timeline__item').each(function(a,b) {
            var o = {date: '', desc: '', sum: '', group: '', cash: ''};
            var d = $(b).find('.m-timeline__item-header').find('.m-timeline__date-short');
            o.date = d.find('.m-timeline__day').text() + ' ' + d.find('.m-timeline__month').text();
            o.desc = $(b).find('.m-timeline__operation-name').text();
            var e = $(b).find('.ui-money_size_l');
            if (!e.hasClass('ui-money_color_red'))
                o.sum = e.text();
            o.group = $(b).find('.m-timeline__category').text();
            o.cash = $(b).find('.m-timeline__bonus').find('span:first').text();
            if (o.sum != '')
                arr.push(o);
        });
        */
    }
    if (!GM_getValue( 'acc_need', true )) {
        account = '-';
    }
    next();
}

function next() {
    var a = arr.splice(0, 50);
    if (a.length) {
        $.post('https://usbo.info/collect/save/', {part: nsend, secret: usbo_secret, type: type, account: account, data: a}, function(data){
            res.push(data);
            next();
        });
        nsend++;
    } else {
        alert(res.join("\n"));
    }
}