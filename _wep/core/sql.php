<?
	register_shutdown_function ('shutdown_function'); // Запускается первым при завершении скрипта

/*
Функция завершения работы скрипта
*/
	function shutdown_function() {
		//ob_end_flush();
		//print_r('shutdown_function');
		session_write_close();
	}
/*SESSION*/

	function session_go($force=0) {
		global $_CFG;
		if(!$_SERVER['robot'] and (isset($_COOKIE[$_CFG['session_name']]) or $force) and !defined('SID')) {
			if($_CFG['wep']['sessiontype']==1) {
				if(!$SESSION_GOGO) {
					require_once($_CFG['_PATH']['core'].'/session.php');
					$SESSION_GOGO = new session_gogo();
				}
			}else {
				session_start();
			}
			return true;
		}
		return false;
	}

/*SQL*/

	class sql
	{
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

		function __destruct()
		{
			global $_CFG;
			$this->sql_close();
			if($_CFG['sql']['log']) fclose($this->logFile);
		}

		function sql_connect()	{
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

		function sql_close()
		{
			if($this->hlink)
				mysql_close($this->hlink);
		}

		function sql_id() {
			return mysql_insert_id();
		}

		function execSQL($sql,$unbuffered=0) {
			return new query(&$this, $sql, $unbuffered);
		}
		function fError($err){
			$this->sql_err[] = $err;
		}

	}

	class query {

		var $handle;
		var $id;
		var $affected;
		var $err;

		function __construct($db, $sql, $unbuffered) {
			global $_CFG,$_tpl;
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
				trigger_error($this->err.=" ({$sql});<br/> ", E_USER_WARNING);
				$this->errno = mysql_errno();
				//$db->fError($this->err);
			}
			else
			{
				if($_CFG['sql']['log']) fwrite($db->logFile,$sql."\n");
				if(strstr(strtolower($sql),'insert into'))
					$this->id = $db->sql_id();
				if(isset($_COOKIE['_showallinfo']) and $_COOKIE['_showallinfo'])
					$_CFG['logs']['sql'][] = $sql;
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
