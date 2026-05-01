<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreRepaymentRequest;
use App\Http\Resources\RepaymentResource;
use App\Models\Farmer;
use App\Services\RepaymentService;
use Illuminate\Http\JsonResponse;

class RepaymentController extends Controller
{
    public function __construct(private RepaymentService $repaymentService) {}

    public function store(StoreRepaymentRequest $request): JsonResponse
    {
        $farmer = Farmer::findOrFail($request->farmer_id);

        try {
            $repayment = $this->repaymentService->recordRepayment(
                $farmer,
                $request->user()->id,
                (float) $request->kg_received
            );

            return new \Illuminate\Http\JsonResponse([
                'success' => true,
                'data'    => new RepaymentResource($repayment),
            ], 201, [], JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}