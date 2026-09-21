<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function enregistrerMouvement(Product $product, string $type, int $quantite, string $motif, ?string $reference = null, ?string $commentaire = null): StockMovement
    {
        if ($quantite <= 0) {
            throw new InvalidArgumentException('La quantité doit être positive.');
        }

        return DB::transaction(function () use ($product, $type, $quantite, $motif, $reference, $commentaire) {
            // Verrouille la ligne produit pour éviter les accès concurrents
            $product = Product::whereKey($product->id)->lockForUpdate()->first();

            if ($type === 'sortie' && $product->stock < $quantite) {
                throw new InvalidArgumentException("Stock insuffisant (disponible : {$product->stock}).");
            }

            $product->stock += $type === 'entree' ? $quantite : -$quantite;
            $product->save();

            return StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $type,
                'quantite' => $quantite,
                'motif' => $motif,
                'reference' => $reference,
                'commentaire' => $commentaire,
            ]);
        });
    }
}