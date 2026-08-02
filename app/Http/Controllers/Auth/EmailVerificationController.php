<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Email verification for local accounts.
 *
 * Deliberately NOT enforced as middleware on the panel: shortening a link is
 * the whole product and gating it on an unread email would be the app's problem,
 * not the person's. What verification buys is the recovery path that landed
 * with it — a typo in the address means the reset link goes nowhere. SSO
 * accounts arrive already verified from the provider.
 */
class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('panel')
            : view('auth.verify-email');
    }

    /** Signed + throttled by the route; the request class checks id/hash against the user. */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('panel');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->route('panel')->with('status', __('Your email is verified.'));
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('panel');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', __('We have resent the verification link.'));
    }
}
