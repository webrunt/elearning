<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseSubmittedForReviewNotification extends Notification implements ShouldQueue
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
        $url = route('admin.courses.review.show', $this->course, absolute: true);

        return (new MailMessage)
            ->subject('Course submitted for review: '.$this->course->title)
            ->line('An instructor submitted a course for admin review.')
            ->line('Course: '.$this->course->title)
            ->action('Review course', $url);
    }
}
