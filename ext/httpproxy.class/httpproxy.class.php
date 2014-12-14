<?php

class httpproxy_class extends kernel_extends
{

	function init()
	{
		parent::init();
		$this->ver = '0.0.2';
		$this->mf_actctrl = true;
		$this->mf_timecr = true;
		$this->mf_timeup = true;
		$this->default_access = '|0|';
		$this->messages_on_page = 100;

		$this->unique_fields['name'] = 'name';

		$this->index_fields['timeout'] = 'timeout';
		$this->index_fields['negative'] = 'negative';
		$this->index_fields['positive'] = 'positive';
		$this->index_fields['mf_timeup'] = 'mf_timeup';

		$this->cf_tools[] = array('func' => 'toolsLoadList', 'name' => 'Загрузка списка прокси');
		$this->cf_tools[] = array('func' => 'toolsClearUse', 'name' => 'Сброс захваченых прокси');
		$this->cf_tools[] = array('func' => 'toolsClearOff', 'name' => 'Сброс счетчиков у отключенных прокси');
		$this->cf_tools[] = array('func' => 'toolsFullClear', 'name' => 'Сброс полный');
		$this->cf_tools[] = array('func' => 'toolsCheckSite', 'name' => 'Ручная проверка');

        $this->cron[] = array('modul' => $this->_cl, 'function' => 'cronCheckSite()', 'active' => 0, 'time' => 60);
        $this->cron[] = array('modul' => $this->_cl, 'function' => 'cronReCheckSite()', 'active' => 0, 'time' => 60);

        $this->_AllowAjaxFn['toolsCheckSite'] = true;

        $this->_enum['type'] = array(
            CURLPROXY_HTTP => 'HTTP',
            CURLPROXY_SOCKS4 => 'SOCKS4',
            CURLPROXY_SOCKS5 => 'SOCKS5',
		);
	}

    protected function _create_conf() {
        parent::_create_conf();

        $this->config['check_site'] = 'http://xakki.ru';
        $this->config['check_word'] = 'Бортовой';
        $this->config['timeout'] = 20;
        $this->config['add_time_default'] = 60;
        $this->config['add_time_check'] = 10;

        $this->config_form['check_site'] = array('type' => 'text', 'caption' => 'check_site');
        $this->config_form['check_word'] = array('type' => 'text', 'caption' => 'check_word');
        $this->config_form['timeout'] = array('type' => 'text', 'caption' => 'Timeout');
        $this->config_form['add_time_default'] = array('type' => 'text', 'caption' => 'Add Error Timeout Default');
        $this->config_form['add_time_check'] = array('type' => 'text', 'caption' => 'Add Error Timeout Check');
    }

	function _create()
	{
		parent::_create();
		$this->caption = 'Http proxy list';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['port'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['type'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['desc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['timeout'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 60, 'noquote' => true);
		$this->fields['negative'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);
		$this->fields['positive'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);
		$this->fields['capture'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['last_time'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['last_code'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);

		//$this->cron[] = array('modul'=>$this->_cl,'function'=>'setRate()','active'=>1,'time'=>6);
		$this->ordfield = 'mf_timeup DESC';
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'HTTP', 'comment' => 'значение localhost- не используется прокси', 'mask' => array());
		$this->fields_form['port'] = array('type' => 'int', 'caption' => 'Port', 'mask' => array());
		$this->fields_form['type'] = array('type' => 'list', 'listname' => 'type', 'caption' => 'Тип', 'mask' => array());
		$this->fields_form['desc'] = array('type' => 'textarea', 'caption' => 'Описание', 'mask' => array());
		$this->fields_form['timeout'] = array('type' => 'int', 'caption' => 'Период(сек)', 'mask' => array());
		$this->fields_form['negative'] = array('type' => 'int', 'caption' => '-', 'mask' => array());
		$this->fields_form['positive'] = array('type' => 'int', 'caption' => '+', 'mask' => array());
		$this->fields_form['last_time'] = array('type' => 'int', 'caption' => 'last_time', 'mask' => array());
		$this->fields_form['last_code'] = array('type' => 'int', 'caption' => 'last_code', 'mask' => array());
		$this->fields_form['mf_timeup'] = array('type' => 'date', 'caption' => 'Дата', 'mask' => array());
		$this->fields_form['capture'] = array('type' => 'checkbox', 'caption' => 'Занято', 'mask' => array());
		$this->fields_form['active'] = array('type' => 'checkbox', 'caption' => 'Вкл/Выкл', 'mask' => array());
	}

	function _childs()
	{
		$this->create_child('httpproxycheck');
	}


	function getProxy($domen = '', $check = false)
	{
        if ($domen) {
            $domen = parse_url($domen);
            $this->domen = $domen['host'];
        }
		if (!isset($_GET['pos']))
			$_GET['pos'] = rand(0, 3);

        $proxyList = [];
        $select = 't1.name,t1.port,t1.id';
        $where = ' WHERE t1.`capture`= 0 and t1.`mf_timeup`<(' . time() . '-t1.`timeout`) ';
        $sort = ' ORDER BY ';
        if ($check==='off') {
            // проверка отключенных
            $where .= 'and t1.`active`=0';
            $sort .= 't1.`mf_timeup`';
        }
        elseif ($check) {
            // проверка включенных
            $where .= 'and t1.`active`=1';
            $sort .= 't1.`mf_timeup`';
        }
        else {
            // Обычная выборка
            $where .= 'and t1.`active`=1';
            $sort .= 'fl, t1.`mf_timeup`';
            //

            $select .= ',(t1.`negative`-t1.`positive`) as fl';
        }
		$limit = ' LIMIT ' . (int)$_GET['pos'] . ',1';
        $where = ' t1 '.$where;


		$this->data = $this->_query($select,$where.$sort.$limit, 'id' ,'', false);

		if (count($this->data)) {
            $data = current($this->data);
			$this->id = $data['id'];
			if ($data['name']) {
				if ($data['name'] and $data['port'])
                    $proxyList[] = $data['name'] . ':' . $data['port'];
				elseif ($data['name'])
                    $proxyList[] = $data['name'];
			}
			$this->_update(array('capture' => 1), false, false);
		}
		return $proxyList;
	}

	function upStatus($check, $err = 0, $info = array())
	{
        $lastcode = (int)$info['http_code'];
        $time = (int)$info['total_time'];
		$addCheck = array('name' => $info['url'], 'total_time' => $time, 'http_code' => $lastcode, 'err' => $err, 'speed_download' => $info['speed_download'], 'size_download' => $info['size_download']);
		$upd = array('capture' => 0, 'last_time' => $time , 'last_code' => $lastcode);

            if ($err===2) {

            }
            elseif ($err===1) { 
                $rate = 1;
                if (isset($this->data[$this->id]) && $this->data[$this->id]['last_code']!=200) {
                    $rate = 2;
                }
                if ($check) {
                    $upd['negative'] = '`negative`+1';
                    $upd['timeout'] = '`timeout`+'.(int) $this->config['add_time_check'] * $rate;
                }
                else {
                    $upd['timeout'] = '`timeout`+'.(int) $this->config['add_time_default'] * $rate;
                }
            }
            else {
                if ($check) {
                    $upd['positive'] = '`positive`+1';
                }
            }

		if ($this->id) {
			$this->_update($upd, false, false);
		}
		else {
			$this->id = 1;
			$this->_update($upd, false, false);
		}

        $this->childs['httpproxycheck']->_add($addCheck, false);

		//else $this->_update($upd,'`name`="localhost"',false);
	}


	function getContent($link, $param = array(), $check = false)
	{
        if ($this->_CFG['wep']['debugmode'] > 1) {
            print_r('<hr/>link = ' . $link);
        }

//		$param= array();
//		$param['COOKIE'] = 'u=1sd8vo18.1dnuct3.ecjxmxedy5; sessid=7f0e8f17c54448f9e21c96c369261621.1401747983; dfp_group=16; _mlocation=653240; v=1401747983; __utmmobile=0xdebe13defcd60834';
//		$param['redirect'] = true;
//		$param['USERAGENT'] = 'Mozilla/5.0 (Linux; Android 4.2.1; en-us; Nexus 4 Build/JOP40D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19';
//		$param['find'] = 'искомый обязательный текст';
//        $param['TIMEOUT'] = $this->config['timeout'];

        if (!isset($param['findRu'])) {
            $param['findRu'] = false;
        }
		$proxyList = $this->getProxy($link, $check);
		if (count($proxyList)) {
			$param['proxy'] = true;
			$param['proxyList'] = $proxyList;
		}
        elseif ($check) {
            return NULL;
        }
		$html = static_tools::_http($link, $param);

		$err = 0;

        if ($html['err']==7 and $html['info']['total_time']==0) {
            $err = 2;
            // если брыв инета
        }
        elseif ($html['err']==7) {
            $err = 1;
        }
		elseif (!$html['text'] || $html['info']['http_code']==0) {
            $err = 1;
		}
        elseif ($html['info']['http_code'] == 0) {
            $err = 1;
        }
		elseif ($html['info']['http_code'] == 403) {
			// or strpos($html['text'],'<td class="headCode">403</td>')!==false
			$err = 1; // ограничение доступа
		}
		elseif ($html['info']['http_code'] == 302) {
			$err = 1;
		}
		elseif ($html['info']['http_code'] != 200) {
			$err = 1;
		}
		elseif ($html['info']['redirect_url'] != '') {
			$err = 1;
			print_r('<p style="color:red;">Редирект <b>' . $html['info']['redirect_url'] . '</b></p>');
		}
		elseif ($html['text'] == '') {
			$err = 1;
		}
		else {
            if (isset($param['find']) and mb_stripos($html['text'], $param['find']) === false) {
                $err = 1;
                if ($this->_CFG['wep']['debugmode'] > 1) {
                    print_r('<p style="color:red;">Не найден текст <b>' . $param['find'] . '</b></p>');
//                    print_r(htmlentities($html['text'], ENT_NOQUOTES, CHARSET));
                }
            }
            elseif ($param['findRu']) {
                preg_match_all('/[А-Яа-яЁё]/u', $html['text'], $matches);
                if (count($matches[0]) < 5) {
                    $err = 1;
                    if ($this->_CFG['wep']['debugmode'] > 1) {
                        print_r('<p style="color:red;">Мало русских букв</p>');
                    }
                }
            }
		}

		if ($err)
			$html['flag'] = false;

        if ($this->_CFG['wep']['debugmode'] > 1) {
            print_r(' | proxy  = ' . implode(',',$proxyList ) .'  |  err = ' . $err.'  |  redirect_url = ' . $html['info']['redirect_url'].'  |  http_code = ' . $html['info']['http_code'].'  |  total_time = ' . $html['info']['total_time']);
        }

		$this->upStatus($check, $err, $html['info']);


		return $html;
	}


	function toolsLoadList()
	{
		global $_tpl;
		$fields_form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $this);
		elseif (count($_POST) and $_POST['txt']) {
			$data = explode("\n", $_POST['txt']);
			foreach ($data as $r) {
				$temp = preg_split("/[\s\t\,\:\;]+/u", $r, -1, PREG_SPLIT_NO_EMPTY);
				if (!$temp[1]) $temp[1] = '80';
				$AD = array('name' => $temp[0], 'port' => $temp[1]);
				if (isset($temp[2]))
					$AD['desc'] = implode(" \n", array_slice($temp[2], 2));
				if (!$this->_add($AD, false))
					$mess[] = static_main::am('error', 'Прокси ' . $temp[0] . ' уже есть в списке!', $this);
			}
			$mess[] = static_main::am('ok', 'Сделано', $this);
		}
		else {
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">' . $this->caption . '</h2>');
			$fields_form['txt'] = array(
				'type' => 'textarea',
				'caption' => 'Список',
				'mask' => array('max' => 999999),
			);

			$fields_form['sbmt'] = array(
				'type' => 'submit',
				'value' => 'Выполнить',
			);
			self::kFields2FormFields($fields_form);
		}
		return array(
			'form' => $fields_form,
			'messages' => $mess,
			'options' => $this->getFormOptions()
		);
	}

	function toolsClearUse()
	{
		$fields_form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $this);
		elseif (count($_POST) and $_POST['dsbmt']) {
			$upd = array(
				'capture' => 0,
			);
			$this->_update($upd, 'WHERE capture!=0', false);
			$mess = array(static_main::am('ok', 'Сделано!!!!!', $this));
		}
		else {
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">Обнулить захваченые прокси?</h2>');
			$fields_form['dsbmt'] = array(
				'type' => 'submit',
				'value' => 'Выполнить',
			);
			self::kFields2FormFields($fields_form);
		}
		return [
			'form' => $fields_form,
			'messages' => $mess,
			'options' => $this->getFormOptions()
		];
	}

    function toolsClearOff()
    {
        $fields_form = $mess = array();
        if (!static_main::_prmModul($this->_cl, array(5, 7)))
            $mess[] = static_main::am('error', 'denied', $this);
        elseif (count($_POST) and $_POST['dsbmt']) {
            $upd = array(
                'negative' => 0,
                'positive' => 0,
            );
            $this->_update($upd, 'WHERE active=0', false);
            $mess = array(static_main::am('ok', 'Сделано!!!!!', $this));
        }
        else {
            $fields_form['_info'] = array(
                'type' => 'info',
                'caption' => '<h2 style="text-align:center;">Обнулить данные отключенных прокси?</h2>');
            $fields_form['dsbmt'] = array(
                'type' => 'submit',
                'value' => 'Выполнить',
            );
            self::kFields2FormFields($fields_form);
        }
        return [
            'form' => $fields_form,
            'messages' => $mess,
            'options' => $this->getFormOptions()
        ];
    }

    function toolsFullClear()
    {
        $fields_form = $mess = array();
        if (!static_main::_prmModul($this->_cl, array(5, 7)))
            $mess[] = static_main::am('error', 'denied', $this);
        elseif (count($_POST) and $_POST['dsbmt']) {
            $upd = array(
                'negative' => 0,
                'positive' => 0,
                'capture' => 0,
            );
            $this->_update($upd, 'WHERE 1=1', false);
            $this->childs['httpproxycheck']->_tableClear();
            $mess = array(static_main::am('ok', 'Сделано!!!!!', $this));
        }
        else {
            $fields_form['_info'] = array(
                'type' => 'info',
                'caption' => '<h2 style="text-align:center;">Обнулить все данные?</h2>');
            $fields_form['dsbmt'] = array(
                'type' => 'submit',
                'value' => 'Выполнить',
            );
            self::kFields2FormFields($fields_form);
        }
        return [
            'form' => $fields_form,
            'messages' => $mess,
            'options' => $this->getFormOptions()
        ];
    }

    static function clearHtml($html)
    {
        $p = mb_strpos($html,'<body');
        if($p) {
            $html = mb_substr($html, $p);
            $p = mb_strpos($html,'</body>');
            if($p) {
                $html = mb_substr($html,0,$p);
                $html = trim($html);
            }
        }
        $html = preg_replace(
            array("'<script[^>]*?>.*?</script>'si", "'<link[^>]*?>'si",
                "'<noscript>.*?</noscript>'si",
                "'<\!--noindex-->.*?<\!--\/noindex-->'si",
                "'<form.*?</form>'si",
                "'<body.*?>'si",
            )
            , '', $html);
        return $html;
    }

    public function cronCheckSite($n=3) {
        $param = array();
        $param['TIMEOUT'] = $this->config['timeout'];
        $param['find'] = $this->config['check_word'];
        for ($i = 0; $i< $n; $i++) {
            $Page = $this->getContent($this->config['check_site'], $param, true);
        }
        return '-OK-';
    }

    public function cronReCheckSite($n=3) {
        $param = array();
        $param['TIMEOUT'] = $this->config['timeout'];
        $param['find'] = $this->config['check_word'];
        for ($i = 0; $i< $n; $i++) {
            $Page = $this->getContent($this->config['check_site'], $param, 'off');
        }
        return '-OK-';
    }

	function toolsCheckSite()
	{
		ini_set("max_execution_time", "360000");
		set_time_limit(3600 * 24);
        $handtimer  = 200;
		$param = array();
		//yabs-sid=1752422071299616340
		//fuid01=4d76925108fcb0e4.rBvCuQR8PRtqU2cm8eaIj544KRBAQW8cUD0k9yG2dNO2-djVIemWHc4uRIFM9hRcLTiT44tUrwm8hDYbtVhmeQi-p4M2cb698Ih3G-mnz1pHaMGhAR9g6sdmV_a-E8Zs
		//yp=2147483647.ygo.172:213'
		//yandex_gid   213 - москва //172 - уфа
		//yandexmarket   число элем на стр,,1,USD,1,,1(не учитыв регион)-2(учитыв регион),0,0,
		//$param['COOKIE'] = 'yandex_gid=213;yandexmarket=100,,1,USD,1,,1,0,0,;';
		//подделываем юзер-агента
		//$param['redirect'] = true;
		$param['TIMEOUT'] = 20;
		$param['find'] = $this->config['check_word'];

		for ($i = 0; $i< 1; $i++) {

            $Page = $this->getContent($this->config['check_site'], $param, true);
            if (is_null($Page)) {
                $handtimer  = 20000;
            }
//
//            $Page['text'] = self::clearHtml($Page['text']);
//            print_r('<pre>');
//            print_r($Page);
//            print_r('</pre>');
//            exit();
		}

        if ($_GET['noajax']) {

            $rtn = '<h2>Перезагрузка страницы через '.$handtimer.'мсек ('.date('Y-m-d H:i:s').')</h2>';
            $rtn .= '<script>
				var tm = setTimeout(function() {window.location.href=location.href;},'.$handtimer.');
				function start_stop(obj) {
					console.log(tm);
					if(tm) {
						clearTimeout(tm);tm=0;
						obj.value= "Start";
					}
					else {
						tm = setTimeout(function() {window.location.href=location.href;},'.$handtimer.');
						obj.value= "STOP";
					}
				}
			</script>
			<style>
				h2 {font-size:20px;margin:18px 0 0;}
				ok, .ok {color:green;font-size:12px;margin:0 5px;}
				err, .err {color:red;margin:10px;font-size:20px;}
			</style>
			<input onclick="start_stop(this)" type="submit" value="STOP"/>
			';
            return $rtn;
        }

		$mess = array(static_main::am('ok', 'Сделано', $this));

		return ['form' => array(), 'messages' => $mess, 'options' => $this->getFormOptions()];

	}

//UPDATE wep_httpproxy SET `use`=0,err=0,time=0
}


class httpproxycheck_class extends kernel_extends
{

	function init()
	{
		parent::init();
		$this->mf_timecr = true;
		$this->mf_timeup = false;
		$this->mf_actctrl = false;
		$this->default_access = '|0|';
		$this->index_fields['http_code'] = 'http_code';
		$this->index_fields['err'] = 'err';
		return true;
	}

	function _create()
	{
		parent::_create();
		$this->caption = 'Проверенные домены';
		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
        $this->fields['http_code'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['total_time'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['speed_download'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['size_download'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->fields['err'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
        $this->ordfield = 'mf_timecr DESC';
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Url', 'mask' => array('min' => 4));
		$this->fields_form['http_code'] = array('type' => 'int', 'caption' => 'HTTP code', 'mask' => array());
		$this->fields_form['total_time'] = array('type' => 'int', 'caption' => 'Timeout', 'mask' => array());
        $this->fields_form['speed_download'] = array('type' => 'int', 'caption' => 'speed_download', 'mask' => array());
        $this->fields_form['size_download'] = array('type' => 'int', 'caption' => 'size_download', 'mask' => array());
        $this->fields_form['err'] = array('type' => 'int', 'caption' => 'Ошибка?', 'mask' => array());
        $this->fields_form['mf_timecr'] = array('type' => 'date', 'caption' => 'Дата', 'mask' => array());
	}

}