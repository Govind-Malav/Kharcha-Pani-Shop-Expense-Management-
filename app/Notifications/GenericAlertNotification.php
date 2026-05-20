<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GenericAlertNotification extends Notification
{
    use Queueable;

    protected $alertData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $alertData)
    {
        $this->alertData = $alertData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->alertData['title'] ?? 'Alert',
            'message' => $this->alertData['message'] ?? '',
            'url' => $this->alertData['url'] ?? '#',
            'type' => $this->alertData['type'] ?? 'info',
        ];
    }
}
