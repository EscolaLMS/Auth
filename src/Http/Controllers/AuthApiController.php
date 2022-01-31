<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Auth\Events\PasswordChanged;
use EscolaLms\Auth\Http\Controllers\Swagger\AuthSwagger;
use EscolaLms\Auth\Http\Requests\ForgotPasswordRequest;
use EscolaLms\Auth\Http\Requests\RefreshTokenRequest;
use EscolaLms\Auth\Http\Requests\ResendVerificationEmailRequest;
use EscolaLms\Auth\Http\Requests\ResetPasswordRequest;
use EscolaLms\Auth\Http\Requests\SocialAuthRequest;
use EscolaLms\Auth\Http\Resources\UserGroupResource;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthApiController extends EscolaLmsBaseController implements AuthSwagger
{
    private AuthServiceContract $authService;
    private UserRepositoryContract $userRepository;

    /**
     * @param AuthServiceContract $authService
     */
    public function __construct(AuthServiceContract $authService, UserRepositoryContract $userRepository)
    {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
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
        $token = $this->authService->createTokenForUser($request->user());
        return $this->sendResponse(['token' => $token], __('Token refreshed'));
    }

    public function socialRedirect(SocialAuthRequest $request): RedirectResponse
    {
        return Socialite::driver($request->route('provider'))->stateless()->redirect();
    }

    public function socialCallback(SocialAuthRequest $request): RedirectResponse
    {
        $token = $this->authService->getTokenBySocial($request->route('provider'));

        return redirect(config('app.frontend_url') . '/authentication?token=' . $token);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
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

        return $this->sendSuccess('Your email address was successfully verified');
    }

    public function resendEmailVerification(ResendVerificationEmailRequest $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->input('email'));

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return $this->sendSuccess(__('Verification message resent if email exists in database'));
    }

    public function registerableGroups(UserGroupServiceContract $userGroupService): JsonResponse
    {
        return $this->sendResponseForResource(UserGroupResource::collection($userGroupService->getRegisterableGroups()), __('Registerable groups list'));
    }
}
