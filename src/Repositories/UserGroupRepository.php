<?php

namespace EscolaLms\Auth\Repositories;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Repositories\Contracts\UserGroupRepositoryContract;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class UserGroupRepository extends BaseRepository implements UserGroupRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    public function model()
    {
        return Group::class;
    }

    public function orderBy(Builder $query, OrderDto $orderDto): Builder
    {
        return match ($orderDto->getOrderBy()) {
            'parent_name' => $query->withAggregate('parent', 'name')->orderBy('parent_name', $orderDto->getOrder() ?? 'asc'),
            default => $query->orderBy($orderDto->getOrderBy() ?? 'id', $orderDto->getOrder() ?? 'desc'),
        };
    }
}
