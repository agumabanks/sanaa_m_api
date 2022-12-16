<?php

namespace Marvel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Marvel\Enums\ProductType;

class ProductCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string'],
            'price'       => ['nullable', 'numeric'],
            'sale_price'  => ['nullable', 'lte:price'],
            'type_id'     => ['required', 'exists:Marvel\Database\Models\Type,id'],
            'shop_id'     => ['required', 'exists:Marvel\Database\Models\Shop,id'],
            'manufacturer_id'     => ['nullable', 'exists:Marvel\Database\Models\Manufacturer,id'],
            'author_id'     => ['nullable', 'exists:Marvel\Database\Models\Author,id'],
            'product_type' => ['required', Rule::in([ProductType::SIMPLE, ProductType::VARIABLE])],
            'categories'  => ['array'],
            'tags'        => ['array'],
            'language'     => ['nullable', 'string'],
            'dropoff_locations'        => ['array'],
            'pickup_locations'        => ['array'],
            'digital_file'       => ['array'],
            'variations'  => ['array'],
            'variation_options'  => ['array'],
            'quantity'    => ['nullable', 'integer'],
            'unit'        => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'sku'         => ['string'],
            'image'       => ['array'],
            'gallery'     => ['array'],
            'video'       => ['array'],
            'status'      => ['string', Rule::in(['publish', 'draft'])],
            'height'      => ['nullable', 'string'],
            'length'      => ['nullable', 'string'],
            'width'       => ['nullable', 'string'],
            'external_product_url'      => ['nullable', 'string'],
            'external_product_button_text'       => ['nullable', 'string'],
            'in_stock'    => ['boolean'],
            'is_taxable'  => ['boolean'],
            'is_digital'  => ['boolean'],
            'is_external'  => ['boolean'],
            'is_rental'  => ['boolean'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
