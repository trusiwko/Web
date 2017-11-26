// ==UserScript==
// @name         Grepolis Helper
// @namespace    http://tampermonkey.net/
// @version      0.21
// @description  try to take over the world!
// @author       usbo
// @match        https://ru59.grepolis.com/game/*
// @grant        none
// ==/UserScript==

var col_timer = 0;
var col_step_timer = 0;
var cur_town = 0;
var btnstate = 0;
var timeleft = 0;
var timelefttimer = 0;
var town_id = 0;

(function() {
    'use strict';

    var dd = $('<div style="z-index:9999;position:fixed;top:0; left:0" />').appendTo($('body'));
    $('<div style="color:white" />').attr('id', 'col_timer_div').appendTo(dd);
    $('<input type="checkbox">').on('click', function() {
        if ($(this).prop('checked')) {
            timeleft = 5.15*60;
            clearInterval(timelefttimer);
            timelefttimer = setInterval(function() {
                $('#col_timer_div').text(timeleft);
                timeleft--;
            }, 1000);
            //
            clearInterval(col_timer);
            col_timer = setInterval(function() {
                timeleft = 5.15*60;
                clearInterval(col_step_timer);
                col_step_timer = setInterval(function() {
                    if ($('#fto_claim_button').length == 0) {
                        $('li.farm_town_overview').find('a[name="farm_town_overview"]').click();
                    } else {
                        if (btnstate == 0) {
                            //console.log($('li[data-town_id="'+town_id+'"]'));
                            var li = $('#fto_town_list .fto_town').eq(cur_town);
                            li.click();
                            town_id = li.data('town_id');
                            btnstate = 1;
                        } else {
                            var wd = $('#fto_wood_exceeded').find('span').hasClass('town_storage_full');
                            var ws = $('#fto_stone_exceeded').find('span').hasClass('town_storage_full');
                            var wi = $('#fto_iron_exceeded').find('span').hasClass('town_storage_full');
                            if (wd || ws || wi) {
                                console.log(town_id + " is full.");
                            } else {
                                $('#fto_claim_button').click();
                            }
                            cur_town++;
                            if (cur_town == $('#fto_town_list .fto_town').length) {
                                cur_town = 0;
                                clearInterval(col_step_timer);
                            }
                            btnstate = 0;
                        }
                    }
                }, 2000);
            }, 5.15 * 60 * 1000);
        } else {
            clearInterval(col_timer);
            clearInterval(timelefttimer);
        }
    }).appendTo(dd);
})();
