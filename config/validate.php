<?php

return [
    'card_number_max_length' => 16,
    'email_max_length' => 255,
    'name_max_length' => 50,
    'numeric_max_value' => 9000000,
    'zip_code_max_length' => 7,
    'password_min_length' => 4,
    'max_image_detail' => 3,
    'password_max_length' => 12,
    'string_max_length' => 255,
    'phone_min_length' => 10,
    'phone_max_length' => 13,
    'quantity_max_length' => 99,
    'token_expire' => (int)env('TOKEN_EXPIRE', 90),
    'text_max_length' => 1000,
    'text_max_length_information_pr' => 2000,
    'date_in_new_range' => 7,
    'min_age' => 20,
    'notify_user_interview_delay' => env('NOTIFY_INTERVIEW_DELAY', 1),
    'max_length_text' => 255,
    'year' => 4,
    'month' => [
        'min_length' => 1,
        'max_length' => 2,
    ],
];
