<?php

namespace App\Services;

use App\Models\MFeedbackType;
use App\Models\MJobFeature;
use App\Models\MJobFeatureCategory;
use App\Models\MProvince;
use App\Models\MProvinceCity;
use App\Models\MSalaryType;
use App\Models\MStation;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MasterDataService extends Service
{
    public const DRIVER_CONFIG = 'config';
    public const DRIVER_ELOQUENT = 'eloquent';
    public const DRIVER_CUSTOM = 'custom';
    public const DEFAULT_PER_PAGE = 20;

    /**
     * @var null
     */
    protected $user = null;

    /**
     * @var array
     */
    protected array $resources = [];

    /**
     * @var array
     */
    protected $availableResources = [
        'm_feedback_types' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataFeedbackTypes',
        ],

        'm_genders' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_interview_approaches' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_interviews_status' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_job_experiences' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_job_features' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataJobFeatures',
        ],

        'm_job_statuses' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_job_types' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_learning_status' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_position_offices' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_province_districts' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_provinces' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_roles' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_work_positions' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_work_types' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataName',
        ],

        'm_stations' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataStations',
        ],

        'm_salary_types' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterDataSalaryTypes',
        ],

        'age' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterAge',
        ],

        'm_provinces_cities' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getMasterProvinceCity',
        ],

        'order_by' => [
            'driver' => self::DRIVER_CUSTOM,
            'target' => 'getListOrderBy',
        ],
    ];

    /**
     * @var null
     */
    protected $data = null;

    /**
     * With resources
     *
     * @param array $resources
     * @return $this
     */
    public function withResources(array $resources)
    {
        $rs = [];
        foreach ($resources as $resourceName => $resourceParams) {
            if ($this->isAvailableResource($resourceName)) {
                $rs[] = [
                    'name' => $resourceName,
                    'params' => $this->decodeParams($resourceParams),
                ];
            }
        }

        $this->resources = $rs;
        return $this;
    }

    /**
     * Check if is available resource
     *
     * @param array $resourceName
     * @return boolean
     */
    protected function isAvailableResource($resourceName)
    {
        if (!isset($this->availableResources[$resourceName])) {
            return false;
        }

        return true;
    }

    /**
     * Decode input params
     *
     * @param string $params
     * @return array
     */
    protected function decodeParams($params)
    {
        try {
            if (empty($params)) {
                return [];
            }

            return json_decode($params, true);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Handle load resource from config driver
     *
     * @param array $resource
     * @param array $resourceConfig
     * @return array
     */
    protected function handleLoadFromConfig(array $resource, array $resourceConfig)
    {
        return config($resourceConfig['target']);
    }

    /**
     * Handle load resource from eloquent driver
     *
     * @param array $resource
     * @param array $resourceConfig
     * @return array|Collection
     */
    protected function handleLoadFromEloquent(array $resource, array $resourceConfig)
    {
        $query = $resourceConfig['target']::query();
        if (!empty($resourceConfig['select'])) {
            $query->select($resourceConfig['select']);
        }
        if (!empty($resourceConfig['order'])) {
            $query->orderBy($resourceConfig['order'][0], $resourceConfig['order'][1]);
        }

        return $query->get();
    }

    /**
     * Handle load resource from custom driver
     *
     * @param array $resource
     * @param array $resourceConfig
     * @return array|Collection
     */
    protected function handleLoadFromCustom(array $resource, array $resourceConfig)
    {
        return $this->{$resourceConfig['target']}($resource, $resourceConfig);
    }

    /**
     * Handle load resource data
     *
     * @param array $resource
     * @return array|Collection
     */
    protected function handleLoad(array $resource)
    {
        $resourceConfig = $this->availableResources[$resource['name']];
        $data = $this->{'handleLoadFrom' . Str::studly($resourceConfig['driver'])}($resource, $resourceConfig);
        if (empty($resourceConfig['convert_array'])) {
            return $data;
        }

        return $data instanceof Collection ? $data->values() : collect($data)->values();
    }

    /**
     * Load data
     *
     * @return array
     */
    public function load()
    {
        $data = [];
        foreach ($this->resources as $resource) {
            if ($this->canGetResource($resource)) {
                $data[$resource['name']] = $this->handleLoad($resource);
            } else {
                $data[$resource['name']] = null;
            }
        }

        $this->data = $data;
        return $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function get()
    {
        if (!$this->data) {
            $this->load();
        }

        return $this->data;
    }

    /**
     * Check user login can get resource
     *
     * @param array $resource
     * @return boolean
     */
    protected function canGetResource(array $resource)
    {
        $resourceConfig = $this->availableResources[$resource['name']];

        if (empty($resourceConfig['auth'])) {
            return true;
        }

        if (!$this->user) {
            return false;
        }

        foreach ($resourceConfig['auth'] as $authName) {
            if ($this->user->{'is' . Str::studly($authName)}()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get paginate params from resource params
     *
     * @param array $params
     * @return array
     */
    protected function getPaginateParams(array $params)
    {
        $page = empty($params['page']) ? 1 : intval($params['page']);
        $perPage = empty($params['per_page']) ? self::DEFAULT_PER_PAGE : intval($params['per_page']);
        $search = empty($params['search']) ? '' : trim($params['search']);

        if ($page <= 0) {
            $page = 1;
        }

        if ($perPage <= 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return [
            'per_page' => $perPage,
            'current_page' => $page,
            'search' => $search,
        ];
    }

    /**
     * @param $params
     * @return array
     */
    protected function getSelectedItems($params)
    {
        if (empty($params['selected']) || !is_array($params['selected'])) {
            return [];
        }

        $selectedIds = [];
        foreach ($params['selected'] as $selectedId) {
            $selectedIds[] = intval($selectedId);
        }

        return $selectedIds;
    }

    /**
     * Get resource with paginate
     *
     * @param $resource
     * @param $query
     * @return array
     */
    protected function paginate($resource, $query, $searchField = null)
    {
        $params = $resource['params'];
        $pageParams = $this->getPaginateParams($params);
        if ($searchField && $pageParams['search']) {
            $query->where($searchField, 'like', '%' . $pageParams['search'] . '%');
        }

        $selectedIds = $this->getSelectedItems($params);
        $selectedCount = count($selectedIds);
        if ($selectedCount) {
            return $this->paginateWithSelected($query, $pageParams, $selectedIds);
        }

        return $this->paginateNoSelected($query, $pageParams);
    }

    /**
     * @param $query
     * @param $pageParams
     * @param $selectedIds
     * @return array
     */
    protected function paginateWithSelected($query, $pageParams, $selectedIds)
    {
        $fromTable = $query->getQuery()->from;
        $limit = $pageParams['per_page'];
        $selectedItems = collect([]);
        $data = collect([]);
        if ($pageParams['current_page'] == 1) {
            $selectedItemQuery = clone $query;
            $selectedItems = $selectedItemQuery->whereIn($fromTable . '.id', $selectedIds)->get();
            $limit = $limit - $selectedItems->count();
        }
        $query->whereNotIn($fromTable . '.id', $selectedIds);

        $total = $query->count();
        $offset = $pageParams['per_page'] * ($pageParams['current_page'] - 1);
        $totalPage = ceil($total / $pageParams['per_page']);
        if ($limit > 0) {
            $data = $query->offset($offset)->limit($limit)->get();
        }

        return [
            'data' => $selectedItems->merge($data)->toArray(),
            'per_page' => $pageParams['per_page'],
            'total_page' => (!$totalPage && $total > 0) ? 1 : $totalPage,
            'current_page' => $pageParams['current_page'],
            'total' => $total + $selectedItems->count(),
        ];
    }

    /**
     * @param $query
     * @param $pageParams
     * @return array
     */
    protected function paginateNoSelected($query, $pageParams)
    {
        $total = $query->count();
        $offset = $pageParams['per_page'] * ($pageParams['current_page'] - 1);
        $data = $query->offset($offset)->limit($pageParams['per_page'])->get();

        return [
            'data' => $data->toArray(),
            'per_page' => $pageParams['per_page'],
            'total_page' => ceil($total / $pageParams['per_page']),
            'current_page' => $pageParams['current_page'],
            'total' => $total,
        ];
    }

    /**
     * @return array
     */
    protected function getMasterDataName($resource)
    {
        if (empty($resource['params']['model'])) {
            return [];
        }

        $modelName = 'App\\Models\\' . $resource['params']['model'];

        $feedbackTypes =  $modelName::all();
        $result = [];

        foreach ($feedbackTypes as $feedbackType) {
            $result[] = [
                'id' =>  $feedbackType->id,
                'name' => $feedbackType->name,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getMasterDataStations()
    {
        $stations =  MStation::all();
        $result = [];

        foreach ($stations as $station) {
            $result[] = [
                'id' =>  $station->id,
                'province_name' => $station->province_name,
                'railway_name' => $station->railway_name,
                'station_name' => $station->station_name,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getMasterDataSalaryTypes()
    {
        $salaryTypes =  MSalaryType::all();
        $result = [];

        foreach ($salaryTypes as $salaryType) {
            $result[] = [
                'id' =>  $salaryType->id,
                'name' => $salaryType->name,
                'term' => $salaryType->term,
                'currency' => $salaryType->currency,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getMasterDataFeedbackTypes()
    {
        $feedbackTypes = MFeedbackType::all();
        $result = [];

        foreach ($feedbackTypes as $feedbackType) {
            $result[] = [
                'id' => $feedbackType->id,
                'name' => $feedbackType->name,
                'has_extend' => $feedbackType->has_extend,
                'placeholder_extend' => $feedbackType->placeholder_extend,
            ];
        }

        return $result;
    }

    /**
     * @return Repository|Application|mixed
     */
    protected function getMasterAge()
    {
        $dataAge = config('date.age');
        $result = [];

        foreach ($dataAge as $key => $age) {
            $result[] = [
                'id' => $key,
                'name' => $age
            ];
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getMasterDataJobFeatures()
    {
        $jobFeatureCategories = MJobFeatureCategory::query()->get();
        $jobFeatures = MJobFeature::query()->with(['category'])->get();
        $result = [];
        $i = 0;

        foreach ($jobFeatureCategories as $category) {
            $result[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'feature' => [],
            ];

            foreach ($jobFeatures as $feature) {
                if ($feature->category_id == $category->id) {
                    $result[$i]['feature'][] = [
                        'id' => $feature->id,
                        'name' => $feature->name,
                    ];
                }
            }

            $i++;
        }

        return $result;
    }

    protected function getMasterProvinceCity()
    {
        $provinces = MProvince::with('provinceCities')->get();
        $result = [];
        $i = 0;

        foreach ($provinces as $province) {
            $result[] = [
               'province_id' => $province->id,
               'name' => $province->name,
                'feature' => [],
            ];

            foreach ($province->provinceCities as $provinceCity) {
                if ($provinceCity->province_id == $province->id) {
                    $result[$i]['feature'][] = [
                        'id' => $provinceCity->id,
                        'name' => $provinceCity->name,
                    ];
                }
            }

            $i++;
        }

        return $result;
    }

    /**
     * @return Repository|Application|mixed
     */
    protected function getListOrderBy()
    {
        return config('order_by.job_posting');
    }
}
