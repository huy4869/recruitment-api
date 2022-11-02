<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Requests\Recruiter\ContactRequest;
use App\Services\Recruiter\ContactService;
use Exception;
use Illuminate\Http\JsonResponse;

class ContactController extends BaseController
{
    /**
     * @param ContactRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(ContactRequest $request)
    {
        $recruiter = $this->guard()->user();
        $inputs = $request->only([
            'email',
            'store_id',
            'tel',
            'content',
        ]);
        $result = ContactService::getInstance()->withUser($recruiter)->store($inputs);

        return $this->sendSuccessResponse($result, trans('validation.INF.008'));
    }

    /**
     * @return null
     */
    public function getAdminPhone()
    {
        $tel = ContactService::getInstance()->getAdminPhone();

        return $this->sendSuccessResponse($tel);
    }
}
