<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_preview_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_id')->constrained('contents')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('expires_at')->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('content_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_preview_links');
    }
};
