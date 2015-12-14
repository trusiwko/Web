<?php defined('SYSPATH') or die('No direct script access.');

class Controller_CollectFilters extends Controller_Base {

    private $my_groups = array(
        'acc' => 'Переводы',
        'int' => 'Переводы между своими счетами',
        'prc' => 'Проценты, комиссии',
        'csh' => 'Кэшбэк',
        'oth' => 'Прочие',
		'c2c' => 'Card2Card',
		'nal' => 'Наличные',
		'ali' => 'Ebay, Aliexpress',
		'gam' => 'Игры, Магазины игрушек',
		'eln' => 'Продажа электроники',
		'cth' => 'Одежда и обувь',
		'tel' => 'Телекоммуникационные услуги',
		'res' => 'Рестораны',
        'gog' => 'Google',
    );

    // Мой фильтр
    public function action_myfilter() {
        $filter = array(
			'*' => $this->get_any_filer(),
            'raiffeisen' => $this->get_my_raiffeisen_filter(),
            'vtb24' => $this->get_my_vtb24_filter(),
            'tinkoff' => $this->get_my_tinkoff_filter(),
            'imoney' => $this->get_my_imoney_filter(),
            'kukuruza' => $this->get_my_kukuruza_filter(),
            'sdm' => $this->get_my_sdm_filter(),
            'hcb' => $this->get_my_hcb_filter(),
            'psb' => $this->get_my_psb_filter()
        );
        die(json_encode($filter));
    }

	private function get_any_filer() {
		$filter = array();
		$filter['mcc'] = array(
            '4111' => Helper_MCC::get_rus('4112'),
            '4812' => array('text' => array('PayPal' => $this->my_groups['ali'])),
			'4814' => $this->my_groups['tel'],
            '5311' => Helper_MCC::get_rus('5411'),
            '5331' => Helper_MCC::get_rus('5411'),
			'5399' => array(
				'text' => array(
					'Ситилинк' => $this->my_groups['eln'],
				)
			),
            '5451' => Helper_MCC::get_rus('5411'),
            '5499' => Helper_MCC::get_rus('5411'),
            '5532' => Helper_MCC::get_rus('5533'),
			'5621' => $this->my_groups['cth'],
			'5641' => $this->my_groups['cth'],
			'5651' => $this->my_groups['cth'],
			'5661' => $this->my_groups['cth'],
			'5691' => $this->my_groups['cth'],
			'5699' => $this->my_groups['cth'],
			'5719' => array(
				'text' => array(
					'PayPal' => $this->my_groups['ali'],
				)
			),
			'5812' => $this->my_groups['res'],
			'5814' => $this->my_groups['res'],
            '5921' => Helper_MCC::get_rus('5411'),
            '5946' => $this->my_groups['ali'],
			'6011' => $this->my_groups['nal'],
			'6012' => $this->my_groups['c2c'],
            '6300' => array('text' => array('VSK.RU' => 'ОСАГО', 'SOAO VSK' => 'ОСАГО')),
            '6536' => $this->my_groups['c2c'],
			'6538' => $this->my_groups['c2c'],
			'6540' => $this->my_groups['nal'],
			'5945' => $this->my_groups['gam'],
			'5300' => array(
				'text' => array(
					'METRO Cash&Carry' => Helper_MCC::get_rus('5411')
				)
			),
			'5732' => array(
				'text' => array(
					'PayPal' => $this->my_groups['ali'],
				),
                '*' => $this->my_groups['eln'],
			),
            '5813' => 'Рестораны',
			'5964' => array(
				'text' => array(
					'Aliexpress.com' => $this->my_groups['ali'],
					'ALIEXPRESS.COM' => $this->my_groups['ali'],
					'ALIBABA.COM' => $this->my_groups['ali'],
                    'Aliexpress' => $this->my_groups['ali'],
				)
			),
            '5968' => array(
				'text' => array(
					'Google' => $this->my_groups['gog'],
                    'GOOGLE *Google Storage' => $this->my_groups['gog'],
				)
			),
			'5999' => array(
				'text' => array(
					'Aliexpress.com' => $this->my_groups['ali'],
					'PayPal' => $this->my_groups['ali'],
					'YM.ALIEXPRESS' => $this->my_groups['ali'],
                    'YM*ALIEXPRESS' => $this->my_groups['ali'],
                    'KAPO DYUTI PEYD' => Helper_MCC::get_rus('5309'),
				)
			),
            '7399' => array(
				'text' => array(
					'GOOGLE *SERVICES' => $this->my_groups['gog'],
                    'Google' => $this->my_groups['gog'],
                    'WWF' => Helper_MCC::get_rus('8398'),
				)
			),
		);
		return $filter;
	}
	
	private function get_my_raiffeisen_filter() {
		$filter = array();
        $filter['groups_desc'] = array(
			'Прочие' => array(
				'text' => array (
					'SAFE 54 RENT FEE' => $this->my_groups['prc'],
					'KEY 54 INSURANCE' => 'Страхование',
					'REPAY KEY 54 INSURANCE' => 'Страхование',
					'CASH DEPOSIT' => $this->my_groups['nal'],
					'CASH WITHDRAWAL' => $this->my_groups['nal'],
					'Interest' => $this->my_groups['prc'],
				),
				'starts' => array(
					'MAABKK LOAN TO ' => 'Ипотека',
					'Card ***7672 RBA ATM ' => $this->my_groups['nal'],
					'MAABKK LOAN REPAY VAL ' => 'Ипотека',
					'MAABKK PRIN REPAY VAL ' => 'Ипотека (основной долг)',
					'MAABKK INTR REPAY VAL ' => 'Ипотека (проценты)',
				),
				'regexp' => array(
					'P\/O.*?30232810700000000022' => $this->my_groups['int'],
					'P\/O.*?40101810200000010001$' => 'Налоги',
					'P\/O.*?40101810200000010001RC' => 'Налоги',
					'P\/O.*?40702810600001400163' => 'Ипотека (cтраховка)',
                    'P\/O.*?40701810825000000002' => 'Ипотека (cтраховка)'
				)
			)
		);
		return $filter;
	}

	private function get_my_vtb24_filter() {
		$filter = array();
        $filter['groups_desc'] = array(
			'Прочие' => array(		
				'text' => array(
					'554386XXXXXX5793 Payment To Contract   salary ' => 'Зарплата, Пособие',
				),
				'starts' => array(
					'554386XXXXXX5793 Retail RUS MOSCOW Tinkoff Bank Card2Card' => $this->my_groups['c2c'],
                    '554386XXXXXX5793 Unique RUS MOSCOW TCS BANK' => $this->my_groups['c2c'],
					'554386XXXXXX5793 Retail RUS MOSCOW TCS BANK' => $this->my_groups['c2c'],
					'554386XXXXXX5793 ATM RUS' => $this->my_groups['nal'],
					'554386XXXXXX5793 Credit RUS SAMARA 30, SPORTIVNAYA' => 'Зарплата, Пособие',
				),
				'regexp' => array(
					
				)
			)
		);
		return $filter;
	}
        
    private function get_my_tinkoff_filter() {
        $filter = array();
        $filter['mcc'] = array(
            '4816' => array(
                'text' => array(
                    'KFTS*MAJORDOMO' => 'Хостинг',
                    'ROBOKASSA*REGGI' => 'Хостинг',
					'PayPal' => $this->my_groups['ali'],
                )
            ),
			'5399' => array(
				'text' => array(
					'Aliexpress.com' => $this->my_groups['ali'],
				)
			),
			'5722' => array(
				'text' => array(
					'Мвидео' => $this->my_groups['eln'],
					'Эльдорадо' => $this->my_groups['eln'],
				)
			),
            '7299' => array(
                'text' => array(
                    'KOMPANIYA HOSTIM.KZ TO' => 'Хостинг',
                    'YM *Triford' => 'Репетиторство',
                    'YM.VASHREPETITOR' => 'Репетиторство',
                )
            ),
        );
        $filter['groups_desc'] = array(
            'Переводы' => array(
                'text' => array(
                    'Ренессанс (Кукуруза)' => 'Погашение кредиток',
                    'АйМани банк' => $this->my_groups['int'],
					'Счет в ПромСвязьБанке' => $this->my_groups['int'],
                    'ХКФ (кредитная)' => $this->my_groups['int'],
                    'ПЖРУ' => 'Коммунальные услуги',
                    'ПТС'  => 'Коммунальные услуги',
					'СКС'  => 'Коммунальные услуги',
                    'Райффайзен (ипотека)' => $this->my_groups['int'],
                    'Питание в школе' => 'Обучение',
                    'ХКФ. Дебетовая' => $this->my_groups['int'],
                    'ЖКУ. Кап.ремонт' => 'Коммунальные услуги',
                    'Кредитка Милы' => $this->my_groups['int'],
					'Оплата налогов и сборов' => 'Налоги',
					'Exist' => 'Автозапчасти и аксессуары',
                    'На счет в другом банке' => $this->my_groups['int'],
                )
            ), 
			'Мобильные/иб' => $this->my_groups['tel'],
			'Эл. кошельки/иб' => $this->my_groups['nal'],
            'Прочие услуги/иб'  => array(
				'text' => array(
                    'ГИБДД онлайн РФ' => 'Штрафы',
                )
            ),
			'Переводы/иб' => array(
				'text' => array(
					'Райффайзен (ипотека)' => $this->my_groups['int'],
					'Ренессанс (Кукуруза)' => 'Погашение кредиток',
					'АйМани банк' => $this->my_groups['int'],
					'ХКФ (кредитная)' => $this->my_groups['int'],
					'ХКФ. Дебетовая' => $this->my_groups['int'],
					'ПЖРУ' => 'Коммунальные услуги',
					'ПТС' => 'Коммунальные услуги',
					'ЖКУ. Кап.ремонт' => 'Коммунальные услуги',
					'СКС' => 'Коммунальные услуги',
					'Питание в школе' => 'Обучение',
					'Ярослав. Английский' => 'Обучение',
                    'Английский язык' => 'Обучение',
					'Внутрибанковский перевод' => $this->my_groups['int'],
					'Кредитка Милы' => $this->my_groups['int'],
					'Счет в ПромСвязьБанке' => $this->my_groups['int'],
					'Гончаренко Людмила: «В счет погашения ипотечного кредита»' => 'Ипотека',
					'Авдеева Татьяна: «Добровольный взнос на лечение»' => Helper_MCC::get_rus('8398'),
					'Буслов Антон: «ДОБРОВОЛЬНОЕ ПОЖЕРТВОВАНИЕ НА ЛЕЧЕНИЕ»' => Helper_MCC::get_rus('8398'),
					'Перевод на Вклад (депозитный счёт)' => $this->my_groups['int'],
					'На счет в другой банк' => $this->my_groups['int'],
                    'На счет в другом банке' => $this->my_groups['int'],
                    'Оплата налогов и сборов' => 'Налоги',
                    'Клиенту Тинькофф Банка' => $this->my_groups['nal'],
                    'Exist' => Helper_MCC::get_rus('5533'),
				),
				'starts' => array(
					'На счёт «' => $this->my_groups['int'],
					'На вклад «' => $this->my_groups['int'],
                    'Перечисление пополнения на вклад' => $this->my_groups['int'],
				),
                'sum' => array(
                    '-1679.37' => array(
                        'oper_group' => 'Коммунальные услуги',
                        'oper_description' => 'ПТС. %s',
                    ), 
                    '-491.74' => array(
                        'oper_group' => 'Коммунальные услуги',
                        'oper_description' => 'СКС. %s',
                    ),
                    '-831.63' => array(
                        'oper_group' => 'Коммунальные услуги',
                        'oper_description' => 'ПЖРУ. %s',
                    ),
                )
			),
			'Пополнение вклада' => $this->my_groups['int'],
			'Проценты' => $this->my_groups['prc'],
			'Другое' => array(
				'text' => array(
					'Гончаренко Павел' => $this->my_groups['int'],
					'Перевод для закрытия накопительного счета' => $this->my_groups['int'],
					//
					'Пополнение. Элекснет' => $this->my_groups['nal'],
					'Пополнение. Евросеть' => $this->my_groups['nal'],
					'Пополнение. КиберПлат' => $this->my_groups['nal'],
					'Пополнение. "Золотая Корона"' => $this->my_groups['nal'],
					'Пополнение. Comepay' => $this->my_groups['nal'],
					'Пополнение. Рапида, Связной' => $this->my_groups['nal'],
					'Пополнение. QIWI' => $this->my_groups['nal'],
					'Пополнение. CONTACT' => $this->my_groups['nal'],
					'Пополнение через РНКО "ПЛАТЕЖНЫЙ ЦЕНТР" (ООО)' => $this->my_groups['nal'],
					'Пополнение через Альфа-Банк' => $this->my_groups['nal'],
					//
					'Троеглазов Виктор' => $this->my_groups['nal'],
					'Буланова Лейла' => $this->my_groups['nal'],
					'Пополнение через ОАО "ВОЛГО-КАМСКИЙ БАНК"' => $this->my_groups['nal'],
					'Пополнение. Карта другого банка' => $this->my_groups['c2c'],
					//
					'Пополнение. Тинькофф Банк. Зачисление денежных средств по обращению' => $this->my_groups['prc'],
                    'Пополнение. Тинькофф Банк. Бонус в День Рождения' => $this->my_groups['prc'],
					'Плата за предоставление услуги SMS-банк' => $this->my_groups['prc'],
					'Плата за Программу страховой защиты' => $this->my_groups['prc'],
					'Комиссия за пополнение.' => $this->my_groups['prc'],
					'Отмена комиссии за операцию в кредитных организациях' => $this->my_groups['prc'],
					'Проценты по неразрешенному овердрафту' => $this->my_groups['prc'],
					
					'Плата за обслуживание' => $this->my_groups['prc'],
					'Плата за обслуживание.' => $this->my_groups['prc'],
					'Проценты на остаток по счету' => $this->my_groups['prc'],
					//
					'Вознаграждение за операции покупок' => $this->my_groups['csh'],
					'Пополнение. Тинькофф Банк. Компенсация покупок по программе лояльности' => $this->my_groups['csh'],
					'Пополнение. Тинькофф Банк. Компенсация покупок по программе лояльности Браво' => $this->my_groups['csh'],
					'Пополнение для перечисления на вклад. CONTACT' => $this->my_groups['int'],
				),
				'starts' => array(
					'На счёт «' => $this->my_groups['int'],
					'Внутренний перевод с договора' => $this->my_groups['int'],
					'Изъятие вклада при закрытии. Закрытие вклада' => $this->my_groups['int'],
					'Частичное изъятие вклада' => $this->my_groups['int'],
					//
					'Комиссия за операцию в кредитных организациях' => $this->my_groups['prc'],
					'Начисление процентов по вкладу' => $this->my_groups['prc'],
					'Комиссия за выдачу наличных в' => $this->my_groups['prc'],
                    
				)
			),
			'Компенсация' => array(
				'text' => array(
					'Компенсация за пополнение вклада через РКЦ' => $this->my_groups['prc'],
				)
			)
        );
        return $filter;
    }
    
    private function get_my_imoney_filter() {
        $filter = array();
        $filter['groups_desc'] = array(
            'Прочие' => array(
                'starts' => array(
                    'Перевод с карты *3976' => $this->my_groups['int'],
                    'Перевод средств по договору № 3412839791 Гончаренко Павел Леонидович' => $this->my_groups['int'],
					'Перевод (списание) средств со вклада' => $this->my_groups['int'],
					'Перевод (зачисление) средств во вклад' => $this->my_groups['int'],
					'Начисление процентов по вкладу' => $this->my_groups['prc'],
					'Комиссия за обслуживание карточного счета' => $this->my_groups['prc'],
                    //
                    'Возврат/отмена по операции в RU LUKOIL AZS 63022 URNP RU360013, 12.09.2015' => 'АЗС',
                )
            ),
            '*' => array(
                'starts' => array(
					'Авторизация. Кэшбэк-зачисление' => $this->my_groups['csh'],
					'Возврат средств по программе CashBack' => $this->my_groups['csh'],
                )
            )
        );
        return $filter;
    }
    
    private function get_my_kukuruza_filter() {
        $filter = array();
		$filter['mcc'] = array(
            '6012' => array(
                'text' => array(
                    'QIWI*Kvartplata 24' => 'Коммунальные услуги',
                )
            ),		
			'7299' => array(
				'text' => array(
					'YM *Triford' => 'Репетиторство'
				)
			)
		);
        $filter['groups_desc'] = array(
            'Разные бытовые услуги' => array(
                'text' => array(
                    'YM *Triford' => 'Репетиторство',
                )
            ),
			'Услуги (прочие)' => array(
                'text' => array(
                    'YM *Triford' => 'Репетиторство',
                )
            ),
            'Прочие' => array(
                'text' => array(
				    // Это всё до июля 2015, когда ничего не было.
                    'Билайн' => 'Телекоммуникационные услуги',
					'Погашение кредита' => 'Погашение кредиток',
					'Лукойл' => 'АЗС',
					'AZS OLVI' => 'АЗС',
					'SAMGES ONLAYN' => 'Коммунальные услуги',
					'SAMARAGAZ LLC' => 'Коммунальные услуги',
					'Ашан' => Helper_MCC::get_rus('5411'),
					'Магнит' => Helper_MCC::get_rus('5411'),
					'Карусель' => Helper_MCC::get_rus('5411'),
					'Пятерочка' => Helper_MCC::get_rus('5411'),
					'Перекресток' => Helper_MCC::get_rus('5411'),
					'PYATEROCHKA 3' => Helper_MCC::get_rus('5411'),
					'AUCHAN SAMARA' => Helper_MCC::get_rus('5411'),
					'AUCHAN 067 YU' => Helper_MCC::get_rus('5411'),
					'M VIDEO 103' => $this->my_groups['eln'],
					'CITILINK SAMA' => $this->my_groups['eln'],
					'CITILINK SAMARA' => $this->my_groups['eln'],
                    'M VIDEO 103 1' => $this->my_groups['eln'],
					'SPORTMASTER S' => Helper_MCC::get_rus('5941'),
					'IKEA' => 'Мебель и оборудование',
					'IKEA DOM 14CA' => 'Мебель и оборудование',
					'IKEA DOM 14 S' => Helper_MCC::get_rus('5411'),
					'OOO FENIKS' => $this->my_groups['res'],
					'MCDONALDS 251' => $this->my_groups['res'],
					'IKEA DOM 14RE' => $this->my_groups['res'],
					'KFC AMBAR  SA' => $this->my_groups['res'],
					'L\'Etoile' => 'Магазины косметики',
					'APTEKA' => 'Аптеки',
					'BIOMED' => 'Аптеки',
                    'OOO TOPOL' => 'Аптеки',
                    'IP SEDELKINA' => 'Аптеки',
					'APTEKA ALIYA' => 'Аптеки',
                    'IP ZAGORSKAYA' => 'Аптеки',
					'IP MOSKVICHEV' => 'Аптеки',
					'IP MOSKVICHEVA' => 'Аптеки',
					'ZNAKOMYY FARMATSEFT 3' => 'Аптеки',
					'LEROY MERLIN SAMARA 3' => 'Садовые принадлежности',
					'Леруа Мерлен' => 'Садовые принадлежности',
					'WWW RZD RU' => Helper_MCC::get_rus('4112'),
					'BUDU MAMOY' => $this->my_groups['cth'],
                    '210410 KARI' => $this->my_groups['cth'],
					'TSENTROBUV 2 33' => $this->my_groups['cth'],
					'KIABI KERUSKA 3' => $this->my_groups['cth'],
					'YM VASHREPETITOR' => 'Репетиторство',
					'ALIBABA COM' => $this->my_groups['ali'],
                    'WWW ALIEXPRESS COM' => $this->my_groups['ali'],
					'Возврат платежа: ALIBABA COM' => $this->my_groups['ali'],
					'Дочки-Сыночки' => $this->my_groups['gam'],					
					'IGROMAGIYA KOSMOPORT' => $this->my_groups['gam'],
                    'PARFENON SHOP' => $this->my_groups['gam'],
                    'IGRY I PODARK' => $this->my_groups['gam'],
					'Корректировка' => $this->my_groups['prc'],
                    'TCS BANK' => $this->my_groups['c2c'],
                    'Пополнение' => $this->my_groups['c2c'],
                    'WWW NETPRINT RU' => Helper_MCC::get_rus('5946'),
					'Пополнение с карты MASTERCARD' => $this->my_groups['c2c'],
					'Покупка с картой «Кукуруза»' => $this->my_groups['csh'],
					'Обмен баллов на скидку' => $this->my_groups['csh'],
                ),
				'starts' => array(
					'Перевод минимального платежа по задолженности перед КБ "РЕНЕССАНС КРЕДИТ"' => 'Погашение кредиток', //$this->my_groups['int'],
				)
            ),
			'Торговля по каталогам' => array(
				'text' => array(
					'Aliexpress' => $this->my_groups['ali'],
					'ALIEXPRESS.COM' => $this->my_groups['ali'],
				)
			),
			'Универмаги' => array(
				'text' => array(
					'Mothercare' => $this->my_groups['cth'],
				)
			),
			'Телекоммуникационные услуги' => array(
				'sum' => array(
					'-3662' => array('oper_group' => 'Коммунальные услуги', 'oper_description' => 'ПТС. %s')
				)
			),
			'Скрытые' => $this->my_groups['tel'],
			'Сотовая связь'  => $this->my_groups['tel'],
			
        );
        return $filter;
    }
    
    private function get_my_sdm_filter() {
        $filter = array();
        $filter['groups_desc'] = array(
            'Прочие' => array(
                'text' => array(
                    'Поступление заработной платы' => 'Зарплата, Пособие',
                    '<<Поступило на счет ГОНЧАРЕНКО ПАВЕЛ ЛЕОНИДОВИЧ>>' => 'Зарплата, Пособие',
                    '<<Поступило на счет ГОНЧАРЕНКО ПАВЕЛ ЛЕОНИДОВИЧ>> ' => 'Зарплата, Пособие',
                    'Поступило на счет ГОНЧАРЕНКО ПАВЕЛ ЛЕОНИДОВИЧ' => 'Зарплата, Пособие',
                ),
                'starts' => array(
                    'Уплата процентов по депозиту' => $this->my_groups['prc'],
                    'Получение наличных в АТМ' => $this->my_groups['nal'],
                    'Получение наличных в ПВН' => $this->my_groups['nal'],
                    'Комиссия за транзакцию' => $this->my_groups['prc'],
                )
            )
        );
        return $filter;
    }
    
    private function get_my_hcb_filter() {
        $filter = array();
        $filter['groups_desc'] = array(
            'Поступивший перевод' => array(
                '*' => $this->my_groups['int'],
            ),
            'Поступление денежных средств "CASHBACK"' => array(
                '*' => $this->my_groups['csh'],
            ),
            'Поступление денежных средств на счет погашения' => array(
                '*' => $this->my_groups['int'],
            ),
            'Поступление денежных средств на счет' => array(
                '*' => $this->my_groups['int'],
            ),
            'Списание денежных средств со счета' => array(
                '*' => $this->my_groups['int'],
            ),
            'Перевод в другой банк' => array(
                '*' => $this->my_groups['int'],
            ),
            'Перевод на карту' => array(
                '*' => $this->my_groups['int'],
            ),
            'Комиссия за перевод в другой банк' => array(
                '*' => array(
                    'oper_group' => $this->my_groups['prc'],
                    'oper_description' => 'Комиссия за перевод в другой банк. %s'
                )
            ),
            'Кафе и рестораны' => $this->my_groups['res'],
            'Продукты питания' => array(
                '*' => Helper_MCC::get_rus('5411')
            ),
            //'Одежда и обувь' => array('*' => 'Одежда и обувь'),
            'Коммунальные платежи' => array(
                'sum' => array(
                    '-1784.99' => array('oper_group' => 'Коммунальные услуги', 'oper_description' => 'ПТС. %s')
                ),
                '*' => 'Коммунальные услуги'
            ),
            'Автомобиль' => array(
                'starts' => array(
                    'АЗС' => 'АЗС'
                )
            ),
            'Здоровье и красота' => array(
                'text' => array(
                    'Аптека' => 'Аптеки',
                    'OOO TEREK' => 'Аптеки',
                    'IP MOSKVICHEVA' => 'Аптеки',
                )
            ),
            'MCC-категория операции не определена' => array(
                'text' => array(
                    'KIABI KERUSKA 3' => $this->my_groups['cth'],
					'PayPal' => $this->my_groups['ali'],
                ),
                '*' => $this->my_groups['oth'],
            ),
            'Платёжные системы' => array(
                'text' => array(
                    'Элекснет - пополнение кошелька' => $this->my_groups['nal'],
                )
            ),
            'Платеж' => $this->my_groups['nal'],
            'Оплата телефона' => array(
                '*' => array(
                    'oper_group' => 'Телекоммуникационные услуги',
                    'oper_description' => 'Оплата телефона. %s'
                )
            ),
            'Досуг и развлечения' => array(
                'text' => array(
                    'Госуслуги' => 'Налоги'
                )
            ),
            'Прочее' => array(
                'text' => array(
                    'CITILINK SAMARA, 163A, MOSKOVSKOE SH., SAMARA, RU' => 'Продажа электроники',
                    'CITILINK SAMARA' => 'Продажа электроники',
                ),
                '*' => $this->my_groups['oth'],
            )
        );
        return $filter;
    }
    
    private function get_my_psb_filter() {
        $filter = array();
        $filter['groups_desc'] = array(
            'Комиссия' => $this->my_groups['prc'],
            'Кэшбэк' => $this->my_groups['csh'],
            'Межбанк' => $this->my_groups['int'],
            'Мобильная связь' => 'Телекоммуникационные услуги',
            'Проценты, дивиденды и доходы от вложений' => array(
                'starts' => array(
                    'Зачисление Cash Back za' => $this->my_groups['csh'],
                    'Уплата процентов на остаток по счету' => $this->my_groups['prc'],
                )
            ),
            'Прочие доходы' => array(
                'starts' => array(
                    'Зачисление Cash Back za' => $this->my_groups['csh'],
                    'Зачисление PSB RETAIL' => $this->my_groups['int'],
                )
            )
        );
        return $filter;
    }
}