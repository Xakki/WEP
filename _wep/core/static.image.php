<?php
/************************* IMAGE *****************************/
/*
	Реализованно для GD2 , Imagick PHP и ImageMagick
*/
class static_image {
	/**
	 * Наложение водяного знака (маркера)
	 *
	 */
	static function _waterMark($InFile, $OutFile,$logoFile='',$posX=0,$posY=0)
	{
		global $_CFG;
		if(!$logoFile)
			$logoFile = $_CFG['_imgwater'];
		$logoFile = $_CFG['_PATH']['path'].$logoFile;

		if(!$imtypeIn = self::_is_image($InFile))// опред тип файла
			return static_main::log('error','File '.$InFile.' is not image');
		$res = true;

		_chmod($InFile);

		if(class_exists('Imagick')) {
			$thumb = new Imagick($InFile);
			$logo = new Imagick($logoFile);
			$thumb->compositeImage( $logo, imagick::COMPOSITE_COPYOPACITY, $posX, $posY );
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		} 
		else {
			$p =  '-compose bumpmap -gravity south';//southeast //center
			$cmd = 'composite '.$p.' '.escapeshellarg($InFile).' '.escapeshellarg($OutFile);
			$out=array();$err = 0;$run = exec($cmd, $out, $err);
			//echo implode ("<br>",$out);
			//print_r($err);
			//print_r($run);
			if($err) {
				static_main::log('error','Exec error - '.$err);
				$res = false;
			}
		}

		if($res) _chmod($OutFile);

		return $res;
	}

	// Меняет размер. пропорционально, до минимального соответсявия по стороне
	static function _resizeImage($InFile, $OutFile, $WidthX, $HeightY)
	{
		$res = true;
		if(!$WidthX and !$HeightY) 
			return true;
		if(!$WidthX) $WidthX='';
		if(!$HeightY) $HeightY='';

		_chmod($InFile);

		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер

		// Если исходный меньше заданных размеров , то не меняем его и просто дублируем
		if($width_orig<$WidthX and $height_orig<$HeightY) { 
			if($InFile!=$OutFile) {
				copy($InFile,$OutFile);
				_chmod($OutFile);
			}
			return true;
		}

		if(class_exists('Imagick')) {
			$thumb = new Imagick($InFile);
			$thumb->resizeImage($WidthX,$HeightY,Imagick::FILTER_LANCZOS,1);
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		} 
		else {
			$cmd = 'convert '.escapeshellarg($InFile).' -resize '.$WidthX.'x'.$HeightY.' '.escapeshellarg($OutFile);
			$out=array();
			$err = 0;
			$run = exec($cmd, $out, $err);
			//echo implode ("<br>",$out);
			//print_r($err);
			//print_r($run);
			if($err) {
				static_main::log('error','Exec error - '.$err);
				$res = false;
			}
		}

		if($res) _chmod($OutFile);

		return $res;
	}
	
	// обрезает
	static function _cropImage($InFile, $OutFile, $WidthX, $HeightY,$posX=0,$posY=0)
	{
		$res = true;
		if(!$WidthX and !$HeightY) 
			return true;
		if(!$WidthX) $WidthX='';
		if(!$HeightY) $HeightY='';

		_chmod($InFile);

		if(class_exists('Imagick')) {
			$thumb = new Imagick($InFile);
			$thumb->cropImage($WidthX,$HeightY,$posX,$posY);
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		} 
		else {
			$cmd = 'convert '.escapeshellarg($InFile).' -gravity Center -crop '.$WidthX.'x'.$HeightY.'+0 '.escapeshellarg($OutFile);
			$out=array();
			$err = 0;
			$run = exec($cmd, $out, $err);
			//echo implode ("<br>",$out);
			//print_r($err);
			//print_r($run);
			if($err) {
				static_main::log('error','Exec error - '.$err);
				$res = false;
			}
		}

		if($res) _chmod($OutFile);

		return $res;
	}

	// Меняет размер обрезая
	static function _thumbnailImage($InFile, $OutFile, $WidthX, $HeightY)
	{
		$res = true;
		if(!$WidthX and !$HeightY) 
			return true;
		// Если одна из сторон не указана - значит квадрат
		if(!$WidthX) $WidthX=$HeightY;
		if(!$HeightY) $HeightY=$WidthX;

		_chmod($InFile);

		if(class_exists('Imagick')) {///// todo 
			$thumb = new Imagick($InFile);
			$thumb->cropThumbnailImage($WidthX2,$HeightY2);
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		} 
		else {
			//$cmd = 'convert '.$InFile.' -thumbnail "'.$WidthX.'x'.$HeightY.'" '.$OutFile;
			$cmd = 'convert '.escapeshellarg($InFile).' -resize "'.$WidthX.'x'.$HeightY.'^" -gravity center -crop '.$WidthX.'x'.$HeightY.'+0+0 +repage  '.escapeshellarg($OutFile);
			$out=array();$err = 0;$run = exec($cmd, $out, $err);
			//echo implode ("<br>",$out);
			//print_r($err);
			//print_r($run);
			if($err) {
				static_main::log('error','Exec error - '.$err);
				$res = false;
			}
		}

		if($res) _chmod($OutFile);

		return $res;
	}

	static function _is_image($file) {
		return exif_imagetype($file);
	}

	static function _get_ext($file) {
		return image_type_to_extension($file);
	}
}
