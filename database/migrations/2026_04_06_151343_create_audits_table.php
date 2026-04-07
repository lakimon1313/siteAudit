<?php

use App\Models\Website;
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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Website::class)->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->string('final_url')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->string('title')->nullable();
            $table->unsignedSmallInteger('title_length')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedSmallInteger('meta_description_length')->nullable();
            $table->unsignedSmallInteger('h1_count')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('robots_txt_exists')->nullable();
            $table->boolean('sitemap_xml_exists')->nullable();
            $table->boolean('is_https')->nullable();
            $table->boolean('ssl_valid')->nullable();
            $table->timestamp('ssl_expires_at')->nullable();
            $table->json('security_headers_json')->nullable();
            $table->json('raw_headers_json')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('audited_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
