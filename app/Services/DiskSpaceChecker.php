<?php

namespace App\Services;

class DiskSpaceChecker
{
    public function getAllUsage(): array
    {
        $paths = config('diskmonitor.paths', ['/']);
        $results = [];

        foreach ($paths as $path) {
            $results[] = $this->getUsage(trim($path));
        }

        return $results;
    }

    public function getUsage(string $path): array
    {
        $total = disk_total_space($path);
        $free  = disk_free_space($path);
        $used  = $total - $free;

        $percentUsed = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'path'          => $path,
            'total_bytes'   => $total,
            'free_bytes'    => $free,
            'used_bytes'    => $used,
            'percent_used'  => $percentUsed,
            'is_warning'    => $percentUsed >= config('diskmonitor.warning_threshold', 70),
            'is_critical'   => $percentUsed >= config('diskmonitor.critical_threshold', 85),
        ];
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}