<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify all certificate templates have valid PDF files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verifying certificate templates...');
        $this->newLine();
        
        $templates = \App\Models\CertificateTemplate::all();
        $missing = 0;
        $ok = 0;
        
        foreach ($templates as $template) {
            $file = 'public/storage/' . str_replace('/storage/', '', $template->background_pdf);
            $exists = file_exists($file);
            
            if ($exists) {
                $this->line("✓ ID {$template->id}: {$template->name}");
                $ok++;
            } else {
                $this->error("✗ ID {$template->id}: {$template->name}");
                $this->line("  File: {$template->background_pdf}");
                $this->line("  Path: {$file}");
                $missing++;
            }
        }
        
        $this->newLine();
        $this->info("Summary:");
        $this->line("  OK: {$ok}");
        
        if ($missing > 0) {
            $this->error("  Missing: {$missing}");
            $this->newLine();
            $this->warn('Some templates have missing PDF files. Please re-upload them.');
            return 1;
        } else {
            $this->info("  Missing: 0");
            $this->newLine();
            $this->info('All templates are valid!');
            return 0;
        }
    }
}
