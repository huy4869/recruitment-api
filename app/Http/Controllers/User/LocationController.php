<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\User\LocationMostApplyRequest;
use App\Models\MJobType;
use App\Services\User\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends BaseController
{
    private LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     *
     * @return JsonResponse
     */
    public function getAccordingToMostApply(LocationMostApplyRequest $request): JsonResponse
    {
        $jobTypeIds = $request->get('types');
        $limit = $request->get('limit');

        if (!$jobTypeIds || !count($jobTypeIds)) {
            $jobTypeIds = [
                MJobType::HAIR,
                MJobType::NAIL,
                MJobType::CHIRO_CAIRO_OXY_HOTBATH,
                MJobType::CLINIC,
                MJobType::OTHER
            ];
        }

        $data = $this->locationService->getAccordingToMostApply($jobTypeIds, $limit);

        return $this->sendSuccessResponse($data);
    }
}
