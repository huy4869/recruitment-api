<?php

namespace App\Services\Recruiter;

use App\Models\JobPosting;
use App\Services\MasterDataService as BaseMasterDataService;

class MasterDataService extends BaseMasterDataService
{
    /**
     * @var array
     */
    protected $availableResources = [];
}
