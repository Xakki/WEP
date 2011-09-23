<?php
	global $SUBSCRIBE;
	$DATA = array();
	if(static_main::_prmUserCheck()) {
		if(!$SUBSCRIBE) _new_class('subscribe',$SUBSCRIBE);
		list($DATA['formcreat'],$flag) = $SUBSCRIBE->_UpdItemModul(array());
		$html = $HTML->transformPHP($DATA,'formcreat');
	}
	else {
		$DATA['messages'][0]= array('name'=>'alert','value'=>'Услуга "Подписка на объявления" дотупна только зарегистрированным пользователям. Вы можете <a href="regme.html">зарегистрироваться</a> абсолютно бесплатно и воспользоваться данной услугой');
		$html = $HTML->transformPHP($DATA,'messages');
	}

	return $html;
