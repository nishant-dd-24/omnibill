<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Pulse\Facades\Pulse;
use Modules\Subscription\Domain\Models\Subscription;

class RecordBusinessMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record business metrics like MRR and Active Subscriptions to Pulse';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $activeSubscriptions = Subscription::where('status', 'active')->with('items.price')->get();
        $activeCount = $activeSubscriptions->count();

        $mrr = 0;
        foreach ($activeSubscriptions as $subscription) {
            foreach ($subscription->items as $item) {
                if ($item->price) {
                    $amount = $item->price->amount;
                    if ($item->price->interval === 'year') {
                        $amount = intdiv($amount, 12);
                    }
                    $mrr += $amount * $item->quantity;
                }
            }
        }

        Pulse::record('mrr', 'total', $mrr)->sum();
        Pulse::record('active_subscriptions', 'total', $activeCount)->sum();

        $this->info("Recorded $activeCount active subscriptions and $mrr MRR.");
    }
}
