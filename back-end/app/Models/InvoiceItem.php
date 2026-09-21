<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'product_id', 'quantite', 'prix_unitaire_ht', 'tva_taux'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalLigneAttribute(): float
    {
        $ht = $this->quantite * $this->prix_unitaire_ht;
        return $ht + ($ht * $this->tva_taux / 100);
    }
}