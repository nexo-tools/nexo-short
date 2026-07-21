<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained()->cascadeOnDelete();
            // Daily-rotating anonymous hash — no IP or User-Agent is stored (ADR-006).
            $table->string('visitor_hash', 64);
            // External referrer host only; null for direct/self.
            $table->string('referrer_host')->nullable();
            // Coarse device class from the UA: mobile | desktop | bot.
            $table->string('device', 7)->default('desktop');
            // Country from Cloudflare's CF-IPCountry (2 letters); null without it.
            $table->char('country', 2)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['link_id', 'created_at']);
            $table->index(['link_id', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
