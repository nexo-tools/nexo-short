<?php

namespace App\Support;

/**
 * Coarse device class from the User-Agent (ADR-006): mobile | desktop | bot.
 * Deliberately blunt — v1 only needs a rough split, and bots are flagged (not
 * dropped) so the panel can filter them. The UA is used transiently here and
 * never stored.
 */
class DeviceClassifier
{
    private const BOT = '/bot|crawl|spider|slurp|curl|wget|python-requests|http-client|headless|facebookexternalhit|feedfetcher|monitor/i';

    private const MOBILE = '/Mobile|Android|iPhone|iPod|Windows Phone|BlackBerry|Opera Mini/i';

    public static function classify(?string $userAgent): string
    {
        $ua = trim((string) $userAgent);

        if ($ua === '' || preg_match(self::BOT, $ua) === 1) {
            return 'bot';
        }

        if (preg_match(self::MOBILE, $ua) === 1) {
            return 'mobile';
        }

        return 'desktop';
    }
}
