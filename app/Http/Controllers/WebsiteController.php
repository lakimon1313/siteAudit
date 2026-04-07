<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Websites/Index', [
            'websites' => $request->user()
                ->websites()
                ->latest()
                ->get(['id', 'url', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Websites/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $request->user()->websites()->create($validated);

        return redirect()->route('websites.index');
    }

    public function show(Request $request, int $website): Response
    {
        $websiteModel = $this->websiteForUser($request, $website);

        return Inertia::render('Websites/Show', [
            'website' => $websiteModel->only(['id', 'url', 'created_at']),
            'latestAudit' => $websiteModel->audits()
                ->latest('audited_at')
                ->first([
                    'id',
                    'website_id',
                    'status',
                    'http_status_code',
                    'final_url',
                    'response_time_ms',
                    'title',
                    'title_length',
                    'meta_description',
                    'meta_description_length',
                    'h1_count',
                    'canonical_url',
                    'robots_txt_exists',
                    'sitemap_xml_exists',
                    'is_https',
                    'ssl_valid',
                    'ssl_expires_at',
                    'security_headers_json',
                    'raw_headers_json',
                    'error_message',
                    'lh_desktop_performance_score',
                    'lh_desktop_accessibility_score',
                    'lh_desktop_best_practices_score',
                    'lh_desktop_seo_score',
                    'lh_desktop_fcp_ms',
                    'lh_desktop_lcp_ms',
                    'lh_desktop_cls',
                    'lh_desktop_tbt_ms',
                    'lh_desktop_speed_index_ms',
                    'lh_mobile_performance_score',
                    'lh_mobile_accessibility_score',
                    'lh_mobile_best_practices_score',
                    'lh_mobile_seo_score',
                    'lh_mobile_fcp_ms',
                    'lh_mobile_lcp_ms',
                    'lh_mobile_cls',
                    'lh_mobile_tbt_ms',
                    'lh_mobile_speed_index_ms',
                    'lighthouse_error_message',
                    'audited_at',
                ]),
        ]);
    }

    private function websiteForUser(Request $request, int $websiteId): Website
    {
        return $request->user()->websites()->findOrFail($websiteId);
    }
}
