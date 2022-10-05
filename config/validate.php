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
    'phone_min_length' => 9,
    'phone_max_length' => 15,
    'quantity_max_length' => 99,
    'token_expire' => (int)env('TOKEN_EXPIRE', 90),
];
