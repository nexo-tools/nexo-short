<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkRequest;
use App\Models\Link;
use App\Models\User;
use App\Services\ClickStats;
use App\Services\LinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(Request $request, LinkService $links): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('panel.index', [
            'links' => $links->forUser($user),
        ]);
    }

    public function store(StoreLinkRequest $request, LinkService $links): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $links->create(
            $user,
            $request->string('target_url')->toString(),
            $request->filled('custom_slug') ? $request->string('custom_slug')->toString() : null,
        );

        return redirect()->route('panel')->with('status', __('Short link created.'));
    }

    public function deactivate(Request $request, Link $link, LinkService $links): RedirectResponse
    {
        // A user can only deactivate their own links.
        abort_unless($link->user_id === $request->user()?->id, 403);

        $links->deactivate($link);

        return redirect()->route('panel');
    }

    public function stats(Request $request, Link $link, ClickStats $stats): View
    {
        abort_unless($link->user_id === $request->user()?->id, 403);

        $excludeBots = $request->boolean('exclude_bots');

        return view('panel.stats', [
            'link' => $link,
            'excludeBots' => $excludeBots,
            'stats' => $stats->forLink($link, $excludeBots),
        ]);
    }
}
