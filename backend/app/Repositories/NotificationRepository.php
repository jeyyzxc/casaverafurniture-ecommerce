<?php

namespace App\Repositories;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationRepository
{
    
    public function getUserNotifications(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $user->notifications()->orderBy('created_at', 'desc');

        if (isset($filters['type'])) {
            $query->where('type', 'LIKE', '%' . $filters['type'] . '%');
        }

        if (isset($filters['read'])) {
            if ($filters['read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        return $query->paginate($perPage);
    }

    public function getAdminNotifications(int $adminId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = AdminNotification::where(function($q) use ($adminId) {
            $q->where('admin_id', $adminId)
              ->orWhereNull('admin_id'); 
        })
        ->where('is_dismissed', false)
        ->orderBy('created_at', 'desc');

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['read'])) {
            $query->where('is_read', $filters['read']);
        }

        return $query->paginate($perPage);
    }

    public function getRecentUserNotifications(User $user, int $limit = 10): Collection
    {
        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentAdminNotifications(int $adminId, int $limit = 10): Collection
    {
        return AdminNotification::where(function($q) use ($adminId) {
            $q->where('admin_id', $adminId)
              ->orWhereNull('admin_id');
        })
        ->where('is_dismissed', false)
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
    }

    public function getUserUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function getAdminUnreadCount(int $adminId): int
    {
        return AdminNotification::where(function($q) use ($adminId) {
            $q->where('admin_id', $adminId)
              ->orWhereNull('admin_id');
        })
        ->where('is_read', false)
        ->where('is_dismissed', false)
        ->count();
    }

    public function markUserNotificationAsRead(User $user, string $notificationId): bool
    {
        try {
            $notification = $user->notifications()->where('id', $notificationId)->first();
            
            if (!$notification) {
                return false;
            }

            $notification->markAsRead();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark user notification as read', [
                'user_id' => $user->id,
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function markAdminNotificationAsRead(int $adminId, int $notificationId): bool
    {
        try {
            $notification = AdminNotification::where('id', $notificationId)
                ->where(function($q) use ($adminId) {
                    $q->where('admin_id', $adminId)
                      ->orWhereNull('admin_id');
                })
                ->first();

            if (!$notification) {
                return false;
            }

            $notification->markAsRead();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to mark admin notification as read', [
                'admin_id' => $adminId,
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function markAllUserNotificationsAsRead(User $user): int
    {
        try {
            return DB::transaction(function() use ($user) {
                return $user->unreadNotifications()->update(['read_at' => now()]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to mark all user notifications as read', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    public function markAllAdminNotificationsAsRead(int $adminId): int
    {
        try {
            return DB::transaction(function() use ($adminId) {
                return AdminNotification::where(function($q) use ($adminId) {
                    $q->where('admin_id', $adminId)
                      ->orWhereNull('admin_id');
                })
                ->where('is_read', false)
                ->where('is_dismissed', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to mark all admin notifications as read', [
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    public function deleteUserNotification(User $user, string $notificationId): bool
    {
        try {
            $notification = $user->notifications()->where('id', $notificationId)->first();
            
            if (!$notification) {
                return false;
            }

            return $notification->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete user notification', [
                'user_id' => $user->id,
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function dismissAdminNotification(int $adminId, int $notificationId): bool
    {
        try {
            $notification = AdminNotification::where('id', $notificationId)
                ->where(function($q) use ($adminId) {
                    $q->where('admin_id', $adminId)
                      ->orWhereNull('admin_id');
                })
                ->first();

            if (!$notification) {
                return false;
            }

            return $notification->update(['is_dismissed' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to dismiss admin notification', [
                'admin_id' => $adminId,
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
