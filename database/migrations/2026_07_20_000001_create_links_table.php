<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            // Creation is account-gated (ADR-005); a link belongs to its owner.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Unique slug drives the hot-path redirect lookup (ADR-004).
            $table->string('slug', 32)->unique();
            // http/https only, enforced at validation (ADR-005 §3).
            $table->text('target_url');
            // Kill-switch (ADR-004/005): moderation deactivates without deleting.
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
