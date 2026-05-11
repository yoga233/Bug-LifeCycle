<?php

use App\Models\Bug;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$bug = Bug::find(157);
if ($bug) {
    echo $bug->comments->where('type', 'discussion')->map(fn($c) => [
        'id' => $c->id,
        'content' => $c->content,
        'user_name' => $c->user?->name,
        'user_initial' => strtoupper(substr($c->user?->name ?? 'U', 0, 1)),
        'created_at' => $c->created_at?->timezone(config('app.timezone'))?->format('d M Y, H:i'),
    ])->values()->toJson();
} else {
    echo "Bug 157 not found";
}
