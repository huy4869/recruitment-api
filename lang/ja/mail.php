<?php

return [
    'subject' => [
        'contact' => config('app.name') . 'までのお問い合わせありがとうございました。',
        'store_user' => config('app.name') . ' create new user',
        'update_user' => config('app.name') . 'よりパスワード再発行のお知らせ',
        'destroy_user' => config('app.name') . ' delete user',
        'verify_register' => config('app.name') . ' verify register',
        'verify_account' => 'メールアドレスの認証',
        'forgot_password' => 'パスワード再設定',
    ]
];
