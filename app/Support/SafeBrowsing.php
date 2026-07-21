<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Google Safe Browsing Lookup API check at link creation (ADR-005 §4). Runs
 * server-side (not the browser surface) and is env-optional: with no key the
 * check is disabled and creation proceeds. A flagged URL is always rejected; on
 * an API error the behavior follows the fail-open (default) / fail-closed flag.
 */
class SafeBrowsing
{
    private const ENDPOINT = 'https://safebrowsing.googleapis.com/v4/threatMatches:find';

    /** True if the URL may be created. */
    public function allows(string $url): bool
    {
        $key = (string) config('nexo.safe_browsing.key');

        // Disabled without a key (self-host default), and only http/https targets
        // are checked (other schemes are already rejected by LinkTargetUrl).
        if ($key === '' || ! preg_match('#^https?://#i', $url)) {
            return true;
        }

        try {
            $response = Http::timeout(4)->post(self::ENDPOINT.'?key='.$key, [
                'client' => ['clientId' => 'nexo-short', 'clientVersion' => '1.0'],
                'threatInfo' => [
                    'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE', 'POTENTIALLY_HARMFUL_APPLICATION'],
                    'platformTypes' => ['ANY_PLATFORM'],
                    'threatEntryTypes' => ['URL'],
                    'threatEntries' => [['url' => $url]],
                ],
            ]);

            if ($response->failed()) {
                return $this->onError('HTTP '.$response->status());
            }

            // A non-empty `matches` array means the URL is flagged → reject.
            return empty($response->json('matches'));
        } catch (Throwable $e) {
            return $this->onError($e->getMessage());
        }
    }

    /** Fail-open (default) or fail-closed on API error, per config. */
    private function onError(string $reason): bool
    {
        Log::warning('Safe Browsing check failed: '.$reason);

        return ! (bool) config('nexo.safe_browsing.fail_closed');
    }
}
