<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
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
    'lh_desktop_tbt_ms',
    'lh_desktop_speed_index_ms',
    'lh_desktop_cls',
    'lh_desktop_raw_json',
    'lh_mobile_performance_score',
    'lh_mobile_accessibility_score',
    'lh_mobile_best_practices_score',
    'lh_mobile_seo_score',
    'lh_mobile_fcp_ms',
    'lh_mobile_lcp_ms',
    'lh_mobile_tbt_ms',
    'lh_mobile_speed_index_ms',
    'lh_mobile_cls',
    'lh_mobile_raw_json',
    'lighthouse_error_message',
    'audited_at',
])]
class Audit extends Model
{
    protected function casts(): array
    {
        return [
            'http_status_code' => 'integer',
            'response_time_ms' => 'integer',
            'title_length' => 'integer',
            'meta_description_length' => 'integer',
            'h1_count' => 'integer',
            'robots_txt_exists' => 'boolean',
            'sitemap_xml_exists' => 'boolean',
            'is_https' => 'boolean',
            'ssl_valid' => 'boolean',
            'ssl_expires_at' => 'datetime',
            'security_headers_json' => 'array',
            'raw_headers_json' => 'array',
            'lh_desktop_performance_score' => 'integer',
            'lh_desktop_accessibility_score' => 'integer',
            'lh_desktop_best_practices_score' => 'integer',
            'lh_desktop_seo_score' => 'integer',
            'lh_desktop_fcp_ms' => 'integer',
            'lh_desktop_lcp_ms' => 'integer',
            'lh_desktop_tbt_ms' => 'integer',
            'lh_desktop_speed_index_ms' => 'integer',
            'lh_desktop_cls' => 'float',
            'lh_desktop_raw_json' => 'array',
            'lh_mobile_performance_score' => 'integer',
            'lh_mobile_accessibility_score' => 'integer',
            'lh_mobile_best_practices_score' => 'integer',
            'lh_mobile_seo_score' => 'integer',
            'lh_mobile_fcp_ms' => 'integer',
            'lh_mobile_lcp_ms' => 'integer',
            'lh_mobile_tbt_ms' => 'integer',
            'lh_mobile_speed_index_ms' => 'integer',
            'lh_mobile_cls' => 'float',
            'lh_mobile_raw_json' => 'array',
            'audited_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
