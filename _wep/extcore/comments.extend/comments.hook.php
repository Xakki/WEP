<?php
function ugroup_hook__create($_this,$arg=array()) {
	if($_this->config['karma']) {
		$_this->fields['maxcomment'] = array('type' => 'int', 'width' =>8, 'attr' => 'NOT NULL', 'default' => 5);
		$_this->fields['maxcommenttime'] = array('type' => 'int', 'width' =>8, 'attr' => 'NOT NULL', 'default' => 60);

		$_this->fields_form['maxcomment'] = array('type' => 'int', 'caption' => 'Максимум комментариев в период времени', 'mask' =>array('fview'=>1));
		$_this->fields_form['maxcommenttime'] = array('type' => 'int', 'caption' => 'Период времени контроля (сек)', 'mask' =>array('fview'=>1));
	}
}