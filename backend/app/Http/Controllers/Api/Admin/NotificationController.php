<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\NotificationRepository;
use App\Transformers\NotificationTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationRepository $notificationRepository
    ) {}

    /**
     * Get all notifications for the authenticated admin
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin')->id();
            
            $filters = [
                'type' => $request->input('type'),
                'read' => $request->has('read') ? $request->boolean('read') : null,
            ];

            $perPage = $request->input('per_page', 20);
            $notifications = $this->notificationRepository->getAdminNotifications($adminId, $filters, $perPage);

            // Transform notifications for consistent API response
            $transformed = $notifications->getCollection()->map(function ($notification) {
                return NotificationTransformer::transformAdminNotification($notification);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $transformed,
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch admin notifications', [
                'admin_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin')->id();
            $count = $this->notificationRepository->getAdminUnreadCount($adminId);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get admin unread count', [
                'admin_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count',
            ], 500);
        }
    }

    /**
     * Get recent notifications (last 10)
     */
    public function recent(): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin')->id();
            $notifications = $this->notificationRepository->getRecentAdminNotifications($adminId, 10);

            $transformed = NotificationTransformer::transformAdminNotifications($notifications);

            return response()->json([
                'success' => true,
                'data' => $transformed,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch recent admin notifications', [
                'admin_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent notifications',
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin')->id();
            $success = $this->notificationRepository->markAdminNotificationAsRead($adminId, $id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark admin notification as read', [
                'admin_id' => Auth::guard('admin')->id(),
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin')->id();
            $count = $this->notificationRepository->markAllAdminNotificationsAsRead($adminId);

            return response()->json([
                'success' => true,
                'message' => "{$count} notifications marked as read",
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all admin notifications as read', [
                'admin_id' => Auth::guard('admin')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
            ], 500);
        }
    }

    /**
     * Dismiss notification
     */
    public function dismiss(int $id): JsonResponse
    {
        try {
            $adminId = Auth::guard('admin')->id();
            $success = $this->notificationRepository->dismissAdminNotification($adminId, $id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification dismissed',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dismiss admin notification', [
                'admin_id' => Auth::guard('admin')->id(),
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss notification',
            ], 500);
        }
    }
}
