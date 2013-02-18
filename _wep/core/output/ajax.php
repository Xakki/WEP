<?php

	$WEPOUT = new wepajax();

	class wepajax
	{
		var $_errn = 14623;

		function __construct() 
		{
			header('Content-type: text/html; charset=utf-8');
			//header('Content-type: application/json; charset=utf-8');
			ob_start(array(&$this, "obHandler"));
			ini_set('display_errors', $this->_errn);
			//$_REQUEST = utf2win_recursive($_REQUEST);
		}

		function __destruct() {
			ob_end_flush();
		}

		function obHandler($buf) {
			global $_tpl;
			if($buf) $_tpl['text'] = $buf;
			if(isset($_tpl['time']) and $_tpl['time'])  $_tpl['onload'] .= '$(\'#inftime\').html(\''.$_tpl['time'].'\');';
			
			if(version_compare(phpversion(),'5.3.0','>')) {
				//$GLOBALS['_RESULT'] = $this->allreplace($GLOBALS['_RESULT']);
				//return var_export($GLOBALS['_RESULT'],true);
				//$GLOBALS['_RESULT']['onload'] = str_replace(array('\\r','\\n','\\t'),'', $GLOBALS['_RESULT']['onload']);
				$result = json_encode($_tpl, JSON_HEX_QUOT | JSON_HEX_TAG ); 
				//JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
				return $result;
			}
			else
				return $this->jsonencode($_tpl);
		}

		function allreplace($arr) {
			$mask = array('/\n+/','/\r+/','/\t+/','/\"\"/');//,'/[\']+/','/[\"]+/'
			$repl = array('','','','""');//,'&#039;','&quot;'
			$repl2 = array('<br/>','','&#160;&#160;&#160;&#160;','""');
			foreach($arr as $k=>$r) {
				if(is_array($r)) 
					$arr[$k] = $this->allreplace($r);
				elseif(is_string($arr[$k])) {
					if($k=='text')
						$arr[$k] = preg_replace($mask,$repl2,$r);
					else
						$arr[$k] = preg_replace($mask,$repl,$r);
				}
			}
			return $arr;
		}

		function jsonencode($value) 
		{
			if (is_int($value)) {
				return (string)$value;   
			} elseif (is_string($value)) {
			  $value = str_replace(array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t",'/\"\"/','<','>'), 
										  array('\\\\', '\/', '\"', '', '', '', '', '','""',"\\u003C","\\u003E"), $value);
			  $convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
			  $result = "";
			  for ($i = mb_strlen($value) - 1; $i >= 0; $i--) {
					$mb_char = mb_substr($value, $i, 1);
					if (mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) {
						 $result = sprintf("\\u%04x", $match[1]) . $result;
					} else {
						 $result = $mb_char . $result;
					}
			  }
			  return '"' . $result . '"';                
			} elseif (is_float($value)) {
				return str_replace(",", ".", $value);         
			} elseif (is_null($value)) {
				return 'null';
			} elseif (is_bool($value)) {
				return $value ? 'true' : 'false';
			} elseif (is_array($value)) {
				$with_keys = false;
				$n = count($value);
				for ($i = 0, reset($value); $i < $n; $i++, next($value)) {
								if (key($value) !== $i) {
					$with_keys = true;
					break;
								}
				}
			} elseif (is_object($value)) {
				$with_keys = true;
			} else {
				return '';
			}
			$result = array();
			if ($with_keys) {
				foreach ($value as $key => $v) {
					 $result[] = $this->jsonencode((string)$key) . ':' . $this->jsonencode($v);    
				}
				return '{' . implode(',', $result) . '}';                
			} else {
				foreach ($value as $key => $v) {
					 $result[] = $this->jsonencode($v);    
				}
				return '[' . implode(',', $result) . ']';
			}
		}

	} 

