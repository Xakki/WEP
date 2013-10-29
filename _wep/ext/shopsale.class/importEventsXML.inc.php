<?php
/**
 * Импорт Акций-Товаров из 1С XML
 * @ShowFlexForm false
 * @type Магазин
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */

/*if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';

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
}*/


$fields_form = $mess = array();

if (!_new_class('shop', $SHOP))
	$mess[] = static_main::am('error', 'Ошибка подключения модуля');
elseif (!static_main::_prmModul($SHOP->_cl, array(5, 7)) and !isset($_GET['secret']))
	$mess[] = static_main::am('error', 'denied', $SHOP); elseif (isset($_GET['secret']) and ($_GET['secret'] != $rowPG['pg'] or !$rowPG['pg']))
	$mess[] = static_main::am('error', 'Не верный код. Код в настройках данного контролера ,в поле `Текст`'); else {
	if (isset($_POST['sbmt'])) {
		$mess = procesedPostDataImport1C();
	}

	if (!count($mess)) {
		if ($rowPG['name'])
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">' . $rowPG['name'] . '</h2>');

		/*$fields_form['offCat'] = array(
			'type' => 'checkbox',
			'caption' => 'Отключить все категории',
			'comment' => 'При загрузке XML, все категории из XML будут включены. Таким образом отключаться те категории которых не было в XML',
		);*/
		if ($rowPG['pg'])
			$fields_form['help'] = array(
				'type' => 'info',
				'caption' => $rowPG['pg']);

		$fields_form['filexmlEvent'] = array(
			'type' => 'file',
			'caption' => '',
			'comment' => 'фаил в формате xml , можно в zip архиве',
			'mask' => array(),
		);

		$fields_form['sbmt'] = array(
			'type' => 'submit',
			'value' => 'Импортировать',
		);
		$this->kFields2FormFields($fields_form, 'POST');
	}
}

$DATA = array(
	'form' => $fields_form,
	'messages' => $mess,
	'options' => $this->getFormOptions()
);
$html = transformPHP($DATA, '#pg#formcreat');


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


function procesedPostDataImport1C()
{
	global $_CFG;
	_new_class('shop', $SHOP);
	$mess = array();
	$fieldFile = 'filexmlEvent';

	/*if(isset($_POST['offCat']))
	{
		if($SHOP->_update(array('active'=>0), 'where 1', false))
			$mess[] = static_main::am('ok', "Успешно отключены все категории");
	}*/


	if (isset($_FILES[$fieldFile]) and $_FILES[$fieldFile]['name']) {
		if (!$_FILES[$fieldFile]['error']) {
			$T1 = time();
			ini_set("max_execution_time", "10000");
			set_time_limit(10000);

			$file = $fieldFile . $_SESSION['user']['id'] . time();
			$file = $_CFG['_PATH']['temp'] . $file;
			//$imgDir = array();
			$xml = $zipDir = '';

			// Если загружен фаил
			if (move_uploaded_file($_FILES[$fieldFile]['tmp_name'], $file)) {
				if (stripos($_FILES[$fieldFile]['name'], '.zip') !== false) {
					$zipDir = static_tools::extractZip($file);
					$DIR = scandir($zipDir);
					foreach ($DIR as $dfile) {
						if ($dfile != '.' and $dfile != '..') {
							if (stripos($dfile, '.xml'))
								$xml = $zipDir . '/' . $dfile;
						}
					}
				} else
					$xml = $file;

				// ИМпорт XML
				if ($xml) {
					$result = array();
					$xmlData = simplexml_load_file($xml);

					static_tools::simplexml2array($xmlData, $result);

					$info = array(
						'Товар' => array(
							'class' => 'shopsale',
							'field' => array(
								'Текст' => 'name',
								'Скидка' => 'sale',
								'Тип' => 'saletype',
								'Начало' => 'periods',
								'Конец' => 'periode',
								'Код' => 'product',
							),
							'default' => array(
								'name' => 'Товар дня',
							),
							'eval' => array(
								'periods' => 'strtotime("%%")',
								'periode' => '(strtotime("%%")+86399)', // конец дня
								'Тип' => '("%%"=="руб."?1:0)',
							),
							'importId' => array(
								'product' => 'SELECT id FROM product WHERE code="%%"',
							)

						)
					);

					if ($result and is_array($result) and count($result)) {
						static_tools::helperImport1C($info, $result);
						$mess[] = static_main::am('ok', "Готово");
					} else {
						$mess[] = static_main::am('err', "Ошибка при обработке фаила");
					}


					//TODO - нарисовать таблицу для того чтобы задать соответствие полей из фаила, полям из БД
					//TODO - СПИСОК доступных полей БД здается в настройках INC , проблемка в том что поля мы задаем позже

				} else
					$mess[] = static_main::am('error', "XML фаил не найден");
			} else
				$mess[] = static_main::am('error', 'Error in move_uploaded_file'); // TODO Trigerr
		} else
			$mess[] = static_main::am('error', '_err_4' . $_FILES[$fieldFile]['error'], array($_FILES[$fieldFile]['name']));
	}

	return $mess;
}


return $html;