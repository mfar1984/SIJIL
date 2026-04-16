<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixTemplateUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:fix-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix certificate template URLs (convert full URLs to relative paths)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing certificate template URLs...');
        $this->newLine();
        
        $templates = \App\Models\CertificateTemplate::all();
        $fixed = 0;
        
        foreach ($templates as $template) {
            $bgPdf = $template->background_pdf;
            
            // Check if it's a full URL
            if (str_contains($bgPdf, 'http://') || str_contains($bgPdf, 'https://')) {
                // Extract path after /storage/
                if (preg_match('/\/storage\/(.+)$/', $bgPdf, $matches)) {
                    $newPath = '/storage/' . $matches[1];
                    
                    $this->line("ID {$template->id}: {$template->name}");
                    $this->line("  Old: {$bgPdf}");
                    $this->line("  New: {$newPath}");
                    
                    $template->background_pdf = $newPath;
                    $template->save();
                    
                    $fixed++;
                    $this->info('  ✓ Fixed');
                    $this->newLine();
                }
            }
        }
        
        if ($fixed > 0) {
            $this->info("Total templates fixed: {$fixed}");
        } else {
            $this->info('No templates needed fixing. All URLs are already correct.');
        }
        
        return 0;
    }
}
