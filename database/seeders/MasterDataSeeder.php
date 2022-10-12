<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('m_roles')->truncate();
        DB::table('m_genders')->truncate();
        DB::table('m_job_types')->truncate();
        DB::table('m_job_experiences')->truncate();
        DB::table('m_work_types')->truncate();
        DB::table('m_job_feature_categories')->truncate();
        DB::table('m_province_districts')->truncate();
        DB::table('m_learning_status')->truncate();
        DB::table('m_interviews_status')->truncate();
        DB::table('m_interview_approaches')->truncate();
        DB::table('m_feedback_types')->truncate();
        DB::table('m_salary_types')->truncate();
        DB::table('m_job_statuses')->truncate();
        DB::table('m_provinces')->truncate();
        DB::table('m_job_features')->truncate();
        DB::table('m_stations')->truncate();

        $time = Carbon::now();

        $dataRoles = [
            ['name' => 'USER', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'REC', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'ADMIN', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_roles')->insert($dataRoles);

        $dataGenders = [
            ['name' => '男性', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '女性', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'その他', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_genders')->insert($dataGenders);

        $dataJobType = [
            ['name' => 'ヘア', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'ネイル・マツゲ', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '整体・カイロ・酸素・温浴', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'フェイシャル・ボディ・脱毛', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '美容クリニック', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'その他', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_job_types')->insert($dataJobType);

        $dataJobExperiences = [
            ['name' => 'ブランク', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '未経験者可', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '管理美容師免許歓迎', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '幹部・店長候補歓迎', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '美容師歓迎', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '免許・資格不問', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '通信生（見習い）相談可', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_job_experiences')->insert($dataJobExperiences);

        $dataWorkTypes = [
            ['name' => '正社員', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '派遣社員', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '契約社員', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'アルバイト', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'その他', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_work_types')->insert($dataWorkTypes);

        $dataJobFeatureCategories = [
            ['name' => '募集の特徴', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '企業の特徴', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '店舗の特徴', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_job_feature_categories')->insert($dataJobFeatureCategories);

        $dataProvinceDistricts = [
            ['name' => '北海道', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '東北', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '関東', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '中部', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '近畿', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '中国', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '四国', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '九州・沖縄', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_province_districts')->insert($dataProvinceDistricts);

        $dataLearningStatus = [
            ['name' => '卒業', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '卒業見込み·', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '休退', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_learning_status')->insert($dataLearningStatus);

        $dataInterviewStatus = [
            ['name' => '応募中', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '面接待ち', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '結果待ち', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '採用', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '不採用', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'キャンセル', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_interviews_status')->insert($dataInterviewStatus);

        $dataInterviewApproaches = [
            ['name' => 'オンライン面接', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '対面', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '電話面接', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_interview_approaches')->insert($dataInterviewApproaches);

        $dataFeedbackTypes = [
            ['name' => '年収／月収に関する相談', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '福利厚生に関するお問合せ', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '教育制度を知りたい', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '残業代が出るか知りたいなど', 'created_at' => $time, 'updated_at' => $time],
            ['name' => 'その他', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_feedback_types')->insert($dataFeedbackTypes);

        $dataSalaryTypes = [
            ['name' => '万円/月収', 'term' => 8760, 'currency' => '￥', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '万円/年収', 'term' => 720, 'currency' => '￥', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '円/時給', 'term' => 1, 'currency' => '￥', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '円/日給', 'term' => 24, 'currency' => '￥', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_salary_types')->insert($dataSalaryTypes);


        $dataJobStatus = [
            ['name' => '下書き', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '公開', 'created_at' => $time, 'updated_at' => $time],
            ['name' => '終了', 'created_at' => $time, 'updated_at' => $time],
        ];
        DB::table('m_job_statuses')->insert($dataJobStatus);

        $path = base_path().'/database/seeders/location.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
