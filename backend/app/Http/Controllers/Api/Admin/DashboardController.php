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
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now());

        // Basic stats
        // Total orders: Show all orders (not date-filtered) for accurate count
        // Revenue: Use date range for period-specific revenue
        $stats = [
            'total_orders' => Order::count(), // All orders regardless of date
            'total_revenue' => Order::whereBetween('created_at', [$startDate, $endDate])
                ->where('payment_status', 'paid')
                ->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(), // All pending orders
            'total_customers' => User::count(),
            'new_customers' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_products' => Product::where('status', 'active')->count(),
            'low_stock_products' => Product::where('track_inventory', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out_of_stock_products' => Product::where('stock_status', 'out_of_stock')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
        ];

        // Revenue by day (last 30 days)
        $revenueByDay = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
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
                    'primary_image' => $product->primaryImage?->image_path ?? '/images/products/placeholder.png',
                ];
            });

        // Stock alerts
        $stockAlerts = StockAlert::with('product:id,name,sku,stock_quantity')
            ->where('is_acknowledged', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

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
                'low_stock_alerts' => StockAlert::where('is_acknowledged', false)->count(),
                'pending_reviews' => Review::where('status', 'pending')->count(),
            ],
        ]);
    }
}
