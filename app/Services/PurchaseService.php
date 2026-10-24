<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * Buat pembelian baru (status: pending).
     * Stok BELUM bertambah sampai Admin konfirmasi.
     */
    public function create(array $data, User $user): Purchase
    {
        return DB::transaction(function () use ($data, $user) {
            $purchase = Purchase::create([
                'supplier_id'     => $data['supplier_id'],
                'user_id'         => $user->id,
                'purchase_number' => $this->generateNumber(),
                'total'           => 0,
                'status'          => 'pending',
                'notes'           => $data['notes'] ?? null,
                'purchased_at'    => now(),
            ]);

            $total = 0;

            // Gabung item duplikat berdasarkan product_id
            $mergedItems = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn ($group, $productId) => [
                    'product_id' => (int) $productId,
                    'quantity'   => $group->sum('quantity'),
                    'unit_cost'  => (float) $group->first()['unit_cost'],
                ]);

            foreach ($mergedItems as $item) {
                $product  = Product::findOrFail($item['product_id']);
                $qty      = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        'items' => "Quantity untuk produk {$product->name} harus lebih dari 0.",
                    ]);
                }

                if ($unitCost < 0) {
                    throw ValidationException::withMessages([
                        'items' => "Harga beli untuk produk {$product->name} tidak boleh negatif.",
                    ]);
                }

                $subtotal = $unitCost * $qty;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_cost'  => $unitCost,
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /**
     * Konfirmasi pembelian (pending → completed).
     * Stok bertambah saat dikonfirmasi.
     * Hanya Admin (dijaga di route/controller).
     */
    public function confirm(Purchase $purchase): void
    {
        if (! $purchase->isPending()) {
            throw new \RuntimeException('Hanya purchase dengan status pending yang bisa dikonfirmasi.');
        }

        DB::transaction(function () use ($purchase) {
            // Urutkan per product_id untuk mengurangi risiko deadlock
            $items = $purchase->items()->with('product')->get()->sortBy('product_id');

            foreach ($items as $item) {
                Product::lockForUpdate()->findOrFail($item->product_id)
                    ->increment('stock', $item->quantity);
            }

            $purchase->update(['status' => 'completed']);
        });
    }

    /**
     * Batalkan pembelian.
     * - Jika masih pending: langsung batalkan, stok tidak berubah.
     * - Jika sudah completed: kurangi stok, tolak jika stok tidak cukup.
     * Hanya Admin (dijaga di route/controller).
     */
    public function cancel(Purchase $purchase): void
    {
        if ($purchase->isCancelled()) {
            throw new \RuntimeException('Purchase ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($purchase) {
            if ($purchase->isCompleted()) {
                // Urutkan per product_id untuk mengurangi risiko deadlock
                $items = $purchase->items()->with('product')->get()->sortBy('product_id');

                foreach ($items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);

                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cancel' => "Stok {$product->name} tidak mencukupi untuk membatalkan purchase ini. "
                                . "Stok saat ini: {$product->stock}, dibutuhkan: {$item->quantity}.",
                        ]);
                    }

                    $product->decrement('stock', $item->quantity);
                }
            }

            $purchase->update(['status' => 'cancelled']);
        });
    }

    /**
     * Generate nomor purchase unik: PO-YYYYMMDD-XXXX (sequential per hari).
     */
    private function generateNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last   = Purchase::where('purchase_number', 'like', $prefix . '%')
            ->orderByDesc('purchase_number')
            ->lockForUpdate()
            ->first();

        $seq = $last ? ((int) substr($last->purchase_number, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
