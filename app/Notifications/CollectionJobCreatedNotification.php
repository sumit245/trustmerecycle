<?php

namespace App\Notifications;

use App\Models\CollectionJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CollectionJobCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CollectionJob $job
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
                    ->subject('Collection Truck Dispatched')
                    ->line("A collection truck has been dispatched to your godown '{$this->job->godown->name}'.")
                    ->action('View Details', url('/vendor/dashboard'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'job_id' => $this->job->id,
            'godown_name' => $this->job->godown->name,
            'message' => "A collection truck has been dispatched to your godown. Please prepare for collection.",
            'truck_details' => $this->job->truck_details,
        ];
    }
}

