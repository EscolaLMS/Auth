<?php

namespace EscolaLms\Auth\Dtos\Admin;

use EscolaLms\Auth\Dtos\UserUpdateDto as BasicUserUpdateDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserUpdateDto extends BasicUserUpdateDto
{
    public static function instantiateFromRequest(Request $request): self
    {
        $value = new self();
        foreach (self::$constructorTypes as $key => $valueCallable) {
            $value->$key = $valueCallable($request);
        }
        return $value;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getEmailVerified(): ?bool
    {
        return $this->email_verified ?? null;
    }
}

UserUpdateDto::extendConstructor([
    'firstName' => fn ($request) => $request->input('first_name'),
    'lastName' => fn ($request) => $request->input('last_name'),
    'age' => fn ($request) => $request->input('age'),
    'gender' => fn ($request) => $request->input('gender'),
    'country' => fn ($request) => $request->input('country'),
    'city' => fn ($request) => $request->input('city'),
    'street' => fn ($request) => $request->input('street'),
    'postcode' => fn ($request) => $request->input('postcode'),
    'email' => fn ($request) => $request->input('email'),
    'roles' => fn ($request) => $request->input('roles'),
    'password' => fn ($request) => $request->input('password'),
    'email_verified' => fn ($request) => $request->input('email_verified', null),
    'isActive' => fn ($request) => $request->input('is_active'),
]);

UserUpdateDto::extendToArray([
    'first_name' => fn ($thisObj) => $thisObj->firstName,
    'last_name' => fn ($thisObj) => $thisObj->lastName,
    'age' => fn ($thisObj) => $thisObj->age,
    'gender' => fn ($thisObj) => $thisObj->gender,
    'country' => fn ($thisObj) => $thisObj->country,
    'city' => fn ($thisObj) => $thisObj->city,
    'street' => fn ($thisObj) => $thisObj->street,
    'postcode' => fn ($thisObj) => $thisObj->postcode,
    'email' => fn ($thisObj) => $thisObj->email,
    //'roles' => fn ($thisObj) => $thisObj->roles,
    'password' => fn ($thisObj) => $thisObj->password ? Hash::make($thisObj->password) : null,
    'is_active' => fn ($thisObj) => $thisObj->isActive,
]);
