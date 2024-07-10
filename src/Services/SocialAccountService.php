<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Exceptions\TokenExpiredException;
use EscolaLms\Auth\Models\PreUser;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\PreUserRepositoryContract;
use EscolaLms\Auth\Repositories\Contracts\SocialAccountRepositoryContract;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\SocialAccountServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Enums\UserRole;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Facades\Socialite;

class SocialAccountService implements SocialAccountServiceContract
{
    private const TOKEN_EXPIRATION_TIME = 10;

    private AuthServiceContract $authService;
    private UserServiceContract $userService;
    private SocialAccountRepositoryContract $socialAccountRepository;
    private UserRepositoryContract $userRepository;
    private PreUserRepositoryContract $preUserRepository;

    public function __construct(
        AuthServiceContract $authService,
        UserServiceContract $userService,
        SocialAccountRepositoryContract $socialAccountRepository,
        UserRepositoryContract $userRepository,
        PreUserRepositoryContract $preUserRepository
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->socialAccountRepository = $socialAccountRepository;
        $this->userRepository = $userRepository;
        $this->preUserRepository = $preUserRepository;
    }

    public function getReturnUrl(string $provider, ?string $returnUrl, ?string $state): string
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        $socialAccount = $this->socialAccountRepository->findByProviderAndProviderId($provider, $socialUser->getId());

        $user = null;
        if ($socialAccount) {
            $user = $socialAccount->user;
        } elseif ($socialUser->getEmail()) {
            $user = $this->findOrCreateUser($socialUser);
            $user->markEmailAsVerified();
            $this->socialAccountRepository->create([
                'user_id' => $user->getKey(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        if ($user) {
            if (!$this->isUserActiveAndVerified($user)) {
                return $this->generateReturnUrl($returnUrl, $state, '') . '&error=true';
            }

            $token = $this->authService
                ->createTokenForUser(
                    $user,
                    Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.socialite_remember_me', false)
                )
                ->accessToken;

            return $this->generateReturnUrl($returnUrl, $state, $token);
        }

        $preUser = $this->createPreUser($provider, $socialUser);

        return $this->generateReturnUrl($returnUrl, $state, $preUser->token, false);
    }

    /**
     * @throws TokenExpiredException
     */
    public function completeData(string $token, string $email, ?string $returnUrl): void
    {
        $preUser = $this->preUserRepository->findByToken($token);

        if ($preUser->created_at <= Carbon::now()->subMinutes(self::TOKEN_EXPIRATION_TIME)) {
            throw new TokenExpiredException();
        }

        $userSaveDto = new UserSaveDto(
            $preUser->first_name,
            $preUser->last_name,
            true,
            [UserRole::STUDENT],
            $email,
            null,
            null,
            true,
        );

        $user = $this->userService->create($userSaveDto);
        event(new AccountRegistered($user, $returnUrl));

        $this->socialAccountRepository->create([
            'user_id' => $user->getKey(),
            'provider' => $preUser->provider,
            'provider_id' => $preUser->provider_id,
        ]);

        $preUser->delete();
    }

    private function findOrCreateUser(AbstractUser $socialUser): User
    {
        /** @var User $user */
        $user = $this->userRepository->findByEmail($socialUser->getEmail());

        if (!$user) {
            [$firstName, $lastName] = $this->splitSocialName($socialUser);

            $userSaveDto = new UserSaveDto(
                $firstName,
                $lastName,
                true,
                [UserRole::STUDENT],
                $socialUser->getEmail(),
                null,
                true,
            );

            $user = $this->userService->create($userSaveDto);
        }

        return $user;
    }

    private function createPreUser(string $provider, AbstractUser $socialUser): PreUser
    {
        [$firstName, $lastName] = $this->splitSocialName($socialUser);

        /** @var PreUser */
        return $this->preUserRepository->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'token' => Str::random(32),
        ]);
    }

    private function splitSocialName(AbstractUser $socialUser): array
    {
        $name = Str::of($socialUser->getName());
        $firstName = Str::of($name->explode(' ')->first())->trim();
        $lastName = $name->after($firstName)->trim();

        return [$firstName, $lastName];
    }

    private function generateReturnUrl(?string $returnUrl, ?string $sate, string $token, bool $isComplete = true): string
    {
        $returnUrl = $returnUrl
            ?? $this->getSocialReturnUrlFromState($sate)
            ?? (config('app.frontend_url') . '/authentication');

        $sep = parse_url($returnUrl, PHP_URL_QUERY) ? '&' : '?';

        return $returnUrl . $sep . 'token=' . $token . '&complete=' . ($isComplete ? 'true' : 'false');
    }

    private function getSocialReturnUrlFromState(?string $state): ?string
    {
        if (is_null($state)) {
            return null;
        }

        $decoded = base64_decode($state, true);
        if (!$decoded || base64_encode($decoded) !== $state) {
            return null;
        }
        $json = json_decode($decoded, true);
        if (is_null($json) || !array_key_exists('return_url', $json)) {
            return null;
        }

        return $json['return_url'];
    }

    private function isUserActiveAndVerified(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->is_active;
    }
}
