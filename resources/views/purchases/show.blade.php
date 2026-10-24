@extends('layouts.app')

@section('title', 'Detail Pembelian — ' . $purchase->purchase_number)

@section('header-actions')
    <a href="{{ route('purchases.index') }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
        Kembali
    </a>

    @can('cancel-transaction')
        @if ($purchase->status === 'pending')
            <form action="{{ route('purchases.confirm', $purchase) }}" method="POST" onsubmit="return confirm('Konfirmasi transaksi ini? Stok produk akan bertambah.');" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                    Konfirmasi Pembelian
                </button>
            </form>
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Batalkan transaksi ini?');" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                    Batalkan Transaksi
                </button>
            </form>
        @elseif ($purchase->status === 'completed')
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Peringatan: Membatalkan pembelian ini akan mengurangi stok kembali. Pastikan stok mencukupi! Lanjutkan?');" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                    Batalkan Pembelian
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
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor Pembelian</p>
                <h2 class="text-2xl font-mono font-bold text-gray-800 mt-0.5">{{ $purchase->purchase_number }}</h2>
            </div>
            <div>
                @if ($purchase->status === 'completed')
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-green-100 text-green-800">
                        Completed
                    </span>
                @elseif ($purchase->status === 'pending')
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-800">
                        Pending
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-red-100 text-red-800">
                        Cancelled
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-6">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Supplier</p>
                <p class="text-base font-semibold text-gray-800 mt-1">{{ $purchase->supplier->name }}</p>
                <p class="text-xs text-gray-500">{{ $purchase->supplier->phone ?: 'Tidak ada kontak' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Petugas Input</p>
                <p class="text-base font-semibold text-gray-800 mt-1">{{ $purchase->user->name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ $purchase->user->role }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Transaksi</p>
                <p class="text-base font-medium text-gray-800 mt-1">{{ $purchase->purchased_at->format('d F Y') }}</p>
                <p class="text-xs text-gray-500">{{ $purchase->purchased_at->format('H:i') }} WIB</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan</p>
                <p class="text-sm text-gray-600 mt-1">{{ $purchase->notes ?: '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Daftar Barang yang Dibeli</h3>
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
                    @foreach ($purchase->items as $idx => $item)
                    <tr>
                        <td class="px-6 py-4 text-xs text-gray-400">{{ $idx + 1 }}</td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $item->product->sku }}</td>
                        <td class="px-6 py-4 font-medium text-gray-800">
                            <a href="{{ route('products.show', $item->product) }}" class="hover:text-indigo-600">
                                {{ $item->product->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center font-medium">{{ $item->quantity }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-medium text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-gray-800">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right font-semibold">Total Pembelian:</td>
                        <td class="px-6 py-4 text-right font-bold text-lg text-indigo-600">
                            Rp {{ number_format($purchase->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
