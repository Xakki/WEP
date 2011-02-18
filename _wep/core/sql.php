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

		function __construct() {
			global $_CFG;
			$this->_iFlag= false;
			if((int)$_CFG['sql']['log']) $this->logFile = fopen($_CFG['_PATH']['wep'].'/log/_'.time().'.log', 'wb');
			$this->sql_connect();
		}

		function __destruct() {
			global $_CFG;
			$this->sql_close();
			if($_CFG['sql']['log']) fclose($this->logFile);
		}

		function sql_connect() {
			global $_CFG;
			$this->hlink = mysql_connect($_CFG['sql']['host'], $_CFG['sql']['login'], $_CFG['sql']['password']);
			if($this->hlink) {
				mysql_query ('SET NAMES '.$_CFG['sql']['setnames']);
				if(!mysql_select_db($_CFG['sql']['database'],$this->hlink)) {
					if(mysql_query('create database `'.$_CFG['sql']['database'].'` character set '.$_CFG['sql']['setnames'].' collate '.$_CFG['sql']['setnames'].'_general_ci;'))
						mysql_select_db($_CFG['sql']['database'],$this->hlink);
					else 
						trigger_error('Can`t create database `'.$_CFG['sql']['database'].'`', E_USER_WARNING);
						die();
				}
			}else {
				trigger_error('Can`t create database `'.$_CFG['sql']['database'].'`', E_USER_WARNING);
				die();
			}
		}

		function sql_close() {
			if($this->hlink)
				mysql_close($this->hlink);
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

	}

	class query {

		var $handle;
		var $id;
		var $affected;
		var $err;

		function __construct(&$db, $sql, $unbuffered) {
			global $_CFG,$_tpl;
			if(isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo'])
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
				if($_CFG['sql']['log']) 
					fwrite($db->logFile,'ERORR: '.$this->err.' ('.$sql.')\n');
				trigger_error($this->err.=" ({$sql});", E_USER_WARNING);
				$this->errno = mysql_errno();
				//$db->fError($this->err);
			}
			else
			{
				if($_CFG['sql']['log']) fwrite($db->logFile,$sql."\n");
				//if(strstr(strtolower($sql),'insert into'))
				//	$this->id = $db->sql_id();
				if(isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo']) {
					$ttt = (getmicrotime()-$ttt);
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

?>
