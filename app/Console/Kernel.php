<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\SubscriptionVerifyJob;
use App\Models\Device;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(function () {
            $devices = Device::all();
            foreach ($devices as $device) {
                SubscriptionVerifyJob::dispatch($device);
            }
        })->everyMinute();

        $schedule->job(new UpdateExpiredSubscriptions())->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
