<?php
/*SQL*/

class sqlpostgre
{
    var $hlink;
    var $sql_query;
    var $sql_err;
    var $sql_res;

    /**Если тру - то проверка таблиц и папок*/

	    function __construct(&$SQL_CFG)
    {
        global $_CFG;
        $this->SQL_CFG = &$SQL_CFG;
        $this->_iFlag = false;
        $this->ready = false;
        $this->logFile = false;
        if (isset($_CFG['log']) and (int)$_CFG['log'] and WEP) {
            $this->logFile = array();
        }
        if (function_exists('pg_connect')) $this->ready = $this->_connect();
        else {
            $_CFG["site"]["work_text"] = '<err>PostgreSQL - ' . static_main::m('Can`t connect to SQL server') . '</err>';
            static_main::downSite();
        }
    }

    function __destruct()
    {
        global $_CFG;
		//$this->sql_close();
        if ($this->logFile !== false and count($this->logFile)) {
            file_put_contents($_CFG['_PATH']['log'] . '_' . date('Y-m-d_H-i-s') . '.log', implode("\n", $this->logFile));
        }
    }

    private function _connect()
    {
        return $this->sql_connect();
    }

    function sql_connect()
    {
        global $_CFG;

        $temp = $_CFG['wep']['catch_bug'];
        $_CFG['wep']['catch_bug'] = 0;

        $this->hlink = pg_connect(
            'host=' . $this->SQL_CFG['host'] . ' port=5432 dbname=' . $this->SQL_CFG['database'] . ' user=' . $this->SQL_CFG['login'] . ' password=' . $this->SQL_CFG['password']
        );
        if (!$this->hlink) {
            $_CFG["site"]["work_text"] = '<err>PostgreSQL - ' . static_main::m('Can`t connect to SQL server') . '</err>';
            static_main::downSite();
        }

        $_CFG['wep']['catch_bug'] = $temp;

        pg_query($this->hlink, "SET time_zone = '" . date_default_timezone_get() . "'");
        return true;
    }

    private function _connectDB()
    {
        global $_CFG;
        if (isset($this->SQL_CFG['setnames']) and $this->SQL_CFG['setnames']) pg_query($this->hlink, 'SET NAMES ' . $this->SQL_CFG['setnames']);
        if (isset($this->SQL_CFG['database']) and $this->SQL_CFG['database']) {
            if (!$this->sql_selectDB($this->SQL_CFG)) {
                if ($this->sql_createDB($this->SQL_CFG)) {
                    if (!$this->sql_selectDB($this->SQL_CFG)) {
                        $_CFG["site"]["work_text"] = '<err>' . static_main::m('Cant`t connect to database') . '</err>';
                        static_main::downSite();
                    }
                } else {
                    $_CFG["site"]["work_text"] = '<err>' . static_main::m('Permission denied to create database') . '</err>';
                    static_main::downSite();
                }
            }
        }
        return true;
    }

    private function sql_selectDB($CFG)
    {
        return mysqli_select_db($this->hlink, $CFG['database']);
    }

    private function sql_createDB($CFG)
    {
        $q = 'create database `' . $CFG['database'] . '`';
        if ($CFG['setnames']) $q .= ' character set ' . $CFG['setnames'] . ' collate ' . $CFG['setnames'] . '_general_ci';
        return pg_query($this->hlink, $q);
    }

    private function sql_createUser($CFG)
    {
        return pg_query(
            $this->hlink,
            'create user \'' . $CFG['login'] . '\'@\'' . $CFG['host'] . '\' identified by \'' . $CFG['password'] . '\''
        );
    }

    private function sql_createGrant($CFG)
    {
        return pg_query(
            $this->hlink,
            'grant all privileges on `' . $CFG['database'] . '`.* to \'' . $CFG['login'] . '\'@\'' . $CFG['host'] . '\''
        );
    }

    private function sql_close()
    {
        if ($this->hlink) pg_close($this->hlink);
    }

    public function sql_install($CFG)
    {
        if (!$this->ready) return array(false, static_main::m('SQL not ready'));
        if (!$this->sql_selectDB($CFG))
        if (!$this->sql_createDB($CFG)) return array(false, static_main::m('SQL can`t create database'));
        if (!count($this->q('Select * from mysql.user where user=\'' . $CFG['login'] . '\' and Host=\'' . $CFG['host'] . '\'')))
        if (!$this->sql_createUser($CFG)) return array(false, static_main::m('SQL can`t create users'));
        if (!count(
            $this->q(
                'Select * from mysql.db where user=\'' . $CFG['login'] . '\' and Host=\'' . $CFG['host'] . '\' and Db=\'' . $CFG['database'] . '\''
            )
        ))
        if (!$this->sql_createGrant($CFG)) return array(false, static_main::m('SQL can`t set grant'));
        return array(true, 'OK');
    }

    /*****************************/

	    public function query($sql)
    {
        if (!$this->ready) return false;
        return new pquery($this, $sql);
    }

    public function execSQL($sql)
    { // Синоним
        return $this->query($sql);
    }

    public function lastId()
    {
        if (!$this->ready) return false;
        return $this->lastId;
    }

    public function escape($val)
    {
        if (!$this->ready) return '';
        return pg_escape_string($val);
    }

    public function SqlEsc($val)
    { // Синоним
        return $this->escape($val);
    }

    public function q($sql, $key = false, $type = 0)
    {
        if (!$this->ready) return false;
        $result = $this->query($sql);
        $data = array();
        if (!$result->err) {
            if ($key !== false) {
                while ($r = $result->fetch($type)) $data[$r[$key]] = $r;
            } else {
                while ($r = $result->fetch($type)) $data[] = $r;
            }
        }
        return $data;
    }

    /******************************/
	    /******************************/
	    /******************************/

	    public function _tableCreate(&$MODUL)
    {
        $fld = array();
        if (count($MODUL->fields))
        foreach ($MODUL->fields as $key => $param) list($fld[], $mess) = $MODUL->SQL->_fldformer($key, $param);
        if (count($MODUL->attaches))
        foreach ($MODUL->attaches as $key => $param) list($fld[], $mess) = $MODUL->SQL->_fldformer($key, $MODUL->attprm);
        /*foreach($MODUL->memos as $key => $param)
		  $fld[]= $MODUL->SQL->_fldformer($key, $MODUL->mmoprm);
		 */
		        $fld[] = 'PRIMARY KEY(id)';

        if (isset($MODUL->unique_fields) and count($MODUL->unique_fields)) {
            foreach ($MODUL->unique_fields as $k => $r) {
                if (is_array($r)) $r = implode('`,`', $r);
                $fld[] = 'UNIQUE KEY `' . $k . '` (`' . $r . '`)';
            }
        }
        if (isset($MODUL->index_fields) and count($MODUL->index_fields)) {
            foreach ($MODUL->index_fields as $k => $r) {
                if (!isset($MODUL->unique_fields[$k])) {
                    if (is_array($r)) $r = implode(',', $r);
                    $fld[] = 'KEY `' . $k . '` (`' . $r . '`)';
                }
            }
        }
        $query = 'CREATE TABLE `' . $MODUL->tablename . '` (' . implode(',', $fld) . ') ENGINE=' . $MODUL->SQL_CFG['engine'] . ' DEFAULT CHARSET=' . $MODUL->SQL_CFG['setnames'] . ' COMMENT = "' . $MODUL->ver . '"';
		// to execute query
        $result = $this->query($query);
        if ($result->err) {
            return false;
        }
        return true;
    }

    public function _tableDelete($tablename)
    {
        return $this->query('DROP TABLE `' . $tablename . '`');
    }

    public function _tableClear($tablename)
    {
        return $this->query('truncate TABLE `' . $tablename . '`');
    }

    public function _tableExists($tablename)
    {
        $result = $this->query('SHOW TABLES LIKE `' . $tablename . '`');
        if (!$result->err) {
            if ($result->num_rows()) return true;
            else return false;
        }
        return NULL;
    }

    public function _tableKeys(&$MODUL)
    {
        $primary = $uniqlist = $indexlist = array();
        $result = $this->query('SHOW KEYS FROM `' . $MODUL->tablename . '`');
        while ($data = $result->fetch_row()) {
            if ($data[2] == 'PRIMARY') //только 1 примарикей
            $primary = $data[4];
            elseif (!$data[1]) //!NON_unique
            $uniqlist[$data[2]][$data[4]] = $data[4];
            else $indexlist[$data[2]][$data[4]] = $data[4];
        }
        return array($primary, $uniqlist, $indexlist);
    }

    /******************************/

	    public function _info()
    {
        return $this->q('show variables', 0, MYSQLI_NUM);
    }

    public function _proc()
    {
        return $this->q('show full processlist');
    }

    public function _status()
    {
        return $this->q('show status');
    }

    public function _getSQLTableInfo($tablename)
    {
        if (!$this->ready) return false;
        $data = array();
        $result = $this->query('SHOW FULL FIELDS FROM `' . $tablename . '`');
        while ($COLUMNS = $result->fetch()) {
            $fldname = $COLUMNS['Field']; //mb_strtolower(
            $data[$fldname] = $COLUMNS;
        }
        $result = $this->query('SHOW CREATE TABLE `' . $tablename . '`');
        if ($row = $result->fetch()) {
            $creat_table = $row['Create Table'];
            $creat_table = explode("\n", $creat_table);
            array_shift($creat_table);
            $info = array_pop($creat_table);
            foreach ($creat_table as $r) {
                $r = trim($r, " ,\t\r");
                if (substr($r, 0, 1) == '`') {
                    $pos = strpos($r, '`', 1);
                    $fldname = substr($r, 1, ($pos - 1));
                    $data[$fldname]['create'] = $r;
                }
            }
        }
        return $data;
    }

    /*****************************/

	    var $alias_types = array(
        'bool' => 'tinyint(1)',
    );
	// типы полей, число - это значение, которое запишется в базу по умолчанию, если не указывать ширину явно
	// false - означает, что для данного типа поля в mysql ширина не указывается
    var $types_width = array(
        'tinyblob' => false,
        'tinytext' => false,
        'blob' => false,
        'text' => false,
        'mediumblob' => false,
        'mediumtext' => false,
        'longblob' => false,
        'longtext' => false,
        'date' => false,
        'datetime' => false,
        'timestamp' => false,
        'time' => false,
        'float' => '8,2',
        'double' => false,
        'precision' => false,
        'real' => false,
        'int' => 11,
        'tinyint' => 4,
        'varchar' => 255,
    );
	// типы полей, в которых нет атрибута default
    var $types_without_default = array(
        'tinytext' => true,
        'text' => true,
        'mediumtext' => true,
        'longtext' => true,
    );

    public function _fldformer($key, $param)
    {
        $mess = array();
        if (isset($param['attr']) and stristr($param['attr'], 'default')) {
            $mess[] = array(
                'alert',
                'Пар-р default прописан в ключе attr. Для корректной работы необходимо прописать его в отдельном элементе с ключом `default`'
            );
        }
        if ($param['type'] == 'text' && isset($param['attr']) && stripos($param['attr'], 'NULL') !== false && stripos($param['attr'], 'NOT NULL') === false) {
            $mess[] = array('alert', 'Атрибут `NULL` для поля `' . $key . '` указывать не обязательно.');
            unset($param['attr']);
        }
        if (isset($param['default']) && isset($this->types_without_default[$param['type']])) {
            $mess[] = array('alert', 'Параметр `default` для поля `' . $key . '` указывать не обязательно.');
            unset($param['default']);
        }
        if (isset($param['width']) and isset($this->types_width[$param['type']]) && $this->types_width[$param['type']] === false) {
            $mess[] = array('alert', 'Параметр `width` для поля `' . $key . '` указывать не обязательно.');
            unset($param['width']);
        } elseif (!isset($param['width']) and isset($this->types_width[$param['type']]) && $this->types_width[$param['type']] !== false) {
            $mess[] = array(
                'alert', 'Параметр `width` для поля `' . $key . '` необходим. По умолчанию будет установленно значение `' . $this->types_width[$param['type']] . '`'
            );
            $param['width'] = $this->types_width[$param['type']];
        }

        if (isset($this->alias_types[$param['type']])) $param['type'] = $this->alias_types[$param['type']];

        $m = '`' . strtolower($key) . '` ' . $param['type'];
        if ($param['type'] == 'timestamp' and (!isset($param['attr']) or ($param['attr'] == 'NOT NULL' and !isset($param['default'])))) $m .= ' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
        elseif ($param['type'] == 'timestamp') {
            $m .= ' ' . $param['attr'];
            if (isset($param['default'])) $m .= ' DEFAULT \'' . $param['default'] . '\'';
        } else {
            if (isset($param['width']) && is_array($param['width'])) {
                if ($param['type'] == 'enum') $m .= '("' . implode('","', array_keys($param['width'])) . '")';
            } elseif (isset($param['width']) && $param['width'] != '') $m .= '(' . $param['width'] . ')';
            if (isset($param['attr'])) $m .= ' ' . $param['attr'];
            elseif (isset($param['default']) and strpos($param['default'], 'NULL') === false and $param['type'] != 'text') $m .= ' NOT NULL';
            if (isset($param['default'])) $m .= ' DEFAULT \'' . $param['default'] . '\'';
            if (!isset($param['default']) and $param['type'] != 'text' and !isset($param['attr'])) $m .= ' DEFAULT NULL';
        }
        return array($m, $mess);
    }

    private function fError($err)
    {
        $this->sql_err[] = $err;
    }

    private function err($mess)
    {
        global $_CFG;
        $mess = static_main::m($mess);
        if (!$_CFG['wep']['debugmode']) {
            die($mess);
        } else {
			//static_main::log('error',$mess);
        }
        return false;
    }

    function longLog($ttt, $sql, $isError = false)
    {
        global $_CFG;
        $_CFG['logs']['sqlTime'] += $ttt;
        if (isset($this->SQL_CFG['longquery']) and $this->SQL_CFG['longquery'] > 0 and $ttt > $this->SQL_CFG['longquery']) {
            trigger_error('LONG QUERY [' . $ttt . ' sec. - мах ' . $this->SQL_CFG['longquery'] . '] (' . $sql . ')', E_USER_WARNING);
            if ($this->logFile !== false) $this->logFile[] = '[' . date('Y-m-d H:i:s') . '] LONG QUERY [' . $ttt . ' sec.] (' . $sql . ')';
        }

        if (canShowAllInfo() > 1) {
            $_CFG['logs']['sql'][] = array($sql, $ttt, $isError);
        } elseif (isBackend() or canShowAllInfo()) $_CFG['logs']['sql'][] = true;
    }
}

class pquery
{
    var $handle;
    var $id;
    var $affected;
    var $err;

    function __construct(&$db, $sql)
    {
        global $_CFG;
        $ttt = getmicrotime();
        $this->handle = pg_query($db->hlink, $sql);
        $this->db = &$db;
        $this->query = $db->query = $sql;
        $this->err = mysqli_error($db->hlink);
        if ($this->err != '') {
            if ($db->logFile !== false) $this->logFile[] = 'ERORR: ' . $this->err . ' (' . $sql . ')';
            trigger_error($this->err .= ' (' . $sql . ');', E_USER_WARNING);
            $this->errno = mysqli_errno($db->hlink);
			//$db->fError($this->err);
        } else {
            $db->longLog((getmicrotime() - $ttt), $sql);
            if (stripos($sql, 'insert into') !== false) $db->lastId = $this->sql_id();
        }
    }

    function lastId()
    { // ID Последний добавленой записи
        return pg_last_oid($this->handle);
    }

    function destroy()
    { // очистка памяти
        return pg_free_result($this->handle);
    }

    function num_rows()
    { // Кол-во полученных записей
        return pg_fetch_row($this->handle);
    }


	// type MYSQLI_ASSOC | MYSQLI_BOTH | MYSQLI_NUM

    function fetch($type = 0)
    { // Выдает асоциативный и нумеровнаый масив
        if ($type == 0) return $this->fetch_assoc($this->handle);
        elseif ($type == 1) return $this->fetch_row($this->handle);
        elseif ($type == 2) return $this->fetch_array($this->handle);
        else return $this->fetch_object($this->handle);
    }

    function fetch_assoc()
    { // Выдает асоциативный масив
        return pg_fetch_assoc($this->handle);
    }

    function fetch_row()
    { // Выдает нумеровнаый масив
        return pg_fetch_row($this->handle);
    }

    function fetch_array()
    { // Выдает асоциативный и нумеровнаый масив
        return pg_fetch_array($this->handle, null, PGSQL_BOTH);
    }

    function fetch_object()
    { // Выдает данные в виде обекта
        return pg_fetch_object($this->handle);
    }

    function affected_rows()
    { // Возвращает кол-во затронутых записей в последней оперции
        return pg_affected_rows($this->db->hlink);
    }

    function sql_seek($offset)
    { // ПЕРЕмещает указатель
        return pg_result_seek($this->handle, $offset);
    }

	// ТЕСТОВЫЕ

    function sql_result($row)
    {
        return pg_fetch_result($this->handle, $row);
    }
}
