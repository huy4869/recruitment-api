<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ContactRequest;
use App\Http\Resources\User\ContactResource;
use App\Services\User\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Create contact
     *
     * @param ContactRequest $request
     * @return JsonResponse
     */
    public function store(ContactRequest $request)
    {
        $user = $this->guard()->user() ?? null;
        $inputs = $request->only(['email', 'name', 'tel', 'content']);

        $data = ContactService::getInstance()->withUser($user)->store($inputs);

        return $this->sendSuccessResponse(new ContactResource($data), trans('validation.INF.008'));
    }

    /**
     * Get admin telephone
     *
     * @return JsonResponse
     */
    public function getAdminPhone()
    {
        $tel = ContactService::getInstance()->getAdminPhone();

        return $this->sendSuccessResponse($tel);
    }
}
