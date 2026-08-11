@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-12">
    
    <!-- Success Banner -->
    <div class="bg-gradient-to-r from-emerald-950 to-slate-900 border border-emerald-500/30 rounded-2xl p-8 shadow-2xl text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-3xl font-bold flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
            ✓
        </div>
        <div>
            <h1 class="text-2xl font-black text-white">Order Confirmed!</h1>
            <p class="text-xs text-emerald-300 mt-1">Thank you for your order. We have received your hardware configuration!</p>
        </div>
        <div class="inline-block bg-slate-950/80 border border-slate-800 px-4 py-2 rounded-xl text-xs text-slate-300 font-mono">
            Order Reference ID: <strong class="text-emerald-400 font-bold">#ORD-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong>
        </div>
    </div>

    <!-- Fulfillment Schedule Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Order Status</span>
            <span class="text-sm font-bold text-emerald-400 uppercase tracking-wider">{{ $order->status }}</span>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Fulfillment Method</span>
            <span class="text-sm font-bold text-white uppercase tracking-wider">
                {{ $order->fulfillment_type === 'delivery' ? '🚚 Home Delivery' : '🏬 In-Store Pickup' }}
            </span>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 p-5 rounded-2xl space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Scheduled Date</span>
            <span class="text-sm font-bold text-blue-400 font-mono">
                {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : 'Pending' }}
            </span>
        </div>
    </div>

    <!-- Ordered Items Snapshot -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
        <h2 class="text-sm font-bold text-white border-b border-slate-800 pb-3">Purchased Hardware Components</h2>

        <div class="space-y-3">
            @foreach($order->items as $item)
                <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-center text-lg">
                            🖥️
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-white">{{ $item->product->name ?? 'Product Component' }}</h4>
                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                Qty: {{ $item->quantity }} × ${{ number_format($item->price_at_purchase, 2) }}
                            </div>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-emerald-400 font-mono">
                        ${{ number_format($item->quantity * $item->price_at_purchase, 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Actions Footer -->
    <div class="flex justify-center gap-4 pt-4">
        <a href="{{ route('catalog.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs px-6 py-3 rounded-xl transition shadow-lg shadow-blue-600/20">
            Back to Catalog
        </a>
        <a href="{{ route('chat.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs px-6 py-3 rounded-xl transition">
            🤖 Ask AI for New Build
        </a>
    </div>

</div>
@endsection
