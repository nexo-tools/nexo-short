<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

// Help center (nexo-ui). FAQ items are translatable: lang/<locale>/help.php holds
// `faqs => [['q' => ..., 'a' => ...], ...]`. The contact target comes from config
// (support URL, else a mailto: on the support email). Panel host only.
class HelpController extends Controller
{
    public function __invoke(): View
    {
        return view('help.index', [
            'faqs' => (array) __('help.faqs'),
            'contactUrl' => config('nexo.support_url') ?: 'mailto:'.config('nexo.support_email', ''),
        ]);
    }
}
