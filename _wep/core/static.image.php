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
		$logoFile = SITE.$logoFile;

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
	// TODo - куму нужна вообще эта функция, которая менятет размер картинки без сохранения соотношения сторон?
	static function _resizeImage($InFile, $OutFile, $WidthX, $HeightY)
	{
		trigger_error('_resizeImage', E_USER_WARNING);
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

		$crop = true;
		if(!$WidthX) {
			$WidthX=$HeightY;
			$crop = false;
		}
		if(!$HeightY) {
			$HeightY=$WidthX;
			$crop = false;
		}

		_chmod($InFile);

		if(class_exists('Imagick',false)) {///// todo 
			$thumb = new Imagick($InFile);
			if($crop)
				$thumb->cropThumbnailImage($WidthX,$HeightY);
			else
				$thumb->thumbnailImage($WidthX,$HeightY, true);
			$res = $thumb->writeImage($OutFile);
			$thumb->destroy();
		}
		else 
		{
			if($crop)
				$cmd = 'convert '.escapeshellarg($InFile).' -resize "'.$WidthX.'x'.$HeightY.'^" -gravity center -crop '.$WidthX.'x'.$HeightY.'+0+0 +repage  '.escapeshellarg($OutFile);
			else
				$cmd = 'convert '.escapeshellarg($InFile).' -thumbnail "'.$WidthX.'x'.$HeightY.'" '.escapeshellarg($OutFile);
			$out=array();$err = 0;$run = exec($cmd, $out, $err);
			if($err) {
				return static_imageGD2::_thumbnailImage($InFile, $OutFile, $WidthX, $HeightY);
			}
			$res = static_main::log('error','Неверное выполнение команды "'.$cmd.'" , код ошибки - '.$err);
		}

		if($res) 
			_chmod($OutFile);

		return $res;
	}

	static function _is_image($file) 
	{
		return exif_imagetype($file);
	}

	static function _get_ext($file, $include_dot=false) 
	{
		return image_type_to_extension($file, $include_dot);
	}

	// get image color in RGB format function 
	static function getImageColor($imageFile_URL, $numColors = 10, $image_granularity = 5, $round = 0x33)
	{
   		$image_granularity = max(1, abs((int)$image_granularity));
   		$colors = array();
   		//find image size
   		$size = getimagesize($imageFile_URL);
   		if($size === false)
   		{
      		trigger_error("Unable to get image size data", E_USER_ERROR);
      		return false;
   		}
   		// open image
   		//$img = @imagecreatefromjpeg($imageFile_URL);
   		$img = static_imageGD2::_imagecreatefrom($imageFile_URL);
   		if(!$img)
   		{
   	  		trigger_error("Unable to open image file", E_USER_ERROR);
   		   return false;
   		}
   		
   		// fetch color in RGB format
   		for($x = 0; $x < $size[0]; $x += $image_granularity)
   		{
      		for($y = 0; $y < $size[1]; $y += $image_granularity)
      		{
         		$thisColor = imagecolorat($img, $x, $y);
         		$rgb = imagecolorsforindex($img, $thisColor);

         		if($round) {
	        		$rgb['red'] = round(round(($rgb['red'] / $round)) * $round);
	         		$rgb['green'] = round(round(($rgb['green'] / $round)) * $round);
	         		$rgb['blue'] = round(round(($rgb['blue'] / $round)) * $round);
	         	}

         		$thisRGB = sprintf('%02X%02X%02X', $rgb['red'], $rgb['green'], $rgb['blue']);
         		if(array_key_exists($thisRGB, $colors))
         		{
           			 $colors[$thisRGB]++;
         		}
         		else
         		{
           			 $colors[$thisRGB] = 1;
         		}
      		}
   		}
   		arsort($colors);
   		// returns maximum used color of image format like #C0C0C0.
   		if($numColors<1) // Используем процентную выборку, относительно максимального цвета
   		{
   			reset($colors);
   			$max = current($colors);
   			$result = array();
   			foreach($colors as $k=>$r) 
   			{
   				if( ($r/$max) < $numColors) break;
   				$result[$k] = $r;
   			}
   			return $result;
   		}
   		else 
   		{
	   		return array_slice(($colors), 0, $numColors,true);
	   	}
	}

	/**
	* RGB-Colorcodes(i.e: 255 0 255) to HEX-Colorcodes (i.e: FF00FF)
	* example - print_r(rgb2hex(array(10,255,255)));
	*/
	static function rgb2hex($rgb)
	{
        if(strlen($hex = dechex($rgb)) == 1)
        {
            $hex = "0".$hex;
        }
	    return $hex;
	}
	/**
	* html(HEX) color to convert in RGB format color like R(255) G(255) B(255)  
	*/
	static function hex2rgb($str_color)
	{
		$str_color = (string) $str_color;
    	if ($str_color[0] == '#')
        	$str_color = substr($str_color, 1);

  	  	if (strlen($str_color) == 6)
        	list($r, $g, $b) = array($str_color[0].$str_color[1],
                                 $str_color[2].$str_color[3],
                                 $str_color[4].$str_color[5]);
    	elseif (strlen($str_color) == 3)
        	list($r, $g, $b) = array($str_color[0].$str_color[0], $str_color[1].$str_color[1], $str_color[2].$str_color[2]);
    	else
        	return false;

    	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    	return array('r' => $r, 'g' => $g, 'b' => $b);
	}

	static function rgb2hsv( $rgb )
	{
		list($r, $g, $b) = array_values($rgb);
		$min = MIN( $r, $g, $b );
		$max = MAX( $r, $g, $b );
		$v = $max;				// v
		$delta = $max - $min;
		if( $max != 0 )
			$s = $delta / $max;		// s
		else {
			// r = g = b = 0		// s = 0, v is undefined
			$s = 0;
			$h = -1;
			return array('h' => $h, 's' => $s, 'v' => $v);
		}
		if( $r == $max )
			$h = ( $g - $b ) / $delta;		// between yellow & magenta
		else if( $g == $max )
			$h = 2 + ( $b - $r ) / $delta;	// between cyan & yellow
		else
			$h = 4 + ( $r - $g ) / $delta;	// between magenta & cyan
		$h *= 60;				// degrees
		if( $h < 0 )
			$h += 360;
		return array('h' => $h, 's' => $s, 'v' => $v);
	}

	static function hex2hsv($str_color)
	{
		return self::rgb2hsv(self::hex2rgb($str_color));
	}

	// Y'UV444 to RGB888 conversion
	// NTSC standard
	static function RGBtoYUV( $rgb )
	{
		$Y = 0.299*$rgb['r'] + 0.587*$rgb['g'] + 0.114*$rgb['b'];
		$U = - 0.147*$rgb['r'] - 0.289*$rgb['g'] + 0.436 *$rgb['b'];
		$V = 0.615*$rgb['r'] - 0.515*$rgb['g'] - 0.1 *$rgb['b'];
		return array('y' => $Y, 'u' => $U, 'v' => $V);
	}
	
	// Y'UV444 to RGB888 conversion
	// The ITU-R version:
	static function RGBtoYUV2( $rgb )
	{
		$Y = 0.299*$rgb['r'] + 0.587*$rgb['g'] + 0.114*$rgb['b'];
		$U = - 0.169*$rgb['r'] - 0.331*$rgb['g'] + 0.499 *$rgb['b'] + 128;
		$V = 0.499*$rgb['r'] - 0.418*$rgb['g'] - 0.0813 *$rgb['b'] + 128;
		return array('y' => $Y, 'u' => $U, 'v' => $V);
	}


	static function deferenceColorHEX($hex1, $hex2)
	{
		return self::deferenceColorRGB(self::hex2rgb($hex1), self::hex2rgb($hex2));
	}
	static function deferenceColorRGB($rgb1, $rgb2)
	{
		return 30*pow($rgb1['r']-$rgb2['r'], 2)+59*pow($rgb1['g']-$rgb2['g'], 2)+11*pow($rgb1['b']-$rgb2['b'], 2);
	}



	static function getTrueColor($imageColors, $trueColors)
	{
		$min = 360;
		$color = 0;
		$resultColor = '';
		foreach($imageColors as $imageColor) {
			$imageColorRGB = self::hex2rgb($imageColor);

			foreach($trueColors as $k=>$r) {
				$tempMin = self::deferenceColorRGB3($r['rgb'], $imageColorRGB);
				if($tempMin<50 && $tempMin<$min) {
					$color = $imageColor;
					$min = $tempMin;
					$resultColor = $k;
				}
			}
			
		}
		$trueColors[$resultColor]['min'] = $min;
		$trueColors[$resultColor]['color'] = $color;
		$trueColors[$resultColor]['key'] = $resultColor;
		return $trueColors[$resultColor];
	}

	static function rgbColorList($enumsColors)
	{
		$trueColors = array();

		foreach($enumsColors as $k=>$r) {
			$trueColors[$k] = array('rgb' => static_image::hex2rgb($r), 'hex' => $r);
		}
		return $trueColors;
	}

	////////////////////////////////////


	static function deferenceColorHEX2($hex1, $hex2)
	{
		return self::deferenceColorRGB2(self::hex2rgb($hex1), self::hex2rgb($hex2));
	}
	static function deferenceColorRGB2($rgb1, $rgb2)
	{
		$dR = $rgb1['r'] - $rgb2['r'];
		$dG = $rgb1['g'] - $rgb2['g'];
		$dB = $rgb1['b'] - $rgb2['b'];
		$result = sqrt(pow($dR, 2)*0.2126 + pow($dG, 2)*0.7152 + pow($dB, 2)*0.0722);
		return  $result;
	}	

	static function deferenceColorHEX3($hex1, $hex2)
	{
		return self::deferenceColorRGB3(self::hex2rgb($hex1), self::hex2rgb($hex2));
	}
	static function deferenceColorRGB3($rgb1, $rgb2)
	{
		$HSV1 = self::rgb2hsv($rgb1);
		$HSV2 = self::rgb2hsv($rgb2);
		// if($HSV1['v']<22 && $HSV2['v']<22) {
		// 	return 0;
		// }
		// if($HSV1['s']<17 || $HSV2['s']<17) {
		// 	return 0;
		// }
		return abs($HSV1['h']-$HSV2['h']);
	}	

}
