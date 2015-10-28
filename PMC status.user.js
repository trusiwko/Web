// ==UserScript==
// @name         PMC status
// @version      0.1
// @author       Pavel_Goncharenko
// @match        https://pmcpct.epam.com/pmc/timejournal/*
// @match        https://pmcmsq.epam.com/pmc/timejournal/*
// @grant        GM_addStyle
// ==/UserScript==

GM_addStyle(".wdStatus.Verified, .wdStatus.Проверен{color:green}\
.wdStatus.Assigned, .wdStatus.Назначен, .wdStatus.Назначена{color:red}\
.wdStatus.Fixed, .wdStatus.Исправлен{color:green}\
.wdStatus.Implemented, .wdStatus.Выполнено{color:green}\
.wdStatus.Resolved, .wdStatus.Устранена{color:green}\
.wdStatus.Declined, .wdStatus.Отклонено{color:#cc0}\
.wdStatus.Cancelled, .wdStatus.Отменена{color:#cc0}\
.wdStatus.Deferred {color:#09F}\
.wdStatus.Pending-Estimation, .wdStatus.Pending-review, .wdStatus.Предварительная-оценка, .wdStatus.На-рассмотрении{color:blue}\
.wdStatus.Reviewed, .wdStatus.Estimated, .wdStatus.Оценка, .wdStatus.Рассмотрено{color:#045FB4}\
.wdStatus.Closed, .wdStatus.Закрыто{color:green}\
.mystatus.Completed, .mystatus.Завершено{color:green}\
.mystatus.Not-Started, .mystatus.Не-начато{color:red}\
.mystatus.In-Progress, .mystatus.В-процессе-выполнения{color:orange}\
.mystatus.On-Hold, .mystatus.Остановлено{color:#663300}\
.mystatus.Cancelled, .mystatus.Отменена{color:#0B3D17}\
.displaynone{display:none}\
.mydate{color:#6D0000}\
.myline{width:12px;height:12px;background-repeat:no-repeat;background-position:center center}\
.myBug, .myДефект{background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA8AAAAQCAYAAADJViUEAAAB8UlEQVQoU4WTPWjVUBTH/+cmqVIpxQcu8ppcbx6CxQ+6FAUF0c2quDoqCC5+DM6uroJScBQ6OtV2cerkUtAiVBCS3tzwRKFFRaVo03uPJM/4Puzj3SHcc3J+OR//HAKAVqu1L0mSAoArbRWqsyD3jIBpBt4T+FZqzOvy3d9DALh8oBUdecFst9I8vx2G4cGAoSHEZE/wVwp8mSTJ91YoH1m4YzrPr1WwiqI5Ai2C3azP/MkK/1IPWF0LuOWAuWGJ1jwWN5JcL1RwJ3v0kh2fA+Nb7SsEZgKHt91ixSQE3qQmu1j6/sFxHE/B2qdgXK2DvWLskA12NruN8tIu0Z0sy7J+eCo+DthVCOwfBjvnfsP3ZrXW7/rgqmzQ5d5eBzN3AF5KjLlSw0IpNcHWbgmQPwp2zhXjvyYa65vr26Qi+ZOAA4PTLe29Mtdxjt0PUkod5V2OBPGrwQ8Mh/kCW6/dnXYzzOCJaFTZDOgNk6m+galQ3iPC41EwmO6muX7SP+0wnGYSqwDGh0kFYNsJOvOfVHEUrTggJuAjOTpV6l33XOpLgtaY+bAQ4nOaZafLJer821JeZ+uew/dOaK0/NJvNxpjnnRcmWHZRMbdj7Uq73f4ipZQeV1v2IDFmvt6qBYbbSI15uJdkvT4l5X3BOJmY7OYf5eXv5ygto0UAAAAASUVORK5CYII=)}\
.mySupport-Request, .myЗапрос-на-поддержку{background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABUklEQVQ4T6WTMUtcURCFvzOCUbFRSCOIpZWtFv4CC6ugpIldQMJaWghaKvZqoZ0gBDGpLPILLExtYyuxEkSIoKtyj1x5C7tv3+4K3nZmvplzZq744FOH+v6I2Lb9DeiTdJRSWgXq5fxKQETs2K41J0vaTyktvwcQkv4DQ6Xkuu1h4KUFXCFhTNJ1lTTb48C/boDPEbFne6GDN79s/wBuGvFmDz5Juq0Yvcx6tD0KPORA2cTpiKjZngHyuINF9SNwJelvSmkXOK+aoNxpSNJdbmJ7BLivktXpDlYkfQemiqIL2wfATrc1Zg9+294AJiUdAv1FwZPtJeBS0qbtL0CW1eLBmqStrNX2XGHSfAE4BQYk/QEmbK8Dmy2AiPhp+2ujo6TjlNIZ4IiYLWJvE0k6SSktdgP0+mL5Ht5updnEJUl5RQ3dnSDPxT/JHrXdQa/ObfFXCsV8EdAXicIAAAAASUVORK5CYII=)}\
.myTask, .myЗадание {background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAsUlEQVQ4T8XSrRJBQRjG8Z+gEEguQyQoClFRRDOqpqqq5gLcg2QoxhjnbhSZwRlzfKyDwJt2dvf5v19PBhEqvosog8N32osqCTidP4lz4p8B2mihm2j5ZQVF7OJ+GpghizpW8X0QUMIaUyyxQB4DjBNDCgLKcZYC9rF4hOHdhF+2UMMcOUzQf7Ke1C000UEv4JVUQJonHgBJQchUD64NWfn/gLT+r++nUreovq24/bg5AlxlLZDFIPmMAAAAAElFTkSuQmCC)}\
.myRequirement, .myТребование {background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAsAAAAQCAYAAADAvYV+AAAAwklEQVQoU43SMUpDMRgH8N8TBFEnh649QUdnB8HJwQt0FrSDvUW3OsgDR2/QC3To5uBJHEqHKgWhlECUvJD3nhmTX/75viQVNnhXHkO8YRaWKyxw14KfMMcj6hSHTefJpnvc4jnOjfuSR5jiCDcpfsVZoZwvhFMWKb6ICbnfY53j0MxJIXkX624kX+G4gH+wypOvO/Ayx5OOMl5yPOho8DPHNU4LNX/jIcctL/433biN/Ll/1Tb+nQb+V3L4oh99EpcHW7sx1C5WtmMAAAAASUVORK5CYII=)}\
");

var tmp = new Array();      // два вспомогательных
var tmp2 = new Array();     // массива
var status_hide = 0;        // скрыть закрытые
var status = 0;             // Статус
var get = location.search;  // строка GET запроса
var id_list = new Array();

var sync = 0; // Асинхронный вызов (1, 0)

if (get != '') {
    tmp = (get.substr(1)).split('&');   // разделяем переменные
    for(var i=0; i < tmp.length; i++) {
        tmp2 = tmp[i].split('=');       // массив param будет содержать
        if (tmp2[0] == 'status_hide') status_hide = 1;
        if (tmp2[0] == 'status') status = 1;
    }
}
var statused = 0;

function loadnext() {
    if (id_list.length == 0) {
        return;
    }
    id = id_list[0];
    id_list = id_list.splice(1);
    loadnextid(id);
}

function loadnextid(id) {
    $.get( "artifact/detail.do?id=" + id, function( data ) {
        m = data.match(/<span class="wdStatus"><input type="hidden" name="state.name" value="([^"]*)">/i);
        wdStatus = (m != null) ? m[1] : '';
        m2 = data.match(/<a[^>]*id="detailHref"[^>]*>([\d]*)</i);
        m3 = data.match(/showDialog\('assignmentedit\.do\?id=([\d]*)'\);/i);
        sre4 = new RegExp('(?:Work complete|Завершено работы):&nbsp;<b>([^%]*)%,&nbsp;([^<]*).*?(?:Planned duration: From|Запланированная длительность: С)&nbsp;<b>([^<]*).*?<a href="[^>]*?id=' + m3[1] + '"', 'i');
        mystatus1 = '';
        mystatus2 = '';
        mystatus3 = '';
        mydate1 = '';
        m4 = data.match(sre4);
        if (m4 != null) {
            mystatus1 = m4[1];
            mystatus2 = m4[2].replace(/\s/g, "-");
            mystatus3 = m4[2];
            mydate1 = m4[3];
        }
        m5 = data.match(/<h1><span><p class="name" style="width:99%;">(.*?):/i);
        var myline = ''; // Тут почему-то очень часто глючит, сервак ошибается. Видать от быстрой загрузки...
        if (m5 != null) {
            myline = m5[1]; // Требование, Запрос на поддержку, Дефект, Задание // Bug, Requirement, Support Request, Task
            myline = myline.replace(/\s/g, "-"); // заменим пробелы на -
        }
        $('.rwTask').find('a').each(function(indx, value) {
            t2 = $(value).attr('href');
            if (t2 != undefined && t2.indexOf(m3[1]) > 0) {
                wStatus = wdStatus.replace(/\s/g, "-");
                var prc = ''; // prc = mystatus1 + '%,&nbsp;';
                var text = '<span class="mystatus '+mystatus2+'">'+prc+mystatus3+'</span> ';
                if (wdStatus != '') text += ' <span class="wdStatus '+wStatus+'">['+wdStatus+']</span>';
                if (mydate1 != '') text += ', <span class="mydate"> '+mydate1+'</span>';
                $(value).prepend(text + ' ');
                if (myline != '') {
                    $('<div />').addClass('addactivity').addClass('myline').addClass('my' + myline).insertBefore($(value));
                }
            }
        });

        if (status_hide == 1) {
            $('.blTimeJournal').find('tr').each(function(indx, value) {
                t = $(value).attr('id');
                if (m3 != null && wdStatus != '' && t.indexOf(m3[1]) >= 0 && (wdStatus == 'Fixed' || wdStatus == 'Resolved' || wdStatus == 'Declined' || wdStatus == 'Cancelled')) {
                    $(value).addClass('displaynone');
                }
            });
        }

        loadnext();

    });
}
function loadStatus() {
    if (statused == 1) 
        return;
    id_list = new Array();
    $('.rwTask').each(function(indx, value) {
        id = $(value).attr('id');
        if (id > 0) {
            if (sync == 1) {
                loadnextid(id);
            } else {
                id_list.push(id);
            }
        }
    });
    
    if (sync == 0)
        loadnext();
    
    statused = 1;
}
if (status == 1 || status_hide == 1) {
   loadStatus();
}
$butt = $('<input type="button" />')
  .addClass('btButton')
  .attr('id', 'button-status')
  .attr('value', 'Статус')
  .click(loadStatus);
$divb = $('<div />')
  .addClass('buttonContainer')
  .append($butt);
$('.formInputFilterPreLast')
  .append($divb);