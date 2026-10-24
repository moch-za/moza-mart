@extends('layouts.app')

@section('title', 'Detail Penjualan — ' . $sale->sale_number)

@section('header-actions')
    <a href="{{ route('sales.index') }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
        Kembali
    </a>

    @can('cancel-transaction')
        @if ($sale->status === 'completed')
            <form action="{{ route('sales.cancel', $sale) }}" method="POST" onsubmit="return confirm('Peringatan: Membatalkan penjualan akan mengembalikan stok produk. Lanjutkan?');" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                    Batalkan Transaksi
                </button>
            </form>
        @endif
    @endcan
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header Info Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor Penjualan</p>
                <h2 class="text-2xl font-mono font-bold text-gray-800 mt-0.5">{{ $sale->sale_number }}</h2>
            </div>
            <div>
                @if ($sale->status === 'completed')
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-green-100 text-green-800">
                        Completed
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-red-100 text-red-800">
                        Cancelled
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-6">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kasir / Petugas</p>
                <p class="text-base font-semibold text-gray-800 mt-1">{{ $sale->user->name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ $sale->user->role }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal & Waktu Transaksi</p>
                <p class="text-base font-medium text-gray-800 mt-1">{{ $sale->sold_at->format('d F Y') }}</p>
                <p class="text-xs text-gray-500">{{ $sale->sold_at->format('H:i') }} WIB</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan</p>
                <p class="text-sm text-gray-600 mt-1">{{ $sale->notes ?: '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Rincian Barang yang Terjual</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-xs uppercase text-gray-400 font-semibold border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3.5">No</th>
                        <th class="px-6 py-3.5">SKU</th>
                        <th class="px-6 py-3.5">Nama Produk</th>
                        <th class="px-6 py-3.5 text-center">Jumlah</th>
                        <th class="px-6 py-3.5 text-right">Harga Satuan (Rp)</th>
                        <th class="px-6 py-3.5 text-right">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($sale->items as $idx => $item)
                    <tr>
                        <td class="px-6 py-4 text-xs text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $item->product->sku }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <a href="{{ route('products.show', $item->product) }}" class="hover:text-indigo-600">
                                {{ $item->product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center font-medium">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-medium text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-gray-800">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right font-semibold">Total Pembayaran:</td>
                        <td class="px-6 py-4 text-right font-bold text-lg text-emerald-600">
                            Rp {{ number_format($sale->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
