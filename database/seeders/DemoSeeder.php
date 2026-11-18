<?php

namespace Database\Seeders;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Database\Seeder;

/**
 * Sample data for trying Praisewall out: php artisan db:seed --class=DemoSeeder
 * Log in with demo@example.com / password. All people and companies are made up.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo', 'password' => 'password'],
        );

        $wall = Wall::query()->updateOrCreate(['slug' => 'orbit'], [
            'name' => 'Orbit',
            'headline' => 'How has Orbit helped your team?',
            'prompt' => 'What were you using before, and what changed after you switched?',
            'accent_color' => '#6d28d9',
            'thank_you_message' => 'Thank you! It means a lot to our small team. Your testimonial will show up once we have had a look.',
            'collecting' => true,
            'ask_for_rating' => true,
            'ask_for_avatar' => true,
        ]);

        $wall->testimonials()->delete();

        $approved = [
            ['Maya Kaya', 'CTO at Fernway', 5, true, "We moved our whole roadmap into Orbit in an afternoon. Two months later, standups take ten minutes instead of thirty, because everyone can already see what's blocked."],
            ['Daniel Osei', 'Founder, Brightloop', 5, false, 'Finally a project tool my designers actually open without being asked.'],
            ['Lena Fischer', 'Head of Product at Norrin', 5, true, "The timeline view sold it for us. We used to rebuild the same Gantt chart in slides every Friday for leadership. Now I send them a link and get my Friday afternoons back.\n\nSupport answered a question within an hour, on a Sunday."],
            ['Kenji Watanabe', 'Engineering Manager, Tessel', 4, false, 'Fast, keyboard friendly and it stays out of the way. I would love a few more integrations, but the GitHub sync alone was worth switching for.'],
            ['Aisha Rahman', 'Operations Lead at Kindle & Co', 5, false, "I manage 14 freelancers across 5 time zones. Orbit is the first tool where I don't have to chase people for status updates."],
            ['Tomás Rivera', 'Agency owner', 5, false, 'We onboard every new client into a shared Orbit space now. Clients love seeing progress without having to email us.'],
            ['Sophie Laurent', 'COO, Maison Digitale', 5, false, 'Clean, calm and quick. Our team adopted it in a week.'],
            ['Ravi Menon', 'Senior Developer at Quanta', 4, false, 'The API is well documented and the webhooks are reliable. Built our release dashboard on top of it in a day.'],
            ['Elif Demir', 'Product Designer', 5, false, "It's rare that a B2B tool feels this considered. Every small interaction has been thought through."],
        ];

        foreach ($approved as $i => [$name, $title, $rating, $featured, $body]) {
            $wall->testimonials()->create([
                'author_name' => $name,
                'author_title' => $title,
                'rating' => $rating,
                'body' => $body,
                'featured' => $featured,
                'status' => TestimonialStatus::Approved,
                'source' => $i === 5 ? TestimonialSource::Manual : TestimonialSource::Form,
                'approved_at' => now()->subDays($i * 3 + 1),
                'created_at' => now()->subDays($i * 3 + 2),
            ]);
        }

        $pending = [
            ['Noah Becker', 'Founder at Stackline', 5, 'We tried four tools last year. Orbit is the one that stuck. The weekly digest email is my favourite feature.', 'noah@stackline.example'],
            ['Grace Liu', 'Marketing Lead, Pebble', 4, 'Easy to set up and the templates saved us hours. Would love a mobile app!', 'grace@pebble.example'],
            ['Omar Haddad', 'CEO, Lumen Labs', 5, 'Orbit replaced three spreadsheets and a whiteboard for us. The team is noticeably calmer.', null],
        ];

        foreach ($pending as $i => [$name, $title, $rating, $body, $email]) {
            $wall->testimonials()->create([
                'author_name' => $name,
                'author_title' => $title,
                'author_email' => $email,
                'rating' => $rating,
                'body' => $body,
                'status' => TestimonialStatus::Pending,
                'source' => TestimonialSource::Form,
                'created_at' => now()->subHours($i * 5 + 2),
            ]);
        }

        $wall->testimonials()->create([
            'author_name' => 'Cheap Followers',
            'body' => 'Buy 10,000 followers today!!! Visit our site for the best deals.',
            'status' => TestimonialStatus::Rejected,
            'source' => TestimonialSource::Form,
            'created_at' => now()->subDays(4),
        ]);
    }
}
