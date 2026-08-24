

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto space-y-8 pb-12">
    
    <!-- Page Header -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur-xl flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-3xl">📜</span>
            <div>
                <h1 class="text-xl font-bold text-white">Your Order History & Build Archive</h1>
                <p class="text-xs text-slate-400">View past orders, track deliveries, and check archived product specifications snapshots</p>
            </div>
        </div>
        <a href="<?php echo e(route('catalog.index')); ?>" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-lg shadow-blue-500/20">
            Browse Components
        </a>
    </div>

    <?php if($orders->isEmpty()): ?>
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center space-y-4">
            <span class="text-5xl opacity-40">📜</span>
            <h2 class="text-lg font-bold text-slate-300">No Orders Found</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">You have not placed any orders yet. Visit our catalog to configure and purchase your first build!</p>
            <div class="pt-2 flex justify-center">
                <a href="<?php echo e(route('catalog.index')); ?>" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition shadow-lg shadow-blue-500/20">
                    Explore Catalog
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden shadow-xl space-y-4 p-6">
                    
                    <!-- Order Summary Card Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                        <div class="flex items-center space-x-4">
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Order Reference</span>
                                <span class="text-xs font-bold text-white font-mono">#ORD-<?php echo e(str_pad($order->id, 6, '0', STR_PAD_LEFT)); ?></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Date Placed</span>
                                <span class="text-xs font-medium text-slate-300"><?php echo e($order->created_at->format('M d, Y H:i')); ?></span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Fulfillment</span>
                                <span class="text-xs font-semibold text-slate-300">
                                    <?php echo e($order->fulfillment_type === 'delivery' ? '🚚 Delivery' : '🏬 Pickup'); ?>

                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Total Cost</span>
                                <span class="text-sm font-bold text-emerald-400 font-mono">$<?php echo e(number_format($order->total_amount, 2)); ?></span>
                            </div>
                            
                            <!-- Reorder Button -->
                            <form method="POST" action="<?php echo e(route('orders.reorder', $order->id)); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition shadow-md shadow-blue-600/20 flex items-center space-x-1.5">
                                    <span>🔄</span>
                                    <span>Reorder Build</span>
                                </button>
                            </form>

                            <!-- Expand/Collapse Button -->
                            <button type="button" onclick="toggleOrderDetails(<?php echo e($order->id); ?>)" 
                                    class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                <span id="toggle-text-<?php echo e($order->id); ?>">View Components ▼</span>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Component Breakdown (Accordion Body) -->
                    <div id="order-details-<?php echo e($order->id); ?>" class="hidden space-y-4 pt-2">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Archived PC Build Components:</h3>

                        <div class="grid grid-cols-1 gap-4">
                            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-sm">
                                                🖥️
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-200">
                                                    <?php echo e($item->product->name ?? 'Deleted / Custom Hardware Component'); ?>

                                                </h4>
                                                <span class="text-[10px] text-slate-500 font-mono">
                                                    Quantity: <?php echo e($item->quantity); ?> × Price: $<?php echo e(number_format($item->price_at_purchase, 2)); ?>

                                                </span>
                                            </div>
                                        </div>
                                        <span class="text-xs font-extrabold text-emerald-400 font-mono">
                                            $<?php echo e(number_format($item->quantity * $item->price_at_purchase, 2)); ?>

                                        </span>
                                    </div>

                                    <!-- Historical Specifications Snapshot Viewer -->
                                    <?php if(!empty($item->spec_snapshot_json)): ?>
                                        <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800/80">
                                            <span class="text-[10px] text-slate-500 uppercase font-semibold block mb-2">Archived Specification Snapshot (Purchase Time)</span>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                <?php $__currentLoopData = $item->spec_snapshot_json; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="bg-slate-950/80 border border-slate-800/50 px-2.5 py-1.5 rounded text-[10px] flex flex-col">
                                                        <span class="text-slate-500 uppercase font-medium text-[9px]"><?php echo e(str_replace('_', ' ', $key)); ?></span>
                                                        <strong class="text-slate-300 mt-0.5"><?php echo e($val); ?></strong>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleOrderDetails(orderId) {
    const detailsDiv = document.getElementById(`order-details-${orderId}`);
    const toggleText = document.getElementById(`toggle-text-${orderId}`);

    if (detailsDiv.classList.contains('hidden')) {
        detailsDiv.classList.remove('hidden');
        toggleText.textContent = 'Hide Components ▲';
    } else {
        detailsDiv.classList.add('hidden');
        toggleText.textContent = 'View Components ▼';
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Arfa\Desktop\ProjectDreamPC\DreamPC\resources\views/orders/index.blade.php ENDPATH**/ ?>