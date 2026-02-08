<?php
declare(strict_types=1);

namespace App\Shared\Exceptions;

class NotFoundException extends \RuntimeException
{
    public function __construct(
        string $message = 'العنصر غير موجود',
        int $code = 404,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
