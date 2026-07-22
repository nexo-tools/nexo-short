<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * SEO surface for the panel host (ADR-008): the panel is indexable (unlike the
 * short host). robots.txt allows crawling and points at the sitemap; sitemap.xml
 * lists the public panel pages with their language alternates. URLs are derived
 * from the request host (multi-instance — ADR-001).
 */
class PanelSeoController extends Controller
{
    /** @var list<string> */
    private const LOCALES = ['en', 'es', 'pt'];

    /** @var list<string> */
    private const PAGES = ['landing', 'privacy', 'terms'];

    public function robots(): Response
    {
        $body = "User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $urls = '';

        foreach (self::PAGES as $page) {
            $loc = route($page);
            $alternates = '';
            foreach (self::LOCALES as $locale) {
                $href = $loc.'?lang='.$locale;
                $alternates .= '        <xhtml:link rel="alternate" hreflang="'.$locale.'" href="'.e($href)."\"/>\n";
            }
            $urls .= "    <url>\n        <loc>".e($loc)."</loc>\n".$alternates."    </url>\n";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n"
            .$urls
            ."</urlset>\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
