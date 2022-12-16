<?php


namespace Marvel\Database\Repositories;

use Marvel\Database\Models\DeliveryTime;

class DeliveryTimeRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return DeliveryTime::class;
    }
}
