<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InputException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LicensesQualificationRequest;
use App\Services\Admin\LicensesQualificationService;

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
     * @return \Illuminate\Http\JsonResponse
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
            return $this->sendSuccessResponse($data, trans('response.INF.006'));
        }

        throw new InputException(trans('response.ERR.006'));
    }

    /**
     * update licenses qualification
     *
     * @param LicensesQualificationRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
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

        throw new InputException(trans('response.ERR.007'));
    }
}
