<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Events\EscolaLmsLogoutTemplateEvent;
use EscolaLms\Auth\Http\Controllers\Swagger\LogoutSwagger;
use EscolaLms\Auth\Http\Requests\LogoutRequest;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class LogoutApiController extends EscolaLmsBaseController implements LogoutSwagger
{
    public function logout(LogoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->token()->revoke();
        event(new EscolaLmsLogoutTemplateEvent($user));
        return $this->sendSuccess(__('You have been successfully logged out!'));
    }
}
