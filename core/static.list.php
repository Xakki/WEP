<?php

class static_list
{
    /**
     * проверка выбранных данных из списка
     * @param mixed $listname - название списока или массив данных для списка
     * @param mixed $value - значение
     * @return array Список
     */
    static function _checkList($_this, &$listname, $value = NULL)
    {
        $templistname = $listname;
        if (is_array($listname)) $templistname = implode(',', $listname);
        $templistname = $_this->_cl . '_' . $templistname;

        if (!isset($_this->_CFG['enum_check'][$templistname])) {
            if (!isset($_this->_CFG['enum'][$templistname])) {
                $data = & $_this->_getCashedList($listname, $value); // , $value
                //$_this->_CFG['enum'][$templistname]
            } else $data = & $_this->_CFG['enum'][$templistname];

            if (!is_array($data) or !count($data)) return false;

            // Скорее всего вскоре этот блок будет лишним ,
            // по идее _checkList всегжа должен иметь $value
            // и _getCashedList выдает готовый рез-тат
            $temp = self::fix_checklist($data);

            if (is_null($value)) // не кешируем если не задано значение и  or !is_array($listname) $listname - выборка из БД(в массиве)
                $_this->_CFG['enum_check'][$templistname] = $temp;
        } else {
            $temp = & $_this->_CFG['enum_check'][$templistname];
        }

        if (is_array($value)) {
            $return_value = array();
            foreach ($value as $r) {
                if (isset($temp[$r])) $return_value[] = $temp[$r];
            }
            if (count($return_value) == count($value)) return $return_value;
        } elseif (isset($temp[$value])) {
            return $temp[$value];
        }
        return false;
    }

    // fix list data
    static function fix_checklist($data)
    {
        $temp2 = array();
        $temp = current($data);
        if (is_array($temp) and !isset($temp['#name#'])) {
            foreach ($data as $krow => $row) {
                if (isset($temp2[$krow])) {
                    if (is_array($temp2[$krow]) and isset($temp2[$krow]['#name#'])) $adname = $temp2[$krow]['#name#'];
                    else $adname = $temp2[$krow];
                    foreach ($row as $kk => $rr) {
                        if (is_array($rr)) {
                            if (isset($rr['#name#'])) $rr = $rr['#name#'];
                            else $rr = implode(' / ', $rr);
                        }
                        $row[$kk] = $adname . ' - ' . $rr;
                    }
                    if (is_array($temp2[$krow]) and isset($temp2[$krow]['#checked#'])) unset($temp2[$krow]);
                }
                $temp2 += $row;
            }
            $temp = & $temp2;
        } else {
            $temp = & $data;
        }
        return $temp;
    }

    /**
     * Получение списка из кеша если он там есть
     * @param $_this    kernel_extends
     * @param $listname array   название списока или массив данных для списка
     * @param null $value mixed  значение
     * @return array Список
     */
    static function &_getCashedList($_this, $listname, $value = NULL)
    {
        $templistname = $listname;
        if (is_array($listname)) $templistname = implode(',', $listname);
        $templistname = $_this->_cl . '_' . $templistname;

        if (!is_null($value)) { // не кешируем если задано $value и $listname - выборка из таблиц(задается массивом)
            $data = $_this->_getlist($listname, $value);
            $data = self::uarray_intersect_key($data, $value);
            return $data;
        } elseif (!isset($_this->_CFG['enum'][$templistname])) $_this->_CFG['enum'][$templistname] = $_this->_getlist($listname, $value);

        return $_this->_CFG['enum'][$templistname];
    }

    /**
     * Выводит массив из элеменов первой переменной, по совпавшим ключам из второй переменной
     */
    static function uarray_intersect_key(array $data, $value)
    {
        // VALUE
        if (!is_array($value)) $tvalue = array($value => $value);
        else $tvalue = array_combine($value, $value);

        $temp = current($data);
        if (!is_array($temp) or isset($temp['#name#'])) {
            return array_intersect_key($data, $tvalue);
        } else {
            $tdata = array();
            foreach ($data as $r) {
                $tdata += array_intersect_key($r, $tvalue);
            }
            return $tdata;
        }
    }

    /**
     * фОРМИРУЕТ стандартные списки и списки по заднанным параметрам
     * @param $_this kernel_extends
     * @param $listname
     * @param null $value
     * @return array
     */
    static function &_getlist($_this, &$listname, $value = NULL) /*LIST SELECTOR*/
    {
        /*Выдает 1 уровневый массив, либо 2х уровневый для структуры типа дерева*/
        /*Конечный уровень может быть с елементами массива #name# итп, этот уровень в счет не входит*/
        $data = array();
        $templistname = $listname;
        if (is_array($listname)) {
            if (isset($listname[0])) $templistname = $listname[0];
            else $templistname = implode(',', $listname);
        }

        if (isset($_this->_enum[$templistname])) {
            return $_this->_enum[$templistname];
        } elseif ($templistname == 'count') {
            if (!$listname[1]) $listname[1] = 1;
            if (!$listname[2]) $listname[2] = 20;
            for ($i = $listname[1]; $i <= $listname[2]; $i++) {
                $data[$i] = $i;
            }
        } elseif ($templistname == 'classList') {
            $data = array('' => ' --- ');
            foreach ($_this->_CFG['modulprm'] as $k => $r) $data[$k] = $r['name'];
        } elseif ($templistname == 'child.class') {
            $dir = array();
            if (file_exists($_this->_CFG['_PATH']['ext'] . $_this->_cl . '.class')) $dir[''] = $_this->_CFG['_PATH']['ext'] . $_this->_cl . '.class';
            if (file_exists($_this->_CFG['_PATH']['wep_ext'] . $_this->_cl . '.class')) $dir['Ядро - '] = $_this->_CFG['_PATH']['wep_ext'] . $_this->_cl . '.class';
            $data = array('' => ' --- ');
            foreach ($dir as $k => $r) {
                $odir = dir($r);
                while (false !== ($entry = $odir->read())) {
                    if (substr($entry, -11) == '.childs.php') {
                        $entry = substr($entry, 0, -11);
                        $data[$entry] = $k . $entry;
                    }
                }
                $odir->close();
            }
        } elseif ($templistname == 'phptemplates') {
            // вызов только для PG
            $data[''][''] = ' - ';

            // Системные модули
            $dir = dir($_this->_CFG['_PATH']['wep_ext']);
            while (false !== ($entry = $dir->read())) {
                if (_strpos($entry, '.class') !== false) {
                    $key = _substr($entry, 0, -6);
                    $dir2 = $_this->_CFG['_PATH']['wep_ext'] . $entry . '/_design/php';
                    if (file_exists($dir2) and is_dir($dir2)) {
                        $dir2Obj = dir($dir2);
                        while (false !== ($entry2 = $dir2Obj->read())) {
                            if (mb_strstr($entry2, '.php')) {
                                $docs = static_tools::getDocFileInfo($dir2 . '/' . $entry2);

                                if (!$docs['type']) $docs['type'] = $entry;
                                if (!$docs['name']) $docs['name'] = $entry2;

                                if (!isset($data[$docs['type']])) $data[''][$docs['type']] = array('#name#' => $docs['type'], '#checked#' => 0);

                                // Определяем совместимость шаблонов
                                if (isset($listname['tags'])) {
                                    // todo - suport multiple tag
                                    if (!$docs['tags']) $docs['#css#'] = 'notags';
                                    elseif ($docs['tags'] != $listname['tags']) $docs['#css#'] = 'nosupport';
                                    else $docs['#css#'] = 'support';
                                }
                                $docs['#name#'] = $docs['name'];

                                $data[$docs['type']]['#' . $key . '#' . _substr($entry2, 0, -4)] = $docs;
                            }
                        }
                        $dir2Obj->close();
                    }
                }
            }
            $dir->close();

            // Пользовательские модули
            $dir = dir($_this->_CFG['_PATH']['ext']);
            while (false !== ($entry = $dir->read())) {
                if (strpos($entry, '.class') !== false) {
                    $key = substr($entry, 0, -6);
                    $dir2 = $_this->_CFG['_PATH']['ext'] . $entry . '/_design/php';
                    if (file_exists($dir2) and is_dir($dir2)) {
                        $dir2Obj = dir($dir2);
                        while (false !== ($entry2 = $dir2Obj->read())) {
                            if (strstr($entry2, '.php')) {
                                $docs = static_tools::getDocFileInfo($dir2 . '/' . $entry2);

                                if (!$docs['type']) $docs['type'] = $entry;
                                if (!$docs['name']) $docs['name'] = $entry2;

                                if (!isset($data[$docs['type']])) $data[''][$docs['type']] = array('#name#' => $docs['type'], '#checked#' => 0);

                                // Определяем совместимость шаблонов
                                if (isset($listname['tags'])) {
                                    // todo - suport multiple tag
                                    if (!$docs['tags']) $docs['#css#'] = 'notags';
                                    elseif ($docs['tags'] != $listname['tags']) $docs['#css#'] = 'nosupport';
                                    else $docs['#css#'] = 'support';
                                }
                                $docs['#name#'] = $docs['name'];

                                $data[$docs['type']]['#' . $key . '#' . _substr($entry2, 0, -4)] = $docs;
                            }
                        }
                        $dir2Obj->close();
                    }
                }
            }
            $dir->close();

            // Дизайн шаблоны
            _new_class('pg', $PGLIST);
            if (file_exists($PGLIST->_CFG['_PATH']['themes'] . 'default/php')) {
                $dir = $PGLIST->_CFG['_PATH']['themes'] . 'default/php';
                $dirObj = dir($dir);
                while (false !== ($entry = $dirObj->read())) {
                    if (strstr($entry, '.php')) {
                        $docs = static_tools::getDocFileInfo($dir . '/' . $entry);

                        if (!$docs['type']) $docs['type'] = $PGLIST->config['design'];
                        if (!$docs['name']) $docs['name'] = $entry;

                        if (!isset($data[$docs['type']])) $data[''][$docs['type']] = array('#name#' => $docs['type'], '#checked#' => 0);

                        // Определяем совместимость шаблонов
                        if (isset($listname['tags'])) {
                            // todo - suport multiple tag
                            if (!$docs['tags']) $docs['#css#'] = 'notags';
                            elseif ($docs['tags'] != $listname['tags']) $docs['#css#'] = 'nosupport';
                            else $docs['#css#'] = 'support';
                        }
                        $docs['#name#'] = $docs['name'];

                        $data[$docs['type']][substr($entry, 0, -4)] = $docs;
                    }
                }
                $dirObj->close();
            }

            // Совместимость со старой версией
            // TODO - clear this code
            global $FUNCPARAM_FIX;
            $f = $value . '/templates';
            if ($FUNCPARAM_FIX and count($FUNCPARAM_FIX) and file_exists($f)) {
                $temp = basename($value);
                $temp = substr($temp, 0, -6);
                print_r('<p class="alert">Данные старой версии - срочно сохраните форму!<p>');
                foreach ($FUNCPARAM_FIX as &$rff) {
                    $rff = str_replace('#ext#', '#' . $temp . '#', $rff);
                }
                unset($rff);
            }
        } elseif ($listname == 'themes') {
            // вызов только для PG
            $data[''] = ' - По умолчанию -';
            $dir = dir($_this->_CFG['_PATH']['themes']);
            if ($dir) {
                while (false !== ($entry = $dir->read())) {
                    if ($entry[0] != '.' && $entry[0] != '..' && $entry{
                        0
                        } != '_'
                    ) {
                        $data[$entry] = $entry;
                    }
                }
                $dir->close();
            }
        } elseif ($listname == 'templates') {
            $data[''] = ' - По умолчанию -';
            $dir = dir($_this->_CFG['_PATH']['themes'] . 'default/templates');
            while (false !== ($entry = $dir->read())) {
                if (strstr($entry, '.tpl')) {
                    $entry = substr($entry, 0, strpos($entry, '.tpl'));
                    if (isset($data[$entry])) $data[$entry] = $entry;
                    else $data[$entry] = $entry;
                }
            }
            $dir->close();
        } elseif ($listname == 'style') {
            // вызов только для PG
            $dir = dir($_this->_CFG['_PATH']['themes'] . 'default/style');
            while (false !== ($entry = $dir->read())) {
                if (strpos($entry, '.css')) {
                    $entry = substr($entry, 0, -4);
                    $data['']['#themes#' . $entry] = '*' . $entry;
                }
            }
            $dir->close();

            $afterSubDir = array();
            $dir = dir($_this->_CFG['_PATH']['_style']);
            while (false !== ($entry = $dir->read())) {
                if (strpos($entry, '.css')) {
                    $entry = substr($entry, 0, -4);
                    $data[''][$entry] = $entry;
                } elseif (strpos($entry, 'style.') === 0) {
                    $afterSubDir[$entry] = array('#name#' => $entry, '#checked#' => 0);
                    $dir2 = dir($_this->_CFG['_PATH']['_style'] . '/' . $entry);
                    while (false !== ($entry2 = $dir2->read())) {
                        if (strpos($entry2, '.css')) {
                            $entry2 = substr($entry2, 0, -4);
                            $data[$entry][$entry . '/' . $entry2] = $entry . '/' . $entry2;
                        }
                    }
                    $dir2->close();
                }
            }
            $dir->close();
            if (count($afterSubDir)) $data[''] = $data[''] + $afterSubDir;
        } elseif ($templistname == "script") {
            // вызов только для PG
            $dir = dir($_this->_CFG['_PATH']['themes'] . 'default/script');
            while (false !== ($entry = $dir->read())) {
                if (strpos($entry, '.js')) {
                    $entry = substr($entry, 0, -3);
                    $data['']['#themes#' . $entry] = '*' . $entry;
                }
            }
            $dir->close();

            $afterSubDir = array();
            $dir = dir($_this->_CFG['_PATH']['_script']);
            while (false !== ($entry = $dir->read())) {
                if (strpos($entry, '.js')) {
                    $entry = substr($entry, 0, -3);
                    $data[''][$entry] = $entry;
                } elseif (strpos($entry, 'script.') === 0) {
                    $afterSubDir[$entry] = array('#name#' => $entry, '#checked#' => 0);
                    $dir2 = dir($_this->_CFG['_PATH']['_script'] . '/' . $entry);
                    while (false !== ($entry2 = $dir2->read())) {
                        if (strpos($entry2, '.js')) {
                            $entry2 = substr($entry2, 0, -3);
                            $data[$entry][$entry . '/' . $entry2] = $entry . '/' . $entry2;
                        }
                    }
                    $dir2->close();
                }
            }
            $dir->close();
            if (count($afterSubDir)) $data[''] = $data[''] + $afterSubDir;
        } elseif ('fieldslist' == $templistname) {
            $data['id'] = '№';
            foreach ($_this->fields_form as $k => $r) {
                if ($_this->fields_form[$k]['caption']) $data[$k] = $_this->fields_form[$k]['caption'];
            }
        } elseif ('list' == $templistname) {
            $q_where = array();
            $q_order = '';

            $name = 'id, ' . $_this->_listname . ' as name';
            if ($_this->mf_istree) {
                $name .= ', ' . $_this->mf_istree;
                if ($_this->mf_istree_root) {
                    if (!isset($_this->data[$_this->id][$_this->ns_config['root']])) {
                        if (isset($_this->data[$_this->id])) {
                            trigger_error('Ошибка в БД. Отсутствует необходимый параметр "root_key"', E_USER_WARNING);
                        }
                    } else {
                        $q_where[] = $_this->ns_config['root'] . '=' . (int)$_this->data[$_this->id][$_this->ns_config['root']];
                    }
                }
            }

            if ($_this->ordfield) $q_order = ' ORDER BY ' . $_this->ordfield;

            if (isset($_this->owner->id) and $_this->owner->id) // либо по owner id
                $q_where[] = $_this->owner_name . ' IN (' . $_this->owner->_id_as_string() . ')';

            if (count($q_where)) $q_where = ' WHERE ' . implode(' and ', $q_where);
            else $q_where = ' ';

            $result = $_this->SQL->execSQL('SELECT ' . $name . ' FROM `' . $_this->tablename . '`' . $q_where . $q_order);

            if (!$result->err) {
                if ($_this->mf_istree) {
                    if ($_this->mf_use_charid) $data[''][''] = static_main::m('_zeroname', $_this);
                    else $data[0][0] = static_main::m('_zeroname', $_this);

                    while (list($id, $name, $pid) = $result->fetch_row()) {
                        $data[$pid][$id] = ($name ? $name : $_this->caption . ' #' . $id);
                    }
                } else {
                    if ($_this->mf_use_charid) $data[''] = static_main::m('_zeroname', $_this);
                    else $data[0] = static_main::m('_zeroname', $_this);

                    while (list($id, $name) = $result->fetch_row()) $data[$id] = ($name ? $name : $_this->caption . ' #' . $id);
                }
            }
        } elseif ('select' == $templistname) {
            trigger_error('Использование списка `select` переделать на `list`', E_USER_WARNING);
            $data = $_this->_select();
        } elseif ('parentlist' == $templistname and $_this->mf_istree) {
            $data = array();
            if ($_this->mf_use_charid) $data[''][''] = static_main::m('_zeroname', $_this);
            else $data[0][0] = static_main::m('_zeroname', $_this);

            $q = 'SELECT `id`, `name`, `parent_id` FROM `' . $_this->tablename . '`';
            $w = [];
            if ($_this->id) $w[] = '`id`!="' . $_this->_id_as_string() . '"';

            if (isset($_this->tree_data) and count($_this->tree_data)) {
                $parents = implode(',', array_column($_this->tree_data, 'parent_id'));
                $w[] = '(`parent_id` in (' . $parents . ') or id in (' . $parents . '))';
            } else {
                $w[] = '(`parent_id`=' . ($_this->parent_id ? $_this->parent_id : 0) . ' '
                    . ($_this->data ? ' or (`left_key`<' . $_this->data[$_this->id][$_this->ns_config['left']] . ' and `right_key`>' . $_this->data[$_this->id][$_this->ns_config['right']] . ')' : '') . ')';
            }

            if (count($w)) {
                $q .= 'WHERE ' . implode(' and ', $w);
            }
            if ($_this->mf_ordctrl) $q .= ' ORDER BY ' . $_this->mf_ordctrl;
            $result = $_this->SQL->execSQL($q);
            if (!$result->err)
                while (list($id, $name, $pid) = $result->fetch_row()) {
                    $data[$pid][$id] = $name;
                }
        } elseif (is_array($listname) and isset($listname[0]) and isset($listname[1]) and 'owner' == $listname[0]) {
            $data = $_this->owner->_getlist($listname[1], $value);
        } elseif ('ownerlist' == $templistname) {
            // TODO : это Кастыль совместимости
            if ($_this->owner) $data = $_this->owner->_getlist('list', $value);
            else $data = array(
                'Ошибка - список ownerlist не может быть создан, так как родитель не доступен'
            );
        } elseif (is_array($listname) and (isset($listname['class']) or isset($listname['tablename']))) {
            $clause = array();
            if (isset($listname['class'])) {
                $listname['tablename'] = static_main::getTableNameOfClass($listname['class']);
            }

            if (!isset($listname['idField'])) $listname['idField'] = 'tx.id';
            if (!isset($listname['nameField'])) $listname['nameField'] = 'tx.name';

            if (isset($listname['leftJoin'])) {
                $clause['from'] = ' FROM `' . $_this->tablename . '` t1 LEFT JOIN `' . $listname['tablename'] . '` tx ON ' . $listname['idField'] . '=t1.' . $listname['idThis'];
                $clause['field'] = 'SELECT t1.' . $listname['idThis'] . ' as id,' . $listname['nameField'] . ' as name';
            } elseif (isset($listname['join'])) {
                $clause['from'] = ' FROM `' . $_this->tablename . '` t1 JOIN `' . $listname['tablename'] . '` tx ON ' . $listname['idField'] . '=t1.' . $listname['idThis'] . ' ' . $listname['join'];
                $clause['field'] = 'SELECT t1.' . $listname['idThis'] . ' as id,' . $listname['nameField'] . ' as name';
            } else {
                $clause['from'] = ' FROM `' . $listname['tablename'] . '` tx ';
                $clause['field'] = 'SELECT ' . $listname['idField'] . ' as id,' . $listname['nameField'] . ' as name';
            }

            if (isset($listname['is_tree'])) {
                if ($listname['is_tree'] === true) $clause['field'] .= ', tx.parent_id as parent_id';
                else $clause['field'] .= ', ' . $listname['is_tree'] . ' as parent_id';
            }
            if (isset($listname['is_checked'])) $clause['field'] .= ', tx.checked as checked';

            if (!isset($listname['where'])) $listname['where'] = array();
            elseif (!is_array($listname['where'])) $listname['where'] = array($listname['where']);

            /*Выбранные элементы*/
            if (!is_null($value)) {
                if (is_array($value)) $listname['where'][] = $listname['idField'] . ' IN ("' . implode('", "', $value) . '")';
                else $listname['where'][] = $listname['idField'] . '="' . $value . '"';
            }

            if (count($listname['where'])) $listname['where'] = ' WHERE ' . implode(' and ', $listname['where']);
            else $listname['where'] = '';

            if (isset($listname['leftJoin']) and $listname['idThis']) $listname['where'] .= ' GROUP BY t1.' . $listname['idThis'];
            if (isset($listname['ordfield']) and $listname['ordfield']) $listname['where'] .= ' ORDER BY ' . $listname['ordfield'];

            if (isset($listname['zeroname'])) $_zeroname = $listname['zeroname'];
            else $_zeroname = static_main::m('_zeroname', $_this);

            $result = $_this->SQL->execSQL($clause['field'] . $clause['from'] . $listname['where']);
//print($_this->SQL->query);
            if (!$result->err) {
                if (!is_null($value) and is_array($value) and count($value)) {
                    while ($row = $result->fetch()) $data[$row['id']] = $row['name'];
                } elseif (!is_null($value)) {
                    if ($row = $result->fetch()) $data[$row['id']] = $row['name'];
                } elseif (isset($listname['is_tree']) and $listname['is_tree']) {
                    while ($row = $result->fetch()) {
                        if (!isset($row['checked'])) $row['checked'] = true;
                        $data[$row['parent_id']][$row['id']] = array('#name#' => $row['name'], '#checked#' => $row['checked']);
                    }
                    if (isset($data[0])) $def = 0;
                    else $def = '';
                    if ($_zeroname) $data[$def] = static_main::MergeArrays(array($def => $_zeroname), $data[$def]);
                } else {
                    if ($_zeroname) $data[''] = $_zeroname;
                    while ($row = $result->fetch()) $data[$row['id']] = $row['name'];
                }
            }
            return $data; // Потому что тут уже обрабатывается $value
        } elseif (!is_array($listname)) {
            static_main::log('error', 'List data `' . $listname . '` not found');
        } else {
            static_main::log('error', 'List ' . current($listname) . ' not found');
        }

        return $data;
    }

    /**
     * Преобразование списка в шаблонный список для формы
     * @param array $path - путь
     * @return string XML
     */
    static function _forlist(&$data, $id = 0, $select = '', $multiple = 0) /*LIST SELECTOR*/
    {
        $upsel = 0;
        /*
		  array('name'=>'NAME','id'=>1 [, 'sel'=>0, 'checked'=>0])
		 */
        //$select - array(значение=>1)
        $s = array();
        if (!is_array($data) or !count($data)) return $s;

        if ($multiple == FORM_MULTIPLE_JQUERY and is_array($select) and count($select)) {
            foreach ($select as $sr) {
                foreach ($data as $kk => $kd) {
                    if (isset($kd[$sr])) {
                        $s[$sr] = array('#id#' => $sr, '#sel#' => 1);
                        if (is_array($kd[$sr]) and isset($kd[$sr]['#name#'])) $s[$sr]['#name#'] = $kd[$sr]['#name#'];
                        else $s[$sr]['#name#'] = $kd[$sr];
                        break;
                    }
                }
            }
            $multiple = 'is temp key';
        }

        if (isset($data[$id]) and is_array($data[$id]) and count($data[$id])) $temp = & $data[$id];
        else $temp = & $data;

        foreach ($temp as $key => $value) {
            $sel = 0;
            if ($select != '') {
                if (is_array($select)) {
                    if (isset($select[$key])) {
                        if ($multiple === 'is temp key') continue;
                        $sel = 1;
                    } else $sel = 0;
                } elseif ($select == $key) {
                    $sel = 1;
                }
            }

            $s[$key] = array('#id#' => $key, '#sel#' => $sel);
            if (is_array($value)) {
                foreach ($value as $k => $r) {
                    // чтоб задавать ID
//					if ($k != '#name#' and $k != '#id#') {
                    $s[$key][$k] = $r;
//                    }
                }
                if (!isset($value['#name#'])) $s[$key]['#name#'] = $key;
//				else
//					$s[$key]['#name#'] = $value['#name#']; //_substr($value['name'],0,60).(_strlen($value['name'])>60?'...':'')
            } else {
                $s[$key]['#name#'] = $value;
            }

            if ($key != $id and isset($data[$key]) and is_array($data[$key]) and count($data[$key])) {
                list($s[$key]['#item#'], $sel2) = self::_forlist($data, $key, $select, $multiple);
                if ($sel2) {
                    $s[$key]['#sel#'] = $sel2;
                }
            }

            if ($s[$key]['#sel#']) {
                $upsel = $s[$key]['#sel#'];
            }
            /*Если это использовать то проверка данных сломается*/
            //if (isset($value['#item#']) and is_array($value['#item#']) and count($value['#item#']))
            //	$s[$key]['#item#'] = $value['#item#']+$s[$key]['#item#'];
        }
        return array($s, $upsel);
    }

    static function getTreeData(&$data, $key = 0, $nm = '#item#')
    {
        $treeData = [];
        foreach ($data[$key] as $ki => $ri) {
            if (isset($data[$ki])) {
                $ri[$nm] = self::getTreeData($data, $ki, $nm);
            }
            $treeData[$ki] = $ri;
        }
        return $treeData;
    }
}
