<?php

namespace App\Listeners;

use App\Events\ReviewCreated;
use App\Notifications\NewReviewCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Marvel\Database\Models\Shop;

class SendReviewNotification
{
    /**
     * Handle the event.
     *
     * @param  ReviewCreated  $event
     * @return void
     */
    public function handle(ReviewCreated $event)
    {
        $shop_id = $event->review->shop_id;
        $shop = Shop::with('owner')->findOrFail($shop_id);
        $shop_owner = $shop->owner;
        $shop_owner->notify(new NewReviewCreated($event->review));
    }
}
