<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table): void {
            $table->unsignedTinyInteger('lh_desktop_performance_score')->nullable()->after('error_message');
            $table->unsignedTinyInteger('lh_desktop_accessibility_score')->nullable()->after('lh_desktop_performance_score');
            $table->unsignedTinyInteger('lh_desktop_best_practices_score')->nullable()->after('lh_desktop_accessibility_score');
            $table->unsignedTinyInteger('lh_desktop_seo_score')->nullable()->after('lh_desktop_best_practices_score');
            $table->unsignedInteger('lh_desktop_fcp_ms')->nullable()->after('lh_desktop_seo_score');
            $table->unsignedInteger('lh_desktop_lcp_ms')->nullable()->after('lh_desktop_fcp_ms');
            $table->unsignedInteger('lh_desktop_tbt_ms')->nullable()->after('lh_desktop_lcp_ms');
            $table->unsignedInteger('lh_desktop_speed_index_ms')->nullable()->after('lh_desktop_tbt_ms');
            $table->decimal('lh_desktop_cls', 8, 3)->nullable()->after('lh_desktop_speed_index_ms');
            $table->json('lh_desktop_raw_json')->nullable()->after('lh_desktop_cls');

            $table->unsignedTinyInteger('lh_mobile_performance_score')->nullable()->after('lh_desktop_raw_json');
            $table->unsignedTinyInteger('lh_mobile_accessibility_score')->nullable()->after('lh_mobile_performance_score');
            $table->unsignedTinyInteger('lh_mobile_best_practices_score')->nullable()->after('lh_mobile_accessibility_score');
            $table->unsignedTinyInteger('lh_mobile_seo_score')->nullable()->after('lh_mobile_best_practices_score');
            $table->unsignedInteger('lh_mobile_fcp_ms')->nullable()->after('lh_mobile_seo_score');
            $table->unsignedInteger('lh_mobile_lcp_ms')->nullable()->after('lh_mobile_fcp_ms');
            $table->unsignedInteger('lh_mobile_tbt_ms')->nullable()->after('lh_mobile_lcp_ms');
            $table->unsignedInteger('lh_mobile_speed_index_ms')->nullable()->after('lh_mobile_tbt_ms');
            $table->decimal('lh_mobile_cls', 8, 3)->nullable()->after('lh_mobile_speed_index_ms');
            $table->json('lh_mobile_raw_json')->nullable()->after('lh_mobile_cls');

            $table->text('lighthouse_error_message')->nullable()->after('lh_mobile_raw_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table): void {
            $table->dropColumn([
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
            ]);
        });
    }
};
