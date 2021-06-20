<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PayRequest extends FormRequest
{
    /**
     * @return string[]
     */
    public function rules()
    {
        return [
            'card.number' => [
                'required',
            ],
            'card.cvv' => [
                'required',
            ],
            'card.expiration' => [
                'required',
                'date_format:Y-m',
            ],
        ];
    }
}
