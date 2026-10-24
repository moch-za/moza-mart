<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── Users ───────────────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'admin',
            'email'    => 'admin@mozamart',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        $owner = User::create([
            'name'     => 'owner',
            'email'    => 'owner@mozamart',
            'password' => Hash::make('ownercantik'),
            'role'     => 'owner',
        ]);

        $staff1 = User::create([
            'name'     => 'staff',
            'email'    => 'staff@mozamart',
            'password' => Hash::make('staff123'),
            'role'     => 'staff',
        ]);

        $staff2 = $staff1; // reuse staff instance for dummy transactions

        // ─── Categories ───────────────────────────────────────────────────────────
        $categories = collect([
            'Elektronik',
            'Pakaian',
            'Makanan & Minuman',
            'Peralatan Rumah Tangga',
            'Alat Tulis',
        ])->map(fn ($name) => Category::create(['name' => $name]));

        // ─── Suppliers ────────────────────────────────────────────────────────────
        $suppliers = collect([
            ['name' => 'PT Sejahtera Jaya',   'phone' => '021-5551234', 'email' => 'info@sejahterajaya.test'],
            ['name' => 'CV Maju Bersama',      'phone' => '031-5559876', 'email' => 'order@majubersama.test'],
            ['name' => 'UD Berkah Mandiri',    'phone' => '022-5554321', 'email' => 'supply@berkahmandiri.test'],
            ['name' => 'PT Global Distribusi', 'phone' => '021-5558765', 'email' => 'sales@globaldist.test'],
            ['name' => 'CV Nusantara Dagang',  'phone' => '024-5556789', 'email' => 'nusantara@dagang.test'],
        ])->map(fn ($data) => Supplier::create($data));

        // ─── Products ─────────────────────────────────────────────────────────────
        $productsData = [
            // Elektronik
            ['category' => 0, 'sku' => 'ELK-001', 'name' => 'Lampu LED 10W',           'purchase_price' => 15000,  'selling_price' => 22000,  'stock' => 50, 'minimum_stock' => 10],
            ['category' => 0, 'sku' => 'ELK-002', 'name' => 'Kabel USB Type-C 1m',     'purchase_price' => 18000,  'selling_price' => 28000,  'stock' => 80, 'minimum_stock' => 15],
            ['category' => 0, 'sku' => 'ELK-003', 'name' => 'Baterai AA (4 pcs)',       'purchase_price' => 12000,  'selling_price' => 18000,  'stock' => 120,'minimum_stock' => 20],
            ['category' => 0, 'sku' => 'ELK-004', 'name' => 'Power Bank 10000mAh',      'purchase_price' => 120000, 'selling_price' => 185000, 'stock' => 25, 'minimum_stock' => 5],
            // Pakaian
            ['category' => 1, 'sku' => 'PKN-001', 'name' => 'Kaos Polos Putih (M)',     'purchase_price' => 25000,  'selling_price' => 45000,  'stock' => 60, 'minimum_stock' => 10],
            ['category' => 1, 'sku' => 'PKN-002', 'name' => 'Kaos Polos Hitam (L)',     'purchase_price' => 25000,  'selling_price' => 45000,  'stock' => 55, 'minimum_stock' => 10],
            ['category' => 1, 'sku' => 'PKN-003', 'name' => 'Celana Chino Beige (32)',  'purchase_price' => 85000,  'selling_price' => 145000, 'stock' => 30, 'minimum_stock' => 5],
            ['category' => 1, 'sku' => 'PKN-004', 'name' => 'Jaket Hoodie Abu-Abu (L)', 'purchase_price' => 120000, 'selling_price' => 195000, 'stock' => 8,  'minimum_stock' => 10], // low stock
            // Makanan & Minuman
            ['category' => 2, 'sku' => 'MKN-001', 'name' => 'Mie Instan Goreng (12pc)','purchase_price' => 22000,  'selling_price' => 32000,  'stock' => 100,'minimum_stock' => 20],
            ['category' => 2, 'sku' => 'MKN-002', 'name' => 'Air Mineral 600ml (24pc)','purchase_price' => 35000,  'selling_price' => 50000,  'stock' => 80, 'minimum_stock' => 15],
            ['category' => 2, 'sku' => 'MKN-003', 'name' => 'Kopi Sachet (10pc)',       'purchase_price' => 18000,  'selling_price' => 27000,  'stock' => 3,  'minimum_stock' => 10], // low stock
            ['category' => 2, 'sku' => 'MKN-004', 'name' => 'Teh Celup (25pc)',         'purchase_price' => 12000,  'selling_price' => 20000,  'stock' => 60, 'minimum_stock' => 10],
            // Peralatan Rumah Tangga
            ['category' => 3, 'sku' => 'PRT-001', 'name' => 'Sapu Ijuk Premium',        'purchase_price' => 20000,  'selling_price' => 35000,  'stock' => 40, 'minimum_stock' => 8],
            ['category' => 3, 'sku' => 'PRT-002', 'name' => 'Ember Plastik 15L',        'purchase_price' => 18000,  'selling_price' => 30000,  'stock' => 35, 'minimum_stock' => 8],
            ['category' => 3, 'sku' => 'PRT-003', 'name' => 'Rak Piring Stainless',     'purchase_price' => 65000,  'selling_price' => 110000, 'stock' => 15, 'minimum_stock' => 5],
            // Alat Tulis
            ['category' => 4, 'sku' => 'ATK-001', 'name' => 'Pulpen Ballpoint (12pc)',  'purchase_price' => 15000,  'selling_price' => 24000,  'stock' => 90, 'minimum_stock' => 15],
            ['category' => 4, 'sku' => 'ATK-002', 'name' => 'Buku Tulis A5 (3pc)',      'purchase_price' => 12000,  'selling_price' => 20000,  'stock' => 70, 'minimum_stock' => 15],
            ['category' => 4, 'sku' => 'ATK-003', 'name' => 'Stapler Medium',           'purchase_price' => 22000,  'selling_price' => 38000,  'stock' => 25, 'minimum_stock' => 5],
            ['category' => 4, 'sku' => 'ATK-004', 'name' => 'Penggaris 30cm',           'purchase_price' => 5000,   'selling_price' => 9000,   'stock' => 60, 'minimum_stock' => 10],
            ['category' => 4, 'sku' => 'ATK-005', 'name' => 'Isolasi Bening 24mm',      'purchase_price' => 4000,   'selling_price' => 7000,   'stock' => 5,  'minimum_stock' => 10], // low stock
        ];

        $products = collect($productsData)->map(function ($data) use ($categories) {
            return Product::create([
                'category_id'    => $categories[$data['category']]->id,
                'sku'            => $data['sku'],
                'name'           => $data['name'],
                'purchase_price' => $data['purchase_price'],
                'selling_price'  => $data['selling_price'],
                'stock'          => $data['stock'],
                'minimum_stock'  => $data['minimum_stock'],
            ]);
        });

        // ─── Purchases (completed) ────────────────────────────────────────────────
        $purchaseData = [
            ['supplier' => 0, 'user' => $admin,  'date' => '-30 days', 'items' => [[0, 20, 15000], [1, 30, 18000]]],
            ['supplier' => 1, 'user' => $staff1, 'date' => '-25 days', 'items' => [[4, 25, 25000], [5, 25, 25000]]],
            ['supplier' => 2, 'user' => $admin,  'date' => '-20 days', 'items' => [[8, 50, 22000], [9, 40, 35000]]],
            ['supplier' => 0, 'user' => $staff2, 'date' => '-15 days', 'items' => [[15, 40, 15000], [16, 30, 12000]]],
            ['supplier' => 3, 'user' => $admin,  'date' => '-10 days', 'items' => [[3, 15, 120000], [7, 20, 120000]]],
            ['supplier' => 1, 'user' => $staff1, 'date' => '-8 days',  'items' => [[12, 20, 18000], [11, 30, 12000]]],
            ['supplier' => 4, 'user' => $admin,  'date' => '-5 days',  'items' => [[13, 20, 18000], [14, 10, 65000]]],
            ['supplier' => 2, 'user' => $staff2, 'date' => '-3 days',  'items' => [[17, 15, 22000], [18, 20, 5000]]],
        ];

        foreach ($purchaseData as $idx => $pd) {
            $seq         = str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $date        = now()->modify($pd['date']);
            $purchaseNum = 'PO-' . $date->format('Ymd') . '-' . $seq;

            $total    = 0;
            $itemRows = [];
            foreach ($pd['items'] as [$prodIdx, $qty, $cost]) {
                $subtotal = $qty * $cost;
                $total   += $subtotal;
                $itemRows[] = [
                    'product_id' => $products[$prodIdx]->id,
                    'quantity'   => $qty,
                    'unit_cost'  => $cost,
                    'subtotal'   => $subtotal,
                ];
            }

            $purchase = Purchase::create([
                'supplier_id'     => $suppliers[$pd['supplier']]->id,
                'user_id'         => $pd['user']->id,
                'purchase_number' => $purchaseNum,
                'total'           => $total,
                'status'          => 'completed',
                'purchased_at'    => $date,
            ]);

            foreach ($itemRows as $row) {
                $purchase->items()->create($row);
            }
        }

        // 2 pending purchases
        $pendingPurchase1 = Purchase::create([
            'supplier_id'     => $suppliers[0]->id,
            'user_id'         => $staff1->id,
            'purchase_number' => 'PO-' . now()->format('Ymd') . '-0009',
            'total'           => 5 * 120000 + 10 * 18000,
            'status'          => 'pending',
            'purchased_at'    => now(),
        ]);
        $pendingPurchase1->items()->createMany([
            ['product_id' => $products[3]->id, 'quantity' => 5,  'unit_cost' => 120000, 'subtotal' => 600000],
            ['product_id' => $products[1]->id, 'quantity' => 10, 'unit_cost' => 18000,  'subtotal' => 180000],
        ]);

        $pendingPurchase2 = Purchase::create([
            'supplier_id'     => $suppliers[2]->id,
            'user_id'         => $admin->id,
            'purchase_number' => 'PO-' . now()->format('Ymd') . '-0010',
            'total'           => 20 * 18000,
            'status'          => 'pending',
            'purchased_at'    => now(),
        ]);
        $pendingPurchase2->items()->create([
            'product_id' => $products[10]->id, 'quantity' => 20, 'unit_cost' => 18000, 'subtotal' => 360000,
        ]);

        // ─── Sales (completed) ────────────────────────────────────────────────────
        $salesData = [
            ['user' => $admin,  'date' => '-28 days', 'items' => [[0, 5,  22000], [2, 10, 18000]]],
            ['user' => $staff1, 'date' => '-25 days', 'items' => [[4, 8,  45000], [5, 6,  45000]]],
            ['user' => $staff2, 'date' => '-22 days', 'items' => [[8, 15, 32000], [9, 10, 50000]]],
            ['user' => $admin,  'date' => '-18 days', 'items' => [[15, 10, 24000], [16, 8, 20000]]],
            ['user' => $staff1, 'date' => '-15 days', 'items' => [[1, 5,  28000], [3, 2, 185000]]],
            ['user' => $admin,  'date' => '-12 days', 'items' => [[11, 5, 20000], [13, 3, 35000]]],
            ['user' => $staff2, 'date' => '-10 days', 'items' => [[6, 4, 145000]]],
            ['user' => $admin,  'date' => '-8 days',  'items' => [[17, 5, 38000], [18, 10, 9000]]],
            ['user' => $staff1, 'date' => '-5 days',  'items' => [[0, 3,  22000], [2, 5,  18000]]],
            ['user' => $staff2, 'date' => '-3 days',  'items' => [[14, 2, 110000]]],
            ['user' => $admin,  'date' => '-2 days',  'items' => [[8, 8,  32000], [9, 5,  50000]]],
            ['user' => $staff1, 'date' => '-1 day',   'items' => [[4, 3,  45000], [16, 5, 20000]]],
            ['user' => $admin,  'date' => 'today',    'items' => [[1, 2,  28000], [15, 4, 24000]]],
            ['user' => $staff2, 'date' => 'today',    'items' => [[8, 5,  32000]]],
            ['user' => $admin,  'date' => 'today',    'items' => [[0, 2,  22000], [2, 3,  18000]]],
        ];

        foreach ($salesData as $idx => $sd) {
            $seq     = str_pad($idx + 1, 4, '0', STR_PAD_LEFT);
            $date    = $sd['date'] === 'today' ? now() : now()->modify($sd['date']);
            $saleNum = 'SL-' . $date->format('Ymd') . '-' . $seq;

            $total    = 0;
            $itemRows = [];
            foreach ($sd['items'] as [$prodIdx, $qty, $price]) {
                $subtotal = $qty * $price;
                $total   += $subtotal;
                $itemRows[] = [
                    'product_id' => $products[$prodIdx]->id,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $subtotal,
                ];
                // Kurangi stok sesuai penjualan
                $products[$prodIdx]->decrement('stock', $qty);
            }

            $sale = Sale::create([
                'user_id'     => $sd['user']->id,
                'sale_number' => $saleNum,
                'total'       => $total,
                'status'      => 'completed',
                'sold_at'     => $date,
            ]);

            foreach ($itemRows as $row) {
                $sale->items()->create($row);
            }
        }
    }
}
