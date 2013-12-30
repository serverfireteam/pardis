<?php

class img {

	public function resize_image($source, $destination = NULL, $wdt, $height = NULL, $show = NULL) {

		list($real_width, $real_height) = getimagesize($source);	   

		if (empty($height)) {
        	if ($real_width > $real_height) {
            	$w = $wdt;
                $h = ($real_height / $real_width) * $w;
                $w = $w;
            } else {
                $w = $wdt;
                $h = $w;
                $w = ($real_width / $real_height) * $w;
            }
		} elseif (preg_match("@^max-([0-9]+)@i", $wdt, $mach_w) and preg_match("@^max-([0-9]+)@i", $height, $mach_h)) {

			$ratio = ( $real_width > $mach_w[1] ) ? (real)($mach_w[1] / $real_width) : 1;
			$w     = ((int)($real_width * $ratio));
			$h     = ((int)($real_height * $ratio));
			// check for images that are still too high
			$ratio = ( $h > $mach_h[1] ) ? (real)($mach_h[1] / $h) : 1;
			$w     = ((int)($w * $ratio)); //mid-size width
			$h     = ((int)($h * $ratio)); //mid-size height

		} elseif ($wdt == 'relative') {
        	$h = $height;
			$w = $real_width / ($real_height / $height);
        } else {
	        // Both width and Height are set.
        	// this will reshape to the new sizes.
            $w = $wdt;
            $h = $height;
        }
        $source_image = @file_get_contents($source) ;//or die('Could not open'.$source);
        $source_image = @imagecreatefromstring($source_image) ;//or die($source.' is not a valid image');
        $sw 		  = imagesx($source_image);
        $sh 		  = imagesy($source_image);
        $ar 		  = $sw/$sh;
        $tar 		  = $w/$h;
        if ($ar >= $tar) {
        	$x1 = round(($sw - ($sw * ($tar/$ar)))/2);
        	$x2 = round($sw * ($tar/$ar));
        	$y1 = 0;
        	$y2 = $sh;
        } else {
        	$x1 = 0;
        	$y1 = 0;
        	$x2 = $sw;
        	$y2 = round($sw/$tar);
        }
        $slate = @imagecreatetruecolor($w, $h);
        imagecopyresampled($slate, $source_image, 0, 0, $x1, $y1, $w, $h, $x2, $y2);
        // If $destination is not set this will output the raw image to the browser and not save the file
        if (!$destination || $show) {
	    	header('Content-type: image/jpeg');
	   		imagejpeg($slate);
	    }

	    imagejpeg($slate, $destination);
        ImageDestroy($slate);
        ImageDestroy($source_image);
        if (!$destination || $show) exit;
        return true;
	}

	public function check_size($img, $w, $h) {
	    list($width, $height) = getimagesize($img);
		return ($width == $w && $height == $h) ? true : false;
	}

	public static function check_img($img, $w, $h, $path = '', $show = '') {
		if (!file_exists($img)) {
		    return 'not found';
		}

		$resize_name = $path . img::findname($img) . '_' . $w . 'x' . $h . '.' . img::findexts($img);
		if (file_exists($resize_name)) {
			if ($show) {
				header('Content-type: image/jpeg');
				imagejpeg(imagecreatefromjpeg( $resize_name));
			} else {
		   		return $resize_name;
			}
		}

		if (!img::check_size($img, $w, $h)) {
			img::resize_image($img, $resize_name, $w, $h, $show);
			return $resize_name;
		}

		copy($img,$resize_name);

		return $resize_name;		 
	}

	public function findexts ($filename) {
	    $filename = strtolower($filename);
 		return preg_replace('/^.*\.([^.]+)$/D', '$1', $filename);
    }

    public function findname ($filename) {
	    return basename($filename, "." . img::findexts($filename));
    }
};

class GetImage {

	var $source;
	var $save_to;
	var $set_extension;
	var $quality;
	var $file_name;
	var $width;
	var $height;

	function __construct() {

	}

	function download($method = 'curl') // default method: curl
	{
		list($this->width, $this->height) = getimagesize($this->source);

		if (!$this->width | !$this->height) return;
		$info = getimagesize($this->source);
		$mime = $info['mime'];

		// What sort of image?
		$type = substr(strrchr($mime, '/'), 1);

		switch ($type) {
			case 'jpeg' :
			    $image_create_func = 'ImageCreateFromJPEG';
			    $image_save_func   = 'ImageJPEG';
				$new_image_ext     = 'jpg';
				// Best Quality: 100
				$quality           = isset($this->quality) ? $this->quality : 100;
			    break;

			case 'png' :
			    $image_create_func = 'ImageCreateFromPNG';
			    $image_save_func   = 'ImagePNG';
				$new_image_ext     = 'png';
				// Compression Level: from 0  (no compression) to 9
				$quality  		   = isset($this->quality) ? $this->quality : 0;
			    break;

			case 'bmp' :
			    $image_create_func = 'ImageCreateFromBMP';
			    $image_save_func   = 'ImageBMP';
				$new_image_ext     = 'bmp';
			    break;

			case 'gif' :
			    $image_create_func = 'ImageCreateFromGIF';
			    $image_save_func   = 'ImageGIF';
				$new_image_ext     = 'gif';
			    break;

			case 'vnd.wap.wbmp' :
			    $image_create_func = 'ImageCreateFromWBMP';
			    $image_save_func   = 'ImageWBMP';
				$new_image_ext     = 'bmp';
			    break;

			case 'xbm' :
			    $image_create_func = 'ImageCreateFromXBM';
			    $image_save_func   = 'ImageXBM';
				$new_image_ext     = 'xbm';
			    break;

			default :
				$image_create_func = 'ImageCreateFromJPEG';
			    $image_save_func   = 'ImageJPEG';
				$new_image_ext     = 'jpg';
		}

		if (isset($this->set_extension)) {
			$ext      = strrchr($this->source, ".");
			$strlen   = strlen($ext);
			$new_name = basename(substr($this->source, 0, -$strlen)).'.'.$new_image_ext;
		} else {
			$new_name = basename($this->source);
		}

		if (isset($this->file_name)) {
			$new_name = $this->file_name.strrchr($this->source, ".");
		}

		$save_to = $this->save_to . $new_name;

		if ($method == 'curl') {
			$save_image = $this->LoadImageCURL($save_to);
		} elseif ($method == 'gd') {

			$img = $image_create_func($this->source);

			if (isset($quality)) {
				$save_image = $image_save_func($img, $save_to, $quality);
			} else {
				$save_image = $image_save_func($img, $save_to);
			}
		}

		return $save_image;
	}

	function LoadImageCURL($save_to)
	{
		$ch = curl_init($this->source);
		$fp = fopen($save_to, "wb");

		// set URL and other appropriate options
		$options = array(CURLOPT_FILE 			=> $fp,
        		         CURLOPT_HEADER 		=> 0,
                		 CURLOPT_FOLLOWLOCATION => 1,
			             CURLOPT_TIMEOUT 	    => 4); // 1 minute timeout (should be enough)

		curl_setopt_array($ch, $options);

		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
};
?>
