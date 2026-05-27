<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\DataTable;
use Manusiakemos\LaravelTanstack\Exceptions\DataTableException;
use Manusiakemos\LaravelTanstack\Tests\Fixtures\User;

it('transforms rows with closure', function () {
    $request = Request::create('/?per_page=2');

    $result = DataTable::for(User::query())
        ->transform(fn ($user) => [
            'id' => $user->id,
            'display' => strtoupper($user->name),
        ])
        ->toArray($request);

    expect($result['data'][0])->toHaveKeys(['id', 'display'])
        ->and($result['data'][0]['display'])->toBe('USER 1');
});

it('returns raw rows when no transformer set', function () {
    $request = Request::create('/?per_page=1');

    $result = DataTable::for(User::query())->toArray($request);

    expect($result['data'][0])->toHaveKey('email');
});

it('throws when authorize closure returns false', function () {
    $request = Request::create('/');

    DataTable::for(User::query())
        ->authorize(fn () => false)
        ->toArray($request);
})->throws(DataTableException::class);

it('passes when authorize closure returns true', function () {
    $request = Request::create('/?per_page=1');

    $result = DataTable::for(User::query())
        ->authorize(fn () => true)
        ->toArray($request);

    expect($result['data'])->toHaveCount(1);
});
