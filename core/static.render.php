<?php

class static_render
{
	public static $def = array();

	public static function message($mess, $class = 'ok')
	{
		$mess = array(
			array($class, $mess)
		);
		return transformPHP($mess, '#pg#messages');
	}

	public static function sqlLog($sqlAr)
	{

        global $_CFG;
        $timeArr = [];
        foreach ($sqlAr as $k => $v) {
            $timeArr[$k] = $v['time'];
        }
        // $timeArr = array_column($sqlAr, 'time');
//        return '+++';
        $maxTime = max($timeArr);
        $avrTime = ($_CFG['logs']['sqlTime']/count($sqlAr));
        $minTime = min($timeArr);
        $dTime = ($maxTime-$minTime);
        foreach ($sqlAr as &$r) {
            $ttt = $r['time'];
            $k = ($ttt-$minTime) / $dTime;
            $ttt = '<span style="color:rgb('. (int) (255 * $k).', '. (int) (255 * (1-$k)).', 0);'.($ttt> ($maxTime-$dTime*0.2) ? 'font-weight:bold;' : '').'">' . $ttt . '</span>';
			$r = '<div>'.htmlentities($r['query'], ENT_NOQUOTES, CHARSET) . ' TIME=' . $ttt. ($r['hasError'] ? ' <span style="color:red;font-size:1.5em;">ERROR</span>' : '').'</div>';
        }
        return implode('', $sqlAr);
    }


} 