<?php

return [

    /*
    |--------------------------------------------------------------------------
    | バリデーションの言語行
    |--------------------------------------------------------------------------
    |
    | 次の言語行には、バリデータクラスで使用されるデフォルトの
    | エラーメッセージが含まれています。これらのルールの一部には、
    | サイズルールなどの複数のバージョンがあります。
    | ここでこれらの各メッセージを自由に調整してください。
    |
    */

    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeには有効なURLを指定してください。',
    'after' => ':attributeには:date以降の日付を指定してください。',
    'after_or_equal' => ':attributeには:dateかそれ以降の日付を指定してください。',
    'alpha' => ':attributeには英字のみからなる文字列を指定してください。',
    'alpha_dash' => ':attributeには英数字・ハイフン・アンダースコアのみからなる文字列を指定してください。',
    'alpha_num' => ':attributeには英数字のみからなる文字列を指定してください。',
    'array' => ':attributeには配列を指定してください。',
    'before' => ':attributeには:date以前の日付を指定してください。',
    'before_or_equal' => ':attributeには:dateかそれ以前の日付を指定してください。',
    'between' => [
        'numeric' => ':attributeは:min～:maxの数字を入力してください。',
        'file' => ':attributeには:min～:max KBのファイルを指定してください。',
        'string' => ':attributeには:min～:max文字の文字列を指定してください。',
        'array' => ':attributeには:min～:max個の要素を持つ配列を指定してください。',
    ],
    'boolean' => ':attributeには真偽値を指定してください。',
    'confirmed' => ':attributeが確認用の値と一致しません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_equals' => ':attributeは:dateと同じ日付でなければなりません。',
    'date_format' => ':attributeは:format形式と一致しません。',
    'different' => ':attributeには:otherとは異なる値を指定してください。',
    'digits' => ':attributeは:digits桁の数字でなければなりません。',
    'digits_between' => ':attributeは:min～:max桁の数字である必要があります。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeに指定された値は重複しています。',
    'email' => ':attributeが正しく入力されていません。',
    'ends_with' => ':attributeは、:valuesのいずれかで終了する必要があります。',
    'exists' => ':attributeが見つかりませんでした。',
    'file' => ':attributeはファイルでなければなりません。',
    'filled' => ':attributeには値が必要です。',
    'gt' => [
        'numeric' => ':attributeは:valueより大きくなければなりません。',
        'file' => ':attributeは:valueキロバイトより大きくなければなりません。',
        'string' => ':attributeは:value文字より大きくなければなりません。',
        'array' => ':attributeには:valueより多くのアイテムが必要です。',
    ],
    'gte' => [
        'numeric' => ':attributeは:value以上でなければなりません。',
        'file' => ':attributeは:valueキロバイト以上でなければなりません。',
        'string' => ':attributeは:value文字以上でなければなりません。',
        'array' => ':attributeには:value以上のアイテムが必要です。',
    ],
    'image' => ':attributeは画像でなければなりません。',
    'in' => '選択された:attributeは無効です。',
    'in_array' => ':attributeは:otherに存在しません。',
    'integer' => ':attributeは整数でなければなりません。',
    'ip' => ':attributeは有効なIPアドレスでなければなりません。',
    'ipv4' => ':attributeは有効なIPv4アドレスでなければなりません。',
    'ipv6' => ':attributeは有効なIPv6アドレスでなければなりません。',
    'json' => ':attributeは有効なJSON文字列でなければなりません。',
    'lt' => [
        'numeric' => ':attributeは:valueより小さくなければなりません。',
        'file' => ':attributeは:valueキロバイトより小さくなければなりません。',
        'string' => ':attributeは:value文字より小さくなければなりません。',
        'array' => ':attributeには:valueより少ないアイテムが必要です。',
    ],
    'lte' => [
        'numeric' => ':attributeは:value以下でなければなりません。',
        'file' => ':attributeは:valueキロバイト以下でなければなりません。',
        'string' => ':attributeは:value文字以下でなければなりません。',
        'array' => ':attributeには:value以下のアイテムが必要です。',
    ],
    'max' => [
        'numeric' => ':attributeは:maxより大きくてはいけません。',
        'file' => ':attributeは:maxキロバイトを超えてはいけません。',
        'string' => ':attributeは:max文字以内で入力してください。',
        'array' => ':attributeには:max個を超えるアイテムを含めることはできません。',
    ],
    'mimes' => ':attributeは:valuesタイプのファイルでなければなりません。',
    'mimetypes' => ':attributeは:valuesタイプのファイルでなければなりません。',
    'min' => [
        'numeric' => ':attributeは:maxより小さくてはいけません。',
        'file' => ':attributeは:maxキロバイトより小さくてはいけません。',
        'string' => ':attributeは:max文字より小さくてはいけません。',
        'array' => ':attributeには少なくとも:min個のアイテムが必要です。',
    ],
    'multiple_of' => ':attributeは:valueの倍数である必要があります。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeは無効な形式です。',
    'numeric' => ':attributeは数値でなければなりません。',
    'password' => 'パスワードが間違っています。',
    'present' => ':attributeが存在する必要があります。',
    'regex' => ':attributeは無効な形式です。',
    'required' => ':attributeが入力されていません。',
    'required_if' => ':otherが:valueの場合、:attributeは必須です。',
    'required_unless' => ':otherが:valueではない場合、:attributeは必須です。',
    'required_with' => ':valuesのうち一つでも存在する場合、:attributeは必須です。',
    'required_with_all' => ':valuesのうち全て存在する場合、:attributeは必須です。',
    'required_without' => ':valuesのうちどれか一つでも存在していない場合、:attributeは必須です。',
    'required_without_all' => ':valuesのうち全て存在していない場合、:attributeは必須です。',
    'prohibited' => ':attributeは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは禁止されています。',
    'prohibited_unless' => ':otherが:valuesにない限り、:attributeは禁止されています。',
    'same' => ':attributeと:otherは一致する必要があります。',
    'size' => [
        'numeric' => ':attributeは:sizeでなければなりません。',
        'file' => ':attributeは:sizeキロバイトでなければなりません。',
        'string' => ':attributeは:size文字でなければなりません。',
        'array' => ':attributeには:sizeが含まれている必要があります。',
    ],
    'starts_with' => ':attributeは:valuesのいずれかで始まる必要があります。',
    'string' => ':attributeは文字列でなければなりません。',
    'timezone' => ':attributeは有効なタイムゾーンでなければなりません。',
    'unique' => ':attributeはすでに使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url' => ':attributeは有効なURLを入力してください。',
    'uuid' => ':attributeは有効なUUIDでなければなりません。',

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーションの言語行
    |--------------------------------------------------------------------------
    |
    | ここでは、「attribute.rule」という規則を使用して行に名前を付けて、
    | 属性のカスタム検証メッセージを指定できます。 これにより、特定の属性ルールに
    | 特定のカスタム言語行をすばやく指定できます。
    |
    */

    'password_regex' => 'パスワードは、6 ～ 32 文字、文字、数字、および特殊文字で構成されます。',
    'upload_error_type' => 'アップロードの種類が無効です。',
    'without_space' => ':attributeにはスペースは含まれません。',
    'phone_not_verified' => '電話番号認証にエラーが発生しました。',
    'user_first_name' => 'セイは全角カタカナで入力してください。',
    'user_last_name' => 'メイは全角カタカナで入力してください。',

    'custom' => [
        'birthday' => [
            'before' => '今日以前の日付を入力してください。'
        ],
        'period_start' => [
            'before' => '終了年月は開始年月より未来年月を選択してください'
        ],
        'period_end' => [
            'after' => '開始日より後の終了日を選択してください'
        ],
        'zip_code' => [
            'digits_between' => '郵便番号は半角数字:max桁を入力してください。'
        ],
        'phone_number' => [
            'digits_between' => '電話番号は:min桁または:max桁の半角数字で入力してください。'
        ],
        'username' => [
            'regex' => 'ユーザーIDは4文字以上40文字以内で入力してください。',
            'between' => [
                'string' => ':attributeは:min文字以上:max文字以内で入力してください。',
            ],
            'alpha_dash' => 'ユーザーIDは4文字以上40文字以内で入力してください。',
        ],
        'image' => [
            'max' => [
                'file' => 'アップロード可能なファイルは最大20MBです。再度選択してください。',
            ],
            'mimes' => 'ファイルのアップロードに失敗しました。',
            'mimetypes' => 'JPG、JPEG、PNG、SVGの写真をアップしてください。',
        ],
        'wrong_password' => 'メールアドレスまた、パスワードが正しくありません。',
        'half_size' => ':attributeは半角英数字で入力してください。',
        'email' => [
            'max' => ':attributeは半角255英数字以内で入力してください。'
        ],
        'password' => [
            'max' => ':attributeは半角英数字で4桁〜12桁まで入力してください。',
            'min' => ':attributeは半角英数字で4桁〜12桁まで入力してください。',
        ],
        'feedback' => [
            'content_max' => 'お問い合わせ内容詳細は1000文字以内で入力してください。'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | カスタムバリデーション属性
    |--------------------------------------------------------------------------
    |
    | 次の言語行を使用して、属性プレースホルダーを「email」ではなく「E-Mail Address」などの
    | 読みやすいものに置き換えます。 これは単にメッセージをより表現力豊かにするのに役立ちます。
    |
    */

    'attributes' => [
        'current_password' => '現在のパスワード',
        'role_id' => '役割',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認のため再入力)',
        'username' => 'ユーザーID',
        'status' => 'ステータス',
        'tel' => '電話番号',
        'description' => '説明文',
        'website' => 'ウェブサイト',
        'postal_code' => '郵便番号',
        'first_name' => '姓',
        'last_name' => '名',
        'first_name_furigana' => '姓（フリガナ）',
        'last_name_furigana' => '名（フリガナ）',
        'birthday' => '生年月日',
        'address' => '建物名',
        'gender' => '性別',
        'note' => '非アクティブされた理由',
        'avatar' => 'プロフィール画像',
        'name' => 'ス名',
        'image_url' => '写真',
        'price' => '単価',
        'images' => '実績紹介写真',
        'images.*.url' => '実績紹介写真',
        'email' => 'メールアドレス',
        'type' => '注文オプション',
        'province_id' => '都道府県',
        'city' => '市区町村・番地',
        'content' => 'お問い合わせ内容',
        'feedback_type_ids' => 'お問い合わせ内容選択',
        'feedback_content' => 'お問い合わせ内容詳細',
        'job_types' => '職種',
        'job_types.*.name' => '職種',
        'work_types' => '雇用形態',
        'work_types.*.name' => '雇用形態',
        'store_name' => '店舗名',
        'company_name' => '勤務先企業名',
        'period_start' => '生年月日',
        'period_end' => '生年月日',
        'position_offices' => '業務内容',
        'position_offices.*.name' => '業務内容',
        'business_content' => '業務内容',
        'experience_accumulation' => '主な取り組み、具体的な施策、 得られた経験',
    ],

    'COM' => [
        '001' => ':attributeが入力されていません。',
        '002' => ':attributeが正しく入力されていません。',
        '003' => ':attributeは半角255英数字以内で入力してください。',
        '004' => ':attributeは半角英数字で入力してください。',
        '005' => ':attributeは半角英数字で4桁〜12桁まで入力してください。',
        '007' => ':attributeが一致しません。',
        '008' => ':attributeは255文字以内で入力してください。',
        '011' => ':attributeは0から始まる10桁から13桁まで入力してください。',
        '014' => ':attributeは1000文字以内で入力してください。',
        '016' => ':attributeは数字とハイフンのみで入力してください。',
    ],

    'INF' => [
        '008' => '送信成功しました。',
    ],

    'ERR' => [
        '002' => 'メールアドレスが既に登録されました。',
        '009' => 'パスワードが正しくありません。',
        '010' => 'パスワードへん変更が失敗しました。',
        '011' => 'お気に入り求人リストから削除失敗しました。',
        '012' => '送信が失敗しました。',
        'exist' => [
            'favorite_job' => '求人掲載のお気に入りは存在しません',
        ],
        '015' => '自己PR編集が保存失敗しました。'
    ],

    'has_not_permission' => 'スーパー管理者レベルである必要があります',
    'role_exist' => 'ロールが存在しません',
];
