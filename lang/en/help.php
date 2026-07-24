<?php

// Help center FAQs for Nexo Short (rendered by HelpController). English is the
// source language; es/pt are hand-authored siblings. Answers may contain light HTML.
return [
    'faqs' => [
        [
            'q' => 'What is Nexo Short?',
            'a' => 'Nexo Short is an open source URL shortener. You turn long links into short ones served from a domain you control, and you get privacy-friendly click metrics — clicks, referrers, devices and countries — with no cookies and no third parties.',
        ],
        [
            'q' => 'Is it free and open source?',
            'a' => 'Yes. Nexo Short is free and MIT-licensed: the code is published on GitHub and you can use it without any cost or fees, like the rest of the Nexo ecosystem.',
        ],
        [
            'q' => 'Can I self-host it?',
            'a' => 'Yes. You can deploy Nexo Short on your own server, point it at your own database and serve short links from your own domain. It runs standalone — no dependency on the hosted instance or the rest of the ecosystem.',
        ],
        [
            'q' => 'Do you use cookies or track me?',
            'a' => 'No. The redirect sets no cookies and loads no third-party trackers, and no raw IP addresses are ever stored. Unique visitors are counted with a daily-rotating anonymous fingerprint that cannot be linked across days.',
        ],
        [
            'q' => 'Do I need an account?',
            'a' => 'You need an account to create and manage links from the panel. Sign-ups may be closed on the hosted instance, but self-hosting gives you your own panel with your own accounts.',
        ],
    ],
];
