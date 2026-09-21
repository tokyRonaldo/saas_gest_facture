<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceSequence;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    public function __construct(private StockService $stockService) {}

    public function creer(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $totaux = $this->calculerTotaux($data['items']);

            $invoice = Invoice::create([
                'client_id' => $data['client_id'],
                'user_id' => Auth::id(),
                'statut' => 'brouillon',
                'date_emission' => $data['date_emission'],
                'date_echeance' => $data['date_echeance'],
                ...$totaux,
            ]);

            $this->syncItems($invoice, $data['items']);

            return $invoice->load('items.product', 'client');
        });
    }

    public function mettreAJour(Invoice $invoice, array $data): Invoice
    {
        if ($invoice->statut !== 'brouillon') {
            throw new InvalidArgumentException('Seule une facture en brouillon peut être modifiée librement.');
        }

        return DB::transaction(function () use ($invoice, $data) {
            $totaux = $this->calculerTotaux($data['items']);

            $invoice->update([
                'client_id' => $data['client_id'],
                'date_emission' => $data['date_emission'],
                'date_echeance' => $data['date_echeance'],
                ...$totaux,
            ]);

            $invoice->items()->delete();
            $this->syncItems($invoice, $data['items']);

            return $invoice->load('items.product', 'client');
        });
    }

    public function envoyer(Invoice $invoice): Invoice
    {
        if ($invoice->statut !== 'brouillon') {
            throw new InvalidArgumentException('Seule une facture en brouillon peut être envoyée.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->numero = $this->genererNumero();
            $invoice->statut = 'envoyee';
            $invoice->save();

            // Sortie de stock automatique pour chaque ligne
            foreach ($invoice->items as $item) {
                $this->stockService->enregistrerMouvement(
                    product: $item->product,
                    type: 'sortie',
                    quantite: $item->quantite,
                    motif: 'vente',
                    reference: $invoice->numero,
                );
            }

            return $invoice;
        });
    }

    // InvoiceService.php
    public function annuler(Invoice $invoice): Invoice
    {
        if ($invoice->statut === 'payee') {
            throw new InvalidArgumentException('Une facture payée ne peut pas être annulée directement.');
        }

        return DB::transaction(function () use ($invoice) {
            // Si la facture avait déjà été envoyée, le stock avait été décrémenté → on le restitue
            if ($invoice->statut === 'envoyee') {
                foreach ($invoice->items as $item) {
                    $this->stockService->enregistrerMouvement(
                        product: $item->product,
                        type: 'entree',
                        quantite: $item->quantite,
                        motif: 'retour',
                        reference: $invoice->numero,
                        commentaire: 'Annulation facture ' . $invoice->numero,
                    );
                }
            }

            $invoice->update(['statut' => 'annulee']);

            return $invoice;
        });
    }

    private function calculerTotaux(array $items): array
    {
        $sousTotal = 0;
        $totalTva = 0;

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $ligneHt = $item['quantite'] * $product->prix_ht;
            $sousTotal += $ligneHt;
            $totalTva += $ligneHt * $product->tva / 100;
        }

        return [
            'sous_total_ht' => $sousTotal,
            'total_tva' => $totalTva,
            'total_ttc' => $sousTotal + $totalTva,
        ];
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            $invoice->items()->create([
                'product_id' => $product->id,
                'quantite' => $item['quantite'],
                'prix_unitaire_ht' => $product->prix_ht, // snapshot
                'tva_taux' => $product->tva,              // snapshot
            ]);
        }
    }

    private function genererNumero(): string
    {
        $annee = now()->year;

        $sequence = InvoiceSequence::where('annee', $annee)->lockForUpdate()->first()
            ?? InvoiceSequence::create(['annee' => $annee, 'dernier_numero' => 0]);

        $sequence->increment('dernier_numero');

        return sprintf('FAC-%d-%05d', $annee, $sequence->dernier_numero);
    }
}