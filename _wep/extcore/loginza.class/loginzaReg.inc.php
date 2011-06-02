<?

/**HELP
$FUNCPARAM[0] - openid провайдеры [yandex,google,rambler,mailruapi,myopenid,openid,loginza]
$FUNCPARAM[2] - страница редиректа для логинзы
HELP**/

	if($_POST['token']) {
		if(!$LOGINZA)
			_new_class('loginza',$LOGINZA);
		$mess =  $LOGINZA->loginzaAuth(true);
		$mess = array('messages'=>$mess);
		return $HTML->transformPHP($mess,'messages');

	}

	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = 'yandex,google,rambler,mailruapi,myopenid,openid,loginza';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = $PGLIST->getHref().'.html';
	$_tpl['script']['loginza'] = array('http://loginza.ru/js/widget.js');
	$form = '<div class="layerblock" style="width:620px;background:none;border:none;margin:20px auto 0;"><iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url='.urlencode($_CFG['_HREF']['BH'].$FUNCPARAM[1]).'&providers_set='.$FUNCPARAM[0].'" style="width:359px;height:180px;float:left;" scrolling="no" frameborder="no" id="loginzaiframe"></iframe>'.$form.'</div>';
	
	//<div class="messhead" style="text-align: center;">'.$result[0].$mess.'</div>
	$html = '<div style="height:100%;">'.$form.'</div>';
	return $html;

