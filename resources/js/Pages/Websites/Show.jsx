import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Show({ website, latestAudit }) {
    const { post, processing } = useForm({});

    const runAudit = () => {
        post(route('websites.audits.store', website.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Website Audit
                    </h2>
                    <Link
                        href={route('websites.index')}
                        className="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Back to Websites
                    </Link>
                </div>
            }
        >
            <Head title="Website Audit" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <p className="text-sm text-gray-500">Website URL</p>
                        <p className="mt-1 break-all text-lg font-medium text-gray-900">
                            {website.url}
                        </p>

                        <div className="mt-6">
                            <PrimaryButton onClick={runAudit} disabled={processing}>
                                {processing ? 'Running...' : 'Run Audit'}
                            </PrimaryButton>
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="text-lg font-semibold text-gray-900">
                            Latest Audit
                        </h3>

                        {!latestAudit ? (
                            <p className="mt-3 text-sm text-gray-600">
                                No audits yet.
                            </p>
                        ) : (
                            <div className="mt-4 grid gap-3 text-sm text-gray-800 sm:grid-cols-2">
                                <Result label="Status" value={latestAudit.status} />
                                <Result label="Audited At" value={formatDate(latestAudit.audited_at)} />
                                <Result label="HTTP Status" value={latestAudit.http_status_code} />
                                <Result label="Final URL" value={latestAudit.final_url} />
                                <Result label="Response Time (ms)" value={latestAudit.response_time_ms} />
                                <Result label="Title" value={latestAudit.title} />
                                <Result label="Title Length" value={latestAudit.title_length} />
                                <Result label="Meta Description" value={latestAudit.meta_description} />
                                <Result
                                    label="Meta Description Length"
                                    value={latestAudit.meta_description_length}
                                />
                                <Result label="H1 Count" value={latestAudit.h1_count} />
                                <Result label="Canonical URL" value={latestAudit.canonical_url} />
                                <Result label="robots.txt exists" value={boolText(latestAudit.robots_txt_exists)} />
                                <Result label="sitemap.xml exists" value={boolText(latestAudit.sitemap_xml_exists)} />
                                <Result label="HTTPS" value={boolText(latestAudit.is_https)} />
                                <Result label="SSL valid" value={boolText(latestAudit.ssl_valid)} />
                                <Result label="SSL expires at" value={formatDate(latestAudit.ssl_expires_at)} />
                                <Result
                                    label="Security headers"
                                    value={formatJson(latestAudit.security_headers_json)}
                                />
                                <Result label="Raw headers" value={formatJson(latestAudit.raw_headers_json)} />
                                <Result label="Error" value={latestAudit.error_message} />
                            </div>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="text-lg font-semibold text-gray-900">
                            Lighthouse
                        </h3>

                        {!latestAudit ? (
                            <p className="mt-3 text-sm text-gray-600">
                                No audits yet.
                            </p>
                        ) : (
                            <div className="mt-4 space-y-6">
                                <LighthouseSection
                                    title="Desktop"
                                    scorePrefix="lh_desktop"
                                    audit={latestAudit}
                                />
                                <LighthouseSection
                                    title="Mobile"
                                    scorePrefix="lh_mobile"
                                    audit={latestAudit}
                                />
                                <Result
                                    label="Lighthouse Error"
                                    value={latestAudit.lighthouse_error_message}
                                />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function LighthouseSection({ title, scorePrefix, audit }) {
    const items = [
        {
            label: 'Performance',
            value: scoreText(audit[`${scorePrefix}_performance_score`]),
            hint: 'Overall loading + responsiveness score. Target: 90+.',
        },
        {
            label: 'Accessibility',
            value: scoreText(audit[`${scorePrefix}_accessibility_score`]),
            hint: 'Checks usability for all users. Target: 90+.',
        },
        {
            label: 'Best Practices',
            value: scoreText(audit[`${scorePrefix}_best_practices_score`]),
            hint: 'General web quality and safety checks. Target: 90+.',
        },
        {
            label: 'SEO',
            value: scoreText(audit[`${scorePrefix}_seo_score`]),
            hint: 'Search-engine basics for crawlability and metadata. Target: 90+.',
        },
        {
            label: 'FCP (ms)',
            value: audit[`${scorePrefix}_fcp_ms`],
            hint: 'First Contentful Paint: first visible content appears. Good: under 1800 ms.',
        },
        {
            label: 'LCP (ms)',
            value: audit[`${scorePrefix}_lcp_ms`],
            hint: 'Largest Contentful Paint: main content visible. Good: under 2500 ms.',
        },
        {
            label: 'CLS',
            value: audit[`${scorePrefix}_cls`],
            hint: 'Layout shift while loading. Good: under 0.10.',
        },
        {
            label: 'TBT (ms)',
            value: audit[`${scorePrefix}_tbt_ms`],
            hint: 'Total Blocking Time: main thread blocked by long tasks. Good: under 200 ms.',
        },
        {
            label: 'Speed Index (ms)',
            value: audit[`${scorePrefix}_speed_index_ms`],
            hint: 'How fast the page becomes visually complete. Lower is better.',
        },
    ];

    return (
        <div>
            <h4 className="text-base font-semibold text-gray-900">{title}</h4>
            <div className="mt-3 grid gap-3 text-sm text-gray-800 sm:grid-cols-2">
                {items.map((item) => (
                    <Result key={item.label} label={item.label} value={item.value} hint={item.hint} />
                ))}
            </div>
        </div>
    );
}

function Result({ label, value, hint = null }) {
    return (
        <div>
            <p className="text-gray-500">{label}</p>
            <p className="break-all text-gray-900">{value ?? '-'}</p>
            {hint && <p className="mt-1 text-xs text-gray-500">{hint}</p>}
        </div>
    );
}

function boolText(value) {
    if (value === null || value === undefined) {
        return '-';
    }

    return value ? 'Yes' : 'No';
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
}

function formatJson(value) {
    if (!value) {
        return '-';
    }

    return JSON.stringify(value);
}

function scoreText(value) {
    if (value === null || value === undefined) {
        return '-';
    }

    return `${value}/100`;
}
