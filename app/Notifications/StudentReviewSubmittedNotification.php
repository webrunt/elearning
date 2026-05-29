<?php

namespace App\Notifications;

use App\Models\CourseReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentReviewSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CourseReview $review,
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
        $course = $this->review->course;
        $url = route('admin.reviews.moderation.index', absolute: true);

        return (new MailMessage)
            ->subject('New course review pending moderation')
            ->line('A student left a '.$this->review->rating.'-star review on "'.$course->title.'".')
            ->action('Moderate reviews', $url);
    }
}
