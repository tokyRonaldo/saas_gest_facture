<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function admin()
    {
        $moisActuel = now()->startOfMonth();
        $moisPrecedent = now()->subMonth()->startOfMonth();

        return response()->json([
            'chiffre_affaires' => $this->chiffreAffaires(),
            'chiffre_affaires_evolution' => $this->evolution(
                Payment::whereBetween('date_paiement', [$moisActuel, now()])->sum('montant'),
                Payment::whereBetween('date_paiement', [$moisPrecedent, $moisActuel])->sum('montant'),
            ),
            'factures_emises' => Invoice::where('statut', '!=', 'brouillon')->count(),
            'factures_emises_evolution' => $this->evolution(
                Invoice::where('statut', '!=', 'brouillon')->where('date_emission', '>=', $moisActuel)->count(),
                Invoice::where('statut', '!=', 'brouillon')->whereBetween('date_emission', [$moisPrecedent, $moisActuel])->count(),
            ),
            'paiements_pendants' => Invoice::where('statut', 'envoyee')
                ->get()
                ->sum(fn ($i) => $i->reste_a_payer),
            'clients_actifs' => Client::count(),

            'ca_mensuel' => $this->caParMois(),
            'factures_par_statut' => $this->facturesParStatut(),
            'top_clients' => $this->topClients(),
        ]);
    }

    public function simple()
    {
        $userId = Auth::id();

        $invoices = Invoice::where('user_id', $userId)->where('statut', '!=', 'brouillon')->get();

        return response()->json([
            'nombre_factures' => $invoices->count(),
            'montant_facture' => $invoices->sum('total_ttc'),
            'montant_paye' => $invoices->sum('montant_paye'),
            'montant_restant' => $invoices->sum('reste_a_payer'),
            'dernieres_factures' => Invoice::where('user_id', $userId)
                ->with('client:id,nom')
                ->latest()
                ->take(5)
                ->get(['id', 'numero', 'client_id', 'total_ttc', 'statut', 'created_at']),
        ]);
    }

    private function chiffreAffaires(): float
    {
        return Payment::sum('montant');
    }

    private function evolution($actuel, $precedent): ?float
    {
        if ($precedent == 0) return null;
        return round((($actuel - $precedent) / $precedent) * 100, 1);
    }

    private function caParMois(): array
    {
        return Payment::selectRaw("DATE_FORMAT(date_paiement, '%Y-%m') as mois, SUM(montant) as total")
            ->where('date_paiement', '>=', now()->subMonths(6))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(fn ($row) => ['mois' => $row->mois, 'total' => (float) $row->total])
            ->all(); // ← ajouté
    }

    private function facturesParStatut(): array
    {
        return Invoice::where('statut', '!=', 'brouillon')
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get()
            ->map(fn ($row) => ['statut' => $row->statut, 'total' => $row->total])
            ->all(); // ← ajouté
    }

    private function topClients(): array
    {
        return Invoice::where('statut', '!=', 'brouillon')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->selectRaw('clients.nom, SUM(invoices.total_ttc) as total')
            ->groupBy('clients.id', 'clients.nom')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn ($row) => ['nom' => $row->nom, 'total' => (float) $row->total])
            ->all(); // ← ajouté
    }
}