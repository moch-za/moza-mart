@extends('layouts.app')

@section('title', 'Buat Transaksi Pembelian')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <form method="POST" action="{{ route('purchases.store') }}" id="purchaseForm" class="space-y-6">
        @csrf

        {{-- Supplier & Info Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider mb-4">Informasi Pembelian</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Supplier</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan atau nomor invoice supplier..."
                        class="w-full px-3.5 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Items Table Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800 uppercase tracking-wider">Item Pembelian</h3>
                <button type="button" id="addItemBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-400 font-semibold border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 min-w-[240px]">Produk</th>
                            <th class="px-4 py-3 w-32 text-center">Jumlah</th>
                            <th class="px-4 py-3 w-40 text-right">Harga Beli Satuan (Rp)</th>
                            <th class="px-4 py-3 w-40 text-right">Subtotal (Rp)</th>
                            <th class="px-4 py-3 w-16 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="itemsTbody">
                        {{-- Row template akan ditambahkan via JavaScript --}}
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 font-semibold text-gray-800">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right">Grand Total:</td>
                            <td class="px-4 py-3 text-right text-indigo-600 font-bold text-base" id="grandTotalText">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchases.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition shadow-sm">
                Simpan Pembelian (Pending)
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

        let optionsHtml = '<option value="">Pilih Produk</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-purchase-price="${p.purchase_price}">${p.name} (${p.sku}) - Stok: ${p.stock}</option>`;
        });

        tr.innerHTML = `
            <td class="px-4 py-3">
                <select name="items[${rowIndex}][product_id]" required class="product-select w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    ${optionsHtml}
                </select>
            </td>
            <td class="px-4 py-3 text-center">
                <input type="number" name="items[${rowIndex}][quantity]" value="1" min="1" required class="qty-input w-24 px-3 py-2 border border-gray-200 rounded-lg text-sm text-center focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </td>
            <td class="px-4 py-3 text-right">
                <input type="number" name="items[${rowIndex}][unit_cost]" value="0" min="0" step="100" required class="price-input w-36 px-3 py-2 border border-gray-200 rounded-lg text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-700 subtotal-text">
                Rp 0
            </td>
            <td class="px-4 py-3 text-center">
                <button type="button" class="remove-btn p-1.5 text-red-500 hover:text-red-700 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        const productSelect = tr.querySelector('.product-select');
        const qtyInput = tr.querySelector('.qty-input');
        const priceInput = tr.querySelector('.price-input');
        const removeBtn = tr.querySelector('.remove-btn');

        productSelect.addEventListener('change', function() {
            const selected = productSelect.options[productSelect.selectedIndex];
            if (selected && selected.dataset.purchasePrice) {
                priceInput.value = selected.dataset.purchasePrice;
            }
            updateCalculations();
        });

        qtyInput.addEventListener('input', updateCalculations);
        priceInput.addEventListener('input', updateCalculations);

        removeBtn.addEventListener('click', function() {
            if (tbody.children.length > 1) {
                tr.remove();
                updateCalculations();
            } else {
                alert('Minimal harus ada 1 item.');
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
            const price = parseFloat(tr.querySelector('.price-input')?.value || 0);
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

    // Row awal saat render pertama
    createRow();
</script>
@endpush
@endsection
