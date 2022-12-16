<?php

namespace Marvel\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Marvel\Exceptions\MarvelException;

trait Translation
{

    /**
     * Get all translations for the model.
     *
     * @return string
     */
    public function getTranslatedLanguagesAttribute()
    {
        if($this->table === 'coupons'){
            $translatedProducts = $this->where('code', $this->code)->get();
            return $translatedProducts->pluck('language')->toArray();
        }

        $translatedProducts = $this->where('slug', $this->slug)->get();
        return $translatedProducts->pluck('language')->toArray();
    }


    public function getTranslations()
    {
        try {
            $translation =  DB::table('translations')->where('item_id', $this->model->id)->where('item_type', get_class($this))->first();
        } catch (\Throwable $th) {
            throw new MarvelException(NOT_FOUND);
        }

        if ($translation->language_code === DEFAULT_LANGUAGE) {
            return DB::table('translations')->where('translation_item_id', $translation->item_id)->where('item_type', get_class($this))->get();
        }
        return DB::table('translations')->where('translation_item_id', $translation->translation_item_id)->orWhere('item_id', $translation->translation_item_id)->where('item_type', get_class($this))->get();
    }

    public function storeTranslation($translation_item_id, $language_code, $source_language_code = DEFAULT_LANGUAGE)
    {
        $translation =  DB::table('translations')->where('item_id', $this->id)->where('item_type', get_class($this))->first();
        if (!$translation) {
            DB::table('translations')->insert([
                'item_id' => $this->id,
                'item_type' =>  get_class($this),
                'language_code' => $language_code,
                'source_language_code' => $source_language_code,
                'translation_item_id' => $translation_item_id
            ]);
        }
    }
}
