<?php

namespace App\Jobs;

use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RunBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 jam
    public $tries = 1;

    protected string $statusKey;

    public function __construct(string $statusKey)
    {
        $this->statusKey = $statusKey;
    }

    public function handle(BackupService $service): void
    {
        Cache::put($this->statusKey, ['percent' => 0, 'message' => 'Memulai backup...', 'done' => false], now()->addHours(2));

        try {
            $path = $service->run(function (int $percent, string $message) {
                Cache::put($this->statusKey, ['percent' => $percent, 'message' => $message, 'done' => false], now()->addHours(2));
            });

            Cache::put($this->statusKey, [
                'percent' => 100,
                'message' => 'Backup selesai',
                'done' => true,
                'path' => $path,
            ], now()->addHours(2));
        } catch (\Throwable $e) {
            Cache::put($this->statusKey, [
                'percent' => 0,
                'message' => 'Gagal: ' . $e->getMessage(),
                'done' => true,
                'error' => true,
            ], now()->addHours(2));
        }
    }
}