<?php

namespace App\Notifications;

use App\Models\Testimonial;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewTestimonial extends Notification
{
    public function __construct(public Testimonial $testimonial) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $testimonial = $this->testimonial;
        $wall = $testimonial->wall;
        $author = $testimonial->author_name.($testimonial->author_title ? ", {$testimonial->author_title}" : '');

        return (new MailMessage)
            ->subject("New testimonial for {$wall->name}")
            ->greeting("{$testimonial->author_name} left a testimonial")
            ->lineIf($testimonial->rating !== null, str_repeat('★', (int) $testimonial->rating).str_repeat('☆', 5 - (int) $testimonial->rating))
            ->line('“'.Str::limit($testimonial->body, 400).'”')
            ->line("— {$author}")
            ->action('Review it', route('walls.show', $wall))
            ->line('It stays off your wall until you approve it.');
    }
}
