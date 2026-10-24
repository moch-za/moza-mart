<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAndTransactionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Category $category;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'  => 'admin',
            'email' => 'admin@test.com',
        ]);

        $this->staff = User::factory()->create([
            'role'  => 'staff',
            'email' => 'staff@test.com',
        ]);

        $this->category = Category::create([
            'name'        => 'Elektronik',
            'description' => 'Barang elektronik',
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'PT Distributor Utama',
            'phone' => '08123456789',
            'email' => 'supplier@test.com',
        ]);

        $this->product = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'TST-001',
            'name'           => 'Mouse Wireless',
            'purchase_price' => 50000,
            'selling_price'  => 75000,
            'stock'          => 20,
            'minimum_stock'  => 5,
        ]);
    }

    public function test_admin_can_access_dashboard_and_stats(): void
    {
        $this->product->update(['stock' => 2]); // di bawah minimum_stock (5)

        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Moza-Mart');
        $response->assertSee('Mouse Wireless');
    }

    public function test_staff_cannot_access_categories_management(): void
    {
        $response = $this->actingAs($this->staff)->get('/categories');
        $response->assertForbidden();
    }

    public function test_admin_can_create_new_category_and_product(): void
    {
        $catResponse = $this->actingAs($this->admin)->post('/categories', [
            'name'        => 'Aksesoris PC',
            'description' => 'Peralatan pendukung PC',
        ]);
        $catResponse->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Aksesoris PC']);

        $newCat = Category::where('name', 'Aksesoris PC')->first();

        $prodResponse = $this->actingAs($this->admin)->post('/products', [
            'category_id'    => $newCat->id,
            'sku'            => 'KEY-001',
            'name'           => 'Keyboard Mechanical',
            'purchase_price' => 150000,
            'selling_price'  => 250000,
            'stock'          => 10,
            'minimum_stock'  => 2,
        ]);
        $prodResponse->assertRedirect('/products');
        $this->assertDatabaseHas('products', ['sku' => 'KEY-001']);
    }

    public function test_purchase_creation_does_not_immediately_add_stock_until_confirmed(): void
    {
        $initialStock = $this->product->stock; // 20

        $response = $this->actingAs($this->staff)->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'notes'       => 'Pemesanan awal',
            'items'       => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 10,
                    'unit_cost'  => 50000,
                ],
            ],
        ]);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $this->supplier->id,
            'status'      => 'pending',
            'total'       => 500000,
        ]);

        // Stok tidak berubah saat pending
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->stock);

        $purchase = \App\Models\Purchase::first();

        // Staff tidak berhak konfirmasi (hanya admin)
        $unauthConfirm = $this->actingAs($this->staff)->post("/purchases/{$purchase->id}/confirm");
        $unauthConfirm->assertForbidden();

        // Admin melakukan konfirmasi
        $authConfirm = $this->actingAs($this->admin)->post("/purchases/{$purchase->id}/confirm");
        $authConfirm->assertRedirect();

        // Stok sekarang harus bertambah 10 menjadi 30
        $this->product->refresh();
        $this->assertEquals(30, $this->product->stock);
        $this->assertEquals('completed', $purchase->fresh()->status);
    }

    public function test_sale_immediately_deducts_stock_and_cannot_exceed_available_stock(): void
    {
        $this->product->update(['stock' => 5]);

        // Jual 3 unit -> Berhasil, sisa 2
        $response = $this->actingAs($this->staff)->post('/sales', [
            'notes' => 'Pelanggan Walk-in',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 3,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals(2, $this->product->stock);

        // Jual 5 unit padahal sisa 2 -> Harus gagal dengan validation error
        $failResponse = $this->actingAs($this->staff)->post('/sales', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 5,
                ],
            ],
        ]);

        $failResponse->assertSessionHasErrors('items');
        $this->product->refresh();
        $this->assertEquals(2, $this->product->stock); // stok tetap aman
    }

    public function test_admin_can_cancel_sale_and_restores_stock(): void
    {
        $this->product->update(['stock' => 10]);

        $saleService = app(SaleService::class);
        $sale = $saleService->create([
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 4],
            ],
        ], $this->staff);

        $this->product->refresh();
        $this->assertEquals(6, $this->product->stock);

        // Admin membatalkan penjualan
        $cancelResponse = $this->actingAs($this->admin)->post("/sales/{$sale->id}/cancel");
        $cancelResponse->assertRedirect();

        $this->product->refresh();
        $this->assertEquals(10, $this->product->stock); // stok pulih kembali
        $this->assertEquals('cancelled', $sale->fresh()->status);
    }

    public function test_cannot_delete_product_with_existing_transactions(): void
    {
        $saleService = app(SaleService::class);
        $saleService->create([
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ], $this->staff);

        // Admin coba hapus produk yang punya transaksi
        $deleteResponse = $this->actingAs($this->admin)->delete("/products/{$this->product->id}");
        $deleteResponse->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $this->product->id]);
    }
}
