<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Concerns;

use Closure;
use Illuminate\Http\Request;

trait HasSearch
{
    /** @var array<int, string> */
    protected array $searchable = [];

    protected ?Closure $customSearch = null;

    protected int $minSearchLength = 1;

    /**
     * Define columns that participate in global search.
     *
     * @param  array<int, string>  $columns
     */
    public function searchable(array $columns): self
    {
        $this->searchable = $columns;

        return $this;
    }

    /**
     * Override the default LIKE search with custom logic.
     *
     * @example ->search(fn ($q, $term) => $q->whereRaw("MATCH(name) AGAINST(?)", [$term]))
     */
    public function search(Closure $callback): self
    {
        $this->customSearch = $callback;

        return $this;
    }

    public function minSearchLength(int $length): self
    {
        $this->minSearchLength = $length;

        return $this;
    }

    protected function applySearch(Request $request): void
    {
        $term = trim((string) $request->input('search', ''));

        if ($term === '' || mb_strlen($term) < $this->minSearchLength) {
            return;
        }

        if ($this->customSearch !== null) {
            ($this->customSearch)($this->query, $term);

            return;
        }

        if (empty($this->searchable)) {
            return;
        }

        $this->query->where(function ($q) use ($term) {
            foreach ($this->searchable as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }
}
