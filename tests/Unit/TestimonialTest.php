<?php

namespace Tests\Unit;

use App\Models\Testimonial;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TestimonialTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function names(): array
    {
        return [
            'first and last' => ['Maya Kaya', 'MK'],
            'middle names are skipped' => ['Ana Maria Lopez Garcia', 'AG'],
            'one word' => ['Cher', 'C'],
            'non-latin letters' => ['Şule Çelik', 'ŞÇ'],
            'extra spaces' => ['  ada   lovelace ', 'AL'],
        ];
    }

    #[DataProvider('names')]
    public function test_initials(string $name, string $initials): void
    {
        $this->assertSame($initials, (new Testimonial(['author_name' => $name]))->initials());
    }
}
