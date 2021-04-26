<?php

namespace EscolaLms\Auth\Dtos\Admin;

use EscolaLms\Auth\Dtos\UserUpdateDto as BasicUserUpdateDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserUpdateDto extends BasicUserUpdateDto
{
    private ?string $email;
    private ?string $password;
    private ?array $roles;

    public function __construct(?string $firstName, ?string $lastName, ?int $age, ?int $gender, ?string $country, ?string $city, ?string $street, ?string $postcode, ?string $email, ?array $roles, ?string $password)
    {
        parent::__construct($firstName, $lastName, $age, $gender, $country, $city, $street, $postcode);
        $this->email = $email;
        $this->roles = $roles;
        $this->password = $password;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->input('first_name'),
            $request->input('last_name'),
            $request->input('age'),
            $request->input('gender'),
            $request->input('country'),
            $request->input('city'),
            $request->input('street'),
            $request->input('postcode'),
            $request->input('email'),
            $request->input('roles'),
            $request->input('password')
        );
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        if ($this->email) {
            $array['email'] = $this->getEmail();
        }
        if ($this->password) {
            $array['password'] = $this->getPassword();
        }
        return $array;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password ? Hash::make($this->password) : null;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }
}
