<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function enregistrer(Invoice $invoice, array $data): Payment
    {
        if ($invoice->statut !== 'envoyee') {
            throw new InvalidArgumentException('Seule une facture envoyée peut recevoir un paiement.');
        }

        return DB::transaction(function () use ($invoice, $data) {
            // Verrouille la facture pour éviter deux paiements simultanés qui dépasseraient le total
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->first();

            $dejaPaye = $invoice->payments()->sum('montant');
            $resteAPayer = $invoice->total_ttc - $dejaPaye;

            if ($data['montant'] > $resteAPayer) {
                throw new InvalidArgumentException(
                    "Le montant dépasse le reste à payer ({$resteAPayer})."
                );
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
                'montant' => $data['montant'],
                'date_paiement' => $data['date_paiement'],
                'mode' => $data['mode'],
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
            ]);

            // Bascule automatique ENVOYEE → PAYEE si le total est atteint
            $totalPaye = $invoice->payments()->sum('montant');
            if ($totalPaye >= $invoice->total_ttc) {
                $invoice->update(['statut' => 'payee']);
            }

            return $payment;
        });
    }

    public function annuler(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $invoice = Invoice::whereKey($payment->invoice_id)->lockForUpdate()->first();

            $payment->delete();

            // Si la facture était PAYEE et qu'elle ne l'est plus après suppression, on la repasse à ENVOYEE
            $totalPaye = $invoice->payments()->sum('montant');
            if ($invoice->statut === 'payee' && $totalPaye < $invoice->total_ttc) {
                $invoice->update(['statut' => 'envoyee']);
            }
        });
    }
}