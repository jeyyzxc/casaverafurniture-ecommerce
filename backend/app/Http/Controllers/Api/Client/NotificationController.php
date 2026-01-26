<?php

namespace App\Http\Controllers\Api\Client;

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
     * Get all notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $filters = [
                'type' => $request->input('type'),
                'read' => $request->has('read') ? $request->boolean('read') : null,
            ];

            $perPage = $request->input('per_page', 20);
            $notifications = $this->notificationRepository->getUserNotifications($user, $filters, $perPage);

            // Transform notifications
            $transformed = $notifications->getCollection()->map(function ($notification) {
                return NotificationTransformer::transformUserNotification($notification);
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
            Log::error('Failed to fetch user notifications', [
                'user_id' => Auth::id(),
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
            $user = Auth::user();
            $count = $this->notificationRepository->getUserUnreadCount($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get unread count', [
                'user_id' => Auth::id(),
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
            $user = Auth::user();
            $notifications = $this->notificationRepository->getRecentUserNotifications($user, 10);

            $transformed = NotificationTransformer::transformUserNotifications($notifications);

            return response()->json([
                'success' => true,
                'data' => $transformed,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch recent notifications', [
                'user_id' => Auth::id(),
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
    public function markAsRead(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $success = $this->notificationRepository->markUserNotificationAsRead($user, $id);

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
            Log::error('Failed to mark notification as read', [
                'user_id' => Auth::id(),
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
            $user = Auth::user();
            $count = $this->notificationRepository->markAllUserNotificationsAsRead($user);

            return response()->json([
                'success' => true,
                'message' => "{$count} notifications marked as read",
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read',
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function delete(string $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $success = $this->notificationRepository->deleteUserNotification($user, $id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete notification', [
                'user_id' => Auth::id(),
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification',
            ], 500);
        }
    }
}
