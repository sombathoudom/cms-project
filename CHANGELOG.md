# Changelog

## [Unreleased]
### Added
- Implemented audit logging for user lifecycle actions with correlation-aware logging and tenant scoping. (E1-F1-I6)
- Exposed paginated audit log API endpoint and Filament resource secured by RBAC. (E1-F1-I6)
- Added automated tests, documentation, and OpenAPI contract for audit log reporting. (E1-F1-I6)
- Integrated Tiptap-based rich text editor with auto-save revisions and RBAC enforcement for knowledge base content. (E2-F1-I5)
- Delivered signed preview links with audit trails plus Filament actions for draft review flows. (E2-F1-I5)
- Expanded API surface with auto-save and preview endpoints, documentation, and regression tests. (E2-F1-I5)
- Embedded a tenant-aware media picker with upload support directly into the editor, returning responsive markup for inserts. (E3-F1-I5)
- Added media listing, upload, and content embed APIs with standard error schema, audit logging, and tenant scoping. (E3-F1-I5)
- Documented the media endpoints in README and OpenAPI plus introduced coverage and policy tests for the media workflow. (E3-F1-I5)
