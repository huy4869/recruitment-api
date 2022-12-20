<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LicensesQualificationRequest;
use App\Http\Resources\Admin\User\DetailLicensesResource;
use App\Services\Admin\LicensesQualificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicensesQualificationController extends Controller
{
    private $licensesQualificationService;

    public function __construct(LicensesQualificationService $licensesQualificationService)
    {
        $this->licensesQualificationService = $licensesQualificationService;
    }

    /**
     * create licenses qualification
     *
     * @param LicensesQualificationRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(LicensesQualificationRequest $request)
    {
        $input = $request->only([
            'name',
            'new_issuance_date'
        ]);

        $data = $this->licensesQualificationService->store($input, $request->get('user_id'));

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.010'));
        }

        throw new InputException(trans('validation.ERR.006'));
    }

    /**
     * update licenses qualification
     *
     * @param LicensesQualificationRequest $request
     * @param $id
     * @return JsonResponse
     * @throws InputException
     */
    public function update(LicensesQualificationRequest $request, $id)
    {
        $input = $request->only([
            'name',
            'new_issuance_date'
        ]);

        $data = $this->licensesQualificationService->update($input, $id, $request->get('user_id'));

        if ($data) {
            return $this->sendSuccessResponse($data, trans('validation.INF.001'));
        }

        throw new InputException(trans('validation.ERR.007'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function detail($id, Request $request)
    {
        $data = $this->licensesQualificationService->detail($id, $request->get('user_id'));

        return $this->sendSuccessResponse(new DetailLicensesResource($data));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws InputException
     */
    public function delete($id, Request $request)
    {
        $this->licensesQualificationService->delete($id, $request->get('user_id'));

        return $this->sendSuccessResponse([], trans('validation.INF.005'));
    }
}
