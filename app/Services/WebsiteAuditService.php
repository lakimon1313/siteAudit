<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebsiteAuditService
{
    public function __construct(
        private LighthouseAuditService $lighthouseAuditService
    ) {
    }

    public function run(string $url): array
    {
        $transferMs = null;
        $effectiveUrl = null;

        try {
            $response = Http::timeout(20)
                ->withOptions([
                    'allow_redirects' => true,
                    'on_stats' => function (TransferStats $stats) use (&$transferMs, &$effectiveUrl): void {
                        $transferMs = (int) round($stats->getTransferTime() * 1000);
                        $effectiveUrl = (string) $stats->getEffectiveUri();
                    },
                ])
                ->get($url);

            $finalUrl = $effectiveUrl ?: $url;
            $rawHeaders = $this->normalizeHeaders($response->headers());
            $securityHeaders = $this->extractSecurityHeaders($rawHeaders);
            $htmlData = $this->extractHtmlData($response->body());

            $rootUrl = $this->rootUrl($finalUrl);
            $robotsExists = $rootUrl ? $this->resourceExists($rootUrl.'/robots.txt') : null;
            $sitemapExists = $rootUrl ? $this->resourceExists($rootUrl.'/sitemap.xml') : null;

            $isHttps = parse_url($finalUrl, PHP_URL_SCHEME) === 'https';
            $sslInfo = $isHttps
                ? $this->sslInfo((string) parse_url($finalUrl, PHP_URL_HOST))
                : ['valid' => false, 'expires_at' => null];
            $lighthouseData = $this->lighthouseAuditService->run($finalUrl);

            return [
                'status' => 'success',
                'http_status_code' => $response->status(),
                'final_url' => $finalUrl,
                'response_time_ms' => $transferMs,
                'title' => $htmlData['title'],
                'title_length' => $this->length($htmlData['title']),
                'meta_description' => $htmlData['meta_description'],
                'meta_description_length' => $this->length($htmlData['meta_description']),
                'h1_count' => $htmlData['h1_count'],
                'canonical_url' => $htmlData['canonical_url'],
                'robots_txt_exists' => $robotsExists,
                'sitemap_xml_exists' => $sitemapExists,
                'is_https' => $isHttps,
                'ssl_valid' => $sslInfo['valid'],
                'ssl_expires_at' => $sslInfo['expires_at'],
                'security_headers_json' => $securityHeaders,
                'raw_headers_json' => $rawHeaders,
                'error_message' => null,
                ...$lighthouseData,
                'audited_at' => now(),
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'failed',
                'http_status_code' => null,
                'final_url' => $effectiveUrl,
                'response_time_ms' => $transferMs,
                'title' => null,
                'title_length' => null,
                'meta_description' => null,
                'meta_description_length' => null,
                'h1_count' => null,
                'canonical_url' => null,
                'robots_txt_exists' => null,
                'sitemap_xml_exists' => null,
                'is_https' => null,
                'ssl_valid' => null,
                'ssl_expires_at' => null,
                'security_headers_json' => null,
                'raw_headers_json' => null,
                'error_message' => $e->getMessage(),
                ...$this->emptyLighthouseData(),
                'audited_at' => now(),
            ];
        }
    }

    private function emptyLighthouseData(): array
    {
        return [
            'lh_desktop_performance_score' => null,
            'lh_desktop_accessibility_score' => null,
            'lh_desktop_best_practices_score' => null,
            'lh_desktop_seo_score' => null,
            'lh_desktop_fcp_ms' => null,
            'lh_desktop_lcp_ms' => null,
            'lh_desktop_cls' => null,
            'lh_desktop_tbt_ms' => null,
            'lh_desktop_speed_index_ms' => null,
            'lh_desktop_raw_json' => null,
            'lh_mobile_performance_score' => null,
            'lh_mobile_accessibility_score' => null,
            'lh_mobile_best_practices_score' => null,
            'lh_mobile_seo_score' => null,
            'lh_mobile_fcp_ms' => null,
            'lh_mobile_lcp_ms' => null,
            'lh_mobile_cls' => null,
            'lh_mobile_tbt_ms' => null,
            'lh_mobile_speed_index_ms' => null,
            'lh_mobile_raw_json' => null,
            'lighthouse_error_message' => null,
        ];
    }

    private function resourceExists(string $url): ?bool
    {
        try {
            $response = Http::timeout(10)->get($url);

            return $response->successful();
        } catch (Throwable) {
            return null;
        }
    }

    private function extractHtmlData(string $html): array
    {
        if ($html === '') {
            return [
                'title' => null,
                'meta_description' => null,
                'h1_count' => null,
                'canonical_url' => null,
            ];
        }

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        libxml_clear_errors();

        $title = null;
        $titleNode = $dom->getElementsByTagName('title')->item(0);

        if ($titleNode !== null) {
            $title = trim($titleNode->textContent);
            if ($title === '') {
                $title = null;
            }
        }

        $metaDescription = null;
        $canonical = null;

        foreach ($dom->getElementsByTagName('meta') as $meta) {
            $name = strtolower((string) $meta->getAttribute('name'));
            if ($name === 'description') {
                $content = trim((string) $meta->getAttribute('content'));
                $metaDescription = $content !== '' ? $content : null;
                break;
            }
        }

        foreach ($dom->getElementsByTagName('link') as $link) {
            $rel = strtolower((string) $link->getAttribute('rel'));
            if (str_contains($rel, 'canonical')) {
                $href = trim((string) $link->getAttribute('href'));
                $canonical = $href !== '' ? $href : null;
                break;
            }
        }

        return [
            'title' => $title,
            'meta_description' => $metaDescription,
            'h1_count' => $dom->getElementsByTagName('h1')->count(),
            'canonical_url' => $canonical,
        ];
    }

    private function rootUrl(string $url): ?string
    {
        $parts = parse_url($url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $root = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $root .= ':'.$parts['port'];
        }

        return $root;
    }

    private function sslInfo(string $host): array
    {
        if ($host === '') {
            return ['valid' => null, 'expires_at' => null];
        }

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $socket = @stream_socket_client(
            "ssl://{$host}:443",
            $errno,
            $error,
            8,
            STREAM_CLIENT_CONNECT,
            $context,
        );

        if (! is_resource($socket)) {
            return ['valid' => null, 'expires_at' => null];
        }

        $params = stream_context_get_params($socket);
        fclose($socket);

        $certificate = $params['options']['ssl']['peer_certificate'] ?? null;

        if ($certificate === null) {
            return ['valid' => null, 'expires_at' => null];
        }

        $parsed = openssl_x509_parse($certificate);
        $validTo = $parsed['validTo_time_t'] ?? null;

        if (! is_int($validTo)) {
            return ['valid' => null, 'expires_at' => null];
        }

        $expiresAt = Carbon::createFromTimestampUTC($validTo);

        return [
            'valid' => now()->lt($expiresAt),
            'expires_at' => $expiresAt,
        ];
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[$name] = implode(', ', $values);
        }

        return $normalized;
    }

    private function extractSecurityHeaders(array $headers): array
    {
        $lookup = array_change_key_case($headers, CASE_LOWER);

        return [
            'content-security-policy' => $lookup['content-security-policy'] ?? null,
            'strict-transport-security' => $lookup['strict-transport-security'] ?? null,
            'x-frame-options' => $lookup['x-frame-options'] ?? null,
            'x-content-type-options' => $lookup['x-content-type-options'] ?? null,
            'referrer-policy' => $lookup['referrer-policy'] ?? null,
        ];
    }

    private function length(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
