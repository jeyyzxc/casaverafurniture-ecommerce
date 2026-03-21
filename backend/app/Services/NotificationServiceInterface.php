<?php

namespace App\Services;

interface NotificationServiceInterface
{
    
    public function createNotification(
        string $title,
        string $message,
        string $type,
        string $priority = 'normal',
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?string $actionUrl = null
    );

    public function markAsRead(int|string $id): bool;

    public function markAllAsRead(): int;

    public function getUnreadCount(): int;
}
