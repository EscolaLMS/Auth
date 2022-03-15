<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Models\User;
use EscolaLms\Categories\Http\Resources\CategoryResource;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;
use EscolaLms\Auth\Traits\ResourceExtandable;

class UserResource extends JsonResource
{
    use ResourceExtandable;

    public function __construct(User $resource)
    {
        $this->permissions = $resource->getAllPermissions();
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $fields = array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'age' => $this->age,
            'gender' => $this->gender,
            'country' => $this->country,
            'city' => $this->city,
            'street' => $this->street,
            'postcode' => $this->postcode,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'onboarding_completed' => $this->onboarding_completed,
            'email_verified' => $this->email_verified,
            'interests' => CategoryResource::collection($this->interests),
            'avatar' => $this->avatar_url,
            'roles' => $this->roles ? array_map(function ($role) {
                return $role['name'];
            }, $this->roles->toArray()) : [],
            'permissions' => $this->permissions ? array_map(function ($role) {
                return $role['name'];
            }, $this->permissions->toArray()) : [],
        ], function ($el) {
            return !is_null($el);
        });

        return self::apply($fields, $this);
    }
}

UserResource::extend(fn ($thisObj) => [
    'path_avatar' => $thisObj->path_avatar
]);

UserResource::extend(fn ($thisObj) => ModelFields::getExtraAttributesValues($thisObj->resource, MetaFieldVisibilityEnum::PUBLIC));

