<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledContent;
use App\Models\Article;
use Illuminate\Support\Facades\Log;

class ExecuteScheduledContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:execute-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled content publishing/unpublishing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pending = ScheduledContent::where('status', 'pending')
                    ->where('scheduled_for', '<=', now())
                    ->get();

        foreach ($pending as $task) {
            try {
                $article = $task->article;
                
                if ($task->action === 'publish') {
                    $article->update([
                        'status' => 'published',
                        'published_at' => now()
                    ]);
                    $this->info("Published article ID: {$article->id}");
                } elseif ($task->action === 'unpublish') {
                    $article->update([
                        'status' => 'archived'
                    ]);
                    $this->info("Unpublished article ID: {$article->id}");
                }

                $task->update(['status' => 'completed', 'executed_at' => now()]);
            
            } catch (\Exception $e) {
                $task->update([
                    'status' => 'failed', 
                    'error_message' => $e->getMessage()
                ]);
                Log::error("Scheduled content failed: " . $e->getMessage());
            }
        }
    }
}
