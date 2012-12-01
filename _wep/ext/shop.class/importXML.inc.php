<?php
/**
 * Импорт Товаров из 1С XML
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

	if(!_new_class('shop',$SHOP)) 
		$mess[] = static_main::am('error', 'Ошибка подключения модуля');
	elseif (!static_main::_prmModul($SHOP->_cl, array(5, 7)) and !isset($_GET['secret']))
		$mess[] = static_main::am('error', 'denied', $SHOP);
	elseif(isset($_GET['secret']) and ($_GET['secret']!=$rowPG['pg'] or !$rowPG['pg']) )
		$mess[] = static_main::am('error', 'Не верный код. Код в настройках данного контролера ,в поле `Текст`');
	else
	{
		if (isset($_POST['sbmt']))
		{
			$mess = procesedPostDataImport1C();
		}

		if(!count($mess))
		{
			if($rowPG['name'])
				$fields_form['_info'] = array(
					'type' => 'info',
					'caption' => '<h2 style="text-align:center;">'.$rowPG['name'].'</h2>');

			$fields_form['offCat'] = array(
				'type' => 'checkbox',
				'caption' => 'Отключить все категории',
				'comment' => 'При загрузке XML, все категории из XML будут включены. Таким образом отключаться те категории которых не было в XML',
			);
			$fields_form['offProd'] = array(
				'type' => 'checkbox',
				'caption' => 'Отключить все товары.',
				'comment' => 'При загрузке XML, все товары из XML будут включены. Таким образом отключаться те товары которых не было в XML',
			);
			$fields_form['fixCatName'] = array(
				'type' => 'checkbox',
				'caption' => 'Убрать дублирование названия в каталоге у прочих',
			);

			$fields_form['filexml'] = array(
				'type' => 'file',
				'caption' => '',
				'comment'=>'фаил формате xml , можно в zip архиве',
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


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////



function simplexml2array($obj, &$result) {
    $data = $obj;
    if (is_object($data)) {
        $data = get_object_vars($data);
    }
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $res = null;
            simplexml2array($value, $res);
            if (($key == '@attributes') && ($key)) {
                $result = $res;
            } else {
                $result[$key] = $res;
            }
        }
    } else {
        $result = $data;
    }
}

function procesedPostDataImport1C()
{
	_new_class('shop',$SHOP);
	$mess = array();

	if(isset($_POST['offCat']))
	{
		if($SHOP->_update(array('active'=>0), 'where 1', false))
			$mess[] = static_main::am('ok', "Успешно отключины все категории");
	}
	
	if(isset($_POST['offProd']))
	{
		_new_class('product',$MODULE);
		if($MODULE->_update(array('active'=>0), 'where 1', false))
			$mess[] = static_main::am('ok', "Успешно отключины все товары");
	}
	if(isset($_POST['fixCatName']))
	{
		
		$data = $SHOP->qs('id,name, parent_id', 'WHERE !uiname');
		foreach($data as $row)
		{
			$SHOP->id = $row['id'];
			$SHOP->_update($row);
		}
		$mess[] = static_main::am('ok', "Названия у категорий исправленны!");
	}

	if (isset($_FILES['filexml']) and $_FILES['filexml']['name'])
	{
		if(!$_FILES['filexml']['error'])
		{
			$T1 = time();
			ini_set("max_execution_time", "10000");
			set_time_limit (10000);

			$file = 'xmlimport'.$_SESSION['user']['id'].time();
			$_SESSION['user']['xmlimport'] = $file;
			$file = $_CFG['_PATH']['temp'].$file;
			//$imgDir = array();
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
							/*else
								$imgDir[$dfile] = $zipDir.'/'.$dfile;*/
						}
					}
				}
				else
					$xml = $file;

				// ИМпорт XML
				if($xml) {
					$result = array();
					simplexml2array(simplexml_load_file($xml), $result);
					
					//file_put_contents(dirname($xml).'/out.txt', print_r($result, true));

					helperImport1C($result);
				

					//TODO - нарисовать таблицу для того чтобы задать соответствие полей из фаила, полям из БД
					//TODO - СПИСОК доступных полей БД здается в настройках INC , проблемка в том что поля мы задаем позже
					$mess[] = static_main::am('ok', "Готово");
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

	return $mess;
}

function helperImport1C($data, $owner=0)
{
	$info = array(
		'Группа' => array(
			'class'=>'shop',
			'setowner' => 'parent_id',
			'key' => 'code',
			'key2' => 'name',
			'setActive' => 1,
			'field' => array(
				'Код' => 'code',
				'Наименование' => 'name'
			),
			'forUpdate'=> array('active')
		),
		'Товар'=> array(
			'class'=>'product',
			'setowner' => 'owner_id',
			'key' => 'code',
			'field' => array(
				'Код' => 'code',
		        'Наименование' => 'name',
		        'Модель' => 'model',
		        'Артикул' => 'articul',
		        'Изготовитель' => 'madein',
		        'Запас' => 'remainder',
		        'Цена' => 'cost'
		    ),
		    'forUpdate'=> array(
		    	'cost', 'remainder', 'active'
		    )
		)
	);

	foreach($info as $k=>$r)
	{
		if(isset($data[$k]) and _new_class($r['class'],$MODEL))
		{
			foreach($data[$k] as $row)
			{
				$insertData = array();
				foreach ($r['field'] as $key => $value) {
					if(isset($row[$key]))
					{
						$insertData[$value] = $row[$key];
					}
				}
				if(isset($r['setowner']))
					$insertData[$r['setowner']] = $owner;

				if(isset($r['setActive']))
					$insertData['active'] = 1;
				else
				{
					$insertData['active'] = 1;
					// available
					// remainder
				}

				// проверяем, есть ли в базе такая же запись
				$q = 'WHERE '.$r['key'].'="'.$MODEL->SqlEsc($insertData[$r['key']]).'"';
				if(isset($r['key2']))
					$q .= ' or '.$r['key2'].'="'.$MODEL->SqlEsc($insertData[$r['key2']]).'"';

				$result = $MODEL->qs('id',$q);

				if(count($result))
				{
					$insertData['id'] = $result[0]['id'];
					if($r['forUpdate']!==false)
					{
						$MODEL->id = $insertData['id'];
						$MODEL->_update(array_intersect_key($insertData, array_flip($r['forUpdate'])), NULL, false);
					}
				}
				else
				{
					$MODEL->_add($insertData, false);
					$insertData['id'] = $MODEL->id;
				}


				if($insertData['id'])
					helperImport1C($row, $insertData['id']);
			}

		}
	}
}

return $html;