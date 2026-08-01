<?php

namespace App\Console\Commands;

use App\Models\CertificateTemplate;
use App\Support\CertificateLayout;
use Illuminate\Console\Command;

/**
 * Rewrites text coordinates in existing templates so the corrected renderer
 * reproduces what the old, broken one drew.
 *
 * The old renderer:
 *   - dropped every text element 5 mm and then lifted it half a line, because
 *     it drew into a fixed 10 mm cell with the text centred vertically;
 *   - ignored x for centred text and centred on the whole page instead;
 *   - pinned right aligned text to the page edge instead of to x;
 *   - shifted left aligned text right by 1.5 mm of default cell padding.
 *
 * This command bakes those offsets into the stored coordinates once, so the
 * output stays the same while the designer finally shows the truth.
 */
class FixTemplatePositions extends Command
{
    protected $signature = 'certificates:fix-template-positions
                            {--dry-run : Show what would change without saving}
                            {--template= : Only process one template id}';

    protected $description = 'Convert legacy certificate template coordinates to the corrected layout model';

    public function handle(): int
    {
        $query = CertificateTemplate::withTrashed();

        if ($this->option('template')) {
            $query->whereKey($this->option('template'));
        }

        $templates = $query->get();

        if ($templates->isEmpty()) {
            $this->warn('No templates found.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;

        foreach ($templates as $template) {
            $data = $template->template_data;

            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            if (!is_array($data) || empty($data['elements']) || !is_array($data['elements'])) {
                $this->line("  <fg=gray>skip</>   #{$template->id} {$template->name} (no elements)");
                continue;
            }

            if (!empty($data['layout_version']) && $data['layout_version'] >= 2) {
                $this->line("  <fg=gray>skip</>   #{$template->id} {$template->name} (already converted)");
                continue;
            }

            $pageWidth = (float) ($data['width'] ?? 297);
            $moved = 0;

            $this->newLine();
            $this->line("  <options=bold>#{$template->id} {$template->name}</>");

            foreach ($data['elements'] as $index => $element) {
                if (($element['type'] ?? null) !== 'text') {
                    continue;
                }

                $before = ['x' => (float) ($element['x'] ?? 0), 'y' => (float) ($element['y'] ?? 0)];
                $after = CertificateLayout::migrateLegacyElement($element, $pageWidth);

                $data['elements'][$index] = $after;
                $moved++;

                $label = mb_substr((string) ($element['content'] ?? '(text)'), 0, 28);
                $align = CertificateLayout::normaliseAlign($element['textAlign'] ?? 'left');

                $this->line(sprintf(
                    '    %-30s %-6s x %6.1f -> %6.1f    y %6.1f -> %6.1f',
                    $label,
                    $align,
                    $before['x'],
                    $after['x'],
                    $before['y'],
                    $after['y']
                ));
            }

            if ($moved === 0) {
                $this->line('    no text elements to convert');
                continue;
            }

            $data['layout_version'] = 2;

            if (!$dryRun) {
                $template->template_data = $data;
                $template->saveQuietly();
            }

            $changed++;
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete. {$changed} template(s) would be updated.");
            $this->line('Run again without --dry-run to apply.');
        } else {
            $this->info("Updated {$changed} template(s).");
            $this->line('Open each template preview to confirm it still looks right.');
        }

        return self::SUCCESS;
    }
}
