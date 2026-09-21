<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero', 'client_id', 'user_id', 'statut',
        'date_emission', 'date_echeance',
        'sous_total_ht', 'total_tva', 'total_ttc',
    ];

    protected function casts(): array
    {
        return [
            'date_emission' => 'date',
            'date_echeance' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // États dérivés — jamais stockés, toujours calculés
    public function getEnRetardAttribute(): bool
    {
        return $this->statut === 'envoyee' && $this->date_echeance->isPast();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getMontantPayeAttribute(): float
    {
        return $this->payments()->sum('montant');
    }

    public function getResteAPayerAttribute(): float
    {
        return max(0, $this->total_ttc - $this->montant_paye);
    }

    public function getPartiellementPayeeAttribute(): bool
    {
        return $this->statut === 'envoyee' && $this->montant_paye > 0 && $this->montant_paye < $this->total_ttc;
    }
}