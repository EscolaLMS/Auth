<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Models\User;
use EscolaLms\Categories\Http\Resources\CategoryResource;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;
use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Support\Collection;

class UserResource extends JsonResource
{
    use ResourceExtandable;

    protected Collection $permissions;

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
            'id' => $this->resource->getKey(),
            'name' => $this->resource->name,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'age' => $this->resource->age,
            'gender' => $this->resource->gender,
            'country' => $this->resource->country,
            'city' => $this->resource->city,
            'street' => $this->resource->street,
            'postcode' => $this->resource->postcode,
            'phone' => $this->resource->phone,
            'is_active' => $this->resource->is_active,
            'created_at' => $this->resource->created_at,
            'onboarding_completed' => $this->resource->onboarding_completed,
            'email_verified' => $this->resource->email_verified,
            'interests' => CategoryResource::collection($this->resource->interests),
            'avatar' => $this->resource->avatar_url,
            'roles' => $this->resource->roles ? array_map(function ($role) {
                return $role['name'];
            }, $this->resource->roles->toArray()) : [],
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

