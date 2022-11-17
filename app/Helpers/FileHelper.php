<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Full Path Not Domain
     *
     * @param $path
     * @return false|string
     */
    public static function fullPathNotDomain($path)
    {
        if (!$path) {
            return '';
        }

        $urlParsed = parse_url($path);
        return trim($urlParsed['path'] ?? '', '/');
    }

    /**
     * Get Full Url
     *
     * @param $path
     * @return mixed
     */
    public static function getFullUrl($path)
    {
        if (!$path) {
            return null;
        }

        $newPath = strstr($path, config('upload.path_origin_image'));
        return self::storageImages()->url($newPath);
    }

    /**
     * @param $path
     * @return mixed
     */
    public static function getFullUrlThumb($path)
    {
        if (!$path) {
            return null;
        }

        $getPathThumb = str_replace(config('upload.path_origin_image'), config('upload.path_thumbnail'), $path);
        $newPathThumb = strstr($getPathThumb, config('upload.path_thumbnail'));
        return self::storageImages()->url($newPathThumb);
    }

    /**
     * Storage Images
     *
     * @return Filesystem
     */
    public static function storageImages()
    {
        $diskName = config('upload.disk');
        return Storage::disk($diskName);
    }

    /**
     * Get File Webp Name
     *
     * @param null $fileExtension
     * @return string
     */
    public static function constructFileName($fileExtension = null)
    {
        $fileName = md5(StringHelper::uniqueCode(15) . Carbon::now() . StringHelper::uniqueCode(25));

        if (!$fileExtension) {
            $fileExtension = config('upload.webp_ext');
        }

        return Str::lower("{$fileName}.{$fileExtension}");
    }

    /**
     * Path Url
     *
     * @param $fileName
     * @param $namePath
     * @return false|string
     */
    public static function pathUrl($fileName, $namePath)
    {
        return $namePath . '/' . $fileName;
    }
}
