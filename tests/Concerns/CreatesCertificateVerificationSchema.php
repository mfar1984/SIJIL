<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The four tables a public certificate check reads, and nothing else.
 *
 * The real migrations cannot run here: several use MySQL-only ALTER TABLE ... MODIFY
 * statements, so every test that relies on them fails on SQLite. Building the few
 * columns the verification lookup touches lets these tests run against an in-memory
 * database without a MySQL server and without any real certificate data.
 *
 * The rows are inserted through the query builder rather than the models, so no
 * model event fires: no activity log entry, no webhook and no generated number.
 */
trait CreatesCertificateVerificationSchema
{
    public const SAMPLE_NUMBER = 'CERT-20260313090000-A1B2C3';

    public const UNKNOWN_NUMBER = 'CERT-20260313090000-FFFFFF';

    public const TRASHED_NUMBER = 'CERT-20260313090000-DE1E7E';

    /**
     * Refuses to create or fill any table unless the default connection is an
     * in-memory SQLite database. Only the configuration is read, so no database
     * connection is opened: a real MySQL or MariaDB database, a SQLite file or a
     * DB_URL override can never receive these tables or the sample rows.
     */
    protected function assertInMemorySqliteConnection(): void
    {
        $name = (string) config('database.default');
        $connection = config("database.connections.{$name}");
        $driver = is_array($connection) ? ($connection['driver'] ?? null) : null;
        $database = is_array($connection) ? ($connection['database'] ?? null) : null;
        $url = is_array($connection) ? ($connection['url'] ?? null) : null;

        if ($driver !== 'sqlite' || $database !== ':memory:' || ($url !== null && $url !== '')) {
            throw new \RuntimeException(sprintf(
                'Refusing to create the certificate verification test schema: the default connection "%s" '
                . 'must be sqlite with database ":memory:" and no DB_URL, but it is driver "%s", database "%s"%s. '
                . 'Run these tests with DB_CONNECTION=sqlite DB_DATABASE=:memory: so they can never write into a real database.',
                $name,
                is_string($driver) ? $driver : 'none',
                is_string($database) ? $database : 'none',
                $url !== null && $url !== '' ? ' and a DB_URL' : ''
            ));
        }
    }

    protected function createVerificationSchema(): void
    {
        $this->assertInMemorySqliteConnection();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('start_time')->nullable();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('template_id');
            $table->string('certificate_number')->unique();
            $table->string('pdf_file')->nullable();
            $table->timestamp('generated_at');
            $table->unsignedBigInteger('generated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Fake sample data only. These names and numbers exist nowhere but in tests.
     */
    protected function seedSampleCertificates(): void
    {
        $this->assertInMemorySqliteConnection();

        $now = '2026-03-13 09:00:00';

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Example Training Unit',
            'email' => null,
            'password' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('participants')->insert([
            'id' => 1,
            'name' => 'Aisyah Rahman',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('events')->insert([
            'id' => 1,
            'name' => 'Digital Leadership Programme',
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-12',
            'start_time' => '09:00',
            'location' => 'Kuala Lumpur',
            'user_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('certificates')->insert([
            [
                'id' => 1,
                'event_id' => 1,
                'participant_id' => 1,
                'template_id' => 1,
                'certificate_number' => self::SAMPLE_NUMBER,
                'pdf_file' => null,
                'generated_at' => $now,
                'generated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'event_id' => 1,
                'participant_id' => 1,
                'template_id' => 1,
                'certificate_number' => self::TRASHED_NUMBER,
                'pdf_file' => null,
                'generated_at' => $now,
                'generated_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => $now,
            ],
        ]);
    }
}
