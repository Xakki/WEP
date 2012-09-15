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
		list($width_orig, $height_orig) = getimagesize($InFile);// опред размер

		if(class_exists('Imagick',false)) {
			if(strpos($posX,'%')!==false)
				$posX = $width_orig*substr($posX,0,-1)/100;
			if(strpos($posY,'%')!==false)
				$posY = $height_orig*substr($posY,0,-1)/100;

			$thumb = new Imagick($InFile);
			$logo = new Imagick($logoFile);
			$thumb->compositeImage( $logo, imagick::COMPOSITE_DEFAULT, $posX, $posY );
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		} 
		else {
			//southeast //center
			//$cmd = 'composite -compose bumpmap -gravity south '.escapeshellarg($InFile).' '.escapeshellarg($logoFile).' '.escapeshellarg($OutFile);
			$cmd = 'convert '.escapeshellarg($InFile).' -gravity SouthWest -draw "image Over 0,0,0,0 '.escapeshellarg($logoFile).'" '.escapeshellarg($OutFile);
			$out=array();$err = 0;$run = exec($cmd, $out, $err);
			//print_r($cmd);
			//echo implode ("<br>",$out);
			//print_r($err);
			//print_r($run);
			if($err) {
				return static_imageGD2::_waterMark($InFile, $OutFile,$logoFile,$posX,$posY);
				static_main::log('error','Неверное выполнение команды "'.$cmd.'" , код ошибки - '.$err);
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

		if(class_exists('Imagick',false)) {
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
				return static_imageGD2::_resizeImage($InFile, $OutFile, $WidthX, $HeightY);
				static_main::log('error','Неверное выполнение команды "'.$cmd.'" , код ошибки - '.$err);
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

		if(class_exists('Imagick',false)) {
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
				return static_imageGD2::_cropImage($InFile, $OutFile, $WidthX, $HeightY,$posX,$posY);
				static_main::log('error','Неверное выполнение команды "'.$cmd.'" , код ошибки - '.$err);
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

		if(class_exists('Imagick',false)) {///// todo 
			$thumb = new Imagick($InFile);
			if(isset($GET['test'])) {
				print_r('thumbnailImage fit');
				$thumb->thumbnailImage($WidthX,$HeightY, true);
			}
			elseif(isset($GET['test2'])) {
				print_r('thumbnailImage');
				$thumb->thumbnailImage($WidthX,$HeightY);
			}
			else
			{
				print_r('cropThumbnailImage');
				$thumb->cropThumbnailImage($WidthX,$HeightY);
			}
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		}
		else 
		{
			//$cmd = 'convert '.$InFile.' -thumbnail "'.$WidthX.'x'.$HeightY.'" '.$OutFile;
			$cmd = 'convert '.escapeshellarg($InFile).' -resize "'.$WidthX.'x'.$HeightY.'^" -gravity center -crop '.$WidthX.'x'.$HeightY.'+0+0 +repage  '.escapeshellarg($OutFile);
			$out=array();$err = 0;$run = exec($cmd, $out, $err);
			/*print_r('***-<pre>');
			print_r($out);
			print_r($err);
			print_r($run);*/
			if($err) {
				return static_imageGD2::_thumbnailImage($InFile, $OutFile, $WidthX, $HeightY);
				static_main::log('error','Неверное выполнение команды "'.$cmd.'" , код ошибки - '.$err);
				$res = false;
			}
		}

		if($res) _chmod($OutFile);

		return $res;
	}

	static function _is_image($file) {
		return exif_imagetype($file);
	}

	static function _get_ext($file, $include_dot=false) {
		return image_type_to_extension($file, $include_dot);
	}
}
