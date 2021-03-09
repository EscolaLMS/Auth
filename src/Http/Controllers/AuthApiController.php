<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Http\Controllers\Swagger\AuthSwagger;
use EscolaLms\Auth\Http\Requests\ForgotPasswordRequest;
use EscolaLms\Auth\Http\Requests\RefreshTokenRequest;
use EscolaLms\Auth\Http\Requests\ResendVerificationEmailRequest;
use EscolaLms\Auth\Http\Requests\ResetPasswordRequest;
use EscolaLms\Auth\Http\Requests\SocialAuthRequest;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

        return new JsonResponse(['success' => true], 200);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword(
                $request->input('email'),
                $request->input('token'),
                $request->input('password'),
            );

            return new JsonResponse(['success' => true], 200);
        } catch (AuthorizationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 401);
        }
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $token = $request->user()->createToken(config('passport.personal_access_client.secret'))->accessToken;

        return new JsonResponse(['success' => true, 'token' => $token]);
    }

    public function socialRedirect(SocialAuthRequest $request): RedirectResponse
    {
        return Socialite::driver($request->route('provider'))->stateless()->redirect();
    }

    public function socialCallback(SocialAuthRequest $request): RedirectResponse
    {
        $token = $this->authService->getTokenBySocial($request->route('provider'));

        return redirect(config('app.frontend_url') . '/#/social-login?token=' . $token);
    }

    public function verifyEmail(EmailVerificationRequest $request, string $id, string $hash): RedirectResponse
    {
        $user = $this->userRepository->find($id);

        if (
            hash_equals($id, (string)$user->getKey()) &&
            hash_equals($hash, sha1($user->getEmailForVerification())) &&
            !$user->hasVerifiedEmail()
        ) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect(config('app.frontend_url'));
    }

    public function resendEmailVerification(ResendVerificationEmailRequest $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->input('email'));

        if ($user) {
            $user->sendEmailVerificationNotification();
        }

        return new JsonResponse(['success' => true]);
    }
}
