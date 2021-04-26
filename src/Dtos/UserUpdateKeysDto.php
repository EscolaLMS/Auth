<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Auth\Dtos\Contracts\ModelKeysDtoContract;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class UserUpdateKeysDto implements InstantiateFromRequest, DtoContract, ModelKeysDtoContract
{
    private bool $firstName = false;
    private bool $lastName = false;
    private bool $age = false;
    private bool $gender = false;
    private bool $country = false;
    private bool $city = false;
    private bool $street = false;
    private bool $postcode = false;

    public function __construct(bool $firstName = false, bool $lastName = false, bool $age = false, bool $gender = false, bool $country = false, bool $city = false, bool $street = false, bool $postcode = false)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
        $this->gender = $gender;
        $this->country = $country;
        $this->city = $city;
        $this->street = $street;
        $this->postcode = $postcode;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->has('first_name'),
            $request->has('last_name'),
            $request->has('age'),
            $request->has('gender'),
            $request->has('country'),
            $request->has('city'),
            $request->has('street'),
            $request->has('postcode'),
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'age' => $this->getAge(),
            'gender' => $this->getGender(),
            'country' => $this->getCountry(),
            'city' => $this->getCity(),
            'street' => $this->getStreet(),
            'postcode' => $this->getPostcode(),
        ];
    }

    public function keyList(): array
    {
        return array_keys(array_filter($this->toArray(), fn (bool $value) => $value));
    }

    public function getFirstName(): bool
    {
        return $this->firstName;
    }

    public function getLastName(): bool
    {
        return $this->lastName;
    }

    public function getAge(): bool
    {
        return $this->age;
    }

    public function getGender(): bool
    {
        return $this->gender;
    }

    public function getCity(): bool
    {
        return $this->city;
    }

    public function getCountry(): bool
    {
        return $this->country;
    }

    public function getPostcode(): bool
    {
        return $this->postcode;
    }

    public function getStreet(): bool
    {
        return $this->street;
    }
}
