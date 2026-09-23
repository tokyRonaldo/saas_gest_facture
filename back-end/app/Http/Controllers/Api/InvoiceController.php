<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}


    public function index(Request $request)
    {
        $query = Invoice::with('client:id,nom');

        // Recherche par numéro ou nom client
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('nom', 'like', "%{$search}%"));
            });
        }

        // Filtre par statut
        if ($statut = $request->query('statut')) {
            if ($statut === 'en_retard') {
                $query->where('statut', 'envoyee')->where('date_echeance', '<', now());
            } else {
                $query->where('statut', $statut);
            }
        }

        // Filtre par période d'émission
        if ($dateDebut = $request->query('date_debut')) {
            $query->whereDate('date_emission', '>=', $dateDebut);
        }
        if ($dateFin = $request->query('date_fin')) {
            $query->whereDate('date_emission', '<=', $dateFin);
        }

        return $query->latest()->paginate(10)
            ->through(fn ($invoice) => $invoice->append(['montant_paye', 'reste_a_payer', 'partiellement_payee', 'en_retard']));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoiceService->creer($request->validated());

        return response()->json($invoice, 201);
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice)
    {
        try {
            $invoice = $this->invoiceService->mettreAJour($invoice, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($invoice);
    }

    public function envoyer(Invoice $invoice)
    {
        try {
            $invoice = $this->invoiceService->envoyer($invoice);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($invoice);
    }

    public function annuler(Invoice $invoice)
    {
        try {
            $invoice = $this->invoiceService->annuler($invoice);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($invoice);
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->statut !== 'brouillon') {
            return response()->json(['message' => 'Seul un brouillon peut être supprimé.'], 422);
        }

        $invoice->delete();

        return response()->json(null, 204);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('items.product', 'client', 'creator', 'payments.user');

        return response()->json($invoice->append(['montant_paye', 'reste_a_payer', 'partiellement_payee', 'en_retard']));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('items.product', 'client', 'creator');

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return $pdf->download("{$invoice->numero}.pdf");
    }
}