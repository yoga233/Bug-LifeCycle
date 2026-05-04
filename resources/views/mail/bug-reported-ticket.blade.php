<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket Bug Report</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.5;">
    <p>Halo {{ $guestName }},</p>

    <p>Terima kasih sudah melaporkan bug. Berikut nomor tiket Anda:</p>

    <p style="font-size: 20px; font-weight: bold;">{{ $ticket }}</p>

    <p><strong>Judul:</strong> {{ $title }}</p>

    <p>
        Anda bisa melacak status bug melalui link berikut:
        <br>
        <a href="{{ $trackingUrl }}">{{ $trackingUrl }}</a>
    </p>

    <p>Salam,<br>DevPanel</p>
</body>
</html>
