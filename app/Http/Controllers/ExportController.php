<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Wall;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads all of a wall's testimonials as CSV, for backups or moving them elsewhere.
 */
class ExportController extends Controller
{
    private const COLUMNS = [
        'status', 'rating', 'body', 'author_name', 'author_title', 'author_email',
        'author_url', 'source', 'featured', 'created_at', 'approved_at',
    ];

    public function __invoke(Wall $wall): StreamedResponse
    {
        $filename = Str::slug($wall->name).'-testimonials-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($wall) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM, so Excel reads the file as UTF-8.
            fputcsv($out, self::COLUMNS, escape: '');

            $wall->testimonials()->oldest()->lazy()->each(function (Testimonial $testimonial) use ($out) {
                fputcsv($out, array_map(self::cell(...), [
                    $testimonial->status->value,
                    $testimonial->rating,
                    $testimonial->body,
                    $testimonial->author_name,
                    $testimonial->author_title,
                    $testimonial->author_email,
                    $testimonial->author_url,
                    $testimonial->source->value,
                    $testimonial->featured ? 'yes' : 'no',
                    $testimonial->created_at?->toIso8601String(),
                    $testimonial->approved_at?->toIso8601String(),
                ]), escape: '');
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Spreadsheets run cells that start with =, +, - or @ as formulas, so those get a leading quote.
     */
    private static function cell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
