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
        <p>{{ sprintf('%s %s', $data['user']['first_name'], $data['user']['last_name']) }}様</p>
        <p>{{ config('app.name') }} をご利用いただき、ありがとうございます。</p>
        <p>{{ \App\Helpers\DateTimeHelper::formatDateJa($data['date']) . ' interview_online.blade.php' . $data['hours'] }}
            日のインタビュー時間とインタビュー リンクの URL をお知らせします。</p>
        <a href="{{ $data['meet_link'] }}">{{ $data['meet_link'] }}</a>
        <p>ご不明な点等ございましたら、下記にお問い合わせをお願いします。</p>
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
