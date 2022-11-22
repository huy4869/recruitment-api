<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body style="background: #cccccc">
<div style="background:#fff; width:100%;margin:auto; word-break: break-all">
    <div style="padding:20px">
        <p>{{ sprintf('%s %s', $user->first_name, $user->last_name) }}様</p>
        <p>いつも {{ config('app.name') }} より、アカウントのパスワードを更新されました。</p>
        <p>■アカウント情報</p>
        <p>アカウントタイプ：{{ $user->role->name }}</p>
        <p>お名前：{{ sprintf('%s %s', $user->first_name, $user->last_name) }}</p>
        <p>メールアドレス：{{ $user->email }}</p>
        <p>パスワード：{{ $new_password }}</p>
        <p>■ログインURL</p>
        <a href="{{ config('app.user_url') . '/login' }}">{{ config('app.user_url') . '/login' }}</a>
        <p>========================================</p>
        <p>{{ config('app.name') }}</p>
        <p>URL:</p>
        <p>Tel:</p>
        <p>Mail:</p>
        <p>========================================</p>
    </div>
</div>
</body>
</html>
