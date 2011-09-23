<?php
	if($this->_CFG['_HREF']['arrayHOST'][0]=='i')
		$PGLIST->config['counter'] = '';
	$DATA = array('#item#'=>$PGLIST->getMap(1));
	$DATA = array('menu'=>$DATA);
	global $_tpl;
	return '<div class="copyright">© '.date('Y').' '.$HTML->transformPHP($DATA,'menu').'</div>';
	//<div class="w3c"><a href="http://validator.w3.org/check?uri=referer"><img src="_design/_img/valid-xhtml10-blue.png" alt="Valid XHTML 1.0 Transitional" style="width:88px;height:31px;"/></a></div>
	//<form method="get" action="http://unidoski.ru/ysearch.html"><div class="yandexbox"><input name="text"/><input type="hidden" name="searchid" value="128795"/><input type="image" src="http://site.yandex.ru/i/yandex_search.png" value="Найти" style="padding-left: 5px; vertical-align: bottom;"/></div></form>
