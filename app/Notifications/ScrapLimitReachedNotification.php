<?php

namespace App\Notifications;

use App\Models\Godown;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScrapLimitReachedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Godown $godown
    ) {
        //
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Godown Capacity Limit Reached')
                    ->line("The godown '{$this->godown->name}' has reached its capacity limit.")
                    ->line("Current Stock: {$this->godown->current_stock_mt} MT")
                    ->line("Capacity Limit: {$this->godown->capacity_limit_mt} MT")
                    ->action('View Godown', url('/admin/godowns/' . $this->godown->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'godown_id' => $this->godown->id,
            'godown_name' => $this->godown->name,
            'vendor_name' => $this->godown->vendor->name,
            'current_stock' => $this->godown->current_stock_mt,
            'capacity_limit' => $this->godown->capacity_limit_mt,
            'stock_percentage' => $this->godown->stock_percentage,
            'message' => "Godown '{$this->godown->name}' has reached its capacity limit. Please dispatch a collection truck.",
            'urgency' => $this->godown->current_stock_mt > $this->godown->capacity_limit_mt ? 'high' : 'medium',
        ];
    }
}

