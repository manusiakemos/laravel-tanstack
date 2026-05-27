<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Concerns;

use Closure;
use Illuminate\Http\Request;

trait HasSorting
{
    /** @var array<int, string> */
    protected array $sortable = [];

    /** @var array<string, Closure> */
    protected array $sortOverrides = [];

    /** @var array<int, array{column: string, direction: string}> */
    protected array $defaultSort = [];

    /**
     * Whitelist columns that can be sorted via the request.
     *
     * @param  array<int, string>  $columns
     */
    public function sortable(array $columns): self
    {
        $this->sortable = $columns;

        return $this;
    }

    /**
     * Provide a custom sort closure for a specific column.
     *
     * @example ->orderColumn('full_name', fn ($q, $dir) => $q->orderBy('last_name', $dir))
     */
    public function orderColumn(string $column, Closure $callback): self
    {
        $this->sortOverrides[$column] = $callback;

        return $this;
    }

    /**
     * Default ordering when request has no sort param.
     */
    public function defaultSort(string $column, string $direction = 'asc'): self
    {
        $this->defaultSort[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'desc' ? 'desc' : 'asc',
        ];

        return $this;
    }

    protected function applySort(Request $request): void
    {
        $sort = (string) $request->input('sort', '');

        if ($sort === '') {
            foreach ($this->defaultSort as $rule) {
                $this->query->orderBy($rule['column'], $rule['direction']);
            }

            return;
        }

        foreach (explode(',', $sort) as $field) {
            $parts = explode(':', $field);
            $column = trim($parts[0]);
            $direction = strtolower(trim($parts[1] ?? 'asc')) === 'desc' ? 'desc' : 'asc';

            if (! in_array($column, $this->sortable, true)) {
                continue;
            }

            if (isset($this->sortOverrides[$column])) {
                ($this->sortOverrides[$column])($this->query, $direction);

                continue;
            }

            $this->query->orderBy($column, $direction);
        }
    }
}
