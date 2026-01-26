<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type,
        public string $priority = 'normal',
        public ?string $relatedType = null,
        public ?int $relatedId = null,
        public ?string $actionUrl = null
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'priority' => $this->priority,
            'related_type' => $this->relatedType,
            'related_id' => $this->relatedId,
            'action_url' => $this->actionUrl,
            'icon' => $this->getIconForType($this->type),
            'color' => $this->getColorForType($this->type),
        ];
    }

    /**
     * Get icon for notification type
     */
    private function getIconForType(string $type): string
    {
        return match($type) {
            'order' => 'shopping-cart',
            'product' => 'package',
            'payment' => 'credit-card',
            'promotion' => 'tag',
            'system' => 'bell',
            default => 'bell',
        };
    }

    /**
     * Get color for notification type
     */
    private function getColorForType(string $type): string
    {
        return match($type) {
            'order' => 'blue',
            'product' => 'green',
            'payment' => 'purple',
            'promotion' => 'orange',
            'system' => 'gray',
            default => 'blue',
        };
    }
}
