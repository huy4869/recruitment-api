<?php

return [
    'attributes' => [
        'name' => '求人タイトル',
        'store_id' => '店舗名',
        'job_status_id' => 'ステータス',
        'pick_up_point' => 'ピックアップポイント',
        'job_banner' => 'メイン画像',
        'job_details' => 'サブ画像',
        'job_type_ids' => '募集職種',
        'description' => '仕事内容',
        'work_type_ids.*' => '雇用形態',
        'salary_type_id' => '給与',
        'salary_min' => '給与',
        'salary_max' => '給与',
        'salary_description' => '給与詳細',
        'start_work_time' => '始業時間',
        'end_work_time' => '終業時間',
        'shifts' => '勤務時間詳細 ',
        'age_min' => '歳以上',
        'age_max' => '歳以下',
        'gender_ids' => '性別',
        'experience_ids' => '経験',
        'postal_code' => '勤務先',
        'province_id' => '都道府県',
        'address' => '市区町村・番地',
        'building' => '建物名',
        'station_ids' => '最寄り駅',
        'welfare_treatment_description' => '福利厚生・待遇 ',
        'feature_ids' => '特徴',
    ],

    'range_hours_type' => [
        'full_day' => '24時間表示',
        'half_day' => '午前・午後表示',
    ],
    'morning' => [
        'one_hours' => '午前:hours時',
        'half_hours' => '午前:hours時半',
    ],
    'afternoon' => [
        'one_hours' => '午後:hours時',
        'half_hours' => '午後:hours時半',
    ],
    'search_in_popular_area' => 'の人気エリアで検索',
];
