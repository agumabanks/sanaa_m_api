<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbusiveReport extends Model
{
    protected $table = 'abusive_reports';

    public $guarded = [];

    /**
     * @return belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the models that own abusive reports.
     */
    public function model()
    {
        return $this->morphTo();
    }
}
