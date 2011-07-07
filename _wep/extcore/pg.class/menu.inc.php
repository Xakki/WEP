<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 1;
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 1;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';//Показывать меню начиная с уровня ID page {id page, #1 - использовать id первого уровня адреса, #2 итп}
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 'menu';

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['typemenuinc'] = array(
			0=>'выводит всё в виде структуры дерева',
			1=>'выводит все в общем массиве',
			2=>'только начальный уровень от `Уровень вывода данных`',
		);
		$temp = 'ownerlist';
		$this->_enum['levelmenuinc'] = $this->_getCashedList($temp);
		$this->_enum['levelmenuinc'][''] = array_merge(array(
			'#0'=>'# первый уровнь адреса',
			'#1'=>'# второй уровнь адреса',
			'#2'=>'# третий уровнь адреса',
			'#3'=>'# четвертый уровнь адреса',
			'#4'=>'# пятый уровнь адреса'),
			$this->_enum['levelmenuinc']['']);
		$form = array(
			'0'=>array('type'=>'list','listname'=>array('owner','menu'), 'caption'=>'Меню'),
			'1'=>array('type'=>'list','listname'=>'typemenuinc', 'caption'=>'Тип вывода меню'),
			'2'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Уровень вывода данных'),
			'3'=>array('type'=>'text','caption'=>'Шаблон'),
		);
		return $form;
	}

	// команды вывода данных для обработчика на с айте
	 // все действия в этой части относительно модуля pg
	if($FUNCPARAM[2] and $FUNCPARAM[2]{0}=='#') {
		$tmp = array_values(array_reverse($this->selected));
		$FUNCPARAM[2] = $tmp[(int)substr($FUNCPARAM[2],1)];
	}
	$DATA = array($FUNCPARAM[3]=>$PGLIST->getMap($FUNCPARAM[0],$FUNCPARAM[1],$FUNCPARAM[2]));
	$html .= $HTML->transformPHP($DATA,$FUNCPARAM[3]);

	return $html;
