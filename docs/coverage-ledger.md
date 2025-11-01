# Coverage Ledger

| Feature | Models | Endpoints | Tests |
| --- | --- | --- | --- |
| Authentication & RBAC | User, Role, Permission, AuditLog | `/health` (auth bypass via Gate) | `RbacPolicyTest`, `AuthPermissionTest`, `HealthCheckTest` |
| Content Lifecycle | Content, ContentRevision, ContentStatus, ContentSlugHistory, SeoMeta, SitemapEntry, ContentPreviewLink | Filament KB resource, `/api/v1/admin/content/{content}/autosave`, `/api/v1/admin/content/{content}/preview-link`, `/preview/content/{content}/{token}` | `KnowledgeBaseCrudTest`, `TenancyScopeTest`, `ContentAutoSaveTest`, `ContentPreviewTest` |
| Media Library | Media, MediaUsage | `/api/v1/admin/media`, `/api/v1/admin/content/{content}/media`, Filament media picker modal | `MediaPickerTest` |
| Taxonomy & Metadata | Category, Tag | Used via content relations | `FactoryTest`, `TenancyScopeTest` |
| Publishing Workflow | Workflow, WorkflowStep, WorkflowInstance, WorkflowAction, PublishQueue, Ticket | Filament Ticket resource | `TicketCrudTest`, `RbacPolicyTest` |
| Site Settings | Setting, Menu, MenuItem, PageLayout, Announcement, Theme, Backup, CacheEntry | Admin configuration APIs (seeder) | `FactoryTest` |
| API & Headless | ApiToken, Webhook, ApiLog | `/health` | `HealthCheckTest` |
| Security & Compliance | AuditLog | `/api/v1/admin/audit-logs`, `/health` validation | `HealthCheckTest`, `AuthPermissionTest`, `AuditLogTest` |
| DevOps & QA | Dockerfile, docker-compose, Makefile, CI workflow | n/a | CI workflow |
| System Architecture Foundations | Domain directory structure, policies | n/a | All tests exercising policies |
