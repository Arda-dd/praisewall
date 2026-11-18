<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use App\Models\Wall;
use App\Notifications\NewTestimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class ExtrasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function submission(array $overrides = []): array
    {
        return [
            'rating' => 5,
            'body' => 'It cut our onboarding time in half.',
            'author_name' => 'Maya Kaya',
            'consent' => '1',
            ...$overrides,
        ];
    }

    public function test_the_form_and_wall_speak_the_wall_language(): void
    {
        $wall = Wall::factory()->create(['locale' => 'tr', 'name' => 'Orbit']);
        Testimonial::factory()->for($wall)->approved()->create(['rating' => 4]);

        $this->get(route('collect.show', $wall))
            ->assertOk()
            ->assertSee('<html lang="tr">', false)
            ->assertSee('Puanınız')
            ->assertSee('Yorumu gönder');

        $this->get(route('wall.show', $wall))
            ->assertOk()
            ->assertSee('Orbit hakkında söylenenler')
            ->assertSee('1 yorum');

        $this->get(route('collect.thanks', $wall))->assertSee('Teşekkürler!');
    }

    public function test_errors_on_the_form_are_in_the_wall_language(): void
    {
        $wall = Wall::factory()->create(['locale' => 'de']);

        $this->post(route('collect.store', $wall), $this->submission(['consent' => null, 'author_name' => '']))
            ->assertSessionHasErrors([
                'consent' => 'Bitte bestätigen Sie, dass wir Ihren Erfahrungsbericht zeigen dürfen.',
                'author_name' => 'Bitte füllen Sie dieses Feld aus.',
            ]);
    }

    public function test_every_language_has_every_text(): void
    {
        $english = array_keys(Arr::dot(trans('praisewall', [], 'en')));

        foreach (array_keys(config('praisewall.locales')) as $locale) {
            $keys = array_keys(Arr::dot(trans('praisewall', [], $locale)));
            $this->assertSame([], array_values(array_diff($english, $keys)), "Missing in {$locale}");
        }
    }

    public function test_the_feed_has_a_summary_and_labels(): void
    {
        $wall = Wall::factory()->create(['locale' => 'fr', 'style' => 'elegant', 'name' => 'Orbit']);
        Testimonial::factory()->for($wall)->approved()->create(['rating' => 5]);
        Testimonial::factory()->for($wall)->approved()->create(['rating' => 4]);
        Testimonial::factory()->for($wall)->approved()->create(['rating' => null]);
        Testimonial::factory()->for($wall)->create(['rating' => 1]);

        $this->getJson(route('api.wall', $wall))
            ->assertJsonPath('style', 'elegant')
            ->assertJsonPath('locale', 'fr')
            ->assertJsonPath('summary', ['count' => 3, 'rated' => 2, 'average' => 4.5])
            ->assertJsonPath('labels.read_more', 'Lire la suite')
            ->assertJsonPath('labels.reviews', [':count avis', ':count avis'])
            ->assertJsonPath('labels.about', 'Ce que les gens disent de Orbit');
    }

    public function test_admins_get_an_email_about_new_testimonials(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $wall = Wall::factory()->create();

        $this->post(route('collect.store', $wall), $this->submission());

        Notification::assertSentTo($admin, NewTestimonial::class, function (NewTestimonial $notification) use ($admin) {
            $mail = $notification->toMail($admin);

            return str_contains($mail->subject, $notification->testimonial->wall->name)
                && $mail->actionUrl === route('walls.show', $notification->testimonial->wall);
        });
    }

    public function test_no_email_when_the_wall_turns_it_off(): void
    {
        Notification::fake();
        User::factory()->create();
        $wall = Wall::factory()->create(['notify' => false]);

        $this->post(route('collect.store', $wall), $this->submission());

        Notification::assertNothingSent();
    }

    public function test_a_broken_mail_setup_does_not_lose_the_testimonial(): void
    {
        User::factory()->create();
        $wall = Wall::factory()->create();
        Notification::shouldReceive('send')->andThrow(new RuntimeException('SMTP is down'));

        $this->post(route('collect.store', $wall), $this->submission())->assertRedirect(route('collect.thanks', $wall));

        $this->assertSame(1, $wall->testimonials()->count());
    }

    public function test_the_csv_export_has_every_testimonial_and_defuses_formulas(): void
    {
        $this->actingAs(User::factory()->create());
        $wall = Wall::factory()->create(['name' => 'Orbit']);
        Testimonial::factory()->for($wall)->approved()->create(['author_name' => 'Maya', 'body' => 'Great, "really" great']);
        Testimonial::factory()->for($wall)->create(['author_name' => '=HYPERLINK("http://evil.example")', 'body' => '+1 from me']);

        $response = $this->get(route('walls.export', $wall));
        $csv = $response->streamedContent();

        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('orbit-testimonials-', $response->headers->get('Content-Disposition'));

        $rows = array_map(str_getcsv(...), array_filter(explode("\n", substr($csv, 3))));
        $this->assertCount(3, $rows);
        $this->assertSame('author_name', $rows[0][3]);
        $this->assertSame('Great, "really" great', $rows[1][2]);
        $this->assertSame('\'=HYPERLINK("http://evil.example")', $rows[2][3]);
        $this->assertSame("'+1 from me", $rows[2][2]);
    }

    public function test_the_share_image_page_has_the_testimonial(): void
    {
        $this->actingAs(User::factory()->create());
        $testimonial = Testimonial::factory()->approved()->create(['author_name' => "O'Brien", 'body' => '</script> quoted']);

        $this->get(route('testimonials.image', $testimonial))
            ->assertOk()
            ->assertSee('Download PNG')
            ->assertSee('data-share-image', false)
            ->assertDontSee("O'Brien\"", false)
            ->assertDontSee('</script> quoted', false);
    }

    public function test_the_extras_need_a_login(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->get(route('walls.export', $testimonial->wall))->assertRedirect('/login');
        $this->get(route('testimonials.image', $testimonial))->assertRedirect('/login');
    }
}
