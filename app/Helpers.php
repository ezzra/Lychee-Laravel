<?php

namespace App;

use Imagick;
use ImagickPixel;

class Helpers
{

    static public function fastImageCopyResampled(&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 4) {

        /**
         * Plug-and-Play fastImageCopyResampled function replaces much slower imagecopyresampled.
         * Just include this function and change all "imagecopyresampled" references to "fastImageCopyResampled".
         * Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
         * Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
         *
         * Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
         * Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
         * 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
         * 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
         * 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
         * 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
         * 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.
         */

        if (empty($src_image) || empty($dst_image) || $quality <= 0) { return false; }

        if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {

            $temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
            imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
            imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
            imagedestroy($temp);

        } else imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        return true;

    }


    /**
     * @return string Generated ID.
     */
    static public function generateID() {

        // Generate id based on the current microtime
        $id = str_replace('.', '', microtime(true));
        $id = substr($id,1,10);

        // Ensure that the id has a length of 14 chars
        while(strlen($id)<10) $id .= 0;

        $id[0] = strval(intval($id[0]) % 4);
        // Return id as a string. Don't convert the id to an integer
        // as 14 digits are too big for 32bit PHP versions.
        return $id;

    }



    /**
     * Returns the extension of the filename (path or URI) or an empty string.
     * @return string Extension of the filename starting with a dot.
     */
    static public function getExtension($filename, $isURI = false) {

        // If $filename is an URI, get only the path component
        if ($isURI===true) $filename = parse_url($filename, PHP_URL_PATH);

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // Special cases
        // https://github.com/electerious/Lychee/issues/482
        list($extension) = explode(':', $extension, 2);

        if (empty($extension)===false) $extension = '.' . $extension;

        return $extension;

    }



    /**
     * Returns the normalized coordinate from EXIF array.
     * @return string Normalized coordinate as float number (degrees).
     */
    static public function getGPSCoordinate($coordinate, $ref) {

        $degrees = count($coordinate) > 0 ? Helpers::formattedToFloatGPS($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? Helpers::formattedToFloatGPS($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? Helpers::formattedToFloatGPS($coordinate[2]) : 0;

        $flip = ($ref == 'W' || $ref == 'S') ? -1 : 1;

        return $flip * ($degrees + (float)$minutes / 60 + (float)$seconds / 3600);

    }

    static public function formattedToFloatGPS($coordinate) {

        $parts = explode('/', $coordinate, 2);

        if (count($parts) <= 0) return 0;
        if (count($parts) == 1) return $parts[0];

        return (float)$parts[0] / $parts[1];

    }

    static public function getGPSAltitude($altitude, $ref) {

        $flip = ($ref == '1') ? -1 : 1;
        return $flip * Helpers::formattedToFloatGPS($altitude);
    }

    static public function hasPermissions($path) {
        // Check if the given path is readable and writable
        // Both functions are also verifying that the path exists
        if (is_readable($path)===true&&is_writeable($path)===true) return true;
        return false;
    }

    static public function gcd($a,$b) {
	    return ($a % $b) ? Helpers::gcd($b,$a % $b) : $b;
    }


}