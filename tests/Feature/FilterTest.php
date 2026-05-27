<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\DataTable;
use Manusiakemos\LaravelTanstack\Tests\Fixtures\User;

it('filters by single value', function () {
    $request = Request::create('/?filter[status]=inactive');

    $result = DataTable::for(User::query())
        ->filterable(['status'])
        ->toArray($request);

    // Every 5th user is inactive: 5, 10, 15, ... 50 = 10 users
    expect($result['meta']['filtered'])->toBe(10);
});

it('filters by array of values (whereIn)', function () {
    $request = Request::create('/?filter[role][]=admin&filter[role][]=user');

    $result = DataTable::for(User::query())
        ->filterable(['role'])
        ->toArray($request);

    expect($result['meta']['filtered'])->toBe(50);
});

it('ignores non-whitelisted filter keys', function () {
    $request = Request::create('/?filter[secret_field]=value');

    $result = DataTable::for(User::query())
        ->filterable(['status'])
        ->toArray($request);

    expect($result['meta']['filtered'])->toBe(50);
});

it('ignores empty filter values', function () {
    $request = Request::create('/?filter[status]=');

    $result = DataTable::for(User::query())
        ->filterable(['status'])
        ->toArray($request);

    expect($result['meta']['filtered'])->toBe(50);
});

it('uses custom filterColumn closure', function () {
    $request = Request::create('/?filter[id_gt]=45');

    $result = DataTable::for(User::query())
        ->filterColumn('id_gt', fn ($q, $value) => $q->where('id', '>', $value))
        ->toArray($request);

    // ids 46..50 = 5 users
    expect($result['meta']['filtered'])->toBe(5);
});
