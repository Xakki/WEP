<?
	//require("a.charset.php");
	header('Content-type: text/html; charset=utf-8');
	$AjaxJquery = new AjaxJquery();
	class AjaxJquery
	{
		var $_errn = 14623;

		function __construct() {
			ob_start(array(&$this, "_obHandler"));
			ini_set('display_errors', $this->_errn);
			//$_REQUEST = utf2win_recursive($_REQUEST);
		}

		function __destruct() {
			ob_end_flush();
		}

		function _obHandler($buf) {
			global $GLOBALS;
			if($buf) $GLOBALS['_RESULT']['text'] = $buf;
			$GLOBALS['_RESULT'] = $this->allreplace($GLOBALS['_RESULT']);
			if(version_compare(phpversion(),'5.3.0','>'))
				return json_encode($GLOBALS['_RESULT'],JSON_HEX_TAG);//JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP
			else
				return json_encode($GLOBALS['_RESULT']);
		}

		function allreplace($arr) {
			$mask = array('/\n+/','/\r+/','/\t+/','/\"\"/');//,'/[\']+/','/[\"]+/'
			$repl = array('','','','""');//,'&#039;','&quot;'
			foreach($arr as $k=>$r) {
				if(is_array($r)) 
					$arr[$k] = $this->allreplace($r);
				elseif(is_string($arr[$k]))
					$arr[$k] = preg_replace($mask,$repl,$r);
			}
			return $arr;
		}

	}   

?>