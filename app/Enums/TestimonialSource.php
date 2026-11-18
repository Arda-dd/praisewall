<?php

namespace App\Enums;

/**
 * Where a testimonial came from: the public collection form, or added by hand
 * from an email, a review site or a social post.
 */
enum TestimonialSource: string
{
    case Form = 'form';
    case Manual = 'manual';
}
