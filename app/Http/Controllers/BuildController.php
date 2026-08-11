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

    /**
     * Display the dynamic visual PC build summary generator page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showSummary(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        if (is_string($productIds)) {
            $productIds = array_filter(explode(',', $productIds));
        }
        $productIds = array_values(array_filter(array_map('intval', (array)$productIds)));

        // Fallback: If no product IDs provided, pick sample products
        if (empty($productIds)) {
            $productIds = \App\Models\Product::pluck('id')->take(4)->toArray();
        }

        $products = \App\Models\Product::with(['category', 'specifications'])->whereIn('id', $productIds)->get();

        // 1. Cost Breakdown & Percentages
        $costBreakdown = $this->calculator->calculateFullBreakdown($productIds);

        // 2. Compatibility & Bottleneck Checks
        $compatibilityEngine = app(\App\Services\CompatibilityEngine::class);
        $compatResult = $compatibilityEngine->checkCompatibility($productIds);
        $warningsResult = $compatibilityEngine->detectBottlenecksAndConflicts($productIds);

        // 3. System Power TDP
        $specExtractor = app(\App\Services\SpecExtractorService::class);
        $context = $specExtractor->getSystemSpecsContext($productIds);
        
        $totalTdp = 0;
        foreach ($context['components'] as $component) {
            $tdpStr = $component['specs']['tdp'] ?? null;
            if ($tdpStr) {
                preg_match('/(\d+)/', $tdpStr, $matches);
                if (!empty($matches[1])) {
                    $totalTdp += (int)$matches[1];
                }
            }
        }
        if ($totalTdp === 0) {
            $totalTdp = 350; // Default reasonable baseline estimate
        }
        $recommendedPsuWattage = (int)ceil($totalTdp * 1.25);

        return view('build.summary', [
            'products' => $products,
            'costBreakdown' => $costBreakdown,
            'compatResult' => $compatResult,
            'warningsResult' => $warningsResult,
            'totalTdp' => $totalTdp,
            'recommendedPsuWattage' => $recommendedPsuWattage,
        ]);
    }
}
