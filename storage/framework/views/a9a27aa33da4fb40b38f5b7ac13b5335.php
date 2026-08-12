

<?php $__env->startSection('content'); ?>
    <div class="w-full max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Hardware Inventory</h1>
                <p class="text-sm text-slate-400">Manage PC components, pricing, stock, and specifications</p>
            </div>
            <a href="<?php echo e(route('products.create')); ?>"
                class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded-lg text-sm transition shadow-lg shadow-blue-600/20">
                + Add New Component
            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl backdrop-blur-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="border-b border-slate-800 bg-slate-950/50 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Product</th>
                        <th class="py-3.5 px-6">SKU</th>
                        <th class="py-3.5 px-6">Category</th>
                        <th class="py-3.5 px-6">Price</th>
                        <th class="py-3.5 px-6">Stock</th>
                        <th class="py-3.5 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6">
                                <div class="font-medium text-white"><?php echo e($product->name); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($product->brand); ?></div>
                            </td>
                            <td class="py-4 px-6 text-slate-300 font-mono text-xs"><?php echo e($product->sku); ?></td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-block bg-slate-800 text-slate-300 text-xs px-2.5 py-1 rounded-md border border-slate-700">
                                    <?php echo e($product->category->name ?? 'Uncategorized'); ?>

                                </span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-emerald-400">$<?php echo e(number_format($product->price, 2)); ?></td>
                            <td class="py-4 px-6">
                                <?php if($product->stock_quantity > 5): ?>
                                    <span
                                        class="bg-emerald-500/10 text-emerald-400 text-xs px-2.5 py-1 rounded-full font-medium border border-emerald-500/20">
                                        <?php echo e($product->stock_quantity); ?> In Stock
                                    </span>
                                <?php elseif($product->stock_quantity > 0): ?>
                                    <span
                                        class="bg-amber-500/10 text-amber-400 text-xs px-2.5 py-1 rounded-full font-medium border border-amber-500/20">
                                        Low Stock (<?php echo e($product->stock_quantity); ?>)
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="bg-red-500/10 text-red-400 text-xs px-2.5 py-1 rounded-full font-medium border border-red-500/20">
                                        Out of Stock
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="<?php echo e(route('products.edit', $product->id)); ?>"
                                    class="text-xs text-blue-400 hover:text-blue-300 font-medium">Edit</a>
                                <form action="<?php echo e(route('products.destroy', $product->id)); ?>" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this component?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="text-xs text-red-400 hover:text-red-300 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 text-sm">
                                No hardware components found in inventory.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if($products->hasPages()): ?>
                <div class="p-4 border-t border-slate-800">
                    <?php echo e($products->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Work\App Dev\PC Builder Website\resources\views/admin/products/index.blade.php ENDPATH**/ ?>