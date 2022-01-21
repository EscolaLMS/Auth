<?php

namespace EscolaLms\Auth\Http\Middleware;

use Closure;
use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use Illuminate\Support\Facades\Config;

class RegistrationEnabled
{
    public function handle($request, Closure $next)
    {
        if (Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.registration', SettingStatusEnum::DISABLED) === SettingStatusEnum::DISABLED) {
            abort(403);
        }

        return $next($request);
    }
}
