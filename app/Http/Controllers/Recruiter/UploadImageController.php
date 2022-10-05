<?php

namespace App\Http\Controllers\Recruiter;

use App\Exceptions\InputException;
use App\Http\Requests\Admin\UploadImageRequest;
use App\Services\Common\FileService;
use Illuminate\Http\JsonResponse;

class UploadImageController extends BaseController
{
    /**
     * Upload
     *
     * @param UploadImageRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function upload(UploadImageRequest $request)
    {
        $data = FileService::getInstance()->uploadImage($request->file('image'), $request->get('type'));

        return $this->sendSuccessResponse($data);
    }
}
