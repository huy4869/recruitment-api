<?php

namespace App\Services\Common;

use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Models\Image as Images;
use App\Services\Service;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class FileService extends Service
{
    public const COUNT_IMAGE = 1;
    public const FILE_EXTENSION = 'svg';

    /**
     * @var string
     */
    protected $diskName;

    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * @return Filesystem
     */
    private function storage()
    {
        if (!$this->storage) {
            $this->storage = Storage::disk($this->diskName);
        }
        return $this->storage;
    }

    /**
     * Upload image
     *
     * @param UploadedFile $file
     * @param $type
     * @return array
     * @throws InputException|ValidationException
     */
    public function uploadImage(UploadedFile $file, $type)
    {
        $fileExtension = $file->getClientOriginalExtension();

        if (!in_array($fileExtension, config('upload.image_ext'))) {
            throw ValidationException::withMessages([
                'image' => trans('validation.custom.image.mimes')
            ]);
        }//end if

        $this->diskName = config('upload.disk');

        if ($fileExtension == self::FILE_EXTENSION) {
            $fileName = FileHelper::constructFileName(self::FILE_EXTENSION);
            [$fullPath, $thumbPath] = $this->uploadFileSvg($file, $fileName);
        } else {
            $fileName = FileHelper::constructFileName();
            [$fullPath, $thumbPath] = $this->resizeImage($file, $type, $fileName);
        }

        $image = Images::query()->create([
            'imageable_id' => $this->user->id ?? null,
            'imageable_type' => $this->user ? get_class($this->user) : null,
            'url' => $fullPath,
            'thumb' => $thumbPath,
            'type' => $type,
        ]);

        $imageUrl = $this->storage()->url($image->url);
        $thumbnailUrl = $this->storage()->url($image->thumb);

        return ['url' => $imageUrl, 'thumb' => $thumbnailUrl, 'type' => $image->type];
    }

    /**
     * Update imageable
     *
     * @param $object
     * @param array $images
     * @param array $types
     * @return bool|void
     */
    public function updateImageable($object, array $images, array $types = [Images::AVATAR_BANNER, Images::AVATAR_DETAIL])
    {
        if (!count($images)) {
            return;
        }

        $dataImages = [];
        foreach ($images as $image) {
            $dataImages[] = Str::after($image, 'storage/upload/');
        }

        $existImages = Images::query()
            ->where(function ($q) use ($dataImages) {
                $q->whereIn('url', $dataImages)
                ->orWhereIn('thumb', $dataImages);
            })
            ->where(function ($query) use ($object) {
                $query->where('imageable_id', $object->id)
                ->orWhere('imageable_id', null);
            });

        if (!$existImages) {
            return;
        }

        $existImagesIds = $existImages->get()->pluck('id')->toArray();
        $objectDeleteImage = $object->images()->whereIn('type', $types)->whereNotIn('id', $existImagesIds);
        $linkImage = 'public/upload/';
        $storageDeletes = [];

        foreach ($objectDeleteImage->get() as $img) {
            $countImage = Images::query()->where('url', '=', $img->url)->get()->count();

            if ($countImage == self::COUNT_IMAGE) {
                $url = $linkImage . $img->url;
                $thumb = $linkImage . $img->thumb;
                array_push($storageDeletes, $url, $thumb);
            }
        }
        Storage::delete($storageDeletes);

        $objectDeleteImage->delete();
        $existImages->update([
            'imageable_id' => $object->id,
            'imageable_type' => get_class($object),
        ]);

        return true;
    }

    /**
     * Fake image
     *
     * @param $type
     * @return array
     * @throws InputException
     */
    public function fakeImage($type)
    {
        $this->diskName = config('upload.disk');
        $typeImage = config('upload.image_types' . '.' . $type);
        $imageUrl = "https://via.placeholder.com/{$typeImage['full_size'][0]}x{$typeImage['full_size'][0]}.png";

        $fileName = FileHelper::constructFileName();

        [$fullPath, $thumbPath] = $this->resizeImage($imageUrl, $type, $fileName);

        $image = Images::query()->create([
            'imageable_id' => $this->user->id ?? null,
            'imageable_type' => $this->user ? get_class($this->user) : null,
            'url' => $fullPath,
            'thumb' => $thumbPath,
            'type' => $type,
        ]);

        $imageUrl = $this->storage()->url($image->url);
        $thumbnailUrl = $this->storage()->url($image->thumb);

        return ['url' => $imageUrl, 'thumb' => $thumbnailUrl];
    }

    /**
     * Upload file svg
     *
     * @param $image
     * @param $fileName
     * @return array
     */
    protected function uploadFileSvg($image, $fileName)
    {
        $fullPath = FileHelper::pathUrl($fileName, config('upload.path_origin_image'));
        $thumbPath = FileHelper::pathUrl($fileName, config('upload.path_thumbnail'));

        $this->storage()->put($fullPath, $image->getContent());
        $this->storage()->put($thumbPath, $image->getContent());

        return [$fullPath, $thumbPath];
    }

    /**
     * Resize
     *
     * @param $image
     * @param $type
     * @param $fileName
     * @return false[]|string[]
     * @throws InputException
     */
    protected function resizeImage($image, $type, $fileName)
    {
        $img = Image::make($image)->orientate();
        $typeImage = config('upload.image_types' . '.' . $type);

        if (!$typeImage) {
            throw new InputException(trans('validation.upload_error_type'));
        }

        $fullPath = FileHelper::pathUrl($fileName, config('upload.path_origin_image'));
        $thumbPath = FileHelper::pathUrl($fileName, config('upload.path_thumbnail'));

        if ($typeImage['crop']) {
            $deltaOld = $typeImage['full_size'][0] / $typeImage['full_size'][1];
            $deltaNew = $img->width() / $img->height();

            if ($deltaOld >= $deltaNew) {
                $width = $img->width();
                $height = $width / $deltaOld;
            } else {
                $height = $img->height();
                $width = $height * $deltaOld;
            }

            $img = $img->crop(intval($width), intval($height));
        }

        $imageOrigin = $img->widen($typeImage['full_size'][0], function ($constraint) {
            $constraint->upsize();
        });

        $imageThumb = clone $img;
        $imageThumb = $imageThumb->widen($typeImage['thumb_size'][0], function ($constraint) {
            $constraint->upsize();
        });

        $encodeType = config('upload.webp_ext');
        $webpQuality = config('upload.webp_quality');

        $imageOrigin = $imageOrigin->encode($encodeType, intval($webpQuality))->stream();
        $imageThumb = $imageThumb->encode($encodeType, intval($webpQuality))->stream();

        $this->storage()->put($fullPath, $imageOrigin->__toString());
        $this->storage()->put($thumbPath, $imageThumb->__toString());

        return [$fullPath, $thumbPath];
    }
}
