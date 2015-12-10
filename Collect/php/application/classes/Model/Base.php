<?php defined('SYSPATH') or die('No direct script access.');

class Model_Base extends Kohana_Model_Database {

    /**
     *
     * @var type Инстанс подключения
     */
    public $_instance = 'prod'; //prod

    public function __construct() {
        parent::__construct($this->_instance);
    }
    
    /**
     * Получаем префикс для таблиц базы данных
     * 
     * @return String
     */
    public function prefix() 
    {
        //return Database::instance()->table_prefix();
        return $this->_db->table_prefix();
    }
    
    /**
     * Выборка одной записи по указанному id
     * 
     * @param integer $id id записи
     * @param string $table
     * @return array массив с данными указанной таблицы
     */
    public function id($table, $id)
    {
    	$id = (int)$id;
        $s = DB::query(Database::SELECT, 'SELECT * FROM `'.$table.'` WHERE id = :id', FALSE)
            ->param(':id', $id)
            ->execute($this->_instance)
            ->as_array();
        return ($s) ? $s[0] : false;
    }

    /**
     * Удаление указанной строки в таблице
     * 
     * @param integer $id id записи
     */
    public function del($table, $data)
    {
        // Получаем массив со списком полей:
        $data_fields = array_keys($data);
        $data_params = array();
        // Получаем массив со списком переменных:
        foreach ($data_fields as $v)
        {
            $data_params[] = $v.' = :'.$v;
        }
        $db = DB::query(Database::DELETE, 'DELETE from '.$table.' where ' . implode(' and ', $data_params));
        foreach ($data as $k => $v)
        {
            $db->param(':' . $k, $v);
        }
		try {
            $db->execute($this->_instance);  
        } catch (Database_Exception $e) {
            return -1 * $e->getCode();
        }
		return 1;
    }
    
    /**
     * Вставка массива в базу данных
     * 
     * @param int $id Уникальный номер записи для обновления
     * @param array $data Массив с данными
     */
    public function upd($table, $id, $data)
    {
        // Получаем массив со списком полей:
        $data_fields = array_keys($data);
        $data_params = array();
        // Вставка данных:
        if ($id == 0) 
        {
            // Получаем массив со списком переменных:
            foreach ($data_fields as $v)
            {
                $data_params[] = ':' . $v;
            }
            // Формируем SQL запрос:
            $sql = 'INSERT INTO ' . $table . ' ('.implode(', ', $data_fields).') 
                                               values('.implode(', ', $data_params).')';
        } else {
            // Получаем массив со списком переменных:
            foreach ($data_fields as $v)
            {
                $data_params[] = $v.' = :'.$v;
            }
            // Формируем SQL запрос:
            $sql = 'UPDATE ' . $table . ' set '.implode(', ', $data_params).' where id = :id';            
        }
        // Заполнение переменных:
        $db = DB::query(($id == 0) ? Database::INSERT : Database::UPDATE, $sql);
        foreach ($data as $k => $v)
        {
            $db->param(':' . $k, $v);
        }
        if ($id <> 0) $db->param (':id', $id);
        // Выполняем запрос:
        try {
            $a = $db->execute($this->_instance);  
        } catch (Database_Exception $e) {
            return -1 * $e->getCode();
        }
        // Возвращаем id новой записи:
        if ($id == 0) return $a[0];
        return true;
    }
  
}