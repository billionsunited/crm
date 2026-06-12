<?php

use App\Models\FollowupNotification;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$notifications = FollowupNotification::all();
$fixedCount = 0;

foreach ($notifications as $notification) {
    $oldUrl = $notification->redirect_url;
    
    // Remove absolute localhost URLs
    if (strpos($oldUrl, 'http://localhost') === 0) {
        $notification->redirect_url = str_replace('http://localhost/', '', $oldUrl);
        $notification->save();
        $fixedCount++;
        continue;
    }
    
    // Ensure relative URLs don't have leading slash for consistency
    if (strpos($oldUrl, '/') === 0) {
        $notification->redirect_url = ltrim($oldUrl, '/');
        $notification->save();
        $fixedCount++;
    }
}

echo "Fixed $fixedCount notifications.\n";
