<?php


namespace Marvel\Database\Repositories;

use Marvel\Database\Models\Language;

class LanguageRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Language::class;
    }
}
