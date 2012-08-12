<?php

class static_render {
	public static $def = array ();
		
	public static function message($mess,$class='ok') {
		global $HTML;
		$mess = array(
			array($class, $mess)
		);
		return $HTML->transformPHP($mess,'#pg#messages');
	}
	

} 