<?php
/*SQL*/

	class sql {
		var $hlink;
		var $sql_query;
		var $sql_err;
		var $sql_res;
		/**Если тру - то проверка таблиц и папок*/

		function __construct(&$CFG_SQL) {
			global $_CFG;
			$this->CFG_SQL = &$CFG_SQL;
			$this->_iFlag= false;
			$this->ready = false;
			$this->logFile = false;
			if(isset($_CFG['log']) and (int)$_CFG['log'] and $_CFG['_PATH']['wep']) {
				$this->logFile = array();
			}

			$this->ready = $this->_connect();
		}

		function __destruct() {
			//$this->sql_close();
			if($this->logFile!==false and count($this->logFile)) {
				file_put_contents($_CFG['_PATH']['log'].'_'.date('Y-m-d_H-i-s').'.log',implode("\n",$this->logFile));
			}
		}

		private function _connect() {
			if($this->sql_connect()) {
				return $this->_connectDB();
			}
			return false;
		}

		private function sql_connect() {
			$this->hlink = @mysqli_connect($this->CFG_SQL['host'], $this->CFG_SQL['login'], $this->CFG_SQL['password']);
			if(!$this->hlink)
				return $this->err('SQL connect error');
			mysqli_query($this->hlink,"SET time_zone = '".date_default_timezone_get()."'");
			return true;
		}

		private function _connectDB() {
			if(isset($this->CFG_SQL['setnames']) and $this->CFG_SQL['setnames'])
				mysqli_query($this->hlink, 'SET NAMES '.$this->CFG_SQL['setnames']);
			if(isset($this->CFG_SQL['database']) and $this->CFG_SQL['database']) {
				if(!$this->sql_selectDB($this->CFG_SQL)) {
					if($this->sql_createDB($this->CFG_SQL)) {
						if(!$this->sql_selectDB($this->CFG_SQL))
							return $this->err('SQL can`t connect to database');
					}
					else 
						return $this->err('SQL can`t create database');
				}
			}
			return true;
		}

		private function sql_selectDB($CFG) {
			return mysqli_select_db($this->hlink,$CFG['database']);
		}

		private function sql_createDB($CFG) {
			$q = 'create database `'.$CFG['database'].'`';
			if($CFG['setnames'])
				$q .= ' character set '.$CFG['setnames'].' collate '.$CFG['setnames'].'_general_ci';
			return mysqli_query($this->hlink, $q);
		}

		private function sql_createUser($CFG) {
			return mysqli_query($this->hlink, 'create user \''.$CFG['login'].'\'@\''.$CFG['host'].'\' identified by \''.$CFG['password'].'\'');
		}

		private function sql_createGrant($CFG) {
			return mysqli_query($this->hlink, 'grant all privileges on `'.$CFG['database'].'`.* to \''.$CFG['login'].'\'@\''.$CFG['host'].'\'');
		}

		public function sql_id() {
			if(!$this->ready) return false;
			return mysqli_insert_id($this->hlink);
		}


		public function SqlEsc($val) {
			if(!$this->ready) return '';
			return mysqli_real_escape_string($this->hlink,$val);
		}

		private function sql_close() {
			if($this->hlink)
				mysqli_close($this->hlink);
		}

/*****************************/

		public function execSQL($sql,$unbuffered=0) {
			if(!$this->ready) return false;
			return new query($this, $sql, $unbuffered);
		}

		public function q($sql,$key=false,$type = MYSQLI_ASSOC) {
			if(!$this->ready) return false;
			$result = new query($this, $sql,0);
			$data = array();
			if (!$result->err) {
				if($key!==false) {
					while ($r = $result->fetch_array($type))
						$data[$r[$key]] = $r;
				} 
				else {
					while ($r = $result->fetch_array($type))
						$data[] = $r;
				}
			}
			return $data;
		}

/*****************************/

		public function _info() {
			return $this->q('show variables',0,MYSQLI_NUM);
		}

		public function _proc() {
			return $this->q('show full processlist');
		}

		public function _status() {
			return $this->q('show status');
		}

		private function fError($err) {
			$this->sql_err[] = $err;
		}

		public function sql_install($CFG) {
			if(!$this->ready)
				return array(false,static_main::m('SQL not ready'));
			if(!$this->sql_selectDB($CFG))
				if(!$this->sql_createDB($CFG))
					return array(false,static_main::m('SQL can`t create database'));
			if(!count($this->q('Select * from mysql.user where user=\''.$CFG['login'].'\' and Host=\''.$CFG['host'].'\'')))
				if(!$this->sql_createUser($CFG))
					return array(false,static_main::m('SQL can`t create users'));
			if(!count($this->q('Select * from mysql.db where user=\''.$CFG['login'].'\' and Host=\''.$CFG['host'].'\' and Db=\''.$CFG['database'].'\'')))
				if(!$this->sql_createGrant($CFG))
					return array(false,static_main::m('SQL can`t set grant'));
			return array(true,'OK');
		}

		private function err($mess) {
			global $_CFG;
			$mess = static_main::m($mess);
			if(!$_CFG['wep']['debugmode']){
				die($mess);
			}
			else {
				//static_main::log('error',$mess);
			}
			return false;
		}

		var $alias_types = array(
				'bool'=>'tinyint(1)',
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

		public function _fldformer($key, $param) {
			$mess = array();
			if (isset($param['attr']) and stristr($param['attr'], 'default')) {
				$mess[] = array( 'alert', 'Пар-р default прописан в ключе attr. Для корректной работы необходимо прописать его в отдельном элементе с ключом `default`' );
			}
			if ($param['type']=='text' && isset($param['attr']) && stripos($param['attr'],'NULL')!==false && stripos($param['attr'],'NOT NULL')===false) {
				$mess[] = array( 'alert', 'Атрибут `NULL` для поля `'.$key.'` указывать не обязательно.');
				unset($param['attr']);
			}
			if (isset($param['default']) &&	isset($this->types_without_default[$param['type']])) {
				$mess[] = array( 'alert', 'Параметр `default` для поля `'.$key.'` указывать не обязательно.');
				unset($param['default']);
			}
			if (isset($param['width']) and isset($this->types_width[$param['type']]) && $this->types_width[$param['type']] === false) {
				$mess[] = array( 'alert', 'Параметр `width` для поля `'.$key.'` указывать не обязательно.');
				unset($param['width']);
			}
			elseif(!isset($param['width']) and isset($this->types_width[$param['type']]) && $this->types_width[$param['type']] !== false) {
				$mess[] = array( 'alert', 'Параметр `width` для поля `'.$key.'` необходим. По умолчанию будет установленно значение `'.$this->types_width[$param['type']].'`');
				$param['width']=$this->types_width[$param['type']];
			}

			if(isset($this->alias_types[$param['type']]))
				$param['type'] = $this->alias_types[$param['type']];

			$m = '`' . strtolower($key) . '` ' . $param['type'];
			if($param['type']=='timestamp' and (!isset($param['attr']) or ($param['attr']=='NOT NULL' and !isset($param['default'])) ) )
				$m.=' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
			elseif($param['type']=='timestamp') {
				$m.=' ' . $param['attr'];
				if(isset($param['default']))
					$m.=' DEFAULT \'' . $param['default'] . '\'';
			}
			else
			{
				if (isset($param['width']) && is_array($param['width'])) {
					if ($param['type'] == 'enum')
						$m.= '("' . implode('","', array_keys($param['width'])) . '")';
				}
				elseif (isset($param['width']) && $param['width'] != '')
					$m.= '(' . $param['width'] . ')';
				if( isset($param['attr']))
					$m.=' ' . $param['attr'];
				elseif(isset($param['default']) and strpos($param['default'],'NULL')===false and $param['type']!='text')
					$m.=' NOT NULL';
				if(isset($param['default']))
					$m.=' DEFAULT \'' . $param['default'] . '\'';
				if(!isset($param['default']) and $param['type']!='text' and !isset($param['attr']))
					$m.=' DEFAULT NULL';
			}
			return array($m,$mess);
		}

		public function _getSQLTableInfo($tablename) {
			if(!$this->ready) return false;
			$data = array();
			$result = $this->execSQL('SHOW FULL FIELDS FROM `' . $tablename . '`');
			while ($COLUMNS = $result->fetch_array()) {
				$fldname = $COLUMNS['Field'];//mb_strtolower(
				$data[$fldname] = $COLUMNS;
			}
			$result = $this->execSQL('SHOW CREATE TABLE `' . $tablename . '`');
			if ($row = $result->fetch_array()) {
				$creat_table = $row['Create Table'];
				$creat_table = explode("\n",$creat_table);
				array_shift($creat_table);
				$info = array_pop($creat_table);
				foreach($creat_table as $r) {
					$r = trim($r," ,\t\r");
					if(substr($r,0,1)=='`') {
						$pos = strpos($r,'`',1);
						$fldname = substr($r,1,($pos-1));
						$data[$fldname]['create'] = $r;
					}
				}
			}
			return $data;
		}

		function longLog($ttt,$sql) {
			global $_CFG;
			if(isset($this->CFG_SQL['longquery']) and $this->CFG_SQL['longquery']>0 and $ttt>$this->CFG_SQL['longquery']) {
				trigger_error('LONG QUERY ['.$ttt.' sec. - мах '.$this->CFG_SQL['longquery'].'] ('.$sql.')', E_USER_WARNING);
				if($this->logFile!==false)
					$this->logFile[] = '['.date('Y-m-d H:i:s').'] LONG QUERY ['.$ttt.' sec.] ('.$sql.')';
			}
			//if(strstr(strtolower($sql),'insert into'))
			//	$this->id = $this->sql_id();
			if(isset($_COOKIE[$_CFG['wep']['_showallinfo']]) and $_COOKIE[$_CFG['wep']['_showallinfo']]>1) {
				if($ttt>0.5) $ttt = '<span style="color:#FF0000;">'.$ttt.'</span>';
				elseif($ttt>0.1) $ttt = '<span style="color:#FF6633;">'.$ttt.'</span>';
				elseif($ttt>0.05) $ttt = '<span style="color:#006699;">'.$ttt.'</span>';
				elseif($ttt>0.01) $ttt = '<span style="color:#66CCCC;">'.$ttt.'</span>';
				elseif($ttt>0.005) $ttt = '<span style="color:#006600">'.$ttt.'</span>';
				else $ttt = '<span style="color:#00FF00;">'.$ttt.'</span>';
				$_CFG['logs']['sql'][] = htmlentities($sql,ENT_NOQUOTES,$_CFG['wep']['charset']).'  TIME='.$ttt;
			}
			elseif($_CFG['_F']['adminpage'] or isset($_COOKIE[$_CFG['wep']['_showallinfo']]))
				$_CFG['logs']['sql'][] = true;
		}

	}

	class query {

		var $handle;
		var $id;
		var $affected;
		var $err;

		function __construct(&$db, $sql) {
			global $_CFG;
			$ttt = getmicrotime();
			$this->handle = mysqli_query($db->hlink, $sql);
			$this->db = &$db;
			$this->query = $db->query = $sql;
			$this->err=mysqli_error($db->hlink);
			if ($this->err!='')
			{
				if($db->logFile!==false)
					$this->logFile[] = 'ERORR: '.$this->err.' ('.$sql.')';
				trigger_error($this->err.=' ('.$sql.');', E_USER_WARNING);
				$this->errno = mysqli_errno($db->hlink);
				//$db->fError($this->err);
			}
			else
			{
				$db->longLog((getmicrotime()-$ttt),$sql);
			}
		}

		function sql_id() {
			return mysqli_insert_id($this->handle);
		}

		function destroy() {
			return mysqli_free_result($this->handle);
		}

		function num_rows() {
			return mysqli_num_rows($this->handle);
		}

		// type MYSQLI_ASSOC | MYSQLI_BOTH | MYSQLI_NUM
		function fetch_array($type = MYSQLI_ASSOC) {
			return mysqli_fetch_array($this->handle, $type);
		}

		function sql_result($row) { /// TODO ???
			return mysqli_fetch_field_direct($this->handle, $row);
		}

		function fetch_object() {
			return mysqli_fetch_object($this->handle);
		}

		function affected_rows() {
			return mysqli_affected_rows($this->db->hlink);
		}

		function sql_seek($row) {
			return mysqli_data_seek($this->handle, $row);
		}
	}