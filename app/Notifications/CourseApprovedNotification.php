<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Course $course,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.courses.edit', $this->course, absolute: true);

        return (new MailMessage)
            ->subject('Your course was approved: '.$this->course->title)
            ->line('Your course has been approved and is now published.')
            ->action('View course', $url);
    }
}
