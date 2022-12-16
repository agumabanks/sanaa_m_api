<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Question;

class QuestionAnswered
{
    public $question;

    /**
     * Create a new event instance.
     *
     * @param Question $question
     */
    public function __construct(Question $question)
    {
        $this->question = $question;
    }

}
