<?php

namespace App\Http\Controllers;

use App\Models\Wall;
use Illuminate\View\View;

class EmbedController extends Controller
{
    public function __invoke(Wall $wall): View
    {
        return view('walls.embed', [
            'wall' => $wall,
            'widgetUrl' => asset('widget.js'),
        ]);
    }
}
