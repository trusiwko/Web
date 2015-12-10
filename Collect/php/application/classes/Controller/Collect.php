<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Collect extends Controller_Base {
    
    public $body = 'collect';
    
    private $mccs = array(
		'Супермаркеты' => array('class' => 'a1', 'mcc' => array('5411')),
		'АЗС' => array('class' => 'a2', 'mcc' => array('5541', '5543', '5983', '5172')),
		'Аптеки' => array('class' => 'a3', 'mcc' => array('5912')),
		'Рестораны' => array('class' => 'a4', 'mcc' => array('5812', '5813', '5814')),
        'Продовольственные магазины' => array('mcc' => array('5499')),
        'Кинотеатры' => array('mcc' => array('7832')),
        'Торговля по каталогам' => array('mcc' => array('5964')),
        'ЖД, Электрички' => array('mcc' => array('4111', '4112')),
        'Одежда и обувь' => array('mcc' => array('5621', '5641', '5661', '5699')),
        'Наличные, Card2Card' => array('mcc' => array('6011', '6012', '6538')),
        'Телекоммуникационные услуги' => array('mcc' => array('4814', '4812')),
        'Авто услуги' => array('mcc' => array('5533', '7538')),
        'Услуги (прочие)' => array('mcc' => array('7399', '7299')),
        'Дом, ремонт' => array('mcc' => array('5722')),
		'Мебель и оборудование' => array('mcc' => array('5712')),
        'Продажа электроники' => array('mcc' => array('5732')),
        'Коммунальные услуги' => array('mcc' => array('4900')),
        'Магазины косметики' => array('mcc' => array('5977')),
        'Благотворительные организации' => array('mcc' => array('8398')),
        'Разные товары' => array('mcc' => array('5399', '5999')),
        'Игры, Магазины игрушек' => array('mcc' => array('5945', '5816')),
        'Страхование' => array('mcc' => array('6300')),
        'Ювелирные изделия и часы' => array('mcc' => array('5944')),
        'Услуги по подписке' => array('mcc' => array('5968')),
        'Универмаги' => array('mcc' => array('5311')),
		'ПО' => array('mcc' => array('5734')),
		'Мед.оборудование' => array('mcc' => array('5047')),
		'Садовые принадлежности' => array('mcc' => array('5261')),
		'Спорттовары' => array('mcc' => array('5941')),
		'Рекламные услуги' => array('mcc' => array('7311')),
        'Фото оборудование' => array('mcc' => array('5946')),
	);
    private $line = array(
        'id' => '',
        'date' => '',
        'group' => '',
        'description' => '',
        'sum' => '',
        'currency' => '',
        'cashback' => '',
        'mcc' => ''
    );
    private $my_groups = array(
        'acc' => 'Переводы',
        'int' => 'Переводы между своими счетами',
        'prc' => 'Проценты, комиссии',
        'csh' => 'Кэшбэк',
        'oth' => 'Прочие'
    );
    
    public function action_ajax() {
        $secret = $this->request->query('secret');
        $sidx = mysql_escape_string($this->request->query('sidx'));
        $sord = mysql_escape_string($this->request->query('sord'));
        $rows = (int)$this->request->query('rows');
        $page = (int)$this->request->query('page');
        $model = new Model_Collect;
        $a = $model->get_all($secret, $sidx, $sord, $rows, $page);
        foreach($a as $k => $v) {
            $a[$k]['mcc_desc'] = Helper_MCC::get_desc($v['oper_mcc']);
        }
        $n = $model->get_cnt($secret);
        die(json_encode(array('page' => $page, 'total' => ceil($n / $rows), 'rows' => $a)));
    }
    
    private function toArr($a) {
        if (gettype($a) == 'object') {
            $b = array();
            foreach ((array)$a as $k => $v) {
                $b[$k] = $this->toArr($v);
            }
        } else {
            $b = $a;
        }
        return $b;
    }
    
    public function action_index() {
        $this->styles[] = 'ui.jqgrid.css';
        $this->styles[] = 'ui.jqgrid-bootstrap.css';
        $this->styles[] = 'ui.jqgrid-bootstrap-ui.css';
        $this->styles[] = 'theme/jquery-ui-custom.css';
        
        if ($this->request->post()) {
            $secret = $this->request->post('secret');
            $link = $this->request->post('link');
        } else {
            $secret = $this->request->query('secret');
            $link = $this->request->query('link');
        }
        $filter = array();
        $err = array();
        if ($link == '') {
            $err[] = 'Не указана ссылка для фильтра.';
        } else {
            if (preg_match('#^https?://.*#', $link)) {
                $filter = @file_get_contents($link);
                if (!$filter) {
                    $err[] = 'Фильтр по ссылке загрузить не удалось.';
                } else {
                    $filter = json_decode($filter);
                    
                    $filter = $this->toArr($filter);

                    $this->add_kukuruza_filter($secret, isset($filter['kukuruza']) ? $filter['kukuruza'] : false);
                    $this->add_imoney_filter($secret, isset($filter['imoney']) ? $filter['imoney'] : false);
                    $this->add_tinkoff_filter($secret, isset($filter['tinkoff']) ? $filter['tinkoff'] : false);
                    $this->add_raiffeisen_filter($secret, isset($filter['raiffeisen']) ? $filter['raiffeisen'] : false);
                    $this->add_vtb24_filter($secret, isset($filter['vtb24']) ? $filter['vtb24'] : false);
                    $this->add_sdm_filter($secret, isset($filter['sdm']) ? $filter['sdm'] : false);

                }
            } else {
                $err[] = 'Ссылка не верна.';
            }
        }
        if ($secret == '') {
            $err[] = 'Не указан секрет.';
        }
        if (count($err) > 0) {
            $this->body->error = implode("<br>", $err);
        }
        $this->body->secret = $secret;
        $this->body->link = $link;
        
    }
    
    public function action_save() 
	{
        header('Access-Control-Allow-Origin:*');
        $secret = $this->request->post('secret');
        if ($secret == '') die('Secret is not defined.');
        
        $type = $this->request->post('type');
        $account = $this->request->post('account');
        $data = $this->request->post('data');
        $part = (int)$this->request->post('part');
        
        if (!is_array($data)) die('No data found.');
        if ($type == 'Кукуруза') {
            $this->add_kukuruza($secret, $account, $part, $data);
        } elseif ($type == 'iMoney') {
            $this->add_imoney($secret, $account, $part, $data);
        } elseif ($type == 'Tinkoff') {
            $this->add_tinkoff($secret, $account, $part, $data);
		} elseif ($type == 'Raiffeisen') {
            $this->add_raiffeisen($secret, $account, $part, $data);
		} elseif ($type == 'VTB24') {
            $this->add_vtb24($secret, $account, $part, $data);
		} elseif ($type == 'SDM') {
            $this->add_sdm($secret, $account, $part, $data);
		} elseif ($type == 'HomeCredit') {
            $this->add_hcb($secret, $account, $part, $data);
		} else {
            die('Type "' . $type . '" is not defined (main).');
        }
    }
    
    private function get_group($mcc, $null = 'Прочие', $nf = 'Остальные') {
        if ($mcc != '') {
            $group = $nf;
            foreach($this->mccs as $gr => $m) {
                if (in_array($mcc, $m['mcc'])) {
                    return $gr;
                }
            }
        } else {
            $group = $null;
        }
        return $group;
    }
    
    private function add_prepare($secret, $ctype, $account, $part, $mindate) {
        
        $model = new Model_Collect;
        
        $main_id = $model->get_main($secret, $ctype, $account);
        if ($main_id == 0) {
            $main_id = $model->add_main($secret, $ctype, $account);
        } elseif ($main_id > 0 && $part == 0) {
            $model->clear_child($model->get_table_name($ctype), $main_id, $mindate);
        }
        if ($main_id < 0) die('Error: ' . $main_id);
        
        return $main_id;
        
    }
    
    private function add_kukuruza($secret, $account, $part, $data) {
        
        $ctype = 'kukuruza';
        
        $model = new Model_Collect;
        
		$mindate = date('Y-m-d', strtotime($data[0]['date']));
		$t = 0;
		if ($part == 0) {
			// Эту дату пропустим (т.к. выписка не по датам, а партиями по 20 штук)
			while (date('Y-m-d', strtotime($data[$t]['date'])) == $mindate) {
				$t++;
			}
			$mindate = date('Y-m-d', strtotime($data[$t]['date']));
		}
		$main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
        for ($k = $t; $k < count($data); $k++) {
			$oper = $data[$k];
            // trim
            $data[$k]['group'] = trim($oper['group']); 
            $data[$k]['curr'] = trim($oper['curr']);
			$data[$k]['desc'] = trim($oper['desc']);
            
            // empty
            if ($data[$k]['group'] == '') 
                $data[$k]['group'] = $this->my_groups['oth'];
            if ($data[$k]['curr'] == '') 
                $data[$k]['curr'] = 'RUR';
            
            // corection
            if ($data[$k]['curr'] == 'RUR') 
                $data[$k]['curr'] = 'RUB';
            
            // format
            $data[$k]['date'] = date('Y-m-d', strtotime($oper['date']));
            $data[$k]['sum'] = (float)$oper['sum'];
            $data[$k]['cb'] = (float)$oper['cb'] / 10;
            
            // bonus https://raw.githubusercontent.com/trusiwko/Web/master/kykyry3a%20MCC.user.js
            if (preg_match('/(.*?)\s\/\sMCC:([\d]{4})\s+/', $oper['desc'], $a)) {
                $data[$k]['mcc'] = $a[2];
                $data[$k]['desc'] = $a[1];
            } else {
                $data[$k]['mcc'] = '';
            }
            
            // to base
            $e = $model->upd($model->get_table_name($ctype), 0, array(
                'pid' => $main_id,
                'oper_id' => $data[$k]['id'],
                'oper_date' => $data[$k]['date'],
                'oper_description' => $data[$k]['desc'],
                'oper_group' => $data[$k]['group'],
                'oper_sum' => $data[$k]['sum'], 
                'oper_currency' => $data[$k]['curr'],
                'oper_cashback' => $data[$k]['cb'],
                'oper_mcc' => $data[$k]['mcc']
            ));
            if ($e < 0) {
                die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($data[$k], true));
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . (count($data) - $t) . ' шт.');
    }
    
    private function add_imoney($secret, $account, $part, $data) {
        
        $ctype = 'imoney';
        
        $model = new Model_Collect;
        
        $mindate = date('Y-m-d', strtotime($data[0]['date']));
        $main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
        foreach ($data as $k => $oper) {
            // format
            $data[$k]['sum'] = (float)str_replace(',', '.', str_replace(' ', '', $oper['sum']));
            $data[$k]['date'] = date('Y-m-d', strtotime($oper['date']));

            // trim
            $data[$k]['desc'] = trim($oper['desc']);            
            
            // empty
            if (preg_match('/ MCC:? ([\d]{4})/', $data[$k]['desc'], $a)) {
                $data[$k]['mcc'] = $a[1];
            } else {
                $data[$k]['mcc'] = '';
            }
            
            // to base
            $e = $model->upd($model->get_table_name($ctype), 0, array(
                'pid' => $main_id,
                'oper_date' => $data[$k]['date'],
                'oper_description' => $data[$k]['desc'],
                'oper_sum' => $data[$k]['sum'], 
                'oper_mcc' => $data[$k]['mcc']
            ));
            if ($e < 0) {
                die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($data[$k], true));
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . count($data) . ' шт.');
    }
    
    private function add_tinkoff($secret, $account, $part, $data) {
        
        $ctype = 'tinkoff';
        
        $model = new Model_Collect;
        
		$delim = ',';
        if ($part == 0 && $data[0] != '') 
            die('Wrong format! First line should be empty');
        $oper = str_getcsv($data[1], $delim, '"');
        if (count($oper) != 12) {
			$delim = ';';
			$oper = str_getcsv($data[1], $delim, '"');
			if (count($oper) != 12) 
				die('Wrong format!');
		}
        $mindate = date('Y-m-d', strtotime($oper[1]));
        $main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
        foreach ($data as $k => $oper) {
            if ($oper != "") {
                $oper = str_getcsv ($oper, $delim, '"');
                // format
                $oper[6] = (float)str_replace(',', '.', $oper[6]);
                $oper[11] = (int)$oper[11];
                $oper[1] = date('Y-m-d', strtotime($oper[1]));
                
                // to base
                $e = $model->upd($model->get_table_name($ctype), 0, array(
                    'pid' => $main_id,
                    'oper_date' => $oper[1],
                    'oper_description' => $oper[10],
                    'oper_group' => $oper[8],
                    'oper_sum' => $oper[6], 
                    'oper_currency' => $oper[7],
                    'oper_cashback' => $oper[11],
                    'oper_mcc' => $oper[9]
                ));
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($data[$k], true));
                }
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . count($data) . ' шт.');
    }
	
	private function add_raiffeisen($secret, $account, $part, $data) {
        
        $ctype = 'raiffeisen';
        
        $model = new Model_Collect;
        
        $oper = str_getcsv($data[0], ';', '"');
        if (count($oper) != 6) {
			die('Wrong format! ('.count($oper).')');
		}
        $mindate = date('Y-m-d', strtotime($oper[0]));
        $main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
		$t = 0;
        foreach ($data as $k => $oper) {
            if ($oper != "") {
				$t++;
                $oper = str_getcsv ($oper, ';', '"');
                // format
                $oper[4] = (float)str_replace(',', '', $oper[4]);
                $oper[0] = date('Y-m-d', strtotime($oper[0]));
				$cur = explode(" ", $oper[3]);
				$cur = $cur[1];
				if ($cur == 'RUR') $cur = 'RUB';
                
                // to base
                $e = $model->upd($model->get_table_name($ctype), 0, array(
                    'pid' => $main_id,
                    'oper_date' => $oper[0],
                    'oper_description' => $oper[2],
                    'oper_sum' => $oper[4], 
                    'oper_currency' => $cur
                ));
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($data[$k], true));
                }
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . $t . ' шт.');
    }
	
	private function add_vtb24($secret, $account, $part, $data) {
        
        $ctype = 'vtb24';
        
        $model = new Model_Collect;
        
		if ($part == 0 && (trim($data[0]) != '' || trim($data[1]) != '')) {
			print_r($data[0].'.');
			print_r($data[1].'.');
			die('Wrong format (2 empty lines are needed).');
		}
		
		if ($part == 0) {
			$oper = str_getcsv($data[3], ';', '"');
			if (count($oper) != 9) {
				die('Wrong format! ('.count($oper).')');
			}
			$mindate = date('Y-m-d', strtotime($oper[1]));
		} else {
			$mindate = '';
		}
        $main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
		$t = 0;
        foreach ($data as $k => $oper) {
            if ($oper != "") {
                $oper = str_getcsv ($oper, ';', '"');
				if (count($oper) == 9 && $oper[0] != 'Номер карты/счета/договора') {
					$t++;
					// format
					$oper[5] = (float)str_replace(',', '.', $oper[5]);
					$oper[1] = date('Y-m-d', strtotime($oper[1]));
					if ($oper[6] == 'RUR') $oper[6] = 'RUB';
					
					// to base
					$e = $model->upd($model->get_table_name($ctype), 0, array(
						'pid' => $main_id,
						'oper_date' => $oper[1],
						'oper_description' => $oper[7],
						'oper_sum' => $oper[5], 
						'oper_currency' => $oper[6]
					));
					if ($e < 0) {
						die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($data[$k], true));
					}
				}
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . $t . ' шт.');
    }
    
    private function add_sdm($secret, $account, $part, $data) {
        
        $ctype = 'sdm';
        
        $model = new Model_Collect;
        
		if ($part == 0) {
			$mindate = date('Y-m-d', strtotime($data[0]['date']));
		} else {
			$mindate = '';
		}
        $main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
		$t = 0;
        foreach ($data as $oper) {
            if ($oper['date'] != 'Итого оборот по счёту') {
                $t++;
                // format
                $oper['sum'] = (float)$oper['sum'];
                $oper['date'] = date('Y-m-d', strtotime($oper['date']));
                if ($oper['curr'] == 'RUR') $oper['curr'] = 'RUB';
                
                // to base
                $e = $model->upd($model->get_table_name($ctype), 0, array(
                    'pid' => $main_id,
                    'oper_id' => $oper['id'],
                    'oper_date' => $oper['date'],
                    'oper_description' => $oper['desc'],
                    'oper_sum' => $oper['sum'], 
                    'oper_currency' => $oper['curr']
                ));
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . $t . ' шт.');
    }
    
    private function hcb_date_to_date($date) {
        $date = str_replace(
            array('Января','Февраля','Марта','Апреля','Мая','Июня','Июля','Августа','Сентября','Октября','Ноября','Декабря'), 
            array('.01.','.02.','.03.','.04.','.05.','.06.','.07.','.08.','.09.','.10.','.11.','.12.'), 
            $date
        );
        $date = str_replace(' ', '', $date);
        return date('Y-m-d', strtotime($date));
    }
    
    private function add_hcb($secret, $account, $part, $data) {
        
        $ctype = 'hcb';
        
        $model = new Model_Collect;
        print_r($data); die();
		if ($part == 0) {
			$mindate = $this->hcb_date_to_date($data[0]['date']);
		} else {
			$mindate = '';
		}
        $main_id = $this->add_prepare($secret, $ctype, $account, $part, $mindate);
        
		$t = 0;
        foreach ($data as $oper) {
            if ($oper['date'] != 'Итого оборот по счёту') {
                $t++;
                // format
                $oper['sum'] = (float)$oper['sum'];
                $oper['date'] = $this->hcb_date_to_date($oper['date']);
                if ($oper['curr'] == 'RUR') $oper['curr'] = 'RUB';
                
                // to base
                $e = $model->upd($model->get_table_name($ctype), 0, array(
                    'pid' => $main_id,
                    'oper_date' => $oper['date'],
                    'oper_description' => $oper['desc'],
                    'oper_sum' => $oper['sum'], 
                    'oper_currency' => $oper['curr'],
                    'oper_group' => $oper['group']
                ));
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }

        die(($part + 1) . '. Данные сохранены: ' . $t . ' шт.');
    }
    
    private function get_tinkoff_filter() {
        $filter = array();
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
            'Другое' => array(
                'text' => array(
                    'Плата за обслуживание' => $this->my_groups['prc'], 
					'Плата за обслуживание.' => $this->my_groups['prc'], 
                    'Плата за предоставление услуги SMS-банк' => $this->my_groups['prc'], 
                    'Плата за Программу страховой защиты' => $this->my_groups['prc'],
                    'Пополнение. Тинькофф Банк. Компенсация покупок по программе лояльности' => $this->my_groups['prc'],
                    'Комиссия за пополнение.' => $this->my_groups['prc'],
                    'Проценты на остаток по счету' => $this->my_groups['prc'], 
                    'Вознаграждение за операции покупок' => $this->my_groups['prc'],
                    'Пополнение. Тинькофф Банк. Зачисление денежных средств по обращению' => $this->my_groups['prc'],
                    'Пополнение. Элекснет' => 'Наличные, Card2Card', 
                    'Пополнение. Евросеть' => 'Наличные, Card2Card',
                    'Пополнение. КиберПлат' => 'Наличные, Card2Card',
					'Пополнение. Comepay' => 'Наличные, Card2Card',
                    'Пополнение. "Золотая Корона"' => 'Наличные, Card2Card',
                    'Перевод для закрытия накопительного счета' => $this->my_groups['int'],
                ),
                'starts' => array(
                    'Изъятие вклада при закрытии' => $this->my_groups['int'],
                    'На вклад «' => $this->my_groups['int'],
                    'На счёт «' => $this->my_groups['int'],
					'Внутренний перевод с договора' => $this->my_groups['int'],
                )
            ),
            'Наличные, c2c' => array(
                '*' => 'Наличные, Card2Card'
            ),
            'Мобильные/иб' => array(
                '*' => 'Телекоммуникационные услуги'
            ),
            'Переводы/иб' => array(
                'text' => array(
                    'Внутрибанковский перевод' => $this->my_groups['int'],
                ),
                'starts' => array(
                    'На вклад «' => $this->my_groups['int'],
                    'На счёт «' => $this->my_groups['int'],
                ),
                '*' => $this->my_groups['acc']
            ),
            'Переводы' => array(
                'text' => array(
                    'Перевод c карты другого банка' => 'Наличные, Card2Card'
                )
            ),
            'Пополнение вклада' => array(
                '*' => $this->my_groups['int']
            ),
            'Вклад' => array(
                '*' => $this->my_groups['int']
            ),
            'Проценты' => array(
                '*' => $this->my_groups['prc']
            ),
            'Компенсация' => array(
                '*' => $this->my_groups['prc']
            ),
        );
        
        return $filter;
    }
	
    private function get_imoney_filter() {
        $filter = array();
        $filter['groups'] = array();
        $filter['groups_desc'] = array(
            'Прочие' => array(
                'text' => array(),
                'starts' => array(
                    'Перевод (зачисление) средств во вклад' => $this->my_groups['int'],
                    'Перевод (списание) средств со вклада' => $this->my_groups['int'],
                    'Начисление процентов по вкладу' => $this->my_groups['prc'],
                    'Комиссия за обслуживание' => $this->my_groups['prc']
                )
            ),
            '*' => array(
                'starts' => array(
                    'Возврат средств по программе CashBack' => $this->my_groups['csh'],
                    'Авторизация. Кэшбэк-зачисление' => $this->my_groups['csh']
                )
            )
        );
        return $filter;
    }
    
    private function get_kukuruza_filter() {
        $filter = array();
        $filter['groups'] = array(
            'Бакалейные магазины, супермаркеты' => 'Супермаркеты',
            'Рестораны быстрого питания' => 'Рестораны',
            'Готовая женская одежда' => 'Одежда и обувь',
            'Обувные магазины' => 'Одежда и обувь',
            'Одежда для детей и подростков' => 'Одежда и обувь',
            'Пассажирские железные дороги' => 'ЖД',
            'Сотовая связь' => 'Телекоммуникационные услуги',
        );
        $filter['groups_desc'] = array(
            'Прочие' => array(
                'text' => array(
                    'Покупка с картой «Кукуруза»' => $this->my_groups['csh'],
                    'Обмен баллов на скидку' => $this->my_groups['csh'] . '-',
                ),
                'starts' => array(
                    'Пополнение с карты MASTERCARD' => 'Наличные, Card2Card'
                )
            ), 
            'Скрытые' => array(
                'starts' => array(
                    'Сотовый' => 'Телекоммуникационные услуги'
                )
            )
        );
        return $filter;
    }
    
    private function get_group_filter_main($filter, $oper) {
        $a = $this->get_group_fltr($filter, $oper);
        if (is_array($a)) {
            if (isset($a['oper_group']))
                $oper['oper_group'] = $a['oper_group'];
            if (isset($a['oper_description'])) 
                $oper['oper_description'] = sprintf($a['oper_description'], $oper['oper_description']);
        } else {
            $oper['oper_group'] = $a;
        }
        return $oper;
    }
    
    private function get_group_fltr($filter, $oper) {
        $oper_group = $oper['oper_group'];
        $oper_description = $oper['oper_description'];
        $oper_sum = $oper['oper_sum'];
        if (isset($filter['groups'][$oper_group])) 
            return $filter['groups'][$oper_group];
        
        foreach($filter['groups_desc'] as $group_name => $desc) {
            
            if ($oper_group == $group_name || $group_name == '*') {
                foreach($desc as $type => $a) {
                    if ($type == 'text') {
                        if (isset($a[$oper_description]))
                            return $a[$oper_description];
                    } elseif ($type == 'starts') {
                        foreach ($a as $b => $c) {
                            if (strpos($oper_description, $b) === 0) 
                                return $c;
                        }
                    } elseif ($type == '*') {
                        return $a;
                    } elseif ($type == 'regexp') {
						foreach ($a as $b => $c) {
                            if (preg_match('/' . $b . '/', $oper_description)) 
                                return $c;
                        }
					} elseif ($type == 'sum') {
						foreach ($a as $b => $c) {
                            if ((float)$b == $oper_sum)
                                return $c;
                        }
					} else {
                        die('Type "'.$type.'" is not defined');
                    }
                }
            }
        }
        
        return $oper_group;
    }
    
    private function add_kukuruza_filter($secret, $filter) {
        
        $model = new Model_Collect;
        
        $sm = $model->get_main_accs($secret, 'kukuruza');
        foreach ($sm as $main) {
            // clear
            $model->clear_data_filtered($main['id']);
            // all data
            $data = $model->get_kukuruza($main['id']);
            foreach ($data as $k => $oper) {

                $oper['oper_group'] = $this->get_group($data[$k]['oper_mcc'], $oper['oper_group']);
                $oper = $this->get_group_filter_main($this->get_kukuruza_filter(), $oper);
                if ($filter) 
                    $oper = $this->get_group_filter_main($filter, $oper);
                $oper['pid'] = $main['id'];
                
                unset($oper['oper_mnth']);
                $e = $model->add_collect($main['id'], $oper);
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }
        
    }
    
    private function add_imoney_filter($secret, $filter) {
        
        $model = new Model_Collect;
        
        $sm = $model->get_main_accs($secret, 'imoney');
        foreach ($sm as $main) {
            // clear
            $model->clear_data_filtered($main['id']);
            // all data
            $data = $model->get_imoney($main['id']);
            foreach ($data as $k => $oper) {

                $oper['oper_group'] = $this->get_group($data[$k]['oper_mcc']);
                $oper = $this->get_group_filter_main($this->get_imoney_filter(), $oper);
                if ($filter)
                    $oper = $this->get_group_filter_main($filter, $oper);
                $oper['pid'] = $main['id'];
                $oper['oper_id'] = md5(rand() . time() . $oper['oper_date']);
                $oper['oper_currency'] = 'RUB';
                
                if ($oper['oper_group'] == $this->my_groups['csh']) {
                    $oper['oper_cashback'] = $oper['oper_sum'];
                    $oper['oper_sum'] = 0;
                }
                
                unset($oper['oper_mnth']);
                
                $e = $model->add_collect($main['id'], $oper);
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }
        
    }
    
    private function add_tinkoff_filter($secret, $filter) {
        
        $model = new Model_Collect;
        
        $sm = $model->get_main_accs($secret, 'tinkoff');
        foreach ($sm as $main) {
            // clear
            $model->clear_data_filtered($main['id']);
            // all data
            $data = $model->get_tinkoff($main['id']);
            foreach ($data as $k => $oper) {

                $oper['oper_group'] = $this->get_group($data[$k]['oper_mcc'], $oper['oper_group']);
                $oper = $this->get_group_filter_main($this->get_tinkoff_filter(), $oper);
                if ($filter) 
                    $oper = $this->get_group_filter_main($filter, $oper);
                $oper['pid'] = $main['id'];
                $oper['oper_id'] = md5(rand() . time() . $oper['oper_date']);
                
                unset($oper['oper_mnth']);
                $e = $model->add_collect($main['id'], $oper);
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }
        
    }
	
	private function add_raiffeisen_filter($secret, $filter) {
        
        $model = new Model_Collect;
        
        $sm = $model->get_main_accs($secret, 'raiffeisen');
        foreach ($sm as $main) {
            // clear
            $model->clear_data_filtered($main['id']);
            // all data
            $data = $model->get_raiffeisen($main['id']);
            foreach ($data as $k => $oper) {

                $oper['oper_group'] = 'Прочие';
                if ($filter) 
                    $oper = $this->get_group_filter_main($filter, $oper);
                $oper['pid'] = $main['id'];
                $oper['oper_id'] = md5(rand() . time() . $oper['oper_date']);
                
                unset($oper['oper_mnth']);
                $e = $model->add_collect($main['id'], $oper);
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }
        
    }
    
	private function add_vtb24_filter($secret, $filter) {
        
        $model = new Model_Collect;
        
        $sm = $model->get_main_accs($secret, 'vtb24');
        foreach ($sm as $main) {
            // clear
            $model->clear_data_filtered($main['id']);
            // all data
            $data = $model->get_vtb24($main['id']);
            foreach ($data as $k => $oper) {

                $oper['oper_group'] = 'Прочие';
                if ($filter)
                    $oper = $this->get_group_filter_main($filter, $oper);
                $oper['pid'] = $main['id'];
                $oper['oper_id'] = md5(rand() . time() . $oper['oper_date']);
                
                unset($oper['oper_mnth']);
                $e = $model->add_collect($main['id'], $oper);
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }
        
    }
    
    private function add_sdm_filter($secret, $filter) {
        
        $model = new Model_Collect;
        
        $sm = $model->get_main_accs($secret, 'sdm');
        foreach ($sm as $main) {
            // clear
            $model->clear_data_filtered($main['id']);
            // all data
            $data = $model->get_sdm($main['id']);
            foreach ($data as $k => $oper) {

                $oper['oper_group'] = 'Прочие';
                if ($filter)
                    $oper = $this->get_group_filter_main($filter, $oper);
                $oper['pid'] = $main['id'];
                
                unset($oper['oper_mnth']);
                $e = $model->add_collect($main['id'], $oper);
                if ($e < 0) {
                    die('Ошибка: ' . $e . ' - ' . mysql_error() .  ': ' . print_r($oper, true));
                }
            }
        }
        
    }
	
}