<?php

namespace App\Notifications\Forum;

use App\Models\Forum\ForumPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        public ForumPost $post,
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'پست جدید از طرف ' . $this->post->user->full_name,
            'body' => $this->post->content,
            'image' => $this->post->image ? asset($this->post->image) : null,
        ];
    }
}
