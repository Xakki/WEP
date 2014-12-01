<?php

class static_antispam
{
    /**
	 * В формат выода сообщения
	 */
	    static public $word_censor = array(
        'хуй\b', 'нахуя', 'нихуя',
        'хреносос', 'хуесос',
        'пизда', 'пиздень', 'пиздец',
        '\bблять\b', '\bбля\b',
        'шалава', 'проститутка',
        'ебать\b', 'еблан\b',
        'долбоеб', 'дибил', 'уебан',
    );

    static public $word_tabu = array(
        'спаис\w?', 'спайс\w?', '\bводк\w?', 'Кристаллиус', 'CRISTALIUS', '\bMDPV\b',
        'курительны\w?\W?микс', 'курительны\w?\W?смес',
        'Легальны\w?\W?порошк',
        'эйфоретик\w?', 'кан(н)?абис',
        'марихуан', 'наркотик\w?', 'гашиш', 'экстази', 'конопл\w?', 'опиум',
        'кокаин', 'амфетамин\w?',
        'Порошк.*микс',
        'jwh[\-\s]+[0-9]+', 'jwh.*соли',
        'RCS[\-\s]+[0-9]+',
        'AKB48'
    );

    static function censorHighlight(&$text)
    {
        $patern = '/(' . implode(')|(', self::$word_censor) . ')/ui';
        preg_match_all($patern, $text, $result, PREG_OFFSET_CAPTURE);
        if (count($result[0])) {
            $wlist = array();
            foreach ($result[0] as $value) {
                $text = str_replace($value[0], '<error>' . $value[0] . '</error>', $text);
            }
            return true;
        }
        return false;
    }

    static function tabuHighlight(&$text)
    {
		//\b
        $patern = '/(' . implode(')|(', self::$word_tabu) . ')/ui';
        preg_match_all($patern, $text, $result, PREG_OFFSET_CAPTURE);
		// global $_tpl;
		// $_tpl['xxxx'] = $text;
		// $_tpl['zzzzz'] = $patern;
		// $_tpl['yyyy'] = $result;
		// exit();
        if (count($result[0])) {
            $wlist = array();
            foreach ($result[0] as $value) {
                $text = str_replace($value[0], '<error>' . $value[0] . '</error>', $text);
            }
            return true;
        }
        return false;
    }

    static function tabu($text)
    {
		//$patern = '/(спайс.?\b)|(водк.?\b)/eui';
        $patern = '/(' . implode('\b)|(', self::$word_tabu) . '\b)/ui';
        preg_match_all($patern, $text, $result, PREG_OFFSET_CAPTURE);
		// global $_tpl;
		// $_tpl['xxxx'] = $text;
		// $_tpl['zzzzz'] = $patern;
		// $_tpl['yyyy'] = $result;
		// exit();
        if (count($result[0])) {
            $wlist = array();
            foreach ($result[0] as $value) {
                $wlist = $value[0];
            }
            return implode(', ', $wlist);
        }
        return false;
    }
}
