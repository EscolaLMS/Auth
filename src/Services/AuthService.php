<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Enums\TokenExpirationEnum;
use EscolaLms\Auth\Events\ForgotPassword;
use EscolaLms\Auth\Events\ResetPassword;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService implements AuthServiceContract
{
    private UserRepositoryContract $userRepository;

    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws Exception
     */
    public function forgotPassword(string $email, string $returnUrl): void
    {
        try {
            $user = $this->userRepository->findByEmailOrFail($email);
            event(new ForgotPassword($user, $returnUrl));
        } catch (ModelNotFoundException $exception) {
            usleep(random_int(200000, 600000));
        }
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        /** @var User $user */
        $user = $this->userRepository->findByEmailOrFail($email);

        if ($token !== $user->password_reset_token) {
            throw new AuthorizationException('Wrong password reset token');
        }

        $this->userRepository->update([
            'password' => Hash::make($password),
            'password_reset_token' => null,
        ], $user->getKey());
        event(new ResetPassword($user));
    }

    public function createTokenForUser(User $user, bool $rememberMe = false): PersonalAccessTokenResult
    {
        Passport::personalAccessTokensExpireIn(
            $rememberMe
                ? now()->addMonth()
                : now()->addMinutes(TokenExpirationEnum::SHORT_TIME_IN_MINUTES)
        );

        return $user->createToken(config('passport.personal_access_client.secret'));
    }

    public function refreshToken(User $user): PersonalAccessTokenResult
    {
        $token = $user->token();
        $rememberMe = $token->expires_at->diffInMinutes($token->created_at) > TokenExpirationEnum::SHORT_TIME_IN_MINUTES;

        return $this->createTokenForUser($user, $rememberMe);
    }
}
