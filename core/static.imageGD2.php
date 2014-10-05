<?php

class static_imageGD2
{
	/************************* IMAGE *****************************/
	/*
		Реализованно для GD2
		TODO - imagemagic
	*/

	/**
	 * Наложение водяного знака (маркера)
	 *
	 */
	static function _waterMark($InFile, $OutFile, $logoFile = '', $posX = 0, $posY = 0)
	{
		global $_CFG;
		if (!$logoFile)
			$logoFile = $_CFG['_imgwater'];

		if (!$imtypeIn = self::_get_type($InFile)) // опред тип файла
			return static_main::log('error', 'File ' . $InFile . ' is not image');
		if ($imtypeIn > 3) return false;

		if (!$imtypeLogo = self::_get_type($logoFile)) // опред тип файла
			return static_main::log('error', 'File ' . $logoFile . ' is not image');
		if ($imtypeLogo > 3) return false;

		$znak_hw = getimagesize($logoFile);
		$foto_hw = getimagesize($InFile);

		$znak = self::_imagecreatefrom($logoFile, $imtypeLogo);
		if (!$znak) {
			return static_main::log('error', "Unable to open image file");
		}

		$foto = self::_imagecreatefrom($InFile, $imtypeIn);
		if (!$foto) {
			return static_main::log('error', "Unable to open image file");
		}

		imagecopy(
			$foto,
			$znak,
			$foto_hw[0] - $znak_hw[0],
			$foto_hw[1] - $znak_hw[1],
			0,
			0,
			$znak_hw[0],
			$znak_hw[1]
		);
		if (file_exists($OutFile)) {
			_chmod($OutFile);
			unlink($OutFile);
		}
		self::_image_to_file($foto, $OutFile, $_CFG['_imgquality'], $imtypeIn); //сохраняем в файл
		imagedestroy($znak);
		imagedestroy($foto);
		if (!file_exists($OutFile)) {
			return static_main::log('error', 'Cant composite file on ' . __LINE__ . ' in kernel');
		}
		return true;
	}

	// Меняет размер. пропорционально, до минимального соответсявия по стороне
	static function _resizeImage($InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		_chmod($InFile);
		list($width_orig, $height_orig) = getimagesize($InFile); // опред размер

		if (!$WidthX and !$HeightY) {
			return true;
		}
		if (!$WidthX) {
			$WidthX = ($width_orig * $HeightY) / $height_orig;
		}
		if (!$HeightY) {
			$HeightY = ($height_orig * $WidthX) / $width_orig;
		}

		if ($width_orig < $WidthX and $height_orig < $HeightY) {
			if ($InFile != $OutFile) {
				copy($InFile, $OutFile);
				global $_CFG;
				_chmod($OutFile);
			}
			return true;
		}
		elseif ($width_orig / $WidthX < $height_orig / $HeightY) {
			$WidthX = round($HeightY * $width_orig / $height_orig);
		}
		elseif ($width_orig / $WidthX > $height_orig / $HeightY) {
			$HeightY = round($WidthX * $height_orig / $width_orig);
		}

		$thumb = imagecreatetruecolor($WidthX, $HeightY); //созд пустой рисунок
		if (!$imtype = self::_get_type($InFile)) // опред тип файла
			return static_main::log('error', 'File ' . $InFile . ' is not image');

		if ($imtype > 3) {
			static_main::log('alert', 'Данный тип изображения не поддерживается на данный момент, рекомендуем использовать JPEG, PNG или GIF');
			copy($InFile, $OutFile);
			return true;
		}

		$source = self::_imagecreatefrom($InFile, $imtype); //открываем рисунок
		if (!$source) {
			return static_main::log('error', "Unable to open image file");
		}

		imagecopyresized($thumb, $source, 0, 0, 0, 0, $WidthX, $HeightY, $width_orig, $height_orig); //меняем размер
		self::_image_to_file($thumb, $OutFile, $_CFG['_imgquality'], $imtype); //сохраняем в файл
		if (!file_exists($OutFile)) return static_main::log('error', 'Cant create file');
		return true;
	}

	// обрезает
	static function _cropImage($InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		_chmod($InFile);
		list($width_orig, $height_orig) = getimagesize($InFile); // опред размер

		if (!$WidthX and !$HeightY)
			return true;
		if (!$WidthX)
			$WidthX = ($width_orig * $HeightY) / $height_orig;
		if (!$HeightY) {
			$HeightY = ($height_orig * $WidthX) / $width_orig;
		}

		// Resample
		$thumb = imagecreatetruecolor($WidthX, $HeightY); //созд пустой рисунок
		if (!$imtype = self::_get_type($InFile)) // опред тип файла
			return static_main::log('error', 'File is not image');
		if ($imtype > 3) {
			static_main::log('alert', 'Данный тип изображения не поддерживается на данный момент, рекомендуем использовать JPEG, PNG или GIF');
			copy($InFile, $OutFile);
			return true;
		}
		$source = self::_imagecreatefrom($InFile, $imtype); //открываем рисунок
		if (!$source) {
			return static_main::log('error', "Unable to open image file");
		}

		imagecopyresampled($thumb, $source, 0, 0, $width_orig / 2 - $WidthX / 2, $height_orig / 2 - $HeightY / 2, $WidthX, $HeightY, $WidthX, $HeightY);
		self::_image_to_file($thumb, $OutFile, $_CFG['_imgquality'], $imtype); //сохраняем в файл
		if (!file_exists($OutFile)) return static_main::log('error', 'Cant create img file ');
		return true;
	}

	// Меняет размер обрезая
	static function _thumbnailImage($InFile, $OutFile, $WidthX, $HeightY)
	{
		global $_CFG;
		_chmod($InFile);

		list($width_orig, $height_orig) = getimagesize($InFile);

		$src_width = $width_orig;
		$src_height = $height_orig;
		$src_x = $src_y = 0;
		$ratio_orig = $width_orig / $height_orig; // пропорции исходной картинки
		$ratio_dist = $WidthX / $HeightY; // пропорции заданного размера
		if ($ratio_dist != $ratio_orig) {
			if ($ratio_dist > $ratio_orig) {
				$src_height = $src_width / $ratio_dist;
				$src_y = abs(($src_height - $height_orig) / 2);
			}
			else {
				$src_width = $src_height * $ratio_dist;
				$src_x = abs(($src_width - $width_orig) / 2);
			}
		}

//        $src_x = $WidthX/2 - $trueX/2;
//        $src_y = $HeightY/2 - $trueY/2;

		/*Определяем тип рисунка*/
		if (!$imtype = self::_get_type($InFile)) // опред тип файла
			return static_main::log('error', 'File is not image');

		/*Обработка только jpeg, gif, png*/
		if ($imtype > 3) {
			static_main::log('alert', 'Данный тип изображения не поддерживается на данный момент, рекомендуем использовать JPEG, PNG или GIF');
			copy($InFile, $OutFile);
			return false;
		}
		/*Создаем пустое изображение на вывод*/
		if (!($thumb = imagecreatetruecolor($WidthX, $HeightY)))
			return static_main::log('error', 'Cannot Initialize new GD image stream');

		/*Открываем исходный рисунок*/
		if (!$source = self::_imagecreatefrom($InFile, $imtype)) //открываем рисунок
			return static_main::log('error', 'File ' . $InFile . ' is not image');

		if (!imagecopyresampled($thumb, $source, 0, 0, $src_x, $src_y, $WidthX, $HeightY, $src_width, $src_height))
			return static_main::log('error', 'Error imagecopyresampled');

//		if(!($thumb2 = imagecreatetruecolor($WidthX, $HeightY)))
//			return static_main::log('error','Cannot Initialize new GD image stream');
//
//        if(!imagecopyresampled($thumb2, $thumb, 0, 0, $src_x, $src_y , $WidthX, $HeightY, $trueX, $trueY))
//            return static_main::log('error','Error imagecopyresampled');

		self::_image_to_file($thumb, $OutFile, $_CFG['_imgquality'], $imtype); //сохраняем в файл
		if (!file_exists($OutFile)) return static_main::log('error', 'Cant create file');
		return true;
	}

	static function _imagecreatefrom($im_file, $imtype = null)
	{
		if (is_null($imtype)) {
			$imtype = self::_get_type($im_file);
		}
		/*
Возвращаемое значение	Константа
1	IMAGETYPE_GIF
2	IMAGETYPE_JPEG
3	IMAGETYPE_PNG
4	IMAGETYPE_SWF
5	IMAGETYPE_PSD
6	IMAGETYPE_BMP
7	IMAGETYPE_TIFF_II
8	IMAGETYPE_TIFF_MM
9	IMAGETYPE_JPC
10	IMAGETYPE_JP2
11	IMAGETYPE_JPX
12	IMAGETYPE_JB2
13	IMAGETYPE_SWC
14	IMAGETYPE_IFF
15	IMAGETYPE_WBMP
16	IMAGETYPE_XBM
		*/
		if ($imtype == 1) {
			if (!($image = @imagecreatefromgif($im_file)))
				return static_main::log('error', 'Can not create a new image from file');
		}
		elseif ($imtype == 2) {
			if (!($image = imagecreatefromjpeg($im_file)))
				return static_main::log('error', 'Can not create a new image from file');
		}
		elseif ($imtype == 3) {
			if (!($image = imagecreatefrompng($im_file)))
				return static_main::log('error', 'Can not create a new image from file');
		}
		else return false;
		return $image;
	}

	static function _image_to_file($im, $file, $q, $imtype)
	{
		static_tools::_checkdir(dirname($file));
		if ($imtype == 1) imagegif($im, $file, $q);
		elseif ($imtype == 2) imagejpeg($im, $file, $q);
		elseif ($imtype == 3) imagepng($im, $file, 8);
		else return false;
		return true;
	}

	static function _is_image($file)
	{
		$res = exif_imagetype($file);
		return ($res > 0 && $res < 4);
	}

	static function _get_type($file)
	{
		return exif_imagetype($file);
	}

	static function _get_ext($file)
	{
		return image_type_to_extension($file);
	}
}
