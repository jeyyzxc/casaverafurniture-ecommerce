<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentStatusHistory;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * List all payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with([
            'order:id,order_number,total',
            'user:id,first_name,last_name,email',
            'paymentMethod:id,name,code',
        ]);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by payment method
        if ($methodId = $request->input('payment_method_id')) {
            $query->where('payment_method_id', $methodId);
        }

        // Filter by date range
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Get single payment
     */
    public function show(Payment $payment): JsonResponse
    {
        $payment->load([
            'order.items',
            'user',
            'paymentMethod',
            'verifiedBy',
            'statusHistory.admin:id,first_name,last_name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    /**
     * Verify payment
     */
    public function verify(Request $request, Payment $payment): JsonResponse
    {
        if (!in_array($payment->status, ['pending', 'awaiting_verification'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or awaiting verification payments can be verified.',
            ], 422);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $previousStatus = $payment->status;

        $payment->update([
            'status' => 'confirmed',
            'verified_at' => now(),
            'verified_by_admin_id' => auth()->id(),
            'verification_notes' => $validated['notes'] ?? null,
        ]);

        // Create status history
        PaymentStatusHistory::create([
            'payment_id' => $payment->id,
            'status' => 'confirmed',
            'previous_status' => $previousStatus,
            'notes' => $validated['notes'] ?? 'Payment verified and confirmed',
            'changed_by_type' => 'admin',
            'admin_id' => auth()->id(),
        ]);

        // Update order payment status
        $payment->order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        ActivityLog::log('verify', 'payments', "Verified payment {$payment->transaction_id}", $payment);

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully.',
            'data' => $payment,
        ]);
    }

    /**
     * Reject payment
     */
    public function reject(Request $request, Payment $payment): JsonResponse
    {
        if (!in_array($payment->status, ['pending', 'awaiting_verification'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or awaiting verification payments can be rejected.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $previousStatus = $payment->status;

        $payment->update([
            'status' => 'failed',
            'failure_reason' => $validated['reason'],
            'failure_code' => 'admin_rejected',
        ]);

        // Create status history
        PaymentStatusHistory::create([
            'payment_id' => $payment->id,
            'status' => 'failed',
            'previous_status' => $previousStatus,
            'notes' => $validated['reason'],
            'changed_by_type' => 'admin',
            'admin_id' => auth()->id(),
        ]);

        ActivityLog::log('reject', 'payments', "Rejected payment {$payment->transaction_id}", $payment);

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected.',
            'data' => $payment,
        ]);
    }

    /**
     * Get payment statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => Payment::count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'awaiting_verification' => Payment::where('status', 'awaiting_verification')->count(),
            'confirmed' => Payment::where('status', 'confirmed')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'total_amount' => Payment::whereIn('status', ['confirmed'])->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
