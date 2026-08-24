<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'DreamPC - Intelligent Hardware Marketplace')); ?></title>
    
    <!-- Google Fonts & Tailwind CSS CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col antialiased selection:bg-blue-500 selection:text-white overflow-x-hidden">
    
    <!-- Responsive Navigation Bar -->
    <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Flash Alert Messages -->
    <?php if(session('success') || session('error')): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <?php if(session('success')): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between shadow-lg backdrop-blur-md">
                    <span><?php echo e(session('success')); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">✕</button>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center justify-between shadow-lg backdrop-blur-md">
                    <span><?php echo e(session('error')); ?></span>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-200">✕</button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Responsive Body Container -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 text-slate-500 text-xs py-6 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center sm:flex sm:justify-between sm:text-left">
            <p>&copy; <?php echo e(date('Y')); ?> DreamPC. All rights reserved.</p>
            <p class="mt-2 sm:mt-0">Intelligent Conversational Hardware Marketplace</p>
        </div>
    </footer>

</body>
</html>
<?php /**PATH C:\Users\Arfa\Desktop\ProjectDreamPC\DreamPC\resources\views/layouts/app.blade.php ENDPATH**/ ?>