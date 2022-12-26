<?php

namespace App\Console\Commands;

use App\Console\Kernel;
use App\Models\MJobExperience;
use App\Models\MJobFeature;
use App\Models\MJobType;
use App\Models\MProvince;
use App\Models\MWorkType;
use App\Models\UserJobDesiredMatch;
use App\Services\User\DesiredConditionService;
use App\Services\User\Job\JobService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MakeUserJobDesiredMatch extends Command
{
    const QUANTITY_CHUNK = 1000;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = Kernel::MAKE_USER_JOB_DESIRED_MATCH;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make user job desired match';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->line('_________START__________');
        $matches = [
            'province_ids',
            'work_type_ids',
            'age',
            'salary',
            'job_experience_ids',
            'job_feature_ids',
            'working_days',
            'work_time',
        ];

        $desires = DesiredConditionService::getInstance()->getList();
        $jobPostings = JobService::getInstance()->getList();

        if (!$desires->count() || !$jobPostings->count()) {
            $this->info('Refresh table data');
            $this->info('Nothing to match');
            DB::table('user_job_desired_matches')->delete();
            $this->line('_________END__________');

            return;
        }

        $dataCreate = [];
        $now = now();

        foreach ($jobPostings as $jobPosting) {
            foreach ($desires as $desire) {
                $matchResult = [
                    'detail' => [],
                    'point' => 0
                ];

                if (!$desire->job_type_ids || !$jobPosting->job_type_ids) {
                    continue;
                } else {
                    $arr_1 = is_array($desire->job_type_ids) ? $desire->job_type_ids : json_decode($desire->job_type_ids);
                    $arr_2 = is_array($jobPosting->job_type_ids) ? $jobPosting->job_type_ids : json_decode($jobPosting->job_type_ids);
                    if (!count(array_intersect($arr_1, $arr_2))) {
                        continue;
                    }
                }

                foreach ($matches as $match) {
                    switch ($match) {
                        case 'province_ids':
                            $matchResult = $this->compareProvince($matchResult, $desire->province_ids, $jobPosting->province_id);
                            break;
                        case 'work_type_ids':
                            $matchResult = $this->compareWorkType($matchResult, $desire->work_type_ids, $jobPosting->work_type_ids);
                            break;
                        case 'age':
                            $matchResult = $this->compareAge($matchResult, $desire->age, $jobPosting->age_min, $jobPosting->age_max);
                            break;
                        case 'job_experience_ids':
                            $matchResult = $this->compareJobExperience($matchResult, $desire->job_experience_ids, $jobPosting->experience_ids);
                            break;
                        case 'job_feature_ids':
                            $matchResult = $this->compareJobFeature($matchResult, $desire->job_feature_ids, $jobPosting->feature_ids);
                            break;
                        case 'salary':
                            $matchResult = $this->compareSalary(
                                $matchResult,
                                [
                                    'type' => $desire->salary_type_id,
                                    'min' => $desire->salary_min,
                                    'max' => $desire->salary_max,
                                ],
                                [
                                    'type' => $jobPosting->salary_type_id,
                                    'min' => $jobPosting->salary_min,
                                    'max' => $jobPosting->salary_max,
                                ]
                            );
                            break;
                        case 'working_days':
                            $matchResult = $this->compareWorkingDay($matchResult, $desire->working_days, $jobPosting->working_days);
                            break;
                        case 'work_time':
                            $matchResult = $this->compareWorkTime(
                                $matchResult,
                                [
                                    'start' => $desire->start_working_time,
                                    'end' => $desire->end_working_time
                                ],
                                [
                                    'start' => $jobPosting->start_work_time,
                                    'end' => $jobPosting->end_work_time
                                ]
                            );
                            break;
                    }//end switch
                }//end foreach

                $dataCreate[] = [
                    'user_id' => $desire->user_id,
                    'job_id' => $jobPosting->id,
                    'match_detail' => json_encode($matchResult['detail']),
                    'suitability_point' => $matchResult['point'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }//end foreach
        }//end foreach

        try {
            DB::beginTransaction();

            $this->info('Refresh table data');
            DB::table('user_job_desired_matches')->delete();
            collect($dataCreate)->chunk(self::QUANTITY_CHUNK)->each(function ($data) {
                UserJobDesiredMatch::insert($data->toArray());
                $this->info(sprintf('Inserted %s record !', count($data->toArray())));
            });

            DB::commit();
            $this->info('The command was successful!');
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage(), [$exception]);
            $this->error('Something went wrong!');
            throw new Exception($exception->getMessage());
        }

        $this->line('_________END__________');
    }

    public function compareProvince($matchResult, $province_d, $province_j) {
        if ($province_d && $province_j && in_array($province_j, $province_d)) {
            $matchResult['detail'][] = [
                'model_class' => MProvince::class,
                'single_attr' => '',
                'match_value' => $province_d
            ];
            $matchResult['point'] += config('criteria_ratio.match_job.province');
        }

        return $matchResult;
    }

    public function compareWorkType($matchResult, $workTypes_d, $workTypes_j) {
        return $this->compareJson(
            $matchResult,
            $workTypes_d,
            $workTypes_j,
            MWorkType::class,
            config('criteria_ratio.match_job.work_type')
        );
    }

    public function compareJobExperience($matchResult, $jobExperience_d, $jobExperience_j) {
        return $this->compareJson(
            $matchResult,
            $jobExperience_d,
            $jobExperience_j,
            MJobExperience::class,
            config('criteria_ratio.match_job.job_experience')
        );
    }

    public function compareJobFeature($matchResult, $jobFeature_d, $jobFeature_j) {
        return $this->compareJson(
            $matchResult,
            $jobFeature_d,
            $jobFeature_j,
            MJobFeature::class,
            config('criteria_ratio.match_job.job_feature')
        );
    }

    public function compareJson($matchResult, $list_1, $list_2, $class, $ratio) {
        if ($list_1 && $list_2) {
            $arr_1 = is_array($list_1) ? $list_1 : json_decode($list_1);
            $arr_2 = is_array($list_2) ? $list_2 : json_decode($list_2);

            if (count($arr_1) && count($arr_2)) {
                $intersect = array_intersect($arr_1, $arr_2);
                if ($intersect) {
                    $matchResult['detail'][] = [
                        'model_class' => $class,
                        'single_attr' => '',
                        'match_value' => json_encode($intersect),
                    ];
                    $matchResult['point'] += $ratio/count($arr_1) * count($intersect);
                }
            }
        }

        return $matchResult;
    }

    public function compareAge($matchResult, $age_d, $ageMin_j, $ageMax_j) {
        if (!$age_d || (is_null($ageMin_j) && is_null($ageMax_j)) || $age_d < $ageMin_j || $age_d > $ageMax_j) {
            return $matchResult;
        }

        $matchResult['detail'][] = [
            'model_class' => "",
            'single_attr' => 'age',
            'match_value' => $age_d,
        ];
        $matchResult['point'] += config('criteria_ratio.match_job.age');

        return $matchResult;
    }

    public function compareSalary($matchResult, $salaryInfo_d, $salaryInfo_j) {
        $hasPoint = false;

        if ($salaryInfo_d['type'] == $salaryInfo_j['type'] && ($salaryInfo_d['min'] || $salaryInfo_d['max'])) {
            if ($salaryInfo_d['min'] && $salaryInfo_d['max']) {
                if (($salaryInfo_d['min'] > $salaryInfo_j['min']) && ($salaryInfo_d['max'] < $salaryInfo_j['max'])) {
                    $matchResult['point'] += config('criteria_ratio.match_job.salary.full');
                } else {
                    $matchResult['point'] += config('criteria_ratio.match_job.salary.half');
                }
                $hasPoint = true;
            } else if (
                ($salaryInfo_d['max'] > $salaryInfo_j['min'] && $salaryInfo_d['max'] < $salaryInfo_j['max']) ||
                ($salaryInfo_d['min'] > $salaryInfo_j['min'] && $salaryInfo_d['min'] < $salaryInfo_j['max'])
            ) {
                $matchResult['point'] += config('criteria_ratio.match_job.salary.half');
                $hasPoint = true;
            }
        }

        if ($hasPoint) {
            $matchResult['detail'][] = [
                'model_class' => "",
                'single_attr' => 'salary',
                'match_value' => json_encode([
                    $salaryInfo_d['min'] ? max($salaryInfo_d['min'], $salaryInfo_j['min']) : $salaryInfo_j['min'],
                    $salaryInfo_d['max'] ? min($salaryInfo_d['max'], $salaryInfo_j['max']) : $salaryInfo_j['max']
                ]),
            ];
        }

        return $matchResult;
    }

    public function compareWorkingDay($matchResult, $workingDays_d, $workingDays_j) {
        return $this->compareJson(
            $matchResult,
            $workingDays_d,
            $workingDays_j,
            'WorkingDays',
            config('criteria_ratio.match_job.working_days')
        );
    }

    public function compareWorkTime($matchResult, $workTimes_d, $workTimes_j)
    {
        if ($workTimes_d['start'] >= $workTimes_j['start']
            && $workTimes_d['end'] <=$workTimes_j['end']) {
            $matchResult['point'] += config('criteria_ratio.match_job.work_time');
        }

        return $matchResult;
    }
}
