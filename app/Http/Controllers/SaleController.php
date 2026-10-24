<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Http\Requests\StoreSaleRequest;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SaleController extends Controller
{
    public function __construct(private SaleService $saleService) {}

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Sale::with('user')->orderByDesc('sold_at');

        // Staff hanya lihat transaksi milik sendiri
        if ($user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->where('sale_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('sold_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('sold_at', '<=', $dateTo);
        }

        $sales = $query->paginate(10)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::with('category')->where('stock', '>', 0)->orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            $sale = $this->saleService->create($request->validated(), auth()->user());
            return redirect()->route('sales.show', $sale)
                ->with('success', "Penjualan {$sale->sale_number} berhasil disimpan.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $this->authorizeSaleAccess($sale);
        $sale->load(['user', 'items.product.category']);
        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        Gate::authorize('cancel-transaction');

        try {
            $this->saleService->cancel($sale);
            return redirect()->route('sales.show', $sale)
                ->with('success', "Penjualan {$sale->sale_number} berhasil dibatalkan. Stok telah dikembalikan.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Staff hanya bisa akses sale milik sendiri.
     */
    private function authorizeSaleAccess(Sale $sale): void
    {
        $user = auth()->user();
        if ($user->isStaff() && $sale->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }
    }
}
