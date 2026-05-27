<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Tests;

use Manusiakemos\LaravelTanstack\LaravelTanstackServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->seedUsers();
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelTanstackServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status')->default('active');
            $table->string('role')->default('user');
            $table->timestamps();
        });
    }

    protected function seedUsers(int $count = 50): void
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => $i % 5 === 0 ? 'inactive' : 'active',
                'role' => $i % 10 === 0 ? 'admin' : 'user',
                'created_at' => now()->subDays($count - $i),
                'updated_at' => now()->subDays($count - $i),
            ];
        }
        $this->app['db']->table('users')->insert($rows);
    }
}
