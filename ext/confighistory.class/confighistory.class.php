<?php

class confighistory_class extends kernel_extends
{

    function init()
    {
        parent::init();
        $this->default_access = '|9|';
        $this->mf_timecr = true; // создать поле хранящее время создания поля

        $this->caption = 'История настроек';
        $this->comment = 'Различные варианты конфигураций модулей';
        $this->ver = '0.1';
    }


    protected function _create()
    {
        parent::_create();
        $this->fields['conf'] = array('type' => 'text', 'attr' => 'NOT NULL');
        $this->fields['modul'] = array('type' => 'varchar', 'width' => 64, 'attr' => 'NOT NULL');
    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);
        $this->fields_form['name'] = array('type' => 'text', 'caption' => 'Название', 'mask' => array('min' => 3, 'fview' => 2));
        $this->fields_form['conf'] = array('type' => 'hidden', 'caption' => 'Конфиг', 'mask' => array('min' => 3));
        $this->fields_form['modul'] = array('type' => 'list', 'listname' => 'classList', 'caption' => 'Модуль', 'mask' => array(), 'relationForm' => 'relationFormModul');
    }

    protected function relationFormModul($val, &$my_fieldsForm)
    {
        if (_new_class($val, $Modul) and count($Modul->config_form)) {

            if ($this->id)
                $conf = json_decode($my_fieldsForm['conf']['value'], true);

            foreach ($Modul->config_form as $k => $r) {
                if (isset($Modul->config[$k]))
                    $my_fieldsForm['config_' . $k] = array(
                        'caption' => $r['caption'],
                        'type' => $r['type'],
                        'value' => (!$this->id ? $Modul->config[$k] : $conf[$k]),
                        'css' => 'addparam');
            }
        }

    }


    public function fFormCheck(&$data, &$param, &$FORMS)
    {
        if (_new_class($data['modul'], $Modul) and count($Modul->config_form)) {
            $temp = array();
            foreach ($Modul->config_form as $k => $r) {
                if (isset($data['config_' . $k]))
                    $temp[$k] = $data['config_' . $k];
            }
            $data['conf'] = json_encode($temp);
            $data['name'] = $Modul->caption . '[' . date('Y-m-d') . ']';
        }

        $arr = parent::fFormCheck($data, $param, $FORMS);

        return $arr;
    }

    public function setConfig($id)
    {
        $mess = array();
        $this->id = (int)$id;
        $data = $this->_select();
        if (count($data)) {
            $data = current($data);
            if (_new_class($data['modul'], $Modul) and count($Modul->config_form)) {
                $conf = json_decode($data['conf'], true);

                $params = array();
                $arr = $Modul->fFormCheck($conf, $params, $Modul->config_form); // 2ой параметр просто так
                $config = array();
                $configPrint = '<h3>Текущие настройки</h3><table border="1">';
                foreach ($Modul->config as $k => $r) {
                    if (isset($arr['vars'][$k])) {
                        $Modul->config_form[$k]['value'] = $arr['vars'][$k];
                        $config[$k] = $arr['vars'][$k];
                        $configPrint .= '<tr><td>' . $Modul->config_form[$k]['caption'] . '<td>' . $config[$k];
                    }
                }
                $configPrint .= '</table>';

                $Modul->config = $config;
                if (!count($arr['mess'])) {
                    $mess[] = static_main::am('ok', 'update', $Modul);
                    $mess[] = static_main::am('txt', $configPrint);
                    static_tools::_save_config($config, $Modul->_file_cfg);
                } else
                    $mess = $arr['mess'];
            } else
                $mess[] = static_main::am('error', 'Ошибка модуля ' . $data['modul']);
        } else
            $mess[] = static_main::am('error', 'Данные по вашему запросы отсутствуют.');

        return $mess;
    }
}


