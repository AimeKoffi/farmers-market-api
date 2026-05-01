<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreFarmerRequest;
use App\Http\Resources\DebtResource;
use App\Http\Resources\FarmerResource;
use App\Models\Farmer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => FarmerResource::collection(Farmer::all()),
        ]);
    }

    public function store(StoreFarmerRequest $request): JsonResponse
    {
        $farmer = Farmer::create($request->validated());
        return response()->json(['success' => true, 'data' => new FarmerResource($farmer)], 201);
    }

    public function show(Farmer $farmer): JsonResponse
    {
        return response()->json(['success' => true, 'data' => new FarmerResource($farmer)]);
    }

    // Recherche par identifiant ou téléphone
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);

        $farmer = Farmer::where('identifier', $request->q)
            ->orWhere('phone', $request->q)
            ->first();

        if (!$farmer) {
            return response()->json(['success' => false, 'message' => 'Agriculteur non trouvé.'], 404);
        }

        return response()->json(['success' => true, 'data' => new FarmerResource($farmer)]);
    }

    // Résumé des dettes ouvertes d'un agriculteur
    public function debts(Farmer $farmer): JsonResponse
    {
        $debts = $farmer->openDebts()->with('transaction')->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'farmer'      => new FarmerResource($farmer),
                'total_debt'  => $farmer->total_debt,
                'open_debts'  => DebtResource::collection($debts),
            ],
        ]);
    }
}