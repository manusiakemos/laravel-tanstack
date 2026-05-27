<?php

declare(strict_types=1);

namespace Manusiakemos\LaravelTanstack\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DataTableException extends HttpException
{
    public static function unauthorized(string $message = 'Unauthorized to access this datatable.'): self
    {
        return new self(403, $message);
    }

    public static function invalidQuery(string $message): self
    {
        return new self(400, $message);
    }
}
