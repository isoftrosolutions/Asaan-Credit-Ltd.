<?php
/**
 * Production backup script.
 *
 * CLI:   php scripts/backup.php
 * HTTP:  include this from an admin page, then read $backupFile
 *
 * Creates a timestamped .zip in storage/backups/ containing:
 *   - Full MySQL dump (via mysqldump, falls back to PHP-based dump)
 *   - public/uploads/ directory
 *
 * Returns: array{success: bool, file?: string, size?: int, error?: string}
 */

function run_backup(bool $single = false): array
{
    $backupDir = __DIR__ . '/../storage/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $ts = date('Y-m-d_His');
    $tmpDir = $backupDir . '/.tmp_' . $ts;
    if (!mkdir($tmpDir, 0755, true)) {
        return ['success' => false, 'error' => 'Failed to create temp directory.'];
    }

    $sqlFile = $tmpDir . '/database.sql';
    $dumpOk = false;

    // Try mysqldump first
    $found = false;
    if (function_exists('exec')) {
        $mysqldump = PHP_OS_FAMILY === 'Windows' ? 'mysqldump.exe' : 'mysqldump';
        exec("where \"{$mysqldump}\" 2>NUL", $out, $code);
        $found = $code === 0;
        if (!$found && PHP_OS_FAMILY !== 'Windows') {
            exec("which {$mysqldump} 2>/dev/null", $out, $code);
            $found = $code === 0;
        }
    }

    if ($found) {
        $cmd = sprintf(
            '"%s" --single-transaction --routines --triggers --host=%s --port=%s --user=%s --password=%s %s > "%s" 2>NUL',
            $mysqldump,
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_PORT),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            $sqlFile
        );
        exec($cmd, $dumpOut, $dumpCode);
        $dumpOk = $dumpCode === 0 && filesize($sqlFile) > 100;
    }

    // Fallback: PHP-based table-by-table dump
    if (!$dumpOk) {
        try {
            $pdo = db();
            $dump = "# Backup: " . DB_NAME . " — " . date('Y-m-d H:i:s') . "\n#\n\n";
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
                if ($create) {
                    $dump .= $create[1] . ";\n\n";
                }

                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                if (empty($rows)) continue;

                $cols = array_keys($rows[0]);
                foreach ($rows as $row) {
                    $vals = [];
                    foreach ($cols as $col) {
                        $v = $row[$col] ?? null;
                        if ($v === null) {
                            $vals[] = 'NULL';
                        } else {
                            $vals[] = $pdo->quote($v);
                        }
                    }
                    $dump .= "INSERT INTO `{$table}` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $vals) . ");\n";
                }
                $dump .= "\n";
            }

            file_put_contents($sqlFile, $dump);
            $dumpOk = filesize($sqlFile) > 100;
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'PHP dump failed: ' . $e->getMessage()];
        }
    }

    if (!$dumpOk) {
        del_tree($tmpDir);
        return ['success' => false, 'error' => 'Database dump failed (both mysqldump and PHP fallback).'];
    }

    // Copy uploads (exclude .gitkeep)
    $uploadsSrc = __DIR__ . '/../public/uploads';
    $uploadsDst = $tmpDir . '/uploads';
    if (is_dir($uploadsSrc)) {
        mkdir($uploadsDst, 0755, true);
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsSrc, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($items as $item) {
            $rel = substr($item->getPathname(), strlen($uploadsSrc));
            if (basename($rel) === '.gitkeep') continue;
            $dest = $uploadsDst . $rel;
            if ($item->isDir()) {
                mkdir($dest, 0755, true);
            } else {
                if (is_readable($item->getPathname())) {
                    copy($item->getPathname(), $dest);
                }
            }
        }
    }

    // Zip everything
    $zipName = $single ? 'backup_latest.zip' : 'backup_' . $ts . '.zip';
    $zipFile = $backupDir . '/' . $zipName;
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
        del_tree($tmpDir);
        return ['success' => false, 'error' => 'Failed to create zip archive.'];
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $file) {
        $localPath = substr($file->getPathname(), strlen($tmpDir) + 1);
        $zip->addFile($file->getPathname(), $localPath);
    }
    $zip->close();

    // Cleanup temp
    del_tree($tmpDir);

    // Prune backups older than 30 days
    $prune = strtotime('-30 days');
    foreach (glob($backupDir . '/backup_*.zip') as $old) {
        if (filemtime($old) < $prune) {
            unlink($old);
        }
    }

    $size = filesize($zipFile);
    return [
        'success' => true,
        'file' => $zipName,
        'size' => $size,
        'path' => $zipFile,
    ];
}

function del_tree(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($dir);
}

// CLI mode
if (PHP_SAPI === 'cli' && !defined('BOOTSTRAP_LOADED')) {
    require __DIR__ . '/../config/bootstrap.php';
    $single = in_array('--single', $argv ?? []);
    $result = run_backup($single);
    if ($result['success']) {
        echo "Backup created: {$result['file']} (" . number_format($result['size'] / 1048576, 2) . " MB)\n";
        exit(0);
    }
    echo "ERROR: {$result['error']}\n";
    exit(1);
}
