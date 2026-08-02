<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * "I forgot my password", for local accounts.
 *
 * This tool shipped without it: a self-hosted instance in local auth mode had
 * no recovery path at all, so a forgotten password meant the operator editing
 * the database by hand. Gated to local mode with the rest of credential auth —
 * on the hosted instance the account lives in Nexo ID and recovery belongs
 * there.
 */
class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Always the same answer, whether or not the address exists: the reply
        // to this form must not tell a stranger which emails have accounts.
        return back()->with('status', __('If that email has an account, we sent it a link.'));
    }
}
