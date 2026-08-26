<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'cols' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
    'gap' => 'gap-6'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'cols' => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
    'gap' => 'gap-6'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => "grid {$cols} {$gap} w-full"])); ?>>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Users\Arfa\Desktop\New folder (2)\DreamPC\resources\views/components/responsive-grid.blade.php ENDPATH**/ ?>