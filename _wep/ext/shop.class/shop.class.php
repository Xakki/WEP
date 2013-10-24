<?php
/** Каталог товаров (интеренет магазин)
 *
 * Для включения "Корзины" достаточно подключить INC basket(Корзина)
 * Варианта заказа : форма письма, онлайн покупка через платежные системы
 *
 *
 */
class shop_class extends rubric_class
{

    protected function _create_conf()
    { /*CONFIG*/
        parent::_create_conf();


        $this->config['orderset'] = array(0 => '0');
        $this->config['available'] = 1;

        $this->config_form['orderset'] = array('type' => 'list', 'listname' => 'orderset', 'multiple' => FORM_MULTIPLE_SIMPLE, 'caption' => 'Варианты заказа товара');
        $this->config_form['yml_info'] = array('type' => 'html', 'value' => 'Ссылка на XML Яндекс.Маркета <b><a href="' . MY_BH . 'yml.xml" target="_blank">' . MY_BH . 'yml.xml</a></b>');
        //http://help.yandex.ru/partnermarket/?id=1111425
        $this->config_form['available'] = array('type' => 'checkbox', 'caption' => 'Отображать статус НАЛИЧИЯ товара');

    }

    protected function _set_features()
    {
        parent::_set_features();
        $this->ver = '0.1.5';
        $this->caption = 'Магазин - Каталог';
        $this->_AllowAjaxFn['jsOrder'] = true;
        //$this->cf_tools[] = array('func'=>'ImportXls','name'=>'Загрузка прайса');
        $this->YML_FILE = $this->_CFG['_PATH']['content'] . 'yml.xml';
        $this->basketEnabled = false;

        $this->_enum['orderset'] = array(
            0 => 'Заказ письмом',
            1 => 'В корзину');

    }

    protected function _create()
    {
        parent::_create();
        $this->fields['uiname'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL');

        // @TODO cf_fields
        $this->fields['code'] = array('type' => 'varchar', 'width' => 11, 'attr' => 'NOT NULL', 'index' => true);
        $this->index_fields['code'] = 'code';

        $this->selFields .= ',t1.uiname';
    }

    public function setFieldsForm($form = 0)
    {
        parent::setFieldsForm($form);
        $this->fields_form['uiname'] = array('type' => 'text', 'caption' => 'Краткое наименование');
        $this->fields_form['code'] = array('type' => 'text', 'caption' => 'Код');
    }

    function _childs()
    {
        parent::_childs();
        $this->create_child('product');
    }

    public function _add($data = array(), $flag_select = true, $flag_update = false)
    {

        if (isset($data['name']) and $data['name'] and !isset($data['uiname']))
            $data['uiname'] = $this->fixNameCat($data['parent_id'], $data['name']);

        if ($ret = parent::_add($data, $flag_select, $flag_update)) {
            // Here can be u code...
        }
        return $ret;
    }

    public function _update($data = array(), $where = null, $flag_select = true)
    {

        if (isset($data['name']) and $data['name'] and !isset($data['uiname']) and isset($data['parent_id']))
            $data['uiname'] = $this->fixNameCat($data['parent_id'], $data['name']);

        if ($ret = parent::_update($data, $where, $flag_select)) {
            // Here can be u code...
        }
        return $ret;
    }

    // убираем дублирование каталога, и сохраняем в новом поле, если что можно выводить в новом шаблоне
    private function fixNameCat($id, $name)
    {
        if ($id) {
            list($data) = $this->qs('name', 'WHERE id=' . $id);
            $fn = _substr($name, 0, _strlen($data['name']));
            if ($fn === $data['name'])
                $name = trim(_substr($name, _strlen($data['name'])));
        }

        return $name;

    }

    function allChangeData($type = '', $data = '')
    {
        if (file_exists($this->YML_FILE)) unlink($this->YML_FILE);
        return parent::allChangeData($type, $data);
    }

    //HOOK
    // TODO - это не очень красывый код
    function sdfs($MAIL)
    {
        $MAIL->fields_form['from']['caption'] = 'Ваш Email';
        $MAIL->fields_form['from']['mask']['min'] = 0;
        $MAIL->fields_form['from']['placeholder'] = 'Отправим вам письмо с подробной информацией о заказе.';
        $MAIL->fields_form['p_phone'] = array('type' => 'text', 'caption' => 'Телефон', 'mask' => array('min' => '5', 'name' => 'phone2'), 'placeholder' => 'Мы перезвоним вам');
        $MAIL->fields_form['p_addr'] = array('type' => 'text', 'caption' => 'Адрес доставки', 'mask' => array('min' => '10'), 'placeholder' => 'Адрес доставки в пределах Уфимского района');
        $MAIL->fields_form['p_count'] = array('type' => 'list', 'listname' => array('count', 1, 10), 'caption' => 'Количество', 'mask' => array('min' => '1', 'max' => 10), 'default' => '1');
        $MAIL->fields_form['p_comment'] = array('type' => 'textarea', 'caption' => 'Дополнительная информация', 'mask' => array('max' => '500'), 'placeholder' => 'Укажите ополнительные требования и пожелания');
    }

    function jsOrder()
    {
        global $_tpl;
        $html = '<div class="messages"><div class="error">Ошибка данных!</div></div>';
        $flag = -1;

        $PRODUCT = & $this->childs['product'];
        $PRODUCT->id = (int)$_GET['id'];

        if ($PRODUCT->id) {
            $data = $PRODUCT->_select();
            if (count($data)) {
                _new_class('mail', $MAIL);
                _new_class('ugroup', $UGROUP);

                $MAIL->HOOK['getFieldsForm'] = array($this, 'sdfs');

                $DATA = array();
                $cap = 'Заказ товара №' . $_GET['id'] . ' (' . $data[$PRODUCT->id]['name'] . ')';
                if (count($_POST)) {
                    $_POST['text'] = 'Товар: ' . $data[$PRODUCT->id]['name'] . ' , #' . $PRODUCT->id . ' <br/>
					Адрес доставки: ' . $_POST['p_addr'] . ' <br/>
					Телефон: ' . $_POST['p_phone'] . ' <br/>
					Кол-во: ' . $_POST['p_count'] . ' <br/>
					Email: ' . (isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : $_POST['from']) . ' <br/>
					Дополнительно: ' . $_POST['p_comment'];
                    $_POST['subject'] = $cap;
                } else {
                }

                list($DATA, $flag) = $MAIL->mailForm($UGROUP->config['mail_to']);
                if (isset($DATA['form']['text'])) {
                    unset($DATA['form']['text']);
                    unset($DATA['form']['subject']);
                    unset($DATA['form']['text_ckedit']);
                    unset($DATA['form']['status']);
                    unset($DATA['form']['mail_to']);
                    unset($DATA['form']['creater_id']);
                    unset($DATA['form']['user_to']);
                    $DATA['form']['_info']['caption'] = $cap;
                }

                $_tpl['DATA'] = $DATA;
                $_tpl['flag'] = $flag;
                $html = '';
                if ($flag == 1) {
                    $DATA['messages'][0]['value'] = 'Ваш заказ принят на расмотрение. В дальнейшем с вами свяжется наш менеджер.';
                    //setTemplate("waction");
                    $html = transformPHP($DATA, '#pg#messages');
                } elseif ($flag == -1) {
                    if (!$_tpl['onload'])
                        $html = transformPHP($DATA, '#pg#messages');
                } else {
                    $html = transformPHP($DATA, '#pg#formcreat');
                    //$_tpl['onload'] .= '$(\'#form_mail\').submit(function(){ JSWin({\'type\':this}); return false;});';
                }
            }
        }

        $_tpl['text'] = $html;
        return true;
    }

    function getPath($id, $page, $startId = 0)
    {
        global $_tpl;
        $temp = $id;
        $tpath = array();
        while (isset($this->data2[$temp])) {
            $_tpl['keywords'] .= ', ' . $this->data2[$temp]['name'];
            $tpath[$page . '/' . $this->data2[$temp]['path']] = array('name' => $this->data2[$temp]['name']);
            $temp = $this->data2[$temp]['parent_id'];
            if ($startId == $temp) break;
        }
        return array_reverse($tpath);
    }

    function fBasketData()
    {
        $RESULT = array();
        if (!_new_class('shopbasket', $SHOPBASKET)) return false;
        return $SHOPBASKET->fBasketData();
    }
}
