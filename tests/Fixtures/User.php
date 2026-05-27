<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = true;
}
