<?php

namespace App\Notifications;

use App\Models\CourseReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentReviewModeratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CourseReview $review,
        public bool $approved,
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
        $url = route('catalog.show', $course->slug, absolute: true);

        if ($this->approved) {
            return (new MailMessage)
                ->subject('Your review was published')
                ->line('Your review for "'.$course->title.'" is now visible on the course page.')
                ->action('View course', $url);
        }

        $mail = (new MailMessage)
            ->subject('Your review was not published')
            ->line('Your review for "'.$course->title.'" did not meet our moderation guidelines.');

        if ($this->review->moderation_note) {
            $mail->line('Note: '.$this->review->moderation_note);
        }

        return $mail;
    }
}
