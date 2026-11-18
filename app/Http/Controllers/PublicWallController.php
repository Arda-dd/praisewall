<?php

namespace App\Http\Controllers;

use App\Models\Wall;
use Illuminate\View\View;

/**
 * A shareable "wall of love" page, for linking from a website, a profile or an email signature.
 */
class PublicWallController extends Controller
{
    public function __invoke(Wall $wall): View
    {
        $wall->load('published');

        return view('wall.show', [
            'wall' => $wall,
            'feed' => $wall->toPublicArray(),
        ]);
    }
}
