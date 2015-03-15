<?php
include('html.php');

class wepajax
{
    var $_errn = 14623;

    function __construct()
    {
        $this->headerssent();

        $this->_mctime_start = getmicrotime();

        ob_start(array(&$this, "obHandler"));

        //ini_set('display_errors', $this->_errn);
        //$_REQUEST = utf2win_recursive($_REQUEST);
    }

    function __destruct()
    {
        global $_CFG;
        include_once($_CFG['_PATH']['core'] . '/includesrc.php');
        fileInclude();
        ob_end_flush();
    }

    function obHandler($buffer)
    {
        global $_tpl, $_CFG;
        if ($buffer) $_tpl['logs'] .= $buffer;

        /*Вывд логов и инфы*/
        if (canShowAllInfo() or isBackend()) {
            $_tpl['logs'] .= $this->getLogInfo();
        }
        $_tpl['logs'] .= static_main::showErr();

        foreach ($_tpl as $k => $r) {
            wephtml::parseTemplate($_tpl[$k], $_tpl); // PARSE
        }

        self::sliceMarker($_tpl);

        if (version_compare(phpversion(), '5.3.0', '>')) {
            //$GLOBALS['_RESULT'] = $this->allreplace($GLOBALS['_RESULT']);
            //return var_export($GLOBALS['_RESULT'],true);
            //$GLOBALS['_RESULT']['onload'] = str_replace(array('\\r','\\n','\\t'),'', $GLOBALS['_RESULT']['onload']);

            $result = json_encode($_tpl, JSON_HEX_QUOT | JSON_HEX_TAG);
            //JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
            return $result;
        } else return $this->jsonencode($_tpl);
    }

    public function getLogInfo()
    {
        global $_CFG;

        $htmlinfo = '';
        $included_files = get_included_files();
        $htmlinfo .= ' time=' . substr((getmicrotime() - $this->_mctime_start), 0, 6) . ' | SQLtime=' . $_CFG['logs']['sqlTime'] . ' | memory=' . (int)(memory_get_usage() / 1024) . 'Kb | maxmemory=' . (int)(memory_get_peak_usage() / 1024) . 'Kb | query=' . count($_CFG['logs']['sql']) . ' | file include=' . count($included_files) . ' <br/> ';

        if (canShowAllInfo() > 1 and count($_CFG['logs']['sql']) > 0) $htmlinfo .= static_main::spoilerWrap('SQL QUERY', static_render::sqlLog($_CFG['logs']['sql']));
        if (canShowAllInfo() > 2) {
            $htmlinfo .= static_main::spoilerWrap('FILE INCLUDE', implode(';<br/>', $included_files));
        }
        return $htmlinfo;
    }

    /*
	  Ф. вывода заголовков
	 */
    function headerssent()
    {
        global $_CFG;
        if (!headers_sent()) {
            if (isDebugMode()) {
                header('Pragma: no-cache');
                header('Cache-Control: public, no-store, no-cache, must-revalidate, post-check=0, pre-check=0'); // no-store, no-cache,
            } else {
                header('Pragma: cache');
                header('Cache-Control: public, post-check=0, pre-check=0');
            }
            //header('Content-type: text/html; charset=utf-8');
            header('Content-type: application/json; charset=utf-8');
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $_CFG['header']['modif']) . " GMT");
            header("Expires: " . gmdate("D, d M Y H:i:s", (time() + $_CFG['header']['expires'])) . " GMT");
            header('X-Accel-Expires: ' . $_CFG['header']['expires']);
            if ($_CFG['site']['origin']) {
                header("Access-Control-Allow-Origin: " . $_CFG['site']['origin']);
            }
            return true;
        }
        return false;
    }

    function allreplace($arr)
    {
        $mask = array('/\n+/', '/\r+/', '/\t+/', '/\"\"/'); //,'/[\']+/','/[\"]+/'
        $repl = array('', '', '', '""'); //,'&#039;','&quot;'
        $repl2 = array('<br/>', '', '&#160;&#160;&#160;&#160;', '""');
        foreach ($arr as $k => $r) {
            if (is_array($r)) $arr[$k] = $this->allreplace($r);
            elseif (is_string($arr[$k])) {
                if ($k == 'text') $arr[$k] = preg_replace($mask, $repl2, $r);
                else $arr[$k] = preg_replace($mask, $repl, $r);
            }
        }
        return $arr;
    }

    function jsonencode($value)
    {
        if (is_int($value)) {
            return (string)$value;
        } elseif (is_string($value)) {
            $value = str_replace(
                array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t", '/\"\"/', '<', '>'),
                array('\\\\', '\/', '\"', '', '', '', '', '', '""', "\\u003C", "\\u003E"),
                $value
            );
            $convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
            $result = "";
            for ($i = mb_strlen($value) - 1; $i >= 0; $i--) {
                $mb_char = mb_substr($value, $i, 1);
                if (mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) {
                    $result = sprintf("\\u%04x", $match[1]) . $result;
                } else {
                    $result = $mb_char . $result;
                }
            }
            return '"' . $result . '"';
        } elseif (is_float($value)) {
            return str_replace(",", ".", $value);
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            $with_keys = false;
            $n = count($value);
            for ($i = 0, reset($value); $i < $n; $i++, next($value)) {
                if (key($value) !== $i) {
                    $with_keys = true;
                    break;
                }
            }
        } elseif (is_object($value)) {
            $with_keys = true;
        } else {
            return '';
        }
        $result = array();
        if ($with_keys) {
            foreach ($value as $key => $v) {
                $result[] = $this->jsonencode((string)$key) . ':' . $this->jsonencode($v);
            }
            return '{' . implode(',', $result) . '}';
        } else {
            foreach ($value as $key => $v) {
                $result[] = $this->jsonencode($v);
            }
            return '[' . implode(',', $result) . ']';
        }
    }

    static function sliceMarker(&$tpl)
    {
        if (isset($_REQUEST['PGMARKER'])) {
            $marker = $_REQUEST['PGMARKER'];
            if (!is_array($marker)) {
                $marker = preg_split("/[\s\,]+/u", $marker, 8, PREG_SPLIT_NO_EMPTY);
            }
            if (isset($tpl['logs']) && $tpl['logs']) {
                $marker[] = 'logs';
            }
            $marker[] = 'onload';
            $marker[] = 'script';
            $marker[] = 'styles';
            $marker[] = 'REQUEST_URI';
            $marker[] = 'BH';
            $marker[] = 'title';
            $tpl = array_intersect_key($tpl, array_flip($marker));
        }
    }
}

