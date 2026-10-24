@extends('layouts.app')

@section('title', 'Peringatan Stok Rendah')

@section('header-actions')
    <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
        Semua Produk
    </a>
    <a href="{{ route('purchases.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
        Buat Pembelian Baru
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-4 text-sm flex items-center gap-3">
        <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="font-semibold">Perhatian: Produk di bawah batas minimum stok!</p>
            <p class="text-xs text-amber-700 mt-0.5">Segera lakukan pembelian stok ke supplier untuk menjaga ketersediaan barang.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400 font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">SKU</th>
                        <th class="px-6 py-3.5">Nama Produk</th>
                        <th class="px-6 py-3.5">Kategori</th>
                        <th class="px-6 py-3.5 text-center">Stok Saat Ini</th>
                        <th class="px-6 py-3.5 text-center">Batas Minimum</th>
                        <th class="px-6 py-3.5 text-center">Kekurangan</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <a href="{{ route('products.show', $product) }}" class="hover:text-indigo-600">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $product->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center font-medium text-gray-600">{{ $product->minimum_stock }}</td>
                        <td class="px-6 py-4 text-center text-red-600 font-semibold">
                            -{{ max(0, $product->minimum_stock - $product->stock) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('purchases.create') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                Restok &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                            Semua stok dalam kondisi aman. Tidak ada produk di bawah batas minimum.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
