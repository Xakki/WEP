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
		$this->cf_tools[] = array('func' => 'toolsClearUse', 'name' => 'Очистка счётчиков');
		$this->cf_tools[] = array('func' => 'toolsCheckSite', 'name' => 'Проверка bash.im');

        $this->_AllowAjaxFn['toolsCheckSite'] = true;
	}

    protected function _create_conf() {
        parent::_create_conf();

        $this->config['check_site'] = 'http://xakki.ru';
        $this->config['check_word'] = 'Бортовой';

        $this->config_form['check_site'] = array('type' => 'text', 'caption' => 'check_site');
        $this->config_form['check_word'] = array('type' => 'text', 'caption' => 'check_word');
    }

	function _create()
	{
		parent::_create();
		$this->caption = 'Http proxy list';

		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL', 'min' => '1');
		$this->fields['port'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['desc'] = array('type' => 'varchar', 'width' => 255, 'attr' => 'NOT NULL', 'default' => '');
		$this->fields['timeout'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 60, 'noquote' => true);
		$this->fields['negative'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);
		$this->fields['positive'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);
		$this->fields['capture'] = array('type' => 'bool', 'attr' => 'NOT NULL', 'default' => 0);

		//$this->cron[] = array('modul'=>$this->_cl,'function'=>'setRate()','active'=>1,'time'=>6);
		$this->ordfield = 'mf_timeup DESC';
	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);

		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'HTTP', 'comment' => 'значение localhost- не используется прокси', 'mask' => array());
		$this->fields_form['port'] = array('type' => 'int', 'caption' => 'Port', 'mask' => array());
		$this->fields_form['desc'] = array('type' => 'textarea', 'caption' => 'Описание', 'mask' => array());
		$this->fields_form['timeout'] = array('type' => 'int', 'caption' => 'Период(сек)', 'mask' => array());
		$this->fields_form['negative'] = array('type' => 'int', 'caption' => '-', 'mask' => array());
		$this->fields_form['positive'] = array('type' => 'int', 'caption' => '+', 'mask' => array());
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

		$res = '';
        $select = 't1.name,t1.port,t1.id';
        $where = ' WHERE t1.`active`=1 and t1.`capture`= 0 and t1.`mf_timeup`<(' . time() . '-t1.`timeout`)  and '.($check ? 't1.`negative`-t1.`positive`<=3' : 't1.`negative`-t1.`positive`<0');
		$sort = ' ORDER BY ' . ( $check ? 't1.`mf_timeup`' : 'fl, t1.`mf_timeup`' );
		$limit = ' LIMIT ' . (int)$_GET['pos'] . ',1';

        if ($domen) {
            //,
            $select .= ',t2.id as domenid ,if( t2.id and t2.use < t2.err * 1.5 ,1 , 0) as fl'; //
            $where = ' t1 LEFT JOIN ' . $this->childs['httpproxycheck']->tablename . ' t2
				ON t2.name="' . $this->SqlEsc($this->domen) . '" and t2.owner_id=t1.id ' . $where;
        }
        else {
            $where = ' t1 '.$where;
            $select .= ',1 as fl';
        }
        // t1.`negative`
//        print_r($this->SQL->query);
        //,false,false,true
        //`use`,`err`,`negative`,`mf_timeup`,`time`
        // and t1.`capture`= 0 and (t2.`err`<t2.`use` or t2.`use`<1)  and t1.`mf_timeup`<('.time().'-t1.`timeout`)
        //print_r(' * '.time().' * ');
        //,t1.`timeout`

		$this->data = $this->_query($select,$where.$sort.$limit, '' ,'', false);


		if (count($this->data)) {
			$this->id = $this->data[0]['id'];
			$this->childs['httpproxycheck']->id = (int)$this->data[0]['domenid'];
			if ($this->data[0]['name'] != 'localhost' and $this->data[0]['name']) {
				if ($this->data[0]['name'] and $this->data[0]['port'])
					$res = $this->data[0]['name'] . ':' . $this->data[0]['port'];
				elseif ($this->data[0]['name'])
					$res = $this->data[0]['name'];
			}
			$this->_update(array('capture' => 1), false, false);
			if (!$this->childs['httpproxycheck']->id) {
				$upd = array('name' => $this->domen, 'err' => 0, 'use' => 0);
				$this->childs['httpproxycheck']->_add($upd, false);
			}
		}
		return $res;
	}

	function upStatus($check, $err = 0, $info = array())
	{
        $lastcode = (int)$info['http_code'];
        $time = (int)$info['total_time'];
		$updCheck = array('use' => '`use`+1', 'time' => $time, 'lastcode' => $lastcode);
		$upd = array('capture' => 0);

            if ($err===2) {

            }
            elseif ($err===1) {
                $updCheck['err'] = '`err`+' . $err;
                if ($check) {
                    $upd['negative'] = '`negative`+1';
                }
            }
            else {
                if ($check) {
                    $upd['positive'] = '`positive`+1';
                }
            }

		if ($this->id) {
			$this->_update($upd, false, false);
			$this->childs['httpproxycheck']->_update($updCheck, false, false);
		}
		else {
			$this->id = 1;
			$this->_update($upd, false, false);
		}

		//else $this->_update($upd,'`name`="localhost"',false);
	}


	function getContent($link, $param = array(), $check = false)
	{
        if ($this->_CFG['wep']['debugmode'] > 1) {
            print_r('<hr/>link = ' . $link);
        }

		/*$param= array();
		$param['COOKIE'] = 'yandex_gid=213;yandexmarket=100,,1,USD,1,,1,0,0,;';
		$param['redirect'] = true;
		$param['TIMEOUT'] = 45;
		$param['find'] = 'искомый обязательный текст';
		*/
        if (!isset($param['findRu'])) {
            $param['findRu'] = false;
        }
		$temp = $this->getProxy($link, $check);
		if ($temp) {
			$param['proxy'] = true;
			$param['proxyList'] = array($temp);
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
            print_r(' | proxy  = ' . $temp .'  |  err = ' . $err.'  |  redirect_url = ' . $html['info']['redirect_url'].'  |  http_code = ' . $html['info']['http_code'].'  |  total_time = ' . $html['info']['total_time']);
        }

//
//        print_r('<pre>');
//        print_r($html);
//        exit();

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
				$temp = preg_split("/[\s\t\,\:\;]+/", $r, -1, PREG_SPLIT_NO_EMPTY);
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
		global $_tpl;
		$fields_form = $mess = array();
		if (!static_main::_prmModul($this->_cl, array(5, 7)))
			$mess[] = static_main::am('error', 'denied', $this);
		elseif (count($_POST) and $_POST['dsbmt']) {
			$upd = array(
				'capture' => 0,
			);
			$this->_update($upd, 'WHERE 1=1', false);
			$mess = array(static_main::am('ok', 'Сделано!!!!!', $this));
		}
		else {
			$fields_form['_info'] = array(
				'type' => 'info',
				'caption' => '<h2 style="text-align:center;">Обнулить данные?</h2>');
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

	function toolsCheckSite()
	{
		ini_set("max_execution_time", "360000");
		set_time_limit(3600 * 24);
		$param = array();
		//yabs-sid=1752422071299616340
		//fuid01=4d76925108fcb0e4.rBvCuQR8PRtqU2cm8eaIj544KRBAQW8cUD0k9yG2dNO2-djVIemWHc4uRIFM9hRcLTiT44tUrwm8hDYbtVhmeQi-p4M2cb698Ih3G-mnz1pHaMGhAR9g6sdmV_a-E8Zs
		//yp=2147483647.ygo.172:213'
		//yandex_gid   213 - москва //172 - уфа
		//yandexmarket   число элем на стр,,1,USD,1,,1(не учитыв регион)-2(учитыв регион),0,0,
		//$param['COOKIE'] = 'yandex_gid=213;yandexmarket=100,,1,USD,1,,1,0,0,;';
		//подделываем юзер-агента
		//$param['redirect'] = true;
		$param['TIMEOUT'] = 10;
		$param['find'] = $this->config['check_word'];

		for ($i = 0; $i< 3; $i++) {

            $Page = $this->getContent($this->config['check_site'], $param, true);

//            $Page['text'] = self::clearHtml($Page['text']);
//            print_r('<pre>');
//            print_r($Page);
//            print_r('</pre>');
		}

        if ($_GET['noajax']) {
            $handtimer  = 200;
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
		$this->mf_timecr = false;
		$this->mf_timeup = false;
		$this->mf_actctrl = false;
		$this->default_access = '|0|';
		$this->index_fields['use'] = 'use';
		$this->index_fields['err'] = 'err';
		$this->index_fields['time'] = 'time';
		return true;
	}

	function _create()
	{
		parent::_create();
		$this->caption = 'Проверенные домены';
		$this->fields['name'] = array('type' => 'varchar', 'width' => 128, 'attr' => 'NOT NULL');
		$this->fields['use'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);
		$this->fields['err'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);
		$this->fields['lastcode'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0);
		$this->fields['time'] = array('type' => 'int', 'width' => 11, 'attr' => 'NOT NULL', 'default' => 0, 'noquote' => true);

	}

	public function setFieldsForm($form = 0)
	{
		parent::setFieldsForm($form);
		$this->fields_form['name'] = array('type' => 'text', 'caption' => 'Domen', 'mask' => array('min' => 4));
		$this->fields_form['use'] = array('type' => 'int', 'caption' => 'Использовано', 'mask' => array());
		$this->fields_form['err'] = array('type' => 'int', 'caption' => 'Ошибки', 'mask' => array());
		$this->fields_form['lastcode'] = array('type' => 'int', 'caption' => 'HTTP code', 'mask' => array());
		$this->fields_form['time'] = array('type' => 'int', 'caption' => 'Время загрузки(сек)', 'mask' => array());

	}

}