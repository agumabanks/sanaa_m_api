<?php


namespace Marvel\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class ReviewCreateRequest extends FormRequest
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
            'order_id'              => ['required', 'exists:Marvel\Database\Models\Order,id'],
            'product_id'            => ['required', 'exists:Marvel\Database\Models\Product,id'],
            'variation_option_id'   => ['integer', 'exists:Marvel\Database\Models\Variation,id'],
            'comment'               => ['required', 'string'],
            'rating'                => ['required', 'integer', 'min:1', 'max:5'],
            'shop_id'               => ['required', 'exists:Marvel\Database\Models\Shop,id'],
            'photos'                => ['array'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        // TODO: Need to check from the request if it's coming from GraphQL API or not.
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
