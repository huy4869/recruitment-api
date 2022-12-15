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
        <p>{{ config('app.name') }} へようこそ！</p>
        <p>以下のリンクをクリックして、メールアドレス認証が完了となります。</p>
        <p><a href="{{ $data['url'] }}">{{ $data['url'] }}</a></p>
        <br>
        <p>ご不明な点等ございましたら、下記にお問い合わせをお願いします。</p>
        <p>========================================</p>
        <p>{{ config('app.name') }}</p>
        <br>
        <p>URL:</p>
        <p>Tel:</p>
        <p>Mail:</p>
        <br>
        ========================================
    </div>
</div>
</body>
</html>
