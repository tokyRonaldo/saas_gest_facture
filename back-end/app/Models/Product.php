<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'reference', 'description', 'prix_ht', 'tva', 'unite', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'prix_ht' => 'decimal:2',
            'tva' => 'decimal:2',
        ];
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}