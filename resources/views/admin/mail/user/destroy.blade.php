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
        <p>{{ sprintf('%s %s', $user->first_name, $user->last_name) }} 様</p>
        <p>平素は{{ config('app.name') }}をご利用頂き、誠にありがとうございました。<br>
        {{ config('app.name') }}の退会が完了しましたので、お知らせいたします。</p>
        <p>またの機会がありましたら、ご利用よろしくお願い申し上げます。<br>
        登録したことがない、退会手続きを間違えてしまった場合は下記までご連絡下さい。</p>
        <p>お問い合わせメールアドレス: {{ config('mail.from.address') }} </p>
        <p>このメールは自動送信しておりますので、お問い合わせは下記ホームページよりお願いいたします。</p>
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
