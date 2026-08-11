@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12">
    
    <!-- Page Header -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur-xl flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-3xl">🛒</span>
            <div>
                <h1 class="text-xl font-bold text-white">Interactive Hardware Shopping Cart</h1>
                <p class="text-xs text-slate-400">Review your component configuration, check compatibility, or swap alternatives</p>
            </div>
        </div>
        <a href="{{ route('catalog.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-xl text-xs font-semibold transition">
            Continue Shopping
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-xs flex items-center justify-between">
            <span>✓ {{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs flex items-center justify-between">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    @if($cart->items->isEmpty())
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center space-y-4">
            <span class="text-5xl opacity-40">🛒</span>
            <h2 class="text-lg font-bold text-slate-300">Your Shopping Cart is Empty</h2>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Explore our catalog or ask our AI Hardware Assistant to generate a custom PC build for you!</p>
            <div class="pt-2 flex justify-center gap-3">
                <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition shadow-lg shadow-blue-500/20">
                    Explore Catalog
                </a>
                <a href="{{ route('chat.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-5 py-2.5 rounded-xl text-xs font-semibold transition">
                    🤖 Talk to AI Assistant
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Cart Items List (Col 2) -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- System Compatibility Banner -->
                <div class="bg-slate-900/90 border border-slate-800 p-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="text-lg">⚙️</span>
                        <div>
                            <h3 class="text-xs font-bold text-slate-200">Cart Build Compatibility</h3>
                            <p class="text-[11px] text-slate-400">Automated socket, RAM generation & TDP verification</p>
                        </div>
                    </div>
                    @if($compatCheck['is_compatible'])
                        <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                            ✓ 100% Compatible
                        </span>
                    @else
                        <span class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                            ⚠️ Incompatibility Warning
                        </span>
                    @endif
                </div>

                @if(!$compatCheck['is_compatible'] && !empty($compatCheck['incompatibilities']))
                    <div class="bg-amber-500/10 border border-amber-500/20 p-3 rounded-xl text-xs text-amber-300 space-y-1">
                        @foreach($compatCheck['incompatibilities'] as $warning)
                            <p>• {{ $warning }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Items Table / Cards -->
                <div class="space-y-3">
                    @foreach($cart->items as $item)
                        <div class="bg-slate-900/80 border border-slate-800/80 hover:border-slate-700 p-4 rounded-2xl transition space-y-3">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if($item->product->image_path)
                                        <img src="{{ $item->product->image_path }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-2xl">🖥️</span>
                                    @endif
                                </div>

                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[10px] bg-slate-800 text-slate-400 border border-slate-700/50 px-2 py-0.5 rounded font-mono uppercase">
                                            {{ $item->product->category->name ?? 'Part' }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ $item->product->brand }}</span>
                                    </div>
                                    <h3 class="text-sm font-semibold text-white truncate mt-0.5">{{ $item->product->name }}</h3>
                                    <div class="text-xs text-emerald-400 font-bold mt-1 font-mono">${{ number_format($item->unit_price, 2) }}</div>
                                </div>

                                <!-- AJAX Quantity Controls -->
                                <div class="flex items-center space-x-2 bg-slate-950 border border-slate-800 rounded-xl p-1">
                                    <button type="button" onclick="updateCartQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" 
                                            class="w-7 h-7 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 flex items-center justify-center text-xs font-bold transition">
                                        -
                                    </button>
                                    <span id="quantity-{{ $item->id }}" class="w-8 text-center text-xs font-bold text-white font-mono">
                                        {{ $item->quantity }}
                                    </span>
                                    <button type="button" onclick="updateCartQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" 
                                            class="w-7 h-7 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 flex items-center justify-center text-xs font-bold transition">
                                        +
                                    </button>
                                </div>

                                <!-- Item Subtotal & Actions -->
                                <div class="text-right flex flex-col items-end space-y-2">
                                    <span id="subtotal-{{ $item->id }}" class="text-sm font-extrabold text-white font-mono">
                                        ${{ number_format($item->quantity * $item->unit_price, 2) }}
                                    </span>
                                    <form method="POST" action="{{ route('cart.destroy', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-slate-500 hover:text-red-400 transition">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Component Alternative Swapper Section -->
                            @if(!empty($alternativesMap[$item->id]))
                                <div class="pt-2 border-t border-slate-800/60 flex items-center justify-between text-xs">
                                    <span class="text-slate-400 text-[11px]">🔄 Swap with compatible alternative:</span>
                                    <div class="flex items-center gap-2">
                                        @foreach($alternativesMap[$item->id] as $altProduct)
                                            <form method="POST" action="{{ route('cart.swap', $item->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="new_product_id" value="{{ $altProduct['id'] }}">
                                                <button type="submit" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white px-2.5 py-1 rounded-lg text-[10px] transition">
                                                    {{ Str::limit($altProduct['name'], 20) }} (${{ number_format($altProduct['price'], 2) }})
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary Sidebar (Col 1) -->
            <div class="space-y-6">
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
                    <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3">Order Financial Breakdown</h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal</span>
                            <span id="summary-subtotal" class="font-mono text-slate-200">${{ number_format($subtotal, 2) }}</span>
                        </div>

                        @if($appliedCoupon)
                            <div class="flex justify-between items-center text-emerald-400 font-semibold bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1.5 rounded-lg">
                                <div class="flex items-center space-x-1.5">
                                    <span>🎟️ {{ $appliedCoupon['code'] }}</span>
                                    <span class="text-[10px] bg-emerald-500/20 px-1.5 py-0.5 rounded font-mono">
                                        {{ $appliedCoupon['discount_type'] === 'percent' ? $appliedCoupon['value'] . '%' : '$' . $appliedCoupon['value'] }} OFF
                                    </span>
                                </div>
                                <span class="font-mono">-${{ number_format($discount, 2) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between text-slate-400">
                            <span>Est. Tax (5%)</span>
                            <span id="summary-tax" class="font-mono text-slate-200">${{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Shipping ($15 base + $2/item)</span>
                            <span id="summary-shipping" class="font-mono text-slate-200">${{ number_format($shipping, 2) }}</span>
                        </div>
                        <div class="pt-3 border-t border-slate-800 flex justify-between text-sm font-bold">
                            <span class="text-white">Total</span>
                            <span id="summary-total" class="text-emerald-400 font-mono text-base">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <!-- Coupon Code Input Box -->
                    <div class="pt-3 border-t border-slate-800 space-y-2">
                        @if($appliedCoupon)
                            <form method="POST" action="{{ route('coupon.remove') }}">
                                @csrf
                                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-red-400 border border-slate-700 font-semibold text-xs py-2 rounded-xl transition">
                                    Remove Coupon ({{ $appliedCoupon['code'] }})
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('coupon.apply') }}" class="flex items-center space-x-2">
                                @csrf
                                <input type="text" name="code" placeholder="Coupon Code (e.g. SAVE10)" 
                                       class="flex-grow bg-slate-950 border border-slate-800 text-xs text-white uppercase rounded-xl px-3 py-2 outline-none focus:border-blue-500 transition" required>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs px-4 py-2 rounded-xl transition shadow-md shadow-blue-600/20 flex-shrink-0">
                                    Apply
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="space-y-2 pt-2">
                        <a href="{{ route('checkout.index') }}" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs py-3 rounded-xl transition shadow-lg shadow-emerald-600/20 text-center block">
                            Proceed to Checkout →
                        </a>
                        <a href="{{ route('build.summary') }}" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-semibold text-xs py-2.5 rounded-xl transition text-center block">
                            📊 View Build Analytics & Summary
                        </a>
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>

<script>
async function updateCartQuantity(itemId, quantity) {
    if (quantity < 0) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/cart/items/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ quantity: quantity })
        });

        const data = await response.json();

        if (data.status === 'success') {
            if (quantity === 0) {
                window.location.reload();
                return;
            }

            document.getElementById(`quantity-${itemId}`).textContent = data.item_quantity;
            document.getElementById(`subtotal-${itemId}`).textContent = data.item_subtotal;
            document.getElementById('summary-subtotal').textContent = data.cart_subtotal;
            document.getElementById('summary-tax').textContent = data.cart_tax;
            document.getElementById('summary-shipping').textContent = data.cart_shipping;
            document.getElementById('summary-total').textContent = data.cart_total;
        }
    } catch (e) {
        console.error('Error updating cart quantity:', e);
    }
}
</script>
@endsection
