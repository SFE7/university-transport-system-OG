<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Trajet;
use Illuminate\Console\Command;

class MarkExpiredTrajetsCompleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trajets:complete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired trajets as completed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = 0;

        Trajet::query()
            ->where('status', 'active')
            ->where('departure_time', '<', now())
            ->chunkById(100, function ($trajets) use (&$count): void {
                foreach ($trajets as $trajet) {
                    $trajet->status = 'completed';
                    $trajet->save();
                    $count++;
                }
            });

        $this->info(sprintf('Marked %d expired trajets as completed.', $count));

        return self::SUCCESS;
    }
}
