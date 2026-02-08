<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\JwtManager;
use App\Infrastructure\Security\PasswordHasher;
use App\Shared\Exceptions\AuthenticationException;
use App\Shared\Exceptions\ValidationException;
use App\Shared\Exceptions\NotFoundException;
use App\Shared\Utils\UUID;

final class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JwtManager $jwtManager,
        private readonly PasswordHasher $passwordHasher,
    ) {}

    /**
     * Authenticate a user by email and password.
     *
     * @return array{user: User, access_token: string, refresh_token: string}
     *
     * @throws AuthenticationException When credentials are invalid or the account is inactive.
     */
    public function login(string $email, string $password): array
    {
        $email = trim(mb_strtolower($email));

        if ($email === '' || $password === '') {
            throw new ValidationException('Email and password are required');
        }

        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new AuthenticationException('Invalid email or password');
        }

        if ($user->getStatus() !== 'active') {
            throw new AuthenticationException(
                "Account is currently {$user->getStatus()}. Please contact support."
            );
        }

        if (!$this->passwordHasher->verify($password, $user->getPasswordHash())) {
            throw new AuthenticationException('Invalid email or password');
        }

        $accessToken  = $this->jwtManager->generateToken($user->getId(), $user->getRole());
        $refreshToken = $this->jwtManager->generateToken($user->getId(), $user->getRole());

        $this->userRepository->updateLastLogin($user->getId());

        return [
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Register a new user account.
     *
     * @param array{email: string, password: string, full_name: string, phone?: string, role?: string} $data
     *
     * @throws ValidationException When required fields are missing or the email is already taken.
     */
    public function register(array $data): User
    {
        $requiredFields = ['email', 'password', 'full_name'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new ValidationException("The field '{$field}' is required");
            }
        }

        $email = trim(mb_strtolower($data['email']));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email address format');
        }

        if (mb_strlen($data['password']) < 8) {
            throw new ValidationException('Password must be at least 8 characters long');
        }

        $existingUser = $this->userRepository->findByEmail($email);

        if ($existingUser !== null) {
            throw new ValidationException('An account with this email already exists');
        }

        $user = new User(
            id:           UUID::generate(),
            email:        $email,
            passwordHash: $this->passwordHasher->hash($data['password']),
            fullName:     trim($data['full_name']),
            phone:        isset($data['phone']) ? trim($data['phone']) : null,
            role:         $data['role'] ?? 'client',
            status:       'active',
        );

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Validate and refresh an existing token pair.
     *
     * @return array{access_token: string, refresh_token: string}
     *
     * @throws AuthenticationException When the refresh token is invalid or expired.
     */
    public function refreshToken(string $token): array
    {
        $payload = $this->jwtManager->validateToken($token);

        if ($payload === null) {
            throw new AuthenticationException('Invalid or expired refresh token');
        }

        $userId = $payload['sub'] ?? null;
        $role   = $payload['role'] ?? null;

        if ($userId === null || $role === null) {
            throw new AuthenticationException('Malformed token payload');
        }

        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new AuthenticationException('User associated with this token no longer exists');
        }

        if ($user->getStatus() !== 'active') {
            throw new AuthenticationException(
                "Account is currently {$user->getStatus()}. Token refresh denied."
            );
        }

        $accessToken  = $this->jwtManager->generateToken($user->getId(), $user->getRole());
        $refreshToken = $this->jwtManager->generateToken($user->getId(), $user->getRole());

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Invalidate a user's session (logout).
     *
     * @throws NotFoundException When the user does not exist.
     */
    public function logout(string $userId): void
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new NotFoundException("User not found: {$userId}");
        }

        $this->userRepository->clearRefreshTokens($userId);
    }

    /**
     * Validate an access token and return its decoded payload.
     *
     * @return array{sub: string, role: string, iat: int, exp: int}|null
     */
    public function validateToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        return $this->jwtManager->validateToken($token);
    }
}
