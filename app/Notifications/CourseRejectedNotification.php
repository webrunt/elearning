<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseRejectedNotification extends Notification implements ShouldQueue
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

        $mail = (new MailMessage)
            ->subject('Course needs changes: '.$this->course->title)
            ->line('Your course was reviewed and sent back to draft status.')
            ->action('Edit course', $url);

        if ($this->course->rejection_feedback) {
            $mail->line('Feedback: '.$this->course->rejection_feedback);
        }

        return $mail;
    }
}
