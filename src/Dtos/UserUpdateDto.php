<?php

namespace EscolaLms\Auth\Dtos;
use EscolaLms\Auth\Dtos\ExtendableDto;

class UserUpdateDto extends ExtendableDto
{

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
]);

UserUpdateDto::extendToArray([
    'first_name' => fn ($thisObj) => $thisObj->firstName,
    'last_name' => fn ($thisObj) => $thisObj->lastName,
    'age' => fn ($thisObj) => $thisObj->age,
    'gender' => fn ($thisObj) => $thisObj->gender,
    'country' => fn ($thisObj) => $thisObj->country,
    'city' => fn ($thisObj) => $thisObj->city,
    'street' => fn ($thisObj) => $thisObj->street,
    'postcode' => fn ($thisObj) => $thisObj->postcode
]);
