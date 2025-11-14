<?php

declare(strict_types=1);

namespace App\Domain\System\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

final class CleanupOldLogsAction
{
    public function __construct(
        private readonly int $retentionDays = 14
    ) {}

    public function execute(): array
    {
        $cutoffDate = now()->subDays($this->retentionDays);

        $stats = collect($this->getLogDirectories())
            ->filter(fn (string $directory) => File::isDirectory($directory))
            ->flatMap(fn (string $directory) => $this->getFilesInDirectory($directory))
            ->map(fn (string $file) => $this->processFile($file, $cutoffDate))
            ->reduce(function (array $carry, array $result) {
                $carry['files_deleted'] += $result['deleted'] ? 1 : 0;
                $carry['files_failed'] += $result['failed'] ? 1 : 0;
                $carry['bytes_freed'] += $result['bytes_freed'];

                if ($result['error']) {
                    $carry['errors'][] = $result['error'];
                }

                return $carry;
            }, [
                'directories_scanned' => $this->countValidDirectories(),
                'files_deleted' => 0,
                'files_failed' => 0,
                'bytes_freed' => 0,
                'errors' => [],
            ]);

        Log::info('Old log cleanup completed', $stats);

        return $stats;
    }

    private function getLogDirectories(): array
    {
        return [
            // Curling.io import logs
            base_path('curling.io'),
            // Add more log directories here as needed
            // storage_path('logs/imports'),
            // storage_path('logs/exports'),
        ];
    }

    private function countValidDirectories(): int
    {
        return collect($this->getLogDirectories())
            ->filter(fn (string $directory) => File::isDirectory($directory))
            ->count();
    }

    private function getFilesInDirectory(string $directory): array
    {
        return File::glob($directory.'/*.txt');
    }

    private function processFile(string $file, $cutoffDate): array
    {
        try {
            $fileModifiedTime = File::lastModified($file);

            if ($fileModifiedTime >= $cutoffDate->timestamp) {
                return [
                    'deleted' => false,
                    'failed' => false,
                    'bytes_freed' => 0,
                    'error' => null,
                ];
            }

            $fileSize = File::size($file);

            if (File::delete($file)) {
                return [
                    'deleted' => true,
                    'failed' => false,
                    'bytes_freed' => $fileSize,
                    'error' => null,
                ];
            }

            return [
                'deleted' => false,
                'failed' => true,
                'bytes_freed' => 0,
                'error' => "Failed to delete: {$file}",
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to process log file during cleanup', [
                'file' => $file,
                'error' => $e->getMessage(),
            ]);

            return [
                'deleted' => false,
                'failed' => true,
                'bytes_freed' => 0,
                'error' => "Error processing {$file}: {$e->getMessage()}",
            ];
        }
    }
}
