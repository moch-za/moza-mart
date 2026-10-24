<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Hitung hanya transaksi completed
        $todaySalesTotal = Sale::where('status', 'completed')
            ->whereDate('sold_at', today())
            ->sum('total');

        $monthSalesTotal = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        $todaySalesCount = Sale::where('status', 'completed')
            ->whereDate('sold_at', today())
            ->count();

        $monthSalesCount = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->count();

        $totalProducts  = Product::count();
        $totalSuppliers = \App\Models\Supplier::count();

        $pendingPurchases = Purchase::where('status', 'pending')->count();

        $lowStockProducts = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->with('category')
            ->orderBy('stock')
            ->get();

        $recentSales = Sale::with('user')
            ->where('status', 'completed')
            ->orderByDesc('sold_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'todaySalesTotal',
            'monthSalesTotal',
            'todaySalesCount',
            'monthSalesCount',
            'totalProducts',
            'totalSuppliers',
            'pendingPurchases',
            'lowStockProducts',
            'recentSales',
        ));
    }
}
