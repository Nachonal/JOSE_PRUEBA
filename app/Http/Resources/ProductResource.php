<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,   // asegurar UUID como string
            'code'       => $this->code,
            'name'       => $this->name,
            'category'   => $this->category,
            'price'      => (float) $this->price,
            'stock'      => (int) $this->stock,
            'active'     => (bool) $this->active, // viene del accessor en el modelo
            'image_url'  => $this->image_url,
            'created_at' => optional($this->created_at)->toJSON(),
            'updated_at' => optional($this->updated_at)->toJSON(),
        ];
    }
}
