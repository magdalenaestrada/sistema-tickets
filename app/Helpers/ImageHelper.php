<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Laravel\Facades\Image;

class ImageHelper
{

    public static function compressImage(UploadedFile $file, int $width = 2048, int $height = 2048)
    {
        $image = Image::read($file);
        if ($image->width() > $width || $image->height() > $height) {
            $image->resize($width, $height);
        }
        $image->encode();
        return $image;
    }

    public static function compressImageAndSave(UploadedFile $file, string $path, int $width = 2048, int $height = 2048)
    {
        $image = self::compressImage($file, $width, $height);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $imagenName = uniqid("IMG-") . time() . "." . $file->getClientOriginalExtension();
        $image->save($path . "/" . $imagenName);
        return $imagenName;
    }
}