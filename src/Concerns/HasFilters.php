<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Concerns;

use Closure;
use Illuminate\Http\Request;

trait HasFilters
{
    /** @var array<int, string> */
    protected array $filterable = [];

    /** @var array<string, Closure> */
    protected array $filterOverrides = [];

    /**
     * Whitelist columns that can be filtered via `filter[col]=value`.
     *
     * @param  array<int, string>  $columns
     */
    public function filterable(array $columns): self
    {
        $this->filterable = $columns;

        return $this;
    }

    /**
     * Custom filter logic for a specific column key.
     *
     * @example ->filterColumn('created_between', fn ($q, $value) => $q->whereBetween('created_at', explode(',', $value)))
     */
    public function filterColumn(string $key, Closure $callback): self
    {
        $this->filterOverrides[$key] = $callback;
        // Auto-whitelist so callers don't have to repeat themselves.
        if (! in_array($key, $this->filterable, true)) {
            $this->filterable[] = $key;
        }

        return $this;
    }

    protected function applyFilters(Request $request): void
    {
        $filters = $request->input('filter', []);

        if (! is_array($filters) || empty($filters)) {
            return;
        }

        foreach ($filters as $key => $value) {
            if (! in_array($key, $this->filterable, true)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (isset($this->filterOverrides[$key])) {
                ($this->filterOverrides[$key])($this->query, $value);

                continue;
            }

            if (is_array($value)) {
                $this->query->whereIn($key, $value);
            } else {
                $this->query->where($key, '=', $value);
            }
        }
    }
}
