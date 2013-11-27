<?php
$param = array(
	'noise' => 1,
	'lstepmin' => 4,
	'lstepmax' => 9,
	'sizemin' => 14, // Минимальный шрифт
	'sizemax' => 29, // Максимальный шрифт
	'angle' => 40, // +- поворот
	'width' => 160, // Высота картинки
	'height' => 40, // Ширина
	'dX' => 7, // Смещение по -X+
	'dY' => 5, // Смещение по -Y+
	'fonts' => array('arial', 'times', 'verdana', 'verdanab', 'georgiai', 'academy-italic', 'dejavuserifitalic'), // ,'hiline'
	'k' => 1, // Коэффициент увеличения/уменьшения картинки
);

$dafault_read = array(
	'noise' => 1,
	'sizemin' => 14, // Минимальный шрифт
	'sizemax' => 14, // Максимальный шрифт
	'angle' => 0, // +- поворот
	'width' => 160, // Высота картинки
	'height' => 40, // Ширина
	'dX' => 0, // Смещение по -X+
	'dY' => 0, // Смещение по -Y+
	'fonts' => array('arial'),
	'k' => 1, // Коэффициент увеличения/уменьшения картинки
);
$data = 0;


if (isset($_COOKIE['chash']) and $_COOKIE['chash'] and $_COOKIE['pkey']) {
	$hash_key = base64_decode(str_replace(array('-', '_'), array('+', '/'), $_COOKIE['pkey']));
	$hash_key = file_get_contents($hash_key) . $_SERVER['REMOTE_ADDR'];
	$hash_key = md5($hash_key);
	if (function_exists('openssl_encrypt')) {
		$data = openssl_decrypt($_COOKIE['chash'], 'aes-128-cbc', $hash_key, false, "1234567812345678");
	}
	elseif (function_exists('mcrypt_encrypt')) {
		$data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $_COOKIE['chash']));
		//$ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		//$iv = mcrypt_create_iv($ivsize, MCRYPT_RAND);
		$data = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, $data, MCRYPT_MODE_ECB);
		$data = trim($data); // Без него mb_strlen будет возвращать фиг знает какое число
	}
	else
		$data = $_COOKIE['chash'];
}
else {
	$data = 'ОШИБКА';
	//$param = $dafault_read;
	/*session_start();
	if(isset($_SESSION["captcha"]))
		$data = $_SESSION["captcha"];
	else
		$data = $_SESSION["captcha"] = rand(10000,99999);
	*/
}

header("Content-type: image/png");

function mt()
{
	list($usec, $sec) = explode(' ', microtime());
	return (float)$sec + ((float)$usec * 100000);
}

$l = mb_strlen($data, 'UTF-8');
if (!(int)$l) $l = 1;

$path = '_design/fonts/';

$center = (int)$param['height'] / 2; // Центральная позиция
$step = (int)$param['width'] / $l; // Шаг для букв
$cntFonts = (count($param['fonts']) - 1); //


// создаем изображение
$im = imagecreate($param['width'], $param['height']);

// Выделяем цвет фона (белый)
$w = imagecolorallocate($im, 255, 255, 255);

// Выделяем цвет для шумовых полосок (светло-серый)
$tmp1 = rand(90, 240);
$tmp2 = rand(30, 240);
$tmp3 = rand(160, 240);
$g1 = imagecolorallocate($im, $tmp1, $tmp2, $tmp3);
$g2 = imagecolorallocate($im, $tmp3, $tmp1, $tmp2);
$lstep = rand($param['lstepmin'], $param['lstepmax']);
$lstep2 = rand($param['lstepmin'], $param['lstepmax']);

// Рисуем сетку
for ($i = 0; $i < $param['width']; $i += $lstep)
	imageline($im, $i + rand(-10, 10), 0 + rand(0, 20), $i + rand(-10, 10), $param['height'] + rand(-20, 0), $g1);
for ($i = 0; $i < $param['height']; $i += $lstep2)
	imageline($im, 0 + rand(0, 20), $i + rand(-10, 10), $param['width'] + rand(-20, 0), $i + rand(-20, 10), $g2);

// Выводим каждую цифру по отдельности, немного смещая случайным образом
$k = (int)$step / 2;

for ($i = 0; $i < $l; $i++) {
	$cl = imagecolorallocate($im, rand(0, 128), rand(0, 128), rand(0, 128));
	$fsize = rand($param['sizemin'], $param['sizemax']);

	/*if($fsize<($param['sizemin']+$param['sizemax'])/2)
	{
		$posX = (int)($k-($fsize/3))+rand(0, $param['dX']);
		$posY = (int)($center+($fsize/2))+rand(0, $param['dY']); // Позиция по Y
	}
	else
	{
		$posX = (int)($k-($fsize/3))+rand(-$param['dX'], 0);
		$posY = (int)($center+($fsize/2))+rand(-$param['dY'], 0); // Позиция по Y
	}*/
	$posX = (int)($k - ($fsize / 3)) + rand(-$param['dX'] / 2, $param['dX'] / 2);

	$posY = (int)($center + ($fsize / 2)); // Позиция по Y


	$font = $path . $param['fonts'][rand(0, $cntFonts)] . '.ttf';
	imagettftext($im, $fsize, rand(-$param['angle'], $param['angle']), $posX, $posY, $cl, $font, mb_substr($data, $i, 1));
	$k += $step;
}

// Коэффициент увеличения/уменьшения картинки
$k = $param['k'];
if ($k !== 1) {
	// Создаем новое изображение, увеличенного размера
	$im1 = imagecreatetruecolor($param['width'] * $k, $param['height'] * $k);
	// Копируем изображение с изменением размеров в большую сторону
	imagecopyresized($im1, $im, 0, 0, 0, 0, $param['width'] * $k, $param['height'] * $k, $param['width'], $param['height']);
	// Создаем новое изображение, нормального размера
	$im2 = imagecreatetruecolor($param['width'], $param['height']);
	// Копируем изображение с изменением размеров в меньшую сторону
	imagecopyresampled($im2, $im1, 0, 0, 0, 0, $param['width'], $param['height'], $param['width'] * $k, $param['height'] * $k);

	imagedestroy($im2);
	imagedestroy($im1);

}
else
	$im2 = $im;

// Генерируем изображение
//imagepng($im);
imagepng($im2);

// Освобождаем память

imagedestroy($im);
