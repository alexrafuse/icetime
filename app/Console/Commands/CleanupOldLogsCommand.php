<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\System\Actions\CleanupOldLogsAction;
use Illuminate\Console\Command;

class CleanupOldLogsCommand extends Command
{
    protected $signature = 'logs:cleanup
                            {--days=14 : Number of days to retain logs}';

    protected $description = 'Clean up log files older than specified retention period';

    public function handle(CleanupOldLogsAction $cleanupAction): int
    {
        $retentionDays = (int) $this->option('days');

        $this->info("Cleaning up log files older than {$retentionDays} days...");
        $this->newLine();

        try {
            $action = new CleanupOldLogsAction($retentionDays);
            $stats = $action->execute();

            $this->displayStats($stats);

            if ($stats['files_deleted'] > 0) {
                $this->newLine();
                $this->info('Cleanup completed successfully!');
            } else {
                $this->newLine();
                $this->info('No old log files found to clean up.');
            }

            if (! empty($stats['errors'])) {
                $this->newLine();
                $this->warn('Errors encountered:');
                foreach ($stats['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Cleanup failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function displayStats(array $stats): void
    {
        $this->table(
            ['Metric', 'Count'],
            [
                ['Directories Scanned', $stats['directories_scanned']],
                ['Files Deleted', $stats['files_deleted']],
                ['Files Failed', $stats['files_failed']],
                ['Bytes Freed', $this->formatBytes($stats['bytes_freed'])],
            ]
        );
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
