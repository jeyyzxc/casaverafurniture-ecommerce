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
    
    public function sales(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $groupBy = $request->input('group_by', 'day'); 

            $query = Order::where('payment_status', 'paid')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            $isPostgres = DB::connection()->getDriverName() === 'pgsql';

            $dateFormat = match ($groupBy) {
                'week' => $isPostgres
                    ? DB::raw("to_char(created_at, 'IYYY-IW') as period")
                    : DB::raw('YEARWEEK(created_at) as period'),
                'month' => $isPostgres
                    ? DB::raw("to_char(created_at, 'YYYY-MM') as period")
                    : DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                default => $isPostgres
                    ? DB::raw('created_at::date as period')
                    : DB::raw('DATE(created_at) as period'),
            };

            $salesData = $query->clone()
                ->select(
                    $dateFormat,
                    DB::raw('SUM(total) as revenue'),
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('AVG(total) as average_order_value')
                )
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            $totals = [
                'total_revenue' => $query->clone()->sum('total'),
                'total_orders' => $query->clone()->count(),
                'average_order_value' => $query->clone()->avg('total') ?? 0,
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

    public function orders(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $query = Order::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            $ordersByStatus = $query->clone()
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            $ordersByPaymentStatus = $query->clone()
                ->select('payment_status', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_status')
                ->pluck('count', 'payment_status');

            $isPostgres = DB::connection()->getDriverName() === 'pgsql';

            $ordersByDay = $query->clone()
                ->select(
                    $isPostgres ? DB::raw('created_at::date as date') : DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

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

    public function products(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $limit = $request->input('limit', 20);

            $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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

            $productsWithNoSales = Product::where('status', 'active')
                ->whereNotIn('id', function ($query) use ($startDate, $endDate) {
                    $query->select('order_items.product_id')
                        ->from('order_items')
                        ->join('orders', 'order_items.order_id', '=', 'orders.id')
                        ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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

    public function users(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $isPostgres = DB::connection()->getDriverName() === 'pgsql';

            $newUsers = User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->select(
                    $isPostgres ? DB::raw('created_at::date as date') : DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $topCustomers = Order::whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
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
                    'total_new_users' => User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
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

    public function summary(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $stats = [
                'total_revenue' => Order::where('payment_status', 'paid')->sum('total') ?? 0,
                'total_orders' => Order::count(),
                'paid_orders' => Order::where('payment_status', 'paid')->count(),
                'completed_orders' => Order::where('status', 'completed')->count(),
                'average_order_value' => Order::where('payment_status', 'paid')->avg('total') ?? 0,
                'total_customers' => User::count(),
                'new_customers' => User::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->count(),
                'total_products' => Product::where('status', 'active')->count(),
            ];

            $topProduct = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('orders.payment_status', 'paid')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as quantity'), DB::raw('SUM(order_items.total) as revenue'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('quantity', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'top_product' => $topProduct ? [
                        'name' => $topProduct->name,
                        'quantity' => (int) $topProduct->quantity,
                        'revenue' => (float) $topProduct->revenue,
                    ] : null,
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
