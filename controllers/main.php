<?php

if ($_NEED_INSTALL) {
    require_once($_CFG['_PATH']['wep_controllers'] . 'install/index.php');
    return true;
}

$URI = $_GET['pageParam'];

if (strpos($URI, WEP_ADMIN) === 0) {
    $is_admin = true;
} else {
    $is_admin = false;
}
//ini_set("max_execution_time", "10");
//set_time_limit(10);

if ($_CFG['site']['worktime'] and !canShowAllInfo() and !$is_admin) {
    static_main::downSite(); // Exit()
}

/**********************************************/

if (strpos($URI, '_') !== false) {
    if (preg_match("/^(.*)_p([0-9]+)\.html$/i", $URI, $regs)) {
        $URI = $regs[1] . '.html';
        $_REQUEST['_pn'] = $_GET['_pn'] = $regs[2];
    }
    if (preg_match("/^(.*)_([0-9]+)\.html$/i", $URI, $regs)) {
        $URI = $regs[1] . '.html';
        $_GET['id'] = $regs[2];
    }
}

/**********************************************/

// вход в админку
if ($is_admin) {
    $_REQUEST['pageParam'] = $_GET['pageParam'] = substr($URI, strlen(WEP_ADMIN));
    require_once($_CFG['_PATH']['backend'] . 'index.php');
    return true;
}

if (file_exists($_CFG['_PATH']['controllers'] . 'route.php')) {
    require_once($_CFG['_PATH']['controllers'] . 'route.php');
}

if (substr($URI, -5) == '.html') {
    $_REQUEST['pageParam'] = $_GET['pageParam'] = substr($URI, 0, -5);
} else {
    $_REQUEST['pageParam'] = $_GET['pageParam'] = $URI;
}

/***********************************************/

if ($_GET['_php'] == '_js') {
    if (file_exists($_CFG['_PATH']['controllers'] . '_js.php'))
        require_once($_CFG['_PATH']['controllers'] . '_js.php');
    else
        require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/_js.php');
    return true;
} elseif ($_GET['_php'] == 'rss') {
    if (file_exists($_CFG['_PATH']['controllers'] . 'rss.php'))
        require_once($_CFG['_PATH']['controllers'] . 'rss.php');
    elseif (file_exists($_CFG['_PATH']['wep_controllers'] . 'frontend/rss.php'))
        require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/rss.php');
    else
        echo 'no RSS';
    return true;
} elseif ($_GET['_php'] == 'sitemap' || $_GET['pageParam'] == 'sitemap.xml') {
    ini_set("max_execution_time", 3600);
    ini_set("memory_limit", '256M');
    setTemplate('text');
//    setNeverShowAllInfo();
//    setNeverShowError();
//    setOffDebug();


    _new_class('pg', $PGLIST);
    $_tpl['text'] = $PGLIST->getSiteMaps();
    if ($_tpl['text'] == '') {
        header('HTTP/1.1 503 Service Unavailable');
    } else {
        header('Pragma: no-cache');
        header('Content-type: text/xml; charset=utf-8');
    }
    return true;
} elseif (strpos($_SERVER['REQUEST_URI'], '.xml') !== false) {
    $php = $_GET['_php'] = mb_substr($_SERVER['REQUEST_URI'], 0, -4);
    if (file_exists($_CFG['_PATH']['controllers'] . $_GET['_php'] . '.xml.php'))
        require_once($_CFG['_PATH']['controllers'] . $_GET['_php'] . '.xml.php');
    elseif (file_exists($_CFG['_PATH']['wep_controllers'] . 'frontend/' . $_GET['_php'] . '.xml.php'))
        require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/' . $_GET['_php'] . '.xml.php');
    else
        echo 'Ашипка!';
    return true;
} elseif (isset($_GET['_php']) and $_GET['_php'] == 'config') {
    setNeverShowAllInfo();
    //Применяется для CKFinder для авторизации по сессии
    session_go();
    return true;
}


//*****************

if (_new_class('pg', $PGLIST)) {
    if (!isset($_REQUEST['pageParam']) || !$_REQUEST['pageParam']) {
        $_REQUEST['pageParam'] = "index";
    } elseif (is_array($_REQUEST['pageParam'])) {
        $_REQUEST['pageParam'] = implode('/', $_REQUEST['pageParam']);
    } elseif (_substr($_REQUEST['pageParam'], -4) == '.php') {
        $_REQUEST['pageParam'] = _substr($_REQUEST['pageParam'], 0, -4);
    }
    $_REQUEST['pageParam'] = preg_split('/\//u', $_REQUEST['pageParam'], 0, PREG_SPLIT_NO_EMPTY);
    //if($_SESSION['_showallinfo']) {print('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}
    if ($PGLIST->config['auto_auth']) {
        static_main::userAuth();
    }

    $PGLIST->display();

    //if($_SESSION['_showallinfo']) print('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки

    /*
        if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
        if(_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
            $_SESSION['showIEwarning']++;
            //$_tpl['meta'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
        }
    */
} else
    static_main::downSite('Система ещё не установлена', 'Модуль "Страницы" не установлен или отключен.');