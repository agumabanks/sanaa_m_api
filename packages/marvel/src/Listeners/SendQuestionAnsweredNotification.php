<?php

namespace App\Listeners;

use App\Events\QuestionAnswered;
use App\Models\User;
use App\Notifications\NotifyQuestionAnswered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendQuestionAnsweredNotification
{
    /**
     * Handle the event.
     *
     * @param  QuestionAnswered  $event
     * @return void
     */
    public function handle(QuestionAnswered $event)
    {
        $customer = User::findOrFail($event->question->user_id);
        $customer->notify(new NotifyQuestionAnswered($event->question));
    }
}
