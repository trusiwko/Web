<?php defined('SYSPATH') or die('No direct script access.');

class Model_Collect extends Model_Base {

    private $_collect = 'collect';
    private $_collect_main = 'collect_main';
    private $_collect_kukuruza = 'collect_kukuruza';
    private $_collect_imoney = 'collect_imoney';
    private $_collect_tinkoff = 'collect_tinkoff';
	private $_collect_raiffeisen = 'collect_raiffeisen';
	private $_collect_vtb24 = 'collect_vtb24';
    private $_collect_sdm = 'collect_sdm';
    private $_collect_hcb = 'collect_hcb';
    private $_collect_psb = 'collect_psb';
    private $_collect_sber = 'collect_sber';
    
    public function __construct() {
        parent::__construct();
        $this->_collect = $this->prefix() . $this->_collect;
        $this->_collect_main = $this->prefix() . $this->_collect_main;
        $this->_collect_kukuruza = $this->prefix() . $this->_collect_kukuruza;
        $this->_collect_imoney = $this->prefix() . $this->_collect_imoney;
        $this->_collect_tinkoff = $this->prefix() . $this->_collect_tinkoff;
		$this->_collect_raiffeisen = $this->prefix() . $this->_collect_raiffeisen;
		$this->_collect_vtb24 = $this->prefix() . $this->_collect_vtb24;
		$this->_collect_sdm = $this->prefix() . $this->_collect_sdm;
        $this->_collect_hcb = $this->prefix() . $this->_collect_hcb;
        $this->_collect_psb = $this->prefix() . $this->_collect_psb;
        $this->_collect_sber = $this->prefix() . $this->_collect_sber;
    }
    
    public function get_table_name($type) {
        if ($type == 'kukuruza') {
            return $this->_collect_kukuruza;
        } elseif ($type == 'imoney') {
            return $this->_collect_imoney;
        } elseif ($type == 'tinkoff') {
            return $this->_collect_tinkoff;
        } elseif ($type == 'raiffeisen') {
            return $this->_collect_raiffeisen;
        } elseif ($type == 'vtb24') {
            return $this->_collect_vtb24;
        } elseif ($type == 'sdm') {
            return $this->_collect_sdm;
        } elseif ($type == 'hcb') {
            return $this->_collect_hcb;
        } elseif ($type == 'psb') {
            return $this->_collect_psb;
        } elseif ($type == 'sber') {
            return $this->_collect_sber;
        } else {
            die('Type "'.$type.'" is not defined (get_table_name).');
        }
    }
    
    public function get_main_accs($secret, $type) {
        $s = DB::query(Database::SELECT, 'SELECT id, account FROM `' . $this->_collect_main . '`
                        where secret = :secret
                          and type = :type', FALSE)
            ->param(':secret', $secret)
            ->param(':type', $type)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_main($secret, $type, $acc) {
        $s = DB::query(Database::SELECT, 'SELECT id FROM `' . $this->_collect_main . '`
                        where secret = :secret
                          and account = :account 
                          and type = :type', FALSE)
            ->param(':secret', $secret)
            ->param(':type', $type)
            ->param(':account', $acc)
            ->execute($this->_instance)
            ->as_array(); 
        return $s ? $s[0] : 0;
    }
    
    public function add_main($secret, $type, $acc) {
        return $this->upd($this->_collect_main, 0, array('secret' => $secret, 'type' => $type, 'account' => $acc));
    }
    
    public function clear_child($tablename, $pid, $date) {
        return DB::query(Database::DELETE, 'DELETE FROM `' . $tablename . '`
                        where pid = :pid
                          and oper_date >= :date', FALSE)
            ->param(':pid', $pid)
            ->param(':date', $date)
            ->execute($this->_instance);
    }
    
    public function clear_data_filtered($pid) {
        return $this->del($this->_collect, array('pid' => $pid));
    }
    
    public function get_kukuruza($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_id,
                        c.oper_group, 
                        c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum, 
                        c.oper_cashback, 
                        c.oper_mcc
                   FROM `' . $this->_collect_kukuruza . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_tinkoff($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_group, 
                        c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum, 
                        c.oper_cashback, 
                        c.oper_mcc
                   FROM `' . $this->_collect_tinkoff . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_imoney($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum, 
                        c.oper_mcc
                   FROM `' . $this->_collect_imoney . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
	
	public function get_raiffeisen($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
						c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum
                   FROM `' . $this->_collect_raiffeisen . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
	
	public function get_vtb24($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
						c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum
                   FROM `' . $this->_collect_vtb24 . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_sdm($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_id,
						c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum
                   FROM `' . $this->_collect_sdm . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_hcb($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_group,
						c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum
                   FROM `' . $this->_collect_hcb . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_psb($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_id,
                        c.oper_group,
						c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_sum,
                        c.oper_mcc
                   FROM `' . $this->_collect_psb . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_sber($pid) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.oper_id,
						c.oper_currency, 
                        c.oper_date, 
                        date_format(c.oper_date, "%Y-%m") oper_mnth,
                        c.oper_description, 
                        c.oper_group,
                        c.oper_sum
                   FROM `' . $this->_collect_sber . '` c
                  WHERE c.pid = :pid', FALSE)
            ->param(':pid', $pid)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_sber_id($pid, $data) {
        $q = DB::query(Database::SELECT, 'SELECT c.oper_id
                   FROM `' . $this->_collect_sber . '` c
                  WHERE c.pid = :pid
                    and (:oper_sum between c.oper_sum - 0.001 and c.oper_sum + 0.001 
                      or :oper_sum between round(c.oper_sum / 1.01, 2) - 0.001 and round(c.oper_sum / 1.01, 2) + 0.001 )
                    and :oper_date between DATE_ADD(c.oper_date,INTERVAL -6 DAY) and c.oper_date
                    order by c.oper_date desc', FALSE)
            ->param(':pid', $pid)
            ->param(':oper_date', $data['date'])
            ->param(':oper_sum', $data['sum'])
            ->execute($this->_instance);
        $s = $q
            ->as_array(); 
            
        if ($s) {
            $s = $s[0];
            DB::query(Database::UPDATE, 'UPDATE `' . $this->_collect_sber . '` 
                    SET oper_group = :oper_group, oper_description = :oper_description
                  WHERE pid = :pid
                    and oper_id = :oper_id', FALSE)
            ->param(':pid', $pid)
            ->param(':oper_id', $s['oper_id'])
            ->bind(':oper_group', $data['group'])
            ->bind(':oper_description', $data['desc'])
            ->execute($this->_instance);
            return true;
        }
        return false;
    }
    
    public function clear($secret, $type, $acc, $part) {
        $pid = $this->get_main($secret, $type, $acc);
        if ($pid > 0) {
            if ($part == 0) {
                $this->del($this->_collect, array('pid' => $pid));
            }
            $this->upd(
                $this->_collect_main, 
                $pid, 
                array('secret' => $secret) // просто чтобы timestamp обновился
            );
        } else {
            $pid = $this->add_main($secret, $type, $acc);
        }
        return $pid;
    }
    
    public function add_collect($pid, $data)
    {
        return $this->upd(
			$this->_collect, 
			0, 
			$data
		);
    }

    public function get_all($secret, $sidx, $sord, $rows, $page) {
        $a = ($page - 1) * $rows;
        $s = DB::query(Database::SELECT, 'SELECT 
                        c.type,
                        m.oper_group, 
                        m.oper_currency, 
                        m.oper_date, 
                        date_format(m.oper_date, "%Y-%m") oper_mnth,
                        m.oper_description, 
                        m.oper_sum, 
                        m.oper_cashback, 
                        m.oper_mcc
                   FROM `' . $this->_collect . '` m
                   JOIN `' . $this->_collect_main . '` c on c.id = m.pid
                  WHERE c.secret = :secret
                  order by ' . $sidx . ' ' . $sord . "
                  limit " . $a . ', ' . $rows, FALSE)
            ->param(':secret', $secret)
            ->execute($this->_instance)
            ->as_array(); 
        return $s;
    }
    
    public function get_cnt($secret) {
        $s = DB::query(Database::SELECT, 'SELECT 
                        count(1) cnt
                   FROM `' . $this->_collect . '` c
                   JOIN `' . $this->_collect_main . '` m on m.id = c.pid
                  WHERE m.secret = :secret', FALSE)
            ->param(':secret', $secret)
            ->execute($this->_instance)
            ->as_array(); 
        return $s[0]['cnt'];
    }
    
}