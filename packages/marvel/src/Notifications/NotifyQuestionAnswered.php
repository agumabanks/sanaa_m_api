<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Product;
use Marvel\Database\Models\Question;

class NotifyQuestionAnswered extends Notification implements ShouldQueue
{
    use Queueable;

    public $question;

    /**
     * Create a new notification instance.
     *
     * @param Question $question
     * @return void
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = '';
        $product = Product::findOrFail($this->question->product_id);
        if( $product) {
            $url = config('shop.shop_url') . '/products/' . $product->slug;
        }

        return (new MailMessage)
            ->markdown('emails.question.answered', ['question' => $this->question, 'url' => $url, 'product' => $product]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
