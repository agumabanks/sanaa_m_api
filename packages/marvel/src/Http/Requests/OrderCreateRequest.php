<?php

namespace Marvel\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Marvel\Enums\PaymentGatewayType;

class OrderCreateRequest extends FormRequest
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
            'status'           => 'required|exists:Marvel\Database\Models\OrderStatus,id',
            'coupon_id'        => 'nullable|exists:Marvel\Database\Models\Coupon,id',
            'shop_id'          => 'nullable|exists:Marvel\Database\Models\Shop,id',
            'customer_id'      => 'nullable|exists:Marvel\Database\Models\User,id',
            'language'         => ['nullable', 'string'],
            'amount'           => 'required|numeric',
            'paid_total'       => 'required|numeric',
            'total'            => 'required|numeric',
            'delivery_time'    => 'nullable|string',
            'customer_contact' => 'string|required',
            'payment_gateway'  => ['required', Rule::in(PaymentGatewayType::getValues())],
            'products'         => 'required|array',
            'card'             => 'array',
            'token'             => 'nullable|string',
            'use_wallet_points' => 'nullable|boolean',
            'shipping_address' => 'array',
            'billing_address'  => 'array',
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
