<?php


namespace Marvel\Database\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Attribute;
use Marvel\Database\Models\AttributeValue;
use Marvel\Exceptions\MarvelException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

class AttributeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name'        => 'like',
        'shop_id',
        'language',
    ];

    protected $dataArray = [
        'name',
        'slug',
        'shop_id',
        'language',
    ];

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Attribute::class;
    }

    public function storeAttribute($request)
    {
        $attribute = $this->create($request->only($this->dataArray));
        if (isset($request['values']) && count($request['values'])) {
            $attribute->values()->createMany($request['values']);
        }
        return $attribute;
    }

    public function updateAttribute($request, $attribute)
    {
        if (isset($request['values'])) {
            foreach ($attribute->values as $value) {
                $key = array_search($value->id, array_column($request['values'], 'id'));
                if (!$key && $key !== 0) {
                    AttributeValue::findOrFail($value->id)->delete();
                }
            }
            foreach ($request['values'] as $value) {
                if (isset($value['id'])) {
                    AttributeValue::findOrFail($value['id'])->update($value);
                } else {
                    $value['attribute_id'] = $attribute->id;
                    AttributeValue::create($value);
                }
            }
        }
        $attribute->update($request->only($this->dataArray));
        return $this->with('values')->findOrFail($attribute->id);
    }
}
