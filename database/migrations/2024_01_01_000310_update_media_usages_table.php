<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_usages', function (Blueprint $table): void {
            if (! Schema::hasColumn('media_usages', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('context');
            }

            if (! Schema::hasColumn('media_usages', 'position')) {
                $table->unsignedInteger('position')->default(0)->after('alt_text');
            }

            if (! Schema::hasColumn('media_usages', 'meta')) {
                $table->json('meta')->nullable()->after('position');
            }

            $table->index(['usable_type', 'usable_id'], 'media_usages_usable_index');
        });
    }

    public function down(): void
    {
        Schema::table('media_usages', function (Blueprint $table): void {
            if (Schema::hasColumn('media_usages', 'meta')) {
                $table->dropColumn('meta');
            }

            if (Schema::hasColumn('media_usages', 'position')) {
                $table->dropColumn('position');
            }

            if (Schema::hasColumn('media_usages', 'alt_text')) {
                $table->dropColumn('alt_text');
            }

            $table->dropIndex('media_usages_usable_index');
        });
    }
};
