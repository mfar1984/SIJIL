<?php

/**
 * Merge certificate_old into the live certificate database.
 *
 * WHY THIS CANNOT SIMPLY COPY ROWS
 * --------------------------------
 * The two schemas share a lineage: `certificate` is a copy of `certificate_old` that
 * was then worked on while the old one kept being used. 349 of 349 shared participant
 * ids describe the same person, 4 of 4 users the same account. But because both sides
 * carried on inserting from the same auto-increment sequence, three ids now mean two
 * different things:
 *
 *   certificates.id 399          old CERT-20260801122918-A55AE0  new CERT-20260801152228-6FA259
 *   certificate_templates.id 26  old PERTANDINGAN MEWARNA A      new Sibu International RC ...
 *   pwa_participants.id 618      old hamdzi@sarawak.gov.my       new devtest@sijil.test
 *
 * So old ids cannot be preserved. Every row brought across is inserted with a fresh
 * id and every foreign key pointing at it is remapped through the tables built below.
 *
 * WHAT IS BROUGHT ACROSS
 * ----------------------
 *   certificate_templates  rows whose (name, created_by) is not already present
 *   events                 rows whose (name, user_id, start_date) is not present
 *   participants           rows whose (email, event) is not present
 *   certificates           rows whose certificate_number is not present
 *   pwa_participants       rows whose email is not present
 *   event_pwa_participant  links between the two, remapped
 *
 * WHAT IS DELIBERATELY NOT TOUCHED
 * --------------------------------
 *   activity_log, notifications, cache, personal_access_tokens, pwa_email_logs,
 *   fcm_tokens        history and session state; the live values are the current ones
 *   users             identical on both sides, nothing to add
 *   roles, permissions, role_has_permissions, model_has_roles
 *                     the live database is authoritative here. The old one predates
 *                     the permission rework, and copying it back would reintroduce the
 *                     hyphenated names that broke the Template Designer.
 *   global_configs, pwa_settings, delivery_configs, pwa_email_templates
 *                     one row each side, live values are current
 *
 * SAFETY
 * ------
 * Runs as a dry run unless --commit is passed. With --commit the whole thing is one
 * transaction: any error rolls back everything. Matching is on natural keys, so
 * running it twice does not duplicate anything.
 *
 *   php database/scripts/merge_old_database.php                 # report only
 *   php database/scripts/merge_old_database.php --commit        # write
 *   php database/scripts/merge_old_database.php --old=other_db  # different source
 *
 * BACK UP THE TARGET DATABASE FIRST. This script inserts rows; it never updates or
 * deletes, but a mistaken run is still far easier to undo from a dump.
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$argv = $_SERVER['argv'] ?? [];
$commit = in_array('--commit', $argv, true);
$oldName = 'certificate_old';

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--old=')) {
        $oldName = substr($arg, 6);
    }
}

Config::set('database.connections.old', array_merge(
    config('database.connections.mysql'),
    ['database' => $oldName]
));

$old = DB::connection('old');
$new = DB::connection('mysql');
$newName = config('database.connections.mysql.database');

echo str_repeat('=', 72) . "\n";
echo "  merge {$oldName}  ->  {$newName}\n";
echo '  mode: ' . ($commit ? 'COMMIT (writing)' : 'DRY RUN (nothing will be written)') . "\n";
echo str_repeat('=', 72) . "\n";

try {
    $old->getPdo();
} catch (Throwable $e) {
    echo "\nCannot reach {$oldName}: " . $e->getMessage() . "\n";
    exit(1);
}

/** Columns the target actually has, so old rows never carry a stale column. */
function targetColumns(string $table): array
{
    static $cache = [];

    return $cache[$table] ??= Schema::connection('mysql')->getColumnListing($table);
}

/**
 * Shape an old row for insertion: drop the primary key, drop anything the target
 * does not have, and apply the caller's remapped foreign keys.
 */
function shape(object $row, string $table, array $overrides = []): array
{
    $data = array_intersect_key((array) $row, array_flip(targetColumns($table)));

    unset($data['id']);

    return array_merge($data, $overrides);
}

$norm = fn ($v) => strtolower(trim((string) $v));

$report = [];
$maps = [
    'templates' => [],
    'events' => [],
    'participants' => [],
    'pwa' => [],
];

$run = function () use ($old, $new, $commit, $norm, &$report, &$maps) {

    /* ---------------------------------------------------------------- templates */

    $existing = $new->table('certificate_templates')->get(['id', 'name', 'created_by'])
        ->keyBy(fn ($t) => $norm($t->name) . '|' . $t->created_by);

    foreach ($old->table('certificate_templates')->orderBy('id')->get() as $row) {
        $key = $norm($row->name) . '|' . $row->created_by;

        if (isset($existing[$key])) {
            $maps['templates'][$row->id] = $existing[$key]->id;
            continue;
        }

        $newId = $commit
            ? $new->table('certificate_templates')->insertGetId(shape($row, 'certificate_templates'))
            : 'DRY-' . $row->id;

        $maps['templates'][$row->id] = $newId;
        $report['certificate_templates'][] = "{$row->name} (old id {$row->id} -> {$newId})";
    }

    /* ------------------------------------------------------------------- events */

    $existing = $new->table('events')->get(['id', 'name', 'user_id', 'start_date'])
        ->keyBy(fn ($e) => $norm($e->name) . '|' . $e->user_id . '|' . $e->start_date);

    foreach ($old->table('events')->orderBy('id')->get() as $row) {
        $key = $norm($row->name) . '|' . $row->user_id . '|' . $row->start_date;

        if (isset($existing[$key])) {
            $maps['events'][$row->id] = $existing[$key]->id;
            continue;
        }

        // certificate_template_id does not exist in the old schema, so it stays null
        // and the event simply has no default template attached.
        $newId = $commit
            ? $new->table('events')->insertGetId(shape($row, 'events'))
            : 'DRY-' . $row->id;

        $maps['events'][$row->id] = $newId;
        $report['events'][] = "{$row->name} (old id {$row->id} -> {$newId})";
    }

    /* ------------------------------------------------------------- participants */

    // Keyed on email within an event, which is how the app already treats a person.
    // Rows with no email fall back to the name.
    $existing = $new->table('participants')->get(['id', 'email', 'name', 'event_id'])
        ->keyBy(fn ($p) => ($norm($p->email) ?: 'name:' . $norm($p->name)) . '|' . $p->event_id);

    foreach ($old->table('participants')->orderBy('id')->get() as $row) {
        $targetEvent = $maps['events'][$row->event_id] ?? null;

        if ($targetEvent === null) {
            $report['skipped'][] = "participant {$row->id} ({$row->email}) - its event {$row->event_id} was not mapped";
            continue;
        }

        $key = ($norm($row->email) ?: 'name:' . $norm($row->name)) . '|' . $targetEvent;

        if (isset($existing[$key])) {
            $maps['participants'][$row->id] = $existing[$key]->id;
            continue;
        }

        $newId = $commit
            ? $new->table('participants')->insertGetId(shape($row, 'participants', [
                'event_id' => $targetEvent,
            ]))
            : 'DRY-' . $row->id;

        $maps['participants'][$row->id] = $newId;
        $report['participants'][] = "{$row->name} <{$row->email}> event {$row->event_id}->{$targetEvent} (old id {$row->id} -> {$newId})";
    }

    // related_participant_id points inside the same table, so it can only be fixed
    // once every participant has an id.
    if ($commit && in_array('related_participant_id', targetColumns('participants'), true)) {
        foreach ($old->table('participants')->whereNotNull('related_participant_id')->get() as $row) {
            $self = $maps['participants'][$row->id] ?? null;
            $related = $maps['participants'][$row->related_participant_id] ?? null;

            if ($self && $related && ! str_starts_with((string) $self, 'DRY-')) {
                $new->table('participants')->where('id', $self)
                    ->update(['related_participant_id' => $related]);
            }
        }
    }

    /* -------------------------------------------------------------- certificates */

    $existing = $new->table('certificates')->pluck('id', 'certificate_number');

    foreach ($old->table('certificates')->orderBy('id')->get() as $row) {
        if (isset($existing[$row->certificate_number])) {
            continue;
        }

        $targetEvent = $maps['events'][$row->event_id] ?? null;
        $targetParticipant = $maps['participants'][$row->participant_id] ?? null;

        if ($targetEvent === null || $targetParticipant === null) {
            $report['skipped'][] = "certificate {$row->certificate_number} - "
                . ($targetEvent === null ? "event {$row->event_id} unmapped" : "participant {$row->participant_id} unmapped");
            continue;
        }

        $overrides = [
            'event_id' => $targetEvent,
            'participant_id' => $targetParticipant,
        ];

        if (property_exists($row, 'template_id')) {
            $overrides['template_id'] = $maps['templates'][$row->template_id] ?? null;
        }

        $newId = $commit
            ? $new->table('certificates')->insertGetId(shape($row, 'certificates', $overrides))
            : 'DRY';

        $report['certificates'][] = "{$row->certificate_number} event {$row->event_id}->{$targetEvent}"
            . ($row->pdf_file ? " file={$row->pdf_file}" : ' NO FILE');
    }

    /* ---------------------------------------------------------- pwa participants */

    $existing = $new->table('pwa_participants')->get(['id', 'email'])
        ->keyBy(fn ($p) => $norm($p->email));

    foreach ($old->table('pwa_participants')->orderBy('id')->get() as $row) {
        $key = $norm($row->email);

        if (isset($existing[$key])) {
            $maps['pwa'][$row->id] = $existing[$key]->id;
            continue;
        }

        $overrides = [];

        // Usernames are unique; the old one may already be taken by a different person.
        if (in_array('username', targetColumns('pwa_participants'), true) && ! empty($row->username)) {
            $candidate = $row->username;
            $suffix = 1;

            while ($new->table('pwa_participants')->where('username', $candidate)->exists()) {
                $candidate = $row->username . $suffix++;
            }

            if ($candidate !== $row->username) {
                $report['renamed'][] = "pwa username {$row->username} -> {$candidate} (already taken)";
            }

            $overrides['username'] = $candidate;
        }

        if (property_exists($row, 'related_participant_id') && $row->related_participant_id) {
            $overrides['related_participant_id'] = $maps['participants'][$row->related_participant_id] ?? null;
        }

        $newId = $commit
            ? $new->table('pwa_participants')->insertGetId(shape($row, 'pwa_participants', $overrides))
            : 'DRY-' . $row->id;

        $maps['pwa'][$row->id] = $newId;
        $report['pwa_participants'][] = "{$row->name} <{$row->email}> (old id {$row->id} -> {$newId})";
    }

    /* ------------------------------------------------- event <-> pwa participant */

    if (Schema::connection('mysql')->hasTable('event_pwa_participant')) {
        foreach ($old->table('event_pwa_participant')->get() as $row) {
            $event = $maps['events'][$row->event_id] ?? null;
            $pwa = $maps['pwa'][$row->pwa_participant_id] ?? null;

            if (! $event || ! $pwa || str_starts_with((string) $event, 'DRY') || str_starts_with((string) $pwa, 'DRY')) {
                if (! $commit) {
                    $report['event_pwa_participant'][] = "event {$row->event_id} <-> pwa {$row->pwa_participant_id}";
                }
                continue;
            }

            $exists = $new->table('event_pwa_participant')
                ->where('event_id', $event)->where('pwa_participant_id', $pwa)->exists();

            if ($exists) {
                continue;
            }

            $new->table('event_pwa_participant')->insert(shape($row, 'event_pwa_participant', [
                'event_id' => $event,
                'pwa_participant_id' => $pwa,
            ]));

            $report['event_pwa_participant'][] = "event {$row->event_id}->{$event} <-> pwa {$row->pwa_participant_id}->{$pwa}";
        }
    }
};

$before = [];
$tablesWatched = ['certificate_templates', 'events', 'participants', 'certificates',
                  'pwa_participants', 'event_pwa_participant'];

foreach ($tablesWatched as $t) {
    $before[$t] = $new->table($t)->count();
}

if ($commit) {
    $new->transaction($run);
} else {
    $run();
}

/* ----------------------------------------------------------------------- report */

foreach (['certificate_templates', 'events', 'participants', 'certificates',
          'pwa_participants', 'event_pwa_participant', 'renamed', 'skipped'] as $section) {
    $rows = $report[$section] ?? [];

    echo "\n" . strtoupper(str_replace('_', ' ', $section)) . ': ' . count($rows) . "\n";

    foreach (array_slice($rows, 0, 12) as $line) {
        echo "    {$line}\n";
    }

    if (count($rows) > 12) {
        echo '    ... and ' . (count($rows) - 12) . " more\n";
    }
}

echo "\n" . str_repeat('-', 72) . "\n";
printf("  %-28s %10s %10s\n", 'table', 'before', 'after');

foreach ($tablesWatched as $t) {
    printf("  %-28s %10d %10d\n", $t, $before[$t], $new->table($t)->count());
}

/* --------------------------------------------------- certificate file check */

echo "\nCERTIFICATE FILES\n";

$missing = 0;
$present = 0;

foreach ($old->table('certificates')->whereNotNull('pdf_file')->pluck('pdf_file') as $file) {
    $rel = ltrim(str_replace('\\', '/', $file), '/');

    $found = is_file(storage_path('app/public/' . $rel))
        || is_file(__DIR__ . '/../../public/storage.pre-symlink-backup/' . $rel);

    $found ? $present++ : $missing++;
}

echo "  referenced by old rows: " . ($present + $missing) . "\n";
echo "  present on this machine: {$present}\n";
echo "  MISSING on this machine: {$missing}\n";

if ($missing > 0) {
    echo "\n  The rows can be merged without the files, but those certificates will\n";
    echo "  show as \"No file\" in the report and cannot be downloaded until the PDFs\n";
    echo "  are copied to storage/app/public. Run this on the server that holds them,\n";
    echo "  or copy them across first.\n";
}

echo "\n" . str_repeat('=', 72) . "\n";

if ($commit) {
    echo "  COMMITTED.\n";
} else {
    echo "  DRY RUN - nothing was written. Re-run with --commit to apply.\n";
}

echo str_repeat('=', 72) . "\n";
