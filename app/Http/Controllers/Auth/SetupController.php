<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Creates the admin account on a fresh install. Once an account exists, the page is gone.
 */
class SetupController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return view('auth.setup');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if(User::query()->exists(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create($data);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('walls.create');
    }
}
