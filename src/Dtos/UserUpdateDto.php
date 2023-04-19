<?php

namespace EscolaLms\Auth\Dtos;
use Illuminate\Http\Request;

class UserUpdateDto extends ExtendableDto
{
    public static function instantiateFromRequest(Request $request): self
    {
        $value = new self();
        self::$keys = $request->keys();

        foreach (self::$constructorTypes as $key => $valueCallable) {
            $value->$key = $valueCallable($request);
        }
        return $value;
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
    'phone' => fn($request) => $request->input('phone'),
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
    'phone' => fn($thisObj) => $thisObj->phone,
]);
