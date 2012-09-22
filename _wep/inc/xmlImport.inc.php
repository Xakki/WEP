<?php
/**
 * Импорт данных из XML
 * @ShowFlexForm true
 * @type Импорт
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */

	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';

	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - включить AJAX

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		global $_CFG;
		$this->_enum['modullist'] = array();
		foreach($_CFG['modulprm'] as $k=>$r) {
			if($r['active'])
				$this->_enum['modullist'][$r['pid']][$k] = $r['name'];
		}

		$form = array(
			0=>array('type'=>'list','listname'=>'modullist', 'caption'=>'Модуль'),
		);
		return $form;
	}


	$fields_form = $mess = array();


	if(!_new_class($FUNCPARAM[0],$MODUL)) 
		$mess[] = static_main::am('error', 'Ошибка подключения модуля');
	elseif (!static_main::_prmModul($MODUL->_cl, array(5, 7)))
		$mess[] = static_main::am('error', 'denied', $MODUL);
	else
	{
		if (isset($_POST['sbmt']) and isset($_FILES['filexml']))
		{
			if(!$_FILES['filexml']['error'])
			{
				$T1 = time();
				ini_set("max_execution_time", "1000");
				set_time_limit (1000);
				$file = 'xmlimport'.$_SESSION['user']['id'].time();
				$_SESSION['user']['xmlimport'] = $file;
				$file = $_CFG['_PATH']['temp'].$file;
				$imgDir = array();
				$xml = $zipDir = '';

				$const_img_dir = $_CFG['_PATH']['content'].'Tovar';

				// Если загружен фаил
				if(move_uploaded_file($_FILES['filexml']['tmp_name'],$file)) {
					if(stripos($_FILES['filexml']['name'],'.zip')!==false) {
						$zipDir = static_tools::extractZip($file);
						$DIR = scandir($zipDir);
						foreach($DIR as $dfile) {
							if($dfile!='.' and $dfile!='..') {
								if(stripos($dfile,'.xml'))
									$xml = $zipDir.'/'.$dfile;
								else
									$imgDir[$dfile] = $zipDir.'/'.$dfile;
							}
						}
					}
					else
						$xml = $file;

					// ИМпорт XML
					if($xml) {
						$category = array();
						$product = array();

						$name_group = 'Группа';

						$data = simplexml_load_file($xml);
						$result = simplexml2array($data);
						
						//print_r('<pre>');print_r($result);
					

						//TODO - нарисовать таблицу для того чтобы задать соответствие полей из фаила, полям из БД
						//TODO - СПИСОК доступных полей БД здается в настройках INC , проблемка в том что поля мы задаем позже
					}
					else
						$mess[] = static_main::am('error', "XML фаил не найден");
				}
				else
					$mess[] = static_main::am('error', 'Error in move_uploaded_file'); // TODO Trigerr
			}
			else
				$mess[] = static_main::am('error', '_err_4'.$_FILES['filexml']['error'], array($_FILES['filexml']['name']));
		}
		elseif (isset($_POST['sbmt']))
		{
			$mess[] = static_main::am('alert', 'TODO');
		}
		else
		{
			if($rowPG['pg'])
				$fields_form['_info'] = array(
					'type' => 'info',
					'caption' => $rowPG['pg']);
			$fields_form['filexml'] = array(
				'type' => 'file',
				'caption' => $rowPG['name'],
				'comment'=>'В формате xml',
				'mask'=>array(),
			);

			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Импортировать',
			);
			$this->kFields2FormFields($fields_form, 'POST');
		}
	}

	$DATA = array('form' => $fields_form, 'messages' => $mess);
	$html = $HTML->transformPHP($DATA,'#pg#formcreat');

	return $html;

function simplexml2array($xml) {
	if (get_class($xml) == 'SimpleXMLElement') {
		$attributes = $xml->attributes();
		foreach($attributes as $k=>$v) {
			if ($v) $a[$k] = (string) $v;
		}
		$x = $xml;
		$xml = get_object_vars($xml);
	}
	if (is_array($xml)) {
		if (count($xml) == 0) return (string) $x; // for CDATA
		foreach($xml as $key=>$value) {
			$r[$key] = simplexml2array($value);
		}
		if (isset($a)) $r['@attributes'] = $a;    // Attributes
		return $r;
	}
	return (string) $xml;
}