<?php

namespace App\Http\Controllers\Public;

use App\Services\Public\ProductDiscoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductDiscoveryController
{
    public function __construct(
        protected ProductDiscoveryService $discoveryService
    ) {}

    public function search(Request $request): JsonResponse
    {
        $queryStr = $request->query('q', '');
        $data = $this->discoveryService->searchVariants($queryStr);

        return response()->json($data);
    }
}
