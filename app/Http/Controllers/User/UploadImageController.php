<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\UploadImageRequest;
use App\Services\Common\FileService;
use Illuminate\Http\JsonResponse;

class UploadImageController extends BaseController
{
    /**
     * UploadController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * Upload image
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
