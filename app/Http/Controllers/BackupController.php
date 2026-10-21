<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * One-click database backup for the Admin (maintenance plan).
 * Produces a .sql file that can be restored with phpMyAdmin → Import.
 */
class BackupController extends Controller
{
    public function index(): View
    {
        return view('backup.index', [
            'database' => DB::getDatabaseName(),
            'tables' => count($this->tables()),
            'lastBackup' => \App\Models\AuditLog::where('action', 'backup')->latest('id')->first(),
        ]);
    }

    public function download(): StreamedResponse
    {
        $name = DB::getDatabaseName();
        $file = $name.'_backup_'.now()->format('Ymd_His').'.sql';

        AuditLog::record('backup', 'Downloaded database backup '.$file);

        return response()->streamDownload(function () use ($name) {
            $pdo = DB::getPdo();

            echo "-- Elite Cafe RMS database backup\n";
            echo '-- Database: '.$name.' | Created: '.now()->format('Y-m-d H:i:s')."\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";

            foreach ($this->tables() as $table) {
                $create = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
                echo "DROP TABLE IF EXISTS `{$table}`;\n".array_values($create)[1].";\n\n";

                // Rows in chunks so large tables do not use too much memory
                DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($table, $pdo) {
                    foreach ($rows as $row) {
                        $values = array_map(
                            fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                            array_values((array) $row)
                        );
                        echo "INSERT INTO `{$table}` VALUES (".implode(', ', $values).");\n";
                    }
                });
                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $file, ['Content-Type' => 'application/sql']);
    }

    /** Names of all tables in the database. */
    private function tables(): array
    {
        return array_map(fn ($row) => array_values((array) $row)[0], DB::select('SHOW TABLES'));
    }
}
