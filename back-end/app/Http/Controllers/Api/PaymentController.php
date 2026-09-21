<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Invoice $invoice)
    {
        return $invoice->payments()->with('user:id,name')->latest()->get();
    }

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        try {
            $payment = $this->paymentService->enregistrer($invoice, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($payment->load('user'), 201);
    }

    public function destroy(Payment $payment)
    {
        $this->paymentService->annuler($payment);

        return response()->json(null, 204);
    }
}