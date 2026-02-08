<?php
declare(strict_types=1);

namespace App\API\Validators;

use App\Shared\Exceptions\ValidationException;

class LoginValidator
{
    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'البريد الإلكتروني مطلوب';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'البريد الإلكتروني غير صالح';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'كلمة المرور مطلوبة';
        }

        if (!empty($errors)) {
            throw new ValidationException('بيانات غير صالحة', $errors);
        }

        return [
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
        ];
    }
}
