<?php
declare(strict_types=1);

namespace App\Shared\Exceptions;

class AuthenticationException extends \RuntimeException
{
    public function __construct(
        string $message = 'غير مصرح',
        int $code = 401,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
