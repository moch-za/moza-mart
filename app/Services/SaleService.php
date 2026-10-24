<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Buat transaksi penjualan baru secara atomik.
     * Stok dikurangi langsung saat transaksi sukses.
     */
    public function create(array $data, User $user): Sale
    {
        return DB::transaction(function () use ($data, $user) {
            $sale = Sale::create([
                'user_id'     => $user->id,
                'sale_number' => $this->generateNumber(),
                'total'       => 0,
                'status'      => 'completed',
                'notes'       => $data['notes'] ?? null,
                'sold_at'     => now(),
            ]);

            $total = 0;

            // Gabung item duplikat berdasarkan product_id
            $mergedItems = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn ($group, $productId) => [
                    'product_id' => (int) $productId,
                    'quantity'   => $group->sum('quantity'),
                ]);

            // Urutkan per product_id untuk mengurangi risiko deadlock
            $sortedItems = $mergedItems->sortBy('product_id');

            foreach ($sortedItems as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty     = (int) $item['quantity'];

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => "Quantity untuk produk {$product->name} harus lebih dari 0.",
                    ]);
                }

                if ($product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup. Tersedia: {$product->stock}, diminta: {$qty}.",
                    ]);
                }

                $subtotal = $product->selling_price * $qty;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $product->selling_price, // ambil dari DB, bukan browser
                    'subtotal'   => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * Batalkan transaksi penjualan.
     * Stok dikembalikan ke produk.
     * Hanya Admin (dijaga di route/controller).
     */
    public function cancel(Sale $sale): void
    {
        if ($sale->isCancelled()) {
            throw new \RuntimeException('Transaksi ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items()->with('product')->get() as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            $sale->update(['status' => 'cancelled']);
        });
    }

    /**
     * Generate nomor transaksi unik: SL-YYYYMMDD-XXXX (sequential per hari).
     */
    private function generateNumber(): string
    {
        $prefix = 'SL-' . now()->format('Ymd') . '-';
        $last   = Sale::where('sale_number', 'like', $prefix . '%')
            ->orderByDesc('sale_number')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->sale_number, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
