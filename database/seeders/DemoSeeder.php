<?php

namespace Database\Seeders;

use App\Models\Click;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data for the landing screenshots (design.md, "Family": real captures from
 * a LOCAL instance, never production). The point is an honest panel — six links
 * a person would plausibly have shortened and two of them with a fortnight of
 * traffic — so the figures on the landing show the product working instead of a
 * seeded emptiness or, worse, somebody's real links.
 *
 * Deliberately NOT registered in DatabaseSeeder: it is run explicitly
 * (`artisan db:seed --class=DemoSeeder`) by whoever is re-capturing.
 *
 * Everything here is deterministic — same slugs, same shape of traffic on every
 * run — because a screenshot that changes when nothing changed is a diff nobody
 * can review.
 */
class DemoSeeder extends Seeder
{
    use WithoutModelEvents;

    /** The traffic window the captures show. */
    private const DAYS = 14;

    /**
     * Generic destinations: long, real-looking URLs pointing at example domains
     * only. No third party's content, no campaign of anyone's.
     *
     * @var list<array{slug:string, url:string}>
     */
    private const LINKS = [
        ['slug' => 'docs-api', 'url' => 'https://docs.example.com/reference/v2/authentication/access-tokens?utm_source=newsletter&utm_medium=email&utm_campaign=launch'],
        ['slug' => 'post-2026', 'url' => 'https://blog.example.org/2026/07/how-we-cut-our-deploy-time-in-half/?ref=weekly-digest'],
        ['slug' => 'mapa', 'url' => 'https://maps.example.net/place/Centro+de+Convenciones/@-34.6037389,-58.3815704,17z/data=!3m1!4b1'],
        ['slug' => 'guia-pdf', 'url' => 'https://files.example.com/downloads/2026/guia-de-implementacion-v3.pdf'],
        ['slug' => 'demo-video', 'url' => 'https://video.example.com/watch?v=8fJk2mQwErT&list=PL9aBcDeFgHiJkLmNoPqRsTuVwXyZ&index=4'],
        ['slug' => 'tienda', 'url' => 'https://shop.example.com/collections/summer-2026/products/mochila-de-viaje-40l?variant=42917364'],
    ];

    /**
     * Clicks per day for the two measured links, oldest day first. Hand-written
     * rather than random: a shared link peaks the day it goes out and decays,
     * and a chart that says that reads as a product, not as noise.
     *
     * @var array<string, list<int>>
     */
    private const TRAFFIC = [
        'docs-api' => [4, 9, 7, 12, 31, 24, 15, 11, 8, 13, 19, 10, 6, 9],
        'post-2026' => [0, 2, 3, 5, 4, 8, 21, 46, 33, 18, 12, 9, 7, 5],
    ];

    /**
     * Weighted by repetition, not by a flat cycle: a real breakdown has a head
     * and a long tail, and a table where every row shows the same count is the
     * one thing that gives a seeded screenshot away. null = direct traffic.
     *
     * @var list<string|null>
     */
    private const REFERRERS = [
        't.co', 't.co', 't.co', 't.co', 't.co',
        'news.ycombinator.com', 'news.ycombinator.com', 'news.ycombinator.com',
        'linkedin.com', 'linkedin.com',
        'github.com',
        null, null, null,
    ];

    private const DEVICES = ['mobile', 'desktop', 'bot'];

    /** @var list<string> */
    private const COUNTRIES = [
        'AR', 'AR', 'AR', 'AR', 'AR',
        'ES', 'ES', 'ES', 'ES',
        'MX', 'MX', 'MX',
        'BR', 'BR',
        'CL', 'CL',
        'US',
        'CO',
    ];

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo', 'password' => Hash::make('password')],
        );

        // Created in listed order but shown newest-first by the panel, so the
        // list is reversed here: the two links with traffic land at the top,
        // which is where the screenshot crop looks.
        $created = [];
        foreach (array_reverse(self::LINKS) as $i => $link) {
            $at = now()->subDays(self::DAYS)->addHours($i * 5);

            // forceFill, not fill: the timestamps are the whole point of the
            // seeder (a fortnight-old link with a fortnight of clicks) and
            // created_at is not — rightly — in the model's fillable list.
            $created[$link['slug']] = Link::firstOrNew(['slug' => $link['slug']])->forceFill([
                'user_id' => $user->id,
                'target_url' => $link['url'],
                'is_active' => true,
                'created_at' => $at,
                'updated_at' => $at,
            ]);

            $created[$link['slug']]->save();
        }

        foreach (self::TRAFFIC as $slug => $perDay) {
            $this->seedClicks($created[$slug], $perDay);
        }
    }

    /**
     * @param  list<int>  $perDay  clicks for each of the last DAYS days, oldest first
     */
    private function seedClicks(Link $link, array $perDay): void
    {
        $link->clicks()->delete();

        $rows = [];
        $n = 0;

        foreach ($perDay as $offset => $count) {
            $day = now()->subDays(self::DAYS - 1 - $offset)->startOfDay();
            // Some people open the same link twice in a day, so uniques land
            // below total the way they do in production. The pool is per day
            // because the stored hash is per day: it rotates at midnight, and
            // yesterday's visitor is arithmetically a new one today (ADR-006).
            $pool = max(1, (int) ceil($count * 0.72));

            for ($i = 0; $i < $count; $i++, $n++) {
                $visitor = 'demo-'.$link->slug.'-'.($i % $pool).'-'.$day->toDateString();

                $rows[] = [
                    'link_id' => $link->id,
                    'visitor_hash' => hash('sha256', $visitor),
                    'referrer_host' => self::REFERRERS[($n * 3) % count(self::REFERRERS)],
                    // Roughly two thirds mobile, a third desktop, the odd bot —
                    // the shape the real device classifier reports.
                    'device' => self::DEVICES[$n % 9 === 8 ? 2 : ($n % 3 === 0 ? 1 : 0)],
                    'country' => self::COUNTRIES[($n * 5) % count(self::COUNTRIES)],
                    // Spread over working hours so the day looks lived in.
                    'created_at' => $day->copy()->addMinutes(8 * 60 + ($n * 37) % (11 * 60)),
                ];
            }
        }

        Click::insert($rows);
    }
}
