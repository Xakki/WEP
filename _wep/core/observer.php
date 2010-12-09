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
				if ($r['obj'] == NULL) {
					eval($r['func'].'();');
				}
				else {
					eval('$r["obj"]->'.$r['func'].'();');
				}
			}
		}
	}	
	
	static function register_observer($obj, $func, $publisher) {
		if (!isset(self::$observers[$publisher])) {
			self::$observers[$publisher] = array();
		}
		
		self::$observers[$publisher][] = array(
			'obj' => $obj,
			'func' => $func,
		);
		
		if (isset(self::$publishers[$publisher])) {
			if ($r['obj'] == NULL) {
				eval($func.'();');
			}
			else {
				eval('$obj->'.$func.'();');
			}
		}
		
	}
}

?>
