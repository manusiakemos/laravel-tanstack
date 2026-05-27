# Changelog

All notable changes to `manusiakemos/laravel-tanstack` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial public release
- Eloquent and Query Builder support via `DataTable::for()`
- Fluent API: `searchable()`, `sortable()`, `filterable()`, `transform()`, `resource()`, `authorize()`
- Custom column overrides: `orderColumn()`, `filterColumn()`, `search()`
- Pagination controls: `defaultPerPage()`, `maxPerPage()`, `skipTotal()`
- Column response controls: `only()`, `except()`
- Default sort support: `defaultSort()`
- Min search length: `minSearchLength()`
- `toDataTable()` macro on EloquentBuilder and QueryBuilder
- `Responsable` interface — controllers can `return DataTable::for(...)` directly
- Comprehensive Pest test suite with Orchestra Testbench
- GitHub Actions CI matrix (PHP 8.2/8.3/8.4, Laravel 11/12)
- Contributing guide in `README.md` covering PR workflow, branch naming, commit style, and the local quality gate (`composer format`, `composer analyse`, `composer test`)
- Project decision log (`MEMORY.md`) and failed approach log (`ERRORS.md`) to preserve cross-session context for maintainers

### Changed
- Standardized all repository content to English, including README example strings (replaced Indonesian labels in `transform()` and TanStack column examples)

### Fixed
- CI failure where Pest could not locate the empty `tests/Unit` directory; added `tests/Unit/.gitkeep` so the directory is tracked by git
