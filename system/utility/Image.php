<?php
require_once("File.php");

class Image extends File {
    var $width;
    var $height;
    var $mime;
    var $bits;
    var $channels;
    var $type;

    /**
     * Initialize the image object with file and image properties read from the file.
     *
     * @param string $file
     */
    function __construct($file) {
    // Set basic file information
        parent::__construct($file);

        $imageInfo = getimagesize($this->file);

        // Check to see if the file is an image
        if(!$imageInfo) {
            throw new Exception("\"".$file."\" is not a valid image file.");
        }

        $imageTypes = array(1 => "gif", 2 => "jpg", 3 => "png", 4 => "swf", 5 => "psd", 6 => "bmp", 7 => "tiff", 8 => "tiff", 9 => "jpc", 10 => "jp2", 11 => "jpx", 12 => "jb2", 13 => "swc", 14 => "iff", 15 => "wbmp", 16 => "xbm");

        $this->width = $imageInfo[0];
        $this->height = $imageInfo[1];
        $this->mime = $imageInfo['mime'];
        $this->bits = $imageInfo['bits'];
        if(isset($imageInfo['channels'])) {
            $this->channels = $imageInfo['channels'];
        }
        $this->type = $imageTypes[$imageInfo[2]];

        return $this;
    }
    
    function blur($distance = 1) {
        $imageResource = imagecreatefromjpeg($this->file);
        
        $imageX = imagesx($imageResource);
        $imageY = imagesy($imageResource);

        for($x = 0; $x < $imageX; ++$x) {
            for($y = 0; $y < $imageY; ++$y) {
                $newR = 0;
                $newG = 0;
                $newB = 0;

                $colors = array();
                $currentColor = imagecolorat($imageResource, $x, $y);

                for($k = $x - $distance; $k <= $x + $distance; ++$k) {
                    for($l = $y - $distance; $l <= $y + $distance; ++$l) {
                        if($k < 0) {
                            $colors[] = $currentColor;
                            continue;
                        }
                        if($k >= $imageX) {
                            $colors[] = $currentColor;
                            continue;
                        }
                        if($l < 0) {
                            $colors[] = $currentColor;
                            continue;
                        }
                        if($l >= $imageY) {
                            $colors[] = $currentColor;
                            continue;
                        }
                        $colors[] = imagecolorat($imageResource, $k, $l);
                    }
                }

                foreach($colors as $colour) {
                    $newR += ($colour >> 16) & 0xFF;
                    $newG += ($colour >> 8) & 0xFF;
                    $newB += $colour & 0xFF;
                }

                $elementCount = count($colors);
                $newR /= $elementCount;
                $newG /= $elementCount;
                $newB /= $elementCount;

                $newColor = imagecolorallocate($imageResource, $newR, $newG, $newB);
                imagesetpixel($imageResource, $x, $y, $newColor);
            }
        }
        
        imagejpeg($imageResource, $this->file, 100);
        
        return $this;
    }
    
    function jpgQuality($quality = 100) {
        $imageResource = imagecreatefromjpeg($this->file);
        imagejpeg($imageResource, $this->file, $quality);
        
        return $this;
    }

    function gaussianBlur($passes = 1) {
        $imageResource = imagecreatefromjpeg($this->file);
        for($i = 0; $i < $passes; $i++) {
            $imageFilter = imagefilter($imageResource, IMG_FILTER_GAUSSIAN_BLUR);    
        }
        imagejpeg($imageResource, $this->file, 100);
        
        return $this;
    }
    
    function opacity($level = 50) {
        $imageSource = imagecreatefromjpeg($this->file);
        $imageDestination = imagecreatefromjpeg($this->file);
        $white = imagecolorallocate($imageResource, 255, 255, 255);
        imagecolortransparent($imageDestination, $white);
        imagefilledrectangle($imageDestination, 0, 0, $this->width, $this->height, $white);
        imagecopymerge($imageDestination, $imageSource, 0, 0, 0, 0, $this->width, $this->height, $level);
        imagejpeg($imageDestination, $this->file, 100);
        
        return $this;
    }
    
    function brightness($level = 50) {
        $imageResource = imagecreatefromjpeg($this->file);
        $imageFilter = imagefilter($imageResource, IMG_FILTER_BRIGHTNESS, $level);    
        imagejpeg($imageResource, $this->file, 100);
        
        return $this;
    }

    /**
     * Convert an image into a jpg. Supports jpeg, gif, png, and bmp.
     *
     * @param boolean $destroyOriginal
     * @return Image
     */
    public function convertToJpg($destroyOriginal = true) {
        $conversionOccurred = false;
        $jpgFile = $this->fileWithoutExtension.".jpg";

        // If the image is a jpeg
        if($this->extension == "jpeg" && $this->type == "jpg") {
            $this->copy($this->nameWithoutExtension.".jpg");
            $conversionOccurred = true;
        }
        // If the image is already a jpg with a .jpg extension
        else if($this->extension == "jpg" && $this->type == "jpg") {
            $destroyOriginal = false;
            $conversionOccurred = true;
        }
        // If the image is a png
        else if($this->type == "png") {
            imagejpeg(imagecreatefrompng($this->file), $jpgFile, 100);
            $conversionOccurred = true;
        }
        // If the image is a gif
        else if($this->type == "gif") {
            imagejpeg(imagecreatefromgif($this->file), $jpgFile, 100);
            $conversionOccurred = true;
        }
        // Use a custom function to convert to bmp as GD doesn't natively handle bmp
        else if($this->type == "bmp") {
            imagejpeg(Image::imagecreatefrombmp($this->file), $jpgFile, 100);
            $conversionOccurred = true;
        }

        if($conversionOccurred) {
            if($destroyOriginal) {
                unlink($this->file);
            }
            return new Image($jpgFile);
        }
        else {
            return false;
        }
    }

    /**
     * Resize a jpg image.
     *
     * @param int $width
     * @param int $height
     * @param int $scale
     * @return Image
     */
    public function resizeJpg($width, $height, $scale = 1) {
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        $sourceImage = imagecreatefromjpeg($this->file);
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $this->width, $this->height);
        imagejpeg($newImage, $this->file, 100);

        return $this->__construct($this->file);
    }

    /**
     * Scale an image to fit specified dimensions.
     *
     * @param int $maximumWidth
     * @param int $maximumHeight
     * @param int $minimumWidth
     * @param int $minimumHeight
     * @return Image
     */
    public function scale($maximumWidth, $maximumHeight, $minimumWidth, $minimumHeight) {
    // Identify what the width should be
        if($this->width <= $maximumWidth && $this->width >= $minimumWidth) {
            $scaleWidth = $this->width;
        }
        else if($this->width > $maximumWidth) {
                $scaleWidth = $maximumWidth;
            }
            else if($this->width < $minimumWidth) {
                    $scaleWidth = $minimumWidth;
                }

        // Identify what the height should be
        if($this->height <= $maximumHeight && $this->height >= $minimumHeight) {
            $scaleHeight = $this->height;
        }
        else if($this->height > $maximumHeight) {
                $scaleHeight = $maximumHeight;
            }
            else if($this->height < $minimumHeight) {
                    $scaleHeight = $minimumHeight;
                }

        // Maintain the aspect ratio
        $aspectRatio = $this->width / $this->height;
        if(($this->height / $scaleHeight) > ($this->width / $scaleWidth)) {
            $scaleWidth = ceil($aspectRatio * $scaleHeight);
        }
        else {
            $scaleHeight = ceil($scaleWidth / $aspectRatio);
        }

        // At this point in attempting to maintain the aspect ratio the minimum width and height restrictions may have been disregarded
        if($scaleWidth < $minimumWidth || $scaleHeight < $minimumHeight) {
        // To correct we must identify the aspect ratio of the min width and min height and crop to the center of the image before scaling
            $minimumAspectRatio = $minimumWidth / $minimumHeight;
            if($aspectRatio > $minimumAspectRatio) {
                $cropWidth = $this->height * $minimumAspectRatio;
                $cropHeight = $this->height;
                $cropX = ($this->width / 2) - ($cropWidth / 2);
                $cropY = 0;
            }
            else {
                $cropWidth = $this->width;
                $cropHeight = $this->width / $minimumAspectRatio;
                $cropX = 0;
                $cropY = ($this->height / 2) - ($cropHeight / 2);
            }

            // Crop and scale the image
            $image = $this->crop($cropX, $cropY, $cropWidth, $cropHeight, $this->file);
            $image = $image->scale($maximumWidth, $maximumHeight, $minimumWidth, $minimumHeight);
            return $this->__construct($image->file);
        }
        else {
            $this->resizeJpg($scaleWidth, $scaleHeight, 1);
        }

        return $this->__construct($this->file);
    }

    /**
     * Crop an image.
     *
     * @param int $cropX
     * @param int $cropY
     * @param int $cropWidth
     * @param int $cropHeight
     * @param string $croppedFile
     * @return Image
     */
    public function crop($cropX, $cropY, $cropWidth, $cropHeight, $croppedFile = "-cropped.jpg") {
        if($croppedFile == "-cropped.jpg") {
            $croppedFile = $this->fileWithoutExtension.$croppedFile;
        }

        if($cropWidth == 0) {
            $cropWidth = $this->width;
        }
        if($cropHeight == 0) {
            $cropHeight = $this->height;
        }

        // Check to see if we need to crop
        if($this->width != $cropWidth || $this->height != $cropHeight) {
            $canvas = imagecreatetruecolor($cropWidth, $cropHeight);
            $source = imagecreatefromjpeg($this->file);
            imagecopy($canvas, $source, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight);
            // Remove the cropped file if it already exists
            if(file_exists($croppedFile)) {
                chmod($croppedFile, 0777);
                unlink($croppedFile);
            }
            imagejpeg($canvas, $croppedFile, 100);
            imagedestroy($canvas);
            imagedestroy($source);
        }
        // No need to crop, make cropped copy anyway
        else if($this->file != $croppedFile) {
                copy($this->file, $croppedFile);
            }

        // Change the permissions and return the image
        chmod($croppedFile, 0777);
        if(file_exists($croppedFile)) {
            return new Image($croppedFile);
        }
        else {
            return false;
        }
    }
    
    public static function aspectRatioExact($width, $height) {
        $greatestCommonDivisor = Number::greatestCommonDivisor($width, $height);
        
        return ($width / $greatestCommonDivisor) . ":" . ($height / $greatestCommonDivisor);
    }
    
    public static function aspectRatio($a, $b) {
        $total = intval($a) + intval($b);
        for($i = 1; $i <= 40; $i++) {
            $arx = $i * 1.0 * $a / $total;
            $brx = $i * 1.0 * $b / $total;
            if($i == 40 || (
                    abs($arx - round($arx)) <= 0.02 &&
                    abs($brx - round($brx)) <= 0.02)) {
                // Accept aspect ratios within a given tolerance
                return round($arx).':'.round($brx);
            }
        }
    }
    
    /*     * ******************************************************
     *   Fingerprint
     *
     *   This function analyses the filename passed to it and
     *   returns an md5 checksum of the file's histogram.
     * ****************************************************** */
    static function fingerprint($file, $thumbWidth = 150, $sensitivity = 2) {
        // Load the image. Escape out if it's not a valid jpeg.
        if(!$image = @imagecreatefromjpeg($file)) {
            return -1;
        }

        // Create thumbnail sized copy for fingerprinting
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = $thumbWidth / $width;
        $newwidth = $thumbWidth;
        $newheight = round($height * $ratio);
        $smallimage = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($smallimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        $palette = imagecreatetruecolor(1, 1);
        $gsimage = imagecreatetruecolor($newwidth, $newheight);

        // Convert each pixel to greyscale, round it off, and add it to the histogram count
        $numpixels = $newwidth * $newheight;
        $histogram = array();
        for($i = 0; $i < $newwidth; $i++) {
            for($j = 0; $j < $newheight; $j++) {
                $pos = imagecolorat($smallimage, $i, $j);
                $cols = imagecolorsforindex($smallimage, $pos);
                $r = $cols['red'];
                $g = $cols['green'];
                $b = $cols['blue'];
                // Convert the colour to greyscale using 30% Red, 59% Blue and 11% Green
                $greyscale = round(($r * 0.3) + ($g * 0.59) + ($b * 0.11));
                $greyscale++;
                $value = (round($greyscale / 16) * 16) - 1;
                if(!isset($histogram[$value])) {
                    $histogram[$value] = 0;
                }
                $histogram[$value] = $histogram[$value] + 1;
            }
        }

        // Normalize the histogram by dividing the total of each colour by the total number of pixels
        $normhist = array();
        foreach($histogram as $value => $count) {
            $normhist[$value] = $count / $numpixels;
        }

        // Find maximum value (most frequent colour)
        $max = 0;
        for($i = 0; $i < 255; $i++) {
            if(isset($normhist[$i]) && $normhist[$i] > $max) {
                $max = $normhist[$i];
            }
        }

        // Create a string from the histogram (with all possible values)
        $histstring = "";
        
        for($i = -1; $i <= 255; $i = $i + 16) {
            if(!isset($normhist[$i])) {
                $h = 0;                
            }
            else {
                $h = ($normhist[$i] / $max) * $sensitivity;
                if($i < 0) {
                    $index = 0;
                }
                else {
                    $index = $i;
                }
            }
                
            $height = round($h);
            $histstring .= $height;    
        }

        // Destroy all the images that we've created
        imagedestroy($image);
        imagedestroy($smallimage);
        imagedestroy($palette);
        imagedestroy($gsimage);

        // Generate an md5sum of the histogram values and return it
        $checksum = md5($histstring);
        return $checksum;
    }

    /**
     * Create an image from a bmp. GD doesn't handle this natively.
     *
     * @param string $filename
     * @return image
     */
    public static function imagecreatefrombmp($filename) {
    // Open the file in binary mode
        if(!$f1 = fopen($filename,"rb")) return FALSE;
        // 1. Load the file
        $file = unpack('vfile_type/Vfile_size/Vreserved/Vbitmap_offset', fread($f1, 14));
        if($file['file_type'] != 19778) return FALSE;
        // 2. Load the BMP settings
        $bmp = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
            '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
            '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
        $bmp['colors'] = pow(2,$bmp['bits_per_pixel']);
        if ($bmp['size_bitmap'] == 0) $bmp['size_bitmap'] = $file['file_size'] - $file['bitmap_offset'];
        $bmp['bytes_per_pixel'] = $bmp['bits_per_pixel']/8;
        $bmp['bytes_per_pixel2'] = ceil($bmp['bytes_per_pixel']);
        $bmp['decal'] = ($bmp['width']*$bmp['bytes_per_pixel']/4);
        $bmp['decal'] -= floor($bmp['width']*$bmp['bytes_per_pixel']/4);
        $bmp['decal'] = 4-(4*$bmp['decal']);
        if ($bmp['decal'] == 4) $bmp['decal'] = 0;
        // 3. Load the color pallete
        $palette = array();
        if ($bmp['colors'] < 16777216) {
            $palette = unpack('V'.$bmp['colors'], fread($f1,$bmp['colors']*4));
        }
        // 4. Create the image
        $img = fread($f1,$bmp['size_bitmap']);
        $vide = chr(0);
        $res = imagecreatetruecolor($bmp['width'],$bmp['height']);
        $P = 0;
        $Y = $bmp['height']-1;
        while($Y >= 0) {
            $X=0;
            while($X < $bmp['width']) {
                if($bmp['bits_per_pixel'] == 24)
                    $COLOR = unpack("V",substr($img,$P,3).$vide);
                elseif ($bmp['bits_per_pixel'] == 16) {
                    $COLOR = unpack("n",substr($img,$P,2));
                    $COLOR[1] = $palette[$COLOR[1]+1];
                }
                elseif($bmp['bits_per_pixel'] == 8) {
                    $COLOR = unpack("n",$vide.substr($img,$P,1));
                    $COLOR[1] = $palette[$COLOR[1]+1];
                }
                elseif($bmp['bits_per_pixel'] == 4) {
                    $COLOR = unpack("n",$vide.substr($img,floor($P),1));
                    if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ;
                    else $COLOR[1] = ($COLOR[1] & 0x0F);
                    $COLOR[1] = $palette[$COLOR[1]+1];
                }
                elseif($bmp['bits_per_pixel'] == 1) {
                    $COLOR = unpack("n",$vide.substr($img,floor($P),1));
                    if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
                    elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
                    elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
                    elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
                    elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
                    elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
                    elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
                    elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
                    $COLOR[1] = $palette[$COLOR[1]+1];
                }
                else return FALSE;
                imagesetpixel($res,$X,$Y,$COLOR[1]);
                $X++;
                $P += $bmp['bytes_per_pixel'];
            }
            $Y--;
            $P+=$bmp['decal'];
        }
        // Close the file
        fclose($f1);
        return $res;
    }
}

?>