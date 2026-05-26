<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    /** @phpstan-return non-falsy-string */
    public function storageDirectory(): string
    {
        $dir = storage_path('app/'.trim((string) Config::get('database_backup.directory', 'database-backups'), '\\/'));

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        /** @phpstan-ignore-next-line */
        return realpath($dir) ?: throw new RuntimeException('Cannot resolve backup directory.');
    }

    /**
     * @return non-empty-string Filename only (basename)
     */
    public function create(): string
    {
        $driver = Config::get('database.default');
        $connection = Config::get("database.connections.{$driver}");

        if (! is_array($connection)) {
            throw new RuntimeException('Database configuration is invalid.');
        }

        $logicalDriver = (string) ($connection['driver'] ?? '');

        $timestamp = now()->format('Y-m-d-His');
        $safeDriver = preg_replace('/[^a-z]/', '', Str::slug($logicalDriver, '')) ?: 'db';

        $filename = match ($logicalDriver) {
            'sqlite' => "backup-{$safeDriver}-{$timestamp}.sqlite",
            'mysql', 'mariadb', 'pgsql' => "backup-{$safeDriver}-{$timestamp}.sql",
            default => throw new RuntimeException("Automatic backup is not supported for driver [{$logicalDriver}]."),
        };

        $path = $this->storageDirectory().DIRECTORY_SEPARATOR.$filename;

        match ($logicalDriver) {
            'sqlite' => $this->backupSqlite($connection, $path),
            'mysql', 'mariadb' => $this->backupMysqlCompatible($logicalDriver, $connection, $path),
            'pgsql' => $this->backupPostgres($connection, $path),
            default => null,
        };

        return $filename;
    }

    /**
     * @return list<array{filename: non-empty-string, size: int, modified: int}>
     */
    public function listBackups(): array
    {
        $dir = $this->storageDirectory();
        $out = [];

        foreach (File::files($dir) as $file) {
            /** @phpstan-ignore-next-line */
            $name = $file->getFilename();
            if (! $this->isAllowedFilename($name)) {
                continue;
            }

            $out[] = [
                'filename' => $name,
                'size' => $file->getSize(),
                'modified' => $file->getMTime(),
            ];
        }

        usort($out, fn (array $a, array $b): int => $b['modified'] <=> $a['modified']);

        return $out;
    }

    public function pruneOlderThan(int $days): int
    {
        if ($days < 1) {
            throw new InvalidArgumentException('Retention days must be at least 1.');
        }

        $cutoff = now()->subDays($days)->getTimestamp();
        $deleted = 0;

        foreach (File::files($this->storageDirectory()) as $file) {
            $name = $file->getFilename();
            if (! $this->isAllowedFilename($name)) {
                continue;
            }

            if ($file->getMTime() < $cutoff) {
                File::delete($file->getRealPath());
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * @return non-falsy-string
     */
    public function absolutePathForDownload(string $filename): string
    {
        $filename = basename($filename);

        if (! $this->isAllowedFilename($filename)) {
            throw new InvalidArgumentException('Invalid backup filename.');
        }

        $path = $this->storageDirectory().DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($path)) {
            abort(404, 'Backup not found.');
        }

        /** @phpstan-ignore-next-line */
        return $path;
    }

    /**
     * @phpstan-param array<string, mixed> $connection
     */
    protected function backupSqlite(array $connection, string $destPath): void
    {
        $dbPath = $connection['database'] ?? '';

        if (! is_string($dbPath) || $dbPath === '' || $dbPath === ':memory:') {
            throw new RuntimeException('Cannot backup an in-memory or empty SQLite path.');
        }

        $resolved = Str::startsWith($dbPath, ['/']) || preg_match('#^[a-zA-Z]:[\\\\/]#', $dbPath) === 1
            ? $dbPath
            : database_path($dbPath);

        if (! File::exists($resolved)) {
            throw new RuntimeException('SQLite database file does not exist: '.$resolved);
        }

        if (! File::copy($resolved, $destPath)) {
            throw new RuntimeException('Failed to copy SQLite database.');
        }
    }

    /**
     * @phpstan-param array<string, mixed> $connection
     */
    protected function backupMysqlCompatible(string $logicalDriver, array $connection, string $destPath): void
    {
        $binary = $this->resolveMysqldumpBinary();

        $host = $connection['host'] ?? config('database.connections.mysql.host');
        $port = (string) ($connection['port'] ?? config('database.connections.mysql.port', '3306'));
        $user = $connection['username'] ?? '';
        $password = (string) ($connection['password'] ?? '');
        $database = $connection['database'] ?? '';

        foreach (['database' => $database, 'username' => $user] as $key => $value) {
            if ($value === '' || $value === null) {
                throw new RuntimeException('Database connection is missing '.$key.'.');
            }
        }

        $command = array_filter([
            $binary,
            '--single-transaction',
            '--routines',
            '--no-tablespaces',
            '-h'.$host,
            '-P'.$port,
            '-u'.$user,
            $database,
        ]);

        try {
            $process = new Process($command);
            $process->setTimeout(3600);
            $process->mustRun(null, ['MYSQL_PWD' => $password]);

            File::put($destPath, $process->getOutput());
        } catch (ProcessFailedException $e) {
            $failure = trim($e->getProcess()->getErrorOutput() ?: $e->getProcess()->getOutput() ?: '');
            $detail = $failure !== '' ? ' '.Str::take($failure, 400) : '';

            throw new RuntimeException("mysqldump failed for [{$logicalDriver}] using binary [{$binary}].".$detail.
            ' Install the MySQL client, add it to PATH, or set DATABASE_BACKUP_MYSQLDUMP_PATH fully (e.g. Laragon: ...\\mysql\\*-winx64\\bin\\mysqldump.exe).', $e->getCode(), previous: $e);
        }
    }

    /**
     * @return non-empty-string
     */
    protected function resolveMysqldumpBinary(): string
    {
        $configured = trim((string) Config::get('database_backup.mysqldump_path', 'mysqldump'));

        if ($configured !== 'mysqldump' &&
            ($configured !== '' && (Str::contains($configured, ['/']) || Str::contains($configured, '\\') || preg_match('#^[a-zA-Z]:[\\\\/]#', $configured) === 1))
        ) {
            return $configured;
        }

        $finder = new ExecutableFinder;

        foreach (['mysqldump.exe', 'mysqldump'] as $name) {
            $found = $finder->find($name);
            if (! in_array($found, [null, false, ''], true)) {
                /** @phpstan-ignore-next-line */
                return str_replace('\\', DIRECTORY_SEPARATOR, $found);
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return $this->guessLaragonMysqldumpPath() ?? 'mysqldump';
        }

        return 'mysqldump';
    }

    protected function guessLaragonMysqldumpPath(): ?string
    {
        $laragonGuess = dirname(base_path(), 2).DIRECTORY_SEPARATOR.'bin'.
            DIRECTORY_SEPARATOR.'mysql'.DIRECTORY_SEPARATOR.'*'.
            DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysqldump.exe';

        foreach (glob($laragonGuess) ?: [] as $path) {
            if (File::exists($path)) {
                /** @phpstan-ignore-next-line */
                return $path;
            }
        }

        return null;
    }

    /**
     * @phpstan-param array<string, mixed> $connection
     */
    protected function backupPostgres(array $connection, string $destPath): void
    {
        $binary = Config::get('database_backup.pg_dump_path', 'pg_dump');

        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '5432');
        $user = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');
        $database = (string) ($connection['database'] ?? '');

        if ($database === '' || $user === '') {
            throw new RuntimeException('PostgreSQL backup requires username and database name.');
        }

        $command = [$binary, '-h', $host, '-p', $port, '-U', $user, '--clean', '--if-exists', $database];

        try {
            $process = new Process($command);
            $process->setTimeout(3600);
            $process->mustRun(null, ['PGPASSWORD' => $password]);

            File::put($destPath, $process->getOutput());

            if (File::size($destPath) === 0) {
                File::delete($destPath);

                throw new RuntimeException('pg_dump produced empty output.');
            }
        } catch (ProcessFailedException $e) {
            if (File::exists($destPath)) {
                File::delete($destPath);
            }

            throw new RuntimeException('pg_dump failed. Ensure PostgreSQL client tools are installed and DATABASE_BACKUP_PG_DUMP_PATH is correct.', $e->getCode(), previous: $e);
        }
    }

    public function isAllowedFilename(string $filename): bool
    {
        return (bool) preg_match('/^backup-(?:mysql|mariadb|pgsql|sqlite)-\d{4}-\d{2}-\d{2}-\d{6}\.(sql|sqlite)$/', $filename);
    }
}
