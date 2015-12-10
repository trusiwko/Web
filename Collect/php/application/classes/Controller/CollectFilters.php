<?php defined('SYSPATH') or die('No direct script access.');

class Controller_CollectFilters extends Controller_Base {

    private $my_groups = array(
        'acc' => 'Переводы',
        'int' => 'Переводы между своими счетами',
        'prc' => 'Проценты, комиссии',
        'csh' => 'Кэшбэк',
        'oth' => 'Прочие'
    );

    // Мой фильтр
    public function action_myfilter() {
        $filter = array(
            'raiffeisen' => $this->get_my_raiffeisen_filter(),
            'vtb24' => $this->get_my_vtb24_filter(),
            'tinkoff' => $this->get_my_tinkoff_filter(),
            'imoney' => $this->get_my_imoney_filter(),
            'kukuruza' => $this->get_my_kukuruza_filter(),
            'sdm' => $this->get_my_sdm_filter()
        );
        die(json_encode($filter));
    }

	private function get_my_raiffeisen_filter() {
		$filter = array();
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
			'Прочие' => array(
				'text' => array (
					'SAFE 54 RENT FEE' => $this->my_groups['prc'],
					'KEY 54 INSURANCE' => 'Страхование',
					'REPAY KEY 54 INSURANCE' => 'Страхование',
					'CASH DEPOSIT' => 'Наличные, Card2Card',					
					'CASH WITHDRAWAL' => 'Наличные, Card2Card',
					'Interest' => $this->my_groups['prc'],
				),
				'starts' => array(
					'MAABKK LOAN TO ' => 'Ипотека',
					'Card ***7672 RBA ATM ' => 'Наличные, Card2Card',
					'MAABKK LOAN REPAY VAL ' => 'Ипотека',
					'MAABKK PRIN REPAY VAL ' => 'Ипотека (основной долг)',
					'MAABKK INTR REPAY VAL ' => 'Ипотека (проценты)',
				),
				'regexp' => array(
					'P\/O .*?30232810700000000022' => $this->my_groups['int'],
					'P\/O .*?40101810200000010001$' => 'Налоги',
					'P\/O .*?40101810200000010001RC' => 'Налоги',
					'P\/O .*?40702810600001400163' => 'Ипотека (cтраховка)',
                    'P\/O .*?40701810825000000002' => 'Ипотека (страховка)'
				)
			)
		);
		return $filter;
	}

	private function get_my_vtb24_filter() {
		$filter = array();
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
			'Прочие' => array(		
				'text' => array(
					'554386XXXXXX5793 Payment To Contract   salary ' => 'Зарплата, Пособие',
				),
				'starts' => array(
					'554386XXXXXX5793 Retail RUS MOSCOW Tinkoff Bank Card2Card' => 'Наличные, Card2Card',
                    '554386XXXXXX5793 Unique RUS MOSCOW TCS BANK' => 'Наличные, Card2Card',
					'554386XXXXXX5793 Retail RUS MOSCOW TCS BANK' => 'Наличные, Card2Card',
					'554386XXXXXX5793 ATM RUS' => 'Наличные, Card2Card',
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
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
            'Переводы' => array(
                'text' => array(
                    'Ренессанс (Кукуруза)' => 'Погашение кредиток',
                    'АйМани банк' => $this->my_groups['int'],
					'Счет в ПромСвязьБанке' => $this->my_groups['int'],
                    'ХКФ (кредитная)' => 'Погашение кредиток', //$this->my_groups['int'],
                    'ПЖРУ' => 'Коммунальные услуги',
                    'ПТС'  => 'Коммунальные услуги',
					'СКС'  => 'Коммунальные услуги',
                    'Райффайзен (ипотека)' => $this->my_groups['int'],
                    'Питание в школе' => 'Обучение',
                    'ХКФ. Дебетовая' => $this->my_groups['int'],
                    'ЖКУ. Кап.ремонт' => 'Коммунальные услуги',
                    'Кредитка Милы' => $this->my_groups['int'],
					'Оплата налогов и сборов' => 'Налоги',
					'Exist' => 'Автомобиль',
                    'На счет в другом банке' => $this->my_groups['int'],
                )
            ), 
			'Разные товары' => array(
				'text' => array(
					'Aliexpress.com' => 'Aliexpress',
					'YM.ALIEXPRESS' => 'Aliexpress',
					'YM*ALIEXPRESS' => 'Aliexpress',
					'Ситилинк' => 'Продажа электроники'
				)
			),
			'Дом, ремонт' => array(
				'text' => array(
					'Мвидео' => 'Продажа электроники',
				)
			),
			'Торговля по каталогам' => array(
				'text' => array(
					'ALIBABA.COM' => 'Aliexpress',
					'Aliexpress.com' => 'Aliexpress',
				)
			),
			'Другое' => array(
				'text' => array(
					'Гончаренко Павел' => $this->my_groups['int'],
				)
			),
			'Остальные' => array(
				'text' => array(
					'MAIL.RU' => 'Развлечения',
				)
			),
			'Рекламные услуги' => array(
				'text' => array(
					'ODNOKLASSNIKI' => 'Развлечения'
				)
			)
        );
        return $filter;
    }
    
    private function get_my_imoney_filter() {
        $filter = array();
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
            'Прочие' => array(
                'starts' => array(
                    'Перевод с карты *3976' => $this->my_groups['int'],
                    'Перевод средств по договору № 3412839791 Гончаренко Павел Леонидович' => $this->my_groups['int'],
                )
            )
        );
        return $filter;
    }
    
    private function get_my_kukuruza_filter() {
        $filter = array();
        $filter['groups'] = array();
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
            'Наличные, Card2Card' => array(
                'text' => array(
                    'QIWI*Kvartplata 24' => 'Коммунальные услуги',
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
					'Ашан' => 'Супермаркеты',
					'Магнит' => 'Супермаркеты',
					'Карусель' => 'Супермаркеты',
					'Пятерочка' => 'Супермаркеты',
					'Перекресток' => 'Супермаркеты',
					'PYATEROCHKA 3' => 'Супермаркеты',
					'AUCHAN SAMARA' => 'Супермаркеты',
					'AUCHAN 067 YU' => 'Супермаркеты',
					'Дочки-Сыночки' => 'Игры, Магазины игрушек',
					'M VIDEO 103' => 'Продажа электроники',
					'CITILINK SAMA' => 'Продажа электроники',
					'CITILINK SAMARA' => 'Продажа электроники',
					'SPORTMASTER S' => 'Спорттовары',
					'IKEA' => 'Мебель и оборудование',
					'IKEA DOM 14CA' => 'Мебель и оборудование',
					'IKEA DOM 14 S' => 'Продовольственные магазины',
					'OOO FENIKS' => 'Рестораны',
					'MCDONALDS 251' => 'Рестораны',
					'IKEA DOM 14RE' => 'Рестораны',
					'KFC AMBAR  SA' => 'Рестораны',
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
					'WWW RZD RU' => 'ЖД, Электрички',
					'BUDU MAMOY' => 'Одежда и обувь',
                    '210410 KARI' => 'Одежда и обувь',
					'TSENTROBUV 2 33' => 'Одежда и обувь',
					'KIABI KERUSKA 3' => 'Одежда и обувь',
					'YM VASHREPETITOR' => 'Репетиторство',
					'ALIBABA COM' => 'Aliexpress',
                    'WWW ALIEXPRESS COM' => 'Aliexpress',
					'Возврат платежа: ALIBABA COM' => 'Aliexpress',
					'IGROMAGIYA KOSMOPORT' => 'Игры, Магазины игрушек',
					'Корректировка' => $this->my_groups['prc'],
                    'PARFENON SHOP' => 'Игры, Магазины игрушек',
                    'IGRY I PODARK' => 'Игры, Магазины игрушек',
                    'M VIDEO 103 1' => 'Продажа электроники',
                    'TCS BANK' => 'Наличные, Card2Card',
                    'Пополнение' => 'Наличные, Card2Card',
                    'WWW NETPRINT RU' => 'Фото оборудование',

                ),
				'starts' => array(
					'Перевод минимального платежа по задолженности перед КБ "РЕНЕССАНС КРЕДИТ"' => 'Погашение кредиток', //$this->my_groups['int'],
				)
            ),
			'Торговля по каталогам' => array(
				'text' => array(
					'Aliexpress' => 'Aliexpress',
					'ALIEXPRESS.COM' => 'Aliexpress',
				)
			),
			'Универмаги' => array(
				'text' => array(
					'Mothercare' => 'Одежда и обувь',
				)
			),
			'Телекоммуникационные услуги' => array(
				'sum' => array(
					(string)'-3662' => array('oper_group' => 'Коммунальные услуги', 'oper_description' => 'ПТС. %s')
				)
			)
        );
        return $filter;
    }
    
    private function get_my_sdm_filter() {
        $filter = array();
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
            'Прочие' => array(
                'text' => array(
                    'Поступление заработной платы' => 'Зарплата, Пособие',
                    '<<Поступило на счет ГОНЧАРЕНКО ПАВЕЛ ЛЕОНИДОВИЧ>>' => 'Зарплата, Пособие',
                ),
                'starts' => array(
                    'Уплата процентов по депозиту' => $this->my_groups['prc'],
                    'Получение наличных в АТМ' => 'Наличные, Card2Card',
                )
            )
        );
        return $filter;
    }
    
}