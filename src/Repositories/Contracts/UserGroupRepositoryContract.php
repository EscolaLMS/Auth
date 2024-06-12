<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Database\Eloquent\Builder;

interface UserGroupRepositoryContract extends BaseRepositoryContract
{
    public function orderBy(Builder $query, OrderDto $orderDto): Builder;
}
