<?php

// Legal pages (privacy + terms), rendered by legal/show.
//
// NOT reviewed by a lawyer. Written to describe accurately what this codebase
// actually does — which is the part an agent can get right — so that a review,
// if the owner wants one, starts from something true rather than from a
// template full of clauses about data the app never touches.
//
// Translation of lang/es/legal.php, which is the source of this content (the
// ecosystem is Spanish-first). Kept by hand: these are paragraphs, so they do
// not go through scripts/generate-translations.mjs.
return [
    'updated' => 'Last updated: 28 July 2026',

    'privacy' => [
        'title' => 'Privacy',
        'intro' => 'This instance of Nexo Short is open source and self-hostable. It is a URL shortener: it collects the minimum needed for a short link to work and for whoever created it to see how many clicks it got. The short domain sets no cookies, there are no third-party trackers, and raw IP addresses are never stored.',
        'sections' => [
            [
                'h' => 'What we store about your account',
                'p' => 'Your name, your email and a hashed version of your password. An account is required to create links; registration may be open or closed depending on the instance. If you sign in with Nexo ID we also store the identifier that service returns so we can recognise you, and your password never passes through here.',
            ],
            [
                'h' => 'What we store about your links',
                'p' => 'For every short link: its code, the destination URL, the account that created it, whether it is active and when it was created. The destination URL is visible to whoever operates this instance, since they answer for what is served from their domain.',
            ],
            [
                'h' => 'What we store for each click',
                'p' => 'When someone opens a short link we store five things: which link and when, the domain of the site they came from (the domain only, and only if the browser sends it), a coarse device type (mobile, desktop or bot), the country reported by the Cloudflare header when available, and an anonymous visitor fingerprint. Nothing else: not the full referring address, not the IP address, not the browser.',
            ],
            [
                'h' => 'The visitor fingerprint, and why it does not identify you',
                'p' => 'The fingerprint is computed from the application key, the current date, the IP address and the browser, and only the result (a sha256) is stored: the IP and the browser are used in memory and never written to disk. Because the date is part of the computation, today\'s fingerprint cannot be compared with tomorrow\'s: it counts unique visitors within a day, it does not follow you.',
            ],
            [
                'h' => 'The short domain uses no cookies',
                'p' => 'The domain that serves the short links sets no cookies, runs no JavaScript and loads no third-party resources. The redirect is temporary (302) and marked as non-cacheable, so deactivating a link takes effect immediately.',
            ],
            [
                'h' => 'Panel cookies',
                'p' => 'Only the ones the panel needs: the session cookie while you are signed in, and the ones that remember the language and the light/dark theme you chose (shared with the rest of the Nexo ecosystem). None of them are advertising or tracking cookies. The session is stored in the database and includes the IP address and browser you signed in with: that is Laravel\'s standard mechanism and it concerns accounts only, never someone clicking a link.',
            ],
            [
                'h' => 'Safety check when a link is created',
                'p' => 'If whoever operates this instance configured a Google Safe Browsing key, the destination URL is sent to that service at creation time to check it is not malicious. With no key configured the check is off and no third-party request leaves the server.',
            ],
            [
                'h' => 'Abuse reports',
                'p' => 'Anyone can report a link without identifying themselves. From a report we store the link code, the reason you picked and the optional note you write. We do not store who sent it: no account, no IP address, no other identifier.',
            ],
            [
                'h' => 'Ecosystem metrics (optional, off by default)',
                'p' => 'Whoever operates the instance can enable an anonymous panel pageview signal towards the Nexo ecosystem hub. It ships disabled, uses no cookies, sends nothing that identifies you, and is never emitted from the short domain.',
            ],
            [
                'h' => 'How long we keep it',
                'p' => 'Clicks are kept for as long as the link exists: deleting a link deletes its clicks, and deleting an account deletes its links along with them. Reports are kept as a moderation record.',
            ],
            [
                'h' => 'Your rights',
                'p' => 'You can request access to your data, its correction or its deletion by writing to whoever operates this instance (the contact is on the help page).',
            ],
            [
                'h' => 'Other instances',
                'p' => 'Nexo Short can be installed on any server. Each installation is independent and responsible for its own data: this policy covers this instance only.',
            ],
        ],
    ],

    'terms' => [
        'title' => 'Terms of use',
        'intro' => 'By using this instance of Nexo Short you accept the following. It is a free service, offered as is.',
        'sections' => [
            [
                'h' => 'What the service is',
                'p' => 'A URL shortener: it turns a long URL into a short link served from this instance\'s domain, and shows whoever created it how many clicks it got, from which sites, with what kind of device and from which countries. Only http and https destinations are accepted.',
            ],
            [
                'h' => 'Your account',
                'p' => 'An account is required to create links. You are responsible for the links created from it and for keeping your password safe. Registration may be closed on this instance; that does not stop links already created from working.',
            ],
            [
                'h' => 'Short links are public by nature',
                'p' => 'Anyone holding the short address can follow it: there is no password on it, and the code is short, so it can be guessed by trying. Do not use short links for private or confidential material. What has to be protected is the destination, not the link.',
            ],
            [
                'h' => 'Misuse',
                'p' => 'Shortening links to malware, phishing, scams, spam, impersonation or illegal content is not allowed, nor is using the service to bypass blocks or disguise a destination of that kind. There are creation limits per account and per IP address, and if whoever operates the instance configured Google Safe Browsing, destinations flagged as dangerous are rejected at creation.',
            ],
            [
                'h' => 'Reports and moderation',
                'p' => 'Anyone can report a link from the short domain itself, without an account. Whoever operates the instance may deactivate any link: it stops redirecting immediately — redirects are never cached — and starts showing the "link not found" page. The link is not deleted, so the moderation record is kept.',
            ],
            [
                'h' => 'Availability',
                'p' => 'The service is offered with no availability guarantee. A short link may stop working, and the service may change or be discontinued. If a link matters to you, keep its original destination too.',
            ],
            [
                'h' => 'Limitation of liability',
                'p' => 'Whoever operates this instance is not liable for damages arising from the use of the service, including links that stop working or lost metrics. The content of the destination site is the responsibility of whoever created the link and of whoever publishes that site.',
            ],
            [
                'h' => 'Free software',
                'p' => 'Nexo Short is distributed under the MIT licence: you can read the code, modify it and host your own instance. The software is provided without warranty, as that licence states.',
            ],
            [
                'h' => 'Changes',
                'p' => 'These terms may change. The date above is the last update.',
            ],
        ],
    ],
];
