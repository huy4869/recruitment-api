<?php

namespace App\Http\Controllers\User;

use App\Exceptions\InputException;
use App\Http\Requests\User\LicensesQualification\LicensesQualificationRequest;
use App\Http\Resources\User\LicensesQualification\LicensesQualificationResource;
use App\Services\User\LicensesQualificationService;
use Illuminate\Http\JsonResponse;

class LicensesQualificationController extends BaseController
{
    /**
     * @var LicensesQualificationService
     */
    private $licensesQualificationService;

    /**
     * LicensesQualificationController constructor.
     * @param LicensesQualificationService $licensesQualificationService
     */
    public function __construct(LicensesQualificationService $licensesQualificationService)
    {
        $this->licensesQualificationService = $licensesQualificationService;
    }

    /**
     * List user licenses qualification
     *
     * @return JsonResponse
     */
    public function list()
    {
        $user = $this->guard()->user();
        $data = $this->licensesQualificationService->withUser($user)->list();

        return $this->sendSuccessResponse(LicensesQualificationResource::collection($data));
    }

    /**
     * User store licenses qualification
     *
     * @param LicensesQualificationRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(LicensesQualificationRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['name', 'new_issuance_date']);
        $data = $this->licensesQualificationService->withUser($user)->store($inputs);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.010'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }

    /**
     * Detail user licenses qualification
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id)
    {
        $user = $this->guard()->user();
        $data = $this->licensesQualificationService->withUser($user)->detail($id);

        return $this->sendSuccessResponse(new LicensesQualificationResource($data));
    }

    /**
     * Update user licenses qualification
     *
     * @param $id
     * @param LicensesQualificationRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function update($id, LicensesQualificationRequest $request)
    {
        $user = $this->guard()->user();
        $inputs = $request->only(['name', 'new_issuance_date']);
        $data = $this->licensesQualificationService->withUser($user)->update($id, $inputs);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.007'));
    }

    /**
     * Delete user licenses qualification
     *
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id)
    {
        $user = $this->guard()->user();
        $data = $this->licensesQualificationService->withUser($user)->delete($id);

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.005'));
        }

        throw new InputException(trans('validation.ERR.008'));
    }
}
