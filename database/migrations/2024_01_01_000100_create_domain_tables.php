<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->primary(['permission_id', 'role_id']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_pk');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_pk');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->uuid('target_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->index();
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->json('variants')->nullable();
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replaced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'mime_type']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('seo')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->enum('type', ['page', 'post', 'kb']);
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->foreignUuid('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->timestamp('publish_at')->nullable()->index();
            $table->foreignUuid('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('settings_snapshot')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('content_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignUuid('editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('diff')->nullable();
            $table->longText('body')->nullable();
            $table->timestamps();
        });

        Schema::create('content_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('content_slug_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_id')->constrained('contents')->cascadeOnDelete();
            $table->string('slug');
            $table->timestamps();
            $table->unique(['content_id', 'slug']);
        });

        Schema::create('seo_meta', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('seoable');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('open_graph')->nullable();
            $table->timestamps();
        });

        Schema::create('sitemap_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('url')->unique();
            $table->string('changefreq')->default('daily');
            $table->decimal('priority', 3, 2)->default(0.8);
            $table->timestamp('last_modified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('location')->index();
            $table->timestamps();
            $table->unique(['tenant_id', 'location']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable()->index();
            $table->string('label');
            $table->string('url');
            $table->unsignedInteger('position')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('menu_items')->nullOnDelete();
        });

        Schema::create('page_layouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('view');
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('key')->index();
            $table->json('value');
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'key']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('title');
            $table->text('body');
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('file_path');
            $table->string('checksum')->index();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuidMorphs('tokenable');
            $table->text('token');
            $table->json('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['tokenable_type', 'tokenable_id'], 'api_tokens_tokenable_index');
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('url');
            $table->string('secret');
            $table->boolean('active')->default(true);
            $table->json('headers')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('method', 8);
            $table->string('path');
            $table->integer('status_code');
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('correlation_id')->nullable()->index();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('media_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('media_id')->constrained('media')->cascadeOnDelete();
            $table->uuidMorphs('usable');
            $table->string('context')->nullable();
            $table->timestamps();
            $table->unique(['media_id', 'usable_type', 'usable_id', 'context'], 'media_usage_unique');
        });

        Schema::create('content_tag', function (Blueprint $table) {
            $table->uuid('content_id');
            $table->uuid('tag_id');
            $table->primary(['content_id', 'tag_id']);
            $table->foreign('content_id')->references('id')->on('contents')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });

        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('tenant_id')->nullable()->index();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->string('code');
            $table->string('label');
            $table->unsignedInteger('position');
            $table->json('rules')->nullable();
            $table->timestamps();
            $table->unique(['workflow_id', 'code']);
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->uuidMorphs('workflowable');
            $table->string('current_step_code')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->string('from_step')->nullable();
            $table->string('to_step')->nullable();
            $table->string('action');
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('publish_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_id')->constrained('contents')->cascadeOnDelete();
            $table->timestamp('scheduled_for')->index();
            $table->string('status')->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('cache_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->longText('value');
            $table->unsignedInteger('ttl')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache_entries');
        Schema::dropIfExists('publish_queue');
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflows');
        Schema::dropIfExists('content_tag');
        Schema::dropIfExists('media_usages');
        Schema::dropIfExists('api_logs');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('backups');
        Schema::dropIfExists('themes');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('page_layouts');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('sitemap_entries');
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('content_slug_histories');
        Schema::dropIfExists('content_statuses');
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('contents');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('media');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
