<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Manusiakemos\LaravelTanstack\DataTable;
use Manusiakemos\LaravelTanstack\Tests\Fixtures\User;

it('limits response to only listed columns', function () {
    $request = Request::create('/?per_page=1');

    $result = DataTable::for(User::query())
        ->only(['id', 'name'])
        ->toArray($request);

    expect(array_keys($result['data'][0]))->toBe(['id', 'name']);
});

it('excludes columns from response', function () {
    $request = Request::create('/?per_page=1');

    $result = DataTable::for(User::query())
        ->except(['email', 'status'])
        ->toArray($request);

    expect($result['data'][0])->not->toHaveKey('email')
        ->and($result['data'][0])->not->toHaveKey('status')
        ->and($result['data'][0])->toHaveKey('name');
});

it('applies only and except after transform', function () {
    $request = Request::create('/?per_page=1');

    $result = DataTable::for(User::query())
        ->transform(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'secret' => 'hidden',
        ])
        ->except(['secret'])
        ->toArray($request);

    expect($result['data'][0])->toHaveKeys(['id', 'name'])
        ->and($result['data'][0])->not->toHaveKey('secret');
});
