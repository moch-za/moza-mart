<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BugHuntingAndEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff1;
    private User $staff2;
    private Category $category;
    private Supplier $supplier;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name'  => 'Admin User',
            'role'  => 'admin',
            'email' => 'admin@test.com',
        ]);

        $this->staff1 = User::factory()->create([
            'name'  => 'Staff 1',
            'role'  => 'staff',
            'email' => 'staff1@test.com',
        ]);

        $this->staff2 = User::factory()->create([
            'name'  => 'Staff 2',
            'role'  => 'staff',
            'email' => 'staff2@test.com',
        ]);

        $this->category = Category::create([
            'name'        => 'Elektronik & Gadget',
            'description' => 'Barang elektronik',
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'Supplier Resmi',
            'phone' => '081299998888',
            'email' => 'supplier@resmi.test',
        ]);

        $this->productA = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'PRD-AAA',
            'name'           => 'Produk A',
            'purchase_price' => 10000,
            'selling_price'  => 15000,
            'stock'          => 10,
            'minimum_stock'  => 3,
        ]);

        $this->productB = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'PRD-BBB',
            'name'           => 'Produk B',
            'purchase_price' => 20000,
            'selling_price'  => 30000,
            'stock'          => 5,
            'minimum_stock'  => 2,
        ]);
    }

    /**
     * TEST 1: Potensi Bug Duplicate Product ID dalam 1 Transaksi Penjualan.
     * Jika kasir mengirim payload:
     * item 1: Produk B qty 3
     * item 2: Produk B qty 3
     * Total diminta = 6, padahal stok Produk B cuma 5!
     * Sistem wajib menggabungkan quantity dan membatalkan transaksi karena stok tidak cukup.
     */
    public function test_bug_sale_duplicate_product_items_exceeding_stock_must_fail(): void
    {
        $response = $this->actingAs($this->staff1)->post('/sales', [
            'items' => [
                ['product_id' => $this->productB->id, 'quantity' => 3],
                ['product_id' => $this->productB->id, 'quantity' => 3],
            ],
        ]);

        // Harus gagal validasi stok
        $response->assertSessionHasErrors('items');

        // Stok Produk B tidak boleh berkurang
        $this->productB->refresh();
        $this->assertEquals(5, $this->productB->stock);
        $this->assertEquals(0, Sale::count());
    }

    /**
     * TEST 2: Potensi Bug Stok Negatif saat Cancel Purchase yang barangnya sudah terjual.
     * Alur:
     * - Beli 10 unit via PO -> Completed (stok awal 10 + 10 = 20)
     * - Jual 15 unit via Sale -> Sisa stok 5
     * - Admin coba batalkan PO 10 unit tadi -> Stok harusnya berkurang 10 (5 - 10 = -5).
     * Sistem HARUS MENOLAK pembatalan PO ini karena stok fisik tidak cukup!
     */
    public function test_bug_cannot_cancel_purchase_if_remaining_stock_is_insufficient(): void
    {
        $purchaseService = app(PurchaseService::class);
        $saleService     = app(SaleService::class);

        // 1. Buat dan konfirmasi PO 10 unit
        $purchase = $purchaseService->create([
            'supplier_id' => $this->supplier->id,
            'items'       => [
                ['product_id' => $this->productA->id, 'quantity' => 10, 'unit_cost' => 10000],
            ],
        ], $this->staff1);
        $purchaseService->confirm($purchase);

        $this->productA->refresh();
        $this->assertEquals(20, $this->productA->stock); // 10 + 10

        // 2. Terjual 15 unit
        $saleService->create([
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 15],
            ],
        ], $this->staff1);

        $this->productA->refresh();
        $this->assertEquals(5, $this->productA->stock); // Sisa 5 unit

        // 3. Admin mencoba cancel PO 10 unit (dibutuhkan 10 unit, tapi fisik cuma ada 5)
        $cancelResponse = $this->actingAs($this->admin)->post("/purchases/{$purchase->id}/cancel");

        // Harus ditolak dan kembali dengan error flash message
        $cancelResponse->assertSessionHas('error');

        // Status PO harus tetap completed, dan stok tidak boleh jadi minus
        $this->assertEquals('completed', $purchase->fresh()->status);
        $this->productA->refresh();
        $this->assertEquals(5, $this->productA->stock);
    }

    /**
     * TEST 3: Potensi Bug Double Cancel / Double Confirm.
     * Membatalkan transaksi yang sudah dibatalkan tidak boleh menambah/mengurangi stok ganda.
     */
    public function test_bug_double_cancellation_prevention(): void
    {
        $saleService = app(SaleService::class);

        // Jual 4 unit dari 10 -> sisa 6
        $sale = $saleService->create([
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 4],
            ],
        ], $this->staff1);

        $this->assertEquals(6, $this->productA->fresh()->stock);

        // Cancel pertama kali oleh Admin
        $saleService->cancel($sale);
        $this->assertEquals(10, $this->productA->fresh()->stock);
        $this->assertEquals('cancelled', $sale->fresh()->status);

        // Cancel kedua kalinya -> Harus melempar exception, stok TIDAK BOLEH jadi 14!
        $this->expectException(\RuntimeException::class);
        $saleService->cancel($sale->fresh());
    }

    /**
     * TEST 4: Potensi Bug Otorisasi Antar Staff (Cross-Staff Data Hijacking).
     * Staff 2 tidak boleh diizinkan melihat detail transaksi milik Staff 1 (harus 403 Forbidden).
     */
    public function test_bug_staff_cannot_view_other_staff_sale_or_purchase(): void
    {
        $saleService = app(SaleService::class);
        $sale1 = $saleService->create([
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 1],
            ],
        ], $this->staff1);

        // Staff 1 bisa melihat transaksinya sendiri
        $respStaff1 = $this->actingAs($this->staff1)->get("/sales/{$sale1->id}");
        $respStaff1->assertOk();

        // Staff 2 mencoba mengintip transaksi Staff 1 via URL ID
        $respStaff2 = $this->actingAs($this->staff2)->get("/sales/{$sale1->id}");
        $respStaff2->assertForbidden();

        // Admin boleh melihat transaksi siapapun
        $respAdmin = $this->actingAs($this->admin)->get("/sales/{$sale1->id}");
        $respAdmin->assertOk();
    }

    /**
     * TEST 5: Potensi Bug Input Negatif / Nol pada Quantity dan Harga.
     */
    public function test_bug_reject_zero_or_negative_quantities_and_prices(): void
    {
        // 1. Sale dengan quantity 0
        $respZeroQty = $this->actingAs($this->staff1)->post('/sales', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => 0],
            ],
        ]);
        $respZeroQty->assertSessionHasErrors();

        // 2. Sale dengan quantity negatif
        $respNegQty = $this->actingAs($this->staff1)->post('/sales', [
            'items' => [
                ['product_id' => $this->productA->id, 'quantity' => -5],
            ],
        ]);
        $respNegQty->assertSessionHasErrors();

        // 3. Purchase dengan harga beli negatif
        $respNegCost = $this->actingAs($this->staff1)->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'items'       => [
                ['product_id' => $this->productA->id, 'quantity' => 2, 'unit_cost' => -1000],
            ],
        ]);
        $respNegCost->assertSessionHasErrors();
    }

    /**
     * TEST 6: Potensi Bug Manipulasi Stok Langsung dari Form Edit Produk.
     * Brief menyatakan: stok TIDAK BOLEH diedit langsung dari form produk.
     */
    public function test_bug_stock_cannot_be_tampered_via_product_update(): void
    {
        $initialStock = $this->productA->stock; // 10

        // User mencoba inject parameter 'stock' = 9999 pada form edit
        $response = $this->actingAs($this->admin)->put("/products/{$this->productA->id}", [
            'category_id'    => $this->category->id,
            'sku'            => $this->productA->sku,
            'name'           => 'Nama Produk Baru',
            'purchase_price' => 12000,
            'selling_price'  => 18000,
            'minimum_stock'  => 4,
            'stock'          => 9999, // payload injeksi
        ]);

        $response->assertRedirect('/products');

        $this->productA->refresh();
        $this->assertEquals('Nama Produk Baru', $this->productA->name);
        // Stok harus TETAP 10, tidak boleh tertimpa jadi 9999!
        $this->assertEquals($initialStock, $this->productA->stock);
    }

    /**
     * TEST 7: Potensi Bug Self-Account Deletion oleh Admin.
     * Admin tidak boleh dapat menghapus dirinya sendiri (menghindari lockout sistem).
     */
    public function test_bug_admin_cannot_delete_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete("/users/{$this->admin->id}");
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * TEST 8: Potensi Bug SKU Unique Collision pada Update.
     * - Update data produk A tanpa ubah SKU -> Sukses (ignore self)
     * - Update data produk A dengan SKU milik produk B -> Ditolak (duplicate)
     */
    public function test_bug_sku_unique_validation_rules(): void
    {
        // 1. Simpan ulang tanpa ganti SKU -> Harus sukses
        $respSameSku = $this->actingAs($this->admin)->put("/products/{$this->productA->id}", [
            'category_id'    => $this->category->id,
            'sku'            => 'PRD-AAA', // SKU dirinya sendiri
            'name'           => 'Produk A Updated',
            'purchase_price' => 10000,
            'selling_price'  => 15000,
            'minimum_stock'  => 3,
        ]);
        $respSameSku->assertSessionHasNoErrors();
        $respSameSku->assertRedirect('/products');

        // 2. Ganti SKU jadi SKU milik Produk B -> Harus ditolak (duplicate SKU)
        $respDupSku = $this->actingAs($this->admin)->put("/products/{$this->productA->id}", [
            'category_id'    => $this->category->id,
            'sku'            => 'PRD-BBB', // SKU milik produk B!
            'name'           => 'Produk A Nabrak',
            'purchase_price' => 10000,
            'selling_price'  => 15000,
            'minimum_stock'  => 3,
        ]);
        $respDupSku->assertSessionHasErrors('sku');
    }

    /**
     * TEST 9: Potensi Bug Delete Supplier & Category yang masih punya relasi aktif.
     */
    public function test_bug_cascade_integrity_delete_guards(): void
    {
        // Kategori punya produk -> tidak boleh dihapus
        $respDelCat = $this->actingAs($this->admin)->delete("/categories/{$this->category->id}");
        $respDelCat->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);

        // Buat purchase untuk supplier
        $purchaseService = app(PurchaseService::class);
        $purchaseService->create([
            'supplier_id' => $this->supplier->id,
            'items'       => [
                ['product_id' => $this->productA->id, 'quantity' => 1, 'unit_cost' => 10000],
            ],
        ], $this->staff1);

        // Supplier punya purchase -> tidak boleh dihapus
        $respDelSup = $this->actingAs($this->admin)->delete("/suppliers/{$this->supplier->id}");
        $respDelSup->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id]);
    }
}
