<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class RecommendationMapperService
{
    /**
     * Take the structured JSON from Gemini and map it to actual database products.
     * 
     * @param array $geminiData The JSON array returned by Gemini
     * @return array The original data augmented with actual product matches.
     */
    public function mapRecommendationsToProducts(array $geminiData): array
    {
        $mappedComponents = [];

        if (!isset($geminiData['components']) || !is_array($geminiData['components'])) {
            return ['explanation' => $geminiData['explanation'] ?? 'I could not generate a component list at this time.', 'components' => []];
        }

        foreach ($geminiData['components'] as $component) {
            $category = $component['category'] ?? '';
            $specs = strtolower($component['recommended_specs'] ?? '');
            $budget = $component['budget_allocation'] ?? 9999;

            // Simple heuristic mapper:
            // 1. Filter by category loosely matching the requested category
            // 2. Filter by price <= budget_allocation (allow 15% buffer)
            // 3. Must be in stock (stock_quantity > 0)
            
            $query = Product::with('category')
                ->where('stock_quantity', '>', 0)
                ->where('price', '<=', $budget * 1.15)
                ->whereHas('category', function($q) use ($category) {
                    $q->where('slug', 'like', '%' . strtolower($category) . '%')
                      ->orWhere('name', 'like', '%' . $category . '%');
                });

            // Extract potential keywords from specs (basic keyword extraction)
            $keywords = array_filter(explode(' ', str_replace([',', '.', '"', "'", '-', '/'], ' ', $specs)), function($word) {
                return strlen(trim($word)) > 2 && !in_array(trim($word), ['and', 'or', 'for', 'the', 'with', 'gb', 'tb', 'mhz']);
            });

            if (!empty($keywords)) {
                $query->where(function($q) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $q->orWhere('name', 'like', '%' . $keyword . '%')
                          ->orWhere('description', 'like', '%' . $keyword . '%');
                    }
                });
            }

            // Get the best match (closest to budget, or highest price under budget)
            $bestMatch = $query->orderBy('price', 'desc')->first();

            $component['matched_product'] = $bestMatch ? [
                'id' => $bestMatch->id,
                'name' => $bestMatch->name,
                'price' => '$' . number_format($bestMatch->price, 2),
                'image_path' => $bestMatch->image_path,
                'brand' => $bestMatch->brand,
                'match_confidence' => 'high'
            ] : null;

            $mappedComponents[] = $component;
        }

        return [
            'explanation' => $geminiData['explanation'] ?? '',
            'components' => $mappedComponents,
        ];
    }
}
