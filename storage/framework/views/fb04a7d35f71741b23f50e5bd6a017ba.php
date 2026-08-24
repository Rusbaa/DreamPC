

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto space-y-8 pb-12">
    
    <!-- Page Header -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <span class="text-3xl">📊</span>
                <div>
                    <h1 class="text-xl font-bold text-white">Visual PC Build Analytics & Summary</h1>
                    <p class="text-xs text-slate-400">Real-time power analysis, cost breakdown, and system compatibility verification</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('chat.index')); ?>" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2">
                <span>🤖</span>
                <span>Ask AI Assistant</span>
            </a>
            <a href="<?php echo e(route('catalog.index')); ?>" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2.5 rounded-xl text-xs font-semibold transition shadow-lg shadow-blue-500/20">
                Browse More Parts
            </a>
        </div>
    </div>

    <!-- Analytics Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Widget 1: Estimated System TDP & Recommended PSU -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl">⚡</span>
                        <h2 class="text-sm font-bold text-white">System TDP & Power Analysis</h2>
                    </div>
                    <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                        1.25x Headroom
                    </span>
                </div>

                <div class="mt-6 space-y-6 text-center">
                    <!-- Total System TDP Meter -->
                    <div class="relative flex flex-col items-center justify-center p-6 bg-slate-950/80 border border-slate-800 rounded-2xl">
                        <span class="text-xs font-medium text-slate-400">Est. Total System TDP</span>
                        <div class="text-4xl font-extrabold text-amber-400 mt-2 font-mono">
                            <?php echo e($totalTdp); ?> <span class="text-lg font-normal text-slate-400">Watts</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">Combined peak power output under high workload</p>
                    </div>

                    <!-- Recommended PSU Output -->
                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-left space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-emerald-400">Recommended PSU Wattage:</span>
                            <span class="text-base font-bold text-emerald-300 font-mono"><?php echo e($recommendedPsuWattage); ?>W+</span>
                        </div>
                        <p class="text-[11px] text-slate-400">
                            Includes safety buffer for transient power spikes and future component upgrades.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-[11px] text-slate-500 border-t border-slate-800/80 pt-3">
                Calculated automatically using SpecExtractor TDP metrics.
            </div>
        </div>

        <!-- Widget 2: Price Distribution Progress Bars -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6 lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">📈</span>
                    <h2 class="text-sm font-bold text-white">Price Allocation & Cost Breakdown</h2>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 block">Total Est. Cost</span>
                    <span class="text-base font-bold text-emerald-400">$<?php echo e(number_format($costBreakdown['total'] ?? 0, 2)); ?></span>
                </div>
            </div>

            <!-- Items Cost Progress Bars -->
            <div class="space-y-4">
                <?php $__empty_1 = true; $__currentLoopData = $costBreakdown['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-200 font-medium truncate max-w-[70%]"><?php echo e($item['name']); ?></span>
                            <div class="space-x-2 font-mono">
                                <span class="text-slate-400">$<?php echo e(number_format($item['price'], 2)); ?></span>
                                <span class="text-blue-400 font-bold">(<?php echo e($item['percentage_contribution']); ?>%)</span>
                            </div>
                        </div>
                        <!-- Progress Bar Container -->
                        <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500" 
                                 style="width: <?php echo e(min(100, max(5, $item['percentage_contribution']))); ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-xs text-slate-500 text-center py-6">No products selected for cost breakdown.</p>
                <?php endif; ?>
            </div>

            <!-- Summary Totals Pill Row -->
            <div class="grid grid-cols-3 gap-3 pt-4 border-t border-slate-800/80 text-center">
                <div class="bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-[10px] text-slate-500 block">Subtotal</span>
                    <span class="text-xs font-bold text-slate-300 font-mono">$<?php echo e(number_format($costBreakdown['subtotal'] ?? 0, 2)); ?></span>
                </div>
                <div class="bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-[10px] text-slate-500 block">Est. Tax (5%)</span>
                    <span class="text-xs font-bold text-slate-300 font-mono">$<?php echo e(number_format($costBreakdown['tax'] ?? 0, 2)); ?></span>
                </div>
                <div class="bg-slate-950/60 p-2.5 rounded-lg border border-slate-800">
                    <span class="text-[10px] text-slate-500 block">Shipping</span>
                    <span class="text-xs font-bold text-slate-300 font-mono">$<?php echo e(number_format($costBreakdown['shipping'] ?? 0, 2)); ?></span>
                </div>
            </div>
        </div>

    </div>

    <!-- Widget 3: Compatibility Status Summary Checklist -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center space-x-2">
                <span class="text-xl">✅</span>
                <h2 class="text-sm font-bold text-white">Hardware Compatibility Checklist</h2>
            </div>
            <div>
                <?php if($compatResult['is_compatible'] && empty($warningsResult['all_warnings'])): ?>
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5">
                        <span>✓</span> System Fully Verified & Ready
                    </span>
                <?php else: ?>
                    <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5">
                        <span>⚠️</span> Compatibility Warnings Detected
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Checklist Item 1: CPU Socket Matching -->
            <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                <div class="text-lg">
                    <?php if($compatResult['is_compatible']): ?>
                        <span class="text-emerald-400">✅</span>
                    <?php else: ?>
                        <span class="text-amber-400">⚠️</span>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200">CPU & Motherboard Socket Match</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Verifies processor pin array physically aligns with motherboard socket architecture.</p>
                </div>
            </div>

            <!-- Checklist Item 2: RAM Generation -->
            <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                <div class="text-lg">
                    <span class="text-emerald-400">✅</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200">RAM Generation (DDR4 vs DDR5)</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Ensures memory module notch standards match motherboard DIMM slot generation.</p>
                </div>
            </div>

            <!-- Checklist Item 3: Form Factor Compatibility -->
            <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                <div class="text-lg">
                    <span class="text-emerald-400">✅</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200">Motherboard & Case Form Factor</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Confirms motherboard dimensions (ATX / Micro-ATX) fit inside chassis mounting standoffs.</p>
                </div>
            </div>

            <!-- Checklist Item 4: Power Supply TDP Sufficiency -->
            <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-start space-x-3">
                <div class="text-lg">
                    <?php if($recommendedPsuWattage <= 850): ?>
                        <span class="text-emerald-400">✅</span>
                    <?php else: ?>
                        <span class="text-amber-400">⚠️</span>
                    <?php endif; ?>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-200">Power Supply Wattage Headroom</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Validates total TDP multiplied by 1.25 headroom factor stays within PSU specifications.</p>
                </div>
            </div>

        </div>

        <?php if(!empty($compatResult['incompatibilities']) || !empty($warningsResult['all_warnings'])): ?>
            <div class="mt-4 bg-amber-500/10 border border-amber-500/20 p-4 rounded-xl space-y-2">
                <h4 class="text-xs font-bold text-amber-400">Detected Issues & Bottleneck Recommendations:</h4>
                <ul class="text-xs text-amber-300 space-y-1 list-disc list-inside">
                    <?php $__currentLoopData = $compatResult['incompatibilities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($err); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $warningsResult['all_warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><strong><?php echo e($warning['title']); ?>:</strong> <?php echo e($warning['message']); ?> (<?php echo e($warning['recommendation']); ?>)</li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Arfa\Desktop\ProjectDreamPC\DreamPC\resources\views/build/summary.blade.php ENDPATH**/ ?>