<?

$_CFG['sql']['host']='localhost';
$_CFG['sql']['login']='core_wep';
$_CFG['sql']['password']='D56FdpnD4th';
$_CFG['sql']['database']='core_wep';
$_CFG['sql']['setnames']='utf8';
$_CFG['sql']['dbpref']='';
$_CFG['sql']['log']=0;

$_CFG['wep']['access']=1; // 1 - вкл доступ по модулю пользователей, 0 - вкл доступ по дефолтному паролю
$_CFG['wep']['login'] = 'root';
$_CFG['wep']['password']='coreadmin';
$_CFG['wep']['md5']='dSS2ffs';
$_CFG['wep']['def_filesize']=100;
$_CFG['wep']['sessiontype']=1 //0 - стандартная сессия, 1 - БД сессия, 2 - ещё какаянибудь
$_CFG['wep']['bug_hunter'] = 1;


$_CFG['site']['rf'] = 0;

$_CFG['session']['multidomain'] = 1;

?>
