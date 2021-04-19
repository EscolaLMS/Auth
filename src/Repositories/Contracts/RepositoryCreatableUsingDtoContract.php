<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use Illuminate\Database\Eloquent\Model;

interface RepositoryCreatableUsingDtoContract
{
    public function createUsingDto(DtoContract $dto): Model;
}
