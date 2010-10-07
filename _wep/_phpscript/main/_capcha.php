<?
//DECODE
$data = $_SESSION["captha"];

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
?>