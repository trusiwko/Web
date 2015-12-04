// ==UserScript==
// @name         Collect
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  Bank operations collector
// @author       usbo
// @match        https://mybank.oplata.kykyryza.ru/
// @grant        none
// ==/UserScript==
/* jshint -W097 */
'use strict';

var s = document.createElement("script");
s.type = "text/javascript";
s.src = "https://code.jquery.com/jquery-2.1.4.min.js";
document.head.appendChild(s);

function waitForFnc(){
  if(typeof jQuery == "undefined"){
      window.setTimeout(waitForFnc,1000);
  }
  else{
      start();
  }
}

waitForFnc();

function start() {
    var div = $('<div />').css('position', 'fixed').css('top', 0).css('right', 0).css('padding', 10).css('padding-right', 20).appendTo($('body'));
    var btn = $('<button />').html('Синхронизировать с USBO.INFO').appendTo(div).on('click', syncStart);
}

function syncStart() {
    console.log('Go');
    var ean = $('.slider_cards_card_shirt_inner_back_ean_first').html() + $('.slider_cards_card_shirt_inner_back_ean_second').html() + $('.slider_cards_card_shirt_inner_back_ean_third').html();
    var arr = new Array();
    $('.history_operations_day_operation').each(function(a, b) {
        
        var o = {id: '', date: '', desc: '', group: '', sum: 0.0, curr: '', cb: 0.0};
        
        var c = $(b).attr('data-reactid').match(/\$([\d]{4})\.\$([\d]{1,2})\.\$([\d]{1,2})/); // .0.$main-view.0.$ad1d6be9.0.$operations.$2014.$11.$1.$21611673.$operation
        if (c != null)
            o.date = c[1] + '-' + c[2] + '-' + c[3];
        
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
        //console.log(o);
    });
    $.post('https://usbo.info/collect/save/', {secret: 'secret', type: 'Кукуруза', ean: ean, data: arr}, function(data){
       console.log(data);
        //alert(data);
    });
}