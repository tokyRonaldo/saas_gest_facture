<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMovementRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request)
    {
        $query = StockMovement::with(['product:id,nom,reference', 'user:id,name'])->latest();

        if ($productId = $request->query('product_id')) {
            $query->where('product_id', $productId);
        }

        return $query->paginate(15);
    }

    public function store(StoreStockMovementRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        try {
            $mouvement = $this->stockService->enregistrerMouvement(
                product: $product,
                type: $request->type,
                quantite: $request->quantite,
                motif: $request->motif,
                commentaire: $request->commentaire,
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($mouvement->load('product', 'user'), 201);
    }
}