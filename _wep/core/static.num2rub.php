<?php

class static_num2rub
{
	public static $def = array(
		'form' => array('1' => 0, '2' => 1, '1f' => 0, '2f' => 1, '3' => 1, '4' => 1),
		'rank' => array(
			0 => array('рубль', 'рубля', 'рублей', 'f' => ''),
			1 => array('тысяча', 'тысячи', 'тысяч', 'f' => 'f'),
			2 => array('миллион', 'миллиона', 'миллионов', 'f' => ''),
			3 => array('миллиард', 'миллиарда', 'миллиардов', 'f' => ''),
			'k' => array('копейка', 'копейки', 'копеек', 'f' => 'f')
		),

		'words' => array(
			'0' => array('', 'десять', '', ''),
			'1' => array('один', 'одиннадцать', '', 'сто'),
			'2' => array('два', 'двенадцать', 'двадцать', 'двести'),
			'1f' => array('одна', '', '', ''),
			'2f' => array('две', '', '', ''),
			'3' => array('три', 'тринадцать', 'тридцать', 'триста'),
			'4' => array('четыре', 'четырнадцать', 'сорок', 'четыреста'),
			'5' => array('пять', 'пятнадцать', 'пятьдесят', 'пятьсот'),
			'6' => array('шесть', 'шестнадцать', 'шестьдесят', 'шестьсот'),
			'7' => array('семь', 'семнадцать', 'семьдесят', 'семьсот'),
			'8' => array('восемь', 'восемнадцать', 'восемьдесят', 'восемьсот'),
			'9' => array('девять', 'девятнадцать', 'девяносто', 'девятьсот')
		)
	);

	public static function run($str)
	{

		$str = number_format($str, 2, '.', ',');
		$rubkop = explode('.', $str);
		$rub = $rubkop[0];
		$kop = (isset($rubkop[1])) ? $rubkop[1] : '00';
		$rub = (strlen($rub) == 1) ? '0' . $rub : $rub;
		$rub = explode(',', $rub);
		$rub = array_reverse($rub);

		$word = array();
		$word[] = self::dvig($kop, 'k', false);
		foreach ($rub as $key => $value) {
			if (intval($value) > 0 || $key == 0) //подсказал skrabus
				$word[] = self::dvig($value, $key);
		}

		$word = array_reverse($word);
		return ucfirst(trim(implode(' ', $word)));
	}

	public static function dvig($str, $key, $do_word = true)
	{
		$def = self::$def;
		$words = $def['words'];
		$form = $def['form'];

		if (!isset($def['rank'][$key])) return '!razriad';
		$rank = $def['rank'][$key];
		$sotni = '';
		$word = '';
		$num_word = '';

		$str = (strlen($str) == 1) ? '0' . $str : $str;
		$dig = str_split($str);
		$dig = array_reverse($dig);

		if (1 == $dig[1]) {
			$num_word = ($do_word) ? $words[$dig[0]][1] : $dig[1] . $dig[0];
			$word = $rank[2];
		}
		else {
			//$rank[3] - famale
			if ($dig[0] != 1 && $dig[0] != 2) $rank['f'] = '';
			$num_word = ($do_word)
				? $words[$dig[1]][2] . ' ' . $words[$dig[0] . $rank['f']][0]
				: $dig[1] . $dig[0];
			$key = (isset($form[$dig[0]])) ? $form[$dig[0]] : false;
			$word = ($key !== false) ? $rank[$key] : $rank[2];
		}

		$sotni = (isset($dig[2])) ? (($do_word) ? $words[$dig[2]][3] : $dig[2]) : '';
		if ($sotni && $do_word) $sotni .= ' ';

		return $sotni . $num_word . ' ' . $word;
	} //function dvig

} 