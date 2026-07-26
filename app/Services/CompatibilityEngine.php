<?php

namespace App\Services;

class CompatibilityEngine
{
    protected SpecExtractorService $specExtractor;

    public function __construct(SpecExtractorService $specExtractor)
    {
        $this->specExtractor = $specExtractor;
    }

    /**
     * Check compatibility of a given array of product IDs representing a system build.
     *
     * @param array $productIds
     * @return array ['is_compatible' => bool, 'incompatibilities' => array]
     */
    public function checkCompatibility(array $productIds): array
    {
        $context = $this->specExtractor->getSystemSpecsContext($productIds);
        $components = $context['components'];

        $isCompatible = true;
        $incompatibilities = [];

        // 1. CPU Socket == Motherboard Socket
        if (isset($components['cpu']) && isset($components['motherboard'])) {
            $cpuSocket = $components['cpu']['specs']['socket'] ?? null;
            $moboSocket = $components['motherboard']['specs']['socket'] ?? null;

            if ($cpuSocket && $moboSocket && strtolower($cpuSocket) !== strtolower($moboSocket)) {
                $isCompatible = false;
                $incompatibilities[] = "CPU Socket ({$cpuSocket}) does not match Motherboard Socket ({$moboSocket}).";
            }
        }

        // 2. RAM Type (DDR4 vs DDR5) == Motherboard RAM Support
        if (isset($components['ram']) && isset($components['motherboard'])) {
            $ramType = $components['ram']['specs']['ram_type'] ?? $components['ram']['specs']['type'] ?? null;
            $moboRamType = $components['motherboard']['specs']['memory_type'] ?? $components['motherboard']['specs']['ram_type'] ?? null;
            
            if ($ramType && $moboRamType && strtolower($ramType) !== strtolower($moboRamType)) {
                $isCompatible = false;
                $incompatibilities[] = "RAM Type ({$ramType}) does not match Motherboard supported RAM ({$moboRamType}).";
            }
        }

        // 3. Motherboard Form Factor fits in Case Form Factor
        if (isset($components['motherboard']) && isset($components['case'])) {
            $moboFormFactor = strtolower($components['motherboard']['specs']['form_factor'] ?? '');
            $caseFormFactors = strtolower($components['case']['specs']['supported_form_factors'] ?? $components['case']['specs']['form_factor'] ?? '');
            
            if ($moboFormFactor && $caseFormFactors) {
                if (!str_contains($caseFormFactors, $moboFormFactor)) {
                    $isCompatible = false;
                    $incompatibilities[] = "Motherboard Form Factor ({$moboFormFactor}) is not supported by Case Form Factors ({$caseFormFactors}).";
                }
            }
        }

        // 4. Total System TDP * 1.25 <= PSU Output Wattage
        $totalTdp = 0;
        foreach ($components as $slug => $component) {
            $tdpStr = $component['specs']['tdp'] ?? null;
            if ($tdpStr) {
                preg_match('/(\d+)/', $tdpStr, $matches);
                if (!empty($matches[1])) {
                    $totalTdp += (int)$matches[1];
                }
            }
        }

        if (isset($components['psu'])) {
            $psuWattageStr = $components['psu']['specs']['wattage'] ?? null;
            if ($psuWattageStr) {
                preg_match('/(\d+)/', $psuWattageStr, $matches);
                if (!empty($matches[1])) {
                    $psuWattage = (int)$matches[1];
                    $requiredWattage = $totalTdp * 1.25;

                    if ($requiredWattage > $psuWattage) {
                        $isCompatible = false;
                        $incompatibilities[] = "PSU Wattage ({$psuWattage}W) is lower than the recommended requirement (" . ceil($requiredWattage) . "W based on system TDP of {$totalTdp}W).";
                    }
                }
            }
        }

        return [
            'is_compatible' => $isCompatible,
            'incompatibilities' => $incompatibilities,
        ];
    }
}
