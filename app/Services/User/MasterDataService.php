<?php

namespace App\Services\User;

use App\Services\MasterDataService as BaseMasterDataService;

class MasterDataService extends BaseMasterDataService
{
    /**
     * @var array
     */
    protected $availableResources = [];
}
