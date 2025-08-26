<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <-- añade esto

class Product extends Model
{
    use HasFactory;
    use HasUuids;                 // <-- genera UUID al crear

    public $incrementing = false; // <-- clave no auto-incremental
    protected $keyType = 'string';// <-- tipo de clave primaria

    protected $table = 'products';

    protected $fillable = [
        'code', 'name', 'category', 'price', 'stock', 'is_active', 'image_url',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Si quieres exponer "active" desde el modelo (además del Resource), deja esto;
    // si prefieres que solo lo haga el Resource, puedes quitar $appends y el accessor.
    protected $appends = ['active'];

    public function getActiveAttribute(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? false);
    }

    public function setActiveAttribute($value): void
    {
        $this->attributes['is_active'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
