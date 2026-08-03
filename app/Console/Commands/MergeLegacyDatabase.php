<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class MergeLegacyDatabase extends Command
{
    protected $signature = 'db:merge-legacy 
                            {--dry-run : Run without actually inserting data}
                            {--table= : Merge specific table only}';

    protected $description = 'Merge data from certificate_old database into certificate database';

    private $oldDb = 'certificate_old';
    private $newDb = 'certificate';
    private $stats = [];
    private $errors = [];

    // Tables to migrate in order (respecting foreign key dependencies)
    private $tablesToMigrate = [
        'users',
        'roles',
        'permissions',
        'role_user',
        'permission_role',
        'campaigns',
        'certificate_templates',
        'events',
        'participants',
        'certificates',
        'event_registrations',
        'attendance_sessions',
        'attendance_records',
        'attendances',
        'surveys',
        'survey_questions',
        'survey_responses',
        'helpdesk_tickets',
        'helpdesk_messages',
        'notifications',
        'fcm_tokens',
        'pwa_settings',
        'pwa_email_templates',
        'pwa_email_logs',
        'pwa_participants',
        'delivery_configs',
        'global_configs',
    ];

    public function handle()
    {
        $this->info("=================================================");
        $this->info("  Database Migration: certificate_old → certificate");
        $this->info("=================================================\n");

        if ($this->option('dry-run')) {
            $this->warn("🔍 DRY RUN MODE - No data will be inserted\n");
        }

        // Test connections
        if (!$this->testConnections()) {
            return 1;
        }

        $specificTable = $this->option('table');
        $tables = $specificTable ? [$specificTable] : $this->tablesToMigrate;

        $this->info("Starting migration...\n");

        DB::beginTransaction();

        try {
            foreach ($tables as $table) {
                $this->migrateTable($table);
            }

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn("\n✓ Dry run completed - no changes made");
            } else {
                DB::commit();
                $this->info("\n✓ Migration completed successfully!");
            }

            $this->displaySummary();

            return 0;

        } catch (Exception $e) {
            DB::rollBack();
            $this->error("\n✗ Migration failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            Log::error('Migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function testConnections()
    {
        $this->info("Testing database connections...");

        try {
            // Test old database
            $oldCount = DB::connection('mysql')->table($this->oldDb . '.users')->count();
            $this->info("✓ Connected to {$this->oldDb} ({$oldCount} users found)");

            // Test new database
            $newCount = DB::connection('mysql')->table($this->newDb . '.users')->count();
            $this->info("✓ Connected to {$this->newDb} ({$newCount} users found)");

            $this->newLine();
            return true;

        } catch (Exception $e) {
            $this->error("✗ Connection failed: " . $e->getMessage());
            $this->error("\nMake sure:");
            $this->error("1. MySQL is running");
            $this->error("2. Both 'certificate' and 'certificate_old' databases exist");
            $this->error("3. Database credentials in .env are correct");
            return false;
        }
    }

    private function migrateTable($table)
    {
        $this->info("Migrating table: {$table}");

        try {
            // Check if table exists in old database
            if (!$this->tableExists($this->oldDb, $table)) {
                $this->warn("  ⚠ Table {$table} does not exist in {$this->oldDb} - skipping");
                $this->stats[$table] = ['skipped' => true];
                return;
            }

            // Check if table exists in new database
            if (!$this->tableExists($this->newDb, $table)) {
                $this->warn("  ⚠ Table {$table} does not exist in {$this->newDb} - skipping");
                $this->stats[$table] = ['skipped' => true];
                return;
            }

            // Get data from old database
            $oldData = DB::connection('mysql')
                ->table($this->oldDb . '.' . $table)
                ->get();

            $totalRows = $oldData->count();
            $inserted = 0;
            $skipped = 0;
            $updated = 0;

            if ($totalRows === 0) {
                $this->info("  → No data to migrate");
                $this->stats[$table] = ['total' => 0, 'inserted' => 0, 'skipped' => 0, 'updated' => 0];
                return;
            }

            $this->info("  → Found {$totalRows} records");

            // Get primary key
            $primaryKey = $this->getPrimaryKey($table);

            // Progress bar
            $bar = $this->output->createProgressBar($totalRows);
            $bar->start();

            foreach ($oldData as $row) {
                $rowArray = (array) $row;

                // Check if record exists in new database
                $query = DB::connection('mysql')->table($this->newDb . '.' . $table);
                
                if ($primaryKey) {
                    $query->where($primaryKey, $rowArray[$primaryKey]);
                } else {
                    // If no primary key, use all columns to check
                    foreach ($rowArray as $key => $value) {
                        $query->where($key, $value);
                    }
                }

                $exists = $query->exists();

                if (!$this->option('dry-run')) {
                    if ($exists) {
                        // Update existing record
                        if ($primaryKey) {
                            DB::connection('mysql')
                                ->table($this->newDb . '.' . $table)
                                ->where($primaryKey, $rowArray[$primaryKey])
                                ->update($rowArray);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        // Insert new record
                        DB::connection('mysql')
                            ->table($this->newDb . '.' . $table)
                            ->insert($rowArray);
                        $inserted++;
                    }
                } else {
                    if ($exists) {
                        $skipped++;
                    } else {
                        $inserted++;
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $this->info("  ✓ Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}");

            $this->stats[$table] = [
                'total' => $totalRows,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped
            ];

        } catch (Exception $e) {
            $this->error("  ✗ Failed: " . $e->getMessage());
            $this->errors[$table] = $e->getMessage();
            $this->stats[$table] = ['error' => $e->getMessage()];
        }

        $this->newLine();
    }

    private function tableExists($database, $table)
    {
        try {
            $result = DB::connection('mysql')
                ->select("SHOW TABLES FROM `{$database}` LIKE '{$table}'");
            return count($result) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getPrimaryKey($table)
    {
        try {
            $result = DB::connection('mysql')
                ->select("SHOW KEYS FROM `{$this->newDb}`.`{$table}` WHERE Key_name = 'PRIMARY'");
            
            if (count($result) > 0) {
                return $result[0]->Column_name;
            }
            return null;
        } catch (Exception $e) {
            return 'id'; // Default to 'id' if query fails
        }
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info("=================================================");
        $this->info("  MIGRATION SUMMARY");
        $this->info("=================================================\n");

        $totalInserted = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        $headers = ['Table', 'Total', 'Inserted', 'Updated', 'Skipped', 'Status'];
        $rows = [];

        foreach ($this->stats as $table => $stat) {
            if (isset($stat['skipped']) && $stat['skipped']) {
                $rows[] = [$table, '-', '-', '-', '-', '⚠ Skipped'];
                continue;
            }

            if (isset($stat['error'])) {
                $rows[] = [$table, '-', '-', '-', '-', '✗ Error'];
                $totalErrors++;
                continue;
            }

            // Only add to totals and display if there's actual data
            if (isset($stat['total'])) {
                $totalInserted += $stat['inserted'];
                $totalUpdated += $stat['updated'];
                $totalSkipped += $stat['skipped'];

                $status = $stat['inserted'] > 0 || $stat['updated'] > 0 ? '✓ Success' : '→ No changes';
                $rows[] = [
                    $table,
                    $stat['total'],
                    $stat['inserted'],
                    $stat['updated'],
                    $stat['skipped'],
                    $status
                ];
            }
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info("Total Records Inserted: {$totalInserted}");
        $this->info("Total Records Updated: {$totalUpdated}");
        $this->info("Total Records Skipped: {$totalSkipped}");
        
        if ($totalErrors > 0) {
            $this->error("Total Errors: {$totalErrors}");
            $this->newLine();
            $this->error("Errors:");
            foreach ($this->errors as $table => $error) {
                $this->error("  - {$table}: {$error}");
            }
        }

        // Save log to file
        $logFile = storage_path('logs/database-migration-' . date('Y-m-d-His') . '.log');
        file_put_contents($logFile, json_encode([
            'timestamp' => now(),
            'dry_run' => $this->option('dry-run'),
            'stats' => $this->stats,
            'errors' => $this->errors,
            'summary' => [
                'inserted' => $totalInserted,
                'updated' => $totalUpdated,
                'skipped' => $totalSkipped,
                'errors' => $totalErrors
            ]
        ], JSON_PRETTY_PRINT));

        $this->info("\nLog saved to: {$logFile}");
    }
}
