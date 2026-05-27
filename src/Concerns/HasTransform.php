<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Concerns;

use Closure;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

trait HasTransform
{
    protected ?Closure $transformer = null;

    /** @var class-string<JsonResource>|null */
    protected ?string $resource = null;

    /**
     * Transform each row with a closure.
     *
     * @example ->transform(fn ($user) => ['id' => $user->id, 'name' => $user->name])
     */
    public function transform(Closure $callback): self
    {
        $this->transformer = $callback;
        $this->resource = null;

        return $this;
    }

    /**
     * Transform rows via an API Resource class.
     *
     * @param  class-string<JsonResource>  $resourceClass
     */
    public function resource(string $resourceClass): self
    {
        $this->resource = $resourceClass;
        $this->transformer = null;

        return $this;
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @return array<int, mixed>
     */
    protected function transformItems(Collection $items): array
    {
        if ($this->resource !== null) {
            $class = $this->resource;
            $rows = $items->map(fn ($item) => (new $class($item))->resolve())->all();
        } elseif ($this->transformer !== null) {
            $rows = $items->map(function ($item) {
                $result = ($this->transformer)($item);

                return is_array($result) ? $result : (array) $result;
            })->all();
        } else {
            $rows = $items->map(fn ($item) => is_array($item) ? $item : $item->toArray())->all();
        }

        return $this->applyColumnControls($rows);
    }

    /**
     * Apply only() / except() column filtering after transform.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function applyColumnControls(array $rows): array
    {
        if (empty($this->only) && empty($this->except)) {
            return $rows;
        }

        $only = $this->only;
        $except = $this->except;

        return array_map(function ($row) use ($only, $except) {
            if (! empty($only)) {
                $row = array_intersect_key($row, array_flip($only));
            }
            if (! empty($except)) {
                $row = array_diff_key($row, array_flip($except));
            }

            return $row;
        }, $rows);
    }
}
