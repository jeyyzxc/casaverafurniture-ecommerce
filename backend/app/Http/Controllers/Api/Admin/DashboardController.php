<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\StockAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get Dashboard Statistics
     */
    public function index(Request $request): JsonResponse
    {
        // Get date range
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        // Basic stats
        // Total orders: Show orders count for the selected period
        // Revenue: Total revenue reflects all successful sales (paid) for the selected period
        $stats = [
            'total_orders' => Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'total_revenue' => Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(), // All pending orders
            'total_customers' => User::count(),
            'new_customers' => User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
            'total_products' => Product::where('status', 'active')->count(),
            'low_stock_products' => Product::where('track_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out_of_stock_products' => Product::where('stock_status', 'out_of_stock')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
        ];

        // Revenue by day for the selected period
        $revenueByDay = Order::select(
                $isPostgres ? DB::raw('created_at::date as date') : DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent orders
        $recentOrders = Order::with(['user:id,first_name,last_name,email'])
            ->select('id', 'order_number', 'user_id', 'customer_name', 'total', 'status', 'payment_status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Order status distribution (all orders, not date-filtered)
        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Top selling products
        $topProducts = Product::select('products.id', 'products.name', 'products.sku', 'products.price', 'products.order_count')
            ->with('primaryImage')
            ->where('status', 'active')
            ->orderBy('order_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'order_count' => $product->order_count,
                    'primary_image' => $product->primaryImage?->image_url ?? '/images/products/placeholder.png',
                ];
            });

        // Stock alerts (Low stock + Out of stock)
        // Instead of relying on StockAlert model which might be incomplete,
        // we fetch products directly that are low on stock or out of stock.
        $stockAlerts = Product::select('id', 'name', 'sku', 'stock_quantity', 'low_stock_threshold')
            ->where('status', 'active')
            ->where('track_inventory', true)
            ->where(function($query) {
                $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                      ->orWhere('stock_quantity', 0)
                      ->orWhere('stock_status', 'out_of_stock');
            })
            ->orderBy('stock_quantity', 'asc') // Show lowest stock first (0 then low)
            ->limit(15)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => null, // Virtual alert
                    'product' => $product
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'revenue_by_day' => $revenueByDay,
                'recent_orders' => $recentOrders,
                'orders_by_status' => $ordersByStatus,
                'top_products' => $topProducts,
                'stock_alerts' => $stockAlerts,
            ],
        ]);
    }

    /**
     * Get Quick Stats for Header/Sidebar
     */
    public function quickStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'pending_orders' => Order::where('status', 'pending')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
                'low_stock_alerts' => Product::where('status', 'active')
                    ->where('track_inventory', true)
                    ->where(function($query) {
                        $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                              ->orWhere('stock_quantity', 0);
                    })->count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
            ],
        ]);
    }
}
