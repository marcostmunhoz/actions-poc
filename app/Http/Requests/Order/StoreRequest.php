<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * @return string[]
     */
    public function rules()
    {
        return [
            'products.*.id' => [
                'required',
                'exists:products,id',
            ],
            'products.*.quantity' => [
                'required',
                'min:1',
            ],
        ];
    }
}
