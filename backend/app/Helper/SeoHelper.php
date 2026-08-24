<?php

/*
 * SEO helper functions.
 * Loaded automatically via AppServiceProvider::boot() (glob on app/Helper/*.php).
 */

if (!function_exists('seoSiteName')) {
    function seoSiteName()
    {
        return config('app.name', 'RateMyEstate');
    }
}

if (!function_exists('seoText')) {
    // Clean + truncate free text (or HTML) into a safe meta description.
    function seoText($text, $length = 160)
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$text)));
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $length - 1)) . '…';
    }
}

if (!function_exists('seoJsonLd')) {
    // Render one or more schema.org structures as JSON-LD script tags.
    function seoJsonLd($data)
    {
        if (empty($data)) {
            return '';
        }
        // Allow either a single schema array or a list of schema arrays.
        $isList = array_keys($data) === range(0, count($data) - 1);
        $blocks = $isList ? $data : [$data];

        $out = '';
        foreach ($blocks as $block) {
            if (empty($block)) {
                continue;
            }
            $json = json_encode(
                $block,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
            $out .= '<script type="application/ld+json">' . $json . '</script>' . PHP_EOL;
        }
        return $out;
    }
}

if (!function_exists('seoOrganizationJsonLd')) {
    // Site-wide Organization + WebSite (with SearchAction) structured data.
    function seoOrganizationJsonLd()
    {
        $name = seoSiteName();
        $home = url('/');
        $logo = asset('images/logo.png');

        return seoJsonLd([
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $name,
                'url' => $home,
                'logo' => $logo,
                'description' => 'Australian property and real estate review platform helping buyers make confident decisions.',
                'sameAs' => array_values(array_filter([
                    config('app.facebook_url'),
                    config('app.instagram_url'),
                ])),
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $name,
                'url' => $home,
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => route('general-search') . '?q={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ]);
    }
}

if (!function_exists('seoBreadcrumbJsonLd')) {
    // Build a BreadcrumbList from [ ['name' => ..., 'url' => ...], ... ].
    function seoBreadcrumbJsonLd(array $items)
    {
        if (empty($items)) {
            return '';
        }
        $elements = [];
        foreach (array_values($items) as $i => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'] ?? '',
            ];
            if (!empty($item['url'])) {
                $element['item'] = $item['url'];
            }
            $elements[] = $element;
        }
        return seoJsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ]);
    }
}
