<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\View\View;

/**
 * Turns a testimonial into an image for social media. The image is drawn in the browser.
 */
class ShareImageController extends Controller
{
    public function __invoke(Testimonial $testimonial): View
    {
        $wall = $testimonial->wall;

        return view('testimonials.image', [
            'testimonial' => $testimonial,
            'data' => [
                'body' => $testimonial->body,
                'rating' => $testimonial->rating,
                'name' => $testimonial->author_name,
                'title' => $testimonial->author_title,
                'avatar' => $testimonial->avatarUrl(),
                'initials' => $testimonial->initials(),
                'wall' => $wall->name,
                'logo' => $wall->logoUrl(),
                'accent' => $wall->accent_color,
                'filename' => 'testimonial-'.$testimonial->id,
            ],
        ]);
    }
}
