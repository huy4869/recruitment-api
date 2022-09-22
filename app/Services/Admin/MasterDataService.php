<?php

namespace App\Services\Admin;

use App\Services\MasterDataService as BaseMasterDataService;

class MasterDataService extends BaseMasterDataService
{
    /**
     * @var array
     */
    protected $availableResources = [];
}
