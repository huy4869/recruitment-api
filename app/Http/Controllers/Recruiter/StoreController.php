<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Services\Recruiter\StoreService;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    protected $storeService;
    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * List store
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = $this->storeService->withUser($this->guard()->user())->list();

        return $this->sendSuccessResponse($data);
    }
}
