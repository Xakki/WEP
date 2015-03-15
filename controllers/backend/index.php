<?php
isBackend(true);

$pageParam = trim($_GET['pageParam'], '/');
$pageParam = explode('/', $pageParam);
$mainPage = array_shift($pageParam);

// if($mainPage=='js.php')
// {
// 	include('js.php');
// 	exit();
// }

$result = static_main::userAuth(); // запскает сессию и проверяет авторизацию

if ($mainPage == 'logout') {
    static_main::userExit();
    $ref = MY_BH . 'index.html';
    static_main::redirect($ref);
    exit();
}

if ($mainPage == 'login' or $result[1] < 1 or !$_SESSION['user']['wep']) {
    /*
    $_REQUEST['ref'] = $_SERVER['REQUEST_URI'];
    $setRedirect = false;
    $mess = array();
    if($result[0])
        $mess[] = array( ($result[1]==1?'ok':'error'), $result[0]);
    */
    if ($mainPage == 'login')
        $setRedirect = true;
    $result = include($_CFG['_PATH']['wep_controllers'] . 'login.php');
    if ($result === false)
        exit();
}


if (!isset($_GET['_modul'])) $_GET['_modul'] = '';

/*ADMIN*/
function fAdminMenu($_modul = '')
{
    global $_CFG;
    $data = array();
    $data['modul'] = $_GET['_modul'];
    $data['user'] = $_SESSION['user'];
    $data['item'] = array();
    if ($_SESSION['user']['level'] <= 1) {
        static_main::_prmModulLoad();
        foreach ($_CFG['modulprm'] as $k => $r) {
            if (static_main::_prmModul($k, array(1, 2)) and $r['active'] == 1) {
                $data['item'][$k] = $r;
                $data['item'][$k]['sel'] = ($_modul == $k ? 1 : 0);
            }
        }
        if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0)
            $data['item']['_tools'] = array('name' => 'TOOLs', 'css' => 'am_tools', 'sel' => ($_modul == '_tools' ? 1 : 0));
    }
    /*weppages*/
    /*if(isset($_SESSION['user']) and count($_SESSION['user']['weppages'])) {
        foreach($_SESSION['user']['weppages'] as $k=>$r0)
            $template['sysconf']['item'][$k] = $r;
    }*/
    return $data;
}

function fXmlSysconf()
{
    global $_CFG;
    $data = array();
    $data['sysconf']['modul'] = $_GET['_modul'];
    $data['sysconf']['user'] = $_SESSION['user'];
    $data['sysconf']['item'] = array();
    if ($_SESSION['user']['level'] <= 1) {
        static_main::_prmModulLoad();
        foreach ($_CFG['modulprm'] as $k => $r) {
            if ($r['active'] == 1 and $r['typemodul'] == 0 and $r['tablename'] and static_main::_prmModul($k, array(1, 2))) {
                if (!$r['name'])
                    $r['name'] = $k;
                if (!$r['active'])
                    $r['name'] = '<span style="color:gray;">' . $r['name'] . '</span>';
                $data['sysconf']['item'][$k] = $r['name'];
            }
        }
        if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0)
            $data['sysconf']['item']['_tools'] = 'TOOLs';
    }
    /*weppages*/
    /*if(isset($_SESSION['user']) and count($_SESSION['user']['weppages'])) {
        foreach($_SESSION['user']['weppages'] as $k=>$r0)
            $template['sysconf']['item'][$k] = $r;
    }*/
    return $data;
}

function fXmlModulslist()
{
    global $_CFG;
    $data = array();
    $data['modulslist']['modul'] = $_GET['_modul'];
    $data['modulslist']['user'] = $_SESSION['user'];
    static_main::_prmModulLoad();
    foreach ($_CFG['modulprm'] as $k => $r) {
        if ($r['active'] == 1 and $r['typemodul'] == 3 and $r['tablename'] and static_main::_prmModul($k, array(1, 2))) {
            if (!$r['name'])
                $r['name'] = $k;
            if (!$r['active'])
                $r['name'] = '<span style="color:gray;">' . $r['name'] . '</span>';
            $data['modulslist']['item'][$k] = $r['name'];
        }
    }

    return $data;
}

function selectDebugMode()
{
    global $_tpl, $_CFG;
    $listDebug = array(
        0 => 'не показывать ошибки',
        1 => 'сообщение об ошибке',
        2 => 'Показать все ошибки',
        3 => 'DEBUG MODE',
        //4 => 'не показывать ошибки',
    );
    $listInfo = array(
        0 => 'Скрыть инфу',
        1 => 'Показать инфу',
        2 => 'Показать SQL запросы',
        3 => 'Показать все логи',
        //4 => 'не показывать ошибки',
    );
    if (static_main::_prmUserCheck(2)) {
        if (!isset($_COOKIE[$_CFG['wep']['_showerror']]))
            setNeverShowError();

        $_tpl['debug'] = '<span class="seldebug"><select onchange="window.location.href=wep.getUrlWithNewParam({' . $_CFG['wep']['_showerror'] . ':this.value});">';
        foreach ($listDebug as $k => $r)
            $_tpl['debug'] .= '<option ' . ($_COOKIE[$_CFG['wep']['_showerror']] == $k ? 'selected="selected"' : '') . ' value="' . $k . '">' . $r . '</option>';
        $_tpl['debug'] .= '</select></span>';


        if (!isset($_COOKIE[$_CFG['wep']['_showallinfo']]))
            $_COOKIE[$_CFG['wep']['_showallinfo']] = 0;

        $_tpl['debug'] .= '<span class="seldebug"><select onchange="window.location.href=wep.getUrlWithNewParam({' . $_CFG['wep']['_showallinfo'] . ':this.value});">';
        foreach ($listInfo as $k => $r)
            $_tpl['debug'] .= '<option ' . ($_COOKIE[$_CFG['wep']['_showallinfo']] == $k ? 'selected="selected"' : '') . ' value="' . $k . '">' . $r . '</option>';
        $_tpl['debug'] .= '</select></span>';

        /*$_tpl['debug'] .= '<span class="seldebug"><select onchange="setCookie(\'cdesign\',this.value);window.location.href=\''.ADMIN_BH.'\';">
<option '.($_design=='default'?'selected="selected"':'').' value="default">Default</option>
<option '.($_design=='extjs'?'selected="selected"':'').' value="extjs">ExtJS</option>
</select></span>';*/
    }
}

function showCmsInfo()
{
    global $_tpl, $SQL, $_CFG;

    if (!isset($_SESSION['wep_info'])) {
        if (!$SQL) $SQL = new $_CFG['sql']['type']($_CFG['sql']);
        $info = $SQL->_info();
        $_SESSION['wep_info'] = 'PHP ver.' . phpversion() . ' | MySQL ver.' . $info['version'][1] . ' | ' . date_default_timezone_get() . ' | ';
    }
    $_tpl['time'] = $_SESSION['wep_info'] . date('Y-m-d H:i:s') . ' | ';

    if ($_CFG['info']['email'])
        $_tpl['contact'] = '<div class="ctd1">e-mail:</div>	<div class="ctd2"><a href="mailto:' . $_CFG['info']['email'] . '">' . $_CFG['info']['email'] . '</a></div>';
    if ($_CFG['info']['icq'])
        $_tpl['contact'] .= '<div class="ctd1">icq:</div><div class="ctd2">' . $_CFG['info']['icq'] . '</div>';
    if (isset($_CFG['info']['phone']) and $_CFG['info']['phone'])
        $_tpl['contact'] .= '<div class="ctd1">телефон:</div><div class="ctd2">' . $_CFG['info']['phone'] . '</div>';

    $_tpl['wep_ver'] = $_CFG['info']['version'];
}

/*---------------ADMIN*/

include(getPathTheme() . '/inc/index.php');

