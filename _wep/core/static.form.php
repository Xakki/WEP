<?

class static_form {

	static function _usabilityDate($time, $format='Y-m-d H:i') {
		global $_CFG;
		$date = getdate($time);
		$de = $_CFG['time'] - $time;
		if ($de < 3600) {
			if ($de < 240) {
				if ($de < 60)
					$date = 'Минуту назад';
				else
					$date = ceil($de / 60) . ' минуты назад';
			}
			else
				$date = ceil($de / 60) . ' минут назад';
		}
		elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] == $date['yday'])
			$date = 'Сегодня ' . date('H:i', $time);
		elseif ($_CFG['getdate']['year'] == $date['year'] and $_CFG['getdate']['yday'] - $date['yday'] == 1)
			$date = 'Вчера ' . date('H:i', $time);
		else
			$date = date($format, $time);
		return $date;
	}

	static function _parseDate($arrdate) {
		$date_str = array();
		// час
		if ($arrdate['H']) {
			$date_str[0] = $arrdate['H'];
		} else {
			$date_str[0] = '0';
		}
		// минуты
		if ($arrdate['i']) {
			$date_str[1] = $arrdate['i'];
		} else {
			$date_str[1] = '0';
		}
		// секунды
		if ($arrdate['s']) {
			$date_str[2] = $arrdate['s'];
		} else {
			$date_str[2] = '0';
		}

		// месяц
		if ($arrdate['m']) {
			$date_str[3] = $arrdate['m'];
		} else {
			$date_str[3] = '0';
		}
		// день
		if ($arrdate['d']) {
			$date_str[4] = $arrdate['d'];
		} else {
			$date_str[4] = '0';
		}
		//год
		if ($arrdate['Y']) {
			$date_str[5] = $arrdate['Y'];
		} else {
			$date_str[5] = '0';
		}
		return $date_str;
	}

//возвращает форматированную дату в зависимости от типа поля в fields_form
	static function _get_fdate($field_form, $inp_date, $field_type) {
		// формат для даты
		if ($field_form['mask']['format']) {
			if ($field_form['mask']['separate'])
				$format = explode($field_form['mask']['separate'], $field_form['mask']['format']);
			else
				$format = explode('-', $field_form['mask']['format']);
		}
		else {
			$format = explode('-', 'Y-m-d');
		}

		// формат для времени
		if ($field_form['mask']['time']) {
			if ($field_form['mask']['separate'])
				$format_time = explode($field_form['mask']['separate_time'], $field_form['mask']['time']);
			else
				$format_time = explode(':', $field_form['mask']['time']);
		}
		else {
			$format_time = explode('-', 'H-i-s');
		}


		if (is_array($inp_date)) {
			$date = $inp_date;
		} else {
			// соединяем массивы и делим данные сначала по пробелу, потом по разделительным знакам, если нет времени, то добавляем значение по умолчанию
			$temp = explode(' ', $inp_date);
			if ($temp[0]) {
				if ($field_form['mask']['separate'])
					$date = explode($field_form['mask']['separate'], $temp[0]);
				else
					$date = explode('-', $temp[0]);
			}
			if ($temp[1]) {
				if ($field_form['mask']['separate_time'])
					$time = explode($field_form['mask']['separate_time'], $temp[1]);
				else
					$time = explode(':', $temp[1]);
			}
			else {
				$time = array(0, 0, 0);
			}

			if (is_array($date) && is_array($time))
				$date = array_merge($date, $time);
		}

		$format = array_merge($format, $format_time);
		if (count($format) == count($date))
			$final_array_date = array_combine($format, $date);
		$date_str = self::_parseDate($final_array_date);

		if ($field_type == 'int') {
			$result = mktime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4], $date_str[5]);
		} elseif ($field_type == 'timestamp') {
			$result = date("Y-m-d H:i:s", mktime($date_str[0], $date_str[1], $date_str[2], $date_str[3], $date_str[4], $date_str[5]));
		}
		else
			return static_main::_message('Тип поля ' . $k . ' неверен для даты', 1);

		return $result;
	}

	static function setCaptcha() {
		global $_CFG;
		$data = rand(10000, 99999); //$_SESSION['captcha']
		if ($_CFG['wep']['sessiontype'] == 1) {
			$hash_key = file_get_contents($_CFG['_PATH']['HASH_KEY']);
			$hash_key = md5($hash_key);
			$crypttext = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $hash_key, $data, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
			_setcookie('chash', $crypttext, (time() + 1800));
			_setcookie('pkey', base64_encode($_CFG['PATH']['HASH_KEY']), (time() + 1800));
		}
	}

	static function getCaptcha() {
		global $_CFG;
		if (isset($_COOKIE['chash']) and $_COOKIE['chash']) {
			$hash_key = file_get_contents($_CFG['_PATH']['HASH_KEY']);
			$hash_key = md5($hash_key);
			$data = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, base64_decode($_COOKIE['chash']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
			return $data;
		}
		return rand(145, 357);
	}

}

?>