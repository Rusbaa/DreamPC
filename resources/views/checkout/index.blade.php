@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-12">
    
    <!-- Page Header -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur-xl flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-3xl">📦</span>
            <div>
                <h1 class="text-xl font-bold text-white">Secure Checkout & Delivery Scheduler</h1>
                <p class="text-xs text-slate-400">Choose fulfillment method, schedule delivery or pickup date, and confirm order</p>
            </div>
        </div>
        <a href="{{ route('cart.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-4 py-2 rounded-xl text-xs font-semibold transition">
            ← Return to Cart
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs">
            <span>⚠️ {{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        @csrf

        <!-- Multi-Step Form Fields (Col 2) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Step 1: Fulfillment Selection -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center space-x-2 border-b border-slate-800 pb-3">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                    <h2 class="text-sm font-bold text-white">Select Fulfillment Method</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <!-- Delivery Option -->
                    <label class="relative flex flex-col p-4 bg-slate-950 border-2 border-slate-800 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="fulfillment_type" value="delivery" checked 
                               onchange="toggleFulfillment('delivery')" class="hidden peer">
                        <div class="flex items-center justify-between peer-checked:text-blue-400">
                            <span class="text-2xl">🚚</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-400">Express Delivery</span>
                        </div>
                        <span class="text-xs font-bold text-white mt-3">Home / Office Shipping</span>
                        <span class="text-[11px] text-slate-400 mt-1">Direct courier dispatch with full tracking to your specified address.</span>
                    </label>

                    <!-- Pickup Option -->
                    <label class="relative flex flex-col p-4 bg-slate-950 border-2 border-slate-800 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="fulfillment_type" value="pickup" 
                               onchange="toggleFulfillment('pickup')" class="hidden peer">
                        <div class="flex items-center justify-between peer-checked:text-emerald-400">
                            <span class="text-2xl">🏬</span>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">In-Store Pickup</span>
                        </div>
                        <span class="text-xs font-bold text-white mt-3">Store Pickup Station</span>
                        <span class="text-[11px] text-slate-400 mt-1">Pick up directly from our hardware distribution warehouse (Free shipping).</span>
                    </label>
                </div>
            </div>

            <!-- Step 2: Date Scheduler & Contact Details -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-5">
                <div class="flex items-center space-x-2 border-b border-slate-800 pb-3">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                    <h2 class="text-sm font-bold text-white">Schedule Date & Contact Info</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', Auth::user()->name ?? '') }}" 
                               class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl px-4 py-3 outline-none focus:border-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number *</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="+1 (555) 000-0000" 
                               class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl px-4 py-3 outline-none focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Date Picker -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        📅 Scheduled <span id="schedule-type-label">Delivery</span> Date *
                    </label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d', strtotime('+1 day'))) }}" 
                           min="{{ date('Y-m-d') }}" 
                           class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl px-4 py-3 outline-none focus:border-blue-500" required>
                </div>

                <!-- Address Input (Toggled for Delivery) -->
                <div id="address-container">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Shipping Address *</label>
                    <textarea name="address" rows="3" placeholder="Enter complete street address, city, state, zip code..." 
                              class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-xl p-3 outline-none focus:border-blue-500">{{ old('address') }}</textarea>
                </div>
            </div>

            <!-- Step 3: Order Confirmation Button -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center">3</span>
                    <span class="text-xs text-slate-300 font-medium">Ready to finalize your hardware order?</span>
                </div>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs px-6 py-3 rounded-xl transition shadow-lg shadow-blue-500/20">
                    Place Order Now →
                </button>
            </div>

        </div>

        <!-- Order Summary Sidebar (Col 1) -->
        <div class="space-y-6">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
                <h2 class="text-base font-bold text-white border-b border-slate-800 pb-3">Order Summary</h2>

                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    @foreach($cart->items as $item)
                        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-800/50">
                            <div class="truncate max-w-[70%]">
                                <span class="text-slate-200 font-medium truncate block">{{ $item->product->name }}</span>
                                <span class="text-[10px] text-slate-400">Qty: {{ $item->quantity }}</span>
                            </div>
                            <span class="text-emerald-400 font-mono font-bold">${{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2 text-xs border-t border-slate-800 pt-4">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-mono text-slate-200">${{ number_format($subtotal, 2) }}</span>
                    </div>

                    @if($appliedCoupon)
                        <div class="flex justify-between text-emerald-400 font-semibold">
                            <span>Coupon ({{ $appliedCoupon['code'] }})</span>
                            <span class="font-mono">-${{ number_format($discount, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-slate-400">
                        <span>Est. Tax (5%)</span>
                        <span class="font-mono text-slate-200">${{ number_format($tax, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-slate-400">
                        <span>Shipping</span>
                        <span class="font-mono text-slate-200">${{ number_format($shipping, 2) }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-between text-sm font-bold">
                        <span class="text-white">Total</span>
                        <span class="text-emerald-400 font-mono text-base">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
function toggleFulfillment(type) {
    const addressContainer = document.getElementById('address-container');
    const label = document.getElementById('schedule-type-label');

    if (type === 'pickup') {
        addressContainer.classList.add('hidden');
        label.textContent = 'Pickup';
    } else {
        addressContainer.classList.remove('hidden');
        label.textContent = 'Delivery';
    }
}
</script>
@endsection
