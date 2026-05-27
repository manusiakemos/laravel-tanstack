# Laravel DataTables for TanStack

[![Latest Version](https://img.shields.io/packagist/v/manusiakemos/laravel-tanstack.svg?style=flat-square)](https://packagist.org/packages/manusiakemos/laravel-tanstack)
[![Tests](https://img.shields.io/github/actions/workflow/status/manusiakemos/laravel-tanstack/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/manusiakemos/laravel-tanstack/actions)
[![License](https://img.shields.io/packagist/l/manusiakemos/laravel-tanstack.svg?style=flat-square)](https://packagist.org/packages/manusiakemos/laravel-tanstack)

Modern server-side datatable for Laravel, purpose-built for [TanStack Table](https://tanstack.com/table) frontends (React, Vue, Svelte, Solid). Inspired by `yajra/laravel-datatables` but with a clean REST API instead of the legacy datatables.net protocol.

Use this when you have an Inertia (or any SPA) frontend, want server-side processing, and want to stop writing pagination + search + sort + filter boilerplate for every table.

## Why this package

- **Three-line endpoints.** Whitelist columns, return the response. That's it.
- **REST-style query string.** Clean, browser-DevTools-friendly, easy to cache.
- **Type-safe FE companion.** Pairs with `@manusiakemos/laravel-tanstack-react` (separate package).
- **Eloquent or Query Builder.** Both supported, no extra setup.
- **No jQuery, no datatables.net baggage.**

## Installation

```bash
composer require manusiakemos/laravel-tanstack
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=laravel-tanstack-config
```

## Quick start

```php
use Manusiakemos\LaravelTanstack\DataTable;
use App\Models\User;

class UserDataTableController
{
    public function __invoke(Request $request)
    {
        return DataTable::for(User::query())
            ->searchable(['name', 'email'])
            ->sortable(['name', 'email', 'created_at'])
            ->filterable(['status', 'role']);
    }
}
```

Register it on a web route so it has access to the session (for Inertia auth):

```php
Route::get('/datatable/users', UserDataTableController::class)->middleware('auth');
```

Done. The controller returns a `JsonResponse` automatically because `DataTable` implements `Responsable`.

## API protocol

### Request

```
GET /datatable/users?
  page=1&
  per_page=25&
  sort=name:asc,created_at:desc&
  search=hafiz&
  filter[status]=active&
  filter[role][]=admin&filter[role][]=editor
```

| Param | Description |
|---|---|
| `page` | 1-indexed page number. Defaults to 1. |
| `per_page` | Rows per page. Clamped to `max_per_page` config. |
| `sort` | Comma-separated `column:direction` pairs. |
| `search` | Global search term across `searchable()` columns. |
| `filter[col]=v` | Equals filter. |
| `filter[col][]=v1&filter[col][]=v2` | `whereIn` filter. |

### Response

```json
{
  "data": [
    { "id": 1, "name": "Hafiz", "email": "hafiz@example.com" }
  ],
  "meta": {
    "page": 1,
    "per_page": 25,
    "total": 1234,
    "filtered": 89,
    "last_page": 4
  }
}
```

## Features

### Transform rows

```php
DataTable::for(User::query()->with('role'))
    ->transform(fn ($user) => [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role->name,
        'status_label' => $user->status === 'active' ? 'Aktif' : 'Nonaktif',
    ]);
```

Or use an API Resource:

```php
DataTable::for(User::query())->resource(UserResource::class);
```

### Custom search

```php
DataTable::for(User::query())
    ->search(fn ($q, $term) =>
        $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($term).'%'])
          ->orWhereHas('role', fn ($r) => $r->where('name', 'like', "%{$term}%"))
    );
```

### Custom sort column

For computed or relation columns:

```php
DataTable::for(User::query())
    ->sortable(['name', 'role_name'])
    ->orderColumn('role_name', fn ($q, $dir) =>
        $q->orderBy(
            Role::select('name')->whereColumn('roles.id', 'users.role_id'),
            $dir
        )
    );
```

### Custom filter

```php
DataTable::for(User::query())
    ->filterColumn('created_between', fn ($q, $value) => 
        $q->whereBetween('created_at', explode(',', $value))
    );
```

Request: `?filter[created_between]=2024-01-01,2024-12-31`

### Authorization

```php
DataTable::for(User::query())
    ->authorize(fn () => Gate::allows('viewAny', User::class));
```

Returns 403 if the closure returns false.

### Default sort

```php
DataTable::for(User::query())
    ->sortable(['created_at'])
    ->defaultSort('created_at', 'desc');
```

### Skip total count

For very large tables where `count(*)` over the unfiltered set is expensive:

```php
DataTable::for(User::query())->skipTotal();
```

The response will have `meta.total = null`; frontend should rely on `filtered` only.

### Pagination limits

```php
DataTable::for(User::query())
    ->defaultPerPage(50)
    ->maxPerPage(200);
```

### Query builder macro

For one-liner usage:

```php
return User::query()
    ->where('active', true)
    ->toDataTable()
    ->searchable(['name'])
    ->sortable(['name']);
```

## Inertia + TanStack pattern

The recommended setup: let Inertia render the page shell (layout, auth state, navigation), and let the table component fetch its own data from a separate JSON endpoint.

```tsx
// Page rendered by Inertia
import { useDataTable } from '@manusiakemos/laravel-tanstack-react'

function UsersIndex() {
  const { table, loading } = useDataTable<User>({
    endpoint: '/datatable/users',
    columns: [
      { accessorKey: 'name', header: 'Nama' },
      { accessorKey: 'email', header: 'Email' },
    ],
  })

  return <DataTableView table={table} loading={loading} />
}
```

The table state lives in React; the page shell stays Inertia-managed. No full-page reload on pagination.

## Security notes

- **Never call `->searchable()` or `->sortable()` with untrusted column names.** The package enforces whitelisting — only listed columns can be searched/sorted — but you must define the list yourself.
- **Use `->authorize()` for any non-public table.** Or check authorization in the controller before returning the DataTable.
- **The package is read-only** — it does not perform writes, so SQL injection surface is limited to filter values which are bound parameters.

## Configuration

The full `config/laravel-tanstack.php`:

```php
return [
    'default_per_page' => 25,
    'max_per_page' => 100,
    'case_insensitive' => true,
    'report_exceptions' => true,
];
```

## Testing

```bash
composer test
```

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x

## Roadmap

- [ ] PostgreSQL-specific `ILIKE` search optimization
- [ ] Laravel Scout integration for full-text search
- [ ] Vue 3 and Svelte FE companions
- [ ] Excel/CSV export endpoint
- [ ] Saved view / preset support

## License

MIT.
