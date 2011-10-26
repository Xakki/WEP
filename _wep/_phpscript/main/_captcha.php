<?php
$data = 0;
if(isset($_COOKIE['chash']) and $_COOKIE['chash'] and $_COOKIE['pkey']) {
	$hash_key = base64_decode($_COOKIE['pkey']);
	$hash_key = file_get_contents($hash_key).$_SERVER['REMOTE_ADDR'];
	$hash_key = md5($hash_key);
	if(function_exists('openssl_encrypt')) {
		$data = openssl_decrypt($_COOKIE['chash'],'aes-128-cbc',$hash_key,false,"1234567812345678");
	} else {
		$data = base64_decode($_COOKIE['chash']);
		$data = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $hash_key, $data, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND));
	}
}
else {
	$data = 'ERROR';
	/*session_start();
	if(isset($_SESSION["captcha"]))
		$data = $_SESSION["captcha"];
	else
		$data = $_SESSION["captcha"] = rand(10000,99999);
	*/
}

header("Content-type: image/png");

function mt() {
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}
$fonts = array(0=>'arial', 'times', 'verdana');

$l = strlen($data);
if (!(int)$l)$l=1;

$path = '_design/_ttf/';
$width = 160;
$height = 40;
$center = $height/2;
$step = $width/$l;
$font_size = 16;
$shY1 = 10;
$shX = 10;
$shY = 10;
$dA = 30;
// создаем изображение
$im=imagecreate($width, $height);
// Выделяем цвет фона (белый)
$w=imagecolorallocate($im, 255, 255, 255);
// Выделяем цвет для фона (светло-серый)
$g1=imagecolorallocate($im, 192, 192, 192);
// Рисуем сетку
for ($i=0;$i<$width;$i+=5) imageline($im, $i, 0, $i+5, $height-1, $g1);
for ($i=0;$i<$height;$i+=5) imageline($im,0, $i, $width, $i, $g1);

// Выводим каждую цифру по отдельности, немного смещая случайным образом

$k = 0;
for($i=0; $i<$l;$i++) {
	$cl=imagecolorallocate($im, rand(0,128), rand(0,128), rand(0,128));
	imagettftext($im, $font_size+rand(0,3), rand(-$dA, $dA), $k+rand(0, $shX), $center+rand($shY1, $shY), $cl, $path.$fonts[rand(0, 2)].'.ttf', substr($data, $i, 1));
	$k+=$step;
}

// Коэффициент увеличения/уменьшения картинки
$k=1.7;
// Создаем новое изображение, увеличенного размера
$im1=imagecreatetruecolor($width*$k, $height*$k);
// Копируем изображение с изменением размеров в большую сторону
imagecopyresized($im1, $im, 0, 0, 0, 0, $width*$k, $height*$k, $width, $height); 
// Создаем новое изображение, нормального размера
$im2=imagecreatetruecolor($width, $height);
// Копируем изображение с изменением размеров в меньшую сторону
imagecopyresampled($im2, $im1, 0, 0, 0, 0, $width, $height, $width*$k, $height*$k); 

// Генерируем изображение
//imagepng($im);
imagepng($im2);

// Освобождаем память
imagedestroy($im2);
imagedestroy($im1);
imagedestroy($im);