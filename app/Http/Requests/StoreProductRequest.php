<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string|min:3|max:120',
            'category' => 'required|in:electronics,clothing,food,otros',
            'price' => 'required|numeric|gt:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'image_url' => 'nullable|url',
        ];
    }
}
