<?php
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 'Код полученный в SAPE.RU';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 2;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 0;
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 0;
	if(!isset($FUNCPARAM[4])) $FUNCPARAM[4] = 7200;

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		global $_CFG;
		$this->_enum['sapefunct'] = array(
			0=>'return_links',
			1=>'return_block_links',
		);
		$form = array(
			'0'=>array('type'=>'text', 'caption'=>'_SAPE_USER'),
			'1'=>array('type'=>'int', 'caption'=>'Кол-во ссылок'),
			'2'=>array('type'=>'list','listname'=>'sapefunct','caption'=>'Функция выввода ссылок'),
			'3'=>array('type'=>'checkbox','caption'=>'Мультисайт'),
			'4'=>array('type'=>'int', 'caption'=>'Timeout'),
		);
		return $form;
	}

	global $SAPE;
	$html='';
//sape************************ www.sape.ru
	if (!defined('_SAPE_USER')) {
		define('_SAPE_USER', $FUNCPARAM[0]); 
	}
	if(!$SAPE) {
		require_once($_SERVER['DOCUMENT_ROOT'].'/'._SAPE_USER.'/sape.php'); 
		$o = array('charset'=>'UTF-8');
		if($FUNCPARAM[3])
			$o['multi_site'] = true; //Указывает скрипту наличие нескольких сайтов
		$o['_cache_reloadtime'] = $FUNCPARAM[4];
		$o['_cache_lifetime'] = $FUNCPARAM[4];
		if(!$SAPE) $SAPE = new SAPE_client($o);unset($o);
	}
		//global $sape; echo $sape->return_links();
		//$sape_context = new SAPE_context();$text = $sape_context->replace_in_text_segment($text);//В данном фрагменте текста страницы моего сайта я хочу продавать контекстные ссылки
		//if($html = $SAPE->return_links($FUNCPARAM[1]) and _strlen($html)>5)
		//	$html .='<div class="hrb" style="margin-top: 5px;"></div>';
		
	if(!$FUNCPARAM[2])
		$html = $SAPE->return_links($FUNCPARAM[1]);
	else
		$html = $SAPE->return_block_links($FUNCPARAM[1]);

//****************sape 
	return $html;
