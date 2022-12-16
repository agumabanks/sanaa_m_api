<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Marvel\Traits\Translation;

class Availability extends Model
{
    use Translation;

    protected $table = 'availabilities';

    public $guarded = [];
}
