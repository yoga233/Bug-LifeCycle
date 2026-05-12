Bug Status Updated — {{ $ticket }}
=====================================

Hello {{ $guestName }},

{{ $headline }}.

TICKET   : {{ $ticket }}
TITLE    : {{ $bugTitle }}
STATUS   : {{ $statusLabel }}
PROGRESS : {{ $progress }}%

Progress timeline:
  [{{ $progress >= 10 ? 'X' : ' ' }}] Reported
  [{{ $progress >= 30 ? 'X' : ' ' }}] Assigned
  [{{ $progress >= 55 ? 'X' : ' ' }}] In Progress
  [{{ $progress >= 80 ? 'X' : ' ' }}] Testing
  [{{ $progress >= 100 ? 'X' : ' ' }}] Resolved

Track your bug report at:
{{ $trackingUrl }}

---
You are receiving this because you submitted a bug report.
You will be notified on each status change until it is resolved.
© {{ date('Y') }} {{ config('app.name') }}
