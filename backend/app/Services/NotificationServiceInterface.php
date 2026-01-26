<?php

namespace App\Services;

interface NotificationServiceInterface
{
    /**
     * Create a notification
     */
    public function createNotification(
        string $title,
        string $message,
        string $type,
        string $priority = 'normal',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?string $actionUrl = null
    );

    /**
     * Mark notification as read
     */
    public function markAsRead(int|string $id): bool;

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): int;

    /**
     * Get unread count
     */
    public function getUnreadCount(): int;
}
