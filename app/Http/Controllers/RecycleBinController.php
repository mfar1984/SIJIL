<?php

namespace App\Http\Controllers;

use App\Support\RecycleBin;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RecycleBinController extends Controller
{
    /**
     * Number of records shown per type inside the Recycle Bin tab.
     */
    private const PER_TYPE_LIMIT = 50;

    /**
     * Build the data used by the Recycle Bin tab on the Global Config page.
     *
     * @return array{types: array<int, array<string, mixed>>, total: int}
     */
    public static function payload(): array
    {
        $types = [];
        $total = 0;

        foreach (RecycleBin::types() as $slug => $type) {
            $query = RecycleBin::query($slug);

            if (!$query) {
                continue;
            }

            $count = (clone $query)->count();
            $total += $count;

            $items = $count > 0
                ? $query->orderByDesc('deleted_at')->limit(self::PER_TYPE_LIMIT)->get()
                : collect();

            $types[] = [
                'slug' => $slug,
                'label' => $type['label'],
                'plural' => $type['plural'],
                'icon' => $type['icon'],
                'count' => $count,
                'items' => $items->map(fn($model) => [
                    'id' => $model->getKey(),
                    'title' => RecycleBin::titleFor($slug, $model),
                    'subtitle' => RecycleBin::subtitleFor($slug, $model),
                    'deleted_at' => $model->deleted_at,
                ])->all(),
                'truncated' => $count > self::PER_TYPE_LIMIT,
            ];
        }

        return ['types' => $types, 'total' => $total];
    }

    /**
     * Restore a single soft-deleted record.
     */
    public function restore(Request $request, string $type, int $id)
    {
        $query = RecycleBin::query($type);

        if (!$query) {
            return back()->with('error', 'Unknown or inaccessible record type.');
        }

        $model = $query->find($id);

        if (!$model) {
            return back()->with('error', 'That record is no longer in the Recycle Bin.');
        }

        $label = RecycleBin::titleFor($type, $model);

        // Several of these tables carry unique indexes that ignore deleted_at
        // (users.email, pwa_participants.email, certificates.certificate_number,
        // events.registration_link). If the value was taken while the record sat
        // in the bin, restore() raises a query exception; a 500 page here would
        // read as data loss.
        try {
            $model->restore();
        } catch (QueryException $e) {
            return $this->backToBin()->with(
                'error',
                "Could not restore \"{$label}\": another record now uses one of its unique values. "
                . 'Change or remove that record first, then try again.'
            );
        }

        activity('recycle_bin')
            ->performedOn($model)
            ->withProperties(['type' => $type])
            ->log("Restored {$label} from Recycle Bin");

        return $this->backToBin()->with('success', "Restored: {$label}");
    }

    /**
     * Permanently remove a single record. This is the only place in the app
     * where data actually leaves the database.
     */
    public function destroy(Request $request, string $type, int $id)
    {
        $query = RecycleBin::query($type);

        if (!$query) {
            return back()->with('error', 'Unknown or inaccessible record type.');
        }

        $model = $query->find($id);

        if (!$model) {
            return back()->with('error', 'That record is no longer in the Recycle Bin.');
        }

        $label = RecycleBin::titleFor($type, $model);

        try {
            $this->deleteAttachedFiles($model);
            $model->forceDelete();
        } catch (QueryException $e) {
            // A restrictive foreign key elsewhere can refuse the delete. Say so
            // instead of letting the exception page imply the record is gone.
            return $this->backToBin()->with(
                'error',
                "Could not permanently delete \"{$label}\": other records still depend on it."
            );
        }

        activity('recycle_bin')
            ->withProperties(['type' => $type, 'record_id' => $id])
            ->log("Permanently deleted {$label}");

        return $this->backToBin()->with('success', "Permanently deleted: {$label}");
    }

    /**
     * Permanently remove every record of one type, or the whole bin.
     */
    public function empty(Request $request)
    {
        $request->validate([
            'type' => ['nullable', 'string', Rule::in(array_keys(RecycleBin::types()))],
        ]);

        $slugs = $request->filled('type')
            ? [$request->type]
            : array_keys(RecycleBin::types());

        $removed = 0;
        $failed = 0;

        foreach ($slugs as $slug) {
            $query = RecycleBin::query($slug);

            if (!$query) {
                continue;
            }

            foreach ($query->get() as $model) {
                try {
                    $this->deleteAttachedFiles($model);
                    $model->forceDelete();
                    $removed++;
                } catch (QueryException $e) {
                    // One blocked row must not abandon the rest of the sweep.
                    $failed++;
                }
            }
        }

        activity('recycle_bin')
            ->withProperties(['types' => $slugs, 'removed' => $removed, 'failed' => $failed])
            ->log("Emptied Recycle Bin ({$removed} records)");

        $message = "Permanently deleted {$removed} record(s).";

        if ($failed > 0) {
            $message .= " {$failed} could not be removed because other records still depend on them.";
        }

        return $this->backToBin()->with($failed > 0 ? 'warning' : 'success', $message);
    }

    /**
     * Send the user back to the Recycle Bin tab rather than to whichever tab
     * the Global Config page opens on by default.
     */
    private function backToBin(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->to(route('settings.global-config') . '#recycle-bin');
    }

    /**
     * Remove files that belong exclusively to a record being purged.
     */
    private function deleteAttachedFiles($model): void
    {
        // Every column across the registered models that holds a path on the
        // public disk. Anything missing here leaves an orphan file behind for
        // good, because the row that named it is gone.
        $fileColumns = ['pdf_file', 'background_pdf', 'preview_image', 'poster', 'profile_image'];

        foreach ($fileColumns as $column) {
            if (!isset($model->{$column}) || !is_string($model->{$column})) {
                continue;
            }

            $path = ltrim(str_replace('storage/', '', $model->{$column}), '/');

            if ($path !== '' && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
