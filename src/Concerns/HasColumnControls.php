<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Concerns;

trait HasColumnControls
{
    /** @var array<int, string> */
    protected array $only = [];

    /** @var array<int, string> */
    protected array $except = [];

    /**
     * Limit response to only these columns (post-transform).
     *
     * @param  array<int, string>  $columns
     */
    public function only(array $columns): self
    {
        $this->only = $columns;

        return $this;
    }

    /**
     * Exclude these columns from response (post-transform).
     *
     * @param  array<int, string>  $columns
     */
    public function except(array $columns): self
    {
        $this->except = $columns;

        return $this;
    }
}
