<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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

        $dataRoles = [['name' => 'USER'],['name' => 'REC'],['name' => 'ADMIN']];
        DB::table('m_roles')->insert($dataRoles);

        $dataGenders = [['name' => '男性'],['name' => '女性'],['name' => 'その他']];
        DB::table('m_genders')->insert($dataGenders);

        $dataJobType = [
            ['name' => 'ヘア'],
            ['name' => 'ネイル・マツゲ'],
            ['name' => '整体・カイロ・酸素・温浴'],
            ['name' => 'フェイシャル・ボディ・脱毛'],
            ['name' => '美容クリニック'],
            ['name' => 'その他'],
        ];
        DB::table('m_job_types')->insert($dataJobType);

        $dataJobExperiences = [
            ['name' => 'ブランク'],
            ['name' => '未経験者可'],
            ['name' => '管理美容師免許歓迎'],
            ['name' => '幹部・店長候補歓迎'],
            ['name' => '美容師歓迎'],
            ['name' => '免許・資格不問'],
            ['name' => '通信生（見習い）相談可'],
        ];
        DB::table('m_job_experiences')->insert($dataJobExperiences);

        $dataWorkTypes = [
            ['name' => '正社員'],
            ['name' => '派遣社員'],
            ['name' => '契約社員'],
            ['name' => 'アルバイト'],
            ['name' => 'その他'],
        ];
        DB::table('m_work_types')->insert($dataWorkTypes);

        $dataJobFeatureCategories = [
            ['name' => '募集の特徴'],
            ['name' => '企業の特徴'],
            ['name' => '店舗の特徴'],
        ];
        DB::table('m_job_feature_categories')->insert($dataJobFeatureCategories);

        $dataProvinceDistricts = [
            ['name' => '北海道'],
            ['name' => '東北'],
            ['name' => '関東'],
            ['name' => '中部'],
            ['name' => '近畿'],
            ['name' => '中国'],
            ['name' => '四国'],
            ['name' => '九州・沖縄'],
        ];
        DB::table('m_province_districts')->insert($dataProvinceDistricts);

        $dataLearningStatus = [
            ['name' => '卒業'],
            ['name' => '卒業見込み·'],
            ['name' => '休退'],
        ];
        DB::table('m_learning_status')->insert($dataLearningStatus);

        $dataInterviewStatus = [
            ['name' => '応募中'],
            ['name' => '面接待ち'],
            ['name' => '結果待ち'],
            ['name' => '採用'],
            ['name' => '不採用'],
            ['name' => 'キャンセル'],
        ];
        DB::table('m_interviews_status')->insert($dataInterviewStatus);

        $dataInterviewApproaches = [
            ['name' => 'オンライン面接'],
            ['name' => '対面'],
            ['name' => '電話面接'],
        ];
        DB::table('m_interview_approaches')->insert($dataInterviewApproaches);

        $dataFeedbackTypes = [
            ['name' => '年収／月収に関する相談'],
            ['name' => '福利厚生に関するお問合せ'],
            ['name' => '教育制度を知りたい'],
            ['name' => '残業代が出るか知りたいなど'],
            ['name' => 'その他'],
        ];
        DB::table('m_feedback_types')->insert($dataFeedbackTypes);

        $dataSalaryTypes = [
            ['name' => '万円/月収', 'term' => 8760, 'currency' => '￥'],
            ['name' => '万円/年収', 'term' => 720, 'currency' => '￥'],
            ['name' => '円/時給', 'term' => 1, 'currency' => '￥'],
            ['name' => '円/日給', 'term' => 24, 'currency' => '￥'],
        ];
        DB::table('m_salary_types')->insert($dataSalaryTypes);


        $dataJobStatus = [
            ['name' => '下書き'],
            ['name' => '公開'],
            ['name' => '終了'],
        ];
        DB::table('m_job_statuses')->insert($dataJobStatus);

        $path = base_path().'/database/seeders/location.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
