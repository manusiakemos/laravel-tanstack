<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\DataTable;
use Manusiakemos\LaravelTanstack\Tests\Fixtures\User;

it('sorts by whitelisted column ascending', function () {
    $request = Request::create('/?sort=id:asc&per_page=3');

    $result = DataTable::for(User::query())
        ->sortable(['id', 'name'])
        ->toArray($request);

    $ids = array_column($result['data'], 'id');
    expect($ids)->toBe([1, 2, 3]);
});

it('sorts by whitelisted column descending', function () {
    $request = Request::create('/?sort=id:desc&per_page=3');

    $result = DataTable::for(User::query())
        ->sortable(['id'])
        ->toArray($request);

    $ids = array_column($result['data'], 'id');
    expect($ids)->toBe([50, 49, 48]);
});

it('ignores sort on non-whitelisted columns', function () {
    $request = Request::create('/?sort=password:asc&per_page=3');

    // Should not throw, just ignore. Default insertion order returned.
    $result = DataTable::for(User::query())
        ->sortable(['name'])
        ->toArray($request);

    expect($result['data'])->toHaveCount(3);
});

it('applies default sort when no sort param', function () {
    $request = Request::create('/?per_page=3');

    $result = DataTable::for(User::query())
        ->defaultSort('id', 'desc')
        ->toArray($request);

    $ids = array_column($result['data'], 'id');
    expect($ids)->toBe([50, 49, 48]);
});

it('supports custom orderColumn override', function () {
    $request = Request::create('/?sort=name_length:asc&per_page=3');

    $result = DataTable::for(User::query())
        ->sortable(['name_length'])
        ->orderColumn('name_length', fn ($q, $dir) => $q->orderByRaw("LENGTH(name) {$dir}, id asc"))
        ->toArray($request);

    expect($result['data'])->toHaveCount(3);
});

it('sorts by multiple columns', function () {
    $request = Request::create('/?sort=status:asc,id:desc&per_page=3');

    $result = DataTable::for(User::query())
        ->sortable(['status', 'id'])
        ->toArray($request);

    expect($result['data'])->toHaveCount(3);
});
