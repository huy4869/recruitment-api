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
        <p>いつも {{ config('app.name') }} をご利用いただきまして、誠にありがとうございます。</p>
        <p>以下の URL にアクセスして、アカウントを有効にしてください。</p>
        <a href="{{ $data['url'] }}">{{ $data['url'] }}</a>
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