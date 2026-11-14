<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur backup database hanya dapat diakses oleh Administrator Logistik.');
        }

        AccessLog::record(
            'system',
            "Administrator {$user->name} mengunduh berkas cadangan database (Full SQL Dump)",
            $user,
            $request
        );

        $databaseName = DB::connection()->getDatabaseName();
        $filename = 'backup_logistik_toc_' . $databaseName . '_' . date('Y-m-d_His') . '.sql';

        return response()->streamDownload(function () use ($databaseName) {
            $pdo = DB::connection()->getPdo();

            echo "-- ====================================================================\n";
            echo "-- LOGISTIK.TOC DATABASE BACKUP (FULL SQL DUMP)\n";
            echo "-- Database: {$databaseName}\n";
            echo "-- Tanggal Dibuat: " . date('Y-m-d H:i:s') . " WIB\n";
            echo "-- Generator: LogiScan Pro Auto-Backup Engine (PHP PDO)\n";
            echo "-- ====================================================================\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n";
            echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            echo "SET time_zone = \"+07:00\";\n\n";

            // Ambil daftar seluruh tabel di database
            $tables = [];
            $stmt = $pdo->query('SHOW TABLES');
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            foreach ($tables as $table) {
                echo "-- --------------------------------------------------------------------\n";
                echo "-- Struktur Tabel untuk `{$table}`\n";
                echo "-- --------------------------------------------------------------------\n";
                echo "DROP TABLE IF EXISTS `{$table}`;\n";

                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $createRow = $createStmt->fetch(\PDO::FETCH_NUM);
                if (!empty($createRow[1])) {
                    echo $createRow[1] . ";\n\n";
                }

                echo "-- Data untuk Tabel `{$table}`\n";
                $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
                $columnCount = $dataStmt->columnCount();

                $batch = [];
                $count = 0;
                while ($row = $dataStmt->fetch(\PDO::FETCH_NUM)) {
                    $values = [];
                    for ($i = 0; $i < $columnCount; $i++) {
                        if ($row[$i] === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote($row[$i]);
                        }
                    }
                    $batch[] = '(' . implode(', ', $values) . ')';
                    $count++;

                    if (count($batch) >= 100) {
                        echo "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $batch) . ";\n";
                        $batch = [];
                    }
                }

                if (!empty($batch)) {
                    echo "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $batch) . ";\n";
                }

                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
            echo "-- SELESAI. Total " . count($tables) . " tabel berhasil dicadangkan dengan aman.\n";
        }, $filename, [
            'Content-Type' => 'application/sql; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
