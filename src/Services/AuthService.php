<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Events\ForgotPassword;
use EscolaLms\Auth\Events\PasswordChanged;
use EscolaLms\Auth\Events\ResetPassword;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Enums\UserRole;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthService implements AuthServiceContract
{
    private UserServiceContract $userService;
    private UserRepositoryContract $userRepository;

    public function __construct(UserServiceContract $userService, UserRepositoryContract $userRepository)
    {
        $this->userService = $userService;
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

    public function getTokenBySocial(string $provider): string
    {
        /** @var \Laravel\Socialite\AbstractUser $socialUser */
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $user = $this->userRepository->findByEmail($socialUser->email);

        if (is_null($user)) {
            $name = Str::of($socialUser->name);
            $firstName = Str::of($name->explode(' ')->first())->trim();
            $lastName = $name->after($firstName)->trim();

            $userSaveDto = new UserSaveDto(
                $firstName,
                $lastName,
                true,
                [UserRole::STUDENT],
                $socialUser->email,
                null,
                true,
            );

            $user = $this->userService->create($userSaveDto);
        }

        return $this->createTokenForUser($user);
    }

    public function createTokenForUser(User $user): string
    {
        return $user->createToken(config('passport.personal_access_client.secret'))->accessToken;
    }
}
