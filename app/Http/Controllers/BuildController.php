<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BuildCostCalculator;

class BuildController extends Controller
{
    protected BuildCostCalculator $calculator;

    public function __construct(BuildCostCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Handle the real-time build cost calculation endpoint.
     * Accepts POST or GET requests with a 'product_ids' array or comma-separated string.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateCost(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }

        if (!is_array($productIds)) {
            $productIds = [];
        }

        $productIds = array_values(array_filter(array_map('intval', $productIds)));

        $breakdown = $this->calculator->calculateFullBreakdown($productIds);

        return response()->json($breakdown);
    }
}
