<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack;

use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\Concerns\HasColumnControls;
use Manusiakemos\LaravelTanstack\Concerns\HasFilters;
use Manusiakemos\LaravelTanstack\Concerns\HasPagination;
use Manusiakemos\LaravelTanstack\Concerns\HasSearch;
use Manusiakemos\LaravelTanstack\Concerns\HasSorting;
use Manusiakemos\LaravelTanstack\Concerns\HasTransform;
use Manusiakemos\LaravelTanstack\Exceptions\DataTableException;

class DataTable implements Responsable
{
    use HasColumnControls;
    use HasFilters;
    use HasPagination;
    use HasSearch;
    use HasSorting;
    use HasTransform;

    protected EloquentBuilder|QueryBuilder $query;

    protected ?Closure $authorize = null;

    protected bool $skipTotal = false;

    public function __construct(EloquentBuilder|QueryBuilder $query)
    {
        $this->query = $query;
    }

    /**
     * Static factory — the primary entry point.
     *
     * @example DataTable::for(User::query())
     */
    public static function for(EloquentBuilder|QueryBuilder $query): self
    {
        return new self($query);
    }

    /**
     * Authorize the request before processing. Throws 403 if returns false.
     */
    public function authorize(Closure $callback): self
    {
        $this->authorize = $callback;

        return $this;
    }

    /**
     * Skip the unfiltered total count (useful for very large tables).
     * The frontend will rely on filtered count only.
     */
    public function skipTotal(bool $skip = true): self
    {
        $this->skipTotal = $skip;

        return $this;
    }

    /**
     * Build the response array. Can be called directly or via Responsable.
     *
     * @return array{data: array<int, mixed>, meta: array<string, mixed>}
     */
    public function toArray(Request $request): array
    {
        $this->guardAuthorization();

        $total = $this->skipTotal ? null : (clone $this->query)->count();

        $this->applySearch($request);
        $this->applyFilters($request);

        $filtered = (clone $this->query)->count();

        $this->applySort($request);

        [$page, $perPage] = $this->resolvePagination($request);

        $items = $this->query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $data = $this->transformItems($items);

        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'filtered' => $filtered,
                'last_page' => (int) ceil($filtered / max($perPage, 1)),
            ],
        ];
    }

    /**
     * Implements Responsable so controllers can `return DataTable::for(...)`.
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this->toArray($request));
    }

    protected function guardAuthorization(): void
    {
        if ($this->authorize === null) {
            return;
        }

        if (! ($this->authorize)()) {
            throw DataTableException::unauthorized();
        }
    }
}
