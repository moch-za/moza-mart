<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Http\Requests\StorePurchaseRequest;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Purchase::with(['supplier', 'user'])->orderByDesc('purchased_at');

        // Staff hanya lihat transaksi milik sendiri
        if ($user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->where('purchase_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('purchased_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('purchased_at', '<=', $dateTo);
        }

        $purchases = $query->paginate(10)->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::with('category')->orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchaseService->create($request->validated(), auth()->user());

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->purchase_number} berhasil dibuat dengan status pending.");
    }

    public function show(Purchase $purchase)
    {
        $this->authorizePurchaseAccess($purchase);
        $purchase->load(['supplier', 'user', 'items.product.category']);
        return view('purchases.show', compact('purchase'));
    }

    public function confirm(Purchase $purchase)
    {
        Gate::authorize('cancel-transaction'); // reuse admin gate

        try {
            $this->purchaseService->confirm($purchase);
            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Purchase {$purchase->purchase_number} berhasil dikonfirmasi. Stok telah diperbarui.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Purchase $purchase)
    {
        Gate::authorize('cancel-transaction');

        try {
            $this->purchaseService->cancel($purchase);
            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Purchase {$purchase->purchase_number} berhasil dibatalkan.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Staff hanya bisa akses purchase milik sendiri.
     */
    private function authorizePurchaseAccess(Purchase $purchase): void
    {
        $user = auth()->user();
        if ($user->isStaff() && $purchase->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }
    }
}
