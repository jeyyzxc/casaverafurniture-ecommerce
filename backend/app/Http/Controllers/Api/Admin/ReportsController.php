<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Get Sales Report
     */
    public function sales(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $groupBy = $request->input('group_by', 'day'); // day, week, month

            $query = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

            // Group by period
            $dateFormat = match ($groupBy) {
                'week' => DB::raw('YEARWEEK(created_at) as period'),
                'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                default => DB::raw('DATE(created_at) as period'),
            };

            $salesData = $query
                ->select(
                    $dateFormat,
                    DB::raw('SUM(total) as revenue'),
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('AVG(total) as average_order_value')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Calculate totals
            $totals = [
                'total_revenue' => $query->sum('total'),
                'total_orders' => $query->count(),
                'average_order_value' => $query->avg('total') ?? 0,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'sales_data' => $salesData,
                    'totals' => $totals,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'group_by' => $groupBy,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales report failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sales report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get Order Report
     */
    public function orders(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $query = Order::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

            // Orders by status
            $ordersByStatus = $query->clone()
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            // Orders by payment status
            $ordersByPaymentStatus = $query->clone()
                ->select('payment_status', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_status')
                ->pluck('count', 'payment_status');

            // Orders by day
            $ordersByDay = $query->clone()
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Recent orders
            $recentOrders = $query->clone()
                ->with(['user:id,first_name,last_name,email'])
                ->select('id', 'order_number', 'user_id', 'customer_name', 'total', 'status', 'payment_status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'orders_by_status' => $ordersByStatus,
                    'orders_by_payment_status' => $ordersByPaymentStatus,
                    'orders_by_day' => $ordersByDay,
                    'recent_orders' => $recentOrders,
                    'total_orders' => $query->count(),
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Order report failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate order report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get Product Performance Report
     */
    public function products(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $limit = $request->input('limit', 20);

            // Top selling products
            $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
                ->where('orders.payment_status', 'paid')
                ->select(
                    'products.id',
                    'products.name',
                    'products.sku',
                    'products.price',
                    DB::raw('SUM(order_items.quantity) as total_quantity_sold'),
                    DB::raw('SUM(order_items.total) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as order_count')
                )
                ->groupBy('products.id', 'products.name', 'products.sku', 'products.price')
                ->orderBy('total_revenue', 'desc')
                ->limit($limit)
                ->get();

            // Products with no sales
            $productsWithNoSales = Product::where('status', 'active')
                ->whereNotIn('id', function ($query) use ($startDate, $endDate) {
                    $query->select('order_items.product_id')
                        ->from('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->where('orders.payment_status', 'paid');
                })
                ->select('id', 'name', 'sku', 'price', 'order_count')
                ->orderBy('name')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'top_products' => $topProducts,
                    'products_with_no_sales' => $productsWithNoSales,
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Product performance report failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate product performance report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get User Activity Report
     */
    public function users(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            // New users
            $newUsers = User::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Top customers by revenue
            $topCustomers = Order::whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
                ->where('orders.payment_status', 'paid')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    DB::raw('COUNT(orders.id) as order_count'),
                    DB::raw('SUM(orders.total) as total_spent')
                )
                ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email')
                ->orderBy('total_spent', 'desc')
                ->limit(20)
                ->get();

            // Customer lifetime value
            $customerLifetimeValue = User::select(
                    'users.id',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.created_at',
                    DB::raw('COUNT(orders.id) as total_orders'),
                    DB::raw('COALESCE(SUM(orders.total), 0) as lifetime_value')
                )
                ->leftJoin('orders', function ($join) {
                    $join->on('users.id', '=', 'orders.user_id')
                        ->where('orders.payment_status', '=', 'paid');
                })
                ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.created_at')
                ->orderBy('lifetime_value', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'new_users' => $newUsers,
                    'top_customers' => $topCustomers,
                    'customer_lifetime_value' => $customerLifetimeValue,
                    'total_new_users' => User::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count(),
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('User activity report failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate user activity report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get Summary Statistics
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            // Sales statistics
            $paidOrders = Order::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                ->where('payment_status', 'paid');

            $allOrders = Order::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);

            $stats = [
                'total_revenue' => $paidOrders->sum('total') ?? 0,
                'total_orders' => $allOrders->count(),
                'paid_orders' => $paidOrders->count(),
                'average_order_value' => $paidOrders->avg('total') ?? 0,
                'total_customers' => User::count(),
                'new_customers' => User::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])->count(),
                'total_products' => Product::where('status', 'active')->count(),
            ];

            // Top product
            $topProduct = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate . ' 23:59:59'])
                ->where('orders.payment_status', 'paid')
                ->select('products.name', DB::raw('SUM(order_items.total) as revenue'))
                ->groupBy('products.name')
                ->orderBy('revenue', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'top_product' => $topProduct ? $topProduct->name : 'N/A',
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Summary report failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summary report.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
