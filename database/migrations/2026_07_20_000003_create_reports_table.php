<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            // The reported slug (the link may or may not exist / may be gone).
            $table->string('slug', 32)->index();
            $table->string('reason', 32);
            $table->string('note', 500)->nullable();
            $table->timestamp('created_at')->nullable();
            // No reporter identity is stored (no auth, no raw IP — ADR-005/006).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
