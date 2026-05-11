<?php

use App\Models\Bug;
use App\Services\TicketService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tickets = app(TicketService::class);
foreach (Bug::all() as $bug) {
    if ($tickets->fromBugId($bug->id) === 'BUG-8XX7L3') {
        print_r($bug->toArray());
        break;
    }
}
