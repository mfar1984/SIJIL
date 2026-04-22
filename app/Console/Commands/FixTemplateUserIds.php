<?php

namespace App\Console\Commands;

use App\Models\CertificateTemplate;
use Illuminate\Console\Command;

class FixTemplateUserIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'templates:fix-user-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix certificate templates with NULL user_id by copying from created_by';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing certificate templates with NULL user_id...');

        // Find all templates where user_id is NULL but created_by is not NULL
        $templates = CertificateTemplate::whereNull('user_id')
            ->whereNotNull('created_by')
            ->get();

        if ($templates->isEmpty()) {
            $this->info('No templates found with NULL user_id.');
            return 0;
        }

        $this->info("Found {$templates->count()} templates to fix.");

        $fixed = 0;
        foreach ($templates as $template) {
            $template->user_id = $template->created_by;
            $template->save();
            $fixed++;
            
            $this->line("Fixed template ID {$template->id}: '{$template->name}' - set user_id to {$template->created_by}");
        }

        $this->info("Successfully fixed {$fixed} templates.");
        return 0;
    }
}
