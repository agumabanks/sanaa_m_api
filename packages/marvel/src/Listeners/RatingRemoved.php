<?php

namespace App\Listeners;

use App\Events\RefundApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Review;

class RatingRemoved
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
     * @param  RefundApproved  $event
     * @return void
     */
    public function handle(RefundApproved $event)
    {
        Review::where('user_id', $event->refund->customer_id)->where('order_id', $event->refund->order_id)->delete();
    }
}
