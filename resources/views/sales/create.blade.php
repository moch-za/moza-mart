@extends('layouts.app')

@section('title', 'Kasir / Transaksi Penjualan Baru')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <form method="POST" action="{{ route('sales.store') }}" id="saleForm" class="space-y-6">
        @csrf

        {{-- Notes Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4">Informasi Transaksi</h3>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Nama pelanggan atau keterangan transaksi..."
                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Items Table Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider">Daftar Barang Penjualan</h3>
                <button type="button" id="addItemBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400 font-semibold border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 min-w-[260px]">Produk & Stok Tersedia</th>
                            <th class="px-4 py-3 w-36 text-center">Jumlah Beli</th>
                            <th class="px-4 py-3 w-40 text-right">Harga Jual Satuan (Rp)</th>
                            <th class="px-4 py-3 w-40 text-right">Subtotal (Rp)</th>
                            <th class="px-4 py-3 w-16 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="itemsTbody">
                        {{-- Row template akan ditambahkan via JavaScript --}}
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 font-semibold text-gray-800">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right">Total Penjualan:</td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-bold text-lg" id="grandTotalText">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('sales.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition shadow-sm">
                Proses & Selesaikan Penjualan
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    const productsData = @json($products);
    const tbody = document.getElementById('itemsTbody');
    const addItemBtn = document.getElementById('addItemBtn');
    const grandTotalText = document.getElementById('grandTotalText');

    let rowIndex = 0;

    function createRow() {
        const tr = document.createElement('tr');
        tr.dataset.index = rowIndex;

        let optionsHtml = '<option value="">Pilih Produk (Hanya yang berstok)</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-sell-price="${p.selling_price}" data-stock="${p.stock}">${p.name} (${p.sku}) — Stok: ${p.stock}</option>`;
        });

        tr.innerHTML = `
            <td class="px-4 py-3">
                <select name="items[${rowIndex}][product_id]" required class="product-select w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    ${optionsHtml}
                </select>
                <p class="text-xs text-gray-400 mt-1 stock-hint">-</p>
            </td>
            <td class="px-4 py-3 text-center align-top">
                <input type="number" name="items[${rowIndex}][quantity]" value="1" min="1" max="1" required class="qty-input w-24 px-3 py-2 border border-gray-200 rounded-lg text-sm text-center focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </td>
            <td class="px-4 py-3 text-right align-top font-medium text-gray-700 price-text">
                Rp 0
            </td>
            <td class="px-4 py-3 text-right align-top font-semibold text-gray-800 subtotal-text">
                Rp 0
            </td>
            <td class="px-4 py-3 text-center align-top">
                <button type="button" class="remove-btn p-1.5 text-red-500 hover:text-red-700 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        const productSelect = tr.querySelector('.product-select');
        const qtyInput = tr.querySelector('.qty-input');
        const priceText = tr.querySelector('.price-text');
        const stockHint = tr.querySelector('.stock-hint');
        const removeBtn = tr.querySelector('.remove-btn');

        productSelect.addEventListener('change', function() {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (selected && selected.value) {
                const stock = parseInt(selected.dataset.stock || 0);
                const price = parseFloat(selected.dataset.sellPrice || 0);

                qtyInput.max = stock;
                if (parseInt(qtyInput.value) > stock) {
                    qtyInput.value = stock;
                }
                stockHint.textContent = `Maksimal beli: ${stock} unit`;
                priceText.textContent = 'Rp ' + Math.round(price).toLocaleString('id-ID');
                tr.dataset.unitPrice = price;
            } else {
                qtyInput.max = 1;
                stockHint.textContent = '-';
                priceText.textContent = 'Rp 0';
                tr.dataset.unitPrice = 0;
            }
            updateCalculations();
        });

        qtyInput.addEventListener('input', updateCalculations);

        removeBtn.addEventListener('click', function() {
            if (tbody.children.length > 1) {
                tr.remove();
                updateCalculations();
            } else {
                alert('Minimal harus ada 1 item dalam transaksi.');
            }
        });

        tbody.appendChild(tr);
        rowIndex++;
        updateCalculations();
    }

    function updateCalculations() {
        let total = 0;
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty-input')?.value || 0);
            const price = parseFloat(tr.dataset.unitPrice || 0);
            const subtotal = qty * price;
            total += subtotal;

            const subtotalText = tr.querySelector('.subtotal-text');
            if (subtotalText) {
                subtotalText.textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
            }
        });

        grandTotalText.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
    }

    addItemBtn.addEventListener('click', () => createRow());

    // Row pertama
    createRow();
</script>
@endpush
@endsection
