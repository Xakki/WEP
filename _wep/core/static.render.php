<?php

class static_render {
	public static $def = array ();
		
	public static function message($mess,$class='ok') {
		$mess = array(
			array($class, $mess)
		);
		return transformPHP($mess,'#pg#messages');
	}
	

} 