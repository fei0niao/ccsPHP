<?php

namespace App\Listeners;

use App\Events\KFChart;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class KFChartListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  KFChart  $event
     * @return void
     */
    public function handle(KFChart $event)
    {
        \Log::info('dddd');
    }
}
