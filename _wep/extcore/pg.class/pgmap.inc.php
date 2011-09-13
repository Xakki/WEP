<?
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = 0;//0 - выводит всё в виде структуры дерева , 1 - выводит все в общем массиве, 2 только начальный уровень от $FUNCPARAM[2]
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 1; // Показывать меню начиная с уровня ID page
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 'pgmap'; // Шаблон

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$this->_enum['typemenuinc'] = array(
			0=>'выводит всё в виде структуры дерева',
			1=>'выводит все в общем массиве',
			2=>'только начальный уровень от `Уровень вывода данных`',
		);
		$temp = 'ownerlist';
		$this->_enum['levelmenuinc'] = $this->_getCashedList($temp);
		$this->_enum['levelmenuinc'][0] = array_merge(array(
			'#0'=>'# первый уровнь адреса',
			'#1'=>'# второй уровнь адреса',
			'#2'=>'# третий уровнь адреса',
			'#3'=>'# четвертый уровнь адреса',
			'#4'=>'# пятый уровнь адреса'),
			$this->_enum['levelmenuinc'][0]);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'typemenuinc', 'caption'=>'Тип вывода карты'),
			'1'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Уровень вывода данных'),
			'2'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[2],dirname(__FILE__));

	$DATA = array($FUNCPARAM[2]=>$PGLIST->getMap(-1,$FUNCPARAM[0],$FUNCPARAM[1]));
	$html .= $HTML->transformPHP($DATA,$tplphp);

	return $html;
