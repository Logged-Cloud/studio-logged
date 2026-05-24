<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LoggedCloud\PageStudio\Models\Page;

class PruneDemoPages extends Command
{
    protected $signature = 'app:prune-demo-pages {--age=24 : Hours before a demo page is pruned}';

    protected $description = 'Delete demo pages older than the configured age so the public sandbox stays small.';

    public function handle(): int
    {
        $cutoff  = now()->subHours((int) $this->option('age'));
        $deleted = Page::where('updated_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} demo page row(s) older than {$this->option('age')}h.");

        return self::SUCCESS;
    }
}
