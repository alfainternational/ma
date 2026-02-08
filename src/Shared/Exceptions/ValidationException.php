<?php
declare(strict_types=1);

namespace App\Shared\Exceptions;

class ValidationException extends \RuntimeException
{
    public function __construct(
        string $message = 'بيانات غير صالحة',
        private array $errors = [],
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
