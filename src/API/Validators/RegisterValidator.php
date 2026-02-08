<?php
declare(strict_types=1);

namespace App\API\Validators;

use App\Shared\Exceptions\ValidationException;

class RegisterValidator
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
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }

        if (empty($data['full_name'])) {
            $errors['full_name'] = 'الاسم الكامل مطلوب';
        } elseif (mb_strlen($data['full_name']) < 2) {
            $errors['full_name'] = 'الاسم يجب أن يكون حرفين على الأقل';
        }

        if (!empty($errors)) {
            throw new ValidationException('بيانات غير صالحة', $errors);
        }

        return [
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'full_name' => trim($data['full_name']),
            'phone' => trim($data['phone'] ?? ''),
        ];
    }
}
