<?php

global $_CFG;

set_error_handler('_myErrorHandler');

/*
  Функция сбора и обработки ошибок
 */
function _myErrorHandler($errno, $errstr, $errfile, $errline)
{ //, $errcontext,$cont
    global $_CFG, $BUG;
    if ($_CFG['wep']['catch_bug']) {
        if (isset($GLOBALS['_ERR'][$_CFG['wep']['catch_bug']]) && count($GLOBALS['_ERR'][$_CFG['wep']['catch_bug']]) > 100) {
//            if (count($GLOBALS['_ERR'][$_CFG['wep']['catch_bug']])==101)
//                $GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][] = array(
//                    'errno' => 0,
//                    'errstr' => '************ Слишком много ошибок ****************',
//                    'errfile' => '',
//                    'errline' => '',
//                    'debug' => '',
//                    'errtype' => 0,
//                );
            return;
        }

        // Debuger
        // для вывода отладчика для всех типов ошибок , можно отключить это условие
        $debug = '';
        if (isset($_CFG['wep']['bug_hunter'][$errno]) and $_CFG['_error'][$errno]['debug']) {
            $debug = debugPrint(2);
        }

        $GLOBALS['_ERR'][$_CFG['wep']['catch_bug']][] = array(
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            //'errcontext'=>$errcontext, // Всякие переменные
            'debug' => $debug,
            'errtype' => $_CFG['_error'][$errno]['type'],
        );

        // Инициальзация ловца-ошибок
        if (is_array($_CFG['wep']['bug_hunter']) and count($_CFG['wep']['bug_hunter']) and !$BUG and !$_CFG['shutdown_function_flag']) {
            _new_class('bug', $BUG);
        }
        //остановка на фатальной ошибке
        if ($_CFG['_error'][$errno]['prior'] == 0 and !$_CFG['wep']['debugmode']) {
            die("\n Aborting...<br />\n");
        }
    }
}

function startCatchError($param = 2)
{
    global $_CFG;
    if ($param < 2) $param = 2;
    $_CFG['_ctemp' . $param]['catch_bug'] = $_CFG['wep']['catch_bug'];
    $_CFG['_ctemp' . $param]['bug_hunter'] = $_CFG['wep']['bug_hunter'];
    $_CFG['_ctemp' . $param]['debugmode'] = $_CFG['wep']['debugmode'];
    $_CFG['wep']['catch_bug'] = $param;
    $_CFG['wep']['bug_hunter'] = array(
        0 => '0', 1 => '1', 4 => '4', 16 => '16', 64 => '64', 256 => '256', 4096 => '4096', 2 => '2', 32 => '32', 128 => '128', 512 => '512', 2048 => '2048'
    );
    $_CFG['wep']['debugmode'] = 2;
    return true;
}

function getCatchError($param = 2)
{
    global $_CFG;
    if ($param < 2) $param = 2;
    $_CFG['wep']['catch_bug'] = $_CFG['_ctemp' . $param]['catch_bug'];
    $_CFG['wep']['bug_hunter'] = $_CFG['_ctemp' . $param]['bug_hunter'];
    $_CFG['wep']['debugmode'] = $_CFG['_ctemp' . $param]['debugmode'];
    if (isset($GLOBALS['_ERR'][$param])) {
        $temp = $GLOBALS['_ERR'];
        $GLOBALS['_ERR'] = array($param => $temp[$param]);
        $return = static_main::showErr(); //static_main::showErr() //$GLOBALS['_ERR'][$param];
        unset($temp[$param]);
        $GLOBALS['_ERR'] = $temp;
    } else $return = '';
    return $return;
}

/*
  Ф. трасировки ошибок
 */
function debugPrint($slice = 1)
{
    $MAXSTRLEN = 2000;
    $s = '<div class="xdebug">';
    $traceArr = debug_backtrace();
    $traceArr = array_slice($traceArr, $slice);
    $i = 0;
    foreach ($traceArr as $arr) {
        $s .= '<div class="xdebug-item" style="margin-left:' . (10 * $i) . 'px;"><span>';
        if (isset($arr['line']) and $arr['file']) $s .= ' #line ' . $arr['line'] . ' in file: <a href="file:/' . $arr['file'] . '">' . $arr['file'] . '</a> : ';
        if (isset($arr['class'])) $s .= '#class <b>' . $arr['class'] . '-></b>';
        $s .= '</span>';
        //$s .= '<br/>';
        $args = array();
        if (isset($arr['args']))
            foreach ($arr['args'] as $v) {
                if (is_null($v)) $args[] = '<b>NULL</b>';
                else if (is_array($v)) $args[] = '<b>Array[' . sizeof($v) . ']</b>';
                else if (is_object($v)) $args[] = '<b>Object:' . get_class($v) . '</b>';
                else if (is_bool($v)) $args[] = '<b>' . ($v ? 'true' : 'false') . '</b>';
                else {
                    $v = (string)@$v;
                    $str = _e(substr($v, 0, $MAXSTRLEN));
                    if (strlen($v) > $MAXSTRLEN) $str .= '...';
                    $args[] = $str;
                }
            }
        $s .= '<b>' . $arr['function'] . '</b>(' . implode(',', $args) . ')';
        $s .= '</div>';
        $i++;
    }
    $s .= '</div>';
    return $s;
}
