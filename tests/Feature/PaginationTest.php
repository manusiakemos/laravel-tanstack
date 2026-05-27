<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\DataTable;
use Manusiakemos\LaravelTanstack\Tests\Fixtures\User;

it('returns paginated data with default per_page', function () {
    $request = Request::create('/?page=1');

    $result = DataTable::for(User::query())->toArray($request);

    expect($result['data'])->toHaveCount(25)
        ->and($result['meta']['page'])->toBe(1)
        ->and($result['meta']['per_page'])->toBe(25)
        ->and($result['meta']['total'])->toBe(50)
        ->and($result['meta']['filtered'])->toBe(50)
        ->and($result['meta']['last_page'])->toBe(2);
});

it('respects custom per_page', function () {
    $request = Request::create('/?page=1&per_page=10');

    $result = DataTable::for(User::query())->toArray($request);

    expect($result['data'])->toHaveCount(10)
        ->and($result['meta']['last_page'])->toBe(5);
});

it('clamps per_page to max', function () {
    $request = Request::create('/?per_page=9999');

    $result = DataTable::for(User::query())->maxPerPage(50)->toArray($request);

    expect($result['meta']['per_page'])->toBe(50);
});

it('clamps invalid page to 1', function () {
    $request = Request::create('/?page=-5');

    $result = DataTable::for(User::query())->toArray($request);

    expect($result['meta']['page'])->toBe(1);
});

it('returns null total when skipTotal enabled', function () {
    $request = Request::create('/');

    $result = DataTable::for(User::query())->skipTotal()->toArray($request);

    expect($result['meta']['total'])->toBeNull()
        ->and($result['meta']['filtered'])->toBe(50);
});
