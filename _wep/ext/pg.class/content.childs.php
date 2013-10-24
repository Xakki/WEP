<?php
/**
 * Дочерний модуль "Контент страниц"
 * @author Xakki
 * @version 0.4.7
 */
class content_class extends kernel_extends
{

    function _set_features()
    {
        parent::_set_features();
        $this->mf_ordctrl = true;
        $this->mf_actctrl = true;
        $this->caption = 'Содержимое';
        $this->tablename = 'pg_content';
        $this->addForm = array();

    }

    function _create()
    {
        parent::_create();

        # fields
        $this->fields['marker'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'min' => '1', 'default' => 'text');
        $this->fields['href'] = array('type' => 'varchar', 'width' => 63, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['global'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['pagetype'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['funcparam'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['onajaxform'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['keywords'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['description'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['ugroup'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default' => '|0|');
        $this->fields['styles'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['script'] = array('type' => 'varchar', 'width' => 254, 'attr' => 'NOT NULL', 'default' => '');
        $this->fields['memcache'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['memcache_solt'] = array('type' => 'tinyint', 'width' => 1, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['access_flag'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['only_production'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '0');
        $this->fields['autocss'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '1');
        $this->fields['autoscript'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => '1');

        # memo
        //$this->memos['pg'] = array('max' => 50000);
        $this->fields['pg'] = array('type' => 'mediumtext', 'attr' => 'NOT NULL');

        $this->owner->_listname = 'name';
    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);
        # fields
        $this->fields_form['owner_id'] = array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'Родительская страница');
        $this->fields_form['name'] = array('type' => 'text', 'caption' => 'Подзаголовок', 'comment' => 'Используется некоторыми контроллерами при выводе контента или просто для обозначение данного контента в списке');
        $this->fields_form['marker'] = array('type' => 'list', 'listname' => 'marker', 'caption' => 'Маркер', 'comment' => 'Специальные позиции в HTML шаблоне', 'mask' => array());
        $this->fields_form['global'] = array('type' => 'checkbox', 'caption' => 'Сквозное', 'comment' => 'Отображать данный контент на всех страницах ниже уровнем?', 'mask' => array());
        $this->fields_form['pagetype'] = array('type' => 'list', 'listname' => 'pagetype', 'caption' => 'Контроллеры', 'mask' => array('onetd' => 'INC'));
        $this->fields_form['funcparam'] = array('type' => 'text', 'caption' => 'Опции', 'mask' => array('name' => 'all', 'onetd' => 'Опции'), 'comment' => 'Значения разделять символом &', 'css' => 'addparam');
        $this->fields_form['href'] = array('type' => 'text', 'caption' => 'Redirect', 'comment' => 'Принудительно перенапрявляет пользователя по указанному URL, если данный `контент` будет доступен пользователю', 'mask' => array('onetd' => 'close'));
        $this->fields_form['pg'] = array('type' => 'ckedit', 'caption' => 'Текст',
            'mask' => array('fview' => 1, 'max' => 500000),
            'paramedit' => array(
                'CKFinder' => array('allowedExtensions' => ''), // разрешаем загрузку любых фаилов
                'extraPlugins' => "'cntlen,syntaxhighlight,timestamp'",
                'toolbar' => 'Page',
            ));
        if ($form) {
            //TODO : сделать подключение стилей , которые подключены к этой странице (включая глобальные стили)
            //$this->fields_form['pg']['paramedit']['contentsCss'] = "['/_design/default/style/main.css', '/_design/_style/main.css']";
        }
        if ($this->_CFG['wep']['access'])
            $this->fields_form['ugroup'] = array('type' => 'list', 'multiple' => FORM_MULTIPLE_JQUERY, 'listname' => 'ugroup', 'caption' => 'Доступ', 'comment' => 'Если выбранны группы пользователей, то только им будет отображаться данный контент', 'def
		ault' => '0'); //'css'=>'minform'
        $this->fields_form['styles'] = array('type' => 'list', 'multiple' => FORM_MULTIPLE_JQUERY, 'listname' => 'style', 'caption' => 'CSS', 'mask' => array('onetd' => 'Дизайн')); //, 'css'=>'minform'
        $this->fields_form['script'] = array('type' => 'list', 'multiple' => FORM_MULTIPLE_JQUERY, 'listname' => 'script', 'caption' => 'SCRIPT', 'mask' => array('onetd' => 'none')); //, 'css'=>'minform'
        $this->fields_form['keywords'] = array('type' => 'text', 'caption' => 'SEO - ключевые слова', 'mask' => array('onetd' => 'none'));
        $this->fields_form['description'] = array('type' => 'text', 'caption' => 'SEO - описание', 'mask' => array('onetd' => 'close', 'name' => 'all'));

        $this->fields_form['onajaxform'] = array('type' => 'checkbox', 'caption' => 'Вкл. AjaxForm', 'mask' => array('onetd' => 'Опции'));
        $this->fields_form['access_flag'] = array('type' => 'checkbox', 'caption' => 'Не отображать на спец. страницах', 'comment' => 'Если скрипт на странице сгенерировал спец.флаг ($this->access_flag=true;) или выполняется AJAX запрос - данный контент не будет выполняться!', 'mask' => array('onetd' => 'none', 'fview' => 1));
        $this->fields_form['only_production'] = array('type' => 'checkbox', 'caption' => 'Production Only', 'comment' => 'Если вкл., то будет отображаться только если в настройках сайта "Production mode" включен', 'mask' => array('onetd' => 'none'));
        $this->fields_form['autocss'] = array('type' => 'checkbox', 'caption' => 'Auto Css', 'comment' => 'Подключать CSS автоматический?', 'mask' => array('onetd' => 'none'));
        $this->fields_form['autoscript'] = array('type' => 'checkbox', 'caption' => 'Auto Script', 'comment' => 'Подключать SCRIPTы автоматический?', 'mask' => array('onetd' => 'none'));
        $this->fields_form['memcache'] = array('type' => 'int', 'caption' => 'Memcache', 'comment' => 'Время кеширования , в сек. ; -1 - отключает кеш полностью, 0 - откл кеширование,1> - кеширование в сек.', 'mask' => array('onetd' => 'close'));
        $this->fields_form['memcache_solt'] = array('type' => 'list', 'listname' => 'memcache_solt', 'caption' => 'Memcache соль', 'mask' => array('fview' => 1));

        $this->fields_form['ordind'] = array('type' => 'int', 'caption' => 'ORD', 'comment' => 'Сортировка');
        $this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл');

        $this->formSort = array(
            'Основное' => array('marker', 'pagetype', 'funcparam'),
            'Контент' => array('pg', 'keywords', 'description'),
            'Дополнительно' => array('owner_id', 'name', 'href', 'ugroup', 'styles', 'script', 'memcache', 'memcache_solt', 'ordind', 'onajaxform', 'access_flag', 'only_production', 'global', 'autocss', 'autoscript'),
            'active',
        );

        $this->_enum['memcache_solt'] = array(
            0 => '---',
            1 => 'UserID',
            2 => 'SessionID',
            3 => 'COOKIE',
            4 => 'IP',
        );
    }

    function _getlist($listname, $value = NULL)
    {
        global $_CFG;
        $data = array();
        if ($listname == 'pagetype') {
            return $this->getInc();
        } elseif ($listname == 'ugroup') {
            return $this->owner->_getlist($listname, $value);
        } elseif ($listname == 'marker') {
            return $this->owner->config['marker'];
        } elseif ($listname == 'content') {
            return $this->getContentList();
        } else
            return parent::_getlist($listname, $value);
        /* else {
          return $this->owner->_getlist($listname,$value);
          } */
        return $data;
    }

    function getContentList()
    {
        $contentData = $this->qs('id as `#id#`,concat(id," - ",name," [",marker,"] ",pagetype) as `#name#`,"1" as `#checked#`,concat("p",owner_id) as oid', '', '#id#', 'oid');
        $vData = $this->owner->qs('concat("p",parent_id) as pid, concat("p",id) as `#id#`,name as `#name#`,"0" as `#checked#`', '', '#id#', 'pid');
        foreach ($contentData as $k => &$r) {
            if (isset($vData[$k])) {
                $vData[$k] = $r + $vData[$k];
            } else
                $vData[$k] = $r;
        }
        return $vData;
    }

    function getInc($pref = '.inc.php', $def = ' - Текст - ')
    {
        $data = array();
        $data[''][''] = $def;

        $dirList = array(
            '2' => $this->_CFG['_PATH']['inc'],
            '3' => $this->_CFG['_PATH']['ext'],
            '0' => $this->_CFG['_PATH']['wep_inc'],
            '1' => $this->_CFG['_PATH']['wep_ext'],
        );

        foreach ($dirList as $kDir => $rDir) {
            $dir = dir($rDir);
            while (false !== ($entry = $dir->read())) {
                if ($entry[0] != '.' && $entry[0] != '..') {
                    // Если тек. фаил это деректория, то смотрим внутри то что нам нужно
                    if (is_dir($rDir . $entry)) {
                        $mn = substr($entry, 0, -6);
                        if (isset($this->_CFG['modulprm'][$mn])) {
                            // Если модуль включен
                            $dir2 = dir($rDir . $entry);
                            while (false !== ($entry2 = $dir2->read())) {
                                if ($entry2[0] != '.' && $entry2[0] != '..' && strstr($entry2, $pref)) {

                                    $temp = substr($entry2, 0, strpos($entry2, $pref));

                                    $fi = static_tools::getDocFileInfo($rDir . $entry . '/' . $entry2);
                                    if (!$fi['type']) $fi['type'] = $this->owner->_enum['inc'][$kDir]['name'];
                                    if (!$fi['name']) $fi['name'] = $entry . '/' . $temp;

                                    if (!isset($data[$fi['type']]))
                                        $data[''][$fi['type']] = array('#name#' => $fi['type'], '#checked#' => 0);

                                    $data[$fi['type']] [$kDir . ':' . $entry . '/' . $temp] = array(
                                        '#name#' => $fi['name'],
                                        'info' => $fi,
                                    );
                                }
                            }
                            $dir2->close();
                        }
                    } elseif (strstr($entry, $pref)) {

                        $temp = substr($entry, 0, strpos($entry, $pref));

                        $fi = static_tools::getDocFileInfo($rDir . '/' . $entry);
                        if (!$fi['type']) $fi['type'] = $this->owner->_enum['inc'][$kDir]['name'];
                        if (!$fi['name']) $fi['name'] = $temp;

                        if (!isset($data[$fi['type']]))
                            $data[''][$fi['type']] = array('#name#' => $fi['type'], '#checked#' => 0);

                        $data[$fi['type']] [$kDir . ':' . $temp] = array(
                            '#name#' => $fi['name'],
                            'info' => $fi,
                        );
                    }
                }

            }
            $dir->close();
        }
        return $data;
    }

    public function kPreFields(&$f_data, &$f_param = array(), &$f_fieldsForm = null)
    {
        $mess = parent::kPreFields($f_data, $f_param, $f_fieldsForm);
        $this->addForm = array();
        $f_fieldsForm['pagetype']['onchange'] = 'contentIncParam(this,\'' . ADMIN_BH . '\',\'' . (isset($f_data['funcparam']) ? htmlspecialchars($f_data['funcparam']) : '') . '\');';

        if (isset($f_data['pagetype']) and $f_data['pagetype']) {
            $this->addForm = $this->getContentIncParam($f_data);
            if (count($this->addForm)) {
                $this->formSort['Основное'] = array_merge($this->formSort['Основное'], array_keys($this->addForm));

                $f_fieldsForm = static_main::insertInArray($f_fieldsForm, 'pagetype', $this->addForm); // обработчик параметров рубрики

                $f_fieldsForm['funcparam']['style'] = 'display:none;';
            }
        } else
            $f_fieldsForm['funcparam']['style'] = 'display:none;';
        return $mess;
    }

    public function getIncFile($typePG)
    {
        return $this->owner->getIncFile($typePG);
    }

    function getContentIncParam(&$rowPG, $ajax = false)
    {
        $formFlex = array();
        $FUNCPARAM = $this->owner->parserFlexData($rowPG['funcparam'], $rowPG['pagetype']);

        if ($flagPG = $this->getIncFile($rowPG['pagetype'])) {
            if (count($_POST) != count($rowPG) or $ajax) {
                $flagSetvalue = true;
            } else
                $flagSetvalue = false;
            //Проверяем есть ли в коде флексформа
            $fileDoc = static_tools::getDocFileInfo($flagPG);
            if ($fileDoc['ShowFlexForm']) {
                $ShowFlexForm = true;
                global $_CFG, $_tpl;
                $tempform = include($flagPG);
                if (is_array($tempform) and count($tempform)) {
                    foreach ($tempform as $k => $r) {
                        if ($flagSetvalue) {
                            $r['value'] = $rowPG['flexform_' . $k] = $FUNCPARAM[$k];
                        }
                        $r['css'] = 'addparam flexform'; // Добавляем форме спец стиль (завязано на скриптах)
                        $formFlex['flexform_' . $k] = $r;
                    }
                } else {
                    $formFlex['tr_flexform_0'] = array('type' => 'info', 'css' => 'addparam flexform', 'caption' => '<span class="error">Ошибка в коде. Обрботчик страниц "' . $flagPG . '" вернул не верные данные!</span>');
                }
            } else {

            }
        } elseif (!$pagetype) {
        } else {
            $formFlex['tr_flexform_0'] = array('type' => 'info', 'css' => 'addparam flexform', 'caption' => '<span class="error">Ошибка выбра данных. Обрботчик страниц "' . $flagPG . '" не найден!</span>');
        }

        return $formFlex;
    }

    public function _update($vars = array(), $where = null, $flag_select = true)
    {
        $vars = $this->SetFuncparam($vars);
        if ($ret = parent::_update($vars, $where, $flag_select)) {
            // в форме для вывода обрабатываем данные
            if ($flag_select and isset($this->data[$this->id]['funcparam']))
                $this->data[$this->id] += $this->owner->parserFlexData($this->data[$this->id]['funcparam'], $this->data[$this->id]['pagetype'], true);
        }
        return $ret;
    }

    public function _add($data = array(), $flag_select = true, $flag_update = false)
    {
        $data = $this->SetFuncparam($data);
        if ($ret = parent::_add($data, $flag_select, $flag_update)) {
            // в форме для вывода обрабатываем данные
            if ($flag_select and isset($this->data[$this->id]['funcparam']))
                $this->data[$this->id] += $this->owner->parserFlexData($this->data[$this->id]['funcparam'], $this->data[$this->id]['pagetype'], true);
        }
        return $ret;
    }

    /*
    * Перед записью в БД массив funcparam, кодируем в строку
    * - по сути можно было бы использоваться serialize, но он немного избыточен по длине сроки, хотя и удобен
    */
    private function SetFuncparam($vars)
    {
        $funcparam = array();
        if (count($this->addForm)) {
            foreach ($this->addForm as $k => $r) {
                if ($r['type'] != 'info') {
                    $key = (int)substr($k, 9);
                    if (is_array($vars[$k]))
                        $funcparam[$key] = implode('|', $vars[$k]);
                    else
                        $funcparam[$key] = $vars[$k];
                }
            }
            if (count($funcparam)) {
                ksort($funcparam);
                $vars['funcparam'] = implode('&', $funcparam);
            }
        }
        return $vars;
    }

    public function fFormCheck(&$DATA, &$param, &$argForm)
    {
        $RESULT = parent::fFormCheck($DATA, $param, $argForm);
        if (mb_strlen($DATA['funcparam']) > 255)
            $RESULT['mess'][] = static_main::am('error', 'Длина строки параметров `funcparam` превысила 255 символов.');
        return $RESULT;
    }
}

