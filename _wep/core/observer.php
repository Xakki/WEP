<?

class observer
{
	static private $publishers = array();
	static private $observers = array();
	
	static function register_publisher($name) {
		if (!isset(self::$publishers[$name])) {
			self::$publishers[$name] = true;
		}
		
		if (isset(self::$observers[$name])) {
			foreach (self::$observers[$name] as $r) {
				if (isset($r['args'])) {
					$str_args = '$r[\'args\']['.implode('], $r[\'args\'][', array_keys($r['args'])).']';
				}
				else {
					$str_args = '';
				}
				
				if ($r['obj'] == NULL) {
					eval($r['func'].'('.$str_args.');');
				}
				else {
					eval('$r["obj"]->'.$r['func'].'('.$str_args.');');
				}
			}
		}
	}	
	
	/* ************
	 * 
	 * ************/
	static function register_observer($params, $publisher) {
		if (!isset($params['func']) || !isset($publisher)) {
			trigger_error('В функцию register_observer не переданы все необходимые параметры');
			return false;
		}
		if (isset($params['args']) && !is_array($params['args'])) {
			trigger_error('В функции register_observer переданный пар-р params[args] должен быть массивом');
			return false;
		}
		
		if (!isset(self::$observers[$publisher])) {
			self::$observers[$publisher] = array();
		}
		
		self::$observers[$publisher][] = $params;
		
		if (isset(self::$publishers[$publisher])) {
			if (isset($params['args'])) {
				$str_args = '$params[\'args\']['.implode('], $params[\'args\'][', array_keys($params['args'])).']';
			}
			else {
				$str_args = '';
			}
			
			if (isset($params['obj'])) {
				eval('$params[\'obj\']->'.$params['func'].'('.$str_args.');');				
			}
			else {
				eval($params['func'].'('.$str_args.');');
			}
		}
		
	}
}

?>
