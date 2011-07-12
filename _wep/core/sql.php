<?
/*SQL*/

	class sql {
		var $hlink;
		var $sql_query;
		var $sql_err;
		var $sql_res;
		var $logFile;
		/**Если тру - то проверка таблиц и папок*/
		var $_iFlag;

		function __construct($CFG_SQL,$new_link = false) {
			global $_CFG;
			$this->CFG_SQL = $CFG_SQL;
			$this->_iFlag= false;
			if((int)$this->CFG_SQL['log']) $this->logFile = fopen($_CFG['_PATH']['wep'].'/log/_'.time().'.log', 'wb');
			$this->sql_connect($new_link);
		}

		function __destruct() {
			$this->sql_close();
			if($this->CFG_SQL['log']) fclose($this->logFile);
		}

		function sql_connect($new_link) {
			$this->hlink = mysql_connect($this->CFG_SQL['host'], $this->CFG_SQL['login'], $this->CFG_SQL['password']);
			if($this->hlink) {
				mysql_query ('SET NAMES '.$this->CFG_SQL['setnames']);
				if(!mysql_select_db($this->CFG_SQL['database'],$this->hlink)) {
					if(mysql_query('create database `'.$this->CFG_SQL['database'].'` character set '.$this->CFG_SQL['setnames'].' collate '.$this->CFG_SQL['setnames'].'_general_ci;'))
						mysql_select_db($this->CFG_SQL['database'],$this->hlink);
					else 
						trigger_error('Can`t create database `'.$this->CFG_SQL['database'].'`', E_USER_WARNING);
						die('SQL config error');
				}
			}else {
				trigger_error('Can`t connect to database `'.$this->CFG_SQL['database'].'`', E_USER_WARNING);
				die('SQL connect error');
			}
		}

		function sql_close() {
			//if($this->hlink)
			//	mysql_close($this->hlink);
		}

		function sql_id() {
			return mysql_insert_id();
		}

		function execSQL($sql,$unbuffered=0) {
			return new query($this, $sql, $unbuffered);
		}
		function fError($err) {
			$this->sql_err[] = $err;
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
				'varchar' => 255,
			);
			// типы полей, в которых нет атрибута default
		var $types_without_default = array(
				'tinytext' => true,
				'text' => true,
				'mediumtext' => true,
				'longtext' => true,
			);

		function _fldformer($key, $param) {
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
			if($param['type']=='timestamp')
				$m.=' NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
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
				if(!isset($param['default']) and $param['type']=='varchar' and !$param['attr'])
					$m.=' DEFAULT NULL';
			}
			return array($m,$mess);
		}

		function _getSQLTableInfo($tablename) {
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

	}

	class query {

		var $handle;
		var $id;
		var $affected;
		var $err;

		function __construct(&$db, $sql, $unbuffered) {
			global $_CFG,$_tpl;
			//if((isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo']) or $db->CFG_SQL['longquery']>0)
				$ttt = getmicrotime();
			if($unbuffered) {// Тут можно задавать запросы, разделённые точкой запятой
				$this->handle = mysql_unbuffered_query($sql, $db->hlink);
			}
			else
				$this->handle = mysql_query($sql, $db->hlink);
			$this->db = &$db;
			$this->query = $db->query = $sql;
			$this->err=mysql_error($db->hlink);
			if ($this->err!='')
			{
				if($db->CFG_SQL['log']) 
					fwrite($db->logFile,'ERORR: '.$this->err.' ('.$sql.')\n');
				trigger_error($this->err.=' ('.$sql.');', E_USER_WARNING);
				$this->errno = mysql_errno();
				//$db->fError($this->err);
			}
			else
			{
				$ttt = (getmicrotime()-$ttt);
				if($db->CFG_SQL['log']) fwrite($db->logFile,$sql."\n");
				if($db->CFG_SQL['longquery']>0 and $ttt>$db->CFG_SQL['longquery'])
					trigger_error('LONG QUERY ['.$ttt.' sec.] ('.$sql.')', E_USER_WARNING);
				//if(strstr(strtolower($sql),'insert into'))
				//	$this->id = $db->sql_id();
				if(isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo']) {
					
					if($ttt>0.5) $ttt = '<span style="color:#FF0000;">'.$ttt.'</span>';
					elseif($ttt>0.1) $ttt = '<span style="color:#FF6633;">'.$ttt.'</span>';
					elseif($ttt>0.05) $ttt = '<span style="color:#006699;">'.$ttt.'</span>';
					elseif($ttt>0.01) $ttt = '<span style="color:#66CCCC;">'.$ttt.'</span>';
					elseif($ttt>0.005) $ttt = '<span style="color:#006600">'.$ttt.'</span>';
					else $ttt = '<span style="color:#00FF00;">'.$ttt.'</span>';
					$_CFG['logs']['sql'][] = htmlentities($sql,ENT_NOQUOTES,$_CFG['wep']['charset']).'  TIME='.$ttt;
				}
				elseif($_CFG['_F']['adminpage'])
					$_CFG['logs']['sql'][] = 1;
				//$_tpl['logs'] .= " ({$sql});<br/> ";
			}
		}

		function sql_id() {
			return $this->db->sql_id();
		}

		function destroy() {
			return mysql_free_result($this->handle);
		}

		function num_rows() {
			return mysql_num_rows($this->handle);
		}

		// type MYSQL_ASSOC | MYSQL_BOTH | MYSQL_NUM
		function fetch_array($type = MYSQL_ASSOC) {
			return mysql_fetch_array($this->handle, $type);
		}

		function sql_result($row) {
			return mysql_result($this->handle, $row);
		}

		function fetch_object() {
			return mysql_fetch_object($this->handle);
		}

		function affected_rows() {
			return mysql_affected_rows($this->db->hlink);
		}

		function sql_seek($row) {
			return mysql_data_seek($this->handle, $row);
		}
	}