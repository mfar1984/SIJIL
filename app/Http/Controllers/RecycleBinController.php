<?php

namespace App\Http\Controllers;

use App\Support\RecycleBin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $model->restore();

        activity('recycle_bin')
            ->performedOn($model)
            ->withProperties(['type' => $type])
            ->log("Restored {$label} from Recycle Bin");

        return back()->with('success', "Restored: {$label}");
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

        $this->deleteAttachedFiles($model);
        $model->forceDelete();

        activity('recycle_bin')
            ->withProperties(['type' => $type, 'record_id' => $id])
            ->log("Permanently deleted {$label}");

        return back()->with('success', "Permanently deleted: {$label}");
    }

    /**
     * Permanently remove every record of one type, or the whole bin.
     */
    public function empty(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string',
        ]);

        $slugs = $request->filled('type')
            ? [$request->type]
            : array_keys(\App\Support\RecycleBin::types());

        $removed = 0;

        foreach ($slugs as $slug) {
            $query = RecycleBin::query($slug);

            if (!$query) {
                continue;
            }

            foreach ($query->get() as $model) {
                $this->deleteAttachedFiles($model);
                $model->forceDelete();
                $removed++;
            }
        }

        activity('recycle_bin')
            ->withProperties(['types' => $slugs, 'removed' => $removed])
            ->log("Emptied Recycle Bin ({$removed} records)");

        return back()->with('success', "Permanently deleted {$removed} record(s).");
    }

    /**
     * Remove files that belong exclusively to a record being purged.
     */
    private function deleteAttachedFiles($model): void
    {
        $fileColumns = ['pdf_file', 'background_pdf', 'preview_image', 'poster'];

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
