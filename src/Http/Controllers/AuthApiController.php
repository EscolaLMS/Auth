<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Exceptions\AuthException;
use EscolaLms\Auth\Http\Controllers\Swagger\AuthSwagger;
use EscolaLms\Auth\Http\Requests\CompleteSocialDataRequest;
use EscolaLms\Auth\Http\Requests\ForgotPasswordRequest;
use EscolaLms\Auth\Http\Requests\RefreshTokenRequest;
use EscolaLms\Auth\Http\Requests\ResendVerificationEmailRequest;
use EscolaLms\Auth\Http\Requests\ResetPasswordRequest;
use EscolaLms\Auth\Http\Requests\SocialAuthRequest;
use EscolaLms\Auth\Http\Resources\LoginResource;
use EscolaLms\Auth\Http\Resources\UserGroupResource;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\SocialAccountServiceContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;

class AuthApiController extends EscolaLmsBaseController implements AuthSwagger
{
    private AuthServiceContract $authService;
    private UserRepositoryContract $userRepository;
    private SocialAccountServiceContract $socialAccountService;

    /**
     * @param AuthServiceContract $authService
     */
    public function __construct(
        AuthServiceContract $authService,
        UserRepositoryContract $userRepository,
        SocialAccountServiceContract $socialAccountService
    )
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->socialAccountService = $socialAccountService;
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword(
            $request->input('email'),
            $request->input('return_url'),
        );

        return $this->sendSuccess(__('Password reset email sent'));
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword(
                $request->input('email'),
                $request->input('token'),
                $request->input('password'),
            );

            return $this->sendSuccess(__('New password saved'));
        } catch (AuthorizationException $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $token = $this->authService->refreshToken($request->user());

        return $this->sendResponse(LoginResource::make($token), __('Token refreshed'));
    }

    public function socialRedirect(SocialAuthRequest $request): RedirectResponse
    {
        /** @var \Laravel\Socialite\SocialiteManager|\Laravel\Socialite\Two\AbstractProvider $socialite */
        $socialite = Socialite::driver($request->route('provider'));
        return $socialite
            ->stateless()
            ->with([
                'return_url' => $request->input('return_url'),
                'state' => base64_encode(
                    json_encode([
                        'return_url' => $request->input('return_url')
                    ])
                )
            ])
            ->redirect();
    }

    public function socialCallback(SocialAuthRequest $request): RedirectResponse
    {
        $returnUrl = $this->socialAccountService->getReturnUrl(
            $request->route('provider'),
            $request->input('return_url'),
            $request->input('state')
        );

        return redirect($returnUrl);
    }

    public function completeSocialData(CompleteSocialDataRequest $request): JsonResponse
    {
        try {
            $this->socialAccountService->completeData(
                $request->input('token'),
                $request->input('email'),
                $request->input('return_url')
            );
        } catch (AuthException $e) {
            return $this->sendError($e->getMessage(), $e->getCode());
        }

        return $this->sendSuccess(__('The data has been completed. Verification email has been sent.'));
    }

    public function verifyEmail(Request $request, string $id, string $hash)
    {
        $user = $this->userRepository->find($id);

        if (
            $user instanceof MustVerifyEmail &&
            hash_equals($id, (string)$user->getKey()) &&
            hash_equals($hash, sha1($user->getEmailForVerification())) &&
            !$user->hasVerifiedEmail()
        ) {
            $user->markEmailAsVerified();
            event(new AccountConfirmed($user));
        }

        if ($request->wantsJson()) {
            return $this->sendSuccess(__('Your email address was successfully verified'));
        }

        return view('auth::email-verified');
    }

    public function resendEmailVerification(ResendVerificationEmailRequest $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->input('email'));

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            event(new AccountRegistered($user, $request->input('return_url', Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.return_url'))));
        }

        return $this->sendSuccess(__('Verification message resent if email exists in database'));
    }

    public function registerableGroups(UserGroupServiceContract $userGroupService): JsonResponse
    {
        return $this->sendResponseForResource(UserGroupResource::collection($userGroupService->getRegisterableGroups()), __('Registerable groups list'));
    }
}
