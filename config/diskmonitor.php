<?php

return [
    // Pisahkan dengan koma di .env, contoh: D:\,E:\
    'paths' => array_filter(explode(',', env('DISK_MONITOR_PATHS', '/'))),
    'warning_threshold' => (float) env('DISK_WARNING_THRESHOLD', 70),
    'critical_threshold' => (float) env('DISK_CRITICAL_THRESHOLD', 85),
];