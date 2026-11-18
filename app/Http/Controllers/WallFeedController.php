<?php

namespace App\Http\Controllers;

use App\Models\Wall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The JSON the embed widget reads. Only approved testimonials, never email addresses.
 */
class WallFeedController extends Controller
{
    public function __invoke(Request $request, Wall $wall): JsonResponse
    {
        $response = response()->json($wall->toPublicArray())
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=60');

        $response->setEtag(md5($response->getContent()));
        $response->isNotModified($request);

        return $response;
    }
}
