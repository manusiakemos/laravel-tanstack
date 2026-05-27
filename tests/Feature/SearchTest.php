<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\DataTable;
use Manusiakemos\LaravelTanstack\Tests\Fixtures\User;

it('applies global search across searchable columns', function () {
    $request = Request::create('/?search=user1');

    $result = DataTable::for(User::query())
        ->searchable(['name', 'email'])
        ->toArray($request);

    // user1, user10..user19 — 11 users contain "user1" in name
    expect($result['meta']['filtered'])->toBe(11);
});

it('ignores search when term is empty', function () {
    $request = Request::create('/?search=');

    $result = DataTable::for(User::query())
        ->searchable(['name'])
        ->toArray($request);

    expect($result['meta']['filtered'])->toBe(50);
});

it('respects minSearchLength', function () {
    $request = Request::create('/?search=a');

    $result = DataTable::for(User::query())
        ->searchable(['name'])
        ->minSearchLength(3)
        ->toArray($request);

    // Search ignored because "a" is shorter than min length
    expect($result['meta']['filtered'])->toBe(50);
});

it('uses custom search closure when provided', function () {
    $request = Request::create('/?search=anything');

    $result = DataTable::for(User::query())
        ->search(fn ($q, $term) => $q->where('id', '<=', 3))
        ->toArray($request);

    expect($result['meta']['filtered'])->toBe(3);
});

it('does nothing when searchable not configured and no custom search', function () {
    $request = Request::create('/?search=user1');

    $result = DataTable::for(User::query())->toArray($request);

    expect($result['meta']['filtered'])->toBe(50);
});
