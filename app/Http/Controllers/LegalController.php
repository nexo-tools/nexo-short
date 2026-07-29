<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Privacy and terms (nexo-ui static pages standard). Panel host only — the short
 * host serves redirects, so it never carries these. Content lives in
 * lang/{en,es,pt}/legal.php, the same pattern the help centre uses: whole
 * paragraphs do not belong in the string-by-string translation map.
 */
class LegalController extends Controller
{
    public function privacy(): View
    {
        return $this->page('privacy');
    }

    public function terms(): View
    {
        return $this->page('terms');
    }

    private function page(string $key): View
    {
        /** @var array{title: string, intro: string, sections: array<int, array{h: string, p: string}>} $content */
        $content = __("legal.{$key}");

        return view('legal.show', [
            'title' => $content['title'],
            'description' => $content['intro'],
            'content' => $content,
            'updated' => __('legal.updated'),
            'operator' => config('nexo.legal.operator'),
            'contact' => config('nexo.legal.contact'),
        ]);
    }
}
