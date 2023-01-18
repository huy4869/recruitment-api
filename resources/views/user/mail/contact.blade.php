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
        <p>{{ $data ? $data['name'] : sprintf('%s %s', $user['first_name'], $user['last_name']) }}様</p>
        <p>いつも{{ config('app.name') }}をご利用いただきまして、誠にありがとうございます。</p>
        <p>下記のお問い合わせを送信完了しました。</p>
        <p>メールアドレス：{{ $data ? $data['email'] : $user['email'] }}</p>
        <p>お名前：{{ $data ? $data['name'] : sprintf('%s %s', $user['first_name'], $user['last_name']) }}</p>
        <p>電話番号：{{ $data ? $data['tel'] : $user['tel'] }}</p>
        <p>お問い合わせ内容：{{ $data['content'] }}</p>
        <p>ご登録のメールアドレス宛にご返信させていただきます。少々お待ちいただけます。</p>

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
