<?php
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status['opcache_enabled']) {
        echo "✅ OPcache ENABLED<br>";
        echo "Memory Used: " . round($status['memory_usage']['used_memory']/1024/1024, 2) . " MB<br>";
        echo "Cached Scripts: " . $status['opcache_statistics']['num_cached_scripts'];
    } else {
        echo "❌ OPcache DISABLED";
    }
} else {
    echo "❌ OPcache NOT INSTALLED";
}