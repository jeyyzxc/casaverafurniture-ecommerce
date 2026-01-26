<?php

namespace App\Transformers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class NotificationTransformer
{
    /**
     * Transform user notification to API format
     */
    public static function transformUserNotification(Model $notification): array
    {
        $data = $notification->data ?? [];
        
        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'system',
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'read' => $notification->read_at !== null,
            'is_read' => $notification->read_at !== null,
            'timestamp' => $notification->created_at->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'link' => $data['action_url'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'icon' => $data['icon'] ?? 'bell',
            'priority' => $data['priority'] ?? 'normal',
            'related_type' => $data['related_type'] ?? null,
            'related_id' => $data['related_id'] ?? null,
        ];
    }

    /**
     * Transform admin notification to API format
     */
    public static function transformAdminNotification(Model $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'read' => $notification->is_read,
            'is_read' => $notification->is_read,
            'timestamp' => $notification->created_at->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'link' => $notification->action_url,
            'action_url' => $notification->action_url,
            'icon' => $notification->icon,
            'priority' => $notification->priority,
            'related_type' => $notification->related_type,
            'related_id' => $notification->related_id,
            'is_dismissed' => $notification->is_dismissed,
            'read_at' => $notification->read_at?->toISOString(),
        ];
    }

    /**
     * Transform collection of user notifications
     */
    public static function transformUserNotifications(Collection $notifications): array
    {
        return $notifications->map(function ($notification) {
            return self::transformUserNotification($notification);
        })->toArray();
    }

    /**
     * Transform collection of admin notifications
     */
    public static function transformAdminNotifications(Collection $notifications): array
    {
        return $notifications->map(function ($notification) {
            return self::transformAdminNotification($notification);
        })->toArray();
    }
}
