<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Farmer;
use App\Models\Product;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $farmer = Farmer::findOrFail($request->farmer_id);

        // Enrichir chaque item avec le prix réel du produit en base
        $items = collect($request->items)->map(function ($item) {
            $product = Product::findOrFail($item['product_id']);
            return [
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'unit_price' => (float) $product->price_fcfa, // prix authorisé depuis la DB
            ];
        })->toArray();

        try {
            $transaction = $this->transactionService->createTransaction(
                $farmer,
                $request->user()->id,
                $items,
                $request->payment_method
            );

            return response()->json([
                'success' => true,
                'data'    => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    public function index(): JsonResponse
    {
        $transactions = request()->user()->isOperator()
            ? request()->user()->transactions()->with(['farmer', 'items.product'])->latest()->get()
            : \App\Models\Transaction::with(['farmer', 'operator', 'items.product'])->latest()->get();

        return response()->json([
            'success' => true,
            'data'    => TransactionResource::collection($transactions),
        ]);
    }

    public function show(\App\Models\Transaction $transaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new TransactionResource(
                $transaction->load(['farmer', 'operator', 'items.product', 'debt'])
            ),
        ]);
    }
}