<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Auth\Http\Requests\Admin\UserSettingsUpdateRequest;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Ramsey\Collection\Map\TypedMap;

class UserUpdateSettingsDto implements InstantiateFromRequest, DtoContract
{
    private TypedMap $settings;

    public function __construct(TypedMap $settings)
    {
        if ($settings->getKeyType() !== 'string') {
            throw new InvalidArgumentException("Settings must be a TypedMap with string keys");
        }
        $this->settings = $settings;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        if ($request->has('settings')) {
            $settings = Arr::pluck($request->input('settings'), 'value', 'key');
        } else {
            $settings = [];
        }
        $map = new TypedMap('string', 'mixed', $settings);
        return new self($map);
    }

    public function toArray(): array
    {
        return $this->settings->toArray();
    }

    public function getSettings(): TypedMap
    {
        return $this->settings;
    }
}
