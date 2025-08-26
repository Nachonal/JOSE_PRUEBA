<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('product'); // por el apiResource
        return [
            'code' => ['sometimes','string', Rule::unique('products','code')->ignore($id, 'id')],
            'name' => 'sometimes|string|min:3|max:120',
            'category' => 'sometimes|in:electronics,clothing,food,otros',
            'price' => 'sometimes|numeric|gt:0',
            'stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'image_url' => 'nullable|url',
        ];
    }
}
