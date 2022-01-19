<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserSaveDto implements InstantiateFromRequest, DtoContract
{
    private string $firstName;
    private string $lastName;
    private ?string $email;
    private ?string $password;
    private bool $isActive;
    private array $roles;
    private bool $verified;

    public function __construct(string $firstName, string $lastName, bool $isActive, array $roles, ?string $email = null, ?string $password = null, bool $verified = false)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
        $this->isActive = $isActive;
        $this->roles = $roles;
        $this->verified = $verified;
    }

    public function getUserAttributes(): array
    {
        $arr = [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'is_active' => $this->getIsActive(),
        ];
        if ($this->email) {
            $arr['email'] = $this->getEmail();
        }
        if ($this->password) {
            $arr['password'] = $this->getPassword();
        }
        return $arr;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->input('first_name'),
            $request->input('last_name'),
            $request->input('is_active', true),
            $request->input('roles', [UserRole::STUDENT]),
            $request->input('email'),
            $request->input('password'),
            $request->has('verified') ? $request->input('verified') : false
        );
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password ? Hash::make($this->password) : null;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getVerified(): bool
    {
        return $this->verified;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
        ];
    }
}
