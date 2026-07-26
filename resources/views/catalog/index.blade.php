@extends('layouts.app')

@section('content')
<div class="w-full space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Hardware Catalog</h1>
            <p class="text-sm text-slate-400 mt-1">Explore custom PC components with real-time specification filtering</p>
        </div>
        
        <!-- Sort Control -->
        <form method="GET" action="{{ route('catalog.index') }}" class="flex items-center space-x-2">
            @foreach(request()->except('sort', 'page') as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <label class="text-xs font-medium text-slate-400">Sort By:</label>
            <select name="sort" onchange="this.form.submit()" 
                class="bg-slate-900 border border-slate-800 text-xs text-white rounded-lg px-3 py-2 outline-none focus:border-blue-500 transition">
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
            </select>
        </form>
    </div>

    <!-- Catalog Content: Sidebar + Products Grid -->
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Filter Sidebar -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <form method="GET" action="{{ route('catalog.index') }}" class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl backdrop-blur-xl space-y-6">
                
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Filters</h2>
                    @if(request()->anyFilled(['category', 'brand', 'min_price', 'max_price', 'spec_key', 'spec_value']))
                        <a href="{{ route('catalog.index') }}" class="text-xs text-blue-400 hover:underline">Reset All</a>
                    @endif
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Category</label>
                    <select name="category" onchange="this.form.submit()"
                        class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-lg px-3 py-2 outline-none focus:border-blue-500 transition">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Brand</label>
                    <select name="brand" onchange="this.form.submit()"
                        class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-lg px-3 py-2 outline-none focus:border-blue-500 transition">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            @if($brand)
                                <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                    {{ $brand }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Price Range Filter (whereBetween) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Price Range ($)</label>
                    <div class="flex items-center space-x-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" min="0" step="1"
                            class="w-1/2 bg-slate-950 border border-slate-800 text-xs text-white rounded-lg px-2.5 py-2 outline-none focus:border-blue-500">
                        <span class="text-slate-600">-</span>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" min="0" step="1"
                            class="w-1/2 bg-slate-950 border border-slate-800 text-xs text-white rounded-lg px-2.5 py-2 outline-none focus:border-blue-500">
                    </div>
                </div>

                <!-- Specification Key-Value Filter -->
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Spec Filter</label>
                    <input type="text" name="spec_key" value="{{ request('spec_key') }}" placeholder="Spec Key (e.g. socket)"
                        class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                    <input type="text" name="spec_value" value="{{ request('spec_value') }}" placeholder="Spec Value (e.g. AM5)"
                        class="w-full bg-slate-950 border border-slate-800 text-xs text-white rounded-lg px-3 py-2 outline-none focus:border-blue-500">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 rounded-lg text-xs transition shadow-lg shadow-blue-600/20">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Product Grid Display -->
        <main class="flex-grow">
            @if($products->count() > 0)
                <x-responsive-grid>
                    @foreach($products as $product)
                        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl overflow-hidden shadow-xl hover:border-slate-700 transition flex flex-col justify-between group">
                            <div>
                                <!-- Product Image / Placeholder -->
                                <div class="h-44 bg-slate-950 flex items-center justify-center border-b border-slate-800/80 relative">
                                    @if($product->image_path)
                                        <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="text-3xl text-slate-700">📦</div>
                                    @endif
                                    
                                    <!-- Stock Status Badge -->
                                    <div class="absolute top-3 right-3">
                                        @if($product->stock_quantity > 5)
                                            <span class="bg-emerald-500/20 text-emerald-300 text-[10px] px-2 py-0.5 rounded-full font-bold border border-emerald-500/30">
                                                In Stock ({{ $product->stock_quantity }})
                                            </span>
                                        @elseif($product->stock_quantity > 0)
                                            <span class="bg-amber-500/20 text-amber-300 text-[10px] px-2 py-0.5 rounded-full font-bold border border-amber-500/30">
                                                Low Stock ({{ $product->stock_quantity }})
                                            </span>
                                        @else
                                            <span class="bg-red-500/20 text-red-300 text-[10px] px-2 py-0.5 rounded-full font-bold border border-red-500/30">
                                                Out of Stock
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Card Details -->
                                <div class="p-5 space-y-3">
                                    <div class="flex items-center justify-between text-xs text-slate-400">
                                        <span>{{ $product->brand }}</span>
                                        <span class="bg-slate-800 px-2 py-0.5 rounded text-[11px] text-slate-300 border border-slate-700">
                                            {{ $product->category->name ?? 'Hardware' }}
                                        </span>
                                    </div>

                                    <h3 class="font-bold text-white text-base group-hover:text-blue-400 transition line-clamp-1">
                                        {{ $product->name }}
                                    </h3>

                                    <!-- Specifications Snapshot -->
                                    @if($product->specifications->count() > 0)
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            @foreach($product->specifications->take(3) as $spec)
                                                <span class="bg-slate-950 text-slate-400 text-[10px] px-2 py-0.5 rounded border border-slate-800">
                                                    {{ $spec->spec_key }}: <strong class="text-slate-300">{{ $spec->spec_value }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer Price & Action -->
                            <div class="p-5 pt-0 flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Price</div>
                                    <div class="text-lg font-black text-emerald-400">${{ number_format($product->price, 2) }}</div>
                                </div>

                                <button type="button" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium px-3.5 py-2 rounded-lg transition shadow-md shadow-blue-600/20">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    @endforeach
                </x-responsive-grid>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
                    <div class="text-4xl mb-3">🔍</div>
                    <h3 class="text-lg font-bold text-white">No products found</h3>
                    <p class="text-xs text-slate-500 mt-1">Try adjusting or resetting your filter criteria.</p>
                </div>
            @endif
        </main>

    </div>
</div>
@endsection
