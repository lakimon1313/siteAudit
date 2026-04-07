<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class LighthouseAuditService
{
    public function run(string $url): array
    {
        try {
            $process = new Process([
                'node',
                base_path('scripts/lighthouse-audit.mjs'),
                $url,
            ], base_path(), [
                ...$_ENV,
                ...$_SERVER,
                'CHROME_PATH' => env('CHROME_PATH'),
                'LIGHTHOUSE_CHROME_PATH' => env('LIGHTHOUSE_CHROME_PATH'),
            ]);

            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput()) ?: 'Lighthouse command failed.');
            }

            $output = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);

            $desktop = $this->mapResult($output['desktop'] ?? []);
            $mobile = $this->mapResult($output['mobile'] ?? []);

            return [
                ...$desktop['fields'],
                ...$mobile['fields'],
                'lighthouse_error_message' => $this->combineErrors($desktop['error'], $mobile['error']),
            ];
        } catch (Throwable $e) {
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
                'lighthouse_error_message' => $e->getMessage(),
            ];
        }
    }

    private function mapResult(array $result): array
    {
        $prefix = ($result['formFactor'] ?? null) === 'mobile' ? 'lh_mobile_' : 'lh_desktop_';

        if (($result['ok'] ?? false) !== true) {
            return [
                'fields' => [
                    "{$prefix}performance_score" => null,
                    "{$prefix}accessibility_score" => null,
                    "{$prefix}best_practices_score" => null,
                    "{$prefix}seo_score" => null,
                    "{$prefix}fcp_ms" => null,
                    "{$prefix}lcp_ms" => null,
                    "{$prefix}cls" => null,
                    "{$prefix}tbt_ms" => null,
                    "{$prefix}speed_index_ms" => null,
                    "{$prefix}raw_json" => null,
                ],
                'error' => $result['error'] ?? null,
            ];
        }

        $data = $result['data'] ?? [];

        return [
            'fields' => [
                "{$prefix}performance_score" => $this->toScore($data['performance_score'] ?? null),
                "{$prefix}accessibility_score" => $this->toScore($data['accessibility_score'] ?? null),
                "{$prefix}best_practices_score" => $this->toScore($data['best_practices_score'] ?? null),
                "{$prefix}seo_score" => $this->toScore($data['seo_score'] ?? null),
                "{$prefix}fcp_ms" => $this->toInt($data['fcp_ms'] ?? null),
                "{$prefix}lcp_ms" => $this->toInt($data['lcp_ms'] ?? null),
                "{$prefix}cls" => $this->toFloat($data['cls'] ?? null),
                "{$prefix}tbt_ms" => $this->toInt($data['tbt_ms'] ?? null),
                "{$prefix}speed_index_ms" => $this->toInt($data['speed_index_ms'] ?? null),
                "{$prefix}raw_json" => $data['raw_json'] ?? null,
            ],
            'error' => null,
        ];
    }

    private function combineErrors(?string $desktopError, ?string $mobileError): ?string
    {
        $errors = [];

        if ($desktopError) {
            $errors[] = 'Desktop: '.$desktopError;
        }

        if ($mobileError) {
            $errors[] = 'Mobile: '.$mobileError;
        }

        return $errors === [] ? null : implode(' | ', $errors);
    }

    private function toScore(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) round(((float) $value) * 100);
    }

    private function toInt(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private function toFloat(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return round((float) $value, 3);
    }
}
