<script type="text/javascript">
	var mAdd = new Array();
	var mHoliday = new Array();
	var mEvent = new Array();
	var mFactPayment = new Array();
	var mFactSum = new Array();
    var mDeposits = new Array();
	var oldFactDate;
	var oldPaySum;
	var iSig = 100;
	var iSigRaif = 100000000;
	var iPerfectSig = 50;
	var public_id = <?=$public_id?>;
	var user_id = <?php if($user_data): ?><?=$user_data['id']?><?php else: ?>'unregistered'<?php endif; ?>;
	
	// settings
	var gUseLeapYearType = 1; // 1 - Использовать перерасчет при високосном годе с 1 января, 2 - Использовать перерасчет при високосном годе с 31 декабря.
	
	var latestExRate = 1; // Для валютных ипотек
	
	var chart_data = new Array();
	
	var gCreditPercentYear = 0; // процентная ставка по кредиту за год
	var gCreditPercentMonth = 0; // процентная ставка по кредиту в месяц
	var gCreditMonthCount = 0; // количество месяцев кредита
	var gFirstPayDate; // первая дата платежа
	var gContractDate; // дата оформления
	var gBankName; // наименование банка
    var gFirstPercent = 1; // первый платеж - проценты
	var currentDate; // текущая дата
	var gLine; // линия для подсветки

	function toD(s) {
		d = new Date(s);
		d.setHours(0,0,0,0);
		return d;
	}
	function daysDiff(d1,d2) {
		return Math.round((d1-d2)/60/60/24/1000);
	}
	function monthDiff(d1,d2) {
		var months;
		months = (d2.getFullYear() - d1.getFullYear()) * 12;
		months -= d1.getMonth() + 1;
		months += d2.getMonth();
		if (d1.getDate() <= d2.getDate()) {
			months++;
		}
		return months <= 0 ? 0 : months;
	}	
    function IsLeapYear(aYear)
    {
        return((((aYear%4==0) && (aYear%100!=0)) || (aYear%400==0)) ? true : false);
    }
	function credit_sum_changed() {
		month_payment_calc();
	}
	function credit_month_changed() {
		month_payment_calc();
	}
    function credit_percent_changed() {
        month_percent_changed();
    }
	function month_percent_changed() {
		month_payment_calc();
	}
	
	function sleep(ms) {
		ms += new Date().getTime();
		while (new Date() < ms){}
	} 
    
    function calcPercent(A, d1, d2) {
        nY = 365;
        if (gUseLeapYearType == 1 && IsLeapYear(d2.getFullYear())) nY = 366;
		if (gUseLeapYearType == 2 && IsLeapYear(d1.getFullYear())) nY = 366;
        return A*gCreditPercentYear*daysDiff(d1, d2)/ nY;
    }
	
	function month_payment_calc() {
		var A = toN($('#credit-sum').val());
		//var N = toN($('#credit-month').val());
        //var S = toN($('#credit-percent').val()) / 12 / 100;
		//var R = A*(S/(1-Math.pow(1+S,-(N-1))));
		
		readGVars();
		R = calcMonthlyPayment(A, "");
		R2 = calcMonthlyPayment(A, "", 1);
		
		if (!isNaN(R)) {
			$('#month-payment').val(R);
			$('#month-payment').attr('title', R2);
		} else {
			$('#month-payment').val('');
		}
	}
	
    // data = {comment, chartIndex}
    function addCommentLine(data) {
		if (data.chartIndex == 0) {
			$res = $('#result-body');
			$tr = $('<tr />').appendTo($res).addClass('comment-line');
			$td = $('<td colspan="7"/>').appendTo($tr);
			$td.html(data.comment);
        }
    }
	
	function saveFactDate() {
		var ds = $('#new-fact-date').val();
		var newFactDate = toD(ds);
		if ('Invalid Date' == newFactDate && ds != '') {
			alert('Дата платежа должна быть введена в локальном формате, либо в формате гггг-мм-дд');
		} else {
			var found = 0;
			var i = 0;
			mFactPayment.forEach(function(a) {
				if (DtoS(a.oldDate) == DtoS(oldFactDate)) {
					if ('Invalid Date' != newFactDate) {
						a.newDate = newFactDate;
					} else {
						mFactPayment.splice(i, 1);
					}
					found = 1;
					i++;
				}
			});
			if (oldFactDate > newFactDate) {
				alert('Обратите внимание! Введенная дата платежа должна быть больше предполагаемой даты.');
			} else if (oldFactDate + 10 > newFactDate) { 
				alert('Обратите внимание! Введенная дата платежа не должна превышать 10 дней от предполагаемой даты.');
			} 
			// else
				if (0 == found) {
					mFactPayment.push({oldDate: new Date(oldFactDate), newDate: new Date(newFactDate)});
					mFactPayment.sort(compareFactPayment);
				}
				calc(0);
				calc(1);
				calc(2);
				$('#myModal').modal('hide');
			
		}
	}
	
	function addFactDate(date) {
		oldFactDate = toD(date);
		$('#new-fact-date').val(date);
		$('#myModal').modal('show');
	}
	
	function saveFactSum() {
		var ds = $('#new-fact-sum').val();
		var newPaySum = toN(ds);
		var found = 0;
		var i = 0;
		mFactSum.forEach(function(a) {
			if (a.oldSum == oldPaySum) {
				if (!isNaN(newPaySum)) {
					a.newSum = newPaySum;
				} else {
					mFactSum.splice(i, 1);
				}
				found = 1;
				i++;
			}
		});
		if (!isNaN(newPaySum)) {
			if (0 == found) {
				mFactSum.push({oldSum: oldPaySum, newSum: newPaySum});
				//mFactSum.sort(compareFactPayment);
			}
		}
		calc(0);
		calc(1);
		calc(2);
		$('#myModal2').modal('hide');
	}
	
	function addPaySum(sum) {
		oldPaySum = sum;
		$('#new-fact-sum').val(sum);
		$('#myModal2').modal('show');
	}
	
	function saveRePaySum() {
		var newRePaySum = toN($('#new-fact-paysum').val());
		var newRePayDate = toD($('#new-fact-paydate').val());
		var found = 0;
		if (!isNaN(newRePaySum)) {
			mAdd.forEach(function(a) {
				if (found == 0 && DtoS(a.date) == DtoS(newRePayDate) && a.type == 'payment') {
					if (a.repeat == '') {
						a.sum = newRePaySum;
						found = 1;
					} else {
						// Сдвигаем плановый платеж на один период вперед, вместо него добавляем неповторяющийся
						var mnth = 0;
						if (a.repeat == 'month') {
							mnth = 1;
						} else if (o.repeat == 'year') {
							mnth = 12;
						}
						var cD = new Date(a.date);
						cD.setMonth(cD.getMonth() + mnth);
						addRepaySumm(a.date, newRePaySum, a.type);
						a.date = cD;
						found = 2;
					}
				}
			});
			if (0 == found) {
				alert('Досрочный платеж на указанную дату не найден.');
			}
		}
		addList();
		calc(0);
		calc(1);
		calc(2);
		$('#myModal3').modal('hide');
	}
	
	// редактируем сумму досрочного платежа
	function addRePaySum(sum, date, firstRePay, secondRePay) {
		$('#new-fact-paysum').val(sum);
		$('#new-fact-paydate').val(date);
        $('#firstRePay').html(firstRePay);
        $('#secondRePay').html(secondRePay);
		$('#myModal3').modal('show');
		// итог в saveRePaySum
	}	
    
	// data = {lineType, monthNumber, operationDate, operationDateEditable, operationDateEditableDate, operationSumEditable, restAmount, 
	//         loanPercentSum, loanPercentSumHint, loanMainSum, loanCommonSum, loanCommonSumHint, 
    //         chartIndex, paymentType}
    function addLine(options) {
	
		var data = {
			lineType : '', // Стиль строки
			monthNumber : '', // Номер месяца 
			operationDate : new Date(), // Дата операции
			operationDateEditable : false,
			operationDateEditableDate : new Date(),
			operationSumEditable: 0, // редактируемая дата ежемесячного платежа
			operationPaySumEditable: 0, // редактируемая дата досрочного платежа
			restAmount : 0, // Остаток основного долга
			loanPercentSum : 0, // Погашение процентов
			loanPercentSumHint: '', // Подсказка к полю "Погашение процентов" (Проценты до погашения основного долга)
			loanMainSum : 0, // Погашение основного долга
			loanCommonSum : 0, // Внесенная сумма (ежемесячный платеж или досрочное погашение)
			loanCommonSumHint: '', // Подсказка к полю "Внесенная сумма / Ежемесячный платеж"
			chartIndex: 0, // Визуализация
			paymentType: '' // Уменьшение ежемесячного платежа или срока
		}
		$.extend(data, options);
		
		if ((+data.operationDate >= +currentDate)) {
			if (chart_data[data.chartIndex].currentOperationDate == '') {
				if (+data.operationDate == +currentDate) {
					var style = ' curr now';
				} else {
					var style = ' curr';
				}
				data.lineType += style;
				chart_data[data.chartIndex].currentOperationDate = data.operationDate;
			}
            // Перфекционизм:
            if (data.loanPercentSum == 0 && data.paymentType == 'payment') {
                // Если планируется досрочное погашение
                var Sx = calcMonthlyPayment(data.restAmount, data.operationDate); // Планируется ежемесячный платеж такой
                var Sd = Math.floor((Sx - 1) / iPerfectSig) * iPerfectSig; // уменьшение платежа
                var Si = Math.ceil((Sx + 1) / iPerfectSig) * iPerfectSig;  // увеличение платежа
                var Spd = Math.round(calcRepayMonth(data.restAmount + data.loanMainSum, Sd, data.operationDate) * iSig) /  iSig; // Пересчитаем планируемый ежемесячный платеж
                Sd = calcMonthlyPayment(data.restAmount + data.loanMainSum - Spd, data.operationDate);
                var Spi = Math.round(calcRepayMonth(data.restAmount + data.loanMainSum, Si, data.operationDate) * iSig) /  iSig; // Пересчитаем планируемый ежемесячный платеж
                Si = calcMonthlyPayment(data.restAmount + data.loanMainSum - Spi, data.operationDate);
                //console.log(data.restAmount , data.loanMainSum, data.operationDate);
                data.loanCommonSumHint = 'Попробуйте погасить ' + NtoS(Spd, ' ') + ' р., получите платеж: ' + NtoS(Sd, ' ') + ' р. (доплатить ' + NtoS(Math.round((Spd - data.loanCommonSum) * iSig) / iSig, ' ') + ' р.)\n' + 'Или ' + NtoS(Spi, ' ') + ' р., получите платеж: ' + NtoS(Si, ' ') + ' р. (забрать ' + NtoS(Math.round((data.loanCommonSum - Spi) * iSig) / iSig, ' ') + ' р.)';
                //chart_data[data.chartIndex].perfectSum = Sd;
                data.firstRePay = Spd;
                data.secondRePay = Spi;
            }
		}
		
		if (data.chartIndex == 0) {
			$res = $('#result-body');
			$tr = $('<tr />').addClass(data.lineType).appendTo($res).addClass('paymenttype' + data.paymentType);
			$td = $('<td />').addClass('text-right').addClass('res-num').appendTo($tr);
			$td.html(data.monthNumber);
			$td = $('<td />').addClass('holiday-sign').appendTo($tr);
			// ДАТА
			$td = $('<td />').addClass('res-date').appendTo($tr);
			$td.html(DtoS(data.operationDate));
			if (data.operationDateEditable) {
				$td.data('day', DtoS(data.operationDateEditableDate, true));
				$td.on('mouseenter', function(){
					$ed = $('<span />').addClass('glyphicon glyphicon-pencil edit-oper-date')
						.attr('title', 'Редактировать дату платежа')
						.on('click', function() {
						addFactDate($(this).parent().data('day'));
					}).html('&nbsp;');
					$(this).append($ed);
				});
				$td.on('mouseleave', function(){
					$(this).find('.edit-oper-date').remove();
				});
			}
			// ПРОЦЕНТЫ
			$td = $('<td />').addClass('res-sum').appendTo($tr);
			if (data.loanPercentSum != 0) {
				$td.html(NtoS(data.loanPercentSum));
			} else {
				$td.html('-');
			}
			if (data.loanPercentSumHint != '') {
				$td.attr('title', data.loanPercentSumHint);
			}
			// ОСНОВНОЙ ДОЛГ
			$td = $('<td />').addClass('res-sum').appendTo($tr);
			if (data.loanMainSum != 0) {
				$td.html(NtoS(data.loanMainSum));
			} else {
				$td.html('-');
			}
			// СУММА ЕЖЕМЕСЯЧНОГО ПЛАТЕЖА:
			$td = $('<td />').addClass('res-sum').appendTo($tr);
			if (data.loanCommonSum != 0) {
				$td.html(NtoS(data.loanCommonSum));
			} else {
				$td.html('-');
			}
			// Редактирование суммы ежемесячного платежа:
			if (data.operationSumEditable != 0) {
				if (data.loanCommonSum != data.operationSumEditable) {
					$td.addClass('sum-edited');
				}
				$td.data('sum', data.operationSumEditable);
				$td.on('mouseenter', function(){
					$ed = $('<span />').addClass('glyphicon glyphicon-pencil edit-oper-num')
						.attr('title', 'Редактировать сумму ежемесячного платежа')
						.on('click', function() {
						addPaySum($(this).parent().data('sum'));
					}).html('');
					$(this).append($ed);
				});
				$td.on('mouseleave', function(){
					$(this).find('.edit-oper-num').remove();
				});
			}
			// Редактирование суммы досрочного платежа:
			if (data.operationPaySumEditable != 0) {
				$td.data('sum', data.loanCommonSum);
				$td.data('date', DtoS(data.operationDate, true));
                $td.data('firstRePay', (typeof data.firstRePay != "undefined" ? data.firstRePay : ""));
                $td.data('secondRePay', (typeof data.secondRePay != "undefined" ? data.secondRePay : ""));
				$td.on('mouseenter', function(){
					$ed = $('<span />').addClass('glyphicon glyphicon-pencil edit-oper-num')
						.attr('title', 'Редактировать сумму досрочного платежа')
						.on('click', function() {
							addRePaySum($(this).parent().data('sum'), $(this).parent().data('date'), $(this).parent().data('firstRePay'), $(this).parent().data('secondRePay'));
						}).html('');
					$(this).append($ed);
				});
				$td.on('mouseleave', function(){
					$(this).find('.edit-oper-num').remove();
				});
			}
			// Хинт
			if (data.loanCommonSumHint != '') {
				$td.attr('title', data.loanCommonSumHint);
			}
			// ОСТАТОК ОСНОВНОГО ДОЛГА:
			$td = $('<td />').addClass('res-sum').appendTo($tr);
			$td.html(NtoS(data.restAmount));
			// Курс валюты
			if (latestExRate != 1) {
				$td = $('<td />').addClass('res-sum').appendTo($tr);
				$td.html(NtoS(latestExRate));
			}
		}
        chart_data[data.chartIndex].lastOperationDate = data.operationDate;
	}
	
	function getFirstDate(err) {
		var D = toD($('#start-date').val());
		if (typeof err != "undefined" && err == true && D == 'Invalid Date') {
			alert('Дата оформления указана не верно');
			return false;
		}
		return D;
	}
	
	// Получаем дату платежа на указанном периоде:
	function getPayDate(nPP) {
		
		var fD = new Date(gContractDate); // дата оформления
		if (!fD) return false;
		
		var day_fD = fD.getDate(); // день даты оформления
		
		if ($('#first-payment')[0].checked) {
			var DP = toN($('#day-payment').val());
		} else {
			var DP = day_fD;
		}

		if (day_fD >= DP) {
			nPP++; // если число платежа меньше текущего, или платим в день оформления - платим в следующем месяце
		}
		
		// Добавляем указанное количество месяцев к дате оформления
		fD.setMonth(fD.getMonth() + nPP);
		
		if (fD.getDate() != day_fD) {
			// Если перескочили месяц, то вернемся на последний день месяца.
			fD.setDate(0);
		}
		
		fD.setDate(DP); // Ставим дату платежа
		
		if (fD.getDate() != DP) {
			// Если перескочили месяц, то вернемся на последний день месяца.
			fD.setDate(0);
		}

		return fD;
		
	}
	
	function getFirstPayDate(err) {
		var fD = new Date(getFirstDate(err));
		if (!fD) return false;
		if ($('#first-payment')[0].checked) {
			var DP = toN($('#day-payment').val());
		} else {
			var DP = fD.getDate();
		}
		if (fD.getDate() >= DP) {
			fD.setMonth(fD.getMonth() + 1);
		}
		fD.setDate(DP);
		return fD;
	}
    
    function checkHoliday(d, cHoliday) {
        var type = '';
        while (cHoliday.length > 0 && cHoliday[0].date < d) {
            cHoliday.splice(0, 1);
        }
        if (cHoliday.length > 0 && +d == +cHoliday[0].date) {
            type = cHoliday[0].type;
        }
        if (type == '' && d.getDay() == 0 && $('#weekend-move')[0].checked) {
            type = 'holiday';
        }
        return type;
    }
    
	// Расчет суммы ежемесячного платежа
    function calcMonthlyPayment(A, d, rn) {
        if (d == "") {
            var M = 0;
        } else {
            var M = monthDiff(gFirstPayDate, d);
        }
		var d = gCreditPercentMonth/(1-Math.pow(1+gCreditPercentMonth,-(gCreditMonthCount-M-1)));
		// Свое округление
		if (gBankName == 'raiffeisen') {
			d = Math.round(d * iSigRaif) / iSigRaif;
		}
        var nPercent = A * d;
        var nRound = 1;
		if (typeof rn == "undefined") {
			if (nRound == 1) {
				nPercent = Math.round(nPercent * iSig) / iSig;
			} else {
				nPercent = Math.floor(nPercent * iSig) / iSig;
			}
		}
        return nPercent;
    }
	
	function readGVars() {
        gCreditPercentYear = toN($('#credit-percent').val()) / 100; // процентная ставка по кредиту за год
        gCreditPercentMonth = toN($('#credit-percent').val()) / 12 / 100; // процентная ставка по кредиту за месяц
        gCreditMonthCount = toN($('#credit-month').val()); // количество месяцев кредита
        gFirstPayDate = getFirstPayDate(false); // дата первого платежа
		gContractDate = new Date(getFirstDate(false));
        gBankName = $('#bank-name').val(); // банк	
        gFirstPercent = $('#first-payment')[0].checked ? 1 : 0;
		currentDate = new Date(); // текущая дата
        currentDate.setHours(0,0,0,0);
        if (gFirstPercent == 0) {
            gCreditMonthCount++;
        }
		gLine = getUrlParameter('line');
		// settings
		if (gBankName == 'vtb24' || gBankName == 'sberbank') {
			gUseLeapYearType = 2;
        } else {
            gUseLeapYearType = 1;
        }
	}
    
    function createRepayCopy(chart_index) {
		var amAdd = new Array();
		if (chart_index == 2)
			return amAdd;
		mAdd.forEach(function(o) {
			if (chart_index == 0 || o.date <= currentDate) {
				amAdd.push({
					date: new Date(o.date), 
					sum: o.sum, 
					paym: o.paym, 
					comment: o.comment,
					type: o.type,
					deposit_percent: o.deposit_percent
					});
			}
            if (o.repeat != "") {
                // Конечная дата (либо указанная, либо конец кредита)
                if (o.endDate != 'Invalid Date') {
                    var fD = new Date(o.endDate);
                } else {
                    var fD = getFirstPayDate();
                    if (!fD) return false;
                    fD.setMonth(fD.getMonth() + toN($('#credit-month').val()));
                }
                if (o.repeat == 'month') {
                    var mnth = 1;
                } else if (o.repeat == 'year') {
                    var mnth = 12;
                }
                var cD = new Date(o.date);
                cD.setMonth(cD.getMonth() + mnth);
                while (cD <= fD) {
					if (chart_index == 0 || o.date <= currentDate) {
						amAdd.push({
							date: new Date(cD), 
							sum: o.sum, 
							paym: o.paym, 
							comment: o.comment,
                            type: o.type
							});
					}
                    cD.setMonth(cD.getMonth() + mnth);
                }
            }
        });
        amAdd.sort(compareAdd);
        return amAdd;
    }
    
	// Статистиска: Проценты, Платеж, Досрочный платеж
    function chartAddMonthly(indx, NN, Prc, Pay, Rp) {
        var N = NN - 1;
        if (typeof chart_data[indx].loanPercent[N] == 'undefined') {
            chart_data[indx].loanPercent[N] = 0;
        }
        if (typeof chart_data[indx].payment[N] == 'undefined') {
            chart_data[indx].payment[N] = 0;
        }
        if (typeof chart_data[indx].repayment[N] == 'undefined') {
            chart_data[indx].repayment[N] = 0;
        }
        chart_data[indx].loanPercent[N] += Prc;
        chart_data[indx].payment[N] += Pay;
        chart_data[indx].repayment[N] += Rp;
		
    }
    function chartCorrectMonthly(indx) {
        var max = Math.max(chart_data[indx].loanPercent.length, chart_data[indx].payment.length, chart_data[indx].repayment.length);
        for (var i = 0; i < max; i++) {
            if (typeof chart_data[indx].loanPercent[i] == 'undefined') chart_data[indx].loanPercent[i] = 0;
            if (typeof chart_data[indx].payment[i] == 'undefined') chart_data[indx].payment[i] = 0;
            if (typeof chart_data[indx].repayment[i] == 'undefined') chart_data[indx].repayment[i] = 0;
        }
    }
    
    // Смена процента в середине срока
    function changePercent(perc) {
        gCreditPercentYear = perc / 100;
        gCreditPercentMonth = perc / 100 / 12;
    }
	
	function load_excurs(d1, d2, d3) {
		$.post(PATH + 'mortgage/exrate', {d1: d1, d2: d2, d3: d3}, function(data) {
			//console.log(data);
			for (var i = 0; i < data.date.length; i++) {
				mEvent.push({date: new Date(data.date[i]), percent: data.rate[i], type: 'exrate'});
			}
			mEvent.sort(compareAdd);
			eventsList();
		}, 'json');
	}
	
	// ОСНОВНОЕ ВЫПОЛНЕНИЕ
	function calc(chart_index) {
		if (chart_index == 0) {
			latestExRate = 1;
			$('#result-body').html('');
			$('#chart1').html('');
			$('#chart1b1').html('');
			$('#chart1b2').html('');
			$('#chart2g1').html('');
			$('#chart2g2').html('');
			$('#chart3').html('');
			$('#chart4').html('');
			$('#tch1').addClass('active');
			$('#tch2').addClass('active');
			$('#tch3').addClass('active');
			$('#tch4').addClass('active');			
		}
		chart_data[chart_index] = {
            restAmount: new Array(), // Остаток суммы основного долга
            loanPercent: new Array(), // Погашено процентов
            payment: new Array(), // В счет основного долга
            repayment: new Array(), // Досрочное погашение
			currentMonth: 0,
			currentOperationDate: '',
        };

		var arestAmount = toN($('#credit-sum').val());
		var amAdd = createRepayCopy(chart_index);
		var nPrc = toN($('#month-payment').val()); // Ежемесячный платеж
		var nPrcEdited = null; // Если исправили сумму ежемесячного платежа
		var aLoanCommonSum12 = nPrc; // Сумма ежемесячного платежа (не округленная) для подсказки

        // События (смена процента, страховка, курс валюты)
		var amEvent = mEvent.slice(0);
        amEvent.sort(compareAdd);        
        
		readGVars();
		month_payment_calc();
        
        if (isNaN(gCreditPercentMonth) || isNaN(gCreditMonthCount) || (gFirstPayDate == 'Invalid Date')) {
            return false;
        }
		
		// Первая строка - дата открытия
		var D = getFirstDate();
		addLine({chartIndex: chart_index, lineType: 'first', operationDate: D, restAmount: arestAmount});
		chart_data[chart_index].restAmount.push(arestAmount);
		
		if (!gFirstPayDate) return false;
		var lD = new Date(gFirstPayDate); // Последняя добавленная (в контексте) дата платежа

        cHoliday = new Array();
        mHoliday.forEach(function(o){
            cHoliday.push({date: o.date, type: o.type});
        });
        var style = '';
        var holiday_sign = 0; 
		
		if (gBankName != 'vtb24') {
			// Первый платеж проценты не переносится на след.рабочий день, если это ВТБ
			while (checkHoliday(lD, cHoliday) == 'holiday') {
				lD.setDate(lD.getDate() + 1);
				holiday_sign = 1;
			}
			if (holiday_sign == 1) {
				style += ' holiday';
			}
		}
		
        var nPP = 0; // месяц по порядку

        var aloanCommonSum = 0; // Последняя заплаченная сумма
        
        if (gFirstPercent == 1) {
            // Вторая строка - проценты
            var operationDateEditableDate;
            var operationSumEditable = 0;
            S = calcPercent(arestAmount, lD, D);
            var S2 = S;
            S = Math.round(S * iSig)/iSig;

            aloanCommonSum = S;
            
            nPP++;
            addLine({
                chartIndex: chart_index, 
                lineType: 'perc' + style, 
                monthNumber: nPP, 
                operationDate: lD, 
                //operationDateEditable: true,
                restAmount: arestAmount, 
                loanPercentSum: aloanCommonSum, 
                loanCommonSum: aloanCommonSum
            });
            if (currentDate > lD) {
                chart_data[chart_index].currentMonth = nPP;
            }
            chartAddMonthly(chart_index, nPP, aloanCommonSum, 0, 0);
        } else {
            lD = getFirstDate();
        }
        var bOnlyPercent = false; // В следующем месяце только проценты
		var amFactPayment = mFactPayment.slice(0);
		var amFactSum = mFactSum.slice(0);

		while (arestAmount > 0.0001) {
			// Дата фактического платежа:
			/*
			tnD = toD(gFirstPayDate);
			tnD.setMonth(tnD.getMonth() + nPP);
			*/
			tnD = getPayDate(nPP);

			operationDateEditableDate = new Date(tnD);
			var factChangedDate = 0;
            var factChangedDate2tnD;
			if ((amFactPayment.length > 0) && (DtoS(operationDateEditableDate) == DtoS(amFactPayment[0].oldDate))) {
                if (gFirstPercent == 1) {
                    // Если это самый первый платеж (не считая первого проценты), то суммы остаются прежними
                    factChangedDate = 2;
                    factChangedDate2tnD = amFactPayment[0].newDate;
                } else {
                    factChangedDate = 1;
                    tnD = amFactPayment[0].newDate;
                }
                amFactPayment.splice(0, 1);
			}			
			
			var oldPrc = 0;
            
			if ((lD.getFullYear() != tnD.getFullYear()) && (IsLeapYear(lD.getFullYear()) || IsLeapYear(tnD.getFullYear()))) {
				var newYear = new Date(tnD.getFullYear(), 0, 1);
				if (gUseLeapYearType == 2) {
					newYear.setDate(0); // минус один день
				}
				amAdd.push({date: newYear, sum: 0, paym: 0, type: 'payment', leap: 1});
				amAdd.sort(compareAdd);
			}
            
            while ((amEvent.length > 0) && (tnD > amEvent[0].date)) {
                if (amEvent[0].type == 'percent') {
                    changePercent(amEvent[0].percent);
                    amAdd.push({date: new Date(amEvent[0].date), sum: 0, paym: 0, type: 'payment', percentChanged: 1});
                    amAdd.sort(compareAdd);
                    addCommentLine({
                        comment: DtoS(amEvent[0].date) + '. Изменился процент по кредиту: ' + amEvent[0].percent + '%', 
                        chartIndex : chart_index
                        });
                } else if (amEvent[0].type == 'insurance') {
                    amAdd.push({date: new Date(amEvent[0].date), sum: 0, paym: 0, type: 'insurance', percentChanged: 1, insurancePercent: amEvent[0].percent});
                    amAdd.sort(compareAdd);
                } else if (amEvent[0].type == 'exrate') {
					amAdd.push({date: new Date(amEvent[0].date), sum: 0, paym: 0, type: 'exrate', percentChanged: 1, exRate: amEvent[0].percent});
                    amAdd.sort(compareAdd);
				}
                amEvent.splice(0, 1);
            }
			
			var nPrcPaym = nPrc; // Сумма ежемесячного платежа (при расчете досрочного погашения, которое зависит от нее)
			if (nPrcEdited != null) nPrcPaym = nPrcEdited;
			nPrcEdited = null;
			
			var rePayedSumm = 0; // Сумма досрочного погашения за период
			var rePayedPrcWoSumm = 0; // Сумма досрочных процентов без досрочных погашений (для сбербанка)
			
			while ((amAdd.length > 0) && (tnD > amAdd[0].date)) {
                if (amAdd[0].type == 'insurance') {
                    addCommentLine({
                        comment: DtoS(amAdd[0].date) + '. Страховка (' + NtoS(amAdd[0].insurancePercent) + '%): ' + NtoS(Math.round(arestAmount * amAdd[0].insurancePercent / 100 * iSig) / iSig) + ' руб.', 
                        chartIndex : chart_index
                    });
                } else if (amAdd[0].type == 'exrate') {
					latestExRate = amAdd[0].exRate;
					// Добавил курс в график
					/*
					if (gFirstPayDate <= amAdd[0].date) {
						addCommentLine({
							comment: DtoS(amAdd[0].date) + '. Курс валюты: ' + latestExRate + ' руб. (платеж ' + NtoS(Math.round(nPrcPaym * latestExRate * 100) / 100) + ' руб., ОД ' + NtoS(Math.round(arestAmount * latestExRate * 100) / 100) + ' руб.)', 
							chartIndex : chart_index
							});
					}
					*/
				} else if (amAdd[0].deposit_percent > 0) {
					addCommentLine({
                        comment: DtoS(amAdd[0].date) + '. На депозит (' + NtoS(amAdd[0].deposit_percent) + '%): ' + NtoS(Math.round(amAdd[0].sum * iSig) / iSig) + ' руб.', 
                        chartIndex : chart_index
                    });
				} else {
					// Сумма досрочного погашения:
                    S = Math.round(amAdd[0].sum / latestExRate * iSig) / iSig;
                    // Если галка "Вычесть из суммы":
                    if (amAdd[0].paym == 1) {
                        if (gBankName != 'sberbank') {
                            S = Math.round((S - nPrcPaym) * iSig) / iSig;
                        } else {
                            S = Math.round((S - aloanCommonSum) * iSig) / iSig;
                        }
                    }
                    
                    // Перенос выходного дня: (кроме добавленных вручную)
                    holiday_sign = 0;
                    if (amAdd[0].type != 'factChanged') {
                        while (checkHoliday(amAdd[0].date, cHoliday) == 'holiday') {
                            // Если выходной
                            amAdd[0].date.setDate(amAdd[0].date.getDate() + 1);
                            holiday_sign = 1;
                        }
                    }
					// Процент с учетом досрочных погашений:
                    oldPrc1 = calcPercent(arestAmount, amAdd[0].date, lD);
                    oldPrc1 = Math.round(oldPrc1 * iSig) / iSig;
					// Процент без учета досрочных погашений (для сбера):
					oldPrc2 = calcPercent(arestAmount + rePayedSumm, amAdd[0].date, lD);
                    oldPrc2 = Math.round(oldPrc2 * iSig) / iSig;
					rePayedPrcWoSumm += oldPrc2;
                    
                    oldPrc += oldPrc1;

                    var addPrc = 0; // Проценты набежавшие до даты погашения
                    if ((gBankName == 'sberbank') && (amAdd[0].type == 'payment') && (typeof amAdd[0].leap == "undefined")) {
						// В Сбербанке от суммы досрочного погашения отнимаются проценты:
                        S = Math.round((S - oldPrc1) * iSig) / iSig;
                        if (S < 0) {
                            addPrc = oldPrc1 + S;
                            S = 0;
                        } else {
                            addPrc = oldPrc1;
                        }
                        oldPrc -= addPrc;
                        bOnlyPercent = true;
                    }
                    loanPercentSumHint = NtoS(oldPrc1, ' ');
                    
					// Если досрочка больше остатка:
                    if (arestAmount < S) {
                        var tPrc = oldPrc;
                        if (arestAmount + tPrc < S) {
                            addPrc = Math.round(tPrc * iSig) / iSig;
                            oldPrc = 0;
                        } else {
                            addPrc = Math.round((S - arestAmount) * iSig) / iSig;
                            oldPrc = addPrc - tPrc;
                        }
                        loanPercentSumHint = '';
                        S = arestAmount;
                    }
					// Остаток основного долга после досрочного погашения:
                    arestAmount = Math.round((arestAmount - S) * iSig) / iSig;
                    
                    if (typeof amAdd[0].leap == "undefined") {
                        if (typeof amAdd[0].percentChanged == "undefined") {
                            style = 'inadd';
                            
                            if (amAdd[0].type == 'factChanged') {
                                style = 'fcadd';
                                CS = amAdd[0].loanCommonSum;
                                CS2 = amAdd[0].loanPercentSum;
                            } else {
                                CS = S + addPrc;
                                CS2 = addPrc;
                            }
                            
                            if (amAdd[0].type == 'factChangedDate2') {
                                style = 'fcdate';
                            }
                            
                            if (holiday_sign == 1) style += ' holiday';
                            if (amAdd[0].date > currentDate) style += ' planned';

                            CS = Math.round(CS * iSig) / iSig;
                            addLine({
                                chartIndex: chart_index, 
                                lineType: style, 
                                operationDate: amAdd[0].date, 
                                loanPercentSum: CS2,
                                restAmount: arestAmount, 
                                loanPercentSumHint: loanPercentSumHint, 
                                loanMainSum: S, 
                                loanCommonSum: CS, 
                                loanCommonSumHint: amAdd[0].comment,
                                paymentType: amAdd[0].type,
								operationPaySumEditable: 1
                            });
                            if (currentDate > amAdd[0].date) {
                                chart_data[chart_index].currentMonth = nPP;
                            }
                            if (amAdd[0].type == 'factChanged') {
                                chartAddMonthly(chart_index, nPP, CS2, S, 0);
                            } else {
                                chartAddMonthly(chart_index, nPP, CS2, 0, S);
                            }
                        }
                        // Уменьшение ежемесячного платежа
                        if (amAdd[0].type == 'payment') {
                            // Поиск нового ежемесячного платежа:
                            var thed = new Date(amAdd[0].date);
                            if (gFirstPercent == 0) {
                                thed.setMonth(thed.getMonth() + 1);
                            }
							nPrcEdited = null;

                            nPrc = calcMonthlyPayment(arestAmount, thed);
                            aLoanCommonSum12 = calcMonthlyPayment(arestAmount, thed, 1); // Не округленная сумма для подсказки

                        } 
                        // Уменьшение срока
                        else if (amAdd[0].type == 'term') {
                            var mnth = getBaseLog(1+gCreditPercentMonth, nPrc / (nPrc - gCreditPercentMonth * arestAmount));
                            //console.log(arestAmount, nPrc, gCreditPercentMonth, nPP + mnth);
                            var newgCreditMonthCount = Math.ceil(nPP + mnth);
                            if (newgCreditMonthCount < gCreditMonthCount) {
                                addCommentLine({
                                    comment: DtoS(amAdd[0].date) + '. Изменился срок кредита с ' + gCreditMonthCount + ' до ' + newgCreditMonthCount + ' мес.', 
                                    chartIndex : chart_index
                                });
                                gCreditMonthCount = newgCreditMonthCount;
                            }
                        }
					}
                    var lD = new Date(amAdd[0].date);
					rePayedSumm += S;
				}
				amAdd.splice(0, 1);
			}
			
            holiday_sign = 0;
			// Перенос на следующий рабочий день для всех, кроме тех, у кого вручную изменили дату
			if (factChangedDate == 0) {
				while (checkHoliday(tnD, cHoliday) == 'holiday') {
					// Если выпал выходной
					tnD.setDate(tnD.getDate() + 1);
					holiday_sign = 1;
				}
			}
			// Процент за текущий период:
			var nCP2 = calcPercent(arestAmount, tnD, lD);
			// Процент за текущий период без учета досрочных платежей (для сбера):
			var nCP3 = calcPercent(arestAmount + rePayedSumm, tnD, lD);
			rePayedPrcWoSumm += nCP3;
			rePayedPrcWoSumm = Math.round(rePayedPrcWoSumm * iSig) / iSig;

			// Суммарный процент:
            nCP = oldPrc + nCP2;
            nCP = Math.round(nCP * iSig) / iSig;
			
			var aloanPercentSumHint = '';
			if (oldPrc != 0) {
                aloanPercentSumHint = '';
                if (oldPrc != 0)
                    aloanPercentSumHint += NtoS(oldPrc, ' ') + ' + ';
                aloanPercentSumHint += NtoS(Math.round(nCP2 * iSig) / iSig, ' ');
			}
            
			nPrcFinal = nPrc;
			if (nCP > nPrcFinal) {
				// Ежемесячный платеж оказался меньше, чем процент за период при досрочном погашении:
                nPrcFinal = nCP;
				operationSumEditable = nPrcFinal;
			} else {
				operationSumEditable = nPrc;
            }
			
			amFactSum.forEach(function(a, b) {
				// Если ежемесячный платеж был исправлен руками:
				if (Math.abs(a.oldSum - nPrcFinal) < 0.0001) {
					nPrcEdited = a.newSum;
					amFactSum.splice(b, 1);
				}
			});

			if (nPrcEdited != null) {
				nPrcFinal = nPrcEdited;
			}
			
			aloanCommonSum = nPrcFinal; // сумма ежемесячного платежа
			aloanPercentSum = nCP; // из нее процентов
			aloanMainSum = nPrcFinal - nCP; // из нее основного долга - погашение основного долга (ежемесячный платеж - проценты)
            
            // Сбербанк
            if (bOnlyPercent) {
				aloanMainSum = nPrcPaym - rePayedPrcWoSumm - rePayedSumm;
				if (aloanMainSum < 0) aloanMainSum = 0;	// Если ЧДП превысило ежемесячный платеж:
				aloanCommonSum = aloanMainSum + nCP;
            }
			
			var style = 'osn';
            if (holiday_sign == 1) 
                style += ' holiday';
			
			if (gLine == nPP + 1) {
				style += ' highlight';
			}
			
			if (factChangedDate != 0) {
				style += ' holiday2';
			}
			
			if (arestAmount < aloanMainSum) {
				aloanMainSum = arestAmount;
				aloanCommonSum = aloanMainSum + aloanPercentSum;
			}

			aloanMainSum = Math.round(aloanMainSum * iSig) / iSig; // погашение основного долга
            
            if (factChangedDate == 2) {
                amAdd.push({date: new Date(factChangedDate2tnD), sum: aloanMainSum, paym: 0, type: 'factChangedDate2'});
                amAdd.sort(compareAdd);
                aloanCommonSum = aloanPercentSum;
                aLoanCommonSum12 = 'Погашение основного долга (' + NtoS(aloanMainSum, ' ') + ') через ' + daysDiff(factChangedDate2tnD, tnD) + ' д.';
                aloanMainSum = 0;
                operationSumEditable = 0;
                // arestAmount остается прежним
            } else {
                arestAmount = Math.round((arestAmount - aloanMainSum) * iSig) / iSig; // остаток основного долга                
            }
			
			if (bOnlyPercent) {
				// Поиск нового ежемесячного платежа:
				var thed = new Date(tnD);
				if (gFirstPercent == 0) {
					thed.setMonth(thed.getMonth() + 1);
				}
				nPrc = calcMonthlyPayment(arestAmount, thed);
				aLoanCommonSum12 = calcMonthlyPayment(arestAmount, tnD, 1); // Не округленная сумма для подсказки
				bOnlyPercent = false;
			}

            aloanCommonSum = Math.round(aloanCommonSum * iSig) / iSig;

            nPP++;
			addLine({
				chartIndex: chart_index, 
				lineType: style, // Стиль строки
				monthNumber: nPP, // Номер месяца 
				operationDate: tnD, // Дата операции
				operationDateEditable: true,
				operationDateEditableDate: operationDateEditableDate,
				operationSumEditable: operationSumEditable, // редактируемая дата ежемесячного платежа
				restAmount: arestAmount, // Остаток основного долга
				loanPercentSum: aloanPercentSum, // Погашение процентов
				loanPercentSumHint: aloanPercentSumHint, // Подсказка к полю "Погашение процентов" (Проценты до погашения основного долга)
				loanMainSum: aloanMainSum, // Погашение основного долга
				loanCommonSum: aloanCommonSum, // Внесенная сумма (ежемесячный платеж или досрочное погашение)
				loanCommonSumHint: aLoanCommonSum12 // Подсказка к полю "Внесенная сумма / Ежемесячный платеж"
			});
			chart_data[chart_index].restAmount.push(arestAmount);
            if (currentDate > tnD) {
				chart_data[chart_index].currentMonth = nPP;
            }
			chartAddMonthly(chart_index, nPP, aloanPercentSum, aloanMainSum, 0);
			var lD = new Date(tnD);
		}
		
		location.hash = '#a';
		
        chartCorrectMonthly(chart_index);
		
		if (chart_index == 0) {
			$('.stat-avg').html(DtoS(chart_data[chart_index].currentOperationDate));
			$('#stat-date').html(DtoS(chart_data[chart_index].lastOperationDate));
		}
		
		if (chart_index == 2) {
			drawchart(toN($('#credit-sum').val()));
		}
	}
	
	function compareAdd(a,b) {
	  if (a.date < b.date)
		 return -1;
	  if (a.date > b.date)
		return 1;
	  return 0;
	}
	
	function compareFactPayment(a,b) {
	  if (a.oldDate < b.oldDate)
		 return -1;
	  if (a.oldDate > b.oldDate)
		return 1;
	  return 0;
	}
	
	function removeAdd(a) {
		mAdd.splice(a, 1);
		addList();
		return false;
	}
    
    function clearinpay() {
        mAdd = [];
        addList();
    }
    
    function sortRepayments() {
        mAdd.sort(compareAdd);
        addList();
		return false;
    }
	
	function addList() {
		$a = $('#inadd-body');
		$a.html('');
		var i = 0;
		mAdd.forEach(function(o) {
			$tr = $('<tr />').appendTo($a);
			if (o.comment != '') {
				$tr.attr('title', o.comment);
			}
            $td = $('<td />').appendTo($tr).css('text-align', 'center');
            $span = $('<span />').css('cursor', 'pointer').data('id', i).data('otype', o.type).appendTo($td);
            if (o.type == 'term') {
                $span.addClass('glyphicon glyphicon-time').attr('title', 'Уменьшение срока');
            } else if (o.type == 'payment') {
                $span.addClass('glyphicon glyphicon-usd').attr('title', 'Уменьшение ежемесячного платежа');
            } else {
                $span.html(o.deposit_percent + '%');
            }
			if (o.type == 'term' || o.type == 'payment') {
				$span.on('click', function() {
					cid = $(this).data('id');
					if ($(this).data('otype') == 'term') mAdd[cid].type = 'payment';
					if ($(this).data('otype') == 'payment') mAdd[cid].type = 'term';
					addList();
				});
			}
			$td = $('<td />').appendTo($tr);
			$input = $('<input type="date"/>').addClass('form-control').data('id', i).data('c', 'date').addClass('saveadd saveaddd').appendTo($td);
			$input.val(DtoS(o.date, true));
			$td = $('<td />').appendTo($tr);
			$input = $('<input />').addClass('form-control').data('id', i).data('c', 'sum').addClass('saveadd').appendTo($td);
			$input.val(o.sum);
			$td = $('<td />').appendTo($tr);
			if (o.paym == 1) {
				spay = 'Да';
			} else {
				spay = '-';
			}
			$td.html(spay);
			$td = $('<td />').appendTo($tr);
			$td.html('<a title="Удалить" href="javascript:;" onclick="return removeAdd('+i+')"><span class="glyphicon glyphicon-remove"></span></a>');
            if (o.repeat != '') {
                $tr = $('<tr />').appendTo($a);
                $td = $('<td />').attr('colspan', 4).appendTo($tr);
                if (o.repeat == 'month') {
                    var srepeat = 'каждый месяц';
                } else if (o.repeat == 'year') {
                    var srepeat = 'каждый год';
                }
                if (o.endDate != 'Invalid Date') {
                    srepeat += ' до ' + DtoS(o.endDate);
                }
                $td.html('Повторять: ' + srepeat);
            }
			i++;
		});
		$('.saveadd').on('focusout', function(o) {
			saveAdd($(this).data('id'), $(this).data('c'), $(this).val());
		});
		$('#addlisthead').html('(' + i + ' шт.)');
	}
	
	function saveAdd(j,t,v) {
		if (t == 'sum') {
			mAdd[j].sum = toN(v);
		}
		if (t == 'date') {
			var d = toD(v);
			if ('Invalid Date' == d) {
				alert('Дата досрочного погашения должна быть введена в локальном формате, либо в формате гггг-мм-дд');
			} else {
				mAdd[j].date = toD(v);
			}
		}
		addList();
	}
	
	// Добавить досрочный платеж:
	function addRepaySumm(date, sum, type) {
		if ('Invalid Date' == toD(date)) {
			alert('Дата должна быть введена в локальном формате, либо в формате гггг-мм-дд');
		} else if (0 == toN(sum)) {
			alert('Сумма введена не верно.');
		} else {
			mAdd.push({
				date: toD(date), 
				sum: toN(sum), 
				paym: 0, 
				comment: '',
				repeat: '',
				endDate: toD(""),
				type: type,
				deposit_percent: 0
			});
			sortRepayments();
		}
	}
	
	// Нажали кнопку добавления досрочного платежа
	function inadd() {
		if ('Invalid Date' == toD($('#add-date').val())) {
			alert('Дата должна быть введена в локальном формате, либо в формате гггг-мм-дд');
		} else if (0 == toN($('#add-sum').val())) {
			alert('Сумма введена не верно.');
		} else {
			if ($('#add-paym')[0].checked && $('#add-period').val() != '') {
				paymsign = 1;
			} else {
				paymsign = 0
			}
            if ($('#income-type').val().match('^dep.*')) {
                var prc = toN($('#income-type').find('option:selected').data('percent'));
            } else {
                var prc = 0;
            }
			mAdd.push({
                date: toD($('#add-date').val()), 
                sum: toN($('#add-sum').val()), 
                paym: paymsign, 
                comment: $('#add-comment').val(),
                repeat: $('#add-period').val(),
                endDate: toD($('#add-monthly-date').val()),
                type: $('#income-type').val(),
                deposit_percent: prc
                });
			addList();
		}
	}
    
    function addHolidayList() {
		mHoliday.sort(compareAdd);
		$a = $('#holiday-body');
		$a.html('');
		var i = 0;
		mHoliday.forEach(function(o) {
            if (o.type == 'holiday') {
                $sclass = 'alert-danger';
                $sclass2 = 'выходной';
            } else if (o.type == 'work') {
                $sclass = 'alert-success';
                $sclass2 = 'рабочий';
            } else {
                $sclass = 'btn-default';
                $sclass2 = '';
            }
            $btn = $('<button />').addClass('btn').data('id', i).addClass($sclass).attr('title', 'Удалить '+$sclass2+' день').on('click', function() {
                removeHoliday($(this).data('id'));
            }).appendTo($a);
            $btn.html(DtoS(o.date));
			i++;
		});
		$('#holidaylisthead').html('(' + i + ' шт.)'); 
    }
    
    function removeHoliday(a) {
        mHoliday.splice(a, 1);
		addHolidayList();
    }
    
    function addholiday() {
        $date = $('#holiday-date').val();
        $type = $('#holiday-type').val();
        
        if ('Invalid Date' == toD($date)) {
			alert('Дата праздничных и выходных дней должна быть введена в локальном формате, либо в формате гггг-мм-дд');
		} else if (($type != 'holiday') && ($type != 'work')) {
			alert('Тип указан не верно.');
		} else {
			mHoliday.push({date: toD($date), type: $type});
			addHolidayList();
		}   
        return false;        
    }
    
    function add_period_changed() {
        v = $('#add-period').val();
        if (v == '') {
            $('.add-period-group').hide();
        } else {
            $('.add-period-group').show();
        }
    }
    
    function event_type_changed() {
        v = $('#event-type').val();
        $('.event-type').hide();
        $('#event-type-' + v).show();
    }
    
    function addevent(e) {
        if (e == 'percent') {
            var d = toD($('#event-date').val());
            var p = toN($('#event-percent').val());
			if (d == 'Invalid Date') {
				alert('Дата события должна быть введена в локальном формате, либо в формате гггг-мм-дд');
			} else {
				mEvent.push({date: d, percent: p, type: e});
			}
        }
        if (e == 'insurance') {
            var d = toD($('#event-date2').val());
            var p = toN($('#event-percent2').val());
			if (d == 'Invalid Date') {
				alert('Дата события должна быть введена в локальном формате, либо в формате гггг-мм-дд');
			} else {
				mEvent.push({date: d, percent: p, type: e});
			}
        }
		if (e == 'exrate') {
            var d = toD($('#event-date3').val());
            var p = toN($('#event-percent3').val());
			if (d == 'Invalid Date') {
				alert('Дата события должна быть введена в локальном формате, либо в формате гггг-мм-дд');
			} else {
				mEvent.push({date: d, percent: p, type: e});
			}
        }
        mEvent.sort(compareAdd);
        eventsList();
        return false;
    }
    
    function deleteEvent(i) {
        mEvent.splice(i, 1);
        eventsList();
		return false;
    }
    
    function eventsList() {
        $('#events-body').html('');
        $table = $('<table />').addClass('table').appendTo($('#events-body'));
        var i = 0;
        mEvent.forEach(function(o) {
            $tr = $('<tr />').appendTo($table);
            $td = $('<td />').appendTo($tr).html(DtoS(o.date));
            $td = $('<td />').appendTo($tr);
            if (o.type == 'percent') {
                $td.html('Изменение процента: ' + o.percent + '%');
            } else if (o.type == 'insurance') {
                $td.html('Страховка от ОД: ' + o.percent + '%');
            } else if (o.type == 'exrate') {
                $td.html('Курс валюты: ' + o.percent + ' руб.');
            }
            $td = $('<td />').appendTo($tr);
            $td.html('<a title="Удалить" href="javascript:;" onclick="return deleteEvent('+i+')"><span class="glyphicon glyphicon-remove"></span></a>');
            i++;
        });
        $('#eventslisthead').html(' (' + mEvent.length + ' шт.)');
    }
	
	// Список опубликованных
	function mortgage_list() {
		$.post(PATH + 'mortgage/list', {}, function(d) {
			$ul = $('#mortgage-list-ul');
			$ul.html('');
			d.forEach(function(a) {
				if (typeof a.info.schedule_description == 'undefined') {
					a.info.schedule_description = '';
				} else {
					a.info.schedule_description = ' <b>' + a.info.schedule_description + '</b>';
				}
				$li = $('<li />').appendTo($ul).html('<a href="'+PATH+'mortgage/?public_id='+a.id+'">График №'+a.id+a.info.schedule_description+' (Сумма: '+a.info.credit_sum+' р., ставка: '+a.info.credit_percent+'%, срок: '+a.info.credit_month+' мес.)</a> <a class="delete-public-list" title="Удалить график" href="javascript:;" onclick="return delete_public_list('+a.id+')"><span class="glyphicon glyphicon-remove"></span></a>');
			});
			if (d.length == 0) {
				$li = $('<li />').appendTo($ul).html('Нет ни одного опубликованного графика');
			}
			$('#mortgage-list').show();
        }, 'json');	
		return false;
	}
	
	function delete_public_list(id) {
		$.post(PATH + 'mortgage/unpublic', {public_id: id}, function(d) {
			addmessage(d.msg);
			mortgage_list();
        }, 'json');	
		return false;
	}
   	
	$(function(){
		$('#credit-percent').on('change', function() {
			credit_percent_changed();
		});
		$('#credit-sum').on('change', function() {
			credit_sum_changed();
		});
		$('#credit-month').on('change', function() {
			credit_month_changed();
		});
		$('#btnshow').on('click', function() {
			var D = toD($('#start-date').val());
			if ('Invalid Date' == D) {
				alert('Дата оформления кредита должна быть введена в локальном формате, либо в формате гггг-мм-дд');
			} else {
				calc(0);
				calc(1);
				calc(2);
			}
			return false;
		});
		$('#btnadd').on('click', function() {
			inadd();
			return false;
		});
        $('#btnclear').on('click', function() {
			clearinpay();
			return false;
		});
        $('#add-period').on('change', function() {
            add_period_changed();
        });
        $('#event-type').on('change', function() {
            event_type_changed();
        });
		$('#first-payment').on('change', function() {
			first_payment_changed();
		});
		$('#bank-name').on('change', function() {
			if ($(this).val() == 'sberbank' && $('#weekend-move')[0].checked) {
				alert('Не забудьте снять галочку "Переносить воскресенье на понедельник", если выбираете Сбербанк');
			}
		});
		loaddata();
        add_period_changed();
        event_type_changed();
	});
	
	
	function first_payment_changed() {
		if ($('#first-payment')[0].checked) {
			$('#day-payment-div').show('fast');
		} else {
			$('#day-payment-div').hide('fast');
		}
		month_payment_calc();
	}
	
	function savedata() {
		data = {};
		data.add = new Array();
        data.holiday = new Array();
        data.percentChange = new Array();
		data.factPayment = new Array();
		data.factSum = new Array();
        data.deposits = new Array();
		var i = 0;
		mAdd.forEach(function(o) {
			data.add.push({
                date: DtoS(o.date, true), 
                sum: o.sum, 
                paym: o.paym, 
                comment: o.comment,
                repeat: o.repeat,
                endDate: DtoS(o.endDate, true),
                type: o.type,
                deposit_percent: o.deposit_percent
            });
		});
        mHoliday.forEach(function(o){
            data.holiday.push({
				date: DtoS(o.date, true), 
				type: o.type
			});
        });
        mEvent.forEach(function(o){
            data.percentChange.push({
				date: DtoS(o.date, true), 
				percent: o.percent,
                type: o.type
			});            
        });
        mDeposits.forEach(function(o){
            data.deposits.push({
				name: o.name, 
				percent: o.percent
			});            
        });
		mFactPayment.forEach(function(o){
            data.factPayment.push({
				oldDate: DtoS(o.oldDate, true), 
				newDate: DtoS(o.newDate, true)
			});            
        });
		mFactSum.forEach(function(o){
            data.factSum.push({
				oldSum: o.oldSum,
				newSum: o.newSum
			});            
        });
		data.credit_sum = toN($('#credit-sum').val());
		data.credit_month = toN($('#credit-month').val());
		data.credit_percent = toN($('#credit-percent').val());
		data.day_payment = toN($('#day-payment').val());
		data.start_date = $('#start-date').val();
        data.weekend_move = $('#weekend-move')[0].checked;
        data.first_payment = $('#first-payment')[0].checked;
        data.bank_name = $('#bank-name').val();
		data.schedule_description = $('#schedule-description').val();
		$.post(PATH + 'mortgage/save', {public_id: public_id, data: data}, function(d) {
			addmessage(d.msg);
		}, 'json');
		return false;
	}
	
	function loaddata() {
		$.post(PATH + 'mortgage/load/', {public_id: public_id}, function(d) {
			if (d) {
				mAdd = new Array();
                mHoliday = new Array();
                mEvent = new Array();
				mFactPayment = new Array();
                mDeposits = new Array();
				if (typeof d.add == 'undefined') 
                    d.add = new Array();
                if (typeof d.holiday == 'undefined')
                    d.holiday = new Array();
                if (typeof d.percentChange == 'undefined')
                    d.percentChange = new Array();
				if (typeof d.factPayment == 'undefined')
					d.factPayment = new Array();
				if (typeof d.factSum == 'undefined')
					d.factSum = new Array();
                if (typeof d.weekend_move == 'undefined')
                    d.weekend_move = "true";
                if (typeof d.first_payment == 'undefined')
                    d.first_payment = "true";
				if (typeof d.schedule_description == 'undefined')
					d.schedule_description = "";
                if (typeof d.deposits == 'undefined') 
                    d.deposits = new Array();
				d.add.forEach(function(o) {
					mAdd.push({
                        date: toD(o.date), 
                        sum: toN(o.sum), 
                        paym: toN(o.paym), 
                        comment: o.comment,
                        repeat: (typeof o.repeat == 'undefined') ? '' : o.repeat,
                        endDate: toD(o.endDate),
                        type: (typeof o.type == 'undefined') ? 'payment' : o.type,
                        deposit_percent : (typeof o.deposit_percent == 'undefined') ? 0 : toN(o.deposit_percent)
                    });
				});
                d.holiday.forEach(function(o){
                    mHoliday.push({date: toD(o.date), type: o.type});
                });
                d.percentChange.forEach(function(o){
                    mEvent.push({date: toD(o.date), percent: o.percent, type: (typeof o.type == 'undefined') ? 'percent' : o.type});
                });
                d.deposits.forEach(function(o){
                    mDeposits.push({name: o.name, percent: o.percent});
                });
				d.factPayment.forEach(function(o){
					mFactPayment.push({oldDate: toD(o.oldDate), newDate: toD(o.newDate)});
				});
				d.factSum.forEach(function(o){
					mFactSum.push({oldSum: toN(o.oldSum), newSum: toN(o.newSum)});
				});
				$('#credit-sum').val(toN(d.credit_sum));
				$('#credit-month').val(toN(d.credit_month));
				$('#credit-percent').val(toN(d.credit_percent));
				$('#day-payment').val(toN(d.day_payment));
				$('#start-date').val(d.start_date);
                $('#weekend-move').prop('checked', d.weekend_move == "true");
                $('#first-payment').prop('checked', d.first_payment == "true");
                $('#bank-name').val(d.bank_name);
				$('#schedule-description').val(d.schedule_description);
				if (d.schedule_description != '')
					$(document).prop('title', d.schedule_description + ' - Кредитный (ипотечный) калькулятор с досрочными погашениями');
				credit_percent_changed();
				first_payment_changed();
				addList();
                addHolidayList();
                eventsList();
                depositsList();
				calc(0);
				calc(1);
				calc(2);
			}
		}, 'json');
	}
	
    // Опубликовать график в общий доступ
    function public_data(){
        $.post(PATH + 'mortgage/public', {}, function(d) {
            if (d.public_id > 0) {
                document.location.href = PATH + 'mortgage/?public_id=' + d.public_id;
            } else {
                addmessage('Ссылку создать не удалось');
            }
        }, 'json');
		return false;
    }
	
	function unpublic_data() {
		if (confirm('Восстановить график будет нельзя. Продолжить?')) {
			$.post(PATH + 'mortgage/unpublic', {public_id: public_id}, function(d) {
				addmessage(d.msg);
			}, 'json');
		}
		return false;
	}
	
	// Функция для подбора досрочного платежа по ежемесячному
	function calcRepayMonth(nRest, nSumm, dOper) {
		var d = new Date(dOper);
		var M = monthDiff(gFirstPayDate, d);
		var A = nSumm / (gCreditPercentMonth/(1-Math.pow(1+gCreditPercentMonth,-(gCreditMonthCount-M-1))));
		return nRest - A;
	}
	
	function create_new() {
		$.post(PATH + 'mortgage/create_new', {}, function(d) {
            if (d.public_id > 0) {
                document.location.href = PATH + 'mortgage/?public_id=' + d.public_id;
            } else {
                addmessage('Ссылку создать не удалось');
            }
        }, 'json');	
		return false;
	}
	
	var toExcel = (function() {
	  var uri = 'data:application/vnd.ms-excel;charset=UTF-8;base64,'
		, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
		, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
		, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
	  return function() {
		var table = $('#result-body').clone();
		$(table).find('.holiday').find('.holiday-sign').html('*').css('color', 'red');
		$(table).find('.res-sum').each(function(i, v) {
			$(v).html($(v).html().replace(/&nbsp;/g, '')).removeAttr('title'); //.css('width', '80px');
		});
		$(table).find('.inadd').css('backgroundColor', '#dff0d8');
		$colgroup = '<colgroup width="40px" align="right"></colgroup><colgroup align="left"></colgroup><colgroup width="85px" align="left"></colgroup><colgroup width="80" span="4" align="right"></colgroup>';
		$thead = '<thead>' + $('#result-head').html() + '</thead>';
		var ctx = {worksheet: 'Amortization Schedule', table: $colgroup + $thead + '<tbody>' + $(table).html() + '</tbody>'};
		window.location.href = uri + base64(format(template, ctx));
	  }
	})();	
    
    function getBaseLog(x, y) {
        return Math.log(y) / Math.log(x);
    }
	
	function getUrlParameter(sParam)
	{
		var sPageURL = window.location.search.substring(1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) 
		{
			var sParameterName = sURLVariables[i].split('=');
			if (sParameterName[0] == sParam) 
			{
				return sParameterName[1];
			}
		}
	}
    
    function add_deposit() {
        var d = $('#deposit-name').val();
        var p = toN($('#deposit-percent').val());
        mDeposits.push({name: d, percent: p});
        depositsList();
        return false;
    }
    
    function deleteDeposit(i) {
        mDeposits.splice(i, 1);
        depositsList();
		return false;
    }
    
    function depositsList() {
        $('#deposits-body').html('');
        $table = $('<table />').addClass('table').appendTo($('#deposits-body'));
        $s = $('#income-type');
        $s.html('');
        $o = $('<option />').val('payment').html('уменьшение ежемесячного платежа').appendTo($s);
        $o = $('<option />').val('term').html('уменьшение срока').appendTo($s);
        var i = 0;
        mDeposits.forEach(function(o) {
            $tr = $('<tr />').appendTo($table);
            $td = $('<td />').appendTo($tr).html(o.name);
            $td = $('<td />').appendTo($tr).html(o.percent + '%');
            $td = $('<td />').appendTo($tr).html('<a title="Удалить" href="javascript:;" onclick="return deleteDeposit('+i+')"><span class="glyphicon glyphicon-remove"></span></a>');
            i++;
            $o = $('<option />').val('dep' + i).attr('data-percent', o.percent).html('Депозит: ' + o.name + ' ('+o.percent+'%)').appendTo($s);
        });
        $('#depositslisthead').html(' (' + mDeposits.length + ' шт.)');
    }    
    
</script>

<div class="navbar navbar-default" role="navigation">
<div class="container-fluid">
    <ul class="nav navbar-nav navbar-right">
      <?php if(!$user_data): ?>
      <li><a rel="nofollow" href="<?=Helper_OAuth_G::get_link()?>"><img height="40" src="<?=Kohana::$base_url?>img/sign-in-with-google.png" alt="google.com" title="Авторизация с помощью Google"/></a></li>
      <li><a rel="nofollow" href="<?=Helper_OAuth_F::get_link()?>"><img height="40" src="<?=Kohana::$base_url?>img/fblogin.png" alt="fb.com" title="Авторизация с помощью Facebook"/></a></li>
      <?php else: ?>
      <li><a rel="nofollow" href="javascript:;" onclick="return mortgage_list()"><span class="glyphicon glyphicon-cloud"></span> Список опубликованных</a></li>
      <?php if(isset($public_id)): ?>
      <li><a rel="nofollow" href="javascript:;" onclick="return unpublic_data()"><span class="glyphicon glyphicon-remove"></span> Удалить <?php if($public_id != 0) :?>публичный <?php endif; ?>график</a></li>
      <?php if($public_id == 0):?>
      <li><a rel="nofollow" href="javascript:;" onclick="return public_data()"><span class="glyphicon glyphicon-globe"></span> Опубликовать график</a></li>
      <?php endif; ?>
      <li><a rel="nofollow" href="javascript:;" onclick="return savedata()"><span class="glyphicon glyphicon-floppy-disk"></span> Сохранить график (<?=$user_data['user_name']?>)</a></li>
      <?php endif; ?>
      <li><a rel="nofollow" href="<?=Kohana::$base_url?>login/logout/"><span class="glyphicon glyphicon-log-out"></span> Выход</a></li>
      <?php endif; ?>
    </ul>
</div><!--/.container-fluid -->
</div>

<div class="row" id="mortgage-list" style="display:none">
    <div class="panel panel-default">
        <div class="panel-body">
			<ul id="mortgage-list-ul"></ul>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Изменение фактической даты</h4>
      </div>
      <div class="modal-body">
      <form action="" class="form-horizontal" onsubmit="return false">
		<div class="form-group" >
			<label class="col-sm-5">Новая дата:</label>
			<div class="col-sm-7"><input id="new-fact-date" type="date" class="form-control" value=""></div>
		  </div>
	  </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" onclick="saveFactDate()">Изменить</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Изменение ежемесячного платежа</h4>
      </div>
      <div class="modal-body">
      <form action="" class="form-horizontal" onsubmit="return false">
		<div class="form-group" >
			<label class="col-sm-5">Новая сумма:</label>
			<div class="col-sm-7"><input id="new-fact-sum" type="number" step="0.01" class="form-control" value=""></div>
		  </div>
	  </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" onclick="saveFactSum()">Изменить</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal2 -->
<div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Изменение досрочного платежа</h4>
      </div>
      <div class="modal-body">
      <form action="" class="form-horizontal" onsubmit="return false">
		<div class="form-group" >
			<label class="col-sm-5">Дата:</label>
			<div class="col-sm-7"><input id="new-fact-paydate" type="date" class="form-control" value=""></div>
		  </div>
		<div class="form-group" >
			<label class="col-sm-5">Новая сумма:</label>
			<div class="col-sm-7"><input id="new-fact-paysum" type="number" step="0.01" class="form-control" value=""></div>
		</div>
		<div class="form-group" >
			<div class="col-sm-6"><button class="btn btn-default btn-block" type="button" id="firstRePay" onclick="$('#new-fact-paysum').val($(this).html());"></button></div>
			<div class="col-sm-6"><button class="btn btn-default btn-block" type="button" id="secondRePay" onclick="$('#new-fact-paysum').val($(this).html());"></button></div>
		</div>
	  </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" onclick="saveRePaySum()">Изменить</button>
      </div>
    </div>
  </div>
</div>

<div id="toppane"></div>
<div class="jumbotron">
  <h1>Кредитный калькулятор</h1>
  <p>Контролируй свои расходы и пусть расходы не контролируют тебя. Планируй досрочные ежемесячные погашения и посмотри результат.</p>
  <p>Данный калькулятор создан для расчета ипотечного кредита, <a href="<?=Kohana::$base_url?>mortgage_about/">подробное описание и примеры по ссылке</a>.</p>
</div>  
<div class="row">
    <div class="alert alert-warning" role="alert">
        <div>Друзья, если вдруг у вас получились расхождения с банковскими выписками (даже на копейку) - сообщите об этом мне: <a href="https://docs.google.com/a/usbo.info/forms/d/1TWTG5MQYFQDOVpPVm9bw-HKNFgevd0sP80RbnsJRQuY/viewform">через форму</a> или напрямую на почту: <a href="mailto:feedback@usbo.info">feedback@usbo.info</a>. В течение одного дня мы разберемся с этим. Спасибо.</div>
    </div>
</div>
<?php if(!$user_data): ?>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
            <div><a href="<?=Kohana::$base_url?>mortgage/?public_id=2">Загрузить пример</a> | <a href="<?=Kohana::$base_url?>mortgage/">Очистить</a>
            <div>! Если Вы хотите сохранить данные для дальнейшего использования, предварительно авторизуйтесь.</div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
            <div><a href="javascript:;" onclick="return create_new()">Создать новый график</a></div>
			<div>! Предварительно сохраните данный, чтобы не потерять изменения</div>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
			<input id="schedule-description" type="text" class="form-control" value="" placeholder="Описание графика">
        </div>
    </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
        <div class="panel-heading ">
            <span class="glyphicon glyphicon-hand-right"></span> Основные данные по кредиту:
        </div>
        <div class="panel-body">
			<form method="post" enctype="multipart/form-data" action="#a" class="form-horizontal" onsubmit="return false">
			  <div class="form-group required" >
				<label class="col-sm-6">Сумма кредита:</label>
				<div class="col-sm-6"><input id="credit-sum" type="number" name="credit_sum" class="form-control" value=""></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-6">Срок кредита, мес:</label>
				<div class="col-sm-6"><input id="credit-month" type="number" name="credit_month" class="form-control" value=""></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-6">Процентная ставка:</label>
				<div class="col-sm-6"><input id="credit-percent" type="number" step="0.01" name="credit_percent" class="form-control" value=""></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-6 help-block">Ежемесячный платеж:</label>
				<div class="col-sm-6"><input id="month-payment" type="text" class="form-control" value="" disabled></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-6">Первый платеж - проценты:</label>
				<div class="col-sm-6"><input id="first-payment" type="checkbox" class="form-control" checked="checked" value="1"></div>
			  </div>
			  <div class="form-group required" id="day-payment-div">
				<label class="col-sm-6">Число ежемесячного платежа:</label>
				<div class="col-sm-6"><input id="day-payment" type="number" class="form-control" value=""></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-6">Дата оформления:</label>
				<div class="col-sm-6"><input id="start-date" type="date" class="form-control" value=""></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-6">Переносить воскресенье на понедельник:</label>
				<div class="col-sm-6"><input id="weekend-move" type="checkbox" class="form-control" checked="checked" value="1"></div>
			  </div>
			  <div class="form-group" >
				<label class="col-sm-6"><a href="<?=Kohana::$base_url?>mortgage_about/#bank">Банк:</a></label>
				<div class="col-sm-6"><select id="bank-name" class="form-control">
                    <option value="">Любой</option>
                    <option value="raiffeisen">РайффайзенБанк</option>
					<option value="vtb24">ВТБ24</option>
                    <option value="sberbank">Сбербанк (хитрое ЧДП)</option>
                </select></div>
			  </div>
              <div>
				<button id="btnshow" class="btn btn-success btn-lg btn-block">Показать график платежей</button>
			  </div>
			</form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading ">
            <a data-toggle="collapse" href="#coll3"><span class="glyphicon glyphicon-circle-arrow-down"></span> Статистика: </a>
        </div>
        <div id="coll3" class="panel-collapse">
        <div class="panel-body">
            <table class="table">
				<tr><th rowspan="2">Платеж</th><th colspan="2"><span class="stat-avg"></span></th><th rowspan="2">Всего (план)</th></tr>
				<tr><th>Средний в месяц</th><th>Всего (факт)</th></tr>
                <tr><td title="Сумма основного долга, уплаченная досрочными платежами">ОД&nbsp;(досрочный):</td><td class="text-right" id="stat-repayavg"></td><td class="statpaycur text-right" id="stat-repayavg-all"></td><td class="text-right" id="stat-inpay"></td></tr>
                <tr><td title="Сумма основного долга, уплаченная из ежемесячных платежей (ОД = ЕП - %)">ОД (из ЕП):</td><td class="text-right" id="stat-payavg"></td><td class="statpaycur text-right" id="stat-payavg-all"></td><td class="text-right" id="stat-payavg-all2"></td></tr>
				<tr class="warning"><td title="Сумма основного долга, уплаченная всего (сумма двух предыдущих)">ОД (всего):</td><td class="text-right" id="stat-payavg-sum"></td><td class="statpaycur text-right" id="stat-payavg-all-sum"></td><td class="text-right" id="stat-payavg-all2-sum"></td></tr>
				<tr><td>Проценты:</td><td class="text-right" id="stat-prcavg"></td><td class="statpaycur text-right" id="stat-percent-avg"></td><td class="text-right" id="stat-percent"></td></tr>
				<tr class="warning"><td>Итого:</td><td class="text-right" id="stat-payallavg"></td><td class="statpaycur text-right" id="stat-payallavg-all"></td><td class="text-right" id="stat-payallavg-all2"></td></tr>
				<tr><td title="За счет досрочных платежей">Экономия:</td><td>&nbsp;</td><td class="statpaycur text-right" id="stat-economy" title="Сэкономлено денег на указанную дату"></td><td class="text-right" id="stat-economy-all" title="Будет сэкономлено денег, если перестать вносить досрочные платежи (то есть фактически уже сэкономлено, но результат будет в конце кредита) / продолжить вносить досрочные платежи как запланировано"></td></tr>
            </table>
			<div>Дата последнего платежа: <span id="stat-date">(еще не рассчитана)</span>, <span id="stat-progressbar">(процент еще не рассчитан)</span></div>
        </div>
        </div>
    </div>
  </div>
  <div class="col-md-6">
	<div class="panel panel-default">
        <div class="panel-heading ">
            <a data-toggle="collapse" href="#coll1"><span class="glyphicon glyphicon-plus"></span> Досрочные погашения: </a>
        </div>
        <div id="coll1" class="panel-collapse collapse in">
        <div class="panel-body">
			<form method="post" enctype="multipart/form-data" action="" class="form-horizontal" onsubmit="return false">
			  <p class="help-block"></p>
			  <div class="form-group required" >
				<label class="col-sm-5">Сумма погашения, руб:</label>
				<div class="col-sm-7"><input id="add-sum" type="number" step="0.01" class="form-control" value=""></div>
			  </div>
			  <div class="form-group required" >
				<label class="col-sm-5">Дата погашения:</label>
				<div class="col-sm-7"><input id="add-date" type="date" class="form-control" value=""></div>
			  </div>
			  <div class="form-group " >
				<label class="col-sm-5">Тип погашения:</label>
				<div class="col-sm-7"><select id="income-type" class="form-control">
                    <option value="payment">уменьшение ежемесячного платежа</option>
                    <option value="term">уменьшение срока</option>
                </select></div>
			  </div>
			  <div class="form-group" >
				<label class="col-sm-5">Повторять:</label>
				<div class="col-sm-7"><select id="add-period" class="form-control">
                    <option value="">нет</option>
                    <option value="month">каждый месяц</option>
                    <option value="year">каждый год</option>
                </select></div>
			  </div>
              <div class="form-group add-period-group">
                <div class="col-sm-1"></div>
				<label class="col-sm-4">... до даты:</label>
				<div class="col-sm-7"><input id="add-monthly-date" type="date" class="form-control"></div>
			  </div>
			  <div class="form-group add-period-group">
                <div class="col-sm-1"></div>
				<label class="col-sm-8">... вычесть из суммы ежемесячный платеж:</label>
				<div class="col-sm-3"><input id="add-paym" type="checkbox" class="form-control" value="1"></div>
			  </div>
			  <div class="form-group" >
				<label class="col-sm-5">Комментарий:</label>
				<div class="col-sm-7"><input id="add-comment" type="text" class="form-control"></div>
			  </div>
			  <div>
				<button id="btnadd" class="btn btn-default">Добавить</button>
			  </div>
			</form>
        </div>
        </div>

        <div class="panel-heading ">
            <span class="glyphicon glyphicon-list-alt"></span> Список досрочных погашений (<a data-toggle="collapse" href="#coll2">показать <span id="addlisthead"></span></a>):
            <div class="right-group">
                <a href="javascript:;" onclick="return sortRepayments()" title="Сортировать в порядке возрастания"><span class="glyphicon glyphicon-sort-by-attributes"></span></a>
            </div>            
        </div>
        <div id="coll2" class="panel-collapse collapse">
        <div class="panel-body">
			<form class="form" onsubmit="return false">
				<table class="table">
					<thead>
						<tr><th></th><th>Дата</th><th>Сумма</th><th>В общем</th><th></th></tr>
					</thead>
					<tbody id="inadd-body">
						
					</tbody>
				</table>
			</form>
        </div>
        <div class="panel-body">
            <div>
                <button id="btnclear" class="btn btn-danger">Очистить</button>
            </div>
        </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="glyphicon glyphicon-calendar"></span> Список праздничных и выходных дней (<a data-toggle="collapse" href="#coll4">показать <span id="holidaylisthead"></span></a>):
        </div>
        <div id="coll4" class="panel-collapse collapse">
            <div class="panel-body">
                <div id="holiday-body">
                    
                </div>
            </div>
        </div>
        <div class="panel-body">
            <form class="form" action="" onsubmit="return addholiday()">
                <div class="form-group">
                    <div class="col-sm-5"><input id="holiday-date" type="date" class="form-control" value=""></div>
                    <div class="col-sm-4"><select id="holiday-type" class="form-control">
                        <option value="holiday">Выходной</option>
                        <option value="work">Рабочий</option>
                    </select></div>
                    <div class="col-sm-3"><button class="btn btn-primary">Добавить</button></div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="glyphicon glyphicon-exclamation-sign"></span> События (<a data-toggle="collapse" href="#coll5">показать <span id="eventslisthead"></span></a>):
        </div>
        <div id="coll5" class="panel-collapse collapse">
            <div class="panel-body">
                <div id="events-body">
                </div>
            </div>
        </div>
        <div class="panel-body">
            <form class="form" action="" onsubmit="return addevent('percent')">
                <p>Добавить изменение процента:</p>
                <div class="form-group">
                    <div class="col-sm-5"><input id="event-date" type="date" class="form-control" value=""></div>
                    <div class="col-sm-4"><input id="event-percent" type="number" step="0.01" class="form-control" value=""></div>
                    <div class="col-sm-3"><button class="btn btn-primary">Добавить</button></div>
                </div>
            </form>
            <form class="form" action="" onsubmit="return addevent('insurance')">
                <p>Добавить процент страховки от суммы основного долга:</p>
                <div class="form-group">
                    <div class="col-sm-5"><input id="event-date2" type="date" class="form-control" value=""></div>
                    <div class="col-sm-4"><input id="event-percent2" type="number" step="0.00001" class="form-control" value=""></div>
                    <div class="col-sm-3"><button class="btn btn-primary">Добавить</button></div>
                </div>
            </form>
			<form class="form" action="" onsubmit="return addevent('exrate')">
                <p>Добавить курс рублей/валюты:</p>
                <div class="form-group">
                    <div class="col-sm-5"><input id="event-date3" type="date" class="form-control" value=""></div>
                    <div class="col-sm-4"><input id="event-percent3" type="number" step="0.0001" class="form-control" value=""></div>
                    <div class="col-sm-3"><button class="btn btn-primary">Добавить</button></div>
                </div>
            </form>
        </div>
    </div>    
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="glyphicon glyphicon-exclamation-sign"></span> Депозиты (<a data-toggle="collapse" href="#coll6">показать <span id="depositslisthead"></span></a>):
        </div>
        <div id="coll6" class="panel-collapse collapse">
            <div class="panel-body">
                <div id="deposits-body">
                </div>
            </div>
        </div>
        <div class="panel-body">
            <form class="form" action="" onsubmit="return add_deposit()">
                <div class="form-group">
                    <div class="col-sm-5"><input id="deposit-name" placeholder="Наименование" type="text" class="form-control" value=""></div>
                    <div class="col-sm-4"><input id="deposit-percent" placeholder="Процент" type="number" step="0.01" class="form-control" value=""></div>
                    <div class="col-sm-3"><button class="btn btn-primary">Добавить</button></div>
                </div>
            </form>
        </div>
    </div>
  </div> <!-- 2nd col -->
</div> <!-- row -->

<div class="row" id="a">
  <div class="col-md-12">
	<div class="panel panel-default">
        <div class="panel-heading ">
            <span class="glyphicon glyphicon-calendar"></span> График платежей / Amortization schedule: 
			<div class="right-group"><a href="javascript:toExcel()"><img src="<?=Kohana::$base_url?>img/excel.png" alt="Excel" title="Экспорт графика платежей в Excel" /></a></div>
        </div>
        <div class="panel-body">
		   <table class="table" id="result">
			<thead id="result-head">
				<tr><th rowspan="2">№</th><th rowspan="2" colspan="2">Дата</th><th colspan="3">Погашение</th><th rowspan="2">Остаток</th></tr>
				<tr><th>Процент</th><th>Осн.долг</th><th>Всего</th></tr>
			</thead>
			<tbody id="result-body">
			</tbody>
            <tfoot>
                <tr class="legend"><td></td><td class="holiday-sign"></td><td colspan="5">Легенда:</td></tr>
                <tr class="holiday"><td></td><td class="holiday-sign"></td><td colspan="5">День перенесен в связи с тем, что выпал на выходной</td></tr>
				<tr class="holiday2"><td></td><td class="holiday-sign"></td><td colspan="5">День указан вручную</td></tr>
                <tr class="inadd"><td></td><td class="holiday-sign"></td><td colspan="5">Досрочное погашение кредита</td></tr>
                <tr class="curr"><td></td><td class="holiday-sign"></td><td colspan="5">Следующее погашение</td></tr>
            </tfoot>
		   </table>
        </div>
    </div>
  </div>
</div>

<div class="row">

<ul class="nav nav-tabs" role="tablist" id="tchg1">
  <li><a href="#tch1" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-stats"></span> На текущий день</a></li>
  <li class="active"><a href="#tch2" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-stats"></span> За весь период</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="tch1">
	<div id="chart1b1"></div>
  </div>
  <div class="tab-pane active" id="tch2">
	<div id="chart1b2"></div>
  </div>
</div>
</div>

<div class="row">
    <div id="chart1"></div>
</div>

<div class="row">

<ul class="nav nav-tabs" role="tablist" id="tchg2">
  <li><a href="#tch3" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-stats"></span> На текущий день</a></li>
  <li class="active"><a href="#tch4" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-stats"></span> За весь период</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="tch3">
	<div id="chart2g1"></div>
  </div>
  <div class="tab-pane active" id="tch4">
	<div id="chart2g2"></div>
  </div>
</div>
</div>


<div class="row">
    <div class="col-md-6">
        <div id="chart3"></div>
    </div>
    <div class="col-md-6">
        <div id="chart4"></div>
    </div>
</div>

<script type="text/javascript" src="<?=Kohana::$base_url?>js/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="<?=Kohana::$base_url?>js/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="<?=Kohana::$base_url?>js/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
<script type="text/javascript" src="<?=Kohana::$base_url?>js/plugins/jqplot.pieRenderer.min.js"></script>
<script type="text/javascript" src="<?=Kohana::$base_url?>js/plugins/jqplot.canvasOverlay.min.js"></script>
<script type="text/javascript">

function drawchart(nMax) {
    // Общая часть:
    
	var chart = chart_data[0];
	
    var chart_loanPercent = chart.loanPercent.slice(0);
	var chart_payment = chart.payment.slice(0);
	var chart_repayment = chart.repayment.slice(0);
	chart_loanPercent.splice(chart.currentMonth);
	chart_payment.splice(chart.currentMonth);
	chart_repayment.splice(chart.currentMonth);
    
    if (chart_loanPercent.length == 0) {
        chart_loanPercent[0] = 0;
        chart_payment[0] = 0;
        chart_repayment[0] = 0;
    }
    
    var nTicks = 20;
    var ticks = new Array();
    var fd = getFirstPayDate();
    var parts = Math.ceil(chart_loanPercent.length / nTicks);
    for (var i = 0; i < chart_loanPercent.length; i++) {
        var dd = new Date(fd);
        dd.setMonth(dd.getMonth() + i);
        var mm = dd.getMonth() + 1;
        sdate = '';
        if (mm < 10) sdate += '0';
        sdate += mm + '.' + dd.getFullYear();
        if (i % parts == 0 || i == chart_loanPercent.length - 1) {
            if (i % parts != 0) sdate = '';
            ticks.push([i+1, sdate]);
        }
    }

	var ticks2 = new Array();
    var parts = Math.ceil(chart.loanPercent.length / nTicks);
    for (var i = 0; i < chart.loanPercent.length; i++) {
        var dd = new Date(fd);
        dd.setMonth(dd.getMonth() + i);
        var mm = dd.getMonth() + 1;
        sdate = '';
        if (mm < 10) sdate += '0';
        sdate += mm + '.' + dd.getFullYear();
        if (i % parts == 0 || i == chart.loanPercent.length - 1) {
            if (i % parts != 0) sdate = '';
            ticks2.push([i+1, sdate]);
        }
    }
	
	var ticks3 = new Array();
    var parts = Math.ceil(chart_data[1].loanPercent.length / nTicks);
    for (var i = 0; i < chart_data[1].loanPercent.length; i++) {
        var dd = new Date(fd);
        dd.setMonth(dd.getMonth() + i);
        var mm = dd.getMonth() + 1;
        sdate = '';
        if (mm < 10) sdate += '0';
        sdate += mm + '.' + dd.getFullYear();
        if (i % parts == 0 || i == chart_data[1].loanPercent.length - 1) {
            if (i % parts != 0) sdate = '';
            ticks3.push([i+1, sdate]);
        }
    }

    var restAmountPlanned = new Array();
    var restAmount = new Array();
	var restAmountFact = new Array();
    var i = 0;
    chart.restAmount.forEach(function(e){
        if ((i + 1) >= chart.currentMonth) {
            restAmountPlanned[i] = e;
			if (typeof chart_data[1].restAmount[i] != 'undefined')
				restAmountFact[i] = chart_data[1].restAmount[i];
        }
        if (i < chart.currentMonth) {
            restAmount[i] = e;
        }
        i++;
    });
	var i = 0;
	chart_data[1].restAmount.forEach(function(e){
        if ((i + 1) >= chart.currentMonth) {
			restAmountFact[i] = e
        }
        i++;
    });
    
    if (restAmount.length == 0) {
        restAmount[0] = 0;
    }
	if (restAmountFact.length == 0) {
		restAmountFact[0] = 0;
	}
        
  var plot1 = $.jqplot ('chart1', [restAmount, restAmountPlanned, restAmountFact], {
      title: 'График уменьшения суммы основного долга',
      axesDefaults: {
        labelRenderer: $.jqplot.CanvasAxisLabelRenderer
      },
      canvasOverlay: {
            show: true,
            objects: [
                {verticalLine: {
                    name: 'barney',
                    x: chart.currentMonth,
                    yOffset: 0,
                    lineCap: 'butt',
                    lineWidth: 2,
                    color: 'rgb(89, 154, 199)',
                    shadow: false
                }}
            ]
        },
      axes: {
        xaxis: {
          pad: 0,
          ticks: ticks3
        },
		yaxis: {
		  min: 0, 
		  max: nMax
		},
      },
	  seriesDefaults: {
		lineWidth: 1.5,
		showMarker: false
	  },
      series:[
        {color: 'rgb(89, 239, 154', lineWidth: 2.5, shadow: false, label: 'Фактическое'},
		{color: '#aa0', shadow: false, label: 'По плану'},
		{color: '#bbb', shadow: false, label: 'Без плана'}
      ],
	   legend: {
		show: true,
		//placement: 'outsideGrid'
	   },
    });
	
	// График погашения процентов:
	
	var chart_loanPercentSum = new Array();
	var chart_paymentSum = new Array();
	var chart_repaymentSum = new Array();
	var chart_loanPercentSumC = new Array();
	var chart_paymentSumC = new Array();
	var chart_repaymentSumC = new Array();
	var chart_loanPercentSumAll = 0;
	var chart_loanPercentSumAllC = 0;
	var chart_restAmountAll = 0;
	var chart_restAmountAllC = 0;
	var chart_repayAmountAll = 0;
	var chart_repayAmountAllC = 0;
	
	for (var i = 0; i < chart.loanPercent.length; i++) {
		chart_loanPercentSumAll += chart.loanPercent[i];
		chart_restAmountAll += chart.payment[i];
		chart_repayAmountAll += chart.repayment[i];
		if (i < chart.currentMonth) {
			chart_loanPercentSumAllC += chart.loanPercent[i];
			chart_restAmountAllC += chart.payment[i];
			chart_repayAmountAllC += chart.repayment[i];
			chart_loanPercentSumC.push(chart_loanPercentSumAllC);
			chart_paymentSumC.push(chart_restAmountAllC);
			chart_repaymentSumC.push(chart_repayAmountAllC);
		}
		chart_loanPercentSum.push(chart_loanPercentSumAll);
		chart_paymentSum.push(chart_restAmountAll);
		chart_repaymentSum.push(chart_repayAmountAll);
	}
	
	// Всего погашено процентов без досрочных погашений:
	chart_percent1 = 0; // Уплачено без досрочных погашений на текущий день
	chart_percent2 = 0; // Уплачено без досрочных погашений всего
	for (var i = 0; i < chart_data[2].loanPercent.length; i++) {
		chart_percent2 += chart_data[2].loanPercent[i];
		if (i < chart.currentMonth) {
			chart_percent1 += chart_data[2].loanPercent[i];
		}
	}
	chart_percent3 = 0; // Уплачено с досрочными погашениями всего фактически
	for (var i = 0; i < chart_data[1].loanPercent.length; i++) {
		chart_percent3 += chart_data[1].loanPercent[i];
	}
		
	// DIV
	var all1 = (chart_loanPercentSumAllC + chart_restAmountAllC + chart_repayAmountAllC);
	var all2 = (chart_loanPercentSumAll + chart_restAmountAll + chart_repayAmountAll);
    if (chart.currentMonth != 0) {
        $('#stat-repayavg').html(NtoS(Math.round(chart_repayAmountAllC / chart.currentMonth * iSig) / iSig));
        $('#stat-payavg').html(NtoS(Math.round(chart_restAmountAllC / chart.currentMonth * iSig) / iSig));
		$('#stat-payavg-sum').html(NtoS(Math.round((chart_restAmountAllC + chart_repayAmountAllC) / chart.currentMonth * iSig) / iSig));
        $('#stat-prcavg').html(NtoS(Math.round(chart_loanPercentSumAllC / chart.currentMonth * iSig) / iSig));
        $('#stat-payallavg').html(NtoS(Math.round((chart_loanPercentSumAllC + chart_restAmountAllC + chart_repayAmountAllC) / chart.currentMonth * iSig) / iSig));
	} else {
        $('#stat-repayavg').html('-');
        $('#stat-payavg').html('-');
		$('#stat-payavg-sum').html('-');
        $('#stat-prcavg').html('-');
        $('#stat-payallavg').html('-');
    }
	$('#stat-repayavg-all').html(NtoS(Math.round(chart_repayAmountAllC * iSig) / iSig));
	$('#stat-payavg-all').html(NtoS(Math.round(chart_restAmountAllC * iSig) / iSig));
	$('#stat-payavg-all-sum').html(NtoS(Math.round((chart_restAmountAllC + chart_repayAmountAllC) * iSig) / iSig));
	$('#stat-percent-avg').html(NtoS(Math.round(chart_loanPercentSumAllC * iSig) / iSig));
	$('#stat-payallavg-all').html(NtoS(Math.round(all1 * iSig) / iSig));
	
	$('#stat-inpay').html(NtoS(Math.round(chart_repayAmountAll * iSig) / iSig));
	$('#stat-payavg-all2').html(NtoS(Math.round(chart_restAmountAll * iSig) / iSig));
	$('#stat-payavg-all2-sum').html(NtoS(Math.round((chart_restAmountAll + chart_repayAmountAll) * iSig) / iSig));
    $('#stat-percent').html(NtoS(Math.round(chart_loanPercentSumAll * iSig) / iSig));
	$('#stat-payallavg-all2').html(NtoS(Math.round(all2 * iSig) / iSig));
	
    if (chart_percent2 - chart_loanPercentSumAll != 0 || chart_percent2 - chart_percent3 != 0) {
        $('#stat-economy-all').html("<span class=\"statpaycur\">" + NtoS(Math.round((chart_percent2 - chart_percent3) * iSig) / iSig) + "</span> / " +NtoS(Math.round((chart_percent2 - chart_loanPercentSumAll) * iSig) / iSig));
    } else {
        $('#stat-economy-all').html('<span title="Вносите досрочные платежи, чтобы получить экономию">-</span>');
    }
    var tsum = Math.round((chart_percent1 - chart_loanPercentSumAllC) * iSig) / iSig;
	if (tsum > 0) {
		$('#stat-economy').html(NtoS(tsum));
	} else if (tsum == 0) {
		$('#stat-economy').html('<span title="Вносите досрочные платежи, чтобы получить экономию">-</span>');
	} else {
		$('#stat-economy').html('<a title="Узнать почему сумма отрицательная" class="minus-profit" href="/mortgage_about/#faq2">' + NtoS(tsum) + '</a>');
	}
	
	$('#stat-progressbar').html(Math.round(all1 / all2 * 100) + '%');
	
    if (chart_loanPercentSumC.length == 0) {
        chart_loanPercentSumC[0] = 0;
        chart_paymentSumC[0] = 0;
        chart_repaymentSumC[0] = 0;
    }
    
        var plot2 = $.jqplot ('chart2g1', [chart_loanPercentSumC, chart_paymentSumC, chart_repaymentSumC], {
          title: 'График погашения на текущий день',
          axesDefaults: {
            labelRenderer: $.jqplot.CanvasAxisLabelRenderer
          },
          axes: {
            xaxis: {
                pad: 0,
                ticks: ticks,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                  angle: -90 
                },
                drawMajorGridlines: true //
            }, 
            yaxis: {
              min: 0,
              max: Math.max(chart_loanPercentSumAllC, chart_restAmountAllC, chart_repayAmountAllC)
            },
          },
          seriesDefaults: {
            lineWidth: 1.5,
            showMarker: false
          },
           series: [
            {label: 'Процент'},
            {label: 'Основной'},
            {label: 'Досрочный'}
           ],
           legend: {
            show: true,
            placement: 'outsideGrid'
           },
        });
	
        var plot2a = $.jqplot ('chart2g2', [chart_loanPercentSum, chart_paymentSum, chart_repaymentSum], {
          title: 'График погашения',
          axesDefaults: {
            labelRenderer: $.jqplot.CanvasAxisLabelRenderer
          },
          canvasOverlay: {
                show: true,
                objects: [
                    {verticalLine: {
                        name: 'barney',
                        x: chart.currentMonth,
                        yOffset: 0,
                        lineCap: 'butt',
                        lineWidth: 2,
                        color: 'rgb(89, 154, 199)',
                        shadow: false
                    }}
                ]
            },
          axes: {
            xaxis: {
                pad: 0,
                ticks: ticks2,
                tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                tickOptions: {
                  angle: -90 
                },
                drawMajorGridlines: true //
            },
            yaxis: {
              min: 0,
              max: Math.max(chart_loanPercentSumAll, chart_restAmountAll, chart_repayAmountAll)
            },
          },
          seriesDefaults: {
            lineWidth: 1.5,
            showMarker: false
          },
           series: [
            {label: 'Процент'},
            {label: 'Основной'},
            {label: 'Досрочный'}
           ],
           legend: {
            show: true,
            placement: 'outsideGrid'
           },
        });
	// Распределение на сегодняшний день
	
      var data = [
        ['Проценты', chart_loanPercentSumAllC], ['Основной долг', chart_restAmountAllC], ['Досрочно', chart_repayAmountAllC]
      ];
      var plot3 = jQuery.jqplot ('chart3', [data], 
        {
          title: 'Распределение на текущий день',
          seriesDefaults: {
            renderer: jQuery.jqplot.PieRenderer, 
            rendererOptions: {
              fill: false,
              showDataLabels: true, 
              sliceMargin: 4, 
              lineWidth: 5
            }
          }, 
          legend: { show:true, location: 'e' }
        }
      );
    // Распределение
  
          var data = [
            ['Проценты', chart_loanPercentSumAll], ['Основной долг', chart_restAmountAll], ['Досрочно', chart_repayAmountAll]
          ];
          var plot4 = jQuery.jqplot ('chart4', [data], 
            {
              title: 'Распределение',
              seriesDefaults: {
                renderer: jQuery.jqplot.PieRenderer, 
                rendererOptions: {
                  fill: false,
                  showDataLabels: true, 
                  sliceMargin: 4, 
                  lineWidth: 5
                }
              }, 
              legend: { show:true, location: 'e' }
            }
          );
	// График платежей на текущий день:
	
    var tsumm = chart_loanPercentSumAllC + chart_restAmountAllC + chart_repayAmountAllC;
    
    plot2 = $.jqplot('chart1b1',[chart_loanPercent, chart_payment, chart_repayment],{
       title: 'График платежей на текущий день',
       stackSeries: true,
       showMarker: false,
       highlighter: {
        show: true,
        showTooltip: false
       },
       seriesDefaults: {
           fill: true,
       },
       series: [
        {label: 'Процент - ' + (Math.round(chart_loanPercentSumAllC / tsumm * 10000) / 100) + '%'},
        {label: 'Основной - ' + (Math.round(chart_restAmountAllC / tsumm * 10000) / 100) + '%'},
        {label: 'Досрочный - ' + (Math.round(chart_repayAmountAllC / tsumm * 10000) / 100) + '%'}
       ],
       legend: {
        show: true,
        placement: 'outsideGrid'
       },
       grid: {
        drawBorder: false,
        shadow: false
       },
       axes: {
           xaxis: {
              ticks: ticks,
              tickRenderer: $.jqplot.CanvasAxisTickRenderer,
              tickOptions: {
                angle: -90 
              },
              drawMajorGridlines: false
          }, 
          yaxis: {
            min: 0
          }
        }
    });
	
	plot2a = $.jqplot('chart1b2',[chart.loanPercent, chart.payment, chart.repayment],{
       title: 'График платежей',
       stackSeries: true,
       showMarker: false,
       highlighter: {
        show: true,
        showTooltip: false
       },
      canvasOverlay: {
            show: true,
            objects: [
                {verticalLine: {
                    name: 'barney',
                    x: chart.currentMonth,
                    yOffset: 0,
                    lineCap: 'butt',
                    lineWidth: 2,
                    color: 'rgb(89, 199, 154)',
                    shadow: false
                }}
            ]
        },
       seriesDefaults: {
           fill: true,
       },
       series: [
        {label: 'Процент'},
        {label: 'Основной'},
        {label: 'Досрочный'}
       ],
       legend: {
        show: true,
        placement: 'outsideGrid'
       },
       grid: {
        drawBorder: false,
        shadow: false
       },
       axes: {
           xaxis: {
              ticks: ticks2,
              tickRenderer: $.jqplot.CanvasAxisTickRenderer,
              tickOptions: {
                angle: -90 
              },
              drawMajorGridlines: false
          }, 
          yaxis: {
            min: 0
          }
        }
    });
	
    $('#tch2').removeClass('active');
    $('#tch4').removeClass('active');
	$('#tchg1 a:first').tab('show');
	$('#tchg2 a:first').tab('show');

};

</script>