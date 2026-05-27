<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Concerns;

use Illuminate\Http\Request;

trait HasPagination
{
    protected ?int $maxPerPage = null;

    protected ?int $defaultPerPage = null;

    public function maxPerPage(int $max): self
    {
        $this->maxPerPage = $max;

        return $this;
    }

    public function defaultPerPage(int $perPage): self
    {
        $this->defaultPerPage = $perPage;

        return $this;
    }

    /**
     * @return array{0: int, 1: int} [page, perPage]
     */
    protected function resolvePagination(Request $request): array
    {
        $config = config('laravel-tanstack', []);

        $max = $this->maxPerPage ?? ($config['max_per_page'] ?? 100);
        $default = $this->defaultPerPage ?? ($config['default_per_page'] ?? 25);

        $page = max((int) $request->input('page', 1), 1);
        $perPage = (int) $request->input('per_page', $default);

        if ($perPage < 1) {
            $perPage = $default;
        }

        $perPage = min($perPage, $max);

        return [$page, $perPage];
    }
}
