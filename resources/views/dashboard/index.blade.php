@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Penjualan Hari Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-400">Hari Ini</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($todaySalesTotal, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $todaySalesCount }} transaksi</p>
        </div>

        {{-- Penjualan Bulan Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-400">Bulan Ini</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($monthSalesTotal, 0, ',', '.') }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $monthSalesCount }} transaksi</p>
        </div>

        {{-- Total Produk --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-violet-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-400">Total</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalProducts }}</p>
            <p class="text-sm text-gray-500 mt-1">Jenis produk</p>
        </div>

        {{-- Pembelian Pending --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-400">Menunggu</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingPurchases }}</p>
            <p class="text-sm text-gray-500 mt-1">Purchase pending</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Stok Rendah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                    <h2 class="font-semibold text-gray-800">Stok Rendah</h2>
                </div>
                <a href="{{ route('products.low-stock') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Lihat semua</a>
            </div>
            @if ($lowStockProducts->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm">Semua stok aman</p>
            </div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach ($lowStockProducts->take(6) as $product)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->category->name }} • SKU: {{ $product->sku }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            Stok: {{ $product->stock }}
                        </span>
                        <p class="text-xs text-gray-400 mt-0.5">Min: {{ $product->minimum_stock }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Penjualan Terbaru --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                    <h2 class="font-semibold text-gray-800">Penjualan Terbaru</h2>
                </div>
                <a href="{{ route('sales.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Lihat semua</a>
            </div>
            @if ($recentSales->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400">
                <p class="text-sm">Belum ada penjualan</p>
            </div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach ($recentSales as $sale)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <a href="{{ route('sales.show', $sale) }}"
                           class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            {{ $sale->sale_number }}
                        </a>
                        <p class="text-xs text-gray-400">{{ $sale->user->name }} • {{ $sale->sold_at->format('d M Y H:i') }}</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-800">Rp {{ number_format($sale->total, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
