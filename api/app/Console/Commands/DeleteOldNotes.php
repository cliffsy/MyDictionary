<?php

namespace App\Console\Commands;

use App\Models\Favorite;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteOldNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notes older than 1 month from favorites table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        $deleted = Favorite::where('created_at', '<', $oneMonthAgo)
            ->forceDelete();

        $this->info("Deleted {$deleted} old notes from favorites table.");

        return Command::SUCCESS;
    }
}
