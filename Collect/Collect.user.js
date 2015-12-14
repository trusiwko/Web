// ==UserScript==
// @name         Collect
// @namespace    http://tampermonkey.net/
// @version      0.5
// @description  Bank operations collector
// @author       usbo
// @match        https://mybank.oplata.kykyryza.ru/
// @match        https://iclick.imoneybank.ru/*
// @match        https://my.tinkoff.ru/*
// @match        https://connect.raiffeisen.ru/rba/*
// @match        https://online.vtb24.ru/content/telebank-client/ru/login/telebank/*
// @match        https://retail.sdm.ru/
// @match        https://ib.homecredit.ru/ibs/group/hcfb/*
// @updateURL    https://raw.githubusercontent.com/trusiwko/Web/master/Collect/Collect.user.js
// @grant        GM_getValue
// @grant        GM_setValue
// @grant        GM_xmlhttpRequest
// ==/UserScript==
/* jshint -W097 */
'use strict';

// GM_setValue( 'acc_need', false );
// GM_setValue( 'secret', 'secret' );

if (location.hostname == "mybank.oplata.kykyryza.ru" || location.hostname == 'my.tinkoff.ru') {
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
    var div = jQuery('<div />').attr('id', 'usbo_btn').css('z-index', 9001).css('position', 'fixed').css('top', 0).css('right', 10).css('padding', 10).appendTo($('body'));
    var btn = jQuery('<button />').html('+ usbo.info').appendTo(div);
    if (location.hostname == 'retail.sdm.ru') {
        btn.bind( "click", syncStart);
    } else {
        btn.on('click', syncStart);
    }
}

var nsend = 0;
var arr = new Array();
var res = new Array();
var account;
var type;

function remove_spaces(s) {
    return s.replace(/ /g, '').replace(/ /g, '').replace(/\u00a0/g, '');
}

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
            arr.reverse();
            next();
        },
        beforeSend: function(jqXHR) { jqXHR.overrideMimeType('text/csv;charset=windows-1251'); }
    });
    if (!GM_getValue( 'acc_need', true )) {
        account = '-';
    }
}

function vtb1() {
	var e = $('.js-head').data('id');
	var h = 'statementRootElement';
	var j = 'CompositeStatement';
	var l = 'Csv';
	var g = $('.productsStatement');
	if (!e || !j) {
		TBC.log("submitDownloadRequest skipped. id or classType not found. objectId: " + e + ", objectClassType: " + j);
		return
	}
	var k = j + ":" + e + ":" + l;
	function f(n) {
		var m = n.errors[0].msg || TBC.I18n.get("label.products.failedTimeOutMessage");
		g.hideLoader();
		c.showInformationPopUp(m)
	}
	function d(m) {
		if (topicUtils.hasError(m)) {
			f(m)
		} else {
			g.hideLoader();
			
            var pd = {
				cacheKey: 'CompositeStatement:' + $('.js-head').data('id') + ':Csv',
				errorPage: "/content/telebank-client/" + TBC.I18n.getLocale() + "/error-pages/500.html",
				pageToken: TBC.securityManager.getPageToken(),
				format: 'Csv'		
			};
            
            $.ajax({
                method: "POST",
                url: "/processor/process/minerva/document",
                //contentType: 'Content-type: text/csv; charset=windows-1251', // Странно, но не работает тут
                data: pd,
                success: function(data) {
                    //console.log(data);
                    arr = data.split("\n");
                    arr.reverse();
                    next();
                },
                beforeSend: function(jqXHR) { jqXHR.overrideMimeType('text/csv;charset=windows-1251'); }
            });

		}
	}
	TBC.dataProvider.registerComponent({
		id: h,
		callback: d,
		failedCallback: f
	}, [{
		id: "DOCUMENT_PRESENTATION",
		execute: true,
		getIncomeParams: {
			cacheKey: k,
			fileName: "statement"
		},
		params: {
			objectId: e,
			className: j,
			format: l,
			fileName: "details",
			pageToken: TBC.securityManager.getPageToken()
		}
	}]);
	g.showLoader()
}

function hcb2() {
    var o = false;
    var date = '';
    var desc = '';
    var group = '';
    var sum = '';
    var curr = '';
    arr = new Array();
    $("[id$='printOperations']").find('tr').each(function(a,b) {
        $(b).find('td').each(function(a,c) {
            var atr = $(c).attr('colspan');
            if (typeof atr != "undefined" && atr == '3') {
                if (o != false) {
                    arr.push({date: date, desc: desc, group: group, sum: sum, curr: curr});
                    desc = '';
                    group = '';
                    sum = '';
                    curr = '';
                    o = false;
                }
                var d = $(c).find('div b').text().trim();
                if (d != '')
                    date = d;
            }
            if ($(c).hasClass('LINE-1')) {
                var d = $(c).find('div').text().trim();
                desc = d;
            }
            if ($(c).hasClass('LINE-2')) {
                var d = $(c).find('div').text().trim();
                group = d;
            }
            if ($(c).hasClass('AMOUNT')) {
                var d = $(c).html();
                var e = d.match(/<span[^>]*>([^<]*)<\/span>&nbsp;([^<]*)<span class="([^"]*)"/);
                var f = remove_spaces(e[2].trim());
                sum = parseFloat(f.replace(',', '.'));
                if (e[1] == '-')
                    sum = -sum;
                curr = e[3];
                o = true;
            }
        });
    });
    arr.reverse();
    next();
}

function hcb() {
    var src = loadPrintMovements.toString().match(/source:'(.*?)'/)[1];
    var id = src.split(':');
    var frm = id.slice(0,3).join(':');
    var upd = id.slice(0,4).join(':') + ':printOperationsComponent';
    
    PrimeFaces.ab({
        source: src,
        formId: frm,
        update: upd,
        oncomplete: function(xhr, status, args) {
            hcb2();
        },
        params: undefined
    });
}

function syncStart() {
    console.log('start');
    arr = new Array();
    res = new Array();
    nsend = 0;
    if (location.hostname == "mybank.oplata.kykyryza.ru") {
        account = $('.card-list_card_name_text').text();
        if (account == "") account = $('.slider_cards_card_info_card-name').text();
        //$('.slider_cards_card_shirt_inner_back_ean_first').html() + $('.slider_cards_card_shirt_inner_back_ean_second').html() + $('.slider_cards_card_shirt_inner_back_ean_third').html();
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
        arr.reverse();
    } else if (location.hostname == 'iclick.imoneybank.ru') {
        type = 'iMoney';
        var acc = $('#main .inner h1 span').text(); //$('#StatementForm').find('dd').find('a[href^="/account/"]').html().trim();
        acc = acc.split("\n");
        account = acc[0];
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
        arr.reverse();
    } else if (location.hostname == 'my.tinkoff.ru') {
        type = 'Tinkoff';
        account = $('#ui-accounts-info').find('.ui-module__header-title').text();
        if ($('.m-timeline__dropdown-menu').length == 0)
          $('.m-timeline__export-tooltip').click();
        open_tinkoff();
        return;
    } else if (location.hostname == 'connect.raiffeisen.ru') {
        type = 'Raiffeisen';
        account = $('option[value="'+$('select[name="objectId"]').val()+'"]').text().split(" ")[0];
        //getReport('showStatementForm', 'CSV')
        var link = $('#showStatementForm').attr('action');
        var data = {};
        $('#showStatementForm').find('input').each(function(a,b) {
            if (typeof $(b).attr('name') != 'undefined') {
                data[$(b).attr('name')] = $(b).val();
            }
        });
        $('#showStatementForm').find('select').each(function(a,b) {
            if (typeof $(b).attr('name') != 'undefined') {
                data[$(b).attr('name')] = $(b).val();
            }
        });
        data['reportType'] = 'CSV';
        //console.log(data);
        $.post(link, data, function(data) {
            arr = data.split("\n");
            next();
        });
        return;
    } else if (location.hostname == 'online.vtb24.ru') {
        type = 'VTB24';
        account = $('.customCheckbox.checked').parent().parent().find('.productInfo i span').text();
        vtb1();
        return;
    } else if (location.hostname == 'retail.sdm.ru') {
        type = 'SDM';
        var t = $('#account_data tr:first td:first').text().split(" ");
        account = t[0];
        var currency = t[1];
        
        $('.Data-Grid tbody tr').each(function(a,b){
            var o = {id: '', date: '', desc: '', sum: 0.0, curr: currency};
            var go = false;
            $(b).find('td').each(function(a,c) {
                var d = $(c).text();
                if (a == 0) {
                    o.date = d;
                } else if (a == 1) {
                    o.id = d;
                } else if (a == 2 && d.trim() != '') {
                    d = d.replace(/ /g, '').replace(/\u00a0/g, '');
                    o.sum = -1 * parseFloat(d);
                } else if (a == 3 && d.trim() != '') {
                    d = d.replace(/ /g, '').replace(/\u00a0/g, '');
                    o.sum = parseFloat(d);
                } else if (a == 4) {
                    o.desc = d;
                    go = true;
                }
            });
            if (go)
              arr.push(o);
        });
        
    } else if (location.hostname == 'ib.homecredit.ru') {
        type = 'HomeCredit';
        account = $('.friendlyNameBox td:first').text();
        hcb();
        return;
    }
    if (!GM_getValue( 'acc_need', true )) {
        account = '-';
    }
    next();
}

function next() {
    var a = arr.splice(0, 50);
    if (a.length) {
        if (location.hostname == 'online.vtb24.ru') {
            // $.param не работает с массивами в этой версии jQuery
            var ks = "part=" + nsend + "&secret=" + usbo_secret + "&type=" + type + "&account=" + account;
            for(var i=0;i<a.length;i++) {ks += '&data[]=' + encodeURIComponent(a[i]);}
            GM_xmlhttpRequest({
                method: "POST",
                url: 'https://usbo.info/collect/save/',
                data: ks,
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                onload: function(data) {
                    res.push(data.responseText);
                    next();
                },
                onerror: function(data) {
                    console.log('ERROR', data);
                    res.push(data.responseText);
                    next();
                }
            });
        } else {
            $.post('https://usbo.info/collect/save/', {part: nsend, secret: usbo_secret, type: type, account: account, data: a}, function(data){
                res.push(data);
                next();
            });
        }
        nsend++;
    } else {
        alert(res.join("\n"));
    }
}