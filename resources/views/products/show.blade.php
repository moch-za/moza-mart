@extends('layouts.app')

@section('title', 'Detail Produk — ' . $product->name)

@section('header-actions')
    <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
        Kembali
    </a>
    @can('manage-master-data')
    <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
        Edit Produk
    </a>
    @endcan
@endsection

@section('content')
<div class="space-y-6">

    {{-- Detail Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">SKU</p>
                <p class="text-base font-mono font-medium text-gray-800 mt-1">{{ $product->sku }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kategori</p>
                <p class="text-base font-medium text-gray-800 mt-1">{{ $product->category->name }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Harga Beli</p>
                <p class="text-base font-medium text-gray-800 mt-1">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Harga Jual</p>
                <p class="text-base font-semibold text-indigo-600 mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Stok Saat Ini</p>
                <div class="mt-1">
                    @if ($product->isLowStock())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            {{ $product->stock }} (Stok Kritis)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            {{ $product->stock }} (Tersedia)
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Batas Minimum Stok</p>
                <p class="text-base font-medium text-gray-800 mt-1">{{ $product->minimum_stock }} unit</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dibuat Pada</p>
                <p class="text-base font-medium text-gray-800 mt-1">{{ $product->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Terakhir Diperbarui</p>
                <p class="text-base font-medium text-gray-800 mt-1">{{ $product->updated_at->format('d M Y H:i') }}</p>
            </div>
        </div>
    </div>

    {{-- Riwayat Transaksi --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Riwayat Pembelian --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Riwayat Pembelian Terbaru
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2">No. Purchase</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-right">Harga Beli</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($product->purchaseItems->take(5) as $item)
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-xs">
                                <a href="{{ route('purchases.show', $item->purchase) }}" class="text-indigo-600 hover:underline">
                                    {{ $item->purchase->purchase_number }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-2.5 text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-semibold capitalize">{{ $item->purchase->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">Belum ada riwayat pembelian.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Riwayat Penjualan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Riwayat Penjualan Terbaru
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2">No. Penjualan</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-right">Harga Jual</th>
                            <th class="px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($product->saleItems->take(5) as $item)
                        <tr>
                            <td class="px-4 py-2.5 font-mono text-xs">
                                <a href="{{ route('sales.show', $item->sale) }}" class="text-indigo-600 hover:underline">
                                    {{ $item->sale->sale_number }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-2.5 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-semibold capitalize">{{ $item->sale->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">Belum ada riwayat penjualan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
