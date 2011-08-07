<?
	$_CFG['_PATH']['wep'] = dirname(dirname(dirname(dirname(__FILE__)))).'/_wep';
	require_once($_CFG['_PATH']['wep'].'/config/config.php');
	//ini_set('error_reporting', 'E_ALL & ~E_DEPRECATED & ~E_NOTICE');
	//error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE);
	//ini_set('display_errors',0);

	_new_class('exportboard',$EXPORTBOARD);

	$data = $EXPORTBOARD->childs['sendboard']->_query('t1.*,t2.wwwadd,t2.encode','t1 LEFT JOIN '.$EXPORTBOARD->tablename.' t2 on t1.owner_id=t2.id WHERE t1.result=0 and t2.active=1');

	$res = '';

	if(count($data)) {
		foreach($data as $k=>$r) {
			//инициализируем сеанс
			$curl = curl_init();
			 
			//уcтанавливаем урл, к которому обратимся
			curl_setopt($curl, CURLOPT_URL, $r['wwwadd']);
			 
			//откл вывод заголовков
			curl_setopt($curl, CURLOPT_HEADER, 0);

			//$headers   = Array();
			//$headers[] = "Content-type: application/x-www-form-urlencoded";
			//curl_setopt ($curl, CURLOPT_HTTPHEADER, $headers);

			//передаем данные по методу post
			curl_setopt($curl, CURLOPT_POST, 1);
			 
			//теперь curl вернет нам ответ, а не выведет
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			 
			//переменные, которые будут переданные по методу post
			if($r['encode']) {
				$r['text'] = mb_convert_encoding($r['text'], $r['encode'], 'UTF-8');
			} else {
			}
			eval('$r[\'text\']='.$r['text'].';');
			

			//$r['text'] = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $r['text'] );
			//$r['text'] = unserialize($r['text']);//TODO :

			curl_setopt($curl, CURLOPT_POSTFIELDS, $r['text']);
			
			//представляемся  //Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14
			curl_setopt($curl, CURLOPT_USERAGENT, 'UniDoski 0.2');

			// оправляем $referer, что пришли с первой страницы сайта
			curl_setopt ( $curl , CURLOPT_REFERER , $r['wwwadd'] );
			
			// если есть массив с cookie, то отправим серверу, эти cookie
			//if ( @is_array ($arr_cookie)){
			//while (list($key, $val) = @each ($arr_cookie)){
			//$COKKIES .= trim ($val[0])."=". trim ($val[1])."; ";
			//}
			//@curl_setopt ( $ch , CURLOPT_COOKIE , $COKKIES." expires=Mon, 14-Apr-08 10:34:13 GMT" );
			//}

			// если с сервера пришло cookie, то запишем его в файл $cookie_file
			//@curl_setopt ( $ch , CURLOPT_COOKIEJAR , $cookie_file );
			//@curl_setopt ( $ch , CURLOPT_COOKIEFILE , $cookie_file );

			$result = curl_exec($curl);
			$flag = 3;

			$PageInfo = curl_getinfo($curl);
			if($err=curl_errno($curl)) {
				$flag = 2;
				if($r['encode'] and $result)
					$result = mb_convert_encoding($result, 'UTF-8', $r['encode']);
				$result = curl_error($curl).'('.$err.')'.$result;
				$res .= ' Error wwwadd='.$r['wwwadd'].' // ';
			}
			else {
				//if($PageInfo['http_code'] == 200)
				if($r['encode'] and $result)
					$result = mb_convert_encoding($result, 'UTF-8', $r['encode']);
				$res .= ' OK wwwadd='.$r['wwwadd'].' // ';
			}
			curl_close($curl);

			$EXPORTBOARD->childs['sendboard']->fld_data['result'] = $flag;
			$EXPORTBOARD->childs['sendboard']->fld_data['pageinfo'] = mysql_real_escape_string(var_export($PageInfo,true));
			$EXPORTBOARD->childs['sendboard']->fld_data['textresult'] = mysql_real_escape_string($result);
			$EXPORTBOARD->childs['sendboard']->id = $r['id'];
			static_form::_update($EXPORTBOARD->childs['sendboard']);//обновление данных
		}
	}
	return 'Exportboard // '.$res;

