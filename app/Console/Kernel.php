<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        try {

            /**
             *  Handle abandoned user carts
             */
            $schedule->call(function () {

                Log::info('Check for abandoned carts');

                //  Get the date and time an hour ago
                $abandoned_an_hour_ago = Carbon::now()->subHours(1)->format('Y-m-d H:i:s');

                //  Get user carts that were abandoned an hour or more ago (Oldest first)
                $abandoned_carts = \App\Cart::where('owner_type', 'user')
                                            ->where('abandoned_status', '0')
                                            ->where('updated_at', '<', $abandoned_an_hour_ago)->oldest();


                //  Get user carts that were abandoned an hour or more ago (Oldest first)
                $abandoned_carts->chunk(100, function ($chunked_abandoned_carts) {

                    //  Foreach company we retrieved from the query
                    foreach ($chunked_abandoned_carts as $abandoned_cart) {

                        //  Mark this resource as abandoned
                        $abandoned_cart->markResourceAsAbandoned();

                    }

                });

            })->name('track_abandoned_carts')->everyMinute()->withoutOverlapping();

        } catch (\Exception $e) {

        }

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
