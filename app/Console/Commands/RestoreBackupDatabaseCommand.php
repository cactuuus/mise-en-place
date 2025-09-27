<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class RestoreBackupDatabaseCommand extends Command
{
    protected $signature = 'backup:restore-db
                            {--latest : Restore from the latest backup}
                            {--list : List available backups}';

    protected $description = 'Restore database from backup stored in configured disks';

    public function handle()
    {
        if ($this->option('list')) {
            return $this->listBackups();
        }

        if ( ! $this->confirm('This will completely replace your current database. Are you sure?')) {
            $this->info('Backup restore cancelled.');

            return 0;
        }

        $backupFile = $this->getBackupFile();

        if ( ! $backupFile) {
            $this->error('No backup file found or specified.');

            return 1;
        }

        $this->info("Restoring from backup: {$backupFile['file']} (disk: {$backupFile['disk']})");

        try {
            // Download and extract backup
            $this->info('Downloading backup...');
            $localPath = $this->downloadBackup($backupFile);

            $this->info('Extracting backup...');
            $sqlFile = $this->extractBackup($localPath);

            // Clear current database
            $this->info('Clearing current database...');
            $this->clearDatabase();

            // Restore from SQL file
            $this->info('Restoring database...');
            $this->restoreDatabase($sqlFile);

            // Cleanup
            $this->cleanup($localPath, $sqlFile);

            $this->info('✅ Database restore completed successfully!');
        } catch (\Exception $e) {
            $this->error("Restore failed: ".$e->getMessage());

            return 1;
        }

        return 0;
    }

    private function listBackups()
    {
        $backupDisks = config('backup.backup.destination.disks');
        $backupName  = config('backup.backup.name');

        $this->info("Available backups");
        $this->info('========================================');

        $totalFiles = 0;

        foreach ($backupDisks as $diskName) {
            $this->info("📁 Disk: {$diskName}");
            $this->info('----------------------------------------');

            try {
                $disk  = Storage::disk($diskName);
                $files = collect($disk->files($backupName))
                    ->filter(fn($file) => str_ends_with($file, '.zip'))
                    ->sortByDesc(fn($file) => $disk->lastModified($file));

                if ($files->isEmpty()) {
                    $this->line("   ⚠️  No backup files found on {$diskName}");
                } else {
                    foreach ($files as $file) {
                        $size     = $this->formatBytes($disk->size($file));
                        $date     = date('Y-m-d H:i:s', $disk->lastModified($file));
                        $fileName = basename($file);

                        $this->line(">   📦 {$fileName} | 📅 {$date} | 💾 {$size}");
                        $totalFiles++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error accessing disk {$diskName}: ".$e->getMessage());
            }

            $this->line("");
        }

        $this->info("Total backup files found: {$totalFiles}");

        return 0;
    }

    private function formatBytes($size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2).' '.$units[$i];
    }

    private function getBackupFile(): array
    {
        if ($this->option('latest')) {
            return $this->getLatestBackup();
        }

        return $this->selectBackupInteractively();
    }

    private function getLatestBackup(): array
    {
        $latest = $this->getListOfBackups()->first();

        return ['file' => $latest['file'], 'disk' => $latest['disk']];
    }

    private function getListOfBackups(): Collection
    {
        $backupDisks = config('backup.backup.destination.disks');
        $backupName  = config('backup.backup.name');
        $allFiles    = collect();

        foreach ($backupDisks as $diskName) {
            try {
                $disk  = Storage::disk($diskName);
                $files = collect($disk->files($backupName))
                    ->filter(fn($file) => str_ends_with($file, '.zip'))
                    ->map(function ($file) use ($disk, $diskName) {
                        return [
                            'file'     => $file,
                            'disk'     => $diskName,
                            'modified' => $disk->lastModified($file),
                        ];
                    });

                $allFiles = $allFiles->concat($files);
            } catch (\Exception $e) {
                $this->warn("Could not access disk {$diskName}: ".$e->getMessage());
                continue;
            }
        }

        return $allFiles->sortByDesc('modified');
    }

    private function selectBackupInteractively(): array
    {
        $sortedFiles = $this->getListOfBackups()->values();

        $choices = $sortedFiles->map(function ($fileData) {
            $disk     = Storage::disk($fileData['disk']);
            $date     = date('Y-m-d H:i:s', $fileData['modified']);
            $size     = $this->formatBytes($disk->size($fileData['file']));
            $fileName = basename($fileData['file']);

            return "{$fileName} ({$fileData['disk']}) - {$date} - {$size}";
        })->toArray();

        $selected = $this->choice('Enter index of backup to restore:', $choices);
        $index    = array_search($selected, $choices);

        $selectedFile = $sortedFiles[$index];

        return ['file' => $selectedFile['file'], 'disk' => $selectedFile['disk']];
    }

    private function downloadBackup($backupData): string
    {
        $backupFile = $backupData['file'];
        $diskName   = $backupData['disk'];

        $disk      = Storage::disk($diskName);
        $localPath = storage_path('app/restore-'.basename($backupFile));

        $contents = $disk->get($backupFile);
        file_put_contents($localPath, $contents);

        return $localPath;
    }

    private function extractBackup($zipPath)
    {
        $extractPath = storage_path('app/backup-extract/');

        // Create extract directory
        if ( ! is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new \Exception('Could not open backup zip file.');
        }

        $databaseName   = config('database.connections.'.config('database.default').'.database');
        $backupFilename = "mysql-{$databaseName}.sql";
        $sqlFile        = $extractPath."db-dumps/".$backupFilename;

        if ( ! file_exists($sqlFile)) {
            throw new \Exception(
                "No SQL file matching '{$backupFilename}' found in db-dumps folder.
                Try inspecting the files in backup, restore it manually if needed.",
            );
        }

        return $sqlFile;
    }

    private function clearDatabase()
    {
        if (app()->environment('production')) {
            if ( ! $this->confirm('You are in PRODUCTION. Are you absolutely sure you want to clear the database?')) {
                throw new \Exception('Database clear cancelled by user.');
            }
        }

        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    private function restoreDatabase($sqlFile)
    {
        $database = config('database.connections.'.config('database.default'));

        $command = sprintf(
            'mysql -h %s -P %s -u %s -p%s %s < %s',
            $database['host'],
            $database['port'],
            $database['username'],
            $database['password'],
            $database['database'],
            $sqlFile,
        );

        $output = shell_exec($command.' 2>&1');

        if ($output && str_contains($output, 'ERROR')) {
            throw new \Exception('MySQL restore failed: '.$output);
        }
    }

    private function cleanup($zipPath, $sqlFile)
    {
        // Remove downloaded zip
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        // Remove extract directory
        $extractDir = dirname($sqlFile);
        if (is_dir($extractDir)) {
            $this->deleteDirectory($extractDir);
        }
    }

    private function deleteDirectory($dir)
    {
        if ( ! is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }
}
