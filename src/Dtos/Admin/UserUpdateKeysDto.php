<?php

namespace EscolaLms\Auth\Dtos\Admin;

use EscolaLms\Auth\Dtos\UserUpdateKeysDto as BasicUserUpdateKeysDto;
use Illuminate\Http\Request;

class UserUpdateKeysDto extends BasicUserUpdateKeysDto
{
    private bool $email;
    private bool $password;
    private bool $roles;

    public function __construct(bool $firstName = false, bool $lastName = false, bool $age = false, bool $gender = false, bool $country = false, bool $city = false, bool $street = false, bool $postcode = false, bool $email = false, bool $roles = false, bool $password = false)
    {
        parent::__construct($firstName, $lastName, $age, $gender, $country, $city, $street, $postcode);
        $this->email = $email;
        $this->roles = $roles;
        $this->password = $password;
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
            $request->has('email'),
            $request->has('roles'),
            $request->has('password')
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'roles' => $this->getRoles(),
        ]);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }
}
