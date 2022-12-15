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
        <p>{{ $data['name'] }}様</p>
        <p>いつも {{ config('app.name') }} をご利用いただきまして、誠にありがとうございます。</p>
        <p>下記URLをクリックし、新しいパスワードを設定してください。</p>
        <a href="{{ $data['url'] }}">{{ $data['url'] }}</a>
        <p>上記URLは送信から30分間のみ有効です。</p>
        <p>30分を過ぎた場合は、もう一度最初からパスワード再設定をやり直してください。</p>
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
